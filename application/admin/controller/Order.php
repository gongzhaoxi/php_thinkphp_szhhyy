<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use app\admin\logic\orderLogic;

/**
 * 运营部订单控制器
 */
class Order extends Base
{

    /**
     * 刷星第三方数据库
     */
    public function refreshThird()
    {
        set_time_limit(0);
        $id = input('id');
        $order = Db::name('order')->where('id',$id)->find();
        $product = Db::name('order_price')->field('b.*,a.*,a.op_id as op_id')->alias('a')->join('order_calculation b','a.op_id=b.op_id','left')->where('order_id',$id)->select();
        $group = Db::name('order_group')->where('order_id',$id)->select();
        $material = Db::name('order_material')->where('order_id',$id)->select();
        $cal = [];
        foreach ($product as $k => $v) {
            if(isset($v['oc_id'])){
                $cal[] = ['oc_id'=>$v['oc_id'],'op_id'=>$v['op_id'],'spacing'=>$v['spacing'],'structure_id'=>$v['structure_id'],'structure'=>$v['structure'],
                    'fixed_height'=>$v['fixed_height'],'hands'=>$v['hands'],'lock_position'=>$v['lock_position']
                ];
            }
        }
        $db2 = Db::connect('database.db2');
        Db::startTrans();
        try{
            $find = $db2->table('erp_order')->where('id',$id)->find();
            if(!$find){
                $db2->table('erp_order')->insert($order);
            }else{
                unset($order[0]);
                $db2->table('erp_order')->where('id',$id)->update($order);
            }

            $oldproduct = $db2->table('erp_order_price')->where('order_id',$id)->column('op_id');//旧产品数据
            //更新产品表
            foreach ($product as $k => $v) {
                $opid = $v['op_id'];
                if(!in_array($opid,$oldproduct)){
                    $db2->table('erp_order_price')->insert($v);
                }else{
                    unset($v['op_id']);
                    $db2->table('erp_order_price')->where('op_id',$opid)->where('order_id',$v['order_id'])->update($v);
                }
            }
            $delete = array_diff($oldproduct,array_column($product,'op_id'));//如果有删除的数据
            if($delete){
                $db2->table('erp_order_price')->whereIn('op_id',$delete)->delete();
            }

            $allopid = array_column($cal,'op_id');
            $oldcal = $db2->table('erp_order_calculation')->whereIn('op_id',$allopid)->column('oc_id');//旧产品数据
            //更新算料信息表
            foreach ($cal as $k => $v) {
                $ocid = $v['oc_id'];
                if(!in_array($ocid,$oldcal)){
                    $db2->table('erp_order_calculation')->insert($v);
                }else{
                    unset($v['oc_id']);
                    $db2->table('erp_order_calculation')->where('oc_id',$ocid)->update($v);
                }
            }
            $deletecal = array_diff($oldcal,array_column($cal,'oc_id'));//如果有删除的数据
            if($deletecal){
                $db2->table('erp_order_calculation')->whereIn('oc_id',$deletecal)->delete();
            }

            $oldgroup = $db2->table('erp_order_group')->whereIn('order_id',$id)->column('og_id');
            //更新组合单表
            foreach ($group as $k => $v) {
                $ogid = $v['og_id'];
                if(!in_array($ogid,$oldgroup)){
                    $db2->table('erp_order_group')->insert($v);
                }else{
                    unset($v['og_id']);
                    $db2->table('erp_order_group')->where('og_id',$ogid)->update($v);
                }
            }
            $delgroup = array_diff($oldgroup,array_column($group,'og_id'));//如果有删除的数据
            if($delgroup){
                $db2->table('erp_order_group')->whereIn('og_id',$delgroup)->delete();
            }

            $oldmaterial = $db2->table('erp_order_material')->whereIn('order_id',$id)->column('om_id');//旧数据
            //更新原材料
            foreach ($material as $k => $v) {
                $omid = $v['om_id'];
                if(!in_array($omid,$oldmaterial)){
                    $db2->table('erp_order_material')->insert($v);
                }else{
                    unset($v['om_id']);
                    $db2->table('erp_order_material')->where('om_id',$omid)->update($v);
                }
            }
            $delmaterial = array_diff($oldmaterial,array_column($material,'om_id'));//如果有删除的数据
            if($delmaterial){
                $db2->table('erp_order_material')->whereIn('om_id',$delmaterial)->delete();
            }

            $db2->table('erp_update_log')->insert(['number'=>$order['number'],'date'=>date('Y-m-d H:i:s',time())]);
            Db::commit();
            $this->success('刷新成功');
        }catch (\Exception $e){
            Db::rollback();
            $this->error('刷新失败');
        }


    }

    /**
     * 刷新订单的交货时间和生产时间
     */
    public function refreshTime()
    {
       $orderId = input('order_id/d');
       $endtime = date('Y-m-d',time()+7*24*3600);
       $productTime = date('Y-m-d',time()+5*24*3600);
       $res = Db::name('order')->where('id',$orderId)->update(['end_time' => $endtime,'make_time' => $productTime]);
       if($res!==FALSE){
           $this->success('刷新成功');
       }
       $this->error('刷新失败');
    }
    
    /**
     * 刷新订单的交货时间和生产时间
     */
    public function refreshTime2()
    {
       $date = input('date');
       $date = date('Y-m-d',strtotime($date)-(2*24*3600));
    
       $this->success('刷新成功',$date);
       
    }
    
    /**
     * 查询订单总金额，数量接口
     */
    public function findTotal()
    {
        $orderId = input('order_id/d');
        $total = Db::name('order')->where('id',$orderId)->find();

        $this->success('',$total);
    }
    
    /**
     * 异步查询经销商--订单基本信息
     */
    public function findBasic()
    {
        $keyword = input('name');
        $field = input('field');
        if($keyword == ''){
            $this->error('');
        }
        if(!in_array($field, ['name','contact'])){
            return;
        }
        
        $list = Db::name('dealer')->where([$field=>['like','%'.$keyword.'%']])->whereOr(['back_contact'=>['like',$keyword.'%']])
                ->whereOr(['code'=>['like',$keyword.'%']])->limit(5)->select();
        $this->success('',$list);
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
            $type = $data['type'];
//            $number = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $timec = timezone_get(1); 
            $findCount = Db::name('order')->where("addtime>={$timec['begin']} and addtime<={$timec['end']}")->count();
            //生成订单号，如果有添加订单则最新订单加1，否则为1
            if($findCount > 0){
                $last = Db::name('order')->where("addtime>={$timec['begin']} and addtime<={$timec['end']}")->order('id desc')->find();
                $lastNumber = isset($last['number'])?$last['number']:0;
    // 			if(strstr($lastNumber,'SZ')){
    // 			 $lastNumber = (int)str_replace('SZ','',$lastNumber);
    // 			}
                $number = str_pad($lastNumber+1,3,'0',STR_PAD_LEFT);
            }else{
                $number = date('Ymd'). str_pad(1,3,'0',STR_PAD_LEFT);
            }
            
            
            //查询是否存在此经销商
            $contact = explode('/', $data['phone']);            
            foreach ($contact as $key => $value){
                $find = Db::name('dealer')->where("name='{$data['dealer']}' and (contact like '%$value%' or back_contact='$value')")->find();
                if(!$find){
                    $this->error('没有此名称和电话的经销商');
                }
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
                $this->success('保存成功', '', url('order/edit', array('id' => $res)));
            }
            $this->error('保存失败，请重试');
            return;
        }
        $endtime = date('Y-m-d',time()+7*24*3600);
        $productTime = date('Y-m-d',time()+5*24*3600);
        $this->assign('product_time',$productTime);
        $this->assign('etime',$endtime);
		$this->assign('order_type',config('order_type'));
        return $this->fetch();
    }

    /**
     * 财务--未处理订单数量
     */
    public function financeNoHandle()
    {
        $list = Db::name('order')->where("status=2 or status2=4")->order('id desc')->count();
        $this->success('',$list);
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
        $where = $this->getPriceWhere();
        $list = Db::name('order')->alias('a')->field('a.*,b.order_id')
                    ->join('order_price b','a.id=b.order_id','left')
                    ->where("(status=1 and status2=1) or status2=3")
                    ->where($where)
                    ->group('a.id')
                    ->order('id desc')
                    ->paginate();
        $salesName = Db::name('dealer')->group('sales_name')->select();
        $this->assign('sales_name',$salesName);
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        $this->assign('keyword_search',$keyword);
        $this->assign('start_search',$start);
        $this->assign('end_search',$end);
        $this->assign('sales_name_search',$sales_name);
        $this->assign('order_type',config('order_type'));
        return $this->fetch();
    }
    
    /**
     * 全部订单
     */
    public function allorder()
    {
        $keyword = input('keyword');//搜索关键字
        $start = input('start_time');
        $end = input('end_time');
        $sales_name = input('sales_name');
				$dealerid = input('dealer_id');
		$type = input('type');
		if($type){
			$type = is_array($type)?$type:explode(',',$type);
		}else{
			$type = [];
		}
        $where = $this->getPriceWhere();
        $list = Db::name('order')->alias('a')->field('a.*,b.order_id')
                    ->join('order_price b','a.id=b.order_id','left')
                    ->where($where)
                    ->group('a.id')
                    ->order('id desc')
                    ->paginate();
        $array = $list->all();

        //统计数据
        $allOrder = Db::name('order')->alias('a')->where($where)->count();
		// 2025-2-24 麦 改为不过滤 “ 返修单 ”
  //       $area =  Db::name('order')->alias('a')->where($where)->where("type!=4")->sum('area');
  //       $price =  Db::name('order')->alias('a')->where($where)->where("type!=4")->sum('total_price');
		// $rebateprice =  Db::name('order')->alias('a')->where($where)->where("type!=4")->sum('finance_rebate_price');
		// $nopay =  Db::name('order')->alias('a')->where($where)->where("type!=4")->sum('have_pay');
		$area =  Db::name('order')->alias('a')->where($where)->sum('area');
		$price =  Db::name('order')->alias('a')->where($where)->sum('total_price');
		$rebateprice =  Db::name('order')->alias('a')->where($where)->sum('finance_rebate_price');
		$nopay =  Db::name('order')->alias('a')->where($where)->sum('have_pay');
		$nopay = $price-$nopay-$rebateprice;
        $watProduct = Db::name('order')->alias('a')->where($where)->where("status=3 or status2=5")->sum('area');
        $producting = Db::name('order')->alias('a')->where($where)->where("status=4 or status2=6")->sum('area');
        $into = Db::name('order')->alias('a')->where($where)->where("status>=7 or status2>=7")->sum('area');

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
        $this->assign('statistics',[
            'area'=>round($area,2),'count'=>$allOrder,'price'=>$price,'nopay'=>$nopay,'wait_product'=>round($watProduct,2),
            'producting' => round($producting,2),'into' => round($into,2)
        ]);
        
        $this->assign('uid', $this->uid);
        $this->assign('type', $type);
        Db::connect('database.db2')->close();
        $this->assign('order_type',config('order_type'));
        return $this->fetch();
    }

    /**
     * 营运部审核订单,即status2=3
     */
    public function status2check()
    {

        $keyword = input('keyword');//搜索关键字
        $start = input('start_time');
        $end = input('end_time');
        $sales_name = input('sales_name');
        $where = $this->getPriceWhere();
        $list = Db::name('order')->alias('a')->field('a.*,b.order_id')
            ->join('order_price b','a.id=b.order_id','left')
            ->where($where)
            ->where('status2',3)
            ->group('a.id')
            ->order('id desc')
            ->paginate();
        $salesName = Db::name('dealer')->group('sales_name')->select();
        $this->assign('sales_name',$salesName);
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        $this->assign('keyword_search',$keyword);
        $this->assign('start_search',$start);
        $this->assign('end_search',$end);
        $this->assign('sales_name_search',$sales_name);
        $this->assign('status_text', config('order_status'));
        $this->assign('status_text2', config('order_status2'));
        return $this->fetch('priced');
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
        $province = input('province');
        $city = input('city');
        $area = input('area');
				$dealerid = input('dealer_id');
		$type = input('type');
        $where = "(add_type=0 or dealer_status=1)";
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
            $type = is_array($type)?$type:explode(',',$type);
			$where .= " and a.type in (".implode(',',$type).")";
        }	
        $areaWhere = "";
        if($province != ''){
            $dealerId = Db::name('dealer')->where('province',$province)->column('id');
            if($dealerId){
                $areaWhere = " and dealer_id in (".implode(',',$dealerId).")";
            }
        }
        if($city != ''){
            $dealerId = Db::name('dealer')->where('city',$city)->column('id');
            if($dealerId){
                $areaWhere = "and dealer_id in (".implode(',',$dealerId).")";
            }
        }
        if($area != ''){
            $dealerId = Db::name('dealer')->where('area',$area)->column('id');
            if($dealerId){
                $areaWhere = "and dealer_id in (".implode(',',$dealerId).")";
            }
        }
				if($dealerid){
					$where .= " and a.dealer_id='$dealerid'";
				}
				
        $where .= $areaWhere;
        return $where;
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
        $ordertype = config('order_type');
        $list = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
                . ",a.status2,a.status,a.is_send,a.sign_time,a.type,a.send_address,b.material,b.yarn_color,b.color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.note,b.flower_type,b.all_width,b.all_height")
                ->join('order_price b','a.id=b.order_id')
                ->where($where)
                ->order('a.id desc')
                ->select();
        $material = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
                . ",a.status2,a.status,a.is_send,a.sign_time,a.type,a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,'' as note,'' as flower_type,b.width as all_width,b.height as all_height")
                ->join('order_material b','a.id=b.order_id')
                ->where($where)
                ->order('a.id desc')
                ->select();
        $list = array_merge($list,$material);
        foreach ($list as $k => $v) {
            $list[$k]['type'] = $ordertype[$v['type']];
            
            $list[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
            $list[$k]['material'] = $v['material'].'/'.$list[$k]['yarn_color'];
 			$status = '';
			if($v['status2'] == 1){
				if($v['status'] == 1){
					$status = '已报价';
				}else if($v['status'] == 2){
					$status = '待财务审核';
				}else if($v['status'] == 3){
					$status = '待生产';
				}else if($v['status'] == 4){
					$status = '生产中';
				}
			}
			if($v['status'] == 4 && $v['status2'] == 7){
				if($v['is_send'] == 0){
					$status = '待配送';
				}else{
					if($v['sign_time'] == 0){
						$status = '配送中';
					}else{
						$status = '已签收';
					}
				}
			}
            $list[$k]['status'] = $status;           
        }
        $excel = new \excel\Excel();
        // $headArr = ['订单日期','订单编号','订单类型','业务员','客户名称','电话','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
        // $field = ['addtime','number','type','sales_name','dealer','phone','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
        // $excel->export('报价清单', $headArr, $list, $field, $title);
				$headArr = ['订单日期','订单编号','订单类型','业务员','客户ID编号','客户名称','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注','状态'];
				$field = ['addtime','number','type','sales_name','dealer_id','dealer','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note','status'];
				$excel->export('报价清单', $headArr, $list, $field, $title);
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
            $havePay = input('have_pay');


            //定金收款只记录 一次
            $find = Db::name('paid_record')->where(['type'=>1,'order_id'=>$orderId])->find();
            //插入财务收款 记录表
            if($havePay || $find){
                if(!$find){
                    Db::name('paid_record')->insert([
                        'type'=>1,'order_id'=>$orderId,'pay_type'=>$data['pay_type'],'have_pay'=>$havePay,'addtime'=>time()
                    ]);
                }else{
                    Db::name('paid_record')->where(['type'=>1,'order_id'=>$orderId])->update([
                        'type'=>1,'order_id'=>$orderId,'pay_type'=>$data['pay_type'],'have_pay'=>$havePay
                    ]);
                }
            }
            $makeTime = date('Y-m-d',strtotime($data['end_time'])-2*24*3600);
            $data['make_time'] = $type == 2?$data['end_time']:$makeTime;
            //订单的收款金额
            $orderPaid = Db::name('paid_record')->where('order_id',$orderId)->sum('have_pay');
            $data['have_pay'] = $orderPaid;
            $data['no_pay'] = $data['total_price']-$orderPaid;
            unset($data['total_price']);
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

        //组合单
        $group = Db::name('order_group')->where('order_id', $id)->select();

        //定制类产品
        $diy = Db::name('order_price')->where('order_id',$id)->where('order_type',4)->select();
        
        //订单原材料
        $material = Db::name('order_material')->where('order_id', $id)->select();
        $materialParea = array_sum(array_column($material, 'product_area'));
        //定金收款记录
        $havePay = Db::name('paid_record')->where(['order_id'=>$id,'type'=>1])->find();
        
        $dealer_id = $res['dealer_id'];		
		//总欠款
		$Ttotal_price = Db::name('order')->where('dealer_id', $dealer_id)->sum('total_price');
		$Thave_pay = Db::name('order')->where('dealer_id', $dealer_id)->sum('have_pay');
		$Tother_pay = Db::name('order')->where('dealer_id', $dealer_id)->sum('other_pay');
		$Tfinance_rebate_price = Db::name('order')->where('dealer_id', $dealer_id)->sum('finance_rebate_price');
		$Tno_pay = $Ttotal_price-$Thave_pay-$Tother_pay-$Tfinance_rebate_price;

        $this->assign('have_pay',$havePay);
        $this->assign('material_parea',$materialParea);
        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('group',$group);
        $this->assign('diy',$diy);
        $this->assign('Tno_pay',$Tno_pay);
        $this->assign('pay_type',config('pay_type'));
        $this->assign('uid', $this->uid);
		$this->assign('order_type',config('order_type'));
        return $this->fetch();
    }
    /**
	 * 手动入库
	 */
	public function editStatus()
	{
		$orderId = input('order_id/d');
		$res = Db::name('order')->where('id', $orderId)->update(['status'=>7,'status2'=>7,'intime'=>time()]);
		if ($res !== false) {
		    $this->success('保存成功');
		}
		$this->error('保存失败，请重试');
		return;
	}

    /**
     * 订单产品表格数据
     */
    public function productTable()
    {
        $orderId = input('order_id');
        $product = Db::name('order_price')->alias('a')->field('a.*')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where("a.order_id=$orderId and a.order_type<=1")
                ->select();
        foreach ($product as $k => $v) {
            $product[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
            $product[$k]['material'] = $v['material'].'/'.$product[$k]['yarn_color'];
        }
        $this->success('', $product);
    }

    /**
     * 原材料表格数据
     */
    public function materialTable()
    {
        $orderId = input('order_id');
        $material = Db::name('order_material')->where('order_id', $orderId)->select();
        $this->success('', $material);
    }

    /**
     * 产品价格修改
     */
    public function editProductPrice()
    {
        $data = input('data/a');  //当前行所有键值
        $id = $data['op_id'];   //更新的订单产品表id
        $field = input('field');  //更新的字段
        $value = input('value');  //更新的价格
        $orderId = input('order_id'); //订单id
        $isGroup = input('is_group');  //是否是组合单产品
        $current = Db::name('order_price')->where('op_id',$id)->find();//表格所有数据后台查找，前端提交的数据可能不对,没实时更新
        $current[$field] = $value;

        $logic = new orderLogic();
        $res = $logic->editPrice('order_price', $field, $value, 'op_id', $id, $orderId, $current,$isGroup);
        if ($res) {
            $this->success('保存成功', $res);
        }
        $this->error('保存失败，请重试');
    }

    /**
     * 原材料价格修改
     */
    public function editMaterialPrice()
    {
        $data = input('data/a');  //当前行所有键值
        $id = $data['om_id'];
        $field = input('field');  //更新的字段
        $value = input('value/d');  //更新的价格
        $orderId = input('order_id'); //订单id
        $totalPrice = input('total_price');  //订单总价格

        $logic = new orderLogic();
        $res = $logic->editPrice('order_material', $field, $value, 'om_id', $id, $orderId, $totalPrice);
        if ($res) {
            $this->success('保存成功', $res);
        }
        $this->error('保存失败，请重试');
    }

    /**
     * 添加产品
     */
    public function addProduct()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $orderId = input('order_id/d');

            //如果是经销商下单，每次下的单必须为同一系列
            $res = Db::name('order_price')->field('a.*,b.add_type')->alias('a')->join('order b','a.order_id=b.id')->where('order_id',$orderId)->find();
            if($res && $res['add_type'] == 1){
                if($res['series_id'] != $data['series_id']){
                    $this->error('必须添加相同系列的产品');
                }
            }
            $order = new orderLogic();
            $res = $order->addInfo($data, $orderId);
            if ($res) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }

        $orderId = input('orderid');
        $this->assign('orderid', $orderId);
        //工艺一级栏目数据
        $one = Db::name('series')->where('parent_id', 0)->select();
        $this->assign('one', $one);
        $res = Db::name('order')->field('a.*,b.dealer_rebate')->alias('a')->join('dealer b','a.dealer_id=b.id','left')
            ->where('a.id',$orderId)->find();
        $this->assign('res',$res);
        return $this->fetch();
    }

    /**
     * 编辑产品
     */
    public function editProduct()
    {

        if ($this->request->isPost()) {
            $opid = input('op_id/d');
            $data = input('post.');
            //如果是经销商下单，每次下的单必须为同一系列
            $res = Db::name('order_price')->field('a.*,b.add_type')->alias('a')->join('order b','a.order_id=b.id')->where('order_id',$data['order_id'])->where('op_id','<>',$opid)->find();
            if($res && $res['add_type'] == 1){
                if($res['series_id'] != $data['series_id']){
                    $this->error('必须添加相同系列的产品');
                }
            }

            $order = new orderLogic();
            $res = $order->editInfo($data, $data['order_id']);
            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $opid = input('id/d');
        $product = Db::name('order_price')->alias('a')->field('a.*,b.*,c.min,c.max,c.name as tips_name,d.add_type')
                        ->join('order_calculation b', 'a.op_id=b.op_id')
                        ->join('order_tips c','a.op_id=c.op_id','left')
                        ->join('order d','a.order_id=d.id')
                        ->where('a.op_id', $opid)
                        ->find();
        
        $order = new orderLogic();
        $array = $order->getProductInfo($product); //多级栏目数组及系列id    
        $flowerHeight = Db::name('bom_flower')->where('id',$product['flower_id'])->find();
        $flowerHeights = Db::name('bom_flower')->where('id',$product['flower_ids'])->find();

        $seriesFilter = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five']; //工艺系列html id
        $colorFilter = [1 => 'color', 2 => 'color_two', 3 => 'color_three']; //铝型花件颜色html lay-filter
        $this->assign('seriesFilter', $seriesFilter);
        $this->assign('colorFilter', $colorFilter);
        $this->assign('flowerHeight',$flowerHeight);
        $this->assign('flowerHeights',$flowerHeights);
        $this->assign('opid', $opid);
        $this->assign('array', $array);
        $this->assign('flower', $array['flower']);
        $this->assign('info', $product);
        $this->assign('is_alum_diy', array_keys($array['alum_color_array'])[0]);  //铝型颜色是否自定义
        $flowerDiy = is_array($array['flower_color_array']) ? array_keys($array['flower_color_array'])[0] : '';
        $this->assign('is_flower_diy', $flowerDiy); //花件颜色是否自定义
        $this->assign('product',$product);
        return $this->fetch();
    }

    /**
     * 删除订单
     */
    public function delOrder()
    {
        $orderId = input('id/d');      
        $orderRes = Db::name('order')->where('id',$orderId)->delete();
        $priceRes = Db::name('order_price')->where('order_id',$orderId)->delete();
        $material = Db::name('order_material')->where('order_id',$orderId)->delete();

        if($orderRes){
            $this->success('删除成功');
        }
        $this->error('删除失败,请重试');
    }
    
    /**
     * 添加手工单
     */
    public function addHandMade()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $orderId = input('order_id/d');
            $order = new orderLogic();
            $res = $order->addHandsOrder($data, $orderId);
            if ($res) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }
        
        $orderId = input('orderid');
        $this->assign('orderid', $orderId);
        //工艺一级栏目数据
        $one = Db::name('series')->where('parent_id', 0)->select();
        $this->assign('one', $one);
        $res = Db::name('order')->field('a.*,b.dealer_rebate')->alias('a')->join('dealer b','a.dealer_id=b.id','left')
            ->where('a.id',$orderId)->find();
        $this->assign('res',$res);
        return $this->fetch();
    }
    
    /**
     * 编辑手工单
     */
    public function editHandMade()
    {
        if ($this->request->isPost()) {
            $opid = input('op_id/d');
            $data = input('post.');
            $order = new orderLogic();
            $res = $order->editHandsOrder($data, $data['order_id']);
            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $opid = input('id/d');
        $product = Db::name('order_price')->alias('a')
                        ->join('order_calculation b', 'a.op_id=b.op_id')
                        ->join('order_result c','a.op_id=c.op_id','left')
                        ->where('a.op_id', $opid)
                        ->find();
        $calculate = isset($product['all_data'])?unserialize($product['all_data']):[];  

        $order = new orderLogic();
        $array = $order->getProductInfo($product); //多级栏目数组及系列id    
        $flowerHeight = Db::name('bom_flower')->where('id',$product['flower_id'])->find();        

        $seriesFilter = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five']; //工艺系列html id
        $colorFilter = [1 => 'color', 2 => 'color_two', 3 => 'color_three']; //铝型花件颜色html lay-filter
        $this->assign('seriesFilter', $seriesFilter);
        $this->assign('colorFilter', $colorFilter);
        $this->assign('flowerHeight',$flowerHeight);
        $this->assign('opid', $opid);
        $this->assign('array', $array);
        $this->assign('flower', $array['flower']);
        $this->assign('info', $product);
        $this->assign('is_alum_diy', array_keys($array['alum_color_array'])[0]);  //铝型颜色是否自定义
        $flowerDiy = is_array($array['flower_color_array']) ? array_keys($array['flower_color_array'])[0] : '';
        $this->assign('is_flower_diy', $flowerDiy); //花件颜色是否自定义
        $this->assign('product',$product);
        $this->assign('calculate',$calculate);
        return $this->fetch();
    }
    
    /**
     * 组合单页面
     */
    public function addGroup()
    {
        $ogId = input('id/d');
        $orderId = input('order_id/d');
        
        $price = Db::name('order_price')->where(['og_id'=>$ogId,'order_type'=>2])->select();
        $calculate = Db::name('order_price')->where(['og_id'=>$ogId,'order_type'=>3])->select();
        
        $this->assign('orderid', $orderId);
        $this->assign('price',$price);
        $this->assign('calculate',$calculate);
        $this->assign('og_id',$ogId);        
        return $this->fetch();
    }
    
    /**
     * 组合单报价信息表格
     */
    public function groupPrice()
    {
        $ogId = input('og_id/d');
        $orderId = input('order_id/d');
        
        $price = Db::name('order_price')->where(['og_id'=>$ogId,'order_type'=>2])->select();
        $number = Db::name('order_price')->where("order_id",$orderId)->where("order_type=0")->count();
        //小于当前组合单id的产品数
        $other = Db::name('order_group')->alias('a')
            ->join('order_price b','a.og_id=b.og_id')
            ->where(['a.order_id'=>$orderId])->where("a.og_id<$ogId")->where('order_type!=3')->count();
        $number = $number+$other;
        //编号跟产品编号 连续
        foreach ($price as $k => $v) {
            $number += 1;
            $price[$k]['numbers'] = $number;
            $price[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
            $price[$k]['material'] = $v['material'].'/'.$price[$k]['yarn_color'];
        }
       
        $this->success('',$price);
        
    }
    
    /**
     * 组合单算料信息表格
     */
    public function groupCalculate()
    {
        $ogId = input('og_id/d');
        $orderId = input('order_id/d');
        
        $calculate = Db::name('order_price')->alias('a')
            ->field('a.*,b.structure')
            ->join('order_calculation b','a.op_id=b.op_id','left')
            ->where(['og_id'=>$ogId,'order_type'=>3])->select();
            
        foreach ($calculate as $k => $v) {
            $calculate[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
            $calculate[$k]['material'] = $v['material'].'/'.$calculate[$k]['yarn_color'];
        }
        
        $this->success('',$calculate);
    }
    
    /**
     * 添加组合单产品
     */
    public function addGroupProduct()
    {
         if ($this->request->isPost()) {
            $data = input('post.');
            $orderId = input('order_id/d');
            $orderType = input('order_type');

            $order = new orderLogic();
            $res = $order->addGroup($data, $orderId,$orderType);//添加产品
            if ($res) {
                $this->success('添加成功', '',url('addGroup',array('id'=>$res,'order_id'=>$orderId)));
            }
            $this->error('添加失败,请重试');
            return;
        }

        $orderId = input('order_id');
        $orderType = input('order_type');
        $ogid = input('og_id');
        //如果是添加 算料信息,自动带出报价信息的 第一个产品数据
        if($orderType == 3){
            $orderPrice = Db::name('order_price')->where('og_id',$ogid)->find();
            if($orderPrice){
                $opid = $orderPrice['op_id'];
                $product = Db::name('order_price')->alias('a')->field('a.*,b.*,c.min,c.max,c.name as tips_name,d.add_type')
                    ->join('order_calculation b', 'a.op_id=b.op_id')
                    ->join('order_tips c','a.op_id=c.op_id','left')
                    ->join('order d','a.order_id=d.id')
                    ->where('a.op_id', $opid)
                    ->find();

                $order = new orderLogic();
                $array = $order->getProductInfo($product); //多级栏目数组及系列id
                $flowerHeight = Db::name('bom_flower')->where('id',$product['flower_id'])->find();

                $seriesFilter = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five']; //工艺系列html id
                $colorFilter = [1 => 'color', 2 => 'color_two', 3 => 'color_three']; //铝型花件颜色html lay-filter
                $this->assign('seriesFilter', $seriesFilter);
                $this->assign('colorFilter', $colorFilter);
                $this->assign('flowerHeight',$flowerHeight);
                $this->assign('opid', $opid);
                $this->assign('array', $array);
                $this->assign('flower', $array['flower']);
                $this->assign('info', $product);
                $this->assign('is_alum_diy', array_keys($array['alum_color_array'])[0]);  //铝型颜色是否自定义
                $flowerDiy = is_array($array['flower_color_array']) ? array_keys($array['flower_color_array'])[0] : '';
                $this->assign('is_flower_diy', $flowerDiy); //花件颜色是否自定义
                $this->assign('product',$product);
            }

        }

        $this->assign('orderid', $orderId);
        $res = Db::name('order')->field('a.*,b.dealer_rebate')->alias('a')->join('dealer b', 'a.dealer_id=b.id', 'left')
            ->where('a.id', $orderId)->find();
        $this->assign('res', $res);
        //工艺一级栏目数据
        $one = Db::name('series')->where('parent_id', 0)->select();
        $this->assign('one', $one);
        $this->assign('order_type', input('order_type'));
        $this->assign('og_id', input('og_id/d'));
        $template = $orderType==3&&$orderPrice?'add_group_cal_product':'add_group_product';
        return $this->fetch($template);
    }
    
    /**
     * 编辑组合单
     */
    public function editGroupProduct()
    {

        if ($this->request->isPost()) {
            $opid = input('op_id/d');
            $data = input('post.');
            $order = new orderLogic();
            $res = $order->editGroup($data, $data['order_id']);
            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $opid = input('id/d');
        $product = Db::name('order_price')->alias('a')->field('a.*,b.*,c.min,c.max,c.name as tips_name')
                        ->join('order_calculation b', 'a.op_id=b.op_id')
                        ->join('order_tips c','a.op_id=c.op_id','left')
                        ->where('a.op_id', $opid)
                        ->find();
                
        $order = new orderLogic();
        $array = $order->getProductInfo($product); //多级栏目数组及系列id    
        $flowerHeight = Db::name('bom_flower')->where('id',$product['flower_id'])->find();        

        $seriesFilter = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five']; //工艺系列html id
        $colorFilter = [1 => 'color', 2 => 'color_two', 3 => 'color_three']; //铝型花件颜色html lay-filter
        $this->assign('seriesFilter', $seriesFilter);
        $this->assign('colorFilter', $colorFilter);
        $this->assign('flowerHeight',$flowerHeight);
        $this->assign('opid', $opid);
        $this->assign('array', $array);
        $this->assign('flower', $array['flower']);
        $this->assign('info', $product);
        $this->assign('is_alum_diy', array_keys($array['alum_color_array'])[0]);  //铝型颜色是否自定义
        $flowerDiy = is_array($array['flower_color_array']) ? array_keys($array['flower_color_array'])[0] : '';
        $this->assign('is_flower_diy', $flowerDiy); //花件颜色是否自定义
        $this->assign('product',$product);
        return $this->fetch();
    }
    
    /**
     * 添加原材料
     */
    public function addMaterial()
    {
        if ($this->request->isPost()) {
            $data = input('post.');            
            $orderId = input('post.order_id');
            
            //整理数据
            $array = [];
            foreach ($data['name'] as $k => $v) {
                $array[] = ['name' => $v, 'order_id' => $orderId, 'type' => $data['type'][$k], 'color' => $data['color'][$k], 'unit' => '㎡',
                    'width' => $data['width'][$k], 'height' => $data['height'][$k], 'count' => $data['count'][$k], 'area' => $data['area'][$k],
                    'product_area' => $data['product_area'][$k],
                    'price' => $data['price'][$k], 'rebate_price' => $data['rebate_price'][$k], 'all_price' => $data['all_price'][$k],
                    'addtime' => time(),'rebate' => $data['rebate'][$k]
                ];
            }
            
            $res = Db::name('order_material')->insertAll($array);
            //更新订单总价
            $product = Db::name('order_price')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                    ->where('order_id',$orderId)
                    ->where('order_type !=3 ')
                    ->find();            
            $material = Db::name('order_material')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                    ->where('order_id',$orderId)->find();
            $total = $product['price']+$material['price'];
            $tarea = $product['area']+$material['area'];
            $tproductArea = $product['parea']+$material['parea'];
            $tcount = $product['count']+$material['count'];
            Db::name('order')->where('id', $orderId)->update(['total_price' => $total,'area'=>$tarea,'product_area'=>$tproductArea,'count'=>$tcount]);
            
            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $orderId = input('orderid');
        $this->assign('order_id', input('orderid'));
        $res = Db::name('order')->field('a.*,b.dealer_rebate')->alias('a')->join('dealer b','a.dealer_id=b.id','left')
            ->where('a.id',$orderId)->find();
        $this->assign('res',$res);
        return $this->fetch();
    }

    /**
     * 编辑原材料
     */
    public function editMaterial()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            unset($data['om_id']);
            $omId = input('om_id');
            $orderId = input('post.order_id');
            $res = Db::name('order_material')->where('om_id', $omId)->update($data);
            
            $product = Db::name('order_price')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                    ->where('order_id',$orderId)
                    ->where('order_type !=3 ')
                    ->find();            
            $material = Db::name('order_material')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                    ->where('order_id',$orderId)->find();
            $total = $product['price']+$material['price'];
            $tarea = $product['area']+$material['area'];
            $tproductArea = $product['parea']+$material['parea'];
            $tcount = $product['count']+$material['count'];
            Db::name('order')->where('id', $orderId)->update(['total_price' => $total,'area'=>$tarea,'product_area'=>$tproductArea,'count'=>$tcount]);
            
            
            if ($res !== false) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $omId = input('id/d');
        $info = Db::name('order_material')->where('om_id', $omId)->find();
        $this->assign('info', $info);
        $this->assign('om_id', $omId);
        return $this->fetch();
    }

 
    /**
     * 异步查询物料池中所有数据
     */
    public function findBom()
    {
        $name = input('name');
        $list = Db::field('name,unit,price')
                ->table('erp_bom_aluminum')
                ->union("select name,unit,price from erp_bom_flower where name like '%" . $name . "%'")
                ->union("select name,unit,price from erp_bom_five where name like '%" . $name . "%'")
                ->union("select name,unit,price from erp_bom_hands where name like '%" . $name . "%'")
                ->union("select name,unit,price from erp_bom_yarn where name like '%" . $name . "%'")
                ->where("name like '%" . $name . "%'")
                ->limit(5)
                ->select();

        $this->success('', $list);
    }

    /**
     * 异步查询物料颜色
     */
    public function findColor()
    {
        $name = input('name');
        $list = Db::name('bom_color')->where("name like '%" . $name . "%'")->limit(5)->select();

        $this->success('', $list);
    }

    /**
     * 订单审核--线路1
     */
    public function sendCar()
    {
        $id = input('id/d');
        
        $calculate = Db::name('order_price')->alias('a')->field('b.*')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where(['a.order_id'=>$id,'order_type'=>0])
                ->select();
        
        $write = true;
//        if(is_array($calculate) && count($calculate)<=0){
//            $this->error('请先添加产品');
//        }
        
        $res = Db::name('order')->where('id', $id)->update(['status' => 2]);
        if ($res) {
            $this->success('发送成功');
        }
        $this->error('发送失败,请重试');
    }
    
    /**
     * 订单审核--线路2
     */
    public function sendCar2()
    {
        $id = input('id/d');
        $status = input('status/d');
        
        $calculate = Db::name('order_price')->alias('a')->field('b.*')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where(['a.order_id'=>$id,'order_type'=>0])
                ->select();
        
        $write = true;
//        if(is_array($calculate) && count($calculate)<=0){
//            $this->error('请先添加产品');
//        }
        
        $res = Db::name('order')->where('id', $id)->update(['status2'=> $status+1]);
        if ($res) {
            $this->success('发送成功');
        }
        $this->error('发送失败,请重试');
    }
    
    public function sendCar3()
    {
        $id = input('id/d');
        
        $res = Db::name('order')->where('id', $id)->update(['status'=> 4, 'finance_status' => 1, 'car_time' => time()]);
        if ($res) {
            $this->success('发送成功');
        }
        $this->error('发送失败,请重试');
    }	

    /**
     * 花件
     */
    public function flower()
    {
        $seriesId = input('id/d');
        $keyword = input('search');
        
        if($seriesId == 0){
            return;
        }
        $where = "series_id != 0";
        if ($seriesId>0){
            $where .= " and series_id=$seriesId";
        }

        if($keyword != ''){
            $where .= " and a.name like '%$keyword%'";
        }
        
        $flower = Db::name('series_flower')->alias('a')->field('a.*,b.pic,b.id,b.max_height,b.min_height')
                ->join('bom_flower b', 'a.flower_id=b.id')
                ->where($where)
                ->orderRaw("convert(a.name using gbk)")
                ->select();
        
        $this->assign('flower', $flower);
        $this->assign('series_id', $seriesId);
        $this->assign('keyword',$keyword);
        return $this->fetch();
    }

    /**
     * 花件筛选，是否可切等
     */
    public function findCut()
    {
        $seriesId = input('series_id');
        $iscut = input('iscut');
        if ($iscut == '') {
            $where = "1=1 and a.series_id=$seriesId";
        } else {
            $where = ['a.series_id' => $seriesId, 'b.is_cut' => $iscut];
        }
        $res = Db::name('series_flower')->alias('a')
                ->field('a.*,b.pic,b.id,b.max_height,b.min_height')
                ->join("bom_flower b", 'a.flower_id=b.id')
                ->where($where)
                ->select();        
        $this->success('', $res);
    }

    /**
     * 结构
     */
    public function structure()
    {
        $seriesId = input('id/d');
        $width = input('width');  //用户填的总宽
        $height = input('height/d'); //用户填的总高
        $flowerMax = input('flower_max'); //花件最大高
        $flowerMin = input('flower_min'); //花件最小高
        $spacing = input('spacing'); //间距值
        $flowerId = input('flower_id');//花件id
        $bottom_spacing = input('bottom_spacing'); //竖间距值
//        $bottom_spacing_count = input('bottom_spacing_count'); //竖间距数
//        $bottom_frame_count = input('bottom_frame_count'); //竖花件外框数
        $flowerMax_winth = input('flower_max_width'); //花件最大宽
        $flowerMin_winth = input('flower_min_width'); //花件最小宽
        $hold_hands_winth = input('hold_hands_width')!='undefined'?input('hold_hands_width'):0; //把手宽
//        $hold_hands_count = input('hold_hands_count'); //把手数量

        if($width == '' || $height == ''){
            return $this->fetch();
        }

        $where = "a.series_id=$seriesId";
        if ($width) {
            $where .= " and $width between min_width and max_width";
        }
        if ($height) {
            $where .= " and $height between min_height and max_height";
        }
        
        //获取当前系列符合用户 所填宽高的结构
        $structure = Db::name('series_structure')->alias('a')->field('a.*,b.*')
                ->join('structure b', 'a.structure_id=b.id')
                ->where($where)
                ->select();

        //系列的边框厚--即物料池 铝型材的最小面
        $alumin = Db::name('series_bom')->alias('a')->field('b.*')
                        ->join('bom_aluminum b','a.two_level=b.id')
                        ->where(['a.series_id'=>$seriesId,'a.type'=>2])
                        ->find();
        $frame = isset($alumin['small'])?$alumin['small']:0;
        
        //如果花件有绑定结构，则直接显示花件绑定的结构
        $flower = Db::name('bom_flower')->where('id',$flowerId)->find();              
        if(isset($flower['structure_id']) && $flower['structure_id'] != null){
            $structureId = explode(',', $flower['structure_id']);                                                 
            foreach($structure as $k => $v){
                if(!in_array($v['structure_id'], $structureId)){
                    unset($structure[$k]);
                } 
            }
        }else{
        
            //否则根据花件高等，筛选结构
            foreach($structure as $k => $v){
                $h_min = $width-$bottom_spacing*$v['bottom_spacing_count']-$frame*$v['bottom_frame_count']-$hold_hands_winth*$v['hold_hands_count'];
                
                $min = $height-$flowerMax-$spacing*$v['spacing_count']-$frame*$v['frame_count'];
                $max = $height-$flowerMin-$spacing*$v['spacing_count']-$frame*$v['frame_count'];
                $max_nosolid = $height-$spacing*$v['spacing_count']-$frame*$v['frame_count'];
                if(input('flower_max/d')>0){
                    if($v['fixed']=='上固定' || $v['fixed']=='下固定'){
                        if($min<130){
                                unset($structure[$k]); 
                        }
                        if($min>1000){
                                unset($structure[$k]); 
                        }
                    }
                    if($v['fixed']=='上下固定'){
                        if($min<260){
                                unset($structure[$k]);
                        }
                        if($min>2000){
                                unset($structure[$k]);
                        }
                    }
                    if($v['fixed']=='不带固定'){
                        if($max_nosolid<$flowerMin){
                                unset($structure[$k]);
                        }
                        if($max_nosolid>$flowerMax){
                                unset($structure[$k]);
                        }
                    }
                    if($h_min >= $flowerMin_winth && $h_min <=$flowerMax_winth){
                        unset($structure[$k]);
                    }
                }
            }
        }
        
        $this->assign('structure', $structure);
        $this->assign('series_id', $seriesId);
        $this->assign('width', $width?$width:0);
        $this->assign('height', $height?$height:0);
        $this->assign('frame',$frame);
        return $this->fetch();
    }

    /**
     * 订单结构异步下拉
     */
    public function findStructure()
    {
        $seriesId = input('id');
        $windowType = input('window_type');
        $hands = input('hands');
        $fixed = input('fixed');
        $width = input('width'); //用户填的总宽
        $height = input('height'); //用户填的总高
        $escape = input('escape');//逃生窗

        $sql = "a.series_id=$seriesId";
        if ($width) {
            $sql .= " and $width between min_width and max_width";
        }
        if ($height) {
            $sql .= " and $height between min_height and max_height";
        }
        if ($windowType != '') {
            $sql .= " and b.window_type='$windowType'";
        }
        if ($hands != '') {
            $sql .= " and b.hands='$hands'";
        }
        if ($fixed != '') {
            $sql .= " and b.fixed='$fixed'";
        }
        if($escape != ''){
            $sql .= " and b.escape='$escape'";
        }
        $list = Db::name('series_structure')->alias('a')->field('a.*,b.*')
                ->join('structure b', 'a.structure_id=b.id')
                ->where($sql)
                ->select();

        $this->success('', $list);
    }

    /**
     * 工艺下拉列表联动
     */
    public function technology()
    {
        $pid = input('pid');
        $list = Db::name('series')->where('parent_id', $pid)->order('sort')->select();
        $this->success('', $list);
    }

    /**
     * 查询当前工艺是否是带花的
     */
    public function findFlower()
    {
        $seriesId = input('series_id/d');
        $res = Db::name('series_flower')->where('series_id',$seriesId)->select();
        if(is_array($res) && count($res)>0){
            $this->success('');
        }
        $this->error('');
    }
    
    /**
     * 纱网一级
     */
    public function yarn()
    {
        $seriesId = input('series_id/d');
        $list = Db::name('series_yarn')->alias('a')->field('a.*,b.name')
                ->join('bom_yarn b', 'a.yarn_id=b.id')
                ->where("series_id", $seriesId)
                ->select();

        $this->success('', $list);
    }

    /**
     * 纱网二级
     */
    public function yarnTwo()
    {
        $yarnId = input('id/d');
        $seriesId = input('series_id');
        $list = Db::name('series_yarn')->alias('a')->field('a.*,b.thickness')
                ->join('bom_yarn b', 'a.yarn_id=b.id')
                ->where(["yarn_id"=>$yarnId,'series_id'=>$seriesId])
                ->find();

        $this->success('', $list);
    }

    /**
     * 五金列表
     */
    public function five()
    {
        $seriesId = input('series_id/d');
        $list = Db::name('series_five')->alias('a')->field('a.*,b.name')
                ->join('bom_five b', 'a.five_id=b.id')
                ->where("series_id", $seriesId)
                ->select();

        $this->success('', $list);
    }
    
    /**
     * 把手位
     */
    public function hands()
    {
        $seriesId = input('series_id/d');
        $list = Db::name('series_hands')->alias('a')->field('a.*,b.name,b.width')
                ->join('bom_hands b', 'a.hands_id=b.id')
                ->where("series_id", $seriesId)
                ->order('b.sort asc')
                ->select();

        $this->success('', $list);
    }

    /**
     * 铝材颜色和花件颜色一级
     */
    public function color()
    {
        $type = input('type'); //边框还是花件
        $seriesId = input('series_id'); //系列Id
        $relation = Db::name('series_color')->where(["series_id"=>$seriesId,'type'=>$type])->column('all_relation');
        $ids = [];
        foreach ($relation as $k => $v) {
            $id = explode(',', $v);
            $ids[] = $id[0];
        }
        $list = Db::name('bom_color')->whereIn('id', $ids)->select();
        $this->success('', $list);
    }

    /**
     * 铝材花件颜色二级
     */
    public function colorTwo()
    {
        $colorId = input('id'); //系列颜色id
        $type = input('type');
        $seriesId = input('series_id');
        
        //如果是用户自填的颜色
        $list = Db::name('series_color')->alias('a')
                    ->join('bom_color b','a.color_id=b.id')
                    ->where(['b.id'=>$colorId,'is_self'=>1])
                    ->find();
        if($list){
            $this->success('',$list);
        }
        
        //不是用户自填的颜色
        $list = Db::name('series_color')->where(['level' => ['>', 1], 'type' => $type, 'series_id' => $seriesId])->column('all_relation');

        $ids = []; //所拥有的二级id
        //处理数据
        foreach ($list as $k => $v) {
            //判断id是否与一级id相同
            $allId = explode(',', $v);
            $id = $allId[0];
            if ($id == $colorId) {
                $ids[] = $allId[1];
            }
        }
        $result = Db::name('bom_color')->whereIn('id', $ids)->select();
        $this->success('', $result);
    }

    /**
     * 铝材花件颜色3级
     */
    public function colorThree()
    {
        $colorId = input('id'); //系列颜色id
        $type = input('type');
        $seriesId = input('series_id');
        $list = Db::name('series_color')->where(['level' => ['>', 2], 'type' => $type, 'series_id' => $seriesId])->column('all_relation');

        $ids = []; //所拥有的3级id
        //处理数据
        foreach ($list as $k => $v) {
            //判断id是否与2级id相同
            $allId = explode(',', $v);
            $id = $allId[1];
            if ($id == $colorId) {
                $ids[] = $allId[2];
            }
        }
        $result = Db::name('bom_color')->whereIn('id', $ids)->select();        
        $this->success('', $result);
    }

    /**
     * 打印报价单
     */
    public function printing()
    {
        $orderId = input('orderid/d');
        $order = Db::name('order')->where('id', $orderId)->find();
        //订单产品
        $product = Db::name('order_price')->alias('a')->field('a.*,c.big,c.small')
                ->join('series_bom b','a.series_id=b.series_id')
                ->join('bom_aluminum c','b.two_level=c.id','left')
                ->where(['a.order_id'=>$orderId,'b.type'=>2])
                ->order('a.op_id asc')
                ->select();      
        $series = Db::name('series')->select();
        foreach($product as $k => $v){
            $temp = $this->getParentName($series,$v['series_id']);     //获取当前系列的所有父级数组       
            $material = $product[$k]['material'];
            foreach ($temp as $k2 => $v2) {
                //如果系列属性为，报价单中不显示，则替换成空
                if($v2['price_show'] == 1){
                    $material = str_replace($v2['name'], '', $material);                    
                }
            }
            $material = str_replace(['//','///','///'], '/', $material);                  
            $product[$k]['material'] = $material;
        }
        //订单原材料
        $material = Db::name('order_material')->where('order_id', $orderId)->select();

        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('order', $order);
        return $this->fetch();
    }

    /**
     * 递归获取系列所有父id的数组
     * @param array $series 系列数组,int $seriesId 系列id
     * @return array 父级数组（包含自身）
     */
    public function getParentName($series,$seriesId)
    {
        $arr = [];
        foreach($series as $v) {
            if($v['id'] == $seriesId){
                $arr[] = $v;
                $arr = array_merge(self::getParentName($series,$v['parent_id']),$arr);
            }
        }
        return $arr;
    }
    
    /**
     * 删除产品
     */
    public function delProduct()
    {
        $id = input('id/d');
        $orderId = input('order_id/d');
        
        $order = Db::name('order')->where('id',$orderId)->find();
        $product =  Db::name('order_price')->where('op_id', $id)->find();
        if($order && $product){
            $res = Db::name('order_price')->where('op_id', $id)->delete();

            //更新订单总价
            $logic = new orderLogic();
            $orderUpdate = $logic->orderPrice($orderId);
            Db::name('order')->where('id',$orderId)->update($orderUpdate);
            if ($res) {
                $this->success('删除成功');
            }
        }
        
        $this->error('删除失败，请重试');
    }
    
    /**
     * 删除组合单产品
     */
    public function delGroupProduct()
    {
        $id = input('id/d');
        $orderId = input('order_id/d');
        
        $order = Db::name('order')->where('id',$orderId)->find();
        $product =  Db::name('order_price')->where('op_id', $id)->find();
        if($order && $product){
            $res = Db::name('order_price')->where('op_id', $id)->delete();
            //组合单 算料信息只删除产品就行
            if($product['order_type'] != 3){
                //更新所属组合单价格等数据
                $gsql = "update erp_order_group set width=width-{$product['all_width']},height=height-{$product['all_height']},total_price=total_price-{$product['all_price']},"
                    . "area=area-{$product['area']},product_area=product_area-{$product['product_area']}";
                if($product['order_type'] == 2){
                    $gsql .= ",price_count=price_count-{$product['count']}";
                }elseif($product['order_type'] == 2){
                    $gsql .= ",price_count=calculate_count-{$product['count']}";
                }
                $gsql .= " where og_id={$product['og_id']}";
                $gprice = Db::execute($gsql);

                //更新订单总价
                $logic = new orderLogic();
                $orderUpdate = $logic->orderPrice($orderId);
                Db::name('order')->where('id',$orderId)->update($orderUpdate);
            }


        
            if ($res) {
                $this->success('删除成功');
            }
        }
        
        $this->error('删除失败，请重试');
    }

    /**
     * 删除原材料
     */
    public function delMaterial()
    {
        $id = input('id/d');
        $price = input('price');
        $orderId = input('order_id');
        
        $order = Db::name('order')->where('id',$orderId)->find();
        $product =  Db::name('order_material')->where('om_id', $id)->find();
        if($order && $product){
            $res = Db::name('order_material')->where('om_id', $id)->delete();
            //更新订单总价
            $logic = new orderLogic();
            $orderUpdate = $logic->orderPrice($orderId);
            Db::name('order')->where('id',$orderId)->update($orderUpdate);
            if ($res && $order !== false) {
                $this->success('删除成功');
            }
        }
        
        $this->error('删除失败，请重试');
    }
    
    /**
     * 删除组合单
     */
    public function delGroup()
    {
        $id = input('id/d');
        $price = input('price');
        $orderId = input('order_id');

        $order = Db::name('order')->where('id',$orderId)->find();
        $product =  Db::name('order_group')->where('og_id', $id)->find();
        if($order && $product){
            $res = Db::name('order_group')->where('og_id', $id)->delete();
            Db::name('order_price')->where('og_id', $id)->delete();

            //更新订单总价
            $logic = new orderLogic();
            $orderUpdate = $logic->orderPrice($orderId);
            Db::name('order')->where('id',$orderId)->update($orderUpdate);
            if ($res && $order !== false) {
                $this->success('删除成功');
            }
        }
        
        $this->error('删除失败，请重试');
    }

    /**
     * 已下车间订单
     */
    public function car()
    {
        $list = Db::name('order')->where("status>=2 or status2>=2")->order('id desc')->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    /**
     * 查看渲染图
     */
    public function pic()
    {
        
        $flower = input('flower'); //花件图片
        $structureId = input('structure_id');
        $alumColor = input('alum_color/a');
        $flowerColor = input('flower_color/a');
        
        $alumId = 0;  //铝型颜色id
        $flowerColorId = 0; //花件颜色id
        foreach ($alumColor as $key => $value) {
            if($value!=''){
                $alumId = $value;
            }
        }
        foreach ($flowerColor as $key => $value) {
            if($value!=''){
                $flowerColorId = $value;
            }
        }
        
        $structure = Db::name('structure')->where('id',$structureId)->find();
        if(!$structure){
            return;
        }
        $alum = Db::name('bom_color')->where('id',$alumId)->find();
        $flowerColord = Db::name('bom_color')->where('id',$flowerColorId)->find();        
        
        $this->assign('flowerPic', isset($flowerColord['pic'])?$flowerColord['pic']:'');
        $this->assign('alumPic', isset($alum['pic'])?$alum['pic']:'');
        $this->assign('rulerPic', isset($structure['structure_pic'])?$structure['structure_pic']:'');
        $this->assign('flower',$flower);
        $this->assign('structure',$structure);
        return $this->fetch();
        
    }
    
    /**
     * 组合单复制
     */
    public function gcopy()
    {
        $id = input('id/d');
        $copy =new \app\admin\logic\copy();
        $res = $copy->groupOrder($id);
        if($res){
            $this->success('复制成功');
        }
        $this->error('复制失败,请重试');
    }
    
    /**
     * 原材料复制
     */
    public function mcopy()
    {
        $id = input('id/d');
        $orderId = input('order_id');
        $sql = "insert into erp_order_material(order_id,name,type,color,unit,price,addtime) select order_id,name,type,color,unit,price,unix_timestamp(now()) from erp_order_material where om_id=$id";
        $res = Db::name('order_material')->execute($sql);
        //更新订单总价
        $product = Db::name('order_price')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                ->where('order_id',$orderId)
                ->where('order_type != 3')
                ->find();            
        $material = Db::name('order_material')->field('sum(count) as count,sum(all_price) as price,sum(area) as area,sum(product_area) as parea')
                ->where('order_id',$orderId)->find();
        $total = $product['price']+$material['price'];
        $tarea = $product['area']+$material['area'];
        $tproductArea = $product['parea']+$material['parea'];
        $tcount = $product['count']+$material['count'];
        Db::name('order')->where('id', $orderId)->update(['total_price' => $total,'area'=>$tarea,'product_area'=>$tproductArea,'count'=>$tcount]);
        if($res){
            $this->success('复制成功');
        }
        $this->error('复制失败,请重试');
    }
    
    /**
     * 产品复制
     */
    public function pcopy()
    {
        $id = input('id/d');
        $orderId = input('order_id/d');
        $this->request->post();
        $priceSql = "insert into erp_order_price(order_id,series_id,name,material,flower_type,flower_id,"
            ."flower_pic,color_name,technology,flower,alum_color,flower_color,alum_color_id,flower_color_id,"
            ."alum_name,flower_name,alum_name_price,flower_name_price,yarn_color,yarn_price,yarn_thickness,five_id,five_count,five_price,window_type_a,escape_type_a,`window`,left_fly,right_fly,top_fly,bottom_fly,arc_height,arc_length_count,other_add_price,"
            ."note,price,rebate,rebate_price,"
            ."order_type,og_id,addtime) select order_id,series_id,name,material,flower_type,flower_id,"
            ."flower_pic,color_name,technology,flower,alum_color,flower_color,alum_color_id,flower_color_id,"
            ."alum_name,flower_name,alum_name_price,flower_name_price,yarn_color,yarn_price,yarn_thickness,five_id,five_count,five_price,window_type_a,escape_type_a,`window`,left_fly,right_fly,top_fly,bottom_fly,arc_height,arc_length_count,other_add_price,"
            ."note,price,rebate,rebate_price,"
            ."order_type,og_id,unix_timestamp(now()) from erp_order_price where op_id=$id;";
        $pres = Db::name('order_price')->execute($priceSql);
        $opId = Db::name('order_price')->getLastInsID();
        $calculateSql = "insert into erp_order_calculation(op_id,spacing,structure_id,structure,fixed_height,hands,lock_position) select last_insert_id(),spacing,structure_id,structure,fixed_height,hands,lock_position from erp_order_calculation where op_id=$id;";
        $cres = Db::name('order_calculation')->execute($calculateSql);
        
        $tips = Db::name('order_tips')->execute("insert into erp_order_tips(op_id,min,max,name) select {$opId},min,max,name from erp_order_tips where op_id=$id");
        
        //更新总价
        $order = new orderLogic();
        $totalPrice = $order->orderPrice($orderId);        
        Db::name('order')->where('id',$orderId)->update([
            'total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'],'product_area'=>$totalPrice['product_area'] ,
            'count' => $totalPrice['count']
                ]);
        if($cres && $pres){
            $this->success('复制成功');
        }
        $this->error('复制失败,请重试');
    }

    public function testPrinting()
    {
        return $this->fetch();
    }
    
     public function onePrinting()
    {
         $number = input('number');
         $this->assign('number',$number);
        return $this->fetch();
    }


    /**
     * 编辑定制类产品
     */
    public function diyedit()
    {
        if(request()->isPost()){
            $opid = input('op_id');
            $data = input('post.');
            $name = input('name/a');
            $orderid = input('order_id');
            $update = [];
            foreach ($name as $k => $v) {
                $update = ['position'=>$data['position'][$k],'material'=>$data['name'][$k],'color_name'=>$data['color_name'][$k],'all_width'=>$data['width'][$k],'all_height'=>$data['height'][$k],
                    'count'=>$data['count'][$k],'product_area'=>$data['product_area'][$k],'area'=>$data['area'][$k],'price'=>$data['price'][$k],
                    'all_price'=>$data['all_price'][$k],'diy_pic'=>$data['diy_pic'][$k],'order_type'=>4,'rebate_price'=>$data['price'][$k],
                ];                
            }
            $res = Db::name('order_price')->where('op_id',$opid)->update($update);
            //更新总价格
            $order = new orderLogic();
            $priceData = $order->orderPrice($orderid);
            Db::name('order')->where('id',$orderid)->update([
                'total_price'=>$priceData['all_price'],'count'=>$priceData['count'],'area'=>$priceData['area'],'product_area'=>$priceData['product_area']
            ]);
            if($res !== false){
                $this->success('保存成功');
            }
            $this->error('保存失败');
            return;
        }
        $id = input('id');
        $res = Db::name('order_price')->where('op_id',$id)->find();
        $this->assign('res',$res);
        return $this->fetch();
    }

    /**
     * 添加定制类产品
     */
    public function addDiy()
    {

        if(request()->isPost()){
            $orderid = input('order_id');
            $data = input('post.');
            $name = input('name/a');

            $insert = [];
            foreach ($name as $k => $v) {
                $insert[] = ['position'=>$data['position'][$k],'material'=>$data['name'][$k],'color_name'=>$data['color_name'][$k],'all_width'=>$data['width'][$k],'all_height'=>$data['height'][$k],
                    'count'=>$data['count'][$k],'product_area'=>$data['product_area'][$k],'area'=>$data['area'][$k],'price'=>$data['price'][$k],
                    'all_price'=>$data['all_price'][$k],'diy_pic'=>$data['diy_pic'][$k],'order_type'=>4,'order_id'=>$orderid,'rebate'=>1,'rebate_price'=>$data['price'][$k]
                ];
            }

            $res = Db::name('order_price')->insertAll($insert);
            //更新总价格
            $order = new orderLogic();
            $priceData = $order->orderPrice($orderid);
            Db::name('order')->where('id',$orderid)->update([
                'total_price'=>$priceData['all_price'],'count'=>$priceData['count'],'area'=>$priceData['area'],'product_area'=>$priceData['product_area']
            ]);
            if($res){
                $this->success('保存成功');
            }
            $this->error('保存失败');
            return;
        }
        $orderid = input('orderid');
        $this->assign('order_id',$orderid);
        return $this->fetch();
    }

}
