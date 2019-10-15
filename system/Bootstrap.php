<?php
//系统常量定义
defined('SYSTEM_PATH') or define('SYSTEM_PATH', __DIR__ . '/'); //框架目录
defined('APP_DEBUG') or define('APP_DEBUG', false); // 是否调试模式

//类文件后缀
const EXT = '.class.php';

//加载核心类
require_once SYSTEM_PATH . 'Core/Frame' . EXT;

//启动
\core\Frame::run();