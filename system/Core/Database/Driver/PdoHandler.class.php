<?php
namespace Core\Database\Driver;

use Core\Database\DatabaseInterface;
use Core\Database\string;

class PdoHandler implements DatabaseInterface
{

	/**
	 * 获取构造器
	 * @param string $db_group
	 * @return mixed
	 */
	public function get_builder(string $db_group = 'master')
	{
		return new \Core\Database\Driver\Build($db_group);
	}
}