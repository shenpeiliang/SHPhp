<?php
return [
    'api' => [
        //ip白名单
        'allowed_ips' => array(
            '127.0.0.*',
            '192.168.*'
        ),
        'authkey' => 'redis#key#api#version_1',
        'source' => array(
            '10086',
            '10000'
        ),
    ],

    //app客户端
    'allow_client_version' => [
        'android' => [
            'title' => '安卓系统',
            'latest_api_version' => '2.0.0', //最新版本
            'minimum_api_version' => '1.0.0' //最低版本
        ],
        'ios' => [
            'title' => '苹果系统',
            'latest_api_version' => '2.0.0',
            'minimum_api_version' => '1.0.0'
        ]
    ]
];