<?php
return [
	//自动加载
	'LOADER' => [
		//psr4规则
		'PSR4' => [
			'MAP' => [
				'Core',
				'Helper'
			],
			'FILE_SUFFIXES' => '.class.php'
		],

	],

	//路由默认
	'ROUTE' => [
		'CONTROLLER' => 'Index',
		'METHOD' => 'index'
	]
];