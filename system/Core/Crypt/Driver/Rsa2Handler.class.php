<?php
namespace Core\Crypt\Driver;

use Core\Crypt\CryptInterface;

/**
 * Rsa2方式加解密
 * @author shenpeiliang
 * @date 2022-01-24 12:01:38
 */
class Rsa2Handler implements CryptInterface
{
    /**
     * 私钥加密
     * @param string $data
     * @return mixed|void
     */
	public function encrypt(string $data)
    {
        $private_key_path = convention_config('crypt.rsa.private_key');

        $private_key = openssl_pkey_get_private(file_get_contents($private_key_path));
        if(!$private_key)
            return false;

        //私钥加密
        $flag = openssl_private_encrypt($data, $encrypted_data, $private_key);

        if($flag)
            return base64_encode($encrypted_data);

        return false;
    }

    /**
     * 公钥解密
     * @param string $data
     * @return mixed|void
     */
    public function decrypt(string $data)
    {
        $public_key_path = convention_config('crypt.rsa.public_key');

        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));

        if(!$public_key)
            return false;

        //公钥解密
        $flag = openssl_public_decrypt(base64_decode($data), $decrypted_data, $public_key);

        if($flag)
            return $decrypted_data;

        return false;
    }

    /**
     * 创建签名
     * @param string $data
     * @return false|string
     */
    public function create_sign(string $data)
    {
        $private_key_path = convention_config('crypt.rsa.private_key');

        $private_key = openssl_pkey_get_private(file_get_contents($private_key_path));
        if(!$private_key)
            return false;

        //创建签名
        $flag = openssl_sign($data, $signature, $private_key, convention_config('crypt.rsa.algorithm'));

        if($flag)
            return base64_encode($signature);

        return false;
    }

    /**
     * 验证签名
     * 签名验证通过后就可以正常地解析post数据了
     * @param string $data
     * @param $signature
     * @return false
     */
    public function verify_sign(string $data, $signature): bool
    {
        $public_key_path = convention_config('crypt.rsa.public_key');

        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));

        if(!$public_key)
            return false;

        //验证签名
        return openssl_verify(base64_decode($data), $signature, $public_key, convention_config('crypt.rsa.algorithm'));
    }
}