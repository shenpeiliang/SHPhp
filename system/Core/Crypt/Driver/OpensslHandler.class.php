<?php
namespace Core\Crypt\Driver;

use Core\Crypt\CryptInterface;

/**
 * openssl方式加解密
 * @author shenpeiliang
 * @date 2022-01-24 12:01:38
 */
class OpensslHandler implements CryptInterface
{
    /**
     * 加密
     * @param string $data
     * @return mixed|void
     */
	public function encrypt(string $data)
    {
        //秘钥
        $key = convention_config('crypt.openssl.key');

        //加密方法
        $method = convention_config('crypt.openssl.method');

        //向量
        //二进制字符串转换为十六进制值，注意解码过程中使用的是substr/strlen是不携带编码的，否则分割字符串时不对
        //$iv = bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

        $encrypted_str = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA , $iv);

        if($encrypted_str)
            return base64_encode($iv . ':' . $encrypted_str);

        return false;
    }

    /**
     * 解密
     * @param string $data
     * @return mixed|void
     */
    public function decrypt(string $data)
    {
        //转码
        $data = base64_decode($data);

        //分隔符位置
        $index = strpos($data, ':');

        //分解向量和密文
        $iv = substr($data, 0, $index);
        $encrypted_str = substr($data, $index + 1, strlen($data));

        //秘钥
        $key = convention_config('crypt.openssl.key');
        //加密方法
        $method = convention_config('crypt.openssl.method');

        return openssl_decrypt($encrypted_str, $method, $key, OPENSSL_RAW_DATA , $iv);
    }
}