<?php
namespace Core;
/**
 * 配置 - 单例应用
 * Class Config
 * @package Core
 */
class Config
{
	/**
	 * 实例
	 * @var
	 */
	private static $instance = NULL;

	/**
	 * 配置文件
	 * @var array
	 */
	private static $config = [];

	private function __construct()
	{
	}

	/**
	 * 获取单例
	 * @return Config
	 */
	public static function get_instance(): self
	{
		if (!self::$instance)
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * 获取配置参数
	 * @param string $key 参数名 格式：文件名.参数名1.参数2...
	 * @param null $default 默认值
	 * @return null
	 */
	public function get(string $keys, $default = NULL)
	{
		//拆分数组 保留非false值 配置项全部为小写
		$keys = array_filter(explode('.', strtolower($keys)));
		if (empty($keys))
			return false;

		//第一个参数表示文件名 首字母大写
		$file = ucfirst(array_shift($keys));

		if (empty(self::$config[$file]))
		{
			//配置文件绝对路径
			$absolute_path = APP_PATH . 'Conf/' . (defined('ENVIRONMENT') ? ENVIRONMENT . '/' : '') . $file . '.php';
			if (!is_file($absolute_path))
				throw \Core\Exceptions\FileException::for_not_found();

			//包含文件
			require($absolute_path);

			if(!isset($config) || !is_array($config))
				throw  \Core\Exceptions\FileException::for_error_param();

			//配置文件的数组名为config
			self::$config[$file] = $config;

		}

		$config = self::$config[$file];

		while ($keys)
		{
			$key = array_shift($keys);

			//如果不存在，直接返回默认值，不再递归
			if (!isset($config[$key]))
			{
				$config = $default;
				break;
			}

			$config = $config[$key];
		}

		return $config;
	}
}