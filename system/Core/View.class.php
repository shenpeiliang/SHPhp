<?php

/**
 * 模板引擎处理
 * Class View
 */
class View
{
	/**
	 * 输出内容文本可以包括Html
	 * @access private
	 * @param string $content 输出内容
	 * @param string $charset 模板输出字符集
	 * @param string $contentType 输出类型
	 * @return mixed
	 */
	private function render($content,$charset='utf-8',$contentType='text/html'){
		$charset = C('DEFAULT_CHARSET');
		if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
		// 网页字符编码
		header('Content-Type:'.$contentType.'; charset='.$charset);
		header('Cache-control: '.C('HTTP_CACHE_CONTROL'));  // 页面缓存控制
		// 输出模板文件
		echo $content;
	}
}