<?php
namespace Controller\Home;
class Index{
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
		echo $str;
	}
}