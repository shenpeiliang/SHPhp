<?php
/**
 * 助手文件
 */

use Core\Cache\CacheFactory;

/**
 * 缓存
 * @param $key
 * @param null $data
 * @param int $expire
 * @return mixed
 */
function cache($key, $data = NULL, $expire = 1800)
{
    //缓存
    $factory = new CacheFactory();
    $cache = $factory->create();

    //获取
    if (is_null($data))
        return $cache->get($key);

    //删除
    if ($data === FALSE)
        return $cache->delete($key);

    //保存
    return $cache->save($key, $data, $expire);
}

/**
 * 获取实例
 * @param string $class
 * @return string
 */
function get_instance(string $class)
{
    return $class::get_instance();
}

/**
 * 获取应用配置参数
 * @param string $keys common.template_compile_path
 * @param null $default
 * @return mixed
 */
function config(string $keys, $default = NULL)
{
    return get_instance('\Core\Config')->get_app_config($keys, $default);
}

/**
 * 获取系统配置惯例
 * @return mixed
 */
function convention_config(string $keys)
{
    return get_instance('\Core\Config')->get_convention_config($keys);
}

/**
 * 递归数组函数
 * @param string $filter
 * @param array $data
 * @return array
 */
function array_map_recursive(string $filter, array $data)
{
    $result = [];
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}

/**
 * 框架过滤方法
 * @param string $value
 */
function frame_filter(string $value)
{
    // 过滤查询特殊字符
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }

    return $value;
}

/**
 * 移除攻击代码
 */
function remove_xss($var)
{
    static $_parser = null;
    if ($_parser === null) {
        require_once SRC_PATH . 'vendor/autoload.php';
        $config = HTMLPurifier_Config::createDefault();
        $_parser = new HTMLPurifier ($config);
    }
    if (is_array($var)) {
        foreach ($var as $key => $val) {
            $var [$key] = remove_xss($val);
        }
    } else {
        $var = $_parser->purify($var);
    }
    return $var;
}

/**
 * 更优于var_dump的打印方式
 * @param $data
 */
function debug_dump($data)
{
    dump($data);
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 ;1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? [$ip, $long] : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 获取指定长度的随机字符
 * @param int $length
 * @return string
 */
function get_rand_string(int $length){
    $source = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";

    $arr = [];

    $max_index = strlen($source) - 1;

    for($i = 0; $i < $length; $i++){
        $arr[] = $source[mt_rand(0, $max_index)];
    }

    return implode('', $arr);
}

