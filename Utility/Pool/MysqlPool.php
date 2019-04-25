<?php
/**
 * Created by PhpStorm.
 * User: sinchan
 * Date: 2018/12/14
 * Time: 21:03
 */

namespace App\Utility\Pool;


use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\EasySwoole\Config;

class MysqlPool extends AbstractPool
{
    protected function createObject()
    {
        $conf = Config::getInstance()->getConf("MYSQL");
        $dbConf = new \EasySwoole\Mysqli\Config($conf);
        return new MysqlObject($dbConf);
    }
}