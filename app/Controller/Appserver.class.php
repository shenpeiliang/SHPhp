<?php

namespace Controller;

use Core\Controller;

/**
 * 接口基类
 * 签名
 * @author shenpeiliang
 * @date 2022-01-17 15:02:57
 */
class Appserver extends Controller
{
    /**
     * 接口超时限制-发送时间和当前时间进行对比 默认5分钟
     */
    const  TIME_EXCEED = 300;

    /**
     * @var array 用户信息
     */
    protected $_user = [];

    /**
     * 过滤器
     * @var array
     */
    protected $_filters = [];

    /**
     * @var string 客户端类型
     */
    protected $client_type = '';

    /**
     * @var string 客户端版本号
     */
    protected $client_version = '';

    /**
     * @var string 接口版本号
     */
    protected $api_version = '';

    /**
     * 移动设备唯一标识符
     * @var string
     */
    protected $uuid = '';


    /**
     * 必须字段
     * @var string[]
     */
    protected $required = [
        'client_type' => '客户端类型',
        'client_version' => '客户端版本号',
        'uuid' => '移动设备唯一标识符',
        'api_version' => '接口版本号',
        'timestamp' => '请求时间戳',
        'signature' => '签名',
    ];

    /**
     * 允许的客户端版本
     * @var string[]
     */
    protected $allow_client_version = [];

    public function __construct()
    {
        parent::__construct();
        //配置
        $this->allow_client_version = config('common.allow_client_version');

        // 参数验证
        $this->_check();

        // 执行过滤器
        foreach ($this->_filters as $filter) {
            $method = '_filter_' . $filter;
            $this->$method();
        }
    }

    /**
     * 过滤登录
     */
    protected function _filter_login()
    {
        //用户编号
        $uid = $this->request->post('uid', 0, 'intval');
        if (!$uid)
            $this->_fail('MISSING_PARAM', '缺少用户编号');

        //token
        $token = $this->request->post('token');

        //用户信息
        $user = cache('user:' . $uid);
        if (!$user)
            $this->_fail('USER_NOT_FOUND', '用户不存在');

        $this->_user = $user;

        //检查用户状态

        //签名是否正确
        if ($user['token'] < time() || $token != $user['token'])
            $this->_fail('ERROR_TOKEN', 'token不存在或已过期');
    }

    /**
     * 参数验证
     */
    private function _check()
    {
        //必须字段
        foreach ($this->required as $field => $title) {
            if (!$this->request->post($field, false))
                $this->_fail('MISSING_PARAM', sprintf('缺少必须字段: %s', $title));
        }

        //客户端类型
        $this->client_type = $this->request->post('client_type', '', 'strtolower');
        if (!isset($this->allow_client_version[$this->client_type]))
            $this->_fail('ERROR_PARAM', sprintf('%s不合法', $this->required['client_type']['title']));

        //客户端版本号
        $this->client_version = $this->request->post('client_version', '');
        if (!$this->client_version || !preg_match('/^\d+\.\d+(\.\d+)?$/', $this->client_version))
            $this->_fail('ERROR_PARAM', sprintf('%s不合法', $this->required['client_version']));

        //接口版本号
        $this->api_version = $this->request->post('api_version');
        if (!$this->api_version || !preg_match('/^\d+\.\d+(\.\d+)?$/', $this->api_version))
            $this->_fail('ERROR_PARAM', sprintf('%s不合法', $this->required['api_version']));

        //接口版本检查
        //最低版本
        if (version_compare($this->api_version, $this->allow_client_version[$this->client_type]['minimum_api_version'], '<'))
            $this->_fail('ERROR_VERSION', '当前版本过低，为确保正常使用，请您升级~');
        //最高版本
        if (version_compare($this->api_version, $this->allow_client_version[$this->client_type]['latest_api_version'], '>'))
            $this->_fail('ERROR_VERSION', '当前版本暂未发布，请耐心等待~');

        // uuid
        $this->uuid = $this->request->post('uuid');

        // 检查签名
        $signature = $this->request->post('signature');
        if (!$signature)
            $this->_fail('ERROR_PARAM', sprintf('缺少%s', $this->required['signature']));

        // 检查时间戳
        $timestamp = $this->request->post('timestamp', 0, 'intval');
        if (abs($timestamp - time()) > self::TIME_EXCEED)
            $this->_fail('TIME_EXPIRED', '请求时间超时');

        //需要签名加密的字段值
        $params = compact(array_keys($this->required));
        ksort($params);  //按数组的键排序
        $sign = ''; //需要签名加密组合的字符串
        foreach ($params as $key => $val) {
            $sign .= $key . '=' . $val;
        }
        //sha1加密
        $sign = sha1($sign);

        //验证是否通过
        if ($sign !== $signature)
            $this->_fail('SIGN_MISMATCH', '签名不匹配');

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