<?php

namespace Core\Crypt;
use Core\Service\BaseFactoryInterface;
/**
 * 加减密工厂类
 * @author shenpeiliang
 * @date 2022-01-24 10:38:34
 */
class CryptFactory implements BaseFactoryInterface
{
    /**
     * 创建驱动
     * @return CryptInterface
     */
	public function create(): CryptInterface
    {
		//基本配置
		$base = new CryptBase();

		//使用默认配置
		$driver_object = $base->valid_drivers[$base->default_driver];

		//配置文件中是否有配置
		$driver = convention_config('crypt_driver');
		if($driver && array_key_exists($driver, $base->valid_drivers))
			$driver_object = $base->valid_drivers[$driver];

		return new $driver_object();
	}
}