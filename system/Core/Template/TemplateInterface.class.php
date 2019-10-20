<?php
/**
 * 模板引擎接口
 */

namespace Core\Template;


interface TemplateInterface
{
    public function fetch(string $template_path);
}