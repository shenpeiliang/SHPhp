<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/18
 * Time: 16:03
 */

namespace Core;


class Session
{
	public function __construct()
	{
		//初始化
		$this->_init();
	}

	/**
	 * 初始化
	 */
	private function _init(): void
	{
		$config = convention_config('session');

		//配置
		if (isset($config['name'])) session_name($config['name']);
		if (isset($config['path'])) session_save_path($config['path']);
		if (isset($config['domain'])) ini_set('session.cookie_domain', $config['domain']);
		if (isset($config['expire']))
		{
			ini_set('session.gc_maxlifetime', $config['expire']);
			ini_set('session.cookie_lifetime', $config['expire']);
		}

		//是否使用了其他的session驱动
		if ($config['driver'])
		{
			//自定义session处理
			$session = new \Core\Session\SessionFactory();
			session_set_save_handler($session->create());
		}

		//是否自动开启
		if ($config['auto_start'])
			session_start();
	}

	/**
	 * 设置session值
	 * @param string $name
	 * @param $value
	 */
	public static function set(string $name, $value = ''): void
	{
		if (is_string($name))
		{
			$_SESSION[$name] = $value;
		} else
		{
			foreach ($name as $key => $val)
			{
				self::set($key, $val);
			}
		}

	}

	/**
	 * 清除session值
	 * @param string $name
	 */
	public static function clear(string $name = ''): void
	{
		//清除指定session值
		if ($name)
			unset($_SESSION[$name]);

		//删除所有session
		session_unset();
		session_destroy();
	}

	/**
	 * 获取session值
	 * @param string $name
	 * @return mixed
	 */
	public static function get(string $name = '')
	{
		//获取指定session值
		if ($name)
			return $_SESSION[$name];

		return $_SESSION;
	}
}