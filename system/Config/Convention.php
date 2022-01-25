<?php
return [
	//默认输出编码
	'default_charset' => 'UTF-8',
	//页面缓存
	'http_cache_control' => 'private',

    //异常模板文件
    'tmpl_exception_file'   =>  SYSTEM_PATH.'Tpl/Exception.html',

	//模板引擎配置项
	'template_option' => [
		'smarty' => [ // smarty引擎
			'suffix' => '.html', //模板文件后缀名
			'delimiter' => '_', //模板文件命名规则 控制器_方法
			'tmpl_cache_on' => true, //是否开启模板编译缓存,设为false则每次都会重新编译
			'tmpl_engine_config' => [ //标签
				'left_delimiter' => '<{',
				'right_delimiter' => '}>'
			],
			'template_compile_path' => SRC_PATH . 'Runtimes/', //模板编译文件保存地址
		],
		'frame' => [ //框架自定义引擎
			'suffix' => '.html',
			'delimiter' => '_',
			'template_compile_path' => SRC_PATH . 'Runtimes/', //模板编译文件保存地址
			'template_tag' => [ //模板引擎解析标签
				//插件标签
				'taglib_begin' => '<',
				'taglib_end' => '>',
				//变量标签
				'tmpl_l_delim' => '<{',
				'tmpl_r_delim' => '}>',
			],
		],
		'origin' => [ //php原生
			'suffix' => '.php',
			'delimiter' => '_',
		],
	],

	//使用的模板引擎，默认框架自定义引擎
	//'template_driver' => '',

	//模板主题
	'template_theme' => 'default',


	//自动加载
	'loader' =>
		[
			//psr4规则
			'psr4' =>
				[
					'map' =>
						[
							'Core' => SYSTEM_PATH . 'Core',
							'Exception' => SYSTEM_PATH . 'Exception'
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
		],

	//SESSION设置
	'session' => [
		'auto_start' => TRUE, //是否自动开启
		'options' => [], //配置数组 支持type name id path expire domain 等参数
		'driver' => '', //驱动，默认文件
		'prefix' => '', //键值前缀
		'db' => '' //指定保存的库
	],

	//Redis配置
	'redis' => [
		'host' => '127.0.0.1',
		'port' => 6379,
		'auth' => null,    //是否有用户验证，默认无密码验证。如果不是为null，则为验证密码
		'timeout' => 0   //连接超时
	],
	//jsonp处理函数名
	'jsonp_handler' => 'callback',

	//curl选项配置
	'curl_option' => [
		'CURLOPT_RETURNTRANSFER' => TRUE, //将curl_exec()获取的信息以字符串返回，而不是直接输出。
		'CURLOPT_HEADER' => TRUE, //将头文件的信息作为数据流输出
		'CURLOPT_FRESH_CONNECT' => TRUE, //强制获取一个新的连接，而不是缓存中的连接
		'CURLOPT_FOLLOWLOCATION' => TRUE, //允许重定向
		'' => '', //

		'CURLOPT_CAINFO' => '', //证书地址
		'CURLOPT_SSL_VERIFYPEER' => FALSE, //是否需要验证证书

		'CURLOPT_TIMEOUT_MS' => 10000, //允许的执行时间 毫秒 默认10秒
		'CURLOPT_CONNECTTIMEOUT_MS' => 5000, //连接超时时间 毫秒 默认5秒
	],

	//cookie配置
	'cookie' => [
		'expire' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => FALSE, //是否仅仅通过安全的 HTTPS 连接传给客户端
		'httponly' => TRUE,
	],

    //加密配置
    'crypt' => [
        'aes' => [
            'key' => 'shikee!root',
            'method' => 'AES-128-CBC', //加密方法
        ],
        'rsa' => [
            'private_key' => SRC_PATH . 'system/Config/rsa_private_key.pem',
            'public_key' => SRC_PATH . 'system/Config/rsa_public_key.pem',
            'algorithm' => OPENSSL_ALGO_SHA256, //RSA2
        ],

    ],
];