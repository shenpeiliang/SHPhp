<?php

namespace Controller;
/**
 * app接口测试
 * @author shenpeiliang
 * @date 2022-01-17 17:58:38
 */
class Address extends Appserver
{
    /**
     * 过滤登录
     * @var string[]
     */
    public $_filters = [
        'login'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function list()
    {
        $category_id = $this->request->post('category_id');
        $this->_ok([], '列表信息');
    }

}