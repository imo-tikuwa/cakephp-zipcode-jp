<?php
namespace ZipcodeJp\Command;

use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use SplFileObject;
use ZipArchive;

/**
 * InitializeZipcodeJp command.
 *
 * @property \ZipcodeJp\Model\Table\ZipcodeJpsTable $ZipcodeJps
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
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addOption('connection', [
            'short' => 'c',
            'default' => 'default',
            'help' => 'The datasource connection to get data from.',
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('initialize_zipcode_jp command start.', 1, ConsoleIo::QUIET);
        $time_start = microtime(true);
        ini_set('memory_limit', '1024M');

        if (!file_exists(self::ZIP_LOCAL_DIR)) {
            mkdir(self::ZIP_LOCAL_DIR);
        }
        if (!in_array('zipcode_jps', ConnectionManager::get($args->getOption('connection'))->getSchemaCollection()->listTables(), true)) {
            $io->abort('There is no zipcode_jps table in the default connection. Please execute migration first.');
        }

        // 最新のマスタデータをダウンロード
        $file = file_get_contents(self::ZIPCODE_DATA_URL);
        file_put_contents(self::ZIP_LOCAL_PATH, $file, LOCK_EX);
        $io->out('Download of postal code data completed.', 1, ConsoleIo::QUIET);

        // 展開
        $zip = new ZipArchive();
        if ($zip->open(self::ZIP_LOCAL_PATH) !== true) {
            $io->abort('ken_all.zip failed to open.');
        } elseif ($zip->extractTo(self::ZIP_LOCAL_DIR) !== true) {
            $io->abort('ken_all.zip failed to extract.');
        }
        $zip->close();
        $io->out('The zip file of the postal code data has been expanded.', 1, ConsoleIo::QUIET);

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
        $io->out('Loaded KEN_ALL.csv.', 1, ConsoleIo::QUIET);

        $rows = [];
        foreach ($csv as $csv_row_index => $row) {
            if (count($row) !== 15) {
                $io->abort('Could not get the csv columns correctly.');
            }

            // CSVについて10000行ごとに処理中の行番号を出力
            $csv_row_count = $csv_row_index + 1;
            if ($csv_row_count % 10000 === 0) {
                $io->out("Processing {$csv_row_count} CSV data.", 1, ConsoleIo::QUIET);
            }

            // 郵便番号データの加工処理
            // 参考：http://zipcloud.ibsnet.co.jp/
            $zipcode = $row[2];
            $chouiki = $row[8];
            if (array_key_exists($zipcode, $rows)) {
                // 町域が2行以上に分かれているとき2行目以降をスキップ
                continue;
            } elseif ($chouiki === '以下に掲載がない場合') {
                // 以下のケースのとき町域削除
                // 01101,"060  ","0600000","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ｲｶﾆｹｲｻｲｶﾞﾅｲﾊﾞｱｲ","北海道","札幌市中央区","以下に掲載がない場合",0,0,0,0,0,0
                $row[8] = '';
            } elseif (str_ends_with($chouiki, 'の次に番地がくる場合')) {
                // 以下のケースのとき町域削除
                // 08546,"30604","3060433","ｲﾊﾞﾗｷｹﾝ","ｻｼﾏｸﾞﾝｻｶｲﾏﾁ","ｻｶｲﾏﾁﾉﾂｷﾞﾆﾊﾞﾝﾁｶﾞｸﾙﾊﾞｱｲ","茨城県","猿島郡境町","境町の次に番地がくる場合",0,0,0,0,0,0
                $row[8] = '';
            } elseif (str_ends_with($chouiki, '一円') && mb_strlen($chouiki) > 2) {
                // 以下のケースのとき町域削除
                // 13362,"10003","1000301","ﾄｳｷｮｳﾄ","ﾄｼﾏﾑﾗ","ﾄｼﾏﾑﾗｲﾁｴﾝ","東京都","利島村","利島村一円",0,0,0,0,0,0
                $row[8] = '';
            } elseif (str_contains($chouiki, '（') && str_ends_with($chouiki, '階）')) {
                // 以下のケースのとき町域を加工
                // 04101,"980  ","9806101","ﾐﾔｷﾞｹﾝ","ｾﾝﾀﾞｲｼｱｵﾊﾞｸ","ﾁｭｳｵｳｱｴﾙ(1ｶｲ)","宮城県","仙台市青葉区","中央アエル（１階）",0,0,0,0,0,0
                $replace = [
                    '（' => '',
                    '）' => '',
                ];
                $row[8] = str_replace(array_keys($replace), array_values($replace), $chouiki);
            } elseif (str_contains($chouiki, '（')) {
                // 以下のようなケースのとき町域を加工
                // 01215,"07901","0790177","ﾎｯｶｲﾄﾞｳ","ﾋﾞﾊﾞｲｼ","ｶﾐﾋﾞﾊﾞｲﾁｮｳ(ｷｮｳﾜ､ﾐﾅﾐ)","北海道","美唄市","上美唄町（協和、南）",1,0,0,0,0,0
                // または、
                // 町域が2行以上に分かれているとき1行目の町域を加工
                // 40206,"826  ","8260043","ﾌｸｵｶｹﾝ","ﾀｶﾞﾜｼ","ﾅﾗ(ｱｵﾊﾞﾁｮｳ､ｵｵｳﾗ､ｶｲｼｬﾏﾁ､ｶｽﾐｶﾞｵｶ､ｺﾞﾄｳｼﾞﾆｼﾀﾞﾝﾁ､ｺﾞﾄｳｼﾞﾋｶﾞｼﾀﾞﾝﾁ､ﾉｿﾞﾐｶﾞｵｶ､","福岡県","田川市","奈良（青葉町、大浦、会社町、霞ケ丘、後藤寺西団地、後藤寺東団地、希望ケ丘、",0,0,0,0,0,0
                // 40206,"826  ","8260043","ﾌｸｵｶｹﾝ","ﾀｶﾞﾜｼ","ﾏﾂﾉｷ､ﾐﾂｲｺﾞﾄｳｼﾞ､ﾐﾄﾞﾘﾏﾁ､ﾂｷﾐｶﾞｵｶ)","福岡県","田川市","松の木、三井後藤寺、緑町、月見ケ丘）",0,0,0,0,0,0
                $row[8] = mb_substr($chouiki, 0, mb_strpos($chouiki, '（'));
            }
            $rows[$zipcode] = $row;
        }
        $io->out("Processed {$csv_row_count} CSV data.", 1, ConsoleIo::QUIET);

        // 既存のデータをトランケートしてから最新のデータを登録
        $this->ZipcodeJps = $this->fetchTable('ZipcodeJps');
        $sqls = (new TableSchema($this->ZipcodeJps->getTable()))->truncateSql($this->ZipcodeJps->getConnection());
        $io->out("Truncate the zipcode_jps table.", 1, ConsoleIo::QUIET);
        foreach ($sqls as $sql) {
            $this->ZipcodeJps->getConnection()->execute($sql)->execute();
        }

        $query = $this->getZipcodeJpsBulkInsertQuery();
        $row_count = count($rows);
        $from_count = 1;
        $to_count = 1;
        foreach ($rows as $row) {
            $query->values([
                'zipcode' => $row[2],
                'pref' => $row[6],
                'city' => $row[7],
                'address' => $row[8],
            ]);
            if ($to_count % 10000 === 0 || $to_count === $row_count) {
                $io->out(sprintf('Register the %d to %d zip code data.', $from_count, $to_count), 1, ConsoleIo::QUIET);
                $from_count = $to_count + 1;
                $query->execute();
                $query = $this->getZipcodeJpsBulkInsertQuery();
            }
            $to_count++;
        }

        $io->out(sprintf('initialize_zipcode_jp command end. took %f secs.', microtime(true) - $time_start), 1, ConsoleIo::QUIET);

        return static::CODE_SUCCESS;
    }

    /**
     * zipcode_jpsテーブルにバルクインサートを行うクエリビルダを返す
     * @return \Cake\ORM\Query
     */
    private function getZipcodeJpsBulkInsertQuery()
    {
        return $this->ZipcodeJps->query()
        ->insert(['zipcode', 'pref', 'city', 'address']);
    }
}
