<?php
namespace app\worker\services;

use think\Db;
use app\model\Worker;


class LoginServices extends Base
{


    public static function login($param)
    {
		try {
			$worker = Worker::where(['username'=>$param['username'],'password'=>$param['password']])->find();
			if(empty($worker['id'])){
				 throw new \Exception("用户或密码错误");
			}
			$token = md5(time().$worker['id']);
			cache($token, $worker->toArray(), 3600*24*90);
			return ['code'=>0,'data'=>['token'=>$token]];
        }catch (\Exception $e){
			return ['msg'=>$e->getMessage(),'code'=>1];
        }
    }

}
