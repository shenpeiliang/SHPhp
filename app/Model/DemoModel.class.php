<?php

namespace Model;

use Core\Model;

class DemoModel extends Model
{
    //表名称定义，默认使用模型名作为表明
    public function __construct(string $name = '')
    {
        parent::__construct($name);
    }

    public function get()
    {
        //默认使用master配置
        $db = $this->db();

        //$db_slave = $this->db('slave');

        //普通查询
        //$datas = $db->where('id >', 0)->get()->fetch_all();

        //普通值类型
        //$datas = $db->where('id in', [1,3])->get()->fetch_all();

        //指定类型
        //$datas = $db->select('id,title')->where('id in', [[1,3], \PDO::PARAM_INT])->get()->fetch_all();

        //还可以调试输出
        //$datas = $db->set_debug()->select('id,title')->where('id in', [[1,3], \PDO::PARAM_INT])->get()->fetch_all();


        /*$datas = $db->select(['id','title'])
            ->where('id in', [[1,3], \PDO::PARAM_INT])
            ->group_by('title')
            ->having('id >', 0)
            //->order('id desc')
            ->order(['id' => 'desc'])
             ->get()
            ->fetch_all();*/



        /*$datas = $db->select(['id','title'])
            ->where('title like', '%ell%')
            ->order(['id' => 'desc'])
            ->get()
            ->fetch_all();*/

        /*$datas = $db->select(['id','title'])
            ->where('id >', [0, \PDO::PARAM_INT])
            ->where('title', ['hello', 'tes1'], 'or')
            ->order(['id' => 'desc'])
            ->get()
            ->fetch_all();*/

      /* $datas = $db->select(['id','title'])
            ->where('title like', ['%ell%', '%es%'], 'OR')
            ->order(['id' => 'desc'])
             ->get()
            ->fetch_all();*/

       /* $datas = $db->select(['demo.id','demo.title'])
            ->table('demo')
            ->join('demo_copy', 'demo_copy.demo_id=demo.id')
            ->where('demo.id in', [[1, 2], [2, 4]], 'or')
            ->order(['demo.id' => 'desc'])
            ->get()
            ->fetch_all();*/

        /*$datas = $db->query('select * from ' . $db->get_table_prefix() . 'demo where title=%s and id in(?)', 'hello', [[1,2,3], 'in', \PDO::PARAM_INT])
            ->fetch_all();*/

        //debug_dump($datas);

        //新增
        //$ret = $db->data(['title' => 'php', 'dateline' => time()])->insert();
        //$ret = $db->insert(['title' => 'php', 'dateline' => time()]);
        //$ret = $db->insert(['title' => 'php', 'dateline' => time()], 'demo_copy');

        //删除
        //$ret = $db->where(['id' => 7])->delete();
        //$ret = $db->where('id in', [4,5])->where(['title' => 'java'])->delete();

        //更新
        //$ret = $db->where('id in', [4,5])->where(['title' => 'tes1'])->data(['dateline' => time()])->update();

        $ret = $db->execute('update ' . $db->get_table_prefix() . 'demo set dateline = ? where title=%s and id in(?)', time(), 'hello', [[1,2,3], 'in', \PDO::PARAM_INT]);

        return $ret;

        //获取一条记录
       /* /*$datas = $this->db()->select(['id','title'])
            ->where('id in', [[1,3], \PDO::PARAM_INT])
            ->order(['id' => 'desc'])
            ->get()
            ->fetch_row();*/

        return $datas;
    }
}