<?php
/**
 * Created by PhpStorm.
 * User: shenpeiliang
 * Date: 2019/11/19
 * Time: 16:19
 */

namespace Core\Database\Driver;

use Core\Database\BuilderBase;

class Build extends BuilderBase
{
    public function __construct(string $db_group = 'master', string $table = '')
    {
        parent::__construct($db_group, $table);
    }
}