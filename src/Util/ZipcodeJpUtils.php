<?php

namespace ZipcodeJp\Util;

/**
 * ZipcodeJpプラグイン内で使用する関数をまとめたUtilクラス
 * @author tikuwa
 *
 */
class ZipcodeJpUtils {

    /**
     * 文字列$haystackは$needleを含む？
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function contain($haystack, $needle)
    {
        return $needle === "" || mb_strpos($haystack, $needle) > 0;
    }

    /**
     * 文字列$haystackは$needleで始まる？
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function starts_with($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * 文字列$haystackは$needleで終わる？
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function ends_with($haystack, $needle)
    {
        return $needle === "" || substr($haystack, - strlen($needle)) === $needle;
    }
}
