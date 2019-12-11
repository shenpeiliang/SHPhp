<?php
/**
 * shenpeiliang
 */

namespace Core;


class Cookie
{
	/**
	 * 设置值
	 * @param string|array $key
	 * @param string $value
	 */
	public static function set($key = '', $value = '')
	{
		$config = convention_config('cookie');

		if ($config['httponly'])
			ini_set("session.cookie_httponly", TRUE);

		$expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;

		if (is_string($key))
			$data[$key] = $value;
		else
			$data = $key;

		foreach ($data as $k => $v)
		{
			setcookie($k, $v, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
			$_COOKIE[$k] = $v;
		}

	}

	/**
	 * 获取值
	 * @param string $key
	 */
	public static function get(string $key = '')
	{
		if ('' == $key)
		{
			return $_COOKIE;
		} else
		{
			if (!isset($_COOKIE[$key]))
				return null;
		}

		return $_COOKIE[$key];
	}

	/**
	 * 删除指定值
	 * @param string $key
	 */
	public static function delete(string $key = '')
	{
		if (empty($_COOKIE) || isset($_COOKIE[$key]))
			return;

		$config = convention_config('cookie');

		if ($config['httponly'])
			ini_set("session.cookie_httponly", TRUE);

		setcookie($key, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
		unset($_COOKIE[$key]);
	}
}