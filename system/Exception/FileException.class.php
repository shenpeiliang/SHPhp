<?php
namespace Exception;

use Exception\ExceptionInterface;
use Exception\FremeException;

/**
 * 文件异常基类
 * @author shenpeiliang
 * @date 2022-01-17 11:26:09
 */
class FileException extends FremeException implements ExceptionInterface
{
	/**
	 * 文件找不到
	 * @return static
	 */
	public static function for_not_found(string $file = '')
	{
		return new static('文件不存在' . ($file ?? ''));
	}

	/**
	 * 配置参数缺少
	 * @return static
	 */
	public static function for_invalid_param()
	{
		return new static('配置参数缺省');
	}

	/**
	 * 配置参数错误
	 * @return static
	 */
	public static function for_error_param()
	{
		return new static('配置参数错误');
	}
}