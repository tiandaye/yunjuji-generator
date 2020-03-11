<?php
/**
 * Created by PhpStorm.
 * User: tianwangchong
 * Date: 2020-03-11
 * Time: 22:42
 */

return [
    // 命令行
    'command'    => [
        // `php` 命令路径
        'php_command' => 'php'
    ],
    // 产生 `fieldJson` 的配置
    'field_json' => [
        'csv' => [
            // 读取 `csv` 时的编码, `UTF-8` 和 `GBK`
            'character_encoding' => 'GBK'
        ]
    ],
    // 模板
    'templates'  => [
        'extend' => '',
        'base'   => 'yunjuji-generator'
    ],
    'name'       => 'tian',
];