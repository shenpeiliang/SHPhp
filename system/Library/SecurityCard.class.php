<?php

namespace Library;

/**
 * 密保卡安全验证
 * @author shenpeiliang
 * @date 2022-01-26 11:03:07
 */
class SecurityCard
{
    public $err;

    /**
     * 下标长度
     * @var unknown
     */
    public $length = 9;

    /**
     * 随机数长度
     * @var unknown
     */
    public $num = 3;

    /**
     * 是否大写
     * @var unknown
     */
    public $is_upper = true;

    /**
     * 密保卡下标
     * @var unknown
     */
    public $key;

    public function __construct(array $option = [])
    {
        //初始化配置
        $this->_init($option);
    }

    /**
     * 初始化配置
     * @param array $option
     */
    private function _init(array $option): void
    {
        if (isset($option['length']) && is_numeric($option['length']))
            $this->length = $option['length'];

        if (isset($option['is_upper']))
            $this->is_upper = $option['is_upper'];

        if (isset($option['num']) && is_numeric($option['num']))
            $this->num = $option['num'];

        //横坐标
        $key = "abcdefghilkmnopqrstuvwxyz";
        if (isset($option['key'])) {
            $key = $option['key'];
            if (is_array($key))
                $key = implode('', $key);
        }
        if ($this->is_upper)
            $key = strtoupper($key);

        $this->key = mb_substr($key, 0, $this->length, 'UTF-8');
    }

    /**
     * 创建卡片，用于生成xls表格文件
     * @return array
     * [
     *  'a' => [1, 2, 3, 4, 5],
     *  'b' => [1, 2, 3, 4, 5]
     * ]
     */
    public function create(): array
    {
        $result = [];
        for ($i = 0; $i < $this->length; $i++) {
            //横坐标字母
            $code = $this->key[$i];

            //纵坐标数字，从0开始
            for ($j = 0; $j < $this->length; $j++) {
                //坐标值
                $rand_num = $this->get_rand_num();
                $result[$code][$j] = $rand_num;
            }
        }

        return $result;
    }

    /**
     * 创建一个字母和数字的坐标
     * @return array
     * [a,1,b,2]
     */
    public function entry(): array
    {
        return [
            $this->key[rand(0, $this->length)],
            rand(0, $this->length),
            $this->key[rand(0, $this->length)],
            rand(0, $this->length)
        ];
    }

    /**
     * 检查密保卡是否匹配
     * @param array $keys 对比的密保卡，每个用户应存有一份
     * @param string $code B8C6
     * @param string $input 6位数 123456
     * @return boolean
     */
    public function check(array $keys = [], string $code = '', $input = ''): bool
    {
        if (!preg_match('/^([A-Za-z]\d+){2}$/', $code)) {
            $this->err = '参数code规则不匹配';
            return false;
        }

        $flag = preg_match_all('/([A-Za-z]\d+)/', $code, $matches);
        if (!$flag) {
            $this->err = '参数code规则不匹配';
            return false;
        }

        if (strlen($input) != ($this->num) * 2) {
            $this->err = '参数input规则不匹配';
            return false;
        }

        //坐标
        $coordinate_1 = mb_substr($matches[0][0], 0, 1, 'utf-8');
        $coordinate_1_1 = mb_substr($matches[0][0], 1, mb_strlen($matches[0][0]), 'utf-8');
        $coordinate_2 = mb_substr($matches[1][1], 0, 1, 'utf-8');
        $coordinate_2_1 = mb_substr($matches[1][1], 1, mb_strlen($matches[1][1]), 'utf-8');

        if (!isset($keys[$coordinate_1][$coordinate_1_1]) || !isset($keys[$coordinate_2][$coordinate_2_1])) {
            $this->err = '参数code不存在';
            return false;
        }

        //对比结果
        if ($input == $keys[$coordinate_1][$coordinate_1_1] . $keys[$coordinate_2][$coordinate_2_1])
            return true;

        return false;
    }

    /**
     * 获取指定长度的随机数
     * @return string
     */
    protected function get_rand_num(): string
    {
        $rand = [];

        //坐标值长度
        for ($i = 0; $i < $this->num; $i++) {
            //第一位不能为0
            if ($i == 0) {
                $rand[] = rand(1, 9);
            } else {
                $rand[] = rand(0, 9);
            }
        }
        return implode('', $rand);
    }

}