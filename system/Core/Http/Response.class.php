<?php
namespace Core\Http;
/**
 * 响应处理
 * shenpeiliang
 */
class Response
{
	/**
	 * Json输出到浏览器
	 * @param $data
	 * @param int $option
	 */
	public function json($data, int $option = JSON_UNESCAPED_UNICODE): void
	{
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($data, $option));
	}

	/**
	 * Json输出到浏览器
	 * 如客户想访问 : https://www.runoob.com/try/ajax/jsonp.php?jsoncallback=callbackFunction。
	 * 假设客户期望返回数据：["customername1","customername2"]。
	 * 真正返回到客户端的数据显示为: callbackFunction(["customername1","customername2"])。
	 * @param $data
	 * @param int $option
	 */
	public function jsonp($data, int $option = JSON_UNESCAPED_UNICODE): void
	{
		header('Content-Type:application/json; charset=utf-8');

		//jsonp处理函数名
		$json_handler = convention_config('jsonp_handler');

		$handler = isset($_GET[$json_handler]) ? $_GET[$json_handler] : $json_handler;

		exit($handler . '(' . json_encode($data, $option) . ');');
	}

	/**
	 * Xml输出到浏览器
	 * @param $data
	 */
	public function xml($data): void
	{
		header('Content-Type:text/xml; charset=utf-8');
		exit(xml_encode($data));
	}

	/**
	 * 可执行的脚本内容输出到浏览器
	 * @param $data
	 */
	public function eval($data): void
	{
		header('Content-Type:text/html; charset=utf-8');
		exit($data);
	}

	/**
	 * 普通内容输出到浏览器
	 * @param $data
	 */
	public function echo ($data): void
	{
		exit($data);
	}
}