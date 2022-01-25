<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 10:27
 */

namespace Core\Cache\Driver;

use Core\Cache\CacheInterface;
use Exception\CacheException;


class RedisHandler implements CacheInterface
{
	//连接对象
	private $handler = NULL;

	public function __construct()
	{
		//初始化
        try {
            $this->init();
        } catch (CacheException $e) {
            printf("Redis连接错误：%s - %s", $e->getCode(), $e->getMessage());
        }
    }

	/**
	 * 获取链接对象
	 * @return null
	 */
	public function get_handler(): \Redis
	{
		return $this->handler;
	}

	/**
	 * 初始化
	 * @return mixed
	 */
	public function init(): void
	{
		try
		{
			//配置
			$config = convention_config('redis');

			//连接
			$this->handler = new \Redis();

			$this->handler->pconnect($config['host'], $config['port'], $config['timeout']);

			/*
			 * 已设置密码的需要验证
			 */
			if (!is_null($config['auth']))
				$this->handler->auth($config['auth']);

		} catch (\RedisException $e)
		{

			throw \Exception\CacheException::for_connect_error($e);

		}
	}

	/**
	 * 获取
	 * @param string $name
	 * @return mixed
	 */
	public function get(string $name)
	{
		//是否是只对某个键中的指定值
		list($key, $data) = expload('.', $name);

		if ($data)
			return $this->handler->hGet($key, $data);
		else
			return $this->handler->hGetAll($key);
	}

	/**
	 * 设置KEY中的指定值
	 * @param string $key
	 * @param string $hashKey
	 * @param $value
	 * @return bool
	 */
	public function set(string $key, string $hashKey, $value): bool
	{
		return $this->handler->hSet($key, $hashKey, $value);
	}

	/**
	 * 是否存在KEY
	 * @param string $name
	 * @return bool
	 */
	public function exists(string $name): bool
	{
		//是否是只对某个键中的指定值
		list($key, $data) = expload('.', $name);

		if ($data)
			return $this->handler->hExists($key, $data);
		else
			return $this->handler->exists($key);
	}

	/**
	 * 保存
	 * @param string $key
	 * @param $value
	 * @param int $ttl
	 * @return bool
	 */
	public function save(string $key, $value, int $ttl = 0): bool
	{
		//如果是对象类型则序列化
		if ('object' == gettype($value))
			$value = serialize($value);

		$flag = $this->handler->hMset($key, $value);

		//设置过期时间
		if ($ttl)
			$flag = $this->handler->expireAt($key, time() + $ttl);

		return $flag;
	}

	/**
	 * 删除
	 * @param string $key
	 * @return bool
	 */
	public function delete(string $key): bool
	{
		return $this->handler->delete($key);
	}

	/**
	 * 获取匹配的KEY
	 * @return mixed
	 */
	public function keys(string $pattern)
	{
		return $this->handler->delete($pattern);
	}

	/**
	 * 自增
	 * @param string $name 例如：goods_num 或 user.coin
	 * @param int $offset
	 * @return bool
	 */
	public function increment(string $name, int $offset = 1): bool
	{
		//是否是只对某个键中的指定值
		list($key, $data) = expload('.', $name);

		if ($data)
			return $this->handler->hIncrBy($key, $data, $offset);
		else
			return $this->handler->incrBy($key, $offset);
	}

	/**
	 * 自减
	 * @param string $name 例如：goods_num 或 user.coin
	 * @param int $offset
	 * @return bool
	 */
	public function decrement(string $name, int $offset = 1): bool
	{
		//是否是只对某个键中的指定值
		list($key, $data) = expload('.', $name);

		if ($data)
			return $this->handler->hIncrBy($key, $data, -$offset);
		else
			return $this->handler->incrBy($key, -$offset);
	}

	/**
	 * 清空所有
	 * @return bool
	 */
	public function clean(): bool
	{
		return $this->handler->flushDB();
	}
}