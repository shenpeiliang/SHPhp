<?php
namespace Exception;

use Exception\ExceptionInterface;
use Exception\FremeException;

/**
 * 请求异常基类
 */
class HttpException extends FremeException implements ExceptionInterface
{
	/**
	 * curl错误
	 * @param string $no
	 * @param string $error
	 * @return static
	 */
	public static function for_curl_rrror(string $no, string $error)
	{
		return new static('CURL错误：编号[' . $no . '],说明[' . $error . ']');
	}
}