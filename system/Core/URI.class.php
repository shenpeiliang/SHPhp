<?php
namespace Core;
/**
 * URI处理
 * Class URI
 */
class URI
{
	/**
	 * 请求url地址
	 * @var string
	 */
	private $uri_string = '';

	/**
	 * 查询字符串
	 * @var string
	 */
	private $query_string = '';

	/**
	 * 解析请求url
	 */
	public function parse_request_uri()
	{
		//解析请求url
		//是否是命令行模式
		if(IS_CLI)
			$uri = $this->_detect_cli_args();
		else
			$uri = $this->_detect_uri();

		//设置请求url
		$this->_set_uri_string($uri);
	}

	/**
	 * 解析命令行参数
	 * @return string
	 */
	private function _detect_cli_args(): string
	{
		//执行脚本名之外的参数
		$args = array_slice($_SERVER['argv'], 1);

		return $args ? implode('/', $args) : '';
	}

	/**
	 * 解析请求url
	 * @return string
	 */
	private function _detect_uri(): string
	{
		//检查是否带有 脚本路径（/index.php）或指定要访问的页面(/index.php/home/index?keyword=hello)
		if (!isset($_SERVER['SCRIPT_NAME']) || !isset($_SERVER['REQUEST_URI']))
			return '';

		$uri = $_SERVER['REQUEST_URI'];

		//去掉脚本路径
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));

		//检查是否开头是以?/开始的
		//字符串二进制比较前两位
		if (strncmp($uri, '?/', 2) === 0)
			$uri = substr($uri, 2);

		//以?分割url地址和查询字符串两部分
		$parts = preg_split('#\?#i', $uri, 2);

		$uri = $parts[0];

		//是否有查询字符串
		if (isset($parts[1]))
			$this->_set_query_string($parts[1]);

		if ($uri == '/' || empty($uri))
			return '';

		//解析url获取合法的path地址
		$uri = parse_url($uri, PHP_URL_PATH);

		//替换非法的符合并返回前后没有/符合的字符串
		return str_replace(['//', '../'], '/', trim($uri, '/'));
	}

	/**
	 * 获取请求url分段集
	 * @return array
	 */
	public function get_uri_segments(): array{
		if(!$this->uri_string)
			return [];

		return explode('/', $this->uri_string);
	}

	/**
	 * 设置请求url
	 * @param string $uri
	 */
	private function _set_uri_string(string $uri)
	{
		$this->uri_string = $uri;
	}

	/**
	 * 获取请求url
	 * @return string
	 */
	public function get_uri_string(): string
	{
		return $this->uri_string;
	}

	/**
	 * 获取查询字符串
	 * @return string
	 */
	public function get_query_string(): string
	{
		return $this->query_string;
	}

	/**
	 * 设置查询字符串
	 * @param string $uri
	 */
	private function _set_query_string(string $query_string)
	{
		$this->query_string = $query_string;
	}
}