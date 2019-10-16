<?php
/**
 * 助手文件
 */

/**
 * 获取实例
 * @param string $class
 * @return string
 */
function get_instance(string $class)
{
	return $class::get_instance();
}

/**
 * 获取配置参数
 * @param string $keys
 * @param null $default
 * @return mixed
 */
function config(string $keys, $default = NULL)
{
	return get_instance('\Core\Config')->get($keys, $default);
}