<?php
namespace Core\Template\Driver;
use Core\Template\TemplateInterface;
/**
 * 自定义模板引擎
 * Class View
 */
class FrameTemp implements TemplateInterface
{
    /**
     * 模板标签配置定义
     * @var array
     */
    private $config = [];

    /**
     * 视图变量赋值
     * @var array
     */
    private $var = [];

    /**
     * 模板编译路径
     * @var mixed|string
     */
    private $template_compile_path = '';

    function __construct(array $var)
    {
        $this->var = $var;
        $this->template_compile_path = config('common.template_compile_path');
        $this->config['taglib_begin'] = $this->_rebuild_tag_preg(config('common.template_tag.taglib_begin'));
        $this->config['taglib_end'] = $this->_rebuild_tag_preg(config('common.template_tag.taglib_end'));
        $this->config['tmpl_l_delim'] = $this->_rebuild_tag_preg(config('common.template_tag.tmpl_l_delim'));
        $this->config['tmpl_r_delim'] = $this->_rebuild_tag_preg(config('common.template_tag.tmpl_r_delim'));
    }

    /**
     * 重建标签正则
     * @param $str
     * @return mixed
     */
    private function _rebuild_tag_preg(string $str): string
    {
        return str_replace(
            array('{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?', '/'),
            array('\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?', '\/'),
            $str);
    }

    /**
     * 解析模板标签
     * @param string $template_path
     * @return string
     */
    public function fetch(string $template_path): string
    {
        $content = file_get_contents($template_path);

        //文件名可以是原文件路径+hash(内容)，如果生成的文件名已经存在就不需要重新写入了
        //编译文件路径
        $compile_path = $this->_get_compile_path($template_path);

        //模板阵列变量分解成为独立变量
        extract($this->var, EXTR_OVERWRITE);

        //页面缓存
        ob_start();

        //关闭绝对刷送
        ob_implicit_flush(0);

        //解析自定义标签
        $this->_parse_tag($content);

        //解析php变量
        $this->_parse_php($content);

        //写入内容
        file_put_contents($compile_path, $content);

        //包含编译文件
        include $compile_path;

        //获取并清空缓存
        $content = ob_get_clean();

        return $content;
    }

    /**
     * 获取编译文件路径
     * @param string $template_path
     * @return string
     */
    private function _get_compile_path(string $template_path = ''): string
    {
        //编译文件名
        $template_name = hash('md5', substr($template_path, strlen(APP_PATH))) . '.php';

        if (!is_dir($this->template_compile_path))
            mkdir($this->template_compile_path, 0755, TRUE);

        return $this->template_compile_path . $template_name;
    }

    /**
     * 解析tag标签
     * @param string $content
     */
    private function _parse_tag(string &$content)
    {
        //是否包含指定标签
        if(strpos($content, $this->config['taglib_begin']) === false)
            return ;

        //include标签
        $pattern_include = '#' . $this->config['taglib_begin'] . 'include\s*file\s*=\s*["\']\s*(.+?)\s*["\']\s*' . $this->config['taglib_end'] . '#is';

        while (preg_match($pattern_include, $content)) {
            $content = preg_replace($pattern_include, "<?php include '$1';?>", $content);
        }

        //if开始标签
        $pattern_if = '#' . $this->config['taglib_begin'] . 'if\s*condition\s*=\s*["\']\s*(.+?)\s*["\']\s*' . $this->config['taglib_end'] . '#is';

        while (preg_match($pattern_if, $content)) {
            $content = preg_replace($pattern_if, "<?php if ($1) { ?>", $content);
        }

        //elseif开始标签
        $pattern_if = '#' . $this->config['taglib_begin'] . 'elseif\s*condition\s*=\s*["\']\s*(.+?)\s*["\']\s*' . $this->config['taglib_end'] . '#is';

        while (preg_match($pattern_if, $content)) {
            $content = preg_replace($pattern_if, "<?php } elseif($1) { ?>", $content);
        }

        //else标签
        $pattern_else = '#' . $this->config['taglib_begin'] . 'else\s*\/\s*' . $this->config['taglib_end'] . '#is';

        while (preg_match($pattern_else, $content)) {
            $content = preg_replace($pattern_else, "<?php } else { ?>", $content);
        }

        //if结束标签
        $pattern_end_if = '#' . $this->config['taglib_begin'] . '\/if\s*' . $this->config['taglib_end'] . '#is';

        while (preg_match($pattern_end_if, $content)) {
            $content = preg_replace($pattern_end_if, "<?php } ?>", $content);
        }

    }

    /**
     * 解析php短标签和<{$tagName}>
     * @param string $content
     */
    private function _parse_php(string &$content)
    {
        //开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
        if (ini_get('short_open_tag')) {
            $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
        }

        //解析普通模板标签<{$tagName}>
        $pattern = '#' . $this->config['tmpl_l_delim'] . '(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)' . $this->config['tmpl_r_delim'] . '#is';

        while (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "<?php echo $1;?>", $content);
        }

        //支持函数{$varname|function1|function2=arg1,arg2}

    }
}