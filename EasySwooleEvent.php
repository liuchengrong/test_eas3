<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Model\Member\GameMemberModel;
use App\Utility\Pool\MysqlObject;
use App\Utility\Pool\MysqlPool;
use App\Utility\Pool\RedisPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        PoolManager::getInstance()->register(MysqlPool::class, Config::getInstance()->getConf('MYSQL.POOL_MAX_NUM'));
        PoolManager::getInstance()->register(RedisPool::class, Config::getInstance()->getConf('REDIS.POOL_MAX_NUM'));

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        ################### mysql 热启动   #######################
        $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId) {
            if ($server->taskworker == false) {
                //每个worker进程都预创建连接
                PoolManager::getInstance()->getPool(MysqlPool::class)->preLoad(10);//最小创建数量
            }
            if ($workerId < 8){
                //每秒钟运行一次循环  开启一次异步任务   实现100万条记录插入  估计只需要几分钟
                Timer::getInstance()->loop(1 * 1000,function (){
                    for ($i = 0;$i < 50;$i++){
                        $indata = [
                            'nickname'=> 'a'.$i,
                            'avatar'=> 'https://www.test.wn/a'.$i.'.png',
                            'openid'=> 'a'.$i,
                            'status'=> 1,
                            'created_at'=> time(),
                            'updated_at'=> time(),
                        ];
                        $ret = MysqlPool::invoke(function (MysqlObject $db) use ($indata){
                            $gmember = new GameMemberModel($db);
                            $ret = $gmember->inster($indata);
                            return $ret;
                        });
                        echo $ret;echo "\n";
                        if ($ret>2000000){
                            Timer::getInstance()->clearAll();
                            break;
                        }
                        TaskManager::async(function (){
                            for ($o = 0;$o < 50;$o++){
                                $indata = [
                                    'nickname'=> 'a'.$o,
                                    'avatar'=> 'https://www.test.wn/a'.$o.'.png',
                                    'openid'=> 'a'.$o,
                                    'status'=> 1,
                                    'created_at'=> time(),
                                    'updated_at'=> time(),
                                ];
                                $ret = MysqlPool::invoke(function (MysqlObject $db) use ($indata){
                                    $gmember = new GameMemberModel($db);
                                    $ret = $gmember->inster($indata);
                                    return $ret;
                                });
                                echo 'a'.$ret;echo "\n";
                                if ($ret > 2000000) {
                                    Timer::getInstance()->clearAll();
                                    break;
                                }
                            }
                        });

                    }

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