<?php
namespace Exception;

use Exception\ExceptionInterface;
use Exception\FremeException;

/**
 * 会话异常基类
 */
class CacheException extends FremeException implements ExceptionInterface
{
	/**
	 * 文件找不到
	 * @return static
	 */
	public static function for_connect_error($e)
	{
		return new static('系统错误，连接错误:' . print_r($e, TRUE));
	}
}