<?php
namespace Core;
/**
 * 控制器
 * Class Controller
 * @package Core
 */
class Controller
{
	protected $view = NULL;

	function __construct()
	{
		//模板对象
		$this->view = new \Core\View();
	}

	/**
	 * 模板变量
	 * @param $name
	 * @param $value
	 * @return $this
	 */
	public function assign($name, $value)
	{
		$this->view->assign($name, $value);
		return $this;
	}

	/**
	 * 模板内容输出
	 * @param string $template_file
	 * @param bool $is_return
	 * @return string
	 */
	public function display(string $template_file, $is_return = FALSE)
	{
		//直接返回结果，不输出
		if($is_return)
			return $this->view->display($template_file, $is_return);

		$this->view->display($template_file, $is_return);
	}

}