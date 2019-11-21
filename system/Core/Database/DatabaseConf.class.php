<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Database;


class DatabaseConf
{
	/**
	 * 默认使用的驱动
	 * @var string
	 */
	public $default_driver = 'PdoHandler';

	/**
	 * 数据库组配置
	 * @var array
	 */
	public static $db_groups = [
		'master',
		'slave'
	];

	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'PdoHandler' => \Core\Database\Driver\PdoHandler::class
	];
}