<?php
namespace Core\File\Driver;

use Core\File\VersionInterface;

/**
 * Git操作类
 * @author shenpeiliang
 * @date 2022-01-28 10:10:06
 */
class Git implements VersionInterface
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
        $args = [
            '-n1',
            '--oneline',
            '--',
            escapeshellarg($file_path)
        ];
        $output = $this->_exec('log', $args);
        if (!$output)
            return false;

        $regex = '/^\w+/';
        if (!preg_match($regex, $output, $matches)) {
            $this->error = sprintf('未匹配结果[%s]', $output);
            return false;
        }

        return $matches [0];
    }

    /**
     * 执行命令
     * @param string $git_cmd
     * @param array $args
     * @param false $is_return_array
     * @return false|string
     */
    protected function _exec(string $git_cmd, array $args, $is_return_array = false)
    {
        //命令
        $cmd = 'git --git-dir=' . $this->root_path . '/.git '
            . $git_cmd . ' '
            . implode(' ', $args);

        exec($cmd, $output, $result_code);

        if ($result_code) {
            $this->error = sprintf('错误code[%s]', $result_code);
            return false;
        }

        if ($is_return_array)
            return $output;

        return implode('\n', $output);
    }
}