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
 * 获取应用配置参数
 * @param string $keys common.template_compile_path
 * @param null $default
 * @return mixed
 */
function config(string $keys, $default = NULL)
{
    return get_instance('\Core\Config')->get_app_config($keys, $default);
}

/**
 * 获取系统配置惯例
 * @return mixed
 */
function convention_config(string $keys)
{
    return get_instance('\Core\Config')->get_convention_config($keys);

}

