<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:38
 */

namespace Core\Cache;
use Core\Service\BaseFactoryInterface;

class CacheFactory implements BaseFactoryInterface
{
	/**
	 * 创建驱动
	 * @return CacheInterface
	 */
	public function create(): CacheInterface
    {
		//基本配置
		$base = new CacheBase();

		//使用默认配置
		$driver_object = $base->valid_drivers[$base->default_driver];

		//配置文件中是否有配置
		$driver = convention_config('cache_driver');
		if($driver && array_key_exists($driver, $base->valid_drivers))
			$driver_object = $base->valid_drivers[$driver];

		return new $driver_object();
	}
}