<?php

namespace Core\File\Driver;

use Core\File\VersionInterface;

/**
 * Time操作类
 * @author shenpeiliang
 * @date 2022-01-28 10:10:06
 */
class Time implements VersionInterface
{
    /**
     * @var 错误
     */
    public $error;

    /**
     * @var 根目录
     */
    private $root_path;

    /**
     * 设置根目录
     * @param string $root_path
     * @return VersionInterface
     */
    public function set_root_path(string $root_path): VersionInterface
    {
        $this->root_path = $root_path;
        return $this;
    }

    /**
     * 获取版本号
     * @param string $file_path
     * @return string
     */
    public function get_file_version(string $file_path): string
    {
        return (string) filemtime($this->root_path . $file_path);
    }
}