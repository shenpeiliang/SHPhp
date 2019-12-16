### 目录介绍

- root入口目录，通常有index.php和其他静态文件

- app应用目录

- system框架目录

- vendor第三方扩展目录


### 配置介绍

1、目录结构

![image](https://github.com/shenpeiliang/shenPhp/blob/master/doc/2.png)


- Dev开发环境配置

- Prod生产环境配置

- Test测试环境配置

如果需要切换生产环境，需要在root/index.php中配置常量值：

define('ENVIRONMENT', 'dev');


2、数据库配置

![image](https://github.com/shenpeiliang/shenPhp/blob/master/doc/3.png)


3、其他说明

- System.php配置会覆盖框架的默认配置，框架配置文件：system/Config/Convention.php

- 配置文件的后缀名为.php


### 控制器介绍

- app/Controller下的文件为控制器文件，支持多级目录，请求访问地址格式参考CI框架方式

- 目录名和文件名以大写为开头，控制器文件以.class.php作为文件后缀

- 路由采用命名空间自动加载，如需要修改控制器的文件后缀，需要在System.php文件配置：loader['psr4']['file_suffixes'] => '.class.php'

- 控制器定义
```
<?php
namespace Controller;
use Core\Controller;

class Index extends Controller{

	public function index(){
	
		$this->assign('name', 'hello');
		$this->assign('lists', ['a', 'b', 'c', 'd']);

		//表单获取
		$this->request->post('name', '', 'htmlspecialchars');
		$this->request->post('remark', '', 'trim,remove_xss');

		//ajax返回
		$data = ['success' => 'ok', 'msg' => 'NO problem'];
		$this->response->json($data);

		//打印调试 使用第三方扩展symfony/var-dumper
		debug_dump($data);

		//模板赋值
		$this->assign('now', time());

		//模板输出
		$this->display();
		
	}
}

```

### 视图介绍

- app/View/Default，模板主题为Default，可以修改配置项'template_theme' => 'default'

- 模板引擎驱动配置项：'template_driver' => 'origin'，其他配置项参考

![image](https://github.com/shenpeiliang/shenPhp/blob/master/doc/4.png)

- 框架提供了三种模板引擎，frame是框架自定义的模板引擎，默认使用，origin为原生PHP模板引擎，还有smarty模板引擎

- 使用方法参考控制器

```
//默认会找到控制器名_方法名.模板引擎对应的文件后缀名，如果控制器上级为子目录，视图文件也是会有目录的
$this->display();

```

### session缓存介绍

- 驱动目录为system/Core/Session/Driver

- 结构图

![image](https://github.com/shenpeiliang/shenPhp/blob/master/doc/5.png)

- 默认使用Redis存储


### 模型

- app/Model下的文件为模型类文件

- 文件名以大写为开头，模型名Model.class.php

- 定义参考

```
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
	
	}
	
}

```

- 暂不支持ORM，以后会添加


### DAO

- 框架提供了Mysql的PDO操作，所有查询条件和数据更新都是预处理的，支持数据类型绑定

- 操作类参考system/Core/Database/BuilderBase.class.php

- 目录结构

![image](https://github.com/shenpeiliang/shenPhp/blob/master/doc/6.png)

- 使用参考

```
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

       /* $datas = $db->select(['id','title'])
            ->where('title like', ['%ell%', '%es%'], 'OR')
            ->order(['id' => 'desc'])
             ->get()
            ->fetch_all();*/

       /* $datas = $db->select(['id','title'])
            ->where('id >', [0, \PDO::PARAM_INT])
            ->where('id', ['hello', 'tes1'], 'or')
            ->order(['id' => 'desc'])
             ->get()
            ->fetch_all();*/

        /*$datas = $db->select(['demo.id','demo.title'])
            ->table('demo')
            ->join('demo_copy', 'demo_copy.demo_id=demo.id')
            ->where('demo.id in', [[1, 2], [2, 4]], 'or')
            ->order(['demo.id' => 'desc'])
            ->get()
            ->fetch_all();*/

        /*$datas = $db->query('select * from ' . $db->get_table_prefix() . 'demo where title=%s and id in(?)', 'hello', [[1,2,3], 'in', \PDO::PARAM_INT])
            ->fetch_all();*/

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
```

- 数据绑定类型可以指定\PDO::PARAM_INT等类型，如果不指定则自动判断绑定值的类型（注意：表单提交过来的数据整数会被转换为字符串，需要特殊指定）

### 其他

- system/Core/Cookie.class.php操作类

- system/Core/Http/Curl.class.php操作类




