<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            //如何避免定时器因为进程重启而丢失
            //例如在第一个进程 添加一个10秒的定时器
            if ($workerId == 0) {

                Timer::getInstance()->loop(10 * 1000, function () {
                    echo 'aaaaa';echo time();echo '\n';
                    // 从数据库，或者是redis中，去获取下个就近10秒内需要执行的任务
                    // 例如:2秒后一个任务，3秒后一个任务 代码如下
                    Timer::getInstance()->loop(2 * 1000, function () {
                        //为了防止因为任务阻塞，引起定时器不准确，把任务给异步进程处理
                        echo 'bbbbb';echo '\n';
                    });
                    Timer::getInstance()->after(3 * 1000, function () {
                        //为了防止因为任务阻塞，引起定时器不准确，把任务给异步进程处理
                        echo 'ccccc';echo '\n';
                    });
                });
            }
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}