<?php

namespace Core\File;

use Core\File\Driver\Git;
use Core\File\Driver\Sha1;
use Core\File\Driver\Time;
use Core\File\VersionInterface;

/**
 * 工厂类
 * @author shenpeiliang
 * @date 2022-01-28 10:34:55
 */
class VersionFactory
{
    /**
     * 驱动
     * @var string[]
     */
    public $valid_drivers = [
        'Git' => Git::class,
        'Time' => Time::class,
        'Sha1' => Sha1::class
    ];

    /**
     * 创建驱动
     * @param string $driver
     * @return VersionInterface
     */
    public function create(string $driver = 'Git'): VersionInterface
    {
        //默认驱动
        if (!array_key_exists($driver, $this->valid_drivers))
            $driver = 'Git';

        $driver_object = $this->valid_drivers[$driver];

        return new $driver_object();
    }
}