<?php
namespace Core\Http;
/**
 * Class Curl
 * shenpeiliang
 * @package Core\Http
 */

class Curl
{
	//配置项
	private $options;

	function __construct(array $options = [])
	{
		//默认配置初始化
		$this->options = convention_config('curl_option');

		//配置覆盖
		if($options)
			$this->set_options($options);
	}

	/**
	 * 设置配置项
	 * @param array $options
	 */
	public function set_options(array $options = []): void
	{
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * get请求
	 * @param string $url
	 * @param string $path_cookie cookie文件路径
	 */
	public function get(string $url, string $path_cookie = '')
	{
		if($path_cookie){
			$this->set_options([
				'CURLOPT_COOKIEJAR' => $path_cookie, //close后保存cookie的文件
				'CURLOPT_COOKIEFILE' => $path_cookie //读取cookie的文件
			]);
		}

		return $this->send($url);
	}

	/**
	 * post请求
	 * @param string $url
	 * @param array $data
	 * @param string $path_cookie
	 * @return mixed
	 */
	public function post(string $url, array $data = [], string $path_cookie = '')
	{
		if($path_cookie){
			$this->set_options([
				'CURLOPT_COOKIEJAR' => $path_cookie, //close后保存cookie的文件
				'CURLOPT_COOKIEFILE' => $path_cookie //读取cookie的文件
			]);
		}

		$this->set_options([
			'CURLOPT_POST' => TRUE,
			'CURLOPT_POSTFIELDS' => http_build_query($data)
		]);

		return $this->send($url);
	}

	/**
	 * 发送请求
	 * @param string $url
	 * @return mixed
	 */
	public function send(string $url)
	{
		//初始化 cURL 会话
		$ch = curl_init();

		//请求地址
		$this->set_options([
			'CURLOPT_URL' => $url
		]);

		//为 cURL 传输会话批量设置选项
		curl_setopt_array($ch, $this->options);

		//执行cURL会话
		$output = curl_exec($ch);

		if ($output === false)
		{
			throw \Exception\HttpException::for_curl_rrror(curl_errno($ch), curl_error($ch));
		}

		//关闭 cURL 会话
		curl_close($ch);

		return $output;
	}
}