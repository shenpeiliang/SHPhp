<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Cache;


class TemplateBase
{
	/**
	 * 默认使用的驱动
	 * @var string
	 */
	public $default_driver = 'Redis';

	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'Redis' => \Core\Cache\Driver\RedisHandler::class
	];
}