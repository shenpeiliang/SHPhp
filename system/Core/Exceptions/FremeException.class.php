<?php
namespace Core\Exceptions;
use Core\Exceptions\ExceptionInterface;
/**
 * 异常基类
 */
class FremeException extends \Exception implements ExceptionInterface {
	/**
	 * 系统错误记录
	 * @param \Exception $error
	 */
	public static function for_system_error(\Exception $error){
		//记录日志文件

		if(APP_DEBUG){
			//直接输出到调试页面
			echo $error;
		}else{
			//只显示错误页

		}
	}
}