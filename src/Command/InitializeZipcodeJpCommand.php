<?php
namespace ZipcodeJp\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SplFileObject;
use ZipArchive;

/**
 * InitializeZipcodeJp command.
 */
class InitializeZipcodeJpCommand extends Command
{
    /** 郵便番号のマスタデータのURL */
    const ZIPCODE_DATA_URL = 'https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip';
    /** 郵便番号のマスタデータのZIPファイルを置くディレクトリ */
    const ZIP_LOCAL_DIR = TMP . 'zipcode_data' . DS;
    /** 郵便番号のマスタデータのZIPファイルのローカルパス */
    const ZIP_LOCAL_PATH = self::ZIP_LOCAL_DIR . 'ken_all.zip';
    /** ZIP展開後のCSVファイルパス */
    const KEN_ALL_CSV_PATH = self::ZIP_LOCAL_DIR . 'KEN_ALL.CSV';

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        ini_set('memory_limit', '1024M');

        if (!file_exists(self::ZIP_LOCAL_DIR)) {
            mkdir(self::ZIP_LOCAL_DIR);
        }

        // 最新のマスタデータをダウンロード
        $file = file_get_contents(self::ZIPCODE_DATA_URL);
        file_put_contents(self::ZIP_LOCAL_PATH, $file, LOCK_EX);

        // 展開
        $zip = new ZipArchive();
        if ($zip->open(self::ZIP_LOCAL_PATH) !== true) {
            $this->abort(self::CODE_ERROR);
        } elseif ($zip->extractTo(self::ZIP_LOCAL_DIR) !== true) {
            $zip->close();
            $this->abort(self::CODE_ERROR);
        }

        // php7でパースずれが発生しないcsv読み込み（sjis、CRLF）
        // 参考：https://qiita.com/tiechel/items/468c737b7a2f38f6f1a8
        setlocale(LC_ALL, 'English_United States.1252');
        $csv = new SplFileObject("php://filter/read=convert.iconv.cp932%2Futf-8/resource=" . self::KEN_ALL_CSV_PATH, 'rb');
        $csv->setFlags(
            SplFileObject::DROP_NEW_LINE |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::READ_CSV
        );
        $rows = [];
        foreach($csv as $row) {
            if (count($row) !== 15) {
                $this->abort(self::CODE_ERROR);
            }
            $rows[] = $row;
        }
        debug(memory_get_usage());
        debug(count($rows));


        // // php7でsjisのcsvを読み込む
        // // 参考：https://mgng.mugbum.info/1014
        // $str = file_get_contents(self::KEN_ALL_CSV_PATH);
        // $is_win = strpos(PHP_OS, "WIN") === 0;
        // if ($is_win) {
        //     setlocale(LC_ALL, "Japanese_Japan.932");
        // } else {
        //     setlocale(LC_ALL, "ja_JP.UTF-8");
        //     $str = mb_convert_encoding($str, "UTF-8", "SJIS-win");
        // }
        // $fp = fopen("php://temp", "r+");
        // fwrite($fp, str_replace(array("\r\n", "\r" ), "\n", $str));
        // rewind($fp);
        // while($row = fgetcsv($fp)) {
        //     if ($is_win) {
        //         $row = array_map(function($val){
        //             return mb_convert_encoding($val, "UTF-8", "SJIS-win");
        //         }, $row);
        //     }
        //     if (count($row) !== 15) {
        //         debug($row);
        //         exit;
        //     } else {
        //         debug("OK");
        //     }
        // }
        // fclose($fp);



        // // // csvファイルを変換(sjis→utf8、ダブルクォーテーション削除)
        // $csv_data = file_get_contents(self::KEN_ALL_CSV_PATH);
        // $csv_data = mb_convert_encoding($csv_data, 'UTF-8', 'sjis-win');
        // // $csv_data = str_replace('"', '', $csv_data);
        // file_put_contents(self::KEN_ALL_CSV_PATH, $csv_data, LOCK_EX);

        // // csvをパース
        // $records = $this->load_postal_cd_csv(self::KEN_ALL_CSV_PATH);
        // debug($records);

        // //
        // $fp = fopen(self::KEN_ALL_CSV_PATH, "r");
        // while ($row = fgetcsv($fp)) {
        //     debug(count($row));
        //     debug($row[2]);
        //     debug($row[6]);
        //     debug($row[7]);
        //     debug($row[8]);
        // }
        // fclose($fp);


        // csv読み込み(sjis,ヘッダ行なし)
        // $csv = new SplFileObject(self::KEN_ALL_CSV_PATH, 'rb');
        // $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        // foreach ($csv as $i => $row) {
        //     debug(count($row));
        //     debug($row);
        //     if (count($row) !== 15) {
        //         $this->abort();
        //     }
        //     var_dump($row);
        // }

    }


    //郵便番号CSVデータを読込む
    //町域名が分割されている場合はマージする
    private function load_postal_cd_csv($path)
    {
        $is_win = strpos(PHP_OS, "WIN") === 0;
        if ($is_win) {
            setlocale(LC_ALL, "Japanese_Japan.932");
        } else {

        }

        $records = array();

        $merge = array();
        $bracketed = FALSE;

        $fp = fopen($path, 'r');
        $ret = TRUE;
        $row = 0;
        while (($data = fgetcsv($fp, 0, ",")) !== FALSE) {
            $chouiki = $data[8];

            $row = NULL;
            $merged = FALSE;

            //括弧は出現していない
            if( ! $bracketed) {
                if( ! $this->contains($chouiki, '（') ) {
                    //括弧の無い通常の行
                    $row = $data;
                } else {
                    if( $this->contains($chouiki, '）') ) {
                        //括弧の含まれる通常の行
                        $row = $data;
                    } else {
                        //閉じ括弧が無い
                        $bracketed = TRUE;
                        $merge = array($data);
                    }
                }
            } else {
                if( $this->contains($chouiki, '）') ) {
                    //閉じ括弧あり(ここまでをマージ)
                    $bracketed = FALSE;
                    $merge[] = $data;
                    $row = $this->merge_rows($merge);
                    $merge = array();
                    $merged = TRUE;
                } else {
                    //閉じ括弧が無い
                    //3行以上に分割された行
                    $merge[] = $data;
                }
            }

            if($row) $records[] = $row;

            //if($merged) {
            //  echo $row[5].'<br />';
            //  echo $row[8].'<br />';
            //}
        }
        return $records;
    }

    //行マージ
    private function merge_rows($rows)
    {
        $prev_chouiki_kana = $rows[0][5];
        $ret = $rows[0];
        for($i=1; $i<count($rows); $i++) {
            $row = $rows[$i];
            $ret[8] .= $row[8]; //町域(漢字)をマージ

            //カナは前行と同じものが繰り返し出現することがあるようなので重複は除く
            if($prev_chouiki_kana != $row[5])
                $ret[5] .= $row[5]; //町域(カナ)をマージ
        }
        return $ret;
    }

    private function contains($chouiki, $str)
    {
        $pos = mb_strpos($chouiki, $str);
        return !($pos === FALSE);
    }
}
