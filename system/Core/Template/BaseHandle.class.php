<?php
/**
 * shenpeiliang
 */

namespace Core\Template;


class BaseHandle
{
	/**
	 * 给模板对象添加控制对象属性
	 */
	protected function set_controller_var(): void{
		//控制器对象
		$controller = new \Core\Controller();

		foreach(get_object_vars($controller) as $key => $item){
			if(!isset($this->$key)){
				$this->$key = $controller->$key;
			}
		}
	}
}