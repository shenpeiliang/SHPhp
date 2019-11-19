<?php
namespace Core\Session\Driver;
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/18
 * Time: 17:05
 */
class RedisHandler implements \SessionHandlerInterface
{
	//驱动对象
	public $driver = NULL;

	//KEY前缀
	private $prefix = '';

	//指定保存的库
	private $db = '';

	public function __construct()
	{
		$config = convention_config('session');
		$this->prefix = $config['prefix'];
		$this->db = $config['db'];
	}

	/**
	 * 当session_start()函数被调用的时候该函数被触发
	 */
	public function open($save_path, $session_id)
	{
		//创建连接
		$this->driver = new \Core\Cache\Driver\RedisHandler();

		//是否指定数据库
		if ($this->db)
			$this->driver->get_handler()->select($this->db);

		return true;
	}

	/**
	 * 关闭当前session 当session关闭的时候该函数自动被触发
	 */
	public function close()
	{
		return true;
	}

	/**
	 * 但是在session_start()函数调用的时候先触发open函数，再触发该函数
	 */
	public function read($session_id)
	{
		$key = $this->prefix . $session_id;

		//读取当前sessionid下的data数据
		$res = $this->driver->get($key . '.data');

		//读取完成以后 更新时间，说明已经操作过session
		$this->driver->set($key, 'last_time', time());

		return $res;

	}

	/**
	 * 将session的数据写入到session的存储空间内。
	 * 当session准备好存储和关闭的时候调用该函数
	 */
	public function write($session_id, $session_data)
	{
		$key = $this->prefix . $session_id;

		return $this->driver->save($key, array('last_time' => time(), 'data' => $session_data));

	}

	/**
	 * 销毁session
	 */
	public function destroy($session_id)
	{
		$key = $this->prefix . $session_id;

		return $this->driver->delete($key, 'data');
	}

	/**
	 * 清除垃圾session，也就是清除过期的session。
	 * 该函数是基于php.ini中的配置选项
	 * session.gc_divisor, session.gc_probability 和 session.gc_lifetime所设置的值的
	 */
	public function gc($maxlifetime)
	{
		/*
		 * 取出所有的 带有指定前缀的键
		 */
		$keys = $this->driver->keys($this->prefix . '*');

		$now = time(); //取得现在的时间
		foreach ($keys as $key)
		{
			//取得当前key的最后更新时间
			$last_time = $this->driver->get($key, 'last_time');
			/*
			 * 查看当前时间和最后的更新时间的时间差是否超过最大生命周期
			 */
			if (($now - $last_time) > $maxlifetime)
			{
				//超过了最大生命周期时间 则删除该key
				$this->driver->delete($key);
			}

		}

	}
}