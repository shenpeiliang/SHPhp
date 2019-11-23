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
        $datas = $this->db()->where('id >', 0)->fetch_all();

        return $datas;
    }
}