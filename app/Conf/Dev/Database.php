<?php
return [
	'database_driver' => 'Mysql',

	//主库
	'master' => [
		'host' => '127.0.0.1',
		'username' => 'root',
		'password' => 'root',
		'database' => 'test',
		'charset' => 'utf8',
		'prefix' => 'hs_',
		'persistent' => false,
	],
	//读库
	'slave' => [
		'host' => '127.0.0.1',
		'username' => 'root',
		'password' => 'root',
		'database' => 'test',
		'charset' => 'utf8',
		'prefix' => 'hs_',
		'persistent' => false,
	]
];