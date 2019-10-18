<?php

//默认输出编码
$config['default_charset'] = 'UTF-8';

//页面缓存
$config['http_cache_control'] = 'private';

//模板文件名分隔符
$config['template_delimiter'] = '_';

//模板文件后缀
$config['template_suffix'] = '.html';

//模板主题
$config['template_theme'] = 'default';

//模板引擎解析标签
$config['template_compile_path'] = SRC_PATH .'Runtimes/';
//插件标签
$config['template_tag']['taglib_begin'] = '<';
$config['template_tag']['taglib_end'] = '/>';
//变量标签
$config['template_tag']['tmpl_l_delim'] = '<{';
$config['template_tag']['tmpl_r_delim'] = '}>';

//自动加载
//psr4规则
$config['loader']['psr4']['map'] = ['core',	'helper'];
//被加载文件的后缀名
$config['loader']['psr4']['file_suffixes'] = '.class.php';

//默认路由设置
$config['route']['controller'] = 'Index';
$config['route']['method'] = 'index';