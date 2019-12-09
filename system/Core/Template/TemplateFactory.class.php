<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Template;
use Core\Service\BaseFactoryInterface;

class TemplateFactory implements BaseFactoryInterface
{
	/**
	 * 创建模板类驱动
	 * @return TemplateInterface
	 */
	public function create(): \Core\Template\TemplateInterface{
		//基本配置
		$template_base = new \Core\Template\TemplateBase();
		//使用默认配置
		$template_driver = $template_base->default_driver;

		//配置文件中是否有配置
		$template_driver_config = convention_config('template_driver');
		if($template_driver_config && array_key_exists($template_driver_config, $template_base->valid_drivers))
			$template_driver = $template_driver_config;

		$template_driver_object = $template_base->valid_drivers[$template_driver];

		return new $template_driver_object();
	}
}