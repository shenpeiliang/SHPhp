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

/**
 * 递归数组函数
 * @param string $filter
 * @param array $data
 * @return array
 */
function array_map_recursive(string $filter, array $data)
{
	$result = [];
	foreach ($data as $key => $val)
	{
		$result[$key] = is_array($val)
			? array_map_recursive($filter, $val)
			: call_user_func($filter, $val);
	}
	return $result;
}

/**
 * 框架过滤方法
 * @param string $value
 */
function frame_filter(string &$value)
{
	// 过滤查询特殊字符
	if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value))
	{
		$value .= ' ';
	}
}

/**
 * 移除攻击代码
 */
function remove_xss($var)
{
	static $_parser = null;
	if ($_parser === null)
	{
		require_once SRC_PATH.'vendor/autoload.php';
		$config = HTMLPurifier_Config::createDefault();
		$_parser = new HTMLPurifier ($config);
	}
	if (is_array($var))
	{
		foreach ($var as $key => $val)
		{
			$var [$key] = remove_xss($val);
		}
	} else
	{
		$var = $_parser->purify($var);
	}
	return $var;
}

/**
 * 更优于var_dump的打印方式
 * @param $data
 */
function debug_dump($data)
{
	require_once SRC_PATH.'vendor/autoload.php';
	dump($data);
}

