<?php


namespace Core\File;

/**
 * 版本接口定义
 * @author shenpeiliang
 * @date 2022-01-28 10:13:09
 */
interface VersionInterface
{
    /**
     * 获取文件当前版本号
     * @param string $file_path
     * @return string
     */
    public function get_file_version(string $file_path): string;

    /**
     * 设置根目录
     * @param string $root_path
     */
    public function set_root_path(string $root_path): VersionInterface;
}