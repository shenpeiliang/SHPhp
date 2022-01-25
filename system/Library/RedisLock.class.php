<?php
namespace Library;

use Core\Cache\Driver\RedisHandler;

/**
 * redis单点并发加锁
 * @author shenpeiliang
 * @date 2022-01-25 17:02:07
 */
class RedisLock{

    /**
     * 锁名前缀
     * @var unknown
     */
    private $prefix = '';

    /**
     * Redis对象
     * @var object
     */
    private $redis = NULL;

    public function __construct(string $prefix = 'lock_'){
        $this->prefix = $prefix;

        //初始化连接
        $handler = new RedisHandler();

        //连接对象
        $this->redis = $handler->get_handler();
    }

    /**
     * 加锁
     * @param string $lock_key 锁的键
     * @param int $lock_time 锁住时间，单位秒
     * @return boolean
     */
    public function lock(string $lock_key, int $lock_time = 3){
        //键名
        $lock_key = $this->prefix . $lock_key;

        //锁不住直接false
        if(!$this->redis->setnx($lock_key, 1)){
            //处理设置过期时间失败的情况：直接删锁，下一个请求就正常了
            if($this->redis->ttl($lock_key) === -1){
                $this->redis->del($lock_key);
            }
            return FALSE;
        }

        //锁n秒，注：此时可能进程中断，导致设置过期时间失败，则ttl = -1
        $this->redis->expire($lock_key, $lock_time);
        return TRUE;
    }

    /**
     * 手动解锁
     * @param string $lock_key 锁的键
     */
    public function un_lock(string $lock_key){
        //键名
        $lock_key = $this->prefix . $lock_key;

        //删除
        $this->redis->del($lock_key);
    }

}
?>
