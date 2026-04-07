<?php

namespace app\api\controller;

use think\Db;

class Index
{
    /**
     * 用户登陆
     */
    public function Login()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/Login?$para");
        //$content = curl_post("http://8.146.200.197:8090/api/HengHui/Login?$para");
        echo $content;
    }
    
    /**
     * 扫码开始工序
     */
    public function StartScans()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/StartScans?$para");
        echo $content;
    }
    
    /**
     * 报工工序
     */
    public function JObBooking()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/JObBooking?$para");
        echo $content;
    }
    
    /**
     * 工作量统计(入库结算是指整个销售订单全部完成时；工序完成是指统计工人工序完成)
     */
    public function GetJObCount()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/GetJObCount?$para");
        echo $content;
    }
    
    /**
     * 销售订单审核通过
     */
    public function OrderSellVerify()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/OrderSellVerify?$para");
        echo $content;
    }
    
    /**
     * 
     */
    public function OrderRouteStatus()
    {
        $data = input('post.');
        $para = "";
        foreach($data as $k => $v){
            $para .= "$k=$v&";
        }
        rtrim($para,'&');
        $content = curl_post("http://39.108.164.249:8090/api/HengHui/OrderRouteStatus?$para");
        echo $content;
    }


    /**
     * 配送登录
     * @param string $name 账号
     * @param string $password 密码
     */
    public function sendlogin()
    {
        $name = input('name');
        $password = input('password');
        $user = Db::name('user')->where(['login_name'=>$name,'is_disable'=>0])->find();
        if($user){
            $passwd = password($password);
            if($passwd != $user['login_password']){
                return json_encode(['code'=>1,'msg'=>'密码不正确']);
            }
            return json_encode(['code'=>0,'data'=>$user]);
        }else{
            return json_encode(['code'=>1,'msg'=>'此用户不存在或已被禁用']);
        }
    }

    /**
     * 订单信息
     * @param string $number订单编号
     */
    public function signOrder()
    {
        $number = input('number');//订单编码
        $numbers = explode(',',$number);
        $number = $numbers[0];
        $res = Db::name('order')->field('id,number,dealer,phone,send_address,note,total_price,have_pay,no_pay,finance_rebate_price,is_send')
            ->where('number',$number)->find();

        if(!$res){
            exit(json_encode(['code'=>1,'msg'=>'单号错误']));
        }
        if($res['is_send']==0){
            exit(json_encode(['code'=>1,'msg'=>'此订单未创建配送单']));
        }
        $havepay = Db::name('paid_record')->where('order_id',$res['id'])->sum('have_pay');
        $nopay = $res['total_price']-$havepay-$res['finance_rebate_price'];
        $res['no_pay'] = $nopay>0?round($nopay,2):0;

        $payType = config('pay_type');
        $pay = [];
        foreach ($payType as $k => $v) {
            if($k > 0){
                $pay[] = ['id'=>$k,'text'=>$v];
            }
        }

        $data = ['code' => 0,'pay_type'=>$pay,'info'=>$res];
        echo json_encode($data);
    }


    /**
     * 保存签收订单
     * @param string $number订单编号
     * @param int $uid配送人id
     * @param int $paytype 收款方式
     * @param decimal $price 收款价格
     */
    public function saveSign()
    {
        $number = input('number');
        $uid = input('uid');
        $paytype = input('paytype');
        $price = input('price');

        $order = Db::name('order')->where('number',$number)->find();
        $surplus = round($order['total_price']-$order['have_pay']-$order['finance_rebate_price'],2);//余款
        //判断现在收的款是否大于余款
        if($price>$surplus){
            return json_encode(['code'=>1,'msg'=>'金额大于尾款']);
        }

        $res = Db::name('order')->where('number',$number)->update(['sign_time'=>time(),'sign_uid'=>$uid]);
        if($res){
            //判断配送单 是否全部配送完成
            $send = Db::name('order_send')->where("find_in_set($number,all_number)")->order('id desc')->find();
            $allorderId = explode(',',$send['all_orderid']);
            $order = Db::name('order')->whereIn('id',$allorderId)->select();
            $flag = 0;
            foreach ($order as $k => $v) {
                if($v['sign_time'] != 0){
                    $flag += 1;
                }
            }

            if($flag == 0){
                $sendStatus = 0;
            }elseif ($flag == count($allorderId)){
                $sendStatus = 2;
            }else{
                $sendStatus = 1;
            }
            Db::name('order_send')->where('id',$send['id'])->update(['status'=>$sendStatus]);

            //保存收款记录
            if($price){
                $order = Db::name('order')->where('number',$number)->find();
                $orderId = $order['id'];
                //插入收款记录表
                Db::name('paid_record')->insert([
                    'order_id'=>$orderId,'pay_type'=>$paytype,'have_pay'=>$price,'addtime'=>time()
                ]);

                $allHavePay = Db::name('paid_record')->where('order_id',$orderId)->sum('have_pay');
                $otherPay = Db::name('paid_record')->where('order_id',$orderId)->sum('other_pay');
                $noPay = round($order['total_price']-$allHavePay,2);
                //更新总单的收款金额
                $res = Db::name('order')->where('id', $orderId)->update([
                    'have_pay' => $allHavePay, 'no_pay' => $noPay<0?0:$noPay, 'other_pay' => $otherPay
                ]);
            }
            return json_encode(['code'=>0,'msg'=>'签收成功']);
        }
        return json_encode(['code'=>1,'msg'=>'签收失败']);
    }

}
