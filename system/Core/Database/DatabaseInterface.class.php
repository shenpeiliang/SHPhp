<?php
/**
 * 统一接口
 */

namespace Core\Database;


interface DatabaseInterface
{
	/**
	 * 获取构造器
	 * @param string $db_group
	 * @param string $table
	 * @return mixed
	 */
	public function get_builder(string $db_group = 'master', string $table = '');
}