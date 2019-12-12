<?php
namespace Controller;
use Core\Controller;

class Index extends Controller{

	public function index(){
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

		//表单获取
		$this->request->post('name', '', 'htmlspecialchars');
		$this->request->post('remark', '', 'trim,remove_xss');

		//ajax返回
		$data = ['success' => 'ok', 'msg' => 'NO problem'];
		$this->response->json($data);

		//打印调试 使用第三方扩展symfony/var-dumper
		debug_dump($data);

		//模板赋值
		$this->assign('now', time());

		//模板输出
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