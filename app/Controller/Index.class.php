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
}