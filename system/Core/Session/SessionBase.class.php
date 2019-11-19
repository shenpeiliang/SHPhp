<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Session;


class SessionBase
{
	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'Redis' => \Core\Session\Driver\RedisHandler::class
	];
}