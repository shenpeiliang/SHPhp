<?php
namespace Controller;
use Core\Controller;

class Index extends Controller{

	public function index(){
        $this->assign('now', time());
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

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