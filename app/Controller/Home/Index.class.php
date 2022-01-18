<?php
namespace Controller\Home;
use Core\Controller;

/**
 * home模块默认
 * @author shenpeiliang
 * @date 2022-01-17 11:39:36
 */
class Index extends Controller{
	/**
	 * 默认方法
	 */
	public function index(){
		echo "hello from home";
	}

	/**
	 * 带参数
	 * @param $str
	 */
	public function hello($str)
	{
		$this->assign('now', time());
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

		$this->display();
	}
}