<?php

namespace app\admin\controller;

use Endroid\QrCode\QrCode;
use excel\Excel;
use think\Controller;
use think\Db;

/**
 * 所有部门可见订单
 */
class Allorder extends Base
{

    /**
     * 未待发货订单数量--异步获取
     */
    public function waitDeliveryCount()
    {
        $list = Db::name('order')->where("(status=7 or status2=7) and is_send=0")->order('id desc')->count();
        $this->success('',$list);
    }
    
    /**
     * 配送批次订单数量--异步获取
     */
    public function deliveryCount()
    {
        $list = Db::name('order_send')->where('is_check=0')->count();
        $this->success('',$list);
    }
    
    /**
     * 待发货订单
     */
    public function waitDelivery()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "(status=7 or status2=7) and is_send=0";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$startTime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime);
            $where .= " and addtime<=$etime";
        }
        $list = Db::name('order')->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('search',input('get.'));
        return $this->fetch();
    }
	
    /**
     * 导出已报价订单
     */
    public function exportWaitDelivery()
    {
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "(status=7 or status2=7) and is_send=0";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        if ($startTime != '') {
            $stime = strtotime($startTime);
            $where .= " and addtime>=$startTime";
        }
        if ($endTime != '') {
            $etime = strtotime($endTime);
            $where .= " and addtime<=$etime";
        }
        $list = Db::name('order')->field(['number','dealer','address','addtime','count','area','end_time','intime'])->where($where)->order('id desc')->select();
        $title = "待发货订单";
        foreach ($list as $k => $v) {
            $list[$k]['addtime'] = date('Y/m/d',$v['addtime']);
            $list[$k]['intime'] = $v['intime']?date('Y/m/d',$v['intime']):'';
        }
        $excel = new \excel\Excel();
		$headArr = ['订单编号','经销商','地址','下单时间','数量','面积','要求交货时间','入库时间'];
		$field = ['number','dealer','address','addtime','count','area','end_time','intime'];
		$excel->export('待发货订单', $headArr, $list, $field, $title);
    }
    /**
     * 添加配送单
     */
    public function addDelivery()
    {
        if ($this->request->isPost()) {
            $snumber = "SD" . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $id = input('id/a'); //所有id
            $orderNumber = input('order_number/a'); //所有订单编号
            $count = input('count/a'); //所有数量
            $arrive = input('arrive_time/a'); //所有到达时间
            $sort = input('sort/a'); //排序
            
            if(!is_array($orderNumber) || count($orderNumber)<=0){
                $this->error('请先添加订单');
            }
            
            $data['snumber'] = $snumber;
            $allNumber = implode(',', $orderNumber);
            $allId = implode(',', $id);
            $allCount = 0;
            foreach ($count as $k => $v) {
                $allCount += $v;
            }

            //插入配送单表
            $res = Db::name('order_send')->insertGetId([
                'snumber' => $snumber, 'all_number' => $allNumber, 'all_orderid' => $allId, 'addtime' => time(), 'count' => $allCount,
                'logistics_name' => input('logistics_name'),'logistics_numbers' => input('logistics_numbers'),'is_send' => input('is_send'),
                'driver_name' => input('driver_name'),'driver_phone' => input('driver_phone'),'send_date'=>input('send_date')
            ]);
            $detail = [];
            foreach ($id as $k => $v) {
                $detail[] = ['sid'=>$res,'order_id'=>$v];
            }
            Db::name('order_send_detail')->insertAll($detail);

            //更新订单信息
            foreach ($id as $k => $v) {
                Db::name('order')->where('id', $v)->update(['arrive_time' => $arrive[$k], 'sort' => $sort[$k],'is_send'=>1]);
            }

            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }

        $name = input('search');  //搜索关键字

        $where = "(status=7 or status2=7) and is_send=0";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        $list = Db::name('order')->where($where)->order('id desc')->limit(20)->select();
        $this->assign('send_date',date('Y-m-d',time()+24*3600));
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加配送单异步搜索
     */
    public function addSearch()
    {
        $name = input('search');  //搜索关键字

        $where = "(status=7 or status2=7) and is_send=0";
        if ($name != '') {
            $where .= " and (number like '%$name%' or dealer like '%$name%')";
        }
        $list = Db::name('order')->where($where)->select();
        $this->success('',$list);
    }

    /**
     * 打印配送单
     */
    public function printDelivery()
    {
        $sid = input('id/d');

        $send = Db::name('order_send')->where('id',$sid)->find();
        //is_send ==1,4 物流公司名称和物流编码 0,3司机名称和司机电话
        if($send['is_send'] == 1 || $send['is_send'] == 4){
            $typename = $send['logistics_name'];
            $typevalue = $send['logistics_numbers'];
            $field1 = "物流公司名称";$field2 = "物流单号";
        }elseif($send['is_send'] == 0 || $send['is_send'] == 3){
            $typename = $send['driver_name'];
            $typevalue = $send['driver_phone'];
            $field1 = "司机名称";$field2 = "司机电话";
        }

        $list = Db::name('order_send_detail')->alias('a')
            ->field('b.*')
            ->join('order b','a.order_id=b.id')
            ->whereIn('a.sid', $sid)
            ->order('sort')
            ->select();
        $totalArea = array_sum(array_column($list,'area'));
        $totalPrice = array_sum(array_column($list,'total_price'));
        //生成二维码
//        $qrcode = new QrCode();
//
//        foreach ($list as $k => $v) {
//            $qr = $qrcode->setText($v['number'])->writeDataUri();
//            $list[$k]['qrcode'] = $qr;
//        }
        $this->assign('send', $send);
        $this->assign('list', $list);
        $this->assign('field1',$field1);
        $this->assign('field2',$field2);
        $this->assign('type_name',$typename);
        $this->assign('type_value',$typevalue);
        $this->assign('total_area',$totalArea);
        $this->assign('total_price',$totalPrice);
        return $this->fetch();
    }

    /**
     * 打印订购清单
     */
    public function printBuy()
    {
        $orderId = input('id/d');
        $order = Db::name('order')->where('id', $orderId)->find();
        //订单产品
        $product = Db::name('order_price')->alias('a')
                ->field('a.*,b.structure')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where('a.order_id', $orderId)
                ->select();
        //订单原材料
        $material = Db::name('order_material')->where('order_id', $orderId)->select();

        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('order', $order);
        return $this->fetch();
       
    }



    /**
     * 配送中订单
     */
    public function delivery()
    {
        $status = input('status');
        $startTime = input('starttime');
        $endTime = input('endtime');
        $number = input('number');

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
        if($number){
            $where .= " and all_number like '%$number%'";
        }

        $list = Db::name('order_send')->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('send_status', config('send_status'));
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('status', $status);
        $this->assign('number',$number);
        $this->assign('send_type',['0'=>'自送','1'=>'物流','2'=>'自提','3'=>'请车','4'=>'快递']);
        return $this->fetch();
    }

    /**
     * 导出配送中订单
     */
    public function exportSend()
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

        //汇总数据
        $sid = Db::name('order_send')->where($where)->column('id');
        $list = Db::name('order_send_detail')->alias('a')
            ->field('c.sort,b.snumber,b.driver_name,b.driver_phone,c.dealer,c.send_address,c.number,c.area,c.total_price,'.
            'c.have_pay,c.sign_time,b.send_date,b.is_send,c.sales_name,c.id')
            ->join('order_send b','a.sid=b.id')
            ->join('order c','a.order_id=c.id')
            ->whereIn('sid',$sid)
            ->select();
        $map = ['0'=>'自送','1'=>'物流','2'=>'自提','3'=>'请车','4'=>'快递'];
        //转换数据格式
        foreach ($list as $k => $v) {
            $list[$k]['sign_time'] = $v['sign_time']!=0?'已签收':'未签收';
            $list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            $list[$k]['is_send'] = $map[$v['is_send']];
        }
        $excel = new Excel();
        $field = ['sort'=>'配送顺序','snumber'=>'配送单号','driver_name'=>'司机名','driver_phone'=>'电话',
            'dealer'=>'客户名称','send_address'=>'送货地址','number'=>'销售单号','area'=>'报价面积','total_price'=>'金额',
            'have_pay'=>'收款金额','z'=>'结款日期','sign_time'=>'是否签收','send_date'=>'送货日期','is_send'=>'发货方式','sales_name'=>'业务代表'
        ];
        $title['汇总'] = $field;
        $lists['汇总'] = $list;

        //详情数据
        $orderid = array_column($list,'id');
        $ordertypeText = config('order_type');
        $list = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer,a.phone,a.address"
            . ",a.send_address,b.material,b.color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.note,b.flower_type,b.all_width,b.all_height,b.order_id"
            .",d.snumber,d.driver_name,d.driver_phone,d.send_date,a.type as order_type")
            ->join('order_price b','a.id=b.order_id')
            ->join('order_send_detail c','a.id=c.order_id')
            ->join('order_send d','c.sid=d.id')
            ->whereIn('a.id',$orderid)
            ->order('a.id desc')
            ->select();
        $material = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer,a.phone,a.address,b.order_id"
            . ",a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,'' as note,'' as flower_type,b.width as all_width,b.height as all_height"
            .",d.snumber,d.driver_name,d.driver_phone,d.send_date,a.type as order_type")
            ->join('order_material b','a.id=b.order_id')
            ->join('order_send_detail c','a.id=c.order_id')
            ->join('order_send d','c.sid=d.id')
            ->whereIn('a.id',$orderid)
            ->order('a.id desc')
            ->select();
        $alldata = array_merge($list,$material);
        foreach ($alldata as $k => $v) {
            $alldata[$k]['order_type'] = $ordertypeText[$v['order_type']];
        }
        //排序
        $sort = [];
        foreach ($alldata as $k => $v) {
            $sort[] = $v['order_id'];
        }
        array_multisort($sort,SORT_DESC,$alldata);
        $detailField = [
            'snumber'=>'配送单号','driver_name'=>'司机名','driver_phone'=>'司机电话','send_date'=>'送货时间',
            'addtime'=>'订单日期','number'=>'订单编号','order_type'=>'订单类型','sales_name'=>'业务员','dealer'=>'客户名称',
            'phone'=>'电话','address'=>'地址','send_address'=>'送货地址','material'=>'材质','flower_type'=>'型号',
            'color_name'=>'颜色','count'=>'数量','all_width'=>'宽','all_height'=>'高','area'=>'报价面积','product_area'=>'产品面积',
            'price'=>'单价','rebate'=>'折扣率','rebate_price'=>'折后价','all_price'=>'总价','note'=>'备注'];
        $title['订单详情'] = $detailField;
        $lists['订单详情'] = $alldata;
        $excel->multi_export($title,$lists,'配送订单详情');
    }

    /**
     * 查看配送中订单
     */
    public function readDelivery()
    {
        $sid = input('id/d');
        $keyword = input('search');
        $where = "1=1";
        if ($keyword != '') {
            $where .= " and (number='$keyword' or dealer like '%$keyword%')";
        }

        $send = Db::name('order_send')->where('id', $sid)->find();
        $orderId = isset($send['all_orderid']) ? $send['all_orderid'] : 0;
        $list = Db::name('order')->whereIn('id', $orderId)->where($where)->order('sort')->select();

        $this->assign('list', $list);
        $this->assign('pay_type', config('pay_type'));
        $this->assign('send',$send);
        return $this->fetch();
    }

    /**
     * 驳回订单
     */
    public function orderBack()
    {
        $id = input('id');
        $res = Db::name('order')->where('id',$id)->update(['is_send'=>0]);
        if($res){
            $this->success('驳回成功');
        }
        $this->error('驳回失败');
    }

    /**
     * 修改配送单
     */
    public function editDelivery()
    {
        if ($this->request->isPost()) {
            $id = input('id/a'); //所有id
            $orderNumber = input('order_number/a'); //所有订单编号
            $count = input('count/a'); //所有数量
            $arrive = input('arrive_time/a'); //所有到达时间
            $sort = input('sort/a'); //排序
            $sendId = input('send_id');
            $sendDate = input('send_date');

            if(!is_array($orderNumber) || count($orderNumber)<=0){
                $this->error('请先添加订单');
            }
            
  
            $allNumber = implode(',', $orderNumber);
            $allId = implode(',', $id);
            $allCount = 0;
            foreach ($count as $k => $v) {
                $allCount += $v;
            }

            //先将所有订单改为 未添加到配送单
            $allorderid = Db::name('order_send')->where('id',$sendId)->find();
            Db::name('order')->whereIn('id',$allorderid['all_orderid'])->update(['is_send'=>0]);

            //修改配送单表
            $res = Db::name('order_send')->where('id', input('send_id'))->update([
                'all_number' => $allNumber, 'all_orderid' => $allId, 'count' => $allCount,'send_date'=>$sendDate,
                'logistics_name' => input('logistics_name'),'logistics_numbers' => input('logistics_numbers'),'is_send' => input('is_send'),
                'driver_name' => input('driver_name'),'driver_phone' => input('driver_phone')
            ]);
            //修改配送单附表
            $oldOrderid = explode(',',$allorderid['all_orderid']);//旧订单id
            $delid = array_diff($oldOrderid,$id);//如果有删除的id
            $addId = [];//如果有新增的 id
            foreach ($id as $k => $v) {
                if(!in_array($v,$oldOrderid)){
                    $addId[] = ['order_id'=>$v,'sid'=>$sendId];
                }
            }
            if($delid){
                Db::name('order_send_detail')->where(['sid'=>$sendId])->whereIn('order_id',$delid)->delete();
            }
            if($addId){
                Db::name('order_send_detail')->insertAll($addId);
            }

            //更新订单信息
            foreach ($id as $k => $v) {
                Db::name('order')->where('id', $v)->update(['arrive_time' => $arrive[$k], 'sort' => $sort[$k],'is_send'=>1]);
            }

            if ($res!==false) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $sid = input('id/d');
        $name = input('search');  //搜索关键字

        $where = "status=7 or status2=7 and is_send=0";
        if ($name != '') {
            $where .= " and (number = $name or dealer like '%$name%')";
        }
        $send = Db::name('order_send')->where('id', $sid)->find();
        $orderid = Db::name('order_send_detail')->where('sid', $sid)->column('order_id');
        $all = Db::name('order')->where($where)->whereNotIn('id',$orderid)->order('id desc')->limit(20)->select();

        $list = Db::name('order')->whereIn('id', $orderid)->order('sort')->select();

        $this->assign('all', $all);
        $this->assign('list', $list);
        $this->assign('send',$send);
        return $this->fetch();
    }

    /**
     * 删除配送单
     */
    public function delDelivery()
    {
        $id = input('id/d');
        $allOrder = Db::name('order_send')->where('id',$id)->find();
        
        $allId = isset($allOrder['all_orderid'])?$allOrder['all_orderid']:0;
        Db::name('order')->whereIn('id', $allId)->update(['is_send'=>0]);
        $res = Db::name('order_send')->where('id', $id)->delete();
        if ($id) {
            Db::name('order_send_detail')->whereIn('sid',$id)->delete();
            $this->success('删除成功');
        }
        $this->error('删除失败,请重试');
    }

    /**
     * 已签收订单
     */
    public function signOrder()
    {
        $name = input('keyword');
        $status = input('status');
        $startTime = input('starttime');
        $endTime = input('endtime');

        $where = "sign_time !=''";
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

        $list = \app\model\Order::with(['paidRecord'])->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));
		
		$res = Db::name('order_send_detail')->alias('a')->join('order_send b','a.sid=b.id')->where('a.order_id','in',$list->column('id'))->column('b.driver_name','a.order_id');
		foreach ($list as &$v) {
			$v['driver_name'] = $res[$v['id']]??'';
		}
		
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('send_status', config('send_status'));
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('status', $status);
		$this->assign('keyword', $name);
        return $this->fetch();
    }

    /**
     * 查看已签收订单
     */
    public function readSignOrder()
    {
        $sid = input('id/d');
        $send = Db::name('order_send')->where('id', $sid)->find();
        $orderId = isset($send['all_orderid']) ? $send['all_orderid'] : 0;
        $list = Db::name('order')->whereIn('id', $orderId)->order('sort')->select();

        $this->assign('list', $list);
        $this->assign('pay_type', config('pay_type'));
        $this->assign('sid', $sid);
        return $this->fetch();
    }

    /**
     * 提交给财务
     */
    public function sendFinance()
    {
        $orderId = input('check/a'); //用户所选的id
        $allNumber = input('number/a'); //当前配送单的全部订单号
        $sid = input('sid/d');  //配送单Id

        if (!is_array($orderId) || count($orderId) <= 0) {
            $this->error('请选择要提交的订单号');
        }

        $orderIds = implode(',', $orderId);
        $send = Db::name('order_send')->where('id', $sid)->find();
        $allId = isset($send['all_orderid'])?$send['all_orderid']:0; 
        $allId = explode(',', $allId);    //配送单内的全部订单id    
        $noSelectId = array_diff($allId,$orderId); //用户未选的订单id
        $noSelectIds = implode(',', $noSelectId);
        
        $number = Db::name('order')->whereIn('id', $orderIds)->column('number'); //用户所选的订单号
        //有提交财务的订单编号改成红色
        $new = [];
        foreach ($allNumber as $k => $v) {
            if (in_array($v, $number)) {
                $new[] = "<span class='red'>$v</span>";
            } else {
                $new[] = $v;
            }
        }
        $snumber = implode(',', $new);
        
        //判断配送单内订单是否全部发送到财务审核
        $sendFinance = 0;        
        if(count($noSelectId) == 0){
            $sendFinance = 1;
        }
        $res = Db::name('order_send')->where('id', $sid)->update(['all_number' => $snumber,'send_finance'=>$sendFinance]);

        //更新所提交的订单状态
        $sres = Db::name('order')->whereIn('id', $orderIds)->update(['status' => 5, 'send_finance' => 1]);
        Db::name('order')->whereIn('id', $noSelectIds)->update(['send_finance' => 0]);

        if ($res !== false && $sres !== false) {
            $this->success('提交成功');
        }
        $this->error('提交失败,请重试');
    }
	
	
	public function printTag()
    {
		$number 	= input('number');
		if($this->request->isAjax()){
			$order 	= Db::name('order')->field('id,number,dealer,send_address,address,building')->where('number', $number)->find();
			$flow	= [];
			$price 	= [];
			if(!empty($order['id'])){
				$price 	= Db::name('order_price')->field('op_id,position,count,all_width,all_height')->where('order_id', $order['id'])->where('order_type', '<>',2)->select();
			}
			/*
			$db2 	= Db::connect('database.db_baogong');
			$flow 	= $db2->name('flow_check')->alias('a')
			->join('gx_list b','a.orstatus=b.id')
			->field('a.orderid,a.error_time,a.stext,a.orstatus,b.dname')->where('a.error_time','>',0)->where('a.stext','<>','')->where('a.stext','NOT NULL')
			->where('a.orderid',$db2->name('order')->where('ordernum',$number)->value('id'))->select();*/
			$this->success('成功',['order'=>$order,'flow'=>$flow,'price'=>$price]);
		}else{
			$this->assign('number', $number);
			return $this->fetch();
		}
    }

}
