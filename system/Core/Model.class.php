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

	public function __construct(string $name = '')
	{
		if ($name)
			$this->model_name = $name;
		else
			$this->get_model_name(); //自动获取Model类名作为表名

	}

	/**
	 * 获取数据库连接
	 * @param string $db_group
	 * @return mixed
	 */
	public function db(string $db_group = 'master')
	{
		//驱动
		$driver = new \Core\Database\DatabaseFactory();

		//创建数据库操作构造器
		return $driver->create()->get_builder($db_group);
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