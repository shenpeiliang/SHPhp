<?php
namespace Core\Http;
/**
 * 请求处理
 * @author shenpeiliang
 * @date 2022-01-17 15:49:00
 */
class Request
{
	/**
	 * 安全过滤
	 * @param $input
	 * @param array $filter_function
	 * @return array|mixed
	 */
	private function _filter_data($input, array $filter_function = [])
	{
		$data = $input;

		//过滤方法 框架默认的过滤方法
		$filters = [
            'frame_filter',
            'trim',
        ];

		//合并用户指定需要过滤的安全方法
		if ($filter_function)
			$filters = array_merge($filters, $filter_function);

		if ($data)
		{
			if (is_array($data))
			{
				foreach ($filters as $filter)
				{
					//递归数组函数
					$data = array_map_recursive($filter, $data); // 参数过滤
				}
			} else
			{
				foreach ($filters as $filter)
				{
					//递归数组函数
					$data = call_user_func($filter, $data); // 参数过滤
				}
			}

		}

		return $data;
	}

	/**
	 * 获取请求数据
	 * @param array $input
	 * @param string $name
	 * @param string $default_value
	 * @param string $filter_function
	 * @return array|mixed|string
	 */
	private function _get_request_data(array $input, string $name = '', $default_value = '', $filter_function = '')
	{
		//过滤方法
        if ($filter_function && is_string($filter_function))
            $filter_function = explode(',', $filter_function);
        else
            $filter_function = [];

		if ('' == $name)
		{//获取全局
			//安全过滤
			$data = $this->_filter_data($input, $filter_function);
		} else
		{ //获取指定值

			//没有指定的值则返回默认值
			if (!isset($input[$name]))
				return $default_value;

			//安全过滤
			$data = $this->_filter_data($input[$name], $filter_function);
		}

		return $data;
	}

	/**
	 * GET数据
	 * @param string $name 指定的值
	 * @param string $default_value 默认值
	 * @param string $filter_function 过滤方法 英文逗号隔开的字符串或数组 如：htmlspecialchars,trim intval
	 */
	public function get(string $name = '', $default_value = '', $filter_function = '')
	{
		$input = $_GET;

		return $this->_get_request_data($input, $name, $default_value, $filter_function);
	}

	/**
	 * POST数据
	 * @param string $name 指定的值
	 * @param string $default_value 默认值
	 * @param string $filter_function 过滤方法 英文逗号隔开的字符串或数组 如：htmlspecialchars,trim intval
	 */
	public function post(string $name = '', $default_value = '', $filter_function = '')
	{
		$input = $_POST;

		return $this->_get_request_data($input, $name, $default_value, $filter_function);
	}

	/**
	 * COOKIE数据
	 * @param string $name 指定的值
	 * @param string $default_value 默认值
	 * @param string $filter_function 过滤方法 英文逗号隔开的字符串或数组 如：htmlspecialchars,trim intval
	 */
	public function cookie(string $name = '', $default_value = '', $filter_function = '')
	{
		$input = $_COOKIE;

		return $this->_get_request_data($input, $name, $default_value, $filter_function);
	}

	/**
	 * POST/GET/COOKIE数据
	 * @param string $name 指定的值
	 * @param string $default_value 默认值
	 * @param string $filter_function 过滤方法 英文逗号隔开的字符串或数组 如：htmlspecialchars,trim intval
	 */
	public function request(string $name = '', $default_value = '', $filter_function = '')
	{
		$input = $_REQUEST;

		return $this->_get_request_data($input, $name, $default_value, $filter_function);
	}

	/**
	 * 跳转提示
	 * @param string $url
	 * @param int $time
	 * @param string $msg
	 */
	public function redirect(string $url, int $time = 0, string $msg = ''): void
	{
		//替换换行符
		$url = str_replace(["\n", "\r"], '', $url);

		//默认提示
		if (empty($msg))
			$msg = "系统将在{$time}秒之后自动跳转到{$url}！";

		//检查HTTP头是否已经发送
		if (!headers_sent())
		{
			if (0 === $time) //直接跳转，不停留
			{
				header('Location: ' . $url);
			} else
			{
				header("refresh:{$time};url={$url}");
				echo($msg);
			}
			exit();
		} else
		{
			$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if ($time != 0)
				$str .= $msg;
			exit($str);
		}
	}

	/**
	 * 获取客户IP
	 * ipv6参考https://github.com/Aldin-SXR/ip-format-tools
	 * @param bool $is_return_int 是否返回整数，默认是，否则返回字符串
	 */
	public function get_client_ip(bool $is_return_int = TRUE)
	{
		//浏览当前页面的用户计算机的网关 高级代理clientip,proxy1,proxy2  所有IP用”,”分割
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos = array_search('unknown', $arr);
			if (false !== $pos) unset($arr[$pos]);
			$ip = trim($arr[0]);
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) //客户端的ip
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) //浏览当前页面的用户计算机的ip地址
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		//整数返回
		if ($is_return_int)
		{
			//转无符号10进制整数
			$long = sprintf("%u", ip2long($ip));
			return $long ?? 0;
		}

		return $ip;
	}
}