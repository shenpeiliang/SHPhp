<?php
namespace Core\Database\Driver;

use Core\Database\DatabaseInterface;

class PdoHandler implements DatabaseInterface
{

    /**
	 * 获取构造器
	 * @param string $db_group
	 * @param string $table
	 * @return mixed
	 */
	public function get_builder(string $db_group = 'master', string $table = ''): \Core\Database\BuilderBase
	{
		return new \Core\Database\Driver\Build($db_group, $table);
	}
}