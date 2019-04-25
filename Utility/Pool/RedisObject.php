<?php
/**
 * Created by PhpStorm.
 * User: sinchan
 * Date: 2019/2/11
 * Time: 10:13
 */

namespace App\Infrastructure\Utility\Pool;

use EasySwoole\Component\Pool\PoolObjectInterface;
use Swoole\Coroutine\Redis;
class RedisObject extends Redis implements PoolObjectInterface
{
    function gc()
    {
        // TODO: Implement gc() method.
        $this->close();
    }
    function objectRestore()
    {
        // TODO: Implement objectRestore() method.
    }
    function beforeUse(): bool
    {
        // TODO: Implement beforeUse() method.
        return true;
    }
}