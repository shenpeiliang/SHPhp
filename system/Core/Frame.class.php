<?php

namespace Core;

use Exception\FremeException;

class Frame
{
    /**
     * 启动配置
     */
    public static function run()
    {
        //时区设置
        date_default_timezone_set('Asia/Shanghai');

        //composer
        require_once SRC_PATH . 'vendor/autoload.php';

        //自动加载
        include SYSTEM_PATH . 'Core/Loader.class.php';
        spl_autoload_register('\Core\Loader::autoload');

        //加载助手文件
        include SYSTEM_PATH . 'Function/Helper.php';

        //session启动
        new Session();

        //注册一个会在php中止时执行的函数
        register_shutdown_function('\Core\Frame::fatal_error');

        //设置用户自定义的错误处理函数
        set_error_handler('\Core\Frame::fatal_error');

        //设置用户自定义的异常处理函数
        // 注意swoole不支持set_exception_handler异常处理，必须在回调函数中进行try/catch捕获异常，否则会导致工作进程退出
        //暂不处理set_exception_handler('\Core\Frame::fatal_error');

        //路由处理
        $route = new \Core\Route();

        //类命名空间
        $class = $route->get_class();

        //方法实现
        try {
            if (!class_exists($class))
                throw new \Exception(sprintf('控制器%s不存在', $class), 0);

            $handler = new $class();
            $method = $route->get_method();
            if (!method_exists($handler, $method))
                throw new FremeException(sprintf('控制器%s中方法%s不存在', $class, $method), 0);

            call_user_func_array([$handler, $method], $route->get_method_param());
        } catch (\Exception $e) {
            FremeException::for_system_error($e);
        }
    }

    /**
     * 致命错误
     */
    public static function fatal_error()
    {
        if ($e = error_get_last()) {
            ob_end_clean();
            self::halt($e);
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    static public function halt($error)
    {
        //调试模式下输出错误信息
        if (!is_array($error)) {
            $trace = debug_backtrace();
            $e['message'] = $error;
            $e['file'] = $trace[0]['file'];
            $e['line'] = $trace[0]['line'];
            ob_start();
            debug_print_backtrace();
            $e['trace'] = ob_get_clean();
        } else {
            $e = $error;
        }

        //错误异常处理
        FremeException::for_system_error($e);
    }
}