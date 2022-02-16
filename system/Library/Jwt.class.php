<?php


namespace Library;

/**
 * JWT跨域鉴权
 * @author shenpeiliang
 * @date 2022-02-16 15:08:06
 */
class Jwt
{
    //签名方法
    public static $supported_algs = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512']
    ];

    /**
     * 允许的时间差(秒)
     * @var int
     */
    public static $leeway = 60;

    /**
     * 错误
     * @var string
     */
    public $error = '';

    /**
     * 错误
     * @param $err
     * @return mixed
     */
    protected function _error($err){
        return $this->error = $err;
    }

    /**
     * 编码
     * @param $payload
     * @param $key
     * @param $alg
     * @param null $head
     * @return string
     */
    public function encode($payload, $key, $alg, $head = null): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $alg
        ];

        if (isset($head) && is_array($head))
            $header = array_merge($head, $header);

        //三部分
        $segments = [];

        $url_safe_base64 = new UrlSafeBase64();

        //头部（header）
        $segments[] = $url_safe_base64->encode(json_encode($header, JSON_UNESCAPED_SLASHES));

        //载荷（payload）
        $segments[] = $url_safe_base64->encode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        //签名 （signature）
        $msg = implode('.', $segments);
        $signature = $this->sign($msg, $key, $alg);
        if(!$signature)
            return false;

        $segments[] = $url_safe_base64->encode($signature, JSON_UNESCAPED_SLASHES);

        //组合
        return implode('.', $segments);
    }

    /**
     * 解码
     * @param $jwt
     * @param $key
     * @return false|string
     */
    public function decode($jwt, $key){
        //当前时间戳
        $timestamp = time();

        //拆分
        list($header64, $payload64, $signature64) = explode('.', $jwt);

        $url_safe_base64 = new UrlSafeBase64();

        //解码
        $header = $url_safe_base64->decode($header64);
        $payload = $url_safe_base64->decode($payload64);
        $signature = $url_safe_base64->decode($signature64);

        if(!isset($header['alg']))
            return $this->_error('参数错误');

        //签名是否合法
        $sign = $this->sign($header64 . '.' . $payload64, $key, $header['alg']);
        if(!$sign) return false;

        if($sign != $signature)
            return $this->_error('签名不匹配');

        //定义在什么时间之前，该jwt都是不可用的
        if (isset($payload['nbf']) && $payload['nbf'] > ($timestamp + static::$leeway))
            return $this->_error('token不可用');

        //jwt的签发时间
        if (isset($payload['iat']) && $payload['iat'] > ($timestamp + static::$leeway))
            return $this->_error('token不合法');

        //jwt的过期时间
        if (isset($payload['exp']) && $payload['exp'] < ($timestamp - static::$leeway))
            return $this->_error('token已过期');

        return $payload;
    }

    /**
     * 签名
     * @param $msg
     * @param $key
     * @param $alg
     * @return mixed
     */
    public function sign($msg, $key, $alg)
    {
        if(!isset(static::$supported_algs[$alg]))
            return $this->_error('token不可用');

        //对应的加密方法
        list($function, $algorithm) = static::$supported_algs[$alg];

        //签名
        return $this->_sign_{$function}($algorithm, $msg, $key, $alg);
    }

    /**
     * hash_hmac签名方法
     * @param $algorithm
     * @param $msg
     * @param $key
     * @return string
     */
    protected function _sign_hash_hmac($algorithm, $msg, $key)
    {
        return hash_hmac($algorithm, $msg, $key, true);
    }
}