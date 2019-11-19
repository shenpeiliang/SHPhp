<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/18
 * Time: 15:30
 */

namespace Core;


class Model
{

	/**
	 * model名称
	 * @var string
	 */
	protected $model_name = '';

	public function __construct()
	{
		//获取Model名称
		$this->get_model_name();

		//创建连接
		$driver = new \Core\Database\DatabaseFactory();
		$driver->create();

	}

	/**
	 * 获取model名称
	 * @return string
	 */
	protected function get_model_name(): void
	{
		//去除Model之后的字符串
		$name = substr(get_class($this), 0, -strlen('Model'));
		if ($pos = strrpos($name, '\\'))
		{
			//有命名空间
			$this->model_name = substr($name, $pos + 1);
		} else
		{
			$this->model_name = $name;
		}

	}
}