<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:28:00
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-13 17:28:54
 */
if (!function_exists('udate')) {
    /**
     * [udate  年月日时分秒毫秒]
     * @param  string  $format     [格式化]
     * @param  [type]  $utimestamp [description]
     * @param  integer $precision  [精度]
     * @return [type]              [description]
     */
    function udate($format = 'u', $utimestamp = null, $precision = 10000)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp    = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * $precision);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}