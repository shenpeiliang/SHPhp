<?php
namespace Core;

/**
 * PSR-4规范
 * PSR-4 规范中必须要有一个顶级命名空间，它的意义在于表示某一个特殊的目录（文件基目录）
 * 子命名空间代表的是类文件相对于文件基目录的这一段路径（相对路径）
 * 类名则与文件名保持一致（注意大小写的区别）
 */

class Route
{
	/**
	 * 目录地址
	 * @var string
	 */
	private $directory = '';

	/**
	 * 类名称（包含命名空间）
	 * @var string
	 */
	private $class = '';

	/**
	 * 模块
	 * @var array
	 */
	private $module = [];

	/**
	 * 方法
	 * @var string
	 */
	private $method = '';

	/**
	 * 方法参数
	 * @var array
	 */
	private $method_param = [];

	/**
	 * URI对象
	 * @var null
	 */
	private $URI = NULL;

	public function __construct()
	{
		$this->URI = new \Core\URI();

		//解析请求url
		$this->URI->parse_request_uri();

		//解析路由
		$this->_parse_routes();
	}

	/**
	 * 设置默认路由
	 */
	private function _set_default_route()
	{
		$config = include(SYSTEM_PATH . 'Config/Convention.php');

		if (!isset($config['ROUTE']))
			throw \Core\Exceptions\AutoLoadException::for_invalid_param();

		$this->directory = APP_PATH . 'Controller';
		$this->class = '\\' . 'Controller' . '\\' . ucfirst($config['ROUTE']['CONTROLLER']);
		$this->method = strtolower($config['ROUTE']['METHOD']);
	}

	/**
	 * 解析路由
	 * @return string
	 */
	private function _parse_routes()
	{
		//获取请求url分段集
		$uri_segments = $this->URI->get_uri_segments();

		//默认配置
		$this->_set_default_route();

		if ($uri_segments)
		{
			$params = [];
			foreach ($uri_segments as $key => $item)
			{
				//APP路径内文件名首字母大写
				$item_module = ucfirst($item);

				//是否是目录
				if (is_dir($this->directory . DIRECTORY_SEPARATOR . $item_module))
				{
					$this->directory = $this->directory . DIRECTORY_SEPARATOR . $item_module;

					//模块
					array_push($this->module, $item_module);
				} else
				{
					$params[] = $item;
				}
			}

			//设置类名
			$this->_set_class($params);
		}

	}

	/**
	 * 设置class信息
	 * @param $params
	 */
	private function _set_class(array $params)
	{
		//如果请求中没有控制器段，则默认控制器名，如: Index
		$class_name = substr($this->class, strpos($this->class, '\\', 1) + 1);

		if ($params)
			$class_name = ucfirst($params[0]); //首字母大写

		//携带命名空间
		if ($this->module)
			$this->class = '\\' . 'Controller' . '\\' . implode('\\', $this->module) . '\\' . $class_name;
		else
			$this->class = '\\' . 'Controller' . '\\' . $class_name;

		//剩余参数为方法名和方法参数
		if (count($params) > 1)
			$params = array_slice($params, 1);

		//设置方法
		$this->_set_method($params);
	}

	/**
	 * 设置method
	 * @param $params
	 */
	private function _set_method(array $params)
	{
		if (!$params)
			return;

		//方法名为全部小写
		$this->method = strtolower($params[0]);

		//剩余参数为方法参数
		if (count($params) > 1)
			$params = array_slice($params, 1);

		//设置方法参数
		$this->_set_method_param($params);
	}

	/**
	 * 设置method参数
	 * @param $params
	 */
	private function _set_method_param(array $params)
	{
		if (!$params)
			return;

		$this->method_param = $params;
	}

	/**
	 * 获取模块分组
	 * @return array
	 */
	public function get_module(): array
	{
		return $this->module;
	}

	/**
	 * 获取方法名
	 * @return string
	 */
	public function get_method(): string
	{
		return $this->method;
	}

	/**
	 * 获取方法参数
	 * @return string
	 */
	public function get_method_param(): array
	{
		return $this->method_param;
	}

	/**
	 * 获取类名(包含命名空间)
	 * @return string
	 */
	public function get_class(): string
	{
		return $this->class;
	}
}