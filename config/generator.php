<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:36:52
 * @Last Modified by:   admin
 * @Last Modified time: 2017-10-12 11:15:20
 */
return [
	// 命令行
	'command' => [
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
	'templates' => [
		'extend' => '',
		'base' => 'yunjuji-generator'
	],
    'name' => 'tian',
];
