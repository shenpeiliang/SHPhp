<?php
/**
 * 统一接口
 */

namespace Core\Cache;


interface CacheInterface
{
	/**
	 * 初始化
	 * @return mixed
	 */
	public function init();

	/**
	 * 是否存在KEY
	 * @param string $key
	 * @return mixed
	 */
	public function exists(string $key);
	/**
	 * 获取
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * 设置KEY中的指定值
	 * @param string $key
	 * @param string $hashKey
	 * @param $value
	 * @return mixed
	 */
	public function set(string $key, string $hashKey, $value);

	/**
	 * 保存
	 * @param string $key
	 * @param $value
	 * @param int $ttl
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60);

	/**
	 * 删除
	 * @param string $key
	 * @return mixed
	 */
	public function delete(string $key);

	/**
	 * 自增
	 * @param string $key
	 * @param int $offset
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1);

	/**
	 * 自减
	 * @param string $key
	 * @param int $offset
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1);

	/**
	 * 清空所有
	 * @return mixed
	 */
	public function clean();

	/**
	 * 获取匹配的KEY
	 * @return mixed
	 */
	public function keys(string $pattern);
}