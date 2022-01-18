<?php
declare(strict_types = 1);

//检测PHP环境
if (version_compare(PHP_VERSION, '7.0.0', '<')) die('require PHP > 7.0.0 !');

//开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', FALSE);

//配置环境 开发dev 生产prod 测试test
define('ENVIRONMENT', 'dev');

//源目录
define('SRC_PATH',  dirname(__DIR__) . '/');

//主目录
define('ROOT_PATH', SRC_PATH . 'root');

// 定义应用目录
define('APP_PATH', SRC_PATH . 'app/');

// 引入框架入口文件
require SRC_PATH . 'system/Bootstrap.php';
