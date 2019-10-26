<?php
/**
 * 模板引擎接口
 */

namespace Core\Template;


interface TemplateInterface
{
    /**
     * 模板解析
     * @param string $template_path 模板地址
     * @param array $template_var 模板变量
     * @return mixed
     */
    public function fetch(string $template_path, array $template_var);
}