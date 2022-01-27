<?php

namespace Library;

use Core\Cache\Driver\RedisHandler;

/**
 * 限流操作
 * 在规定时间内（有效缓存时间）累计操作的次数不能超过配置次数，超过次数需要等待（限制一段时间后才可以操作）
 * @author shenpeiliang
 * @date 2022-01-27 11:06:26
 */
class Limiter
{
    /**
     * 缓存key
     * @var unknown
     */
    private $cache_key = null;

    /**
     * 当前累计次数
     * @var unknown
     */
    private $count = 0;

    /**
     * 开始时间 用于判断是否过时
     * @var unknown
     */
    private $start_time = 0;

    /**
     * 配置
     * @var unknown
     */
    private $config = [];

    /**
     * Redis对象
     * @var object
     */
    private $redis = NULL;

    public function __construct(array $option = [])
    {
        //初始化配置
        $this->_init($option);

        //初始化连接
        $handler = new RedisHandler();

        //连接对象
        $this->redis = $handler->get_handler();
    }

    /**
     * 初始化配置
     * @param array $option
     */
    private function _init(array $option = []): void
    {
        //默认配置
        $this->config = [
            'prefix' => 'limiter_',
            'id' => '', //唯一标识
            'threshold' => 10,//计数阈值 次数
            'keep_time' => 60,// 计数保持时间 - 未达到阈值时 缓存时间 秒
            'exceed_keep_time' => 60 * 10// 计数保持时间 - 达到或超出阈值时 缓存时间 秒
        ];
        $this->config = array_merge($this->config, $option);

        if (!$this->config['id'])
            $this->config['id'] = session_id();

        $this->cache_key = $this->config['prefix'] . $this->config['id'];
    }

    /**
     * 是否超出阀值
     * @return boolean
     */
    public function is_exceeded(): bool
    {
        $this->_stat_data();
        return $this->count >= $this->config['threshold'];
    }

    /**
     * 清除计数
     */
    public function clear(): void
    {
        $this->redis->del($this->cache_key);
    }

    /**
     * 统计最新的访问次数记录
     * count累计数 start_time开始时间
     */
    private function _stat_data(): void
    {
        //读取缓存
        $data = unserialize($this->redis->get($this->cache_key));

        $count = 0;
        $now = time();

        //开始时间
        $start_time = $now;
        if ($data) {
            $count = isset($data['count']) ? (int)$data['count'] : 0;
            $start_time = ($count && isset($data['start_time'])) ? (int)$data['start_time'] : $now;
            //是否超时 true:重新计数  （当前时间-开始时间）> 缓存时间
            if ($now - $start_time > ($count >= $this->config['threshold'] ? $this->config['exceed_keep_time'] : $this->config['keep_time'])) {
                $count = 0;
                $start_time = $now;
            }
        }
        $this->count = $count;
        $this->start_time = $start_time;
    }

    /**
     * 计数加1
     * @return int
     */
    public function increase(): int
    {
        //获取最新的访问次数记录
        $this->_stat_data();

        $this->count++;
        //超过阀值则变更缓存时间    当前计数统计开始时间也进行变更
        if ($this->count >= $this->config['threshold']) {
            $time = $this->config['exceed_keep_time'] ?: $this->config['keep_time'];
            //超出后都要更新，目的是让用户暂停操作
            $this->start_time = time();
        } else {
            $time = $this->config['keep_time'];
        }
        //缓存数据
        $data = [
            'count' => $this->count,
            'start_time' => $this->start_time
        ];
        $this->redis->set($this->cache_key, serialize($data), $time);
        return $this->count;
    }
}