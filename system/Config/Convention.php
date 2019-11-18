<?php
return [
	//默认输出编码
	'default_charset' => 'UTF-8',
	//页面缓存
	'http_cache_control' => 'private',


	//模板文件名分隔符
	'template_delimiter' => '_',
	//模板文件后缀
	'template_suffix' => '.html',
	//模板主题
	'template_theme' => 'default',
	//模板编译文件保存地址
	'template_compile_path' => SRC_PATH . 'Runtimes/',

	//模板引擎
	//'template_driver' => 'Smarty',
	'tmpl_cache_on' => true,        //Smarty是否开启模板编译缓存,设为false则每次都会重新编译

	//模板引擎解析标签
	'template_tag' =>
		[
			//插件标签
			'taglib_begin' => '<',
			'taglib_end' => '>',
			//变量标签
			'tmpl_l_delim' => '<{',
			'tmpl_r_delim' => '}>',
		],
	//smarty模板引擎配置
	'tmpl_engine_config' => [
		'left_delimiter' => '<{',
		'right_delimiter' => '}>'
	],
	//自动加载
	'loader' =>
		[
			//psr4规则
			'psr4' =>
				[
					'map' =>
						[
							'Core'
						],
					//被加载文件的后缀名
					'file_suffixes' => '.class.php',
				],
		],
	//默认路由设置
	'route' =>
		[
			'controller' => 'Index',
			'method' => 'index',
		]
];