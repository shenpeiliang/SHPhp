<?php

namespace Controller;

use Core\Controller;

/**
 * 接口验证操作类 用于第三方接口调用
 * 必须传递的字段信息：'data','timestamp','source','signature'
 * 进行加密的字段信息：'data', 'timestamp', 'source','authkey'
 * 加密规则：加密字段先按字典排序，然后再进行sha1加密
 * @author shenpeiliang
 * @date 2022-01-18 16:41:31
 */
class Api extends Controller
{
    /**
     * 接口超时限制-发送时间和当前时间进行对比 默认5分钟
     */
    const  TIME_EXCEED = 300;

    /**
     * 来源 个别方法可能只允许某来源访问
     * @var unknown
     */
    protected $_source;

    /**
     * 解析json后的数据
     * @var array
     */
    protected $_data;

    /**
     * 配置
     * @var unknown
     */
    protected $_config = [];

    public function __construct()
    {
        //配置
        $this->_config = config('common.api');

        //认证
        $this->auth();
    }

    /**
     * 认证
     */
    protected function auth()
    {
        //ip地址
        $ip = get_client_ip();

        //ip白名单
        foreach ($this->_config['allowed_ips'] as $pattern) {
            if (!fnmatch($pattern, $ip))
                $this->_fail('IP_NOT_ALLOWED', '访问不允许');
        }

        //必须字段
        $required = ['data', 'timestamp', 'source', 'signature'];
        foreach ($required as $item) {
            if (!$this->request->post($item, false))
                $this->_fail(strtoupper($item) . '_INVALID', '缺少必须字段');
        }
        //请求数据
        $data = $this->request->post('data');//json数据
        $timestamp = $this->request->post('timestamp', 0, 'intval');//时间戳
        $this->_source = $source = $this->request->post('source', 0, 'intval');//来源
        $signature = $this->request->post('signature');//签名

        //检查来源
        if (!in_array($source, $this->_config['source']))
            $this->_fail('SOURCE_INVALID', '来源不允许');

        // 检查时间戳
        if (abs($timestamp - time()) > self::TIME_EXCEED)
            $this->_fail('TIME_EXPIRED', '请求时间超时');

        // 检查签名
        $authkey = $this->_config['authkey'];

        //需要签名加密的字段值
        $args = compact('data', 'timestamp', 'source', 'authkey');

        ksort($args);  //按数组的键排序
        $sign = ''; //需要签名加密组合的字符串
        foreach ($args as $key => $val) {
            $sign .= $key . '=' . $val;
        }
        //sha1加密
        $sign = sha1($sign);

        //验证是否通过
        if ($sign !== $signature)
            $this->_fail('SIGN_MISMATCH', '签名不匹配');

        // 解码请求数据
        $this->_data = json_decode($data, true);

        //json_decode出错时返回null或false
        if (!$this->_data)
            $this->_fail('DATA_INVALID', '数据不合法');
    }
}