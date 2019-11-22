<?php
namespace Core\Database;

/**
 * @author shenpeiliang
 * 20170829
 * * 标注：此类的条件、更新使用到了预处理绑定，因此查询时要统一占位符，另更新条件中只能使用命名占位符
 * where('id = :id or sess_val = :val')
 * where(array('id = :id', 'sess_val = :val'))->bind(array(':id'=>17,':val'=>'c2'))
 * where(array('id = ?', 'sess_val = ?'))->bind(array(1=>17,2=>'c2'))
 * field('id,name') / field(array('id','name'))
 * order('id desc,expire asc') / order(array('id desc','expire asc'))
 * 更新set(array('sess_val'=>time(),'sess_key'=>30))->update()
 * 删除where(array('id = ?', 'sess_val = ?'))->bind(array(1=>17,2=>'c2'))->delete()
 * （只支持单条保存）插入set(array('sess_val'=>time(),'sess_key'=>30))->insert()
 *
 * 单例：
 * $pdo = MysqlPdo::getInstance($config);
 *
 *
 * 事务支持
 * 自动方式
 * $this->transStart();
 * ..
 * $this->transComplete();
 *
 * 手动方式
 * $this->transBegin();
 * $this->transRollback();
 * $this->transCommit();
 * Class PdoHandler
 * @package Core\Database\Driver
 */
class BuilderBase
{
	private $connection = NULL;    //mysql对象
	private $statement = NULL;    //预处理对象
	private $charset = ''; //编码
	private $dsn = ''; //dsn
	private $prefix = '';    //表前缀
	private $debug = FALSE;    //是否开启调试模式
	private $persistent = FALSE; //是否持久连接

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
	 * value可以为字符串或数组，格式为val=>type， type可以是pdo类型参数，默认为空 如：[3 => \PDO::PARAM_INT]
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
	 * 分组查询
	 * @var array
	 */
	protected $having = [];

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
	 * 更新的值
	 * @var unknown
	 */
	protected $data = [];
	protected $param_data = [];

	/**
	 * sql
	 * @var string
	 */
	protected $sql = '';

	//预处理绑定-查询
	protected $param = [];

	/**
	 * 事务状态
	 * @var unknown
	 */
	protected $trans_status = TRUE;

	/**
	 * 记录当前事务是否回滚
	 * @var unknown
	 */
	protected $rollbacked = FALSE;

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
	 * 配置
	 */
	private function init($db_group)
	{
		//配置
		$config = config('database.' . $db_group);

		$this->prefix = isset($config['prefix']) ? $config['prefix'] : '';

		$this->charset = isset($config['charset']) ? $config['charset'] : '';

		$this->persistent = isset($config['persistent']) ? $config['persistent'] : FALSE;

		$this->dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'];

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
				\PDO::ATTR_PERSISTENT => $this->persistent, //是否持久化连接(使用连接池不能使用长连接，会不断地创建连接从而导致服务器超载)
				\PDO::ATTR_EMULATE_PREPARES => FALSE,//启用或禁用预处理语句的模拟 ;使用此设置强制PDO总是模拟预处理语句（如果为 TRUE ），或试着使用本地预处理语句（如果为 FALSE ）
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset, //编码类型
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,//设置默认的提取模式 ;返回一个索引为结果集列名的数组
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING //抛出错误异常
			);

			$this->connection = new \PDO($this->dsn, $this->config['user'], $this->config['passwd'], $options);

		} catch (\PDOException $e)
		{
			return $this->_err($e->getMessage());
		}
	}

	/**
	 * 获取数据库连接
	 * @return \PDO
	 */
	public function get_connection():\PDO
	{
		return $this->connection;
	}

	/**
	 * 获取预处理查询的sql语句
	 * @return string
	 */
	public function get_sql():string
	{
		return $this->sql;
	}

	/**
	 * 获取预处理绑定参数
	 * @return string|array
	 */
	public function get_param():array
	{
		return $this->param;
	}

	/**
	 * 获取预处理绑定参数 Update
	 * @return string|array
	 */
	public function get_data_param():array
	{
		return $this->param_data;
	}

	/**
	 * 组建绑定预处理 Select
	 * @return MysqlPdo
	 */
	private function _bind_value_for_where():self
	{
		if (empty($this->where))
			return $this;

		$count = count($this->where);
		for ($i = 0; $i < $count; $i++)
		{
			if (is_array($this->where[$i]['value']))
				$this->statement->bindValue(':' . $i . ':' . $this->where[$i]['key'], $this->where[$i]['value'][0], $this->where[$i]['value'][1]);
			else
				$this->statement->bindValue(':' . $i . ':' . $this->where[$i]['key'], $this->where[$i]['value']);
		}

		return $this;
	}

	/**
	 * 组建绑定预处理 Update
	 * @return MysqlPdo
	 */
	private function _bind_value_for_where_data():self
	{
		if (empty($this->param_data))
			return $this;
		foreach ($this->param_data as $key => $val)
		{
			$this->statement->bindValue($key, $val);
		}
		return $this;
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
			$table .= $this->prefix . $this->from . ' AS ' . $this->from . $fill;
		}

		return $table;
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
		return ' ' . $this->lock . ' ';
	}

	/**
	 * 组建查询sql
	 */
	private function _build_select():string
	{
		return 'SELECT FROM '
		. $this->_build_having()
		. $this->_build_table()
		. $this->_build_where()
		. $this->_build_order()
		. $this->_build_group_by()
		. $this->_build_having()
		. $this->_build_limit()
		. $this->_build_lock();
	}

	/**
	 * 组建更新sql
	 */
	private function _build_update():string
	{
		return $this->sql = 'UPDATE FROM '
			. $this->_build_table()
			. $this->build_set()
			. $this->_build_where();
	}

	/**
	 * 组建删除sql
	 */
	private function _build_delete():string
	{
		return $this->sql = 'DELETE FROM '
			. $this->_build_table()
			. $this->_build_where();
	}

	/**
	 * 组建插入sql
	 * @return string
	 */
	private function _build_insert():string
	{
		$this->sql = 'INSERT INTO '
			. $this->_build_table()
			. $this->build_insert();
	}

	/**
	 * 构建插入sql
	 * @return string
	 */
	protected function build_insert()
	{
		if (!$this->data)
		{
			return false;
		}
		$insert = ' ( ';
		$count = count($this->data);
		$i = 0;
		//字段名
		$insert_key = '';
		//字段值
		$insert_val = '';
		foreach ($this->data as $key => $value)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$insert_key .= ' ' . $key . $fill;
			$insert_val .= ' :' . $key . $fill;
			//绑定预处理
			$this->param_data [':' . $key] = $value;
			$i++;
		}
		$insert .= $insert_key . ' ) VALUE (' . $insert_val;
		$insert .= ' ) ';
		return $insert;
	}

	/**
	 * 构建where查询
	 * @return string
	 */
	protected function _build_where():string
	{
		if (!$this->where)
		{
			return '';
		}
		$where = ' WHERE ';
		$count = count($this->where);

		$where_and = [];
		$where_or = [];

		//转换 name='a' 为 name=:0:name
		for ($i = 0; $i < $count; $i++)
		{
			if ($this->where[$i]['type'] != 'WHERE')
				continue;

			//默认算数符
			$where_definition = '=';

			//一个或多个空格分隔作为条件
			$where_key = strpos('/\s+/', trim($this->where[$i]['key']));
			if (isset($where_key[1]))
				$where_definition = strtoupper($where_key[1]);

			if ($this->where[$i]['exp'] == 'OR')
				$where_or[] = $this->where[$i]['key'] . $where_definition . $this->_build_where_definition($where_definition, $i);
			else
				$where_and[] = $this->where[$i]['key'] . $where_definition . $this->_build_where_definition($where_definition, $i);

		}

		if ($where_and)
			$where .= implode(' AND ', $where_and);

		if ($where_or)
			$where .= ' AND (' . implode(' OR ', $where_or) . ')';

		return $where;
	}

	/**
	 * 构造查询条件 - 预处理
	 * @param string $where_definition
	 * @param int $i where索引
	 * @return string
	 */
	private function _build_where_definition(string $where_definition, int $i): string
	{
		$bind_value = $this->where[$i]['value'];

		if ($where_definition == 'LIKE')
		{
			$match_left = $match_right = '';
			if ($bind_value[0] == '%')
			{
				$bind_value = substr($bind_value, 1);
				$match_left = '%';
			}

			if ($bind_value[mb_strlen($bind_value) - 1] == '%')
			{
				$bind_value = substr($bind_value, 0, -1);
				$match_right = '%';
			}

			//修改绑定值
			$this->where[$i]['value'] = $bind_value;

			return $match_left . ':' . $i . ':' . $this->where[$i]['key'] . $match_right;
		} elseif ($where_definition == 'IN')
		{
			return '(:' . $i . ':' . implode(',', $this->where[$i]['key']) . ')';
		} else
		{
			return ':' . $i . ':' . $this->where[$i]['key'];
		}
	}

	/**
	 * 构建having查询
	 * @return string
	 */
	protected function _build_having():string
	{
		if (!$this->where)
		{
			return '';
		}
		$having = ' HAVING ';
		$count = count($this->where);

		$having_and = [];
		$having_or = [];

		//转换 name='a' 为 name=:0:name
		for ($i = 0; $i < $count; $i++)
		{
			if ($this->where[$i]['type'] != 'HAVING')
				continue;

			//默认算数符
			$having_definition = '=';

			//一个或多个空格分隔作为条件
			$having_key = strpos('/\s+/', trim($this->where[$i]['key']));
			if (isset($having_key[1]))
				$having_definition = strtoupper($having_key[1]);

			if ($this->where[$i]['exp'] == 'OR')
				$having_or[] = $this->where[$i]['key'] . $having_definition . ':' . $i . ':' . $this->where[$i]['key'];
			else
				$having_and[] = $this->where[$i]['key'] . $having_definition . ':' . $i . ':' . $this->where[$i]['key'];

		}

		if ($having_and)
			$having .= implode(' AND ', $having_and);

		if ($having_or)
			$having .= ' AND (' . implode(' OR ', $having_or) . ')';

		return $having;
	}

	/**
	 * 构建更新sql
	 * @return string
	 */
	protected function build_set():string
	{
		if (!$this->data)
		{
			return false;
		}
		$set = ' SET ';
		$count = count($this->data);
		$i = 0;
		foreach ($this->data as $key => $value)
		{
			$fill = ($i == ($count - 1)) ? ' ' : ' , ';
			$set .= ' ' . $key . ' = :' . $key . $fill;
			//绑定预处理
			$this->param_data [':' . $key] = $value;
			$i++;
		}
		return $set;
	}

	/**
	 * 构建分组查询
	 * @return string
	 */
	protected function _build_group_by():string
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
	protected function _build_order():string
	{
		if (!$this->order)
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
	 * 更新字段信息
	 * @param unknown $value
	 * @return MysqlPdo
	 */
	public function set($value):self
	{
		if (empty($value) || !is_array($value))
			return false;
		foreach ($value as $key => $value)
		{
			$this->data[$key] = $value;
		}
		return $this;
	}

	/**
	 * 查询字段信息
	 * @param string $value string|array
	 * @return MysqlPdo
	 */
	public function select($value):self
	{
		if (is_array($value))
		{
			foreach ($value as $item)
			{
				$this->field[] = $item;
			}
		} else
		{
			$this->field[] = $value;
		}
		return $this;
	}

	/**
	 * 表名设置
	 * @param unknown $value string
	 * @return MysqlPdo
	 */
	public function table($value):self
	{
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
	public function join(string $table, string $condition, string $type = 'INNER'):self
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
	 * @param string $value
	 * @param string $exp
	 * @return array
	 */
	private function _build_where_having(string $build_type, string $key, string $value, string $exp): array
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
	 * @param string $value
	 * @param string $exp
	 * @return BuilderBase
	 */
	public function having(string $key, string $value = '', string $exp = 'AND'):self
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
	 * @param string $value
	 * @param string $exp
	 * @return BuilderBase
	 */
	public function where(string $key, string $value = '', string $exp = 'AND'):self
	{
		if (is_string($key))
		{
			$key = trim($key);
			$value = trim($value);
			$key = [$key => $value];
		}

		//统一大写
		$exp = strtoupper($exp);
		//关系
		if (!in_array($exp, ['AND', 'OR']))
			$exp = 'AND';

		foreach ($key as $k => $v)
		{
			$this->__build_where_having('WHERE', trim($k), trim($v), $exp);
		}

		return $this;
	}

	/**
	 * 查询条件
	 * @param $value array
	 */
	public function bind(array $value):self
	{
		if (!is_array($value) || empty($value))
			return $this;
		foreach ($value as $key => $val)
		{
			if (!is_int($key))
			{//非问号占位符
				if (strpos($key, ':') === false)
				{//是否以':'开头
					$key = ':' . trim($key);
				}
			}
			$this->param [$key] = $val;
		}
		return $this;
	}

	/**
	 * 排序
	 * @param unknown $value string|array
	 * 例如： 'id desc' / ['id' => 'desc']
	 * @return MysqlPdo|boolean
	 */
	public function order($value):self
	{
		if (is_array($value))
		{
			foreach ($value as $key => $item)
			{
				$this->order_by[] = $key . ' ' . strtoupper($item);
			}
		} else
		{
			$this->order_by[] = $value;
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
	 * @param string $by
	 * @return BuilderBase
	 */
	public function group_by(string $by):self
	{
		if (is_string($by))
		{
			if (strpos($by, ','))
			{
				$by = explode(',', $by);
				array_filter($by);
			} else
			{
				$by = [$by];
			}

		}

		foreach ($by as $item)
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
	public function limit($offset = '', int $length = 0):self
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
	 * @return boolean|int
	 */
	public function insert()
	{
		$this->_build_insert();
		if ($this->execute())
		{
			return $this->connection->lastInsertId();
		}
		return false;
	}

	/**
	 * 删除数据
	 * @return boolean|int
	 */
	public function delete()
	{
		$this->_build_delete();
		if ($this->execute())
		{
			return $this->statement->rowCount();
		}
		return false;
	}

	/**
	 * 更新操作
	 * @return boolean|int
	 */
	public function update()
	{
		$this->_build_update();
		if ($this->execute())
		{
			return $this->statement->rowCount();
		}
		return false;
	}

	/**
	 * 执行操作
	 * @return boolean
	 */
	public function execute(): bool
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
			{
				//预处理绑定-查询条件
				$this->_bind_value_for_where();

				//预处理绑定-更新、插入
				$this->_bind_value_for_where_data();

				//执行操作
				return $this->statement->execute();
			}
		} catch (\PDOException $e)
		{
			return $this->_err($e->getMessage());
		}

	}

	/**
	 * 返回结果数据（一维关联数据）
	 * @return Ambigous <multitype:, mixed>|boolean
	 */
	public function fetch_row()
	{
		$this->_build_select();
		if ($this->execute())
		{
			return $this->statement->fetch();
		}
		return false;
	}

	/**
	 * 返回结果数据（一维关联数据）
	 * @return Ambigous <multitype:, mixed>|boolean
	 */
	public function fetch_all()
	{
		$this->_build_select();
		if ($this->execute())
		{
			return $this->statement->fetchAll();
		}
		return false;
	}

	/**
	 * 事务状态
	 * @return unknown
	 */
	public function trans_status(): bool
	{
		return $this->trans_status;
	}

	/**
	 * 事务开启-自动
	 * @return MysqlPdo
	 */
	public function trans_start()
	{
		$this->connection->beginTransaction();
	}

	/**
	 * 事务提交 -自动
	 * @return MysqlPdo
	 */
	public function trans_complete():bool
	{
		if ($this->trans_status === FALSE)
		{
			$this->connection->rollBack();
			return false;
		}
		$this->connection->commit();
		return true;
	}

	/**
	 * 开启事务 - 手动
	 * @return boolean
	 */
	public function trans_begin():bool
	{
		if ($this->trans_depth++ > 0)
		{
			return true;
		}
		$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
		$this->connection->beginTransaction();
		return true;
	}

	/**
	 * 事务回滚 - 手动
	 * @return boolean
	 */
	public function trans_rollback():bool
	{
		if (--$this->trans_depth > 0)
		{
			$this->rollbacked = TRUE;
			return true;
		}
		$this->connection->rollBack();
		$this->rollbacked = FALSE;
		$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
		return true;
	}

	/**
	 * 提交事务 - 手动
	 * @return boolean
	 */
	public function trans_commit():bool
	{
		if (--$this->trans_depth > 0)
		{
			$this->rollbacked = TRUE;
			return true;
		}
		if ($this->rollbacked)
		{
			$this->connection->rollBack();
			$result = FALSE;
		} else
		{
			$this->connection->commit();
			$result = TRUE;
		}
		$this->rollbacked = FALSE;
		$this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
		return $result;
	}

	/**
	 * 检查连接是否可用
	 * @param string $connection
	 * @return boolean
	 */
	public function pdo_ping($connection = NULL):bool
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
	public function close():void
	{
		$this->connection = NULL;
	}

	/**
	 * 输出错误信息
	 * @param unknown $msg
	 */
	private function _err($msg): bool
	{
		if ($this->debug)
		{
			echo $msg;
		}

		return false;
	}
}