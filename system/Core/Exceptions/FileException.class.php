<?php
namespace Core\Exceptions;
use Core\Exceptions\ExceptionInterface;
use Core\Exceptions\FremeException;
/**
 * 文件异常基类
 */
class FileException extends FremeException implements ExceptionInterface{
	/**
	 * 文件找不到
	 * @return static
	 */
	public static function for_not_found()
	{
		return new static('文件不存在');
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