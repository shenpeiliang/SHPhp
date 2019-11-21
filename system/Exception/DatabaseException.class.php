<?php
namespace Exception;

use Exception\ExceptionInterface;
use Exception\FremeException;

/**
 * 异常基类
 */
class DatabaseException extends FremeException implements ExceptionInterface
{
	/**
	 * 配置参数错误
	 * @return static
	 */
	public static function for_invalid_param()
	{
		return new static('配置参数缺省');
	}

	/**
	 * 连接错误
	 * @return static
	 */
	public static function for_connect_error($e)
	{
		return new static('系统错误，连接错误:' . print_r($e, TRUE));
	}
}