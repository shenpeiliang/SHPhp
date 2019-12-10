<?php
/**
 * 原生PHP模板
 * User: shenpeiliang
 * Date: 2019/12/9
 * Time: 17:26
 */

namespace Core\Template\Driver;

use Core\Template\BaseHandle;
use Core\Template\TemplateInterface;

class OriginHandler extends BaseHandle implements TemplateInterface
{
	/**
	 * 缓冲机制的嵌套级别
	 * @var int
	 */
	private $ob_level = 0;

	/**
	 * 缓冲输出内容
	 * @var string
	 */
	private $output = '';

	public function __construct()
	{
		$this->ob_level = ob_get_level();

		//给模板对象添加控制对象属性
		$this->set_controller_var();
	}

	/**
	 * 解析模板标签
	 * @param string $template_path
	 * @param array $var
	 * @return string
	 */
	public function fetch(string $template_path, array $template_var = []): string
	{
		//模板阵列变量分解成为独立变量
		extract($template_var, EXTR_OVERWRITE);

		//页面缓存
		ob_start();

		//关闭绝对刷送
		ob_implicit_flush(0);

		$content = file_get_contents($template_path);

		//开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
		if (ini_get('short_open_tag'))
		{
			$content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
			//执行PHP代码，代码不能包含打开/关闭 PHP tags
			echo eval("?>" . $content . "<?php;");
		} else
		{
			//包含编译文件
			include $template_path;
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		} else
		{
			$this->_append_output(ob_get_contents());
			@ob_end_clean();
		}

		return $this->output;
	}

	/**
	 * 输出缓存内容追加
	 * @param string $content
	 */
	private function _append_output(string $content): void
	{
		if ($this->output)
			$this->output .= $content;
		else
			$this->output = $content;
	}
}