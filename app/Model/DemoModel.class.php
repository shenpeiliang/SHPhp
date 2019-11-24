<?php

namespace Model;

use Core\Model;

class DemoModel extends Model
{
    public function __construct(string $name = '')
    {
        parent::__construct($name);
    }

    public function get()
    {
        //普通查询
        //$datas = $this->db()->where('id >', 0)->fetch_all();

        //普通值类型
        //$datas = $this->db()->where('id in', [1,3])->fetch_all();

        //指定类型
        $datas = $this->db()->select('id,title')->where('id in', [[1,3], \PDO::PARAM_INT])->fetch_all();



        //$datas = $this->db()->test();

        return $datas;
    }
}