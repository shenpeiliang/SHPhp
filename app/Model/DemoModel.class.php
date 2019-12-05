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
        //$datas = $this->db()->select('id,title')->where('id in', [[1,3], \PDO::PARAM_INT])->fetch_all();


        /*$datas = $this->db()->select(['id','title'])
            ->where('id in', [[1,3], \PDO::PARAM_INT])
            ->group_by('title')
            ->having('id >', 0)
            //->order('id desc')
            ->order(['id' => 'desc'])
            ->fetch_all();*/

        $db = $this->db();

        /*$datas = $db->select(['id','title'])
            ->where('title like', '%ell%')
            ->order(['id' => 'desc'])
            ->fetch_all();*/

       /* $datas = $db->select(['id','title'])
            ->where('title like', ['%ell%', '%es%'], 'OR')
            ->order(['id' => 'desc'])
            ->fetch_all();*/

       /* $datas = $db->select(['id','title'])
            ->where('id >', [0, \PDO::PARAM_INT])
            ->where('id', ['hello', 'tes1'], 'or')
            ->order(['id' => 'desc'])
            ->fetch_all();*/

        $datas = $db->select(['demo.id','demo.title'])
            ->table('demo')
            ->join('demo_copy', 'demo_copy.demo_id=demo.id')
            ->where('demo.id in', [[1, 2], [2, 4]], 'or')
            ->order(['demo.id' => 'desc'])
            ->fetch_all();

        //获取一条记录
       /* /*$datas = $this->db()->select(['id','title'])
            ->where('id in', [[1,3], \PDO::PARAM_INT])
            ->order(['id' => 'desc'])
            ->fetch_row();*/

        $db->dump_statement_params();

        //$datas = $this->db()->test();

        return $datas;
    }
}