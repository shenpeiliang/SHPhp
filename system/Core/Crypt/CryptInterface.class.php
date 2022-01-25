<?php

namespace Core\Crypt;

/**
 * 加密接口
 * @author shenpeiliang
 * @date 2022-01-24 10:44:12
 */
interface CryptInterface
{
    /**
     * 加密
     * @param string $data
     * @return mixed
     */
	public function encrypt(string $data);

    /**
     * 解密
     * @param string $data
     * @return mixed
     */
	public function decrypt(string $data);

}