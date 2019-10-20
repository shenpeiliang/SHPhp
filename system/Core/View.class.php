<?php
namespace Core;
/**
 * 视图处理
 * Class View
 */
class View
{
	/**
	 * 模板输出变量
	 * @var array
	 */
	protected $var = [];

	function __construct()
	{
	}

	/**
	 * 模板变量赋值
	 * @param $name
	 * @param $value
	 */
	public function assign($name, $value)
	{
		if (is_array($name))
			$this->var = array_merge($this->var, $name);
		else
			$this->var[$name] = $value;

		return $this;
	}

	/**
	 * 模板内容输出
	 * @param string $template_file
	 * @param bool $is_return
	 * @return string
	 */
	public function display(string $template_file, bool $is_return = FALSE)
	{
		//模板绝对路径
		$absolute_path = $this->get_template_path($template_file);
		if (!is_file($absolute_path))
			throw \Core\Exceptions\FileException::for_not_found($absolute_path);

		//解析模板标签
		$template = new \Core\Template\Driver\FrameTemp($this->var);
		$content = $template->fetch($absolute_path);

		//直接返回结果，不输出
		if ($is_return)
			return $content;

		//网页字符编码
		header('Content-Type:text/html; charset=' . config('common.default_charset'));
		//页面缓存控制
		header('Cache-control: ' . config('common.http_cache_control'));
		echo $content;
	}

	/**
	 * 获取模板文件路径
	 * 如果不指定模板名，则默认是以模块名的文件夹下的控制器和方法组合的文件
	 * @param string $template_file
	 * @return string
	 */
	private function get_template_path(string $template_file): string
	{
		//模板路径
		$dir_view = APP_PATH . 'View/' . ucfirst(config('common.template_theme'));

		if ($template_file)
			return $dir_view . '/' . $template_file . config('common.template_suffix');

		//模块对应文件夹  控制名+方法名
		$route = new \Core\Route();
		$uri_segments = $route->get_uri_segments();

		//非模块参数
		$param_not_module = [];

		foreach ($uri_segments as $item)
		{
			if (is_dir($dir_view . '/' . ucfirst($item)))
				$dir_view .= '/' . ucfirst($item);
			else
				$param_not_module[] = $item;
		}

		return $dir_view . '/' . ucfirst(implode(config('common.template_delimiter'), $param_not_module)) . config('common.template_suffix');
	}
}