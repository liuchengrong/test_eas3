<?php
/**
 * Created by PhpStorm.
 * User: sinchan
 * Date: 2019/2/11
 * Time: 10:11
 */

namespace App\Infrastructure\Utility\Pool;

use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\EasySwoole\Config;

class RedisPool extends AbstractPool
{
    protected function createObject()
    {
        // TODO: Implement createObject() method.
        $redis = new RedisObject();
        $conf = Config::getInstance()->getConf('REDIS');
        if( $redis->connect($conf['host'],$conf['port'])){
            if(!empty($conf['auth'])){
                $redis->auth($conf['auth']);
            }
            $redis->select($conf['select']);
            return $redis;
        }else{
            return null;
        }
    }
}