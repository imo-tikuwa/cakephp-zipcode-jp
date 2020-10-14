<?php

namespace ZipcodeJp\Util;

/**
 * ZipcodeJpプラグイン内で使用する関数をまとめたUtilクラス
 * @author tikuwa
 *
 */
class ZipcodeJpUtils
{
    /**
     * 文字列$haystackは$needleを含む？
     * @param string $haystack 検索の対象文字列
     * @param string $needle 検索する文字列
     * @return boolean
     */
    public static function contain($haystack, $needle)
    {
        return $needle === "" || mb_strpos($haystack, $needle) > 0;
    }

    /**
     * 文字列$haystackは$needleで始まる？
     * @param string $haystack 検索の対象文字列
     * @param string $needle 検索する文字列
     * @return boolean
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * 文字列$haystackは$needleで終わる？
     * @param string $haystack 検索の対象文字列
     * @param string $needle 検索する文字列
     * @return boolean
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, - strlen($needle)) === $needle;
    }
}
