<?php

namespace Core;
/**
 * 配置 - 单例应用
 * Class Config
 * @package Core
 */
class Config
{
    /**
     * 实例
     * @var
     */
    private static $instance = NULL;

    /**
     * 配置文件
     * @var array
     */
    private static $config = [];

    private function __construct()
    {
    }

    /**
     * 获取单例
     * @return Config
     */
    public static function get_instance(): self
    {
        if (!self::$instance)
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * 获取应用配置参数
     * @param string $key 参数名 格式：文件名.参数名1.参数2...
     * 例如：common.template_compile_path
     * @param null $default 默认值
     * @return null
     */
    public function get_app_config(string $keys, $default = NULL)
    {
        //拆分数组 保留非false值 配置项全部为小写
        $keys = array_filter(explode('.', strtolower($keys)));
        if (empty($keys))
            return false;

        //第一个参数表示文件名 首字母大写
        $file = ucfirst(array_shift($keys));

        if (empty(self::$config['app_' . $file])) {
            //配置文件绝对路径
            $absolute_path = APP_PATH . 'Conf/' . (defined('ENVIRONMENT') ? ENVIRONMENT . '/' : '') . $file . '.php';
            if (!is_file($absolute_path))
                throw \Core\Exceptions\FileException::for_not_found();

            //包含文件
            $config = include($absolute_path);

            if (!isset($config) || !is_array($config))
                throw  \Core\Exceptions\FileException::for_error_param();

            //配置文件的数组名为config
            self::$config['app_' . $file] = $config;

        }

        $config = self::$config['app_' . $file];

        while ($keys) {
            $key = array_shift($keys);

            //如果不存在，直接返回默认值，不再递归
            if (!isset($config[$key])) {
                $config = $default;
                break;
            }

            $config = $config[$key];
        }

        return $config;
    }

    /**
     * 获取系统参数 参数
     * @param string $key 参数名 格式：参数名1.参数2...
     * 例如：template_compile_path.left
     * @param null $default
     * @return bool|mixed|null
     */
    public function get_system_config(string $keys, $default = NULL)
    {
        //拆分数组 保留非false值 配置项全部为小写
        $keys = array_filter(explode('.', strtolower($keys)));
        if (empty($keys))
            return false;

        //首字母大写
        $file = 'Convention';

        if (empty(self::$config['system_' . $file])) {
            //配置文件绝对路径
            $absolute_path = SYSTEM_PATH . 'Config/' . $file . '.php';
            if (!is_file($absolute_path))
                throw \Core\Exceptions\FileException::for_not_found();

            //包含文件
            $config = include($absolute_path);

            if (!isset($config) || !is_array($config))
                throw  \Core\Exceptions\FileException::for_error_param();

            //配置文件的数组名为config
            self::$config['system_' . $file] = $config;

        }

        $config = self::$config['system_' . $file];

        while ($keys) {
            $key = array_shift($keys);

            //如果不存在，直接返回默认值，不再递归
            if (!isset($config[$key])) {
                $config = $default;
                break;
            }

            $config = $config[$key];
        }

        return $config;
    }

    /**
     * 获取配置惯例（读取应用配置System.php，默认读取框架Convention.php配置）
     * @param string $key 参数名 格式：参数名1.参数2...
     * 例如：template_compile_path.left
     * @return bool|mixed|null
     */
    public function get_convention_config(string $keys)
    {
        //用户配置(优先级较高)
        $app_config = $this->get_app_config('system.' . $keys);
        if ($app_config)
            return $app_config;

        //系统配置
        $system_config = $this->get_system_config($keys);
        if ($system_config)
            return $system_config;

        return false;
    }
}