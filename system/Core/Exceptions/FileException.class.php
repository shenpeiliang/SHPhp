<?php
namespace Core\Exceptions;
use Core\Exceptions\ExceptionInterface;
use Core\Exceptions\FremeException;
/**
 * 文件异常基类
 */
class FileException extends FremeException implements ExceptionInterface{
	public static function for_not_found()
	{
		return new static('文件不存在');
	}
}