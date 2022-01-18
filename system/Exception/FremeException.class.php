<?php
namespace Exception;
use Core\View;
use Exception\ExceptionInterface;

/**
 * FremeException框架异常处理
 * @author shenpeiliang
 * @date 2022-01-17 11:19:28
 */
class FremeException extends \Exception implements ExceptionInterface {
	/**
	 * 系统错误记录
	 * @param $error
	 */
	public static function for_system_error(\Exception $error){
        $error = get_object_vars($error);
		//记录日志文件
		if(APP_DEBUG){
			//直接输出到调试页面
			if(function_exists('dump'))
                dump($error['message']);
            else
                var_dump($error['message']);
		}else{
			//只显示错误页
            $view = new View();
            $view->assign('e', $error)->display(convention_config('tmpl_exception_file'));
		}
	}
}