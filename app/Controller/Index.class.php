<?php
namespace Controller;
use Core\Controller;
use Core\Crypt\Driver\AesHandler;
use Core\Crypt\Driver\Rsa2Handler;
use Core\File\VersionFactory;
use Library\SecurityCard;

/**
 * 默认首页
 * @author shenpeiliang
 * @date 2022-01-17 11:39:21
 */
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

    /**
     * 加解密测试
     */
    public function crypt(){
        $str = "hello world !!";

	    $aes = new AesHandler();
	    $encrypt_str = $aes->encrypt($str);

	    $decrypt_str = $aes->decrypt($encrypt_str);

	    var_dump($encrypt_str, $decrypt_str);

	    echo "<br/>";
	    $rsa = new Rsa2Handler();
        $encrypt_str = $rsa->encrypt($str);
        $decrypt_str = $rsa->decrypt($encrypt_str);
        var_dump($encrypt_str, $decrypt_str);

        echo "<br/>";
        $rsa = new Rsa2Handler();
        $encrypt_str = $rsa->create_sign($str);
        $decrypt_str = $rsa->verify_sign($str, $encrypt_str);
        var_dump($encrypt_str, $decrypt_str);
    }

    //密保卡
    public function card(){
        $card = new SecurityCard();
        $keys = $card->create();

        $info = implode('', $card->entry());

        $input = '232301';
        $flag = $card->check($keys, $info, $input);

        var_dump($keys, $info, $flag);
    }

    //文件版本号获取
    public function version(){
        $v = new VersionFactory();
        $driver = $v->create('Sha1');
        $output = $driver->set_root_path(SRC_PATH)->get_file_version('app/Controller/Index.class.php');
        if(!$output)
            echo $driver->error;
        echo $output;
    }
}