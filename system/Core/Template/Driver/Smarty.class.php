<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 10:27
 */

namespace Core\Template\Driver;

use Core\Template\TemplateInterface;

define('SMARTY_SPL_AUTOLOAD', TRUE);

class Smarty implements TemplateInterface
{
	/**
	 * 解析模板标签
	 * @param string $template_path
	 * @param array $var
	 * @return string
	 */
	public function fetch(string $template_path, array $template_var): string
	{
		//模板目录
		$template_dir = APP_PATH . 'View/' . ucfirst(convention_config('template_theme')) . '/';

		$templateFile = substr($template_path, strlen($template_dir));
		include_once SYSTEM_PATH . 'Vendor/Smarty/Smarty.class.php';
		$tpl = new \Smarty();
		$tpl->caching = convention_config('tmpl_cache_on');
		$tpl->template_dir = $template_dir;
		$tpl->compile_dir = convention_config('template_compile_path');
		$tpl->cache_dir = convention_config('template_compile_path');
		if (convention_config('tmpl_engine_config'))
		{
			$config = convention_config('tmpl_engine_config');
			foreach ($config as $key => $val)
			{
				$tpl->{$key} = $val;
			}
		}
		$tpl->assign($template_var);
		return $tpl->fetch($templateFile);
	}

}