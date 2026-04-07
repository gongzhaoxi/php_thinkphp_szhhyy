<?php


namespace app\admin\controller;

use think\Db;

/**
 * 经销商下单
 */
class Dealerorder extends Base
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->assign('group_id',$this->group_id);
    }

    /**
     * 获取报价单列表的搜索
     * @param string
     */
    public function getPriceWhere()
    {
        $keyword = str_replace(' ','',input('keyword'));//搜索关键字
        $start = input('start_time');
        $end = input('end_time');
        $sales_name = input('sales_name');
        $type = input('type');
        if($this->uid == 1) {
            $where = "add_type=1";
        }else{
            $where = "dealer_id=".$this->bind_dealer."";
        }
        if($keyword){
            $where .= " and (replace(a.dealer,' ','') like '%$keyword%' or a.number like '%$keyword%' or phone like '%$keyword%' or send_address like '%$keyword%')";
        }
        if($start){
            $startTime = strtotime($start);
            $where .= " and a.addtime>='$startTime'";
        }
        if($end){
            $endTime = strtotime($end)+24*3600;
            $where .= " and a.addtime<='$endTime'";
        }
        if($sales_name){
            $where .= " and a.sales_name like '%$sales_name%'";
        }
        if($type){
            $where .= " and a.type like '%$type%'";
        }
        return $where;
    }

    /**
     * 已报价订单
     */
    public function priced()
    {
        $keyword = input('keyword');//搜索关键字
        $start = input('start_time');
        $end = input('end_time');
        $sales_name = input('sales_name');
        $type = input('type');
        $where = $this->getPriceWhere();
        $statusText = config('order_status');
        $statusText2 = config('order_status2');
        // $nopay = Db::name('order')->field('sum(total_price-have_pay-finance_rebate_price) as no_pay')
        //    ->where("dealer_id=$this->bind_dealer")->find();
		   
		//$nopay = Db::name('order')->alias('a')->where($where)->sum('have_pay');

        $price = Db::name('order')->alias('a')->where($where)->sum('total_price');
		$rebateprice =  Db::name('order')->alias('a')->where($where)->sum('finance_rebate_price');
		$nopay =  Db::name('order')->alias('a')->where($where)->sum('have_pay');
		$nopay = $price-$nopay-$rebateprice;
		
		// $price =  Db::name('order')->alias('a')->where($where)->sum('total_price');
		// $rebateprice =  Db::name('order')->alias('a')->where($where)->sum('finance_rebate_price');
		// $nopay =  Db::name('order')->alias('a')->where($where)->sum('have_pay');
		
		
		
		
		
        $list = Db::name('order')->alias('a')->field('a.*,b.order_id')
            ->join('order_price b','a.id=b.order_id','left')
//            ->where("add_type=1")
            ->where($where)
            ->group('a.id')
            ->order('id desc')
            ->paginate()
            ->each(function ($item,$key) use($statusText,$statusText2){
                if($item['add_type'] == 1 && $item['dealer_status'] == 0){
                    $item['status_text'] = "营运部审核中";
                }else{
                    if($item['status'] > 1){
                        $item['status_text'] = $statusText[$item['status']];
                    }else{
                        $item['status_text'] = $statusText2[$item['status2']];
                    }
                    if($item['is_send']==1){
                        $item['status_text'] = "配送中";
                    }
                    if($item['sign_time']!=0){
                        $item['status_text'] = "已签收";
                    }

                }
                return $item;
            });
        $salesName = Db::name('dealer')->group('sales_name')->select();
        //订单进度状态
		
        //$where = $this->getPriceWhere();
        // $lista = Db::name('order')->alias('a')->field('a.*,b.order_id')
        //             ->join('order_price b','a.id=b.order_id','left')
        //             ->where($where)
        //             ->group('a.id')
        //             ->order('id desc')
        //             ->paginate();
        $array = $list->all();
		$number = array_column($array,'number');
		$progress = Db::connect('database.db2')->table('henghuiorder')->whereIn('number',$number)->select();
		//获取车间的生产进度
		$gx = [];
		foreach ($progress as $k => $v) {
		    $state = explode('|',$v['state']);
		    //找到不等于 未开始 的位数
		    $position = 0;
		    foreach ($state as $k2 => $v2) {
		        $position = $k2;
		        if($v2 == '未开始'){
		            $position = $k2-1;
		            break;
		        }
		    }
		    $gxname = explode('|',$v['wp']);
		    $gx[$v['number']] = empty($gxname[$position])?'生产中(未开始工序)':$gxname[$position];
		}
		
		foreach ($array as $k => $v) {
		    $array[$k]['gxname'] = isset($gx[$v['number']])?$gx[$v['number']]:'生产中(未开始工序)';
		}
		
		
		
			
  //       $this->assign('sales_name',$salesName);
  //       $list->appends(input('get.'));
  //       $this->assign('total_money',$total_money);
  //       $this->assign('page', $list->render());
  //       $this->assign('list', $list);
  //       $this->assign('keyword_search',$keyword);
  //       $this->assign('start_search',$start);
  //       $this->assign('end_search',$end);
  //       $this->assign('sales_name_search',$sales_name);
  //       $this->assign('type_search',$type);
  //       $this->assign('no_pay',$this->bind_dealer==0?['no_pay'=>0]:$nopay);
		
		
		// Db::connect('database.db2')->close();
  //       return $this->fetch();
		
		
		
		$salesName = Db::name('dealer')->group('sales_name')->select();
		$this->assign('sales_name', $salesName);
		$this->assign('dealer_id', $dealerid);
		$list->appends(input('get.'));
		$this->assign('page', $list->render());
		$this->assign('list', $array);
		$this->assign('keyword_search', $keyword);
		$this->assign('start_search', $start);
		$this->assign('end_search', $end);
		$this->assign('sales_name_search', $sales_name);
		$this->assign('status_text', config('order_status'));
		$this->assign('status_text2', config('order_status2'));
		$this->assign('search',input('get.'));
		$this->assign('type_search',$type);
		$this->assign('total_money',$total_money);
		$this->assign('no_pay',$nopay);
		$this->assign('statistics',[
		    'area'=>round($area,2),'count'=>$allOrder,'price'=>$price,'nopay'=>$nopay,'wait_product'=>round($watProduct,2),
		    'producting' => round($producting,2),'into' => round($into,2)
		]);
		
		// $this->assign('uid', $this->uid);
		
		Db::connect('database.db2')->close();
		$this->assign('order_type',config('order_type'));
		return $this->fetch();
    }

    /**
     * 导出已报价订单
     */
    public function exportPrice()
    {
        $startTime = input('start_time');
        $endTime = input('end_time');
        if($startTime && $endTime) {
            $title = $startTime . '--' . $endTime . '订购清单';
        }else{
            $title = "订购清单";
        }
        $where = $this->getPriceWhere();
        $list = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer,a.phone,a.address"
            . ",a.send_address,b.material,b.color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.note,b.flower_type,b.all_width,b.all_height")
            ->join('order_price b','a.id=b.order_id')
            ->where($where)
            ->order('a.id desc')
            ->select();
        $material = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer,a.phone,a.address"
            . ",a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,'' as note,'' as flower_type,b.width as all_width,b.height as all_height")
            ->join('order_material b','a.id=b.order_id')
            ->where($where)
            ->order('a.id desc')
            ->select();
        $list = array_merge($list,$material);
        $excel = new \excel\Excel();
        $headArr = ['订单日期','订单编号','业务员','客户名称','电话','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
        $field = ['addtime','number','sales_name','dealer','phone','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
        $excel->export('报价清单', $headArr, $list, $field, $title);
    }

    /**
     * 添加订单基本信息
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['status'] = 1;
            $data['status2'] = 1;
            $data['addtime'] = time();
            $data['uid'] = $this->uid;
            $data['add_type'] = 1;
            $data['dealer_status'] = $this->group_id==2?0:1;
            $type = $data['type'];
            $timec = timezone_get(1);
            $findCount = Db::name('order')->where("addtime>={$timec['begin']} and addtime<={$timec['end']}")->count();
            //生成订单号，如果有添加订单则最新订单加1，否则为1
            if($findCount > 0){
                $last = Db::name('order')->where("addtime>={$timec['begin']} and addtime<={$timec['end']}")->order('id desc')->find();
                $lastNumber = isset($last['number'])?$last['number']:0;
                $number = str_pad($lastNumber+1,3,'0',STR_PAD_LEFT);
            }else{
                $number = date('Ymd'). str_pad(1,3,'0',STR_PAD_LEFT);
            }

            //二维码
            $qrcode = qrcode($number.','.date('Y-m-d',time()).','.$data['dealer']);
            $data['qrcode'] = '/upload/qrcode/'.date('Ymd').'/'.$qrcode;
            $data['number'] = $number;

            //更新经销商下单时间
            if(input('dealer_id/d')!=0){
                Db::name('dealer')->where('id', input('dealer_id/d'))->update(['order_time'=> time()]);
                $dealer = Db::name('dealer')->where('id', input('dealer_id/d'))->find();
                $data['sales_name'] = $dealer['sales_name'];
            }

            //生产完成时间
            $makeTime = date('Y-m-d',strtotime($data['end_time'])-2*24*3600);
            $data['make_time'] = $type == 2?$data['end_time']:$makeTime;
            $res = Db::name('order')->insertGetId($data);

            if ($res) {
                $this->success('保存成功', '', url('edit', array('id' => $res)));
            }
            $this->error('保存失败，请重试');
            return;
        }
        $endtime = date('Y-m-d',time()+7*24*3600);
        $productTime = date('Y-m-d',time()+5*24*3600);
        $dealer = Db::name('dealer')->where('id',$this->bind_dealer)->find();

        $this->assign('product_time',$productTime);
        $this->assign('etime',$endtime);
        $this->assign('dealer',$dealer);
        return $this->fetch();
    }

    /**
     * 添加，编辑订单基本信息
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $orderId = input('order_id/d');
            $type = $data['type'];

            $makeTime = date('Y-m-d',strtotime($data['end_time'])-2*24*3600);
            $data['make_time'] = $type == 2?$data['end_time']:$makeTime;
            $res = Db::name('order')->where('id', $orderId)->update($data);
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
        $product = Db::name('order_price')->alias('a')->field('a.*,b.structure')
            ->join('order_calculation b', 'a.op_id=b.op_id')
            ->where('a.order_id', $id)
            ->select();
        $this->assign('product', $product);
        return $this->fetch();
    }

    /**
     * 审核订单
     */
    public function check()
    {
        $id = input('id/d');
        if($this->group_id != 1){
            $this->error('你无权操作');
        }
        $res = Db::name('order')->where('id',$id)->update(['dealer_status'=>1]);
        if($res !== false){
            $this->success('审核成功');
        }
        $this->error('审核失败,请重试');
    }

    /**
     * 经销商查单
     */
    public function progress()
    {
        $dealer = Db::name('dealer')->where('id',$this->bind_dealer)->find();

        $ordernum = input("ordernum/s");
        $ordertime = input("ordertime/s");
        $uname = input("uname/s");
        $phone = input('phone/s');
        $isMobile = input('is_mobile');

        $where = "Cname='$dealer[name]'";
        if (!empty($ordertime)) {
            $start = strtotime($ordertime);
            $end = strtotime($ordertime . ' 23:59:59');
            $where .= " and time between $start and $end";
        }
        !empty($ordernum) ? $where .= " and number like '%$ordernum%'" : $where;
        !empty($uname) ? $where .= " and Cname like '%$uname%'" : $where;
        !empty($phone) ? $where .= " and tel like '%$phone%'" : $where;
        $condition = array();
        $condition['ordernum'] = $ordernum;
        $condition['ordertime'] = $ordertime;
        $condition['uname'] = $uname;
        $condition['phone'] = $phone;
        $this->assign('condition', $condition);

        $result = Db::connect('database.db2')->table('henghuiorder')->where($where)
            ->order('id desc')->paginate(20, false, ['query' => request()->param()]);
        if ($result) {
            $list = $result->all();
            foreach ($list as $key => $val) {
                $state_arr = array();
                $wp = explode('|', $val['wp']);
                $state = explode('|', $val['state']);
                foreach ($wp as $k2 => $v2) {
                    if($state[$k2] != '未开始'){
                        $temp = explode('-',$state[$k2]);
                        if(is_array($temp) && count($temp) > 0) {
                            $temp1 = isset($temp[1])?$temp[1]:'';
                            $temp0 = isset($temp[0])?$temp[0]:'';
                            $time = $temp0 . '<br/>' . $temp1;
                        }else{
                            $time = $state[$k2];
                        }
                    }else{
                        $time = $state[$k2];
                    }
                    $in_arr = array('name' => $v2, 'time' => $time);
                    $state_arr[] = $in_arr;
                }
                $list[$key]['status'] = $state_arr;
            }

            $page = $result->render();
            $total = $result->total();
            $this->assign("page", $page);
            $this->assign("total", $total);
            $this->assign("list", $list);
        }

        return $this->fetch('orderprogress/orderdetail');
    }

}