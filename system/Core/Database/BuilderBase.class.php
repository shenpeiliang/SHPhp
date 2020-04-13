<?php

namespace Core\Database;

/**
 * Class BuilderBase
 * User: shenpeiliang
 * @package Core\Database
 */
class BuilderBase
{
	protected $connection = NULL;    //mysql对象
	protected $statement = NULL;    //预处理对象
	protected $db_config = []; //数据库配置

	/**
	 * 是否开启调试
	 * @var bool
	 */
	protected $debug = FALSE;

	/**
	 * 查询字段
	 * @var array
	 */
	protected $field = [];

	/**
	 * 查询表
	 * @var array
	 */
	protected $from = [];

	/**
	 * 关联表
	 * @var array
	 * [表，条件，关联类型]
	 */
	protected $join = [];

	/**
	 * 查询条件
	 * 结构 [key,value,exp(and/or),type(where/having)
	 * key为字符串，可以是  ? %d :name
	 * value可以为字符串或数组，格式为[value,type]， type可以是pdo类型参数，默认为空 如：[3 => \PDO::PARAM_INT]
	 * exp关系字符串 and in 等
	 * where('num >', $num)
	 * where('num in', $num)
	 * @var array
	 */
	protected $where = [];

	/**
	 * 排序
	 * @var array
	 */
	protected $order_by = [];

	/**
	 * 分组
	 * @var array
	 */
	protected $group_by = [];


	/**
	 * 结果数
	 * @var int
	 */
	protected $limit = 0;


	/**
	 * 偏移量
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * 锁
	 * @var string
	 */
	protected $lock = '';

	/**
	 * 预绑定参数 用于query excute update insert
	 * 结构 [key => [value,where_type(in),type(pdo类型)]
	 * @var unknown
	 */
	protected $param = [];

	/**
	 * 是否是预绑定参数
	 * @var bool
	 */
	protected $is_bind_param = false;

	/**
	 * sql
	 * @var string
	 */
	protected $sql = '';

	/**
	 * 事务嵌套级别
	 * @var unknown
	 */
	protected $trans_depth = 0;

	/**
	 * 初始化
	 * BuilderBase constructor.
	 * @param string $db_group
	 * @param string $table
	 */
	public function __construct(string $db_group = 'master', string $table = '')
	{
		//配置初始化
		$this->init($db_group);

		//连接数据库
		$this->db_connect();

		if ($table)
			$this->table($table);
	}

	/**
	 * 开启调试，输出预处理参数
	 * @return BuilderBase
	 */
	public function set_debug(): self
	{
		$this->debug = TRUE;
		return $this;
	}

	/**
	 * 清空查询
	 */
	private function _clear(): void
	{
		$this->from = [];
		$this->field = [];
		$this->join = [];
		$this->where = [];
		$this->order_by = [];
		$this->group_by = [];
		$this->param = [];
		$this->limit = 0;
		$this->offset = 0;
		$this->lock = '';
		$this->sql = '';
		$this->is_bind_param = false;

		//是否开启调试
		if ($this->debug)
			$this->_dump_statement_params();

		$this->statement = NULL;
	}

	/**
	 * 配置
	 */
	private function init($db_group)
	{
		//配置
		$config = config('database.' . $db_group);

		$this->db_config['host'] = isset($config['host']) ? $config['host'] : '';

		$this->db_config['prefix'] = isset($config['prefix']) ? $config['prefix'] : '';

		$this->db_config['charset'] = isset($config['charset']) ? $config['charset'] : '';

		$this->db_config['persistent'] = isset($config['persistent']) ? $config['persistent'] : FALSE;

		$this->db_config['username'] = isset($config['username']) ? $config['username'] : '';

		$this->db_config['password'] = isset($config['password']) ? $config['password'] : '';

		$this->db_config['database'] = isset($config['database']) ? $config['database'] : '';

	}

	/**
	 * 获取表前缀
	 * @return string
	 */
	public function get_table_prefix(): string
	{
		return $this->db_config['prefix'];
	}

	/**
	 * 解析连接
	 */
	private function db_connect()
	{
		//实例化mysql对象
		try
		{
			$options = array(
				\PDO::ATTR_PERSISTENT => $this->db_config['persistent'], //是否持久化连接(使用连接池不能使用长连接，会不断地创建连接从而导致服务器超载)
				\PDO::ATTR_EMULATE_PREPARES => TRUE,//启用或禁用预处理语句的模拟 ;使用此设置强制PDO总是模拟预处理语句（如果为 TRUE ），或试着使用本地预处理语句（如果为 FALSE ）
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->db_config['charset'], //编码类型
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,//设置默认的提取模式 ;返回一个索引为结果集列名的数组
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING //抛出错误异常
			);

			$dsn = 'mysql:host=' . $this->db_config['host'] . ';dbname=' . $this->db_config['database'];
			$this->connection = new \PDO($dsn, $this->db_config['username'], $this->db_config['password'], $options);

		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_connect_error($e);
			return false;
		}
	}

	/**
	 * 获取数据库连接
	 * @return \PDO
	 */
	public function get_connection(): \PDO
	{
		return $this->connection;
	}

	/**
	 * 获取预处理查询的sql语句
	 * @return string
	 */
	public function get_sql(): string
	{
		return $this->sql;
	}

	/**
	 * 获取预处理绑定参数
	 * @return string|array
	 */
	public function get_param(): array
	{
		return $this->param;
	}

	/**
	 * 组建绑定预处理
	 * @return MysqlPdo
	 */
	private function _bind_value(): self
	{
		if (empty($this->where))
			return $this;

		$count = count($this->where);
		for ($i = 0; $i < $count; $i++)
		{
			if ($this->where[$i]['exp'] == 'OR')
				$this->_bind_value_or($i);
			else
				$this->_bind_value_and($i);
		}

		return $this;
	}

	/**
	 * 组建绑定预处理 or
	 * @param $i
	 */
	private function _bind_value_or($i): void
	{
		$value = $this->where[$i]['value'];
		$where_definition = '=';

		//一个或多个空格分隔作为条件
		$where_key = preg_split('/\s+/', trim($this->where[$i]['key']));
		if (isset($where_key[1]))
			$where_definition = strtoupper($where_key[1]);

		if ($where_definition == 'IN')
		{
			for ($j = 0; $j < count($value); $j++)
			{
				//第二个参数是否是指定参数类型的
				if (is_array($value[$j][0]))
				{
					$bind_val = $value[$j][0];
					if (isset($value[$j][1]))
						$pdo_param = $value[$j][1];
					else
						$pdo_param = $this->pdo_param_type($bind_val[0]); //绑定数据类型
				} else
				{
					$bind_val = $value[$j];
					$pdo_param = $this->pdo_param_type($bind_val[0]); //绑定数据类型
				}

				for ($k = 0; $k < count($bind_val); $k++)
				{
					$this->statement->bindValue(':_' . $i . '_' . $j . '_' . $k, $bind_val[$k], $pdo_param);
				}

			}

		} elseif ($where_definition == 'LIKE')
		{
			for ($j = 0; $j < count($value); $j++)
			{
				$this->statement->bindValue(':_' . $i . '_' . $j, $value[$j], \PDO::PARAM_STR);
			}
		} else
		{
			//绑定类型
			//第二个参数是否是指定参数类型的
			if (is_array($value[0]))
			{
				$bind_value = $value[0];
				if (isset($value[1]))
					$pdo_param = $value[1];
				else
					$pdo_param = $this->pdo_param_type($bind_value[0]); //绑定数据类型
			} else
			{
				$bind_value = $value;
				$pdo_param = $this->pdo_param_type($bind_value[0]); //绑定数据类型
			}
			for ($j = 0; $j < count($bind_value); $j++)
			{
				$this->statement->bindValue(':_' . $i . '_' . $j, $bind_value[$j], $pdo_param);
			}
		}
	}

	/**
	 * 组建绑定预处理 and
	 * @param $i
	 */
	private function _bind_value_and($i): void
	{
		$value = $this->where[$i]['value'];
		$where_definition = '=';

		//一个或多个空格分隔作为条件
		$where_key = preg_split('/\s+/', trim($this->where[$i]['key']));
		if (isset($where_key[1]))
			$where_definition = strtoupper($where_key[1]);

		//绑定类型
		//第二个参数是否是指定参数类型的
		if (is_array($value[0]))
		{
			$bind_value = $value[0];
			if (isset($value[1]))
				$pdo_param = $value[1];
			else
				$pdo_param = $this->pdo_param_type($bind_value[0]); //绑定数据类型
		} else
		{
			$bind_value = $value;
			$pdo_param = $this->pdo_param_type($bind_value); //绑定数据类型
		}

		if ($where_definition == 'IN')
		{
			for ($j = 0; $j < count($bind_value); $j++)
			{
				$this->statement->bindValue(':_' . $i . '_' . $j, $bind_value[$j], $pdo_param);
			}

		} else
		{
			$this->statement->bindValue(':_' . $i, $bind_value, $pdo_param);

		}
	}

	/**
	 * 构建查询表
	 * @return string
	 */
	private function _build_table(): string
	{
		$table = '';
		//去重
		$this->from = array_unique($this->from);
		$count = count($this->from);
		for ($i = 0; $i < $count; $i++)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$table .= $this->db_config['prefix'] . strtolower($this->from[$i]) . (!$this->is_bind_param ? ' AS ' . strtolower($this->from[$i]) : '') . $fill;
		}

		return $table;
	}

	/**
	 * 构建join
	 * @return string
	 */
	private function _build_join(): string
	{
		$join = '';
		$count = count($this->join);
		for ($i = 0; $i < $count; $i++)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$join .= ' ' . $this->join[$i][2] . ' JOIN '
				. $this->db_config['prefix']
				. $this->join[$i][0]
				. ' AS '
				. $this->join[$i][0]
				. ' ON '
				. $this->join[$i][1]
				. $fill;
		}
		return $join;
	}

	/**
	 * 构建查询字段
	 * @return string
	 */
	private function _build_field(): string
	{
		if (!$this->field)
			return '*';

		$field = '';
		$count = count($this->field);
		for ($i = 0; $i < $count; $i++)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$field .= $this->field[$i] . $fill;
		}

		return $field;
	}

	/**
	 * 构建分页查询
	 * @return string
	 */
	private function _build_limit(): string
	{
		if (!$this->limit)
			return '';

		return ' LIMIT ' . $this->offset . ',' . $this->limit;
	}

	/**
	 * 构建锁
	 * @return string
	 */
	private function _build_lock(): string
	{
		if (!$this->lock)
			return '';

		return ' ' . $this->lock . ' ';
	}

	/**
	 * 组建查询sql
	 */
	private function _build_select(): void
	{
		$this->sql = 'SELECT '
			. $this->_build_field()
			. ' FROM '
			. $this->_build_table()
			. $this->_build_join()
			. $this->_build_where()
			. $this->_build_group_by()
			. $this->_build_having()
			. $this->_build_order()
			. $this->_build_limit()
			. $this->_build_lock();
	}

	/**
	 * 构建更新sql
	 */
	private function _build_update(): void
	{
		$this->is_bind_param = TRUE;
		$this->sql = 'UPDATE '
			. $this->_build_table()
			. $this->_build_update_data()
			. $this->_build_where();
	}

	/**
	 * 构建删除sql
	 */
	private function _build_delete(): void
	{
		$this->is_bind_param = TRUE;
		$this->sql = 'DELETE FROM '
			. $this->_build_table()
			. $this->_build_where();
	}

	/**
	 * 构建插入sql
	 */
	private function _build_insert(): void
	{
		$this->is_bind_param = TRUE;
		$this->sql = 'INSERT INTO '
			. $this->_build_table()
			. $this->_build_insert_data();
	}

	/**
	 * 构建更新sql
	 * @return string
	 */
	protected function _build_update_data(): string
	{
		if (!$this->param)
		{
			return '';
		}
		$set = ' SET ';
		$count = count($this->param);
		$i = 0;
		foreach ($this->param as $key => $value)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$set .= ' ' . substr($key, 2) . ' = ' . $key . $fill;
			$i++;
		}
		return $set;
	}

	/**
	 * 构建插入sql
	 * @return string
	 */
	protected function _build_insert_data(): string
	{
		if (!$this->param)
			return '';

		$insert = ' ( ';
		$count = count($this->param);
		$i = 0;
		//字段名
		$insert_key = '';
		//字段值
		$insert_val = '';
		foreach ($this->param as $key => $value)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$insert_key .= substr($key, 2) . $fill;
			$insert_val .= $key . $fill;
			$i++;
		}
		$insert .= $insert_key . ' ) VALUE ( ' . $insert_val;
		$insert .= ' ) ';
		return $insert;
	}

	/**
	 * 构建where查询
	 * @return string
	 */
	protected function _build_where(): string
	{
		return $this->_build_where_having_sql();
	}

	/**
	 * 构建having查询
	 * @return string
	 */
	protected function _build_having(): string
	{
		return $this->_build_where_having_sql('HAVING');
	}

	/**
	 * 构建where/having Sql预处理
	 * @param string $type
	 * @return string
	 */
	private function _build_where_having_sql(string $type = 'WHERE')
	{
		if (!$this->where)
			return '';

		$where = '';
		$count = count($this->where);

		$where_and = $where_or = [];

		//转换 name='a' 为 name=:0:name
		for ($i = 0; $i < $count; $i++)
		{
			if ($this->where[$i]['type'] != $type)
				continue;

			//默认算数符
			$where_definition = '=';

			//一个或多个空格分隔作为条件
			$where_key = preg_split('/\s+/', trim($this->where[$i]['key']));
			if (isset($where_key[1]))
				$where_definition = strtoupper($where_key[1]);

			if ($this->where[$i]['exp'] == 'OR')
				$where_or[] = $this->_build_where_definition_or($where_definition, $i);
			else
				$where_and[] = $this->where[$i]['key'] . ' ' . $this->_build_where_definition_and($where_definition, $i);

		}

		if ($where_and)
			$where .= implode(' AND ', $where_and);

		if ($where_or)
			$where .= ($where_and ? ' AND ' : '') . implode(' AND ', $where_or);

		if ($where)
			$where = ' ' . strtoupper($type) . ' ' . $where;

		return $where;
	}

	/**
	 * 构造查询预处理 或关系
	 * @param string $where_definition
	 * @param int $i where索引
	 * @return string
	 */
	private function _build_where_definition_or(string $where_definition, int $i): string
	{
		//一个条件中包含多个选项值
		$bind_value = $this->where[$i]['value'];

		if ($where_definition == 'LIKE')
		{
			$sql = '(';
			for ($j = 0; $j < count($bind_value); $j++)
			{
				$sql .= $this->where[$i]['key'] . ' :_' . $i . '_' . $j . (($j == count($bind_value) - 1) ? '' : ' OR ');
			}
			$sql .= ')';
		} elseif ($where_definition == 'IN')
		{
			$sql = '(';
			for ($j = 0; $j < count($bind_value); $j++)
			{
				//根据数组长度替换sql参数为?
				$value = $bind_value[$j];
				//第二个参数是否是指定参数类型的
				if (is_array($value[0]))
					$value = $value[0];

				$sql .= $this->where[$i]['key'] . '(';
				for ($k = 0; $k < count($value); $k++)
				{
					$sql .= ':_' . $i . '_' . $j . '_' . $k . (($k == count($value) - 1) ? '' : ',');
				}

				$sql .= ")" . (($j == count($bind_value) - 1) ? '' : ' OR ');
			}

			$sql .= ')';
		} else
		{
			$sql = '(';
			for ($j = 0; $j < count($bind_value); $j++)
			{
				$sql .= $this->where[$i]['key'] . '=' . ':_' . $i . '_' . $j . (($j == count($bind_value) - 1) ? '' : ' OR ');
			}
			$sql .= ')';
		}
		return $sql;
	}

	/**
	 * 构造查询预处理 并关系
	 * @param string $where_definition
	 * @param int $i where索引
	 * @return string
	 */
	private function _build_where_definition_and(string $where_definition, int $i): string
	{
		$bind_value = $this->where[$i]['value'];

		if ($where_definition == 'IN')
		{
			//根据数组长度替换sql参数为?
			$value = $bind_value;
			//第二个参数是否是指定参数类型的
			if (is_array($bind_value[0]))
				$value = $bind_value[0];

			$sql = '(';

			for ($j = 0; $j < count($value); $j++)
			{
				$sql .= ':_' . $i . '_' . $j . (($j == count($value) - 1) ? '' : ',');
			}

			return $sql . ')';
		} elseif ($where_definition == 'LIKE')
		{
			return ' :_' . $i;
		} else
		{
			return ' = :_' . $i;
		}
	}

	/**
	 * 构建分组查询
	 * @return string
	 */
	protected function _build_group_by(): string
	{
		if (!$this->group_by)
			return '';

		$group = ' GROUP BY ';
		$count = count($this->group_by);
		for ($i = 0; $i < $count; $i++)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$group .= $this->group_by[$i] . $fill;
		}
		return $group;
	}

	/**
	 * 构建order查询
	 * @return string
	 */
	protected function _build_order(): string
	{
		if (!$this->order_by)
		{
			return '';
		}
		$order = ' ORDER BY ';
		$count = count($this->order_by);
		for ($i = 0; $i < $count; $i++)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			if (is_array($this->order_by[$i]))
				$order .= $this->order_by[$i][0] . $this->order_by[$i][1];
			else
				$order .= $this->order_by[$i];

			$order .= $fill;
		}
		return $order;
	}

	/**
	 * 查询字段信息
	 * @param string $value string|array
	 * @return MysqlPdo
	 */
	public function select($value): self
	{
		if (is_string($value))
		{
			$value = preg_split('/\,/', $value);
		}

		foreach ($value as $item)
		{
			$this->field[] = $item;
		}

		return $this;
	}

	/**
	 * 表名设置
	 * @param unknown $value string
	 * @return MysqlPdo
	 */
	public function table($value): self
	{
		//统一小写
		$value = strtolower($value);

		//是否已经存在
		if (array_key_exists($value, $this->from))
			return $this;

		if (is_array($value))
		{
			foreach ($value as $item)
			{
				$this->from[] = $item;
			}
		} else
		{
			$this->from[] = $value;
		}
		return $this;
	}

	/**
	 * 关联查询
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return BuilderBase
	 */
	public function join(string $table, string $condition, string $type = 'INNER'): self
	{
		if ($type !== '')
		{
			$type = strtoupper(trim($type));

			if (!in_array($type, ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'], true))
			{
				$type = 'INNER';
			}

		}

		$this->join[] = [$table, $condition, $type];

		return $this;
	}

	/**
	 * 构造where条件
	 * 结构 [key,value,exp(and/or),type(where/having)]
	 * @param string $build_type
	 * @param string $key
	 * @param mix $value
	 * @param string $exp
	 */
	private function _build_where_having(string $build_type, string $key, $value, string $exp): void
	{
		$build_type = strtoupper($build_type);
		if (!in_array($build_type, ['WHERE', 'HAVING']))
			return;

		$this->where[] = [
			'key' => $key,
			'value' => $value,
			'exp' => $exp,
			'type' => $build_type
		];
	}

	/**
	 * having查询条件
	 * 例如：
	 *  having("name='%s'", 'hello')
	 *  having(['name' => 'hello'])
	 *  having(['num >' => 10])
	 *  having(['id in' => [10,30,33,22,12]])
	 *  having(['name' => 'hello', 'name' => 'world'], '', 'OR')
	 * @param string $key name = %s ['name' => ]
	 * @param $value
	 * @param string $exp
	 * @return BuilderBase
	 */
	public function having(string $key, $value = '', string $exp = 'AND'): self
	{
		if (is_string($key))
		{
			$key = [$key => $value];
		}

		//统一大写
		$exp = strtoupper($exp);
		//关系
		if (!in_array($exp, ['AND', 'OR']))
			$exp = 'AND';

		foreach ($key as $k => $v)
		{
			$this->_build_where_having('HAVING', $k, $v, $exp);
		}

		return $this;
	}

	/**
	 * 查询条件
	 * 例如：
	 *  where("name='%s'", 'hello')
	 *  where(['name' => 'hello'])
	 *  where(['num >' => 10])
	 *  where(['id in' => [10,30,33,22,12]])
	 *  where(['name' => 'hello', 'name' => 'world'], '', 'OR')
	 * @param string $key name = %s ['name' => ]
	 * @param string $key
	 * @param $value
	 * @param string $exp
	 * @return BuilderBase
	 */
	public function where($key, $value = '', string $exp = 'AND'): self
	{
		if (is_string($value))
			$value = trim($value);

		if (is_string($key))
		{
			$key = trim($key);
			$key = [$key => $value];
		}

		//统一大写
		$exp = strtoupper($exp);
		//关系
		if (!in_array($exp, ['AND', 'OR']))
			$exp = 'AND';

		foreach ($key as $k => $v)
		{
			if (is_string($v))
				$v = trim($v);
			$this->_build_where_having('WHERE', trim($k), $v, $exp);
		}

		return $this;
	}

	/**
	 * 排序
	 * @param unknown $value string|array
	 * 例如： 'id desc' / ['id' => 'desc']
	 * @return MysqlPdo|boolean
	 */
	public function order($value): self
	{
		if (is_string($value))
		{
			$value = preg_split('/\,/', $value);
			foreach ($value as $item)
			{
				$this->order_by[] = trim($item);
			}
		} else
		{
			foreach ($value as $key => $item)
			{
				$this->order_by[] = trim($key) . ' ' . trim($item);
			}

		}

		return $this;
	}

	/**
	 * 加锁
	 * @param string $lock
	 */
	public function lock(string $lock)
	{
		$this->lock = strtoupper($lock);
	}

	/**
	 * 分组
	 * @param mix $value
	 * @return BuilderBase
	 */
	public function group_by($value): self
	{
		if (is_string($value))
		{
			$value = preg_split('/\,/', $value);
		}

		foreach ($value as $item)
		{
			$this->group_by[] = $item;
		}

		return $this;
	}

	/**
	 * 分页查询
	 * @param string $offset
	 *  字符串'0,10'
	 *  数字0
	 * @param int $length
	 * @return BuilderBase
	 */
	public function limit($offset = '', int $length = 0): self
	{
		if (is_string($offset))
		{
			$str_limit = explode(',', $offset);
			array_filter($str_limit);

			if (!$str_limit)
				return $this;

			$offset = (int)$str_limit[0];

			if (isset($str_limit[1]))
				$length = (int)$str_limit[1];

		} else
		{
			$offset = (int)$offset;
			$length = (int)$length;
		}

		$this->limit = $length;

		if ($offset)
			$this->offset = $offset;

		return $this;
	}

	/**
	 * 新增数据
	 * @param array $data
	 * @param string $table
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function insert(array $data = [], string $table = '')
	{
		try
		{
			if ($table)
			{
				$this->from = [];
				$this->table($table);
			}

			if ($data)
				$this->data($data);

			//构建SQL
			$this->_build_insert();
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				$this->_bind_param();

				$this->statement->execute();
				return $this->connection->lastInsertId();
			}
			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}
	}

	/**
	 * 删除数据
	 * @param string $table
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function delete(string $table = '')
	{
		try
		{
			if ($table)
			{
				$this->from = [];
				$this->table($table);
			}

			//构建SQL
			$this->_build_delete();
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				$this->_bind_value();

				$this->statement->execute();
				return $this->statement->rowCount();
			}
			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}
	}

	/**
	 * 更新操作
	 * @param array $data
	 * @param string $table
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function update(array $data = [], string $table = '')
	{
		try
		{
			if ($table)
			{
				$this->from = [];
				$this->table($table);
			}

			if ($data)
				$this->data($data);

			//构建SQL
			$this->_build_update();
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				$this->_bind_value();

				//预处理绑定-更新数据
				$this->_bind_param();

				$this->statement->execute();
				return $this->statement->rowCount();
			}
			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}
	}

	/**
	 * 执行预处理操作
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function prepare(): bool
	{
		try
		{
			/*使用长连接为避免出现错误： MySQL server has gone away in 出现，在使用query前都要判断
		有没有连接，close之后再重新创建连接
		*/
			if (!$this->connection)
			{
				$this->close();
				$this->db_connect();
			}

			if ($this->statement = $this->connection->prepare($this->sql))
				return true;

			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		}

	}

	/**
	 * 打印 SQL 预处理命令
	 */
	private function _dump_statement_params(): void
	{
		$this->statement->debugDumpParams();
	}

	/**
	 * SQL查询
	 * $model->query('select * from user where id=%d and status=%d',$id,$status);
	 * @param string $sql
	 * @param array ...$params 其中参数格式：值 [值, IN, 类型]
	 * @return BuilderBase
	 * @throws \Exception
	 */
	public function query(string $sql, ...$params): self
	{
		//绑定预处理开启
		$this->is_bind_param = TRUE;

		//https://www.runoob.com/php/func-string-printf.html
		//?问号占位符  %.2f匹配符 :name命名占位符

		$pattern = '#((\?)|(:{1}[a-zA-Z_\x7f-\xff]{1}[a-zA-Z_\x7f-\xff\s\-\,:\|\#"\'=]*)|(%{1}[a-zA-Z\.\d]+))#is';

		$num = preg_match_all($pattern, $sql, $matches);

		//原生SQL
		if (!$num)
		{
			$this->sql = $sql;
			return $this;
		}

		//绑定模式需要指定参数个数一致
		if (count($params) != $num)
		{
			throw new \Exception('绑定参数个数错误');
			return false;
		}

		//构建SQL
		$k = 0;
		$this->sql = preg_replace_callback($pattern, function ($match) use (&$k)
		{
			return ':_' . $k++;
		}, $sql);

		//预处理绑定-查询条件
		foreach ($params as $key => $param)
		{
			if ($matches[$key][0] == '%')
			{ //格式化值
				$param = sprintf($matches[$key], $param);
			}

			if (isset($param[1]) && strtoupper($param[1]) == 'IN')
			{
				if (is_string($param[0]))
					$param[0] = [$param[0]];

				//替换文本
				$str_replace = '';
				for ($i = 0; $i < count($param[0]); $i++)
				{
					$str_replace .= ':_' . $key . '_' . $i . (($i == count($param[0]) - 1) ? '' : ',');
					$this->param[':_' . $key . '_' . $i] = $param;
				}
				$this->sql = str_replace(':_' . $key, $str_replace, $this->sql);
			} else
			{
				$this->param[':_' . $key] = $param;
			}

		}

		return $this;
	}

	/**
	 * SQL操作
	 * @param string $sql
	 * @param array ...$params 其中参数格式：值 [值, IN, 类型]
	 * @return $this|bool
	 * @throws \Exception
	 */
	public function execute(string $sql, ...$params)
	{
		try
		{
			//绑定预处理开启
			$this->is_bind_param = TRUE;

			//https://www.runoob.com/php/func-string-printf.html
			//?问号占位符  %.2f匹配符 :name命名占位符

			$pattern = '#((\?)|(:{1}[a-zA-Z_\x7f-\xff]{1}[a-zA-Z_\x7f-\xff\s\-\,:\|\#"\'=]*)|(%{1}[a-zA-Z\.\d]+))#is';

			$num = preg_match_all($pattern, $sql, $matches);

			//原生SQL
			if (!$num)
			{
				$this->sql = $sql;
				return $this;
			}

			//绑定模式需要指定参数个数一致
			if (count($params) != $num)
			{
				throw new \Exception('绑定参数个数错误');
				return false;
			}

			//构建SQL
			$k = 0;
			$this->sql = preg_replace_callback($pattern, function ($match) use (&$k)
			{
				return ':_' . $k++;
			}, $sql);

			//预处理绑定-查询条件
			foreach ($params as $key => $param)
			{
				if ($matches[$key][0] == '%')
				{ //格式化值
					$param = sprintf($matches[$key], $param);
				}

				if (isset($param[1]) && strtoupper($param[1]) == 'IN')
				{
					if (is_string($param[0]))
						$param[0] = [$param[0]];

					//替换文本
					$str_replace = '';
					for ($i = 0; $i < count($param[0]); $i++)
					{
						$str_replace .= ':_' . $key . '_' . $i . (($i == count($param[0]) - 1) ? '' : ',');
						$this->param[':_' . $key . '_' . $i] = $param;
					}
					$this->sql = str_replace(':_' . $key, $str_replace, $this->sql);
				} else
				{
					$this->param[':_' . $key] = $param;
				}

			}

			//预处理
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				$this->_bind_value();

				//预处理绑定-更新数据
				$this->_bind_param();

				$this->statement->execute();
				return $this->statement->rowCount();
			}
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}

	}

	/**
	 * 绑定数据
	 * @param array $params
	 * @return BuilderBase
	 */
	public function data(array $params): self
	{
		// 结构 [key => [value,where_type(in),type(pdo类型)]
		foreach ($params as $key => $param)
		{
			$this->param[':_' . $key] = $param;
		}

		return $this;
	}

	/**
	 * 预处理绑定-查询条件
	 * 结构 [key => [value,where_type(in),type(pdo类型)]
	 * @return BuilderBase
	 */
	private function _bind_param(): self
	{
		if (empty($this->param))
			return $this;

		foreach ($this->param as $key => $item)
		{
			//是否是IN
			if (is_array($item) && isset($item[1]) && strtoupper($item[1]) == 'IN')
			{
				if (is_string($item[0]))
					$item[0] = [$item[0]];

				$item_index = substr($key, -1);

				//绑定类型
				if (isset($item[2]))
				{
					$data_type = $item[2];
				} else
				{ //根据值来判断类型
					$data_type = $this->pdo_param_type($item[0][$item_index]);
				}

				$this->statement->bindValue($key, $item[0][$item_index], $data_type);

			} else
			{
				//绑定数据类型
				$data_type = $this->pdo_param_type($item);

				$this->statement->bindValue($key, $item, $data_type);
			}

		}

		return $this;
	}

	/**
	 * 获得值的pdo类型
	 *
	 * @param unknown $value
	 * @return number
	 */
	public function pdo_param_type($value)
	{
		if (is_int($value))
			return \PDO::PARAM_INT;

		if (is_bool($value))
			return \PDO::PARAM_BOOL;

		if (is_null($value))
			return \PDO::PARAM_NULL;

		return \PDO::PARAM_STR;
	}

	/**
	 * 获取数据
	 * @return BuilderBase
	 */
	public function get(): self
	{
		//构建查询SQL
		$this->_build_select();
		return $this;
	}

	/**
	 * 返回结果数据（一维关联数据）
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function fetch_row()
	{
		try
		{
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				if ($this->is_bind_param)
					$this->_bind_param();
				else
					$this->_bind_value();

				$this->statement->execute();
				$data = $this->statement->fetch();

				return $data;
			}
			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}

	}

	/**
	 * 返回结果数据（一维关联数据）
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function fetch_all()
	{
		try
		{
			if ($this->prepare())
			{
				//预处理绑定-查询条件
				if ($this->is_bind_param)
					$this->_bind_param();
				else
					$this->_bind_value();

				$this->statement->execute();
				$data = $this->statement->fetchAll();

				return $data;
			}
			return false;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		} finally
		{
			//清空查询条件
			$this->_clear();
		}
	}

	/**
	 * 获取事务当前的嵌套级别
	 * @return int
	 */
	public function get_trans_depth(): int
	{
		return $this->trans_depth;
	}

	/**
	 * 开启事务
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function trans_begin(): bool
	{
		try
		{
			//嵌套开启
			if ($this->trans_depth++ > 0)
				return true;

			$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
			$this->connection->beginTransaction();
			return true;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		}
	}

	/**
	 * 事务回滚
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function trans_rollback(): bool
	{
		try
		{
			//嵌套回滚
			if (--$this->trans_depth > 0)
				return true;

			$this->connection->rollBack();
			$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
			return true;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		}
	}

	/**
	 * 提交事务
	 * @return bool
	 * @throws \Exception\DatabaseException
	 */
	public function trans_commit(): bool
	{
		try
		{
			//嵌套提交
			if (--$this->trans_depth > 0)
				return true;

			$this->connection->commit();
			$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
			return true;
		} catch (\PDOException $e)
		{
			throw \Exception\DatabaseException::for_statement_error($e);
			return false;
		}
	}

	/**
	 * 检查连接是否可用
	 * @param string $connection
	 * @return boolean
	 */
	public function pdo_ping($connection = NULL): bool
	{
		try
		{
			$connection->getAttribute(PDO::ATTR_SERVER_INFO);
		} catch (\PDOException $e)
		{
			if (strpos($e->getMessage(), 'MySQL server has gone away') != false)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 关闭连接
	 */
	public function close(): void
	{
		$this->connection = NULL;
	}
}