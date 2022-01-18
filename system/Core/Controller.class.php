<?php
namespace Core;
use Core\Http\Request;
use Core\Http\Response;

/**
 * 控制器
 * Class Controller
 * @package Core
 */
class Controller
{
	/**
	 * 视图对象
	 * @var View|null
	 */
	public $view = NULL;

	/**
	 * 响应处理对象
	 * @var View|null
	 */
	public $response = NULL;

	/**
	 * 请求处理对象
	 * @var View|null
	 */
	public $request = NULL;

	function __construct()
	{
		//模板对象
		$this->view = new View();

		$this->response = new Response();

		$this->request = new Request();
	}

    /**
     * 模板变量
     * @param string $name
     * @param $value
     * @return $this
     */
	protected function assign(string $name, $value = '')
	{
		$this->view->assign($name, $value);
		return $this;
	}

	/**
	 * 模板内容输出
	 * @param string $template_file
	 * @param array $data 模板变量
	 * @param bool $is_return
	 * @return mixed|string
	 * @throws \Exception\FileException
	 */
	protected function display(string $template_file = '', array $data = [], bool $is_return = FALSE)
	{
		//模板变量
		if($data)
			$this->assign($data);

		//直接返回结果，不输出
		if ($is_return)
			return $this->view->display($template_file, $is_return);

		$this->view->display($template_file, $is_return);
	}

    /**
     * 成功响应
     * @param array $data
     * @param string $msg
     */
    protected function _ok($data = [], $msg = '')
    {
        $this->_json_result('ok', $msg, $data);
    }

    /**
     * 失败响应
     * @param string $code
     * @param string $msg
     */
    protected function _fail($code = '', $msg = '')
    {
        if (!$code)
            $code = 'unknown_error';

        if (!$msg)
            $msg = '未知错误';

        $this->_json_result($code, $msg);
    }

    /**
     * JSON格式化数据结果
     * @param $code
     * @param string $msg
     * @param array $data
     * @return false|string
     */
    protected function _json_result($code, $msg = '', $data = [])
    {
        $this->response->json(compact('code', 'msg', 'data'));
    }


}