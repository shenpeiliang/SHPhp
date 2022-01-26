<?php

namespace Library;

/**
 * 常用的抽奖算法
 * @author shenpeiliang
 * @date 2022-01-26 16:26:16
 */
class LuckDraw
{
    //(level为中奖等级，max为兑换券最大值，min则为最小值，weight为中奖概率=weight/sum(weight)，weight越大中奖几率越大)
    //注意其中的weight必须为整数，weight设置成0，该项抽中的几率是0，weight从小到大排序
    private $awards = [
        ['level' => 1, 'max' => 1000, 'min' => 800, 'weight' => 1],
        ['level' => 2, 'max' => 799, 'min' => 600, 'weight' => 50],
        ['level' => 3, 'max' => 599, 'min' => 400, 'weight' => 100],
        ['level' => 4, 'max' => 399, 'min' => 200, 'weight' => 500],
        ['level' => 5, 'max' => 199, 'min' => 100, 'weight' => 1000],
        ['level' => 6, 'max' => 99, 'min' => 21, 'weight' => 5000],
        ['level' => 7, 'max' => 20, 'min' => 1, 'weight' => 100000],
        ['level' => 8, 'max' => 0, 'min' => 0, 'weight' => 100000],
    ];

    public function __construct($awards_config = [])
    {
        //初始化配置
        $this->_init_config($awards_config);
    }

    /**
     * 初始化配置
     * @param unknown $awards_config
     */
    protected function _init_config($awards_config = []): volevel
    {
        $config = array_merge($this->awards, $awards_config);
        $this->awards = array_column($config, NULL, 'level');
    }

    /**
     * 返回随机的等级数
     * 统计所有权重的总和
     * 随机数以1开始（权重为0代表中奖权重为0，没意义），如果随机数小于该权重数，则直接返回结果
     * 否则权重总数减去轮询项的权重后再继续，保证最终结果
     * @param unknown $arr_level_weight [level=>weight,..)
     * @return string
     */
    protected function _get_rand_level($arr_level_weight = [])
    {
        //权重总和
        $weight_sum = array_sum($arr_level_weight);
        $ret = false;

        foreach ($arr_level_weight as $level => $weight) {
            //随机数以1开始
            $rand_num = mt_rand(1, $weight_sum);
            if ($rand_num <= $weight) {
                $ret = $level;
                break;
            } else {
                $weight_sum -= $weight;
            }
        }
        return $ret;
    }

    /**
     * 获取结果兑换券数
     * @param bool $is_return_level 是否返回等级，默认只返回中奖数
     * @return array|int
     */
    public function get_award_num(bool $is_return_level = false)
    {
        $arr_level_weight = [];

        //统计所有权重的总和并获取随机数
        foreach ($this->awards as $level_index => $item) {
            //每个等级对应的权重
            $arr_level_weight[$level_index] = $item['weight'];
        }
        //获取随机的的等级-只要有配置项肯定会有随机等级数
        $level = $this->_get_rand_level($arr_level_weight);

        //根据兑换券数量范围随机分配
        $min = $this->awards[$level]['min'];
        $max = $this->awards[$level]['max'];

        $num = mt_rand($min, $max);
        if ($is_return_level) {
            return [$level, $num];
        }
        return $num;
    }
}