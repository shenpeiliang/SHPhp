<?php

namespace Core;
/**
 * 打印curl命令日志
 * @author shenpeiliang
 * @date 2022-01-19 10:39:47
 */
class Log
{
    /**
     * 生成curl命令
     * @param string $url
     * @param array $headers
     * @param array $data
     * @param string $method
     * @return string
     */
    public function build_curl_cmd(string $url, array $headers = [], array $data = [], string $method = 'GET'): string
    {
        $curl = $method == 'GET' ? 'curl　-G' : 'curl';

        //请求头信息
        $headers = $this->_build_curl_header($headers);

        //ssl证书验证
        $url = $this->_build_curl_ssl($url);

        //cookie
        $cookies = $this->_build_curl_cookie();

        //数据参数
        $data = '-d ' . $this->_build_curl_query($data);

        return "$curl $headers $cookies $data $url";
    }

    /**
     * 构造cookie
     * @return string
     */
    private function _build_curl_cookie(): string
    {
        $str = " -b '";
        foreach ($_COOKIE as $k => $v) {
            $str .= "$k=$v;";
        }
        return rtrim($str, ';') . "'";
    }

    /**
     * 构建ssl验证
     * @param string $url
     * @return string
     */
    private function _build_curl_ssl(string $url): string
    {
        $url_scheme = parse_url($url, PHP_URL_SCHEME);
        if ($url_scheme && $url_scheme == 'https')
            return ' -k ' . $url;

        return $url;
    }

    /**
     * 构建query string
     * @param array $data
     * @return string
     */
    private function _build_curl_query(array $data = []): string
    {
        //会经过URL-encode转义，通常需要保留原始数据
        //return http_build_query($data);

        $str = "'";
        foreach ($data as $k => $v) {
            $str .= "$k=$v&";
        }
        return rtrim($str, '&') . "'";
    }

    /**
     * 构建header
     *
     * @param array $data
     * @return string
     */
    private function _build_curl_header(array $data = []): string
    {
        $str = "";
        foreach ($data as $k => $v) {
            $str .= "-H '$k:$v' ";
        }
        return $str;
    }
}