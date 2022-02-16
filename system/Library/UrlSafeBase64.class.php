<?php


namespace Library;

/**
 * base64_encode编码安全
 * @author shenpeiliang
 * @date 2022-02-16 11:53:02
 */
class UrlSafeBase64
{
    /**
     * 编码
     * @param string $input
     * @return string|string[]
     */
    public function encode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * 解码
     * @param string $input
     * @return string
     */
    public function decode(string $input): string
    {
        //按位填充等号
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}