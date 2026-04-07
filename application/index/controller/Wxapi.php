<?php
namespace app\index\controller;
use think\Db;

class Wxapi extends Super{
    public function login(){
        $account = ctrim(input("param.uname"));
        $pwd = ctrim(input("param.pwd"));
        $mdpwd = md5($pwd);
        
        //检验
        $result = Db::name('login')->field("id,uname")->where("uname='$account' and password='$mdpwd'")->find();
        if ($result){
            return json_encode(array('status'=>1,'result'=>$result));
        }else {
            return json_encode(array('status'=>2));
        }
    }
    
    //生成订单并返回
    public function create_order(){
        $order_num = ctrim(input("param.ordersn"));
        $in_time = input("time/s");
        $uid = ctrim(input("param.uid"));
        $name = ctrim(input("param.uname"));
        $look = input("param.look");
        $str_date = strtotime($in_time);
        $time = time();
        //查询是否订单是否存在
        $exist = Db::name('order')->where("ordernum='$order_num'")->find();
        if ($exist){
            echo json_encode($exist);
        }else {
            //录入订单
            $in_arr = array();
            $in_arr = ['uname'=>$name,'ordernum'=>$order_num,'uid'=>$uid,'ordertime'=>$str_date,'status'=>0,'look'=>$look,'addtime'=>$time];
            $in_order = Db::name('order')->insertGetId($in_arr);
            if ($in_order){
                $result = Db::name('order')->where("id='$in_order'")->find();
                echo json_encode($result);
            }
        }
    }
    
    //修改订单状态
    public function change_order(){
        $orderid = intval(input("param.orderid"));
        $old_status = intval(input("param.olstatus"));
        $change_status = intval(input("param.statusid"));
        $uid = intval(input("param.uid"));
        $e = 0;
        $time = time();
        
        //修改订单状态
        $back_data = Db::name('order')->where("id='$orderid'")
                     ->update(array('status'=>$change_status,'addtime'=>$time));
        
        //订单状态流
        for($i=$old_status; $i<$change_status;$i++){
            $in_data = array();
            $in_data = ['orderid'=>$orderid,'uid'=>$uid,'orstatus'=>$old_status+$e,'addtime'=>$time];
            $in_flow = Db::name('flow_check')->insert($in_data);
            $e++;
        }
        if ($back_data && $in_flow){
            echo json_encode(array('status'=>1));
        }else {
            echo json_encode(array('status'=>2));
        }
    }
}