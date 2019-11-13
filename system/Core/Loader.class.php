<?php
/**
 * PSR-4规范
 * \<顶级命名空间>(\<子命名空间>)*\<类名>
 * PSR-4 规范中必须要有一个顶级命名空间，它的意义在于表示某一个特殊的目录（文件基目录）。子命名空间代表的是类文件相对于文件基目录的这一段
 * 路径（相对路径），类名则与文件名保持一致（注意大小写的区别）
 */
namespace Core;

class Loader
{
	/**
	 * 路径映射
	 * @var array
	 */
	private static $psr4 = [];

	/**
	 * 初始化配置
	 * @throws AutoLoadException
	 * @throws Exception
	 */
	private static function _init()
	{
        $config = include(SYSTEM_PATH . 'Config/Convention.php');

        if (!isset($config['loader']['psr4']))
            throw new \Exception('缺少配置loader');

        self::$psr4['map'] = $config['loader']['psr4']['map'];
		self::$psr4['file_suffixes'] = $config['loader']['psr4']['file_suffixes'];
	}

	/**
	 * 自动加载处理
	 * @param String $class
	 */
	public static function autoload(String $class)
	{
		//初始化配置
		self::_init();

		//解析并加载文件
		self::_include_file(self::_parse_file($class));
	}

	/**
	 * 解析文件路径
	 * @param String $class
	 * @return String
	 */
	private static function _parse_file(String $class): String
	{
		//顶级命名空间
		$vendor = substr($class, 0, strpos($class, '\\'));

		//文件相对路径
		$file_path = substr($class, strlen($vendor)) . EXT;

		//文件基目录 默认是基于APP目录地址
		$vendor_dir = APP_PATH . $vendor;

		//配置规则存在则覆盖
		if (in_array($vendor, self::$psr4['map'])){
			$vendor_dir = SYSTEM_PATH . $vendor;
			//文件相对路径
			$file_path = substr($class, strlen($vendor)) . self::$psr4['file_suffixes'];
		}

		// 文件绝对路径
		return strtr($vendor_dir . $file_path, '\\', DIRECTORY_SEPARATOR);
	}

	/**
	 * 包含文件
	 * @param String $file
	 */
	private static function _include_file(String $file)
	{
		if (!is_file($file))
            throw new \Exception('文件不存在');

		require_once $file;
	}
}