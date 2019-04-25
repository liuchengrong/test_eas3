<?php
/**
 * Created by PhpStorm.
 * User: sinchan
 * Date: 2019/2/11
 * Time: 10:27
 */

namespace App\Infrastructure\Utility\Abst;

use App\Infrastructure\Utility\Pool\RedisObject;
use App\Infrastructure\Utility\Pool\RedisPool;
use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;

abstract class HttpController extends Controller
{
    public function index()
    {
        // TODO: Implement index() method.
    }
    protected function actionNotFound(?string $action): void
    {
        $this->returnJson(Code::NOT_FOUND);
    }

    /**
     * 回包
     * @param $route
     * @param $uid
     * @param $token
     * @param array $data
     * @param int $code
     */
    protected function returnJson($route,$uid,$token,$data = [],$code = 0){
        $configService = Di::getInstance()->get("configService");
        $configInfo = $configService->getSwitchConfig();
        $head = [
            'route'=>$route,
            'flag' => (int)$configInfo['flag'],// 诱导分享
            'ad_flag' => (int)$configInfo['ad_flag'],// 商业广告
            'fh_flag' => (int)$configInfo['fh_flag'],// 复活抢救开关
            'fhAdType_flag' => (int)$configInfo['fhAdType_flag'],// 复活功能广告类型 1 banner，0 视频
            'uid'=>$uid,
            'token'=>$token,
            'code'=>$code,
            'servertime'=>time()
        ];
        $res = ['head'=>$head,'body'=>$data];
//        if($route == 'http.reqGoodsList') {
//            echo "<--output-->\n";
//            var_dump($res);
//        }
        $this->response()->write(json_encode($res));
    }

    /**
     * 刷新token
     * @param $openId
     * @return mixed
     */
    protected function refreshToken($openId){
        $token_name = "req_token_" . $openId;
        $token = RedisPool::invoke(function (RedisObject $redisObject) use($token_name) {
            $token = uniqid(mt_rand().'_', true);
            $redisObject->setex($token_name, 10800, $token);
            return $token;
        });
        return $token;
    }

    /**
     * 校验
     * @param $param
     * @return int
     */
    protected function check($param)
    {
        $header = $param['head'];
        // 缺少uid或mi，返回错误
        if(empty($header['uid']) || empty($header['mi'])){
            return 10010000;
        }else{
            $token_name = "req_token_" . $header['uid'];
            $token = RedisPool::invoke(function (RedisObject $redisObject) use($token_name) {
                $token = $redisObject->get($token_name);
                return $token;
            });
            // 获取redis中玩家的token，并进行验证
            if(empty($token)){
                return 10010001;
            }
            $data['body'] = $param['body'];
            if(sizeof($data['body'])>0){
                $jsonBody = json_encode($data['body']);
                $jsonBody = str_replace("\\","",$jsonBody);
            }else{
                $jsonBody = "{}";
            }
            $encodeStr = "token:{$token}&uid:{$header['uid']}&key:jdqs&data:{$jsonBody}";
            $code = hash_hmac('sha1',$encodeStr,"jdqs");
            $mi = $abc=substr($code,5);
            if($header['mi']!=$mi){
                return 10010002;
            }
        }
        return 0;
    }

    protected function createChannelLog($uid, $type)
    {
        $memberChannelLogService = Di::getInstance()->get("memberChannelLogService");
        switch ($type) {
            // 普通分享
            case 1:
                $memberChannelLogService->createLog($uid, 5);
                break;
            // 视频
            case 2:
                $memberChannelLogService->createLog($uid, 7);
                break;
            // 群分享
            case 4:
                $memberChannelLogService->createLog($uid, 4);
                break;
            // 其他分享
            case 5:
                $memberChannelLogService->createLog($uid, 6);
                break;
            default:
                break;
        }
    }

}