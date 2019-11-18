<?php

/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/14
 * Time: 9:46
 */
namespace Core\Service;
interface BaseFactoryInterface
{
	/**
	 * 创建模板类驱动
	 * @return \Core\Template\TemplateInterface
	 */
	public function create(): \Core\Template\TemplateInterface ;
}