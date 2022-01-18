<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Session;
use Core\Service\BaseFactoryInterface;

class SessionFactory implements BaseFactoryInterface
{
	/**
	 * 创建模板类驱动
	 * @return TemplateInterface
	 */
	public function create(): \SessionHandlerInterface{
		//基本配置
		$session_base = new SessionBase();

		//配置文件中是否有配置
		$session_driver = convention_config('session.driver');
		if($session_driver && array_key_exists($session_driver, $session_base->valid_drivers))
			$session_driver_object = $session_base->valid_drivers[$session_driver];

		return new $session_driver_object();
	}
}