<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

/**
 * 更改订单入库状态
 */
class Into extends Controller
{
    public function index()
    {
		$myfile = fopen("log.txt", "w");
		$txt = date('Y-m-d H:i:s',time())."\n";
		file_put_contents("log.txt", $txt, FILE_APPEND | LOCK_EX);
		
        ////找到所有未入库的订单,只取2020-10-12日以后的数据   旧的：2025-3--25  修改人：mai
        // $uninto = Db::name('order')->where('addtime>=1596211200')->where("status<7 or status2<7")->select();
        // $orderid = array_column($uninto,'id');
        // //查询中间库，获取已经入库的订单id
        // $db2 = Db::connect('database.db2');
        // $route = $db2->table('orderroute')->whereIn('order_id',$orderid)->order('order_id,SortId asc')->select();
		
		//找到所有未入库的订单,只取2020-10-12日以后的数据   新的：2025-3--25  修改人：mai
		$uninto = Db::name('order')->where('addtime>=1596211200')->where("status=4")->select();
		$orderid = array_column($uninto,'id');
		//查询中间库，获取已经入库的订单id
		$db2 = Db::connect('database.db2');
		$route = $db2->table('orderroute')->where('Enddate is not null')->whereIn('order_id',$orderid)->order('order_id,SortId asc')->select();
        
        $list = [];
        foreach ($route as $k => $v) {
            $list[$v['order_id']] = $v;
        }
        //判断Fstartdate 字段是否为空
        $id = [];
        foreach ($list as $k => $v) {
            if($v['Fstartdate']){
                $id[] = $v['order_id'];
            }
        }
		
        $res = Db::name('order')->whereIn('id',$id)->update(['status'=>7,'status2'=>7,'intime'=>time()]);
        if($res !== false){
            exit(0);
        }
        exit(1);
    }
}