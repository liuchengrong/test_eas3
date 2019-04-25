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
                PoolManager::getInstance()->getPool(MysqlPool::class)->preLoad(5);//最小创建数量
            }
            if ($workerId == 1){
                Timer::getInstance()->loop(1 * 1000,function (){
                    for ($i=0;$i<100;$i++){
                        $indata = [
                            'nickname'=> 'a'.$i,
                            'icon_url'=> 'https://www.test.wn/a'.$i.'.png',
                            'account'=> 'a'.$i,
                            'password'=> md5('a'.$i),
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

                        if ($ret>300000){
                            Timer::getInstance()->clearAll();
                            break;
                        }


                        TaskManager::async(function (){
                            for ($o=0;$o<100;$o++){
                                $indata = [
                                    'nickname'=> 'a'.$o,
                                    'icon_url'=> 'https://www.test.wn/a'.$o.'.png',
                                    'account'=> 'a'.$o,
                                    'password'=> md5('a'.$o),
                                    'status'=> 1,
                                    'created_at'=> time(),
                                    'updated_at'=> time(),
                                ];
                                $ret = MysqlPool::invoke(function (MysqlObject $db) use ($indata){
                                    $gmember = new GameMemberModel($db);
                                    $ret = $gmember->inster($indata);
                                    return $ret;
                                });
                                echo 'aa'.$ret;echo "\n";
                                if ($ret > 300000) {
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