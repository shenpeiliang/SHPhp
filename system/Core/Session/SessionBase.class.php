<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Session;


use Core\Session\Driver\RedisHandler;

class SessionBase
{
	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'Redis' => RedisHandler::class
	];
}