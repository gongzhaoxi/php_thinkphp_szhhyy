<?php

namespace app\admin\controller;

use excel\Excel;
use think\Controller;
use think\Db;

/**
 * 财务控制器
 */
class Finance extends Base
{
    /**
     * 经销商账套收款
     */
    public function dealerPrice()
    {
        $search = input('get.');
        $dealer = Db::name('dealer')->order('name')->select();
        $this->assign('dealer',$dealer);
        $this->assign('search',$search);
        $this->assign('pay_type',config('pay_type'));
        return $this->fetch();
    }

    /**
     * 异步获取经销商的总金额
     */
    public function getDealerPrice()
    {
        $id = input('id/d');
        $res = Db::name('order')
            ->field('COALESCE(sum(total_price),0) as total_price,COALESCE(sum(have_pay),0) as have_pay,COALESCE(sum(finance_rebate_price),0) as rebate')
            ->where('dealer_id',$id)->where('sign_time != 0')
            ->find();
        if(!$res){
            $this->error('');
        }
        $nopay = round($res['total_price']-$res['have_pay']-$res['rebate'],2);
        $res['have_pay'] = round($res['have_pay']+$res['rebate'],2);
        $res['no_pay'] = $nopay;
        $this->success('',$res);
    }

    /**
     * 异步获取经销商 未核销的订单
     */
    public function getDealerOrder()
    {
        $id = input('dealer_id');
        $price = input('all_price');
        $allRebate = input('all_rebate_price');
        $price = $price+(is_numeric($allRebate)&&$allRebate!=''?$allRebate:0);
        $allprice = $price;
        $list = Db::name('order')->alias('a')->field('a.*,c.snumber,c.send_date')
            ->join('order_send_detail b','a.id=b.order_id','left')
            ->join('order_send c','b.sid=c.id','left')
            ->group('a.id')
            ->where('dealer_id',$id)->where('a.sign_time != 0')->where("(have_pay+finance_rebate_price) < total_price")->select();
        foreach ($list as $k => $v) {
            $list[$k]['have_pay'] = round($v['have_pay']+$v['finance_rebate_price'],2);
            $wait = round($v['total_price']-$v['have_pay']-$v['finance_rebate_price'],2);
            $price = round($price-$wait,2);
            if($price >= 0){
                $list[$k]['cal'] = $wait;
            }else{
                $list[$k]['cal'] = isset($temp)?$temp:$allprice;
                break;
            }
            $temp = $price;
        }
        //不够减的默认为0,处理数据
        foreach ($list as $k => $v) {
            if(!isset($v['cal'])){
                $list[$k]['cal'] = 0;
            }
            if(!$v['snumber']){
                $list[$k]['snumber'] = '';
            }
            if(!$v['send_date']){
                $list[$k]['send_date'] = '';
            }
            $list[$k]['addtime'] = date('Y-m-d',$v['addtime']);
        }
        return $list;
    }

    /**
     * 保存核销数据
     */
    public function savePayments()
    {
        set_time_limit(0);
        $id = input('id/a');
        $price = input('cal/a');//核销金额
        $rebate = input('rebate/a');//折让金额
        $paytype = input('pay_type');
        $paytime = input('pay_time');//收款时间

        Db::startTrans();
        try{
            //去除金额为0的数据
            $cal = [];
            foreach ($price as $k => $v) {
                if($v > 0){
                    $cal[$k] = $v;
                }
            }
            foreach ($cal as $k => $v) {
                $tempid = $id[$k];
                $tempRebate = $rebate[$k];
                Db::name('paid_record')->insert([
                    'order_id'=>$tempid,'pay_type'=>$paytype,'have_pay'=>$v,'finance_rebate'=>$tempRebate,'addtime'=>strtotime($paytime)
                ]);
                //更新订单的总收款
                $find = Db::name('paid_record')->field("COALESCE(sum(have_pay),0) as have_pay,COALESCE(SUM(finance_rebate),0) as rebate")
                    ->where('order_id',$tempid)->find();
                Db::name('order')->where('id',$tempid)->update(['have_pay'=>$find['have_pay'],'finance_rebate_price'=>$find['rebate']]);
            }
            Db::commit();
            $this->success('操作成功');
        }catch (\Exception $e){
            Db::rollback();
            $this->error('操作失败');
        }
    }

    /**
     * 导出收款记录
     */
    public function exportPayment()
    {
        if(request()->isPost()){
            $stime = input('start_time');
            $endtime = input('end_time');
            $dealerId = input('dealer_id');
            $where = "1=1";
            if($stime){
                $where .= " and b.addtime>=".strtotime($stime);
            }
            if($endtime){
                $endtime = strtotime($endtime)+(24*3600-1);
                $where .= " and b.addtime<=".$endtime;
            }
            if($dealerId){
                $where .= " and dealer_id=$dealerId";
            }
            $sql = Db::name('order_price')->where("order_type!=3")->buildSql();
            $ordertype = config('order_type');
 
            $list = Db::name('order')->alias('a')
                ->field('a.type,a.dealer,a.sales_name,a.phone,a.address,a.send_address,a.number,a.finance_rebate_price,c.*,a.addtime,b.addtime as paid_time,b.have_pay,b.pay_type,b.id as paid_id,c.order_type')
                ->join('paid_record b','a.id=b.order_id')
                ->join("$sql c",'b.order_id=c.order_id','left')
                ->where($where)
                ->order('a.id,b.id')
                ->select();
            $orderId = array_column($list,'order_id');
            $material = Db::name('order')->alias('a')->field("a.number,a.sales_name,a.dealer,a.phone,a.address,a.send_address"
                . ",a.type,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.width as all_width,b.height as all_height,b.order_id,b.addtime")
                ->join('order_material b','a.id=b.order_id')
                ->whereIn('order_id',$orderId)
                ->select();
            $materialArray = [];//存储键值关系
            foreach ($material as $k => $v) {
                $materialArray[$v['order_id']][] = $v;
                
            }
            foreach ($list as $k => $v) {
         		$list[$k]['type'] = $ordertype[$v['type']];
            	$list[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
            	$list[$k]['material'] = $v['material'].'/'.$list[$k]['yarn_color'];
        	}
       
            $materialOrderid = array_column($material,'order_id');

            //送货单号和日期
            $send = Db::name('order_send')->alias('a')->field('a.*,b.order_id')
                ->join('order_send_detail b','a.id=b.sid')
                ->whereIn('order_id',$orderId)
                ->select();
            $sendArray = [];//存储键值关系
            foreach ($send as $k => $v) {
                $sendArray[$v['order_id']] = ['snumber'=>$v['snumber'],'send_date'=>$v['send_date']];
                
            }

            $headArr = [
                '订单类型','订单日期','货单号','送货日期','送货地址','业务员','订单号','客户名称','电话','地址','材质','颜色(型材/花件)','个数',
                '面积','单价','折扣率','折后价','金额','备注','收款日期','折让金额'
            ];
            $paytype = config('pay_type');unset($paytype[0]);
            $headArr = array_merge($headArr,$paytype);
            $field = [
                'type','addtime','snumber','send_date','send_address','sales_name','number','dealer','phone','address','material','color_name','count',
                'area','price','rebate','rebate_price','all_price','note','paid_time','finance_rebate_price2'
            ];
            
            foreach($paytype as $k=>$v){
    			$field[] = 'paytype'.$k;
    		}	
            
            //处理数据
            $temp = [];
            $newlist = [];
            foreach ($list as $k => $v) {
                $list[$k]['addtime'] = date('Y-m-d',$v['addtime']);
                $list[$k]['paid_time'] = date('Y-m-d',$v['paid_time']);
                //excel表格中的 收款记录 同一个记录的单 只显示一次
                if(!isset($temp[$v['paid_id'].$v['order_id']])){
                    foreach ($paytype as $k2 => $v2) {
                        $havepay = $v['pay_type']==$k2?$v['have_pay']:'';
                        $list[$k]['paytype'.$k2] = $havepay;
                    }
                    $list[$k]['finance_rebate_price2'] = $v['finance_rebate_price'];
                }
                $temp[$v['paid_id'].$v['order_id']] = 1;
//              //加入送货单号
                $list[$k]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
                $list[$k]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';

                $newlist[] = $list[$k];
                //如果有原材料 则加入原材料
                if(in_array($v['order_id'],$materialOrderid)){
                    $tempMaterial = isset($materialArray[$v['order_id']])?$materialArray[$v['order_id']]:[];
//                  //原材料中加入送货单号
                    foreach ($tempMaterial as $k3 => $v3) {
                        $tempMaterial[$k3]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
                        $tempMaterial[$k3]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';
                        $tempMaterial[$k3]['addtime'] = date('Y-m-d',$v3['addtime']);
                        $tempMaterial[$k3]['paid_time'] = date('Y-m-d',$v['paid_time']);
                        $tempMaterial[$k3]['type'] = $ordertype[$tempMaterial[$k3]['type']];
                    }
                    //如果有下一行 下一行跟当前行的order_id不同
                    if(isset($list[$k+1]['order_id']) && $list[$k+1]['order_id']!=$v['order_id']){
                        $newlist = array_merge($newlist,$tempMaterial);
                        ///如果有下一行 下一行跟当前行的order_id相同 且 付款时间不同
                    }elseif ($list[$k+1]['order_id'] && $list[$k+1]['order_id']==$v['order_id'] && $list[$k+1]['paid_time']!=$v['paid_time']){
                        $newlist = array_merge($newlist,$tempMaterial);
                        //如果是最后一行
                    }elseif (!isset($list[$k+1]['order_id'])){
                        $newlist = array_merge($newlist,$tempMaterial);
                    }
                }
            }
            $excel = new Excel();
            $excel->export('收款记录',$headArr,$newlist,$field,"恒辉出货明细表");
            return;
        }
        $dealer = Db::name('dealer')->order('name')->select();
        $this->assign('dealer',$dealer);
        return $this->fetch();
    }

    /**
     * 导出对账单
     */
    public function exportBill()
    {
        if(request()->isPost()){
            set_time_limit(0);
			$start_time = input('start_time');
            $end_time = input('end_time');
            $send = input('send_order');
            $signorder = input('sign_order');
            $intoOrder = input('into_order');
            $dealer = input('dealer_id');
            $where = "have_pay + finance_rebate_price < total_price";
            $dealername = "";
            if($dealer){
                $where .= " and dealer_id in ($dealer)";
            }
			if($start_time){
				$where .= " and a.addtime>=".strtotime($start_time);
			}
			if($end_time){
				$end_time = strtotime($end_time)+(24*3600-1);
				$where .= " and a.addtime<=".$end_time;
			}
            $sql = Db::name('order_price')->where("order_type!=3")->buildSql();
            $list = Db::name('order')->alias('a')
                ->field('a.dealer,a.sales_name,a.phone,a.send_address,a.number,a.total_price,c.*,a.have_pay,a.finance_rebate_price')
                ->join("$sql c",'a.id=c.order_id','left')
                ->where($where)
                ->select();
            $orderId = array_column($list,'order_id');
            //原材料信息
            $material = Db::name('order')->alias('a')->field("a.number,a.sales_name,a.dealer,a.phone,a.address"
                . ",a.type,a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.width as all_width,b.height as all_height,b.order_id,b.addtime")
                ->join('order_material b','a.id=b.order_id')
                ->whereIn('order_id',$orderId)
                ->select();
            $materialArray = [];//存储键值关系
            foreach ($material as $k => $v) {
                $materialArray[$v['order_id']][] = $v;
            }
            $materialOrderid = array_column($material,'order_id');

            //送货单号和日期
            $send = Db::name('order_send')->alias('a')->field('a.*,b.order_id')
                ->join('order_send_detail b','a.id=b.sid')
                ->whereIn('order_id',$orderId)
                ->select();
            $sendArray = [];//存储键值关系
            foreach ($send as $k => $v) {
                $sendArray[$v['order_id']] = ['snumber'=>$v['snumber'],'send_date'=>$v['send_date']];
            }

            //处理数据
            $temp = [];
            $newlist = [];
            /*
            $allnonay = Db::name('order')
                ->field('COALESCE(sum(total_price),0) as total_price,COALESCE(sum(have_pay),0) as have_pay')
                ->where('dealer_id',$dealer)
                ->find();
            $allnonay = isset($allnonay)?$allnonay['total_price']-$allnonay['have_pay']:0;
            */
            //汇总记录
            $payments = $this->billRecord($dealer);
            $allnonay = array_sum(array_column($payments,'price'));
            $titles['汇总'] = ['price'=>'打款金额','time'=>'打款时间','rebate'=>'本次折让','pay_type'=>'收款方式'];
            //最后一行加入总欠款金额
            $payments[] = ['price'=>'当前打款金额'.$allnonay];
            $lists['汇总'] = $payments;
            //收款明细
            $pays = $this->billPayments();
            $titles['收款明细'] = $pays['title'];
            $lists['收款明细'] = $pays['list'];
            //欠款明细
            $titles['欠款明细'] = [
                'addtime'=>'订单日期','snumber'=>'货单号','send_date'=>'送货日期','number'=>'订单号','dealer'=>'客户名称','send_address'=>'送货地址','count'=>'个数',
                'area'=>'面积','price'=>'单价','rebate'=>'折扣率','rebate_price'=>'折后价','all_price'=>'金额','total_price2'=>'订单总金额',
                'have_pay2'=>'已收款(含定金)','no_pay'=>'未收款','finance_rebate_price2'=>'折让金额'
            ];
            foreach ($list as $k => $v) {
                $list[$k]['addtime'] = date('Y-m-d',$v['addtime']);
                //excel表格中的 订单总金额 只显示一次
                if(!in_array($v['order_id'],$temp)){
                    $list[$k]['total_price2'] = $v['total_price'];
                    $list[$k]['have_pay2'] = $v['have_pay'];
                    $list[$k]['no_pay'] = round($v['total_price']-$v['have_pay'],2);
                    $list[$k]['finance_rebate_price2'] = $v['finance_rebate_price'];
                }
                $temp[$v['order_id']] = $v['order_id'];
                //加入送货单号
                $list[$k]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
                $list[$k]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';

                $newlist[] = $list[$k];
                //如果有原材料 则加入原材料
                if(in_array($v['order_id'],$materialOrderid)){
                    $tempMaterial = isset($materialArray[$v['order_id']])?$materialArray[$v['order_id']]:[];
                    //原材料中加入送货单号
                    foreach ($tempMaterial as $k3 => $v3) {
                        $tempMaterial[$k3]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
                        $tempMaterial[$k3]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';
                        $tempMaterial[$k3]['addtime'] = date('Y-m-d',$v3['addtime']);
                    }
                    //如果有下一行 下一行跟当前行的order_id不同
                    if(isset($list[$k+1]['order_id']) && $list[$k+1]['order_id']!=$v['order_id']){
                        $newlist = array_merge($newlist,$tempMaterial);
                    }elseif (!isset($list[$k+1]['order_id'])){
                        $newlist = array_merge($newlist,$tempMaterial);
                    }
                }
            }
            $nopaidDetail = [];
            foreach($newlist as $vo){
                if($vo['no_pay'] && $vo['no_pay'] > 0){
                    $nopaidDetail[] = $vo;
                }
            }
            $sumprice = array_sum(array_column($nopaidDetail,'total_price2'));
            $sumNopay = array_sum(array_column($nopaidDetail,'no_pay'));
            //最后一行加入汇总金额
            $newlist[] = ['all_price'=>'汇总','total_price2'=>$sumprice,'no_pay'=>$sumNopay];
            $lists['欠款明细'] = $nopaidDetail;
            $excel = new Excel();
            $excel->multi_export($titles,$lists,$dealername."未付订单明细(对账单)");
            return;
        }
        $dealer = Db::name('dealer')->order('name')->select();
        $this->assign('dealer',$dealer);
        return $this->fetch();
    }

    /**
     * 对账单打款 汇总记录
     */
    public function billRecord($dealerId)
    {
		$start_time = input('start_time');
        $end_time = input('end_time');
        $time = timezone_get(5);
		
		$where = "b.dealer_id in ($dealerId)";
		if($start_time){
			$where .= " and a.addtime>=".strtotime($start_time);
		}
		if($end_time){
			$end_time = strtotime($end_time)+(24*3600-1);
			$where .= " and a.addtime<=".$end_time;
		}
        //半年内此经销商的所有收款记录
        $paidRecord = Db::name('paid_record')
            ->alias('a')
            ->field("a.*,FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime")
            ->join('order b','a.order_id=b.id')
//            ->where("a.addtime between {$time['begin']} and {$time['end']}")
            ->where($where)
            ->order('a.addtime')
            ->select();
        $list = [];
        foreach ($paidRecord as $k => $v) {
            $list[$v['addtime'].$v['pay_type']][] = $v;
        }
        //累加
        $paytype = config('pay_type');
        $data = [];
        foreach ($list as $k => $v) {
            $price = 0;
            $rebate = 0;
            foreach ($v as $k2 => $v2) {;
                $price += $v2['have_pay'];
                $rebate += $v2['finance_rebate'];
            }
            $time = $v[0]['addtime'];
            $pay = $paytype[$v[0]['pay_type']];

            $data[] = ['price'=>$price,'time'=>$time,'pay_type'=>$pay,'rebate'=>$rebate];
        }
        return $data;
        
    }

    /**
     * 对账单收款记录
     */
    public function billPayments()
    {
        $stime = input('start_time');
        $endtime = input('end_time');
        $dealerId = input('dealer_id');
        $where = "have_pay>0 and dealer_id in ($dealerId)";
		$where2 = "b.dealer_id in ($dealerId)";
        if($stime){
            //$where .= " and a.addtime>=".strtotime($stime);
			$where2 .= " and a.addtime>=".strtotime($stime);
        }
        if($endtime){
            $endtime = strtotime($endtime)+(24*3600-1);
            //$where .= " and a.addtime<=".$endtime;
			$where2 .= " and a.addtime<=".$endtime;
        }
		
		$tmp = Db::name('paid_record')
            ->alias('a')
            ->field("a.order_id")
            ->join('order b','a.order_id=b.id')
            ->where($where2)
            ->group('a.order_id')
            ->select();
		$order_ids = array_column($tmp,'order_id');
		if(!$order_ids){
			$order_ids = [-1];
		}
		
        $sql = Db::name('order_price')->where("order_type!=3")->buildSql();
        $list = Db::name('order')->alias('a')
            ->field('a.dealer,a.sales_name,a.phone,a.address,a.number,c.*,c.order_type')
            ->join("$sql c",'a.id=c.order_id','left')
            ->where($where)
			->whereIn('a.id',$order_ids)
            ->order('a.id')
            ->select();

        $orderId = array_column($list,'order_id');
        //订单原材料
        $material = Db::name('order')->alias('a')->field("a.number,a.sales_name,a.dealer,a.phone,a.address"
            . ",a.type,a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.width as all_width,b.height as all_height,b.order_id,b.addtime")
            ->join('order_material b','a.id=b.order_id')
            ->whereIn('order_id',$orderId)
            ->select();
        $materialArray = [];//存储键值关系
        foreach ($material as $k => $v) {
            $materialArray[$v['order_id']][] = $v;
        }
        $materialOrderid = array_column($material,'order_id');
        //订单的收款记录
        $orderPayment = Db::name('paid_record')->whereIn('order_id',$orderId)->select();
        $recordArray = [];
        foreach ($orderPayment as $k => $v){
            $recordArray[$v['order_id']][] = $v;
        }
        $record = [];
        $paytype = config('pay_type');
        foreach ($recordArray as $k => $v){
            $havepayText = array_column($v,'have_pay');
            $rebate = array_sum(array_column($v,'finance_rebate'));
            $paytypeText = [];
            $paidDate = [];
            foreach ($v as $k2 => $v2){
                $temptype = $paytype[$v2['pay_type']];
                $tempdate = date('Y-m-d',$v2['addtime']);
                $paytypeText[] = $temptype;
                $paidDate[] = $tempdate;
            }
            //存储键值关系
            $record[$k] = [
                'have_pay'=>implode('+',$havepayText),'pay_type'=>implode('+',$paytypeText),
                'rebate'=>$rebate,'paid_date'=>implode('+',$paidDate)
            ];
        }

        //送货单号和日期
        $send = Db::name('order_send')->alias('a')->field('a.*,b.order_id')
            ->join('order_send_detail b','a.id=b.sid')
            ->whereIn('order_id',$orderId)
            ->select();
        $sendArray = [];//存储键值关系
        foreach ($send as $k => $v) {
            $sendArray[$v['order_id']] = ['snumber'=>$v['snumber'],'send_date'=>$v['send_date']];
        }

        $titles = [
            'addtime'=>'订单日期','snumber'=>'货单号','send_date'=>'送货日期','sales_name'=>'业务员',
            'number'=>'订单号','dealer'=>'客户名称','phone'=>'电话','address'=>'地址','material'=>'材质',
            'color_name'=>'颜色(型材/花件)','count'=>'个数','area'=>'面积','price'=>'单价','rebate'=>'折扣率',
            'rebate_price'=>'折后价','all_price'=>'金额','note'=>'备注','have_pay'=>'收款金额','pay_type'=>'收款方式',
            'finance_rebate'=>'总折让金额','paid_date'=>'收款日期'
        ];
        //处理数据
        $temp = [];
        $newlist = [];
        foreach ($list as $k => $v) {
            //excel表格中的 收款记录 同一个记录的单 只显示一次
            if(!isset($temp[$v['order_id']])){
                $list[$k]['have_pay'] = isset($record[$v['order_id']])?$record[$v['order_id']]['have_pay']:'';
                $list[$k]['pay_type'] = isset($record[$v['order_id']])?$record[$v['order_id']]['pay_type']:'';
                $list[$k]['finance_rebate'] = isset($record[$v['order_id']])?$record[$v['order_id']]['rebate']:'';
                $list[$k]['paid_date'] = isset($record[$v['order_id']])?$record[$v['order_id']]['paid_date']:'';
            }
            $temp[$v['order_id']] = 1;
            //加入送货单号
            $list[$k]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
            $list[$k]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';
            $list[$k]['addtime'] = date('Y-m-d',$v['addtime']);

            $newlist[] = $list[$k];
            //如果有原材料 则加入原材料
            if(in_array($v['order_id'],$materialOrderid)){
                $tempMaterial = isset($materialArray[$v['order_id']])?$materialArray[$v['order_id']]:[];
                //原材料中加入送货单号
                foreach ($tempMaterial as $k3 => $v3) {
                    $tempMaterial[$k3]['snumber'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['snumber']:'';
                    $tempMaterial[$k3]['send_date'] = isset($sendArray[$v['order_id']])?$sendArray[$v['order_id']]['send_date']:'';
                    $tempMaterial[$k3]['addtime'] = date('Y-m-d',$v3['addtime']);
                }
                //如果有下一行 下一行跟当前行的order_id不同
                if(isset($list[$k+1]['order_id']) && $list[$k+1]['order_id']!=$v['order_id']){
                    $newlist = array_merge($newlist,$tempMaterial);
                }elseif (!isset($list[$k+1]['order_id'])){
                    $newlist = array_merge($newlist,$tempMaterial);
                }
            }
        }
        return ['title'=>$titles,'list'=>$newlist];
    }

    /**
     * 待处理订单
     */
    public function noHandle()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');
		$type = input('type');
        $where = "(status=2 or status2=4)";
        if ($name != '') {
            $where .= " and (number ='$name' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$stime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and addtime<=$etime";
        }
		if($type){
			$type = is_array($type)?$type:explode(',',$type);
			$where .= " and type in (".implode(',',$type).")";
		}else{
			$type = [];
		}
        $list = \app\model\Order::with(['paidRecord'])->where($where)->paginate();
        $list->appends(input('get.'));

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('name', $name);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('pay_type', config('pay_type'));
        $this->assign('action',request()->action());
		$this->assign('order_type',config('order_type'));
		$this->assign('type', $type);
        return $this->fetch();
    }

    /**
     * 到达财务的全部订单
     */
    public function allorder()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');
		$type 	= input('type');

//        $where = "(status>=2 or status2>=4)";
        $where = "1=1";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$stime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and addtime<=$etime";
        }
	    if($type){
			//$where .= " and (type = '$type')";
			$type = is_array($type)?$type:explode(',',$type);
			$where .= " and type in (".implode(',',$type).")";
		}else{
			$type = [];
		}
        $list = \app\model\Order::with(['paidRecord'])->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('name', $name);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('pay_type', config('pay_type'));
        $this->assign('order_type',config('order_type'));
        $this->assign('action',request()->action());
		$this->assign('type', $type);
        return $this->fetch('no_handle');
    }

    /**
     * 查看收款记录
     */
    public function paidRecord()
    {
        if(request()->isPost()){
            $id = input('id');
            $orderId = input('order_id');
            $data = input('post.');

            $res = Db::name('paid_record')->where(['id'=>$id])->update([
                'pay_type'=>$data['pay_type'],'have_pay'=>$data['have_pay'],'finance_rebate'=>$data['rebate'],'finance_remark'=>$data['finance_remark']
            ]);

            $allHavePay = Db::name('paid_record')->where('order_id',$orderId)->sum('have_pay');
            $otherPay = Db::name('paid_record')->where('order_id',$orderId)->sum('other_pay');
            $rebatePrice =  Db::name('paid_record')->where('order_id',$orderId)->sum('finance_rebate');
            $noPay = round($data['total_price']-$allHavePay,2);
            //更新总单的收款金额
            $resd = Db::name('order')->where('id', $orderId)->update([
                'have_pay' => $allHavePay, 'no_pay' => $noPay<0?0:$noPay, 'other_pay' => $otherPay,'finance_rebate_price'=>$rebatePrice
            ]);
            if($res !== false){
                $this->success('保存成功');
            }
            $this->error('保存失败');
            return;
        }
        $id = input('id/d');
        $list = Db::name('paid_record')->where('order_id',$id)->order('addtime asc')->select();
        $this->assign('list',$list);
        $this->assign('pay_type',config('pay_type'));
        $this->assign('order_id',$id);
        return $this->fetch();
    }


    /**
     * 订单状态返回营运
     */
    public function back()
    {
        $id = input('id/d');
        $res = Db::name('order')->where('id',$id)->update(['status'=>1,'status2'=>1]);
        if($res){
            $this->success('返回成功');
        }
        $this->error('返回失败,请重试');
    }
    
    /**
     * 配送批次订单
     */
    public function delivery()
    {
        $status = input('status');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "1=1";
        if ($status != '') {
            $where .= " and status=$status";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and send_date>='$startTime'";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and send_date<='$endTime'";
        }

        $list = Db::name('order_send')->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('send_status', config('send_status'));
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('status', $status);
        return $this->fetch();
    }
    
    /**
     * 已处理未收款订单
     */
    public function noPay()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "sign_time !='' and total_price>(have_pay+finance_rebate_price)";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and sign_time>=$stime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and sign_time<=$etime";
        }

        $list = Db::name('order')->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('send_status', config('send_status'));
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('name',$name);
        return $this->fetch();
    }
    
    
    /**
     * 查看订单详细
     */
    public function readOrder()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $orderId = input('order_id/d');
            $havePay = input('have_pay');
            $paidtime = input('paid_time');
            if(!$havePay){
                $this->error('请填写收款金额');
            }
            //插入收款记录表
            Db::name('paid_record')->insert([
                'order_id'=>$orderId,'pay_type'=>$data['pay_type'],'have_pay'=>$havePay,'addtime'=>$paidtime!=''?strtotime($paidtime):time(),
                'finance_rebate'=>$data['finance_rebate'],'finance_remark'=>$data['finance_remark']
            ]);

            $allHavePay = Db::name('paid_record')->where('order_id',$orderId)->sum('have_pay');
            $otherPay = Db::name('paid_record')->where('order_id',$orderId)->sum('other_pay');
            $allfinance = Db::name('paid_record')->where('order_id',$orderId)->sum('finance_rebate');
            $noPay = round($data['total_price']-$allHavePay-$allfinance,2);
            //更新总单的收款金额
            $res = Db::name('order')->where('id', $orderId)->update([
               'have_pay' => $allHavePay, 'no_pay' => $noPay<0?0:$noPay, 'other_pay' => $otherPay,'finance_rebate_price'=>$allfinance
            ]);
            if ($res !== false) {
                $this->success('保存成功');
            }
            $this->error('保存失败，请重试');
            return;
        }

        $id = input('id/d');
        //订单基本信息
        $res = Db::name('order')->where('id', $id)->find();
        $this->assign('orderid', $id);
        $this->assign('res', $res);

        //订单产品
        $product = Db::name('order_price')->alias('a')->field('a.*,b.structure,b.structure_id')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where("a.order_id=$id and a.order_type<=1")
                ->select();
        
        //组合单
        $group = Db::name('order_group')->where('order_id', $id)->select();

        //订单原材料
        $material = Db::name('order_material')->where('order_id', $id)->select();

        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('order_type', config('order_type'));
        $this->assign('group',$group);
        $this->assign('pay_type',config('pay_type'));
        return $this->fetch();
    }

    /**
     * 订单审核通过
     */
    public function check()
    {
        $id = input('id/d');
        $field = input('field');
        $status = input('status/d');
        
        $res = Db::name('order')->where('id', $id)->update([$field => $status+1, 'finance_status' => 1, 'car_time' => time()]);
        if ($res) {
            $this->success('审核成功');
        }
        $this->error('审核失败,请重试');
    }

    /**
     * 审核配送单
     */
    public function checkDelivery()
    {
        $id = input('id/d');
        
        $res = Db::name('order_send')->where('id', $id)->update(['is_check'=>1]);
        if ($res) {
            $this->success('审核成功');
        }
        $this->error('审核失败,请重试');
    }
    
    /**
     * 确认完工
     */
    public function confirm()
    {
        $id = input('id/d');
        $res = Db::name('order')->where('id', $id)->update(['status' => 8]);
        if ($res) {
            $this->success('确认成功', '', url('financeHandle'));
        }
        $this->error('确认失败,请重试');
    }

    /**
     * 已处理订单--竣工订单
     */
    public function financeHandle()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "(status=8 and finance_status=1)";
        if ($name != '') {
            $where .= " and (number ='$name' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$stime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and addtime<=$etime";
        }
        $list = Db::name('order')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('name', $name);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('pay_type',config('pay_type'));
        return $this->fetch();
    }
	
	
	
	    /**
     * 导出已报价订单
     */
    public function exportNoHandle()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');
		$type 	= input('type/d');

//        $where = "(status>=2 or status2>=4)";
        $where = "1=1";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$stime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime) + 24 * 3600;
            $where .= " and addtime<=$etime";
        }
	    if($type){
			$where .= " and (type = '$type')";
		} 
        $list = \app\model\Order::with(['paidRecord'])->where($where)->order('id desc')->limit($where=='1=1'?20:10000)->select();
		$order_type = config('order_type');
		foreach($list as &$vo){
			$vo = $vo->toArray();
			$vo['type'] = $order_type[$vo['type']]??'';
			$vo['addtime'] = date('Y/m/d',$vo['addtime']);
			$vo['finance_remark'] = implode(';',array_column($vo['paid_record'],'finance_remark'));
			$vo['no_receive'] = round($vo['total_price']-$vo['have_pay']-$vo['finance_rebate_price'],2);
			$vo['is_sign'] = $vo['sign_time']?'已签收':'未签收';
		}
        $excel = new \excel\Excel();
		$headArr = ['订单编号','订单类型','经销商','地址','下单时间','数量','面积','总金额','已收款','收款备注','未收款','折让(优惠)','是否签收'];
		$field = ['number','type','dealer','send_address','addtime','count','area','total_price','have_pay','finance_remark','no_receive','finance_rebate_price','is_sign'];
		$excel->export('财务全部订单', $headArr, $list, $field, $title);
    }

}
