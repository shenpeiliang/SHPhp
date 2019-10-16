<?php
namespace Controller;
use Core\Controller;
class Index extends Controller{

	public function index(){
		$this->assign('name', 'hello');

		$this->display();
	}
}