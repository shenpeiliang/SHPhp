<?php
namespace Controller;
use Core\Controller;

class Index extends Controller{

	public function index(){
        $this->assign('now', time());
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

		/*
		 $data = [
			'name' => 'hello'
		];
		//打印调试
		debug_dump($this);

		//安全过滤
		echo remove_xss("sdfdsf<script type='text/javascript'>alert('哦哦');</script>");

		*/


		$this->display();
	}

	public function smarty(){
		$this->assign('now', time());
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

		$this->display();
	}

	public function table(){
	    $mod = new \Model\DemoModel();

	    var_dump($mod->get());
    }
}