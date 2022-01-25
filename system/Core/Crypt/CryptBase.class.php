<?php

namespace Core\Crypt;

use Core\Cache\Driver\OpensslHandler;
use Core\Cache\Driver\Rsa2Handler;

/**
 * 驱动配置
 * @author shenpeiliang
 * @date 2022-01-24 14:07:54
 */
class CryptBase
{
	/**
	 * 默认使用的驱动
	 * @var string
	 */
	public $default_driver = 'Openssl';

	/**
	 * 驱动配置
	 * @var array
	 */
	public $valid_drivers = [
		'openssl' => OpensslHandler::class,
        'rsa2' => Rsa2Handler::class
	];
}