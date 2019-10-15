<?php
namespace Core\Exceptions;
use Core\Exceptions\ExceptionInterface;
use Core\Exceptions\FremeException;
/**
 * 加载异常基类
 */
class AutoLoadException extends FremeException implements ExceptionInterface{
	/**
	 * 配置参数错误
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
	public static function for_file_not_found()
	{
		return new static('文件不存在');
	}
}