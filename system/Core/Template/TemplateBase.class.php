<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Template;


class TemplateBase
{
	/**
	 * 默认使用的驱动
	 * @var string
	 */
	public static $default_driver = 'Frame';

	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'Frame' => \Core\Template\Driver\FrameHandler::class,
		'Smarty' => \Core\Template\Driver\SmartyHandler::class,
		'Origin' => \Core\Template\Driver\OriginHandler::class
	];
}