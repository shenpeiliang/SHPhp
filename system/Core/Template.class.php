<?php
namespace Core;
/**
 * 模板引擎处理
 * Class View
 */
class Template
{
	/**
	 * 模板标签配置定义
	 * @var array
	 */
	private $config = [];

	/**
	 * 模板编译路径
	 * @var mixed|string
	 */
	private $template_compile_path = '';

	function __construct()
	{
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
	private function _rebuild_tag_preg($str)
	{
		return str_replace(
			array('{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?', '/'),
			array('\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?', '\/'),
			$str);
	}

	/**
	 * 解析模板标签
	 * @param string $template_path
	 */
	public function parse(string $template_path = '')
	{
		$content = file_get_contents($template_path);

		//文件名可以是原文件路径+hash(内容)，如果生成的文件名已经存在就不需要重新写入了
		//编译文件路径
		$compile_path = $this->get_compile_path($template_path);

		//解析include标签
		$this->_parse_include($content);

		//解析php语法
		$this->_parse_php($content);

		//写入内容
		file_put_contents($compile_path, $content);

		include $compile_path;
	}

	/**
	 * 获取编译文件路径
	 * @param string $template_path
	 * @return string
	 */
	private function get_compile_path(string $template_path = ''): string {
		//编译文件名
		$template_name = hash('md5', substr($template_path, strlen(APP_PATH))) . '.php';

		if(!is_dir($this->template_compile_path))
			mkdir($this->template_compile_path, 0755);

		return $this->template_compile_path . $template_name;
	}

	/**
	 * 解析include标签
	 * @param string $content
	 */
	private function _parse_include(string &$content)
	{
		//读取模板中的include标签
		$pattern = '#' . $this->config['taglib_begin'] . 'include\s*file\s*=\s*"\s*(.+?)\s*"\s*' . $this->config['taglib_end'] . '#is';

		while (preg_match($pattern, $content))
		{
			$content = preg_replace($pattern, "<?php include '$1';?>", $content);
		}
	}

	/**
	 * 解析php标签
	 * @param string $content
	 */
	private function _parse_php(string &$content)
	{
		//开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
		if (ini_get('short_open_tag'))
		{
			$content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
		}

		//解析普通模板标签 {$tagName}
		$pattern = '#'.$this->config['tmpl_l_delim'] .'(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'. $this->config['tmpl_r_delim'] . '#is';
		//$res = preg_match_all($pattern, $content, $matches);

		while (preg_match($pattern, $content))
		{
			$content = preg_replace($pattern, "<?php echo $1;?>", $content);
		}

	}
}