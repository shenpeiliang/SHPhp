<?php
namespace Exception;
use Exception\ExceptionInterface;
/**
 * 异常基类
 */
class FremeException extends \Exception implements ExceptionInterface {
	/**
	 * 系统错误记录
	 * @param $error
	 */
	public static function for_system_error($error){
		//记录日志文件

		if(APP_DEBUG){
			//直接输出到调试页面
			echo $error;
		}else{
			//只显示错误页

		}
	}
}