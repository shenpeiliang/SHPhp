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
	public $default_driver = 'FrameTemp';

	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'FrameTemp' => \Core\Template\Driver\FrameTemp::class,
		'Smarty' => \Core\Template\Driver\Smarty::class
	];
}