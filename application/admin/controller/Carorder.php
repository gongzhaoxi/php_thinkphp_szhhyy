<?php

namespace app\admin\controller;

use app\admin\logic\AbutmentErp;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;
use app\admin\logic\orderLogic;
use app\admin\logic\calculate;

/**
 * 车间订单控制器
 */
class Carorder extends Base
{
    protected function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 未处理订单
     */
    public function noHandle()
    {
        $keyword = input('keyword');
		$start = input('start_time');
		$end = input('end_time');
        $page = input('get.page');
        $getstr = "keyword=$keyword&page=$page";		
		
        $where = "1=1";
        if($keyword){
            $where .= " and (number like '%$keyword%' or dealer like '%$keyword%')";
        }
		if($start){
		    $startTime = strtotime($start);
		    $where .= " and car_time>='$startTime'";
		}
		if($end){
		    $endTime = strtotime($end)+24*3600;
		    $where .= " and car_time<='$endTime'";
		}
		//统计数据
		$allOrder = Db::name('order')->where("status=3 or status2=2 or status2=5")->where($where)->count();
		$area =  Db::name('order')->where("status=3 or status2=2 or status2=5")->where($where)->sum('area');
		$list = Db::name('order')->where("status=3 or status2=2 or status2=5")->where($where)->order('id desc')->paginate();
		$list->appends(input('get.'));
		$this->assign('page', $list->render());
		$this->assign('list', $list);
		$this->assign('getstr',$getstr);
		$this->assign('start_search', $start);
		$this->assign('end_search', $end);
		$this->assign('keyword', $keyword);
		$this->assign('search',input('get.'));
		$this->assign('statistics',[
		    'area'=>round($area,2),'count'=>$allOrder,'into' => round($into,2)
		]);
		return $this->fetch();		
    }
	/**
	 * 获取报价单列表的搜索   车间未处理
	 * @param string
	 */
	public function getPriceWhere()
	{
	    $keyword = str_replace(' ','',input('keyword'));//搜索关键字
	    $start = input('start_time');
	    $end = input('end_time');
		
	    $where = "(status=3 or status2=2 or status2=5)";
	    if($keyword){
	        $where .= " and (replace(a.dealer,' ','') like '%$keyword%' or a.number like '%$keyword%' or phone like '%$keyword%' or send_address like '%$keyword%')";
	    }
		if($start){
			$startTime = strtotime($start);
			$where .= " and a.car_time>='$startTime'";
		}
		if($end){
			$endTime = strtotime($end)+24*3600;
			$where .= " and a.car_time<='$endTime'";
		}
	    return $where;
	}
	
	/**
	 * 导出车间未处理订单
	 */
	public function exportPrice()
	{
	    $startTime = input('start_time');
	    $endTime = input('end_time');
	    if($startTime && $endTime) {
	        $title = $startTime . '--' . $endTime . '车间未处理订单';
	    }else{
	        $title = "车间未处理订单";
	    }
	    $where = $this->getPriceWhere();
	    $ordertype = config('order_type');
	    $list = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
	            . ",a.type,a.send_address,b.material,b.yarn_color,b.color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.note,b.flower_type,b.all_width,b.all_height")
	            ->join('order_price b','a.id=b.order_id')
	            ->where($where)
	            ->order('a.id desc')
	            ->select();
	    $material = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
	            . ",a.type,a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,'' as note,'' as flower_type,b.width as all_width,b.height as all_height")
	            ->join('order_material b','a.id=b.order_id')
	            ->where($where)
	            ->order('a.id desc')
	            ->select();
	    $list = array_merge($list,$material);
	    foreach ($list as $k => $v) {
	        $list[$k]['type'] = $ordertype[$v['type']];
	        
	        $list[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
	        $list[$k]['material'] = $v['material'].'/'.$list[$k]['yarn_color'];
	        
	    }
	    $excel = new \excel\Excel();
	    // $headArr = ['订单日期','订单编号','订单类型','业务员','客户名称','电话','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
	    // $field = ['addtime','number','type','sales_name','dealer','phone','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
	    // $excel->export('报价清单', $headArr, $list, $field, $title);
				$headArr = ['订单日期','订单编号','订单类型','业务员','客户ID编号','客户名称','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
				$field = ['addtime','number','type','sales_name','dealer_id','dealer','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
				$excel->export('车间未处理订单', $headArr, $list, $field, $title);
	}

    public function noHandleCount()
    {
        $list = Db::name('order')->where("status=3 or status2=2 or status2=5")->order('id desc')->count();
        $this->success('',$list);
    }
    
    /**
     * 发给营运审核
     */
    public function orderCheck()
    {
        $id = input('id/d');
        
        $res = Db::name('order')->where('id', $id)->update(['status2' => 3]);
        if ($res!==false) {
            $this->success('审核成功', '', url('handle'));
        }
        $this->error('审核失败,请重试');
    }

    public function test(){
        $logic = new AbutmentErp();
        $data = $logic->getStockSql(18869);dump($data);
    }

    /**
     * 订单审核通过
     */
    public function check()
    {
        $id = input('id/d');
        $field = input('field');
        $status = input('status/d');
        $select = input('select');
        
        $calculate = Db::name('order_price')->alias('a')->field('b.*')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where("a.order_id=$id and (order_type=0 or order_type=3)")
                ->select();       
        $write = true;       
        foreach ($calculate as $k => $v) {
            if($v['structure_id']==0){
                $write = false;
                break;
            }
        }
        if($write == false && $select !=1){
            $this->error('有产品未选择结构，请先选择结构');
        }
        $order = Db::name('order')->where('id', $id)->find();
        if($field == 'status2' && $order['status'] < 4){
            $res = Db::name('order')->where('id', $id)->update(['status'=>4,$field => $status + 1]);
        }else{
            $res = Db::name('order')->where('id', $id)->update([$field => $status + 1]);
        }
        
        //插入数据到erp的公共库
        if (($field == 'status' && $status + 1 == 4) || ($field == 'status2' && $status + 1 == 6)) {
            $order = Db::name('order')->where('id', $id)->find();
            $product = Db::name('order_price')->field('b.*,a.*,a.op_id as op_id')->alias('a')->join('order_calculation b', 'a.op_id=b.op_id', 'left')->where('order_id', $id)->select();
            $group = Db::name('order_group')->where('order_id', $id)->select();
            $material = Db::name('order_material')->where('order_id', $id)->select();
            $cal = [];
            foreach ($product as $k => $v) {
                //组合单的算料产品不用下车间
                if($v['order_type'] == 2){
                    unset($product[$k]);
                    continue;
                }
                if (isset($v['oc_id'])) {
                    $cal[] = ['oc_id' => $v['oc_id'], 'op_id' => $v['op_id'], 'spacing' => $v['spacing'], 'structure_id' => $v['structure_id'], 'structure' => $v['structure'],
                        'fixed_height' => $v['fixed_height'], 'hands' => $v['hands'], 'lock_position' => $v['lock_position']
                    ];
                }
            }


            $db2 = Db::connect('database.db2');
            //判断是否有多人同时点 下车间
            $checkCar = $db2->table('erp_order')->where('number',$order['number'])->find();
            if($checkCar){
                $this->_error('此单已经被其它人下到车间了');
            }

            $db2->startTrans();
            try{
                $db2->table('erp_order')->insert($order);
                if($product){
                    $db2->table('erp_order_price')->insertAll($product);
                }
                if($cal){
                    $db2->table('erp_order_calculation')->insertAll($cal);
                }
                if($group){
                    $db2->table('erp_order_group')->insertAll($group);
                }
                if($material){
                    $db2->table('erp_order_material')->insertAll($material);
                }
                //扣除中间表的 库存数
                $logic = new AbutmentErp();
                $sql = $logic->getStockSql($id);
                $db2->execute($sql);

                $db2->commit();
                $this->success('审核成功', '', url('handle'));
                $db2->close();
            }catch (\Exception $e){
                $db2->rollback();
                Db::name('order')->where('id', $id)->update([$field => $status]);//如果失败改回订单状态
                $this->error('审核失败,请联系系统管理员');
            }

        }

        $this->success('审核成功', '', url('handle'));

    }

    /**
     * 验证需要算料的产品是否有填写算料信息
     */
    public function calculateCheck()
    {
        $opid = input('opid');
        
        $calculate = Db::name('order_price')->alias('a')->field('b.*')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->whereIn("a.op_id",$opid)
                ->select();
        
        $write = [];
        
        foreach ($calculate as $k => $v) {
            if($v['structure_id']!=0){
                $write[] = $v['structure_id'];
            }
        }
        if(count($write) == 0){
            $this->error('至少选择一个结构,若需手动算料,点击查看算料结果按钮即可');
        }
        $this->success('');
    }

    /**
     * 查看产品详情
     */
    public function carEditInfo()
    {
        
        if ($this->request->isPost()) {
            $opId = input('op_id/d');
            $orderId = input('order_id');
            $data = input('post.');

            $orderlogic = new orderLogic();
            $caculationRes = $orderlogic->carEditInfo($data,$orderId);
            if ($caculationRes !== false) {
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
     * 已处理订单
     */
    public function handle()
    {
        $keyword = input('keyword');
		$start = input('start_time');
		$end = input('end_time');
		
		$where = "1=1";        
        if($keyword){
            $where .= " and (number like '%$keyword%' or dealer like '%$keyword%')";
        }
		if($start){
		    $startTime = strtotime($start);
		    $where .= " and car_time>='$startTime'";
		}
		if($end){
		    $endTime = strtotime($end)+24*3600;
		    $where .= " and car_time<='$endTime'";
		}
		
        $list = Db::name('order')->where("status=4 or status2=6 or status2=7 ")->where($where)->order('id desc')->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
		$this->assign('start_search', $start);
		$this->assign('end_search', $end);
		$this->assign('keyword', $keyword);
		$this->assign('search',input('get.'));
        return $this->fetch();
    }

	/**
	 * 获取报价单列表的搜索   车间已处理订单
	 * @param string
	 */
	public function getPriceWherea()
	{
	    $keyword = str_replace(' ','',input('keyword'));//搜索关键字
	    $start = input('start_time');
	    $end = input('end_time');
		
	    $where = "(status=4 or status2=6 or status2=7)";
	    if($keyword){
	        $where .= " and (replace(a.dealer,' ','') like '%$keyword%' or a.number like '%$keyword%' or phone like '%$keyword%' or send_address like '%$keyword%')";
	    }
		if($start){
			$startTime = strtotime($start);
			$where .= " and a.car_time>='$startTime'";
		}
		if($end){
			$endTime = strtotime($end)+24*3600;
			$where .= " and a.car_time<='$endTime'";
		}
	    return $where;
	}
	
	/**
	 * 导出车间已处理订单
	 */
	public function exportPricea()
	{
	    $startTime = input('start_time');
	    $endTime = input('end_time');
	    if($startTime && $endTime) {
	        $title = $startTime . '--' . $endTime . '车间已处理订单';
	    }else{
	        $title = "车间已处理订单";
	    }
	    $where = $this->getPriceWherea();
	    $ordertype = config('order_type');
	    $list = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
	            . ",a.type,a.send_address,b.material,b.yarn_color,b.color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,b.note,b.flower_type,b.all_width,b.all_height")
	            ->join('order_price b','a.id=b.order_id')
	            ->where($where)
	            ->order('a.id desc')
	            ->select();
	    $material = Db::name('order')->alias('a')->field("FROM_UNIXTIME(a.addtime, '%Y-%m-%d') as addtime,a.number,a.sales_name,a.dealer_id,a.dealer,a.phone,a.address"
	            . ",a.type,a.send_address,b.type as material,b.color as color_name,b.count,b.area,b.product_area,b.price,b.rebate,b.rebate_price,b.all_price,'' as note,'' as flower_type,b.width as all_width,b.height as all_height")
	            ->join('order_material b','a.id=b.order_id')
	            ->where($where)
	            ->order('a.id desc')
	            ->select();
	    $list = array_merge($list,$material);
	    foreach ($list as $k => $v) {
	        $list[$k]['type'] = $ordertype[$v['type']];
	        
	        $list[$k]['yarn_color'] = unserialize($v['yarn_color'])['name'];
	        $list[$k]['material'] = $v['material'].'/'.$list[$k]['yarn_color'];
	        
	    }
	    $excel = new \excel\Excel();
	    // $headArr = ['订单日期','订单编号','订单类型','业务员','客户名称','电话','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
	    // $field = ['addtime','number','type','sales_name','dealer','phone','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
	    // $excel->export('报价清单', $headArr, $list, $field, $title);
				$headArr = ['订单日期','订单编号','订单类型','业务员','客户ID编号','客户名称','地址','送货地址','材质','型号','颜色','数量','宽','高','报价面积','产品面积','单价','折扣率','折后价','总价','备注'];
				$field = ['addtime','number','type','sales_name','dealer_id','dealer','address','send_address','material','flower_type','color_name','count','all_width','all_height','area','product_area','price','rebate','rebate_price','all_price','note'];
				$excel->export('车间已处理订单', $headArr, $list, $field, $title);
	}



    /**
     * 查看已处理订单
     */
    public function handleOrder()
    {
        $id = input('id/d');
        //订单基本信息
        $res = Db::name('order')->where('id', $id)->find();
        $this->assign('orderid', $id);
        $this->assign('res', $res);

        //订单产品
        $product = Db::name('order_price')->alias('a')->field('a.*,b.structure,b.structure_id')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where(['a.order_id'=>$id,'order_type'=>['<=',1]])
                ->select();

        //订单原材料
        $material = Db::name('order_material')->where('order_id', $id)->select();

        //组合单
        $group = Db::name('order_group')->where('order_id', $id)->select();
        //定制类产品
        $diy = Db::name('order_price')->where('order_id',$id)->where('order_type',4)->select();
        
        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('order_type', config('order_type'));
        $this->assign('diy',$diy);
        $this->assign('group',$group);
        return $this->fetch();
    }

    /**
     * 查看订单详细
     */
    public function readOrder()
    {
        $id = input('id/d');

        //订单基本信息
        $res = Db::name('order')->where('id', $id)->find();
        $this->assign('orderid', $id);
        $this->assign('res', $res);

        //订单产品
        $product = Db::name('order_price')->alias('a')->field('a.*,b.structure,b.structure_id')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where("a.order_id=$id and a.order_type<=1")
                ->order('a.op_id asc')
                ->select();

        //订单原材料
        $material = Db::name('order_material')->where('order_id', $id)->select();
        
        //组合单
        $group = Db::name('order_group')->where('order_id', $id)->select();
        //组合单产品
        $groupProduct = Db::name('order_price')->alias('a')->field('a.*,b.structure,b.structure_id')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where(['a.order_id'=>$id,'a.order_type'=>3])
                ->select();

        //定制类产品
        $diy = Db::name('order_price')->where('order_id',$id)->where('order_type',4)->select();

        //需要算料的产品总数
        $totalGoods = Db::name('order_price')->alias('a')
            ->join('order_calculation b','a.op_id=b.op_id')
            ->where("order_id=$id and (order_type=0 or order_type=3) and b.structure_id!=0")->select();
        $haveStructure = array_column($totalGoods,'op_id');//有选结构图的总产品数
        $allruler = scandir('./ruler/');
        //判断是否有标尺脚本
        foreach ($totalGoods as $k => $v) {
            $formula = Db::name('series_structure')->alias('a')->field('a.*,b.id,b.path_url,b.structure_pic,b.ruler_pic')
                ->join('structure b', 'a.structure_id=b.id')
                ->where(['structure_id' => $v['structure_id'], 'series_id' => $v['series_id']])
                ->find();
            if(!in_array($formula['path_url'],$allruler)){
                unset($totalGoods[$k]);
            }
        }
        $opids = array_column($totalGoods,'op_id');//有标尺脚本的产品id

        $this->assign('material', $material);
        $this->assign('product', $product);
        $this->assign('order_type', config('order_type'));
        $this->assign('group',$group);
        $this->assign('group_product',$groupProduct);
        $this->assign('total_good',count($opids));
        $this->assign('diy',$diy);
        $this->assign('opids', implode(',', $haveStructure));
        $str = 'page='.input('page','').'&keyword='.input('keyword','');
        $this->assign('getstr',$str);
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
        $calculate = Db::name('order_price')->alias('a')
            ->field('a.*,b.structure')
            ->join('order_calculation b','a.op_id=b.op_id','left')
            ->where(['og_id'=>$ogId,'order_type'=>3])->select();
        
        $this->assign('orderid', $orderId);
        $this->assign('price',$price);
        $this->assign('calculate',$calculate);
        $this->assign('og_id',$ogId);
        $this->assign('car_group',1);
        return $this->fetch();
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
     * 组合单页面
     */
    public function groupOrder()
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
     * 查看产品详细
     */
    public function readProduct()
    {
        $opId = input('id/d');
        $order = new orderLogic();
        $info = $order->readProduct($opId);

        $this->assign('fixed', $info['fixed']);
        $this->assign('hands', $info['hands']);
        $this->assign('info', $info['info']);
        return $this->fetch();
    }

    /**
     * 查看手工单
     */
    public function readHands()
    {
        $opId = input('id/d');
        $order = new orderLogic();
        $info = $order->readProduct($opId);

        $result = Db::name('order_result')->where('op_id',$opId)->find();
        
        $this->assign('result',unserialize($result['all_data']));
        $this->assign('fixed', $info['fixed']);
        $this->assign('hands', $info['hands']);
        $this->assign('info', $info['info']);
        return $this->fetch();
    }
    
    /**
     * 批量算料
     */
    public function allIframe()
    {
        error_reporting(E_ALL);
        $opIds = input('op_id/a');
        $seriesIds = input('series_id/a');
        $structureIds = input('structure_id/a');
        $orderId = input('order_id/d');

        Db::name('order_result')->where(['order_id'=>$orderId,'is_hand'=>0])->delete();
        $allruler = scandir('./ruler/');
        $loop = [];//存储标尺数据
        foreach ($opIds as $k => $v) {
            $structureId = $structureIds[$k];
            //若未选结构，则不进行算料
            if($structureId == 0){
                continue;
            }
            $seriesId = $seriesIds[$k];
            $opId = $v;

            //系列所选的结构 公式id
            $formula = Db::name('series_structure')->alias('a')->field('a.*,b.id,b.path_url,b.structure_pic,b.ruler_pic')
                    ->join('structure b', 'a.structure_id=b.id')
                    ->where(['structure_id' => $structureId, 'series_id' => $seriesId])
                    ->find();
            
            //系列的物料绑定关系-对应铝型材的物料小面为 边框，外框等
            $seriesBom = Db::name('series_bom')->alias('a')->field('a.*,b.small')
                    ->join('bom_aluminum b', 'a.two_level=b.id','left')
                    ->where('series_id', $seriesId)
                    ->select();

            //花件最大宽，最大高等
            $flowerBom = Db::name('order_price')->field('b.*')->alias('a')->join('bom_flower b', 'a.flower_id=b.id')->where('a.op_id', $opId)->find();
            
            $product = Db::name('order_price')->alias('a')->field('a.*,b.*,c.*,a.count as ccount')
                    ->join('order_calculation b', 'a.op_id=b.op_id')
                    ->join('order c', 'a.order_id=c.id')
                    ->where(['a.op_id' => $opId])
                    ->find();            
            $calculateRes = [];
            $flower = isset($product['flower_pic']) ? $product['flower_pic'] : ''; //花件图
            $flowers = isset($product['flower_pics']) ? $product['flower_pics'] : ''; //花件图
            $rulerPic = '';  //标尺图
            $alumPic = ''; //铝型色卡
            $flowerPic = ''; //花件色卡

            if ($formula) {
                //标尺公式
                $rulerFormula = Db::name('structure_ruler_formula')->where('srf_id', $formula['srf_id'])->find();
                //算料公式
                $calculateFormula = Db::name('structure_calculate_formula')->where('scf_id', $formula['scf_id'])->find();
                $calculate = new calculate();

                $alumColorId = isset($product['alum_color_id']) ? $product['alum_color_id'] : 0;  //铝型颜色最后一级Id
                $flowerColorId = isset($product['flower_color_id']) ? $product['flower_color_id'] : 0; //花件颜色最后一级id
                $alumColor = Db::name('bom_color')->where('id', $alumColorId)->find();
                $flowerColor = Db::name('bom_color')->where('id', $flowerColorId)->find();

                $rulerPic = $formula['structure_pic']; //标尺图
                $alumPic = isset($alumColor['pic']) ? $alumColor['pic'] : ''; //铝型色卡
                $flowerPic = isset($flowerColor['pic']) ? $flowerColor['pic'] : ''; //花件色卡
                //执行公式  
//				print_r(unserialize($rulerFormula['formula']));
				$export = $calculate->getResult(unserialize($rulerFormula['formula']), unserialize($calculateFormula['formula']),$product, $seriesBom, $flowerBom);                
                $calculateRes = $export['cexport']; 
                $rulerExport = explode('&', $export['export']);
                $pathUrl = $formula['path_url'];  //调起的脚本名称
            }
            
            //将算料结果存入数据表中
            $insert = ['op_id' => $opId, 'order_id' => $product['order_id'], 'calculate_size' => serialize($calculateRes['result']),
                'flower' => $calculateRes['flower'], 'ruler_data' => $export['rexport'],
                'calculate_data' => $export['calculate_str']];
            $resId = Db::name('order_result')->insertGetId($insert);

            //有标尺脚本的才 进行标尺
            if(in_array($pathUrl,$allruler)){
                $loop[] = ['export' => $rulerExport, 'ruler_pic' => $rulerPic, 'alum_pic' => $alumPic, 'flower_pic' => $flowerPic, 'flowerd' => $flower,
                    'op_id' => $v, 'res_id' => $resId, 'path_url' => $pathUrl,'structure_id' => $formula['id'],'flowerds' => $flowers,];
            }
        }
        $this->assign('order_id', $orderId);
        $this->assign('loop', $loop);
        $this->assign('allruler',$allruler);
        return $this->fetch();
    }

    /**
     * 单个算料
     */
    public function iframeImg()
    {
        error_reporting(E_ALL);
        $op_id = input('op_id/d');
        $order_id = input('order_id/d');

        $this->assign('op_id', $op_id);
        $this->assign('order_id', $order_id);


        //执行脚本，显示HTML,并且自动执行截图
        $seriesId = input('series_id');
        $opId = input('op_id'); //产品id
        $structureId = input('structure_id');

        //系列所选的结构 公式id
        $formula = Db::name('series_structure')->alias('a')->field('a.*,b.path_url,b.structure_pic,b.ruler_pic')
                ->join('structure b', 'a.structure_id=b.id')
                ->where(['structure_id' => $structureId, 'series_id' => $seriesId])
                ->find();


        //系列的物料绑定关系-对应铝型材的物料小面为 边框，外框等
        $seriesBom = Db::name('series_bom')->alias('a')->field('a.*,b.small')
                ->join('bom_aluminum b', 'a.two_level=b.id')
                ->where('series_id', $seriesId)
                ->select();

        $product = Db::name('order_price')->alias('a')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->join('order c', 'a.order_id=c.id')
                ->where(['a.op_id' => $opId])
                ->find();
        $calculateRes = [];
        $flower = isset($product['flower_pic']) ? $product['flower_pic'] : ''; //花件图
        $rulerPic = '';  //标尺图
        $alumPic = ''; //铝型色卡
        $flowerPic = ''; //花件色卡
        if ($formula) {
            //标尺公式
            $rulerFormula = Db::name('structure_ruler_formula')->where('srf_id', $formula['srf_id'])->find();
            //算料公式
            $calculateFormula = Db::name('structure_calculate_formula')->where('scf_id', $formula['scf_id'])->find();

            //花件最大宽，最大高等
            $flowerBom = Db::name('order_price')->field('b.*')->alias('a')->join('bom_flower b', 'a.flower_id=b.id')->where('a.op_id', $opId)->find();

            $calculate = new calculate();

            $alumColorId = isset($product['alum_color_id']) ? $product['alum_color_id'] : 0;  //铝型颜色最后一级Id
            $flowerColorId = isset($product['flower_color_id']) ? $product['flower_color_id'] : 0; //花件颜色最后一级id
            $alumColor = Db::name('bom_color')->where('id', $alumColorId)->find();
            $flowerColor = Db::name('bom_color')->where('id', $flowerColorId)->find();

            $rulerPic = $formula['structure_pic']; //标尺图
            $alumPic = isset($alumColor['pic']) ? $alumColor['pic'] : ''; //铝型色卡
            $flowerPic = isset($flowerColor['pic']) ? $flowerColor['pic'] : ''; //花件色卡
            //执行公式  
            $export = $calculate->getResult(unserialize($rulerFormula['formula']), unserialize($calculateFormula['formula']), $product, $seriesBom, $flowerBom);
            $calculateRes = $export['cexport'];
            $rulerExport = explode('&', $export['export']);

            $pathUrl = $formula['path_url'];  //调起的脚本名称
        }

        //判断是否已经存在结果，然后将算料结果存入数据表中
        $exist = Db::name('order_result')->where('op_id', $opId)->find();
        if ($exist) {
            Db::name('order_result')->where('or_id', $exist['or_id'])->update(['calculate_size' => serialize($calculateRes['result']), 'flower' => $calculateRes['flower']]);
            $resId = $exist['or_id'];
        } else {
            $insert = ['op_id' => $opId, 'order_id' => $order_id, 'calculate_size' => serialize($calculateRes['result']), 'flower' => $calculateRes['flower']];
            $resId = Db::name('order_result')->insertGetId($insert);
        }

        $this->assign('ruler_pic', $rulerPic);
        $this->assign('alum_pic', $alumPic);
        $this->assign('flower_pic', $flowerPic);
        $this->assign('flowerd', $flower);
        $this->assign('export', $rulerExport);
        $this->assign('res_id', $resId);
        $this->assign('path_url', $pathUrl);
        $this->assign('structure_id',$structureId);
        $this->assign('allruler',scandir('./ruler/'));
        return $this->fetch();
    }

    /**
     * 保存图片
     */
    public function saveImg()
    {
        error_reporting(E_ALL);
        $op_id = input('op_id/d'); //价格op_id     
        $order_id = input('order_id/d'); //保存订单id

        $image = $_POST['image']; //base_64数据
        $ext = input('suffix');

        //保存二进制图片
        $path = $this->Fupload($image, $ext);

        if ($path === false) {
            return array('status' => '1', 'msg' => '保存图片失败,请重试');
        }

        //保存标尺图到数据表
        $id = Db::name('order_result')->where('or_id', input('res_id'))->update(['path' => substr($path, 1), 'addtime' => time()]);

        return array('status' => '1', 'msg' => '保存图片成功', 'or_id' => input('res_id'), 'order_id' => $order_id);
    }

    /**
     * 图片保存函数,保存base64数据
     * @param  $image 图片二进制数据
     * @param  $ext 图片文件后缀
     * @return boolean|string
     */
    function Fupload($image, $ext)
    {
        error_reporting(E_ALL);
        $targetFolder = config('upload') . 'calculate/'; // 独立一个文件夹
        $targetPath = $targetFolder . date('Ymd') . '/'; //保存到当前文件夹

        $now = time();
        $targetFile = $targetPath . md5($now . rand(10000, 99999)) . '.' . $ext;

        //创建目录
        if (!file_exists($targetPath)) {
            mk_dir($targetPath);
        }
        if (strstr($image, ",")) {
            $image = explode(',', $image);
            $image = $image[1];
        }
        $r = file_put_contents($targetFile, base64_decode($image)); //存储大图到目录
        if (!$r) {
            return false;
        } else {
            return $targetFile; //返回路径前缀含.的路径
        }
    }

    /**
     * 算料标尺结果--全部
     */
    public function allCalculate()
    {
        $orderId = input('order_id/d');

        $order = Db::name('order')->where('id', $orderId)->find();
        $product = Db::name('order_result')->alias('a')->field('a.*,b.series_id,b.flower_type,b.material,b.color_name,b.area,b.product_area,
        b.count,b.note,b.order_type,c.lock_position,c.structure_id,c.spacing,c.cal_note,c.bottom_spacing,c.right_bottom_spacing,c.bottom_fixed_spacing,c.bottom_vertical_spacing,c.flower_types')
                ->join('order_price b', 'a.op_id=b.op_id','right')
                ->join('order_calculation c', 'c.op_id=b.op_id')
                ->where('b.order_id', $orderId)
                ->where('b.order_type != 2')
                ->order('b.op_id asc')
                ->select();

        //整理数据
        $list = [];
        foreach ($product as $k => $v) {
            $v['type'] = substr($v['material'], strrpos($v['material'], '-') + 1);
            $calculate = unserialize($v['calculate_size']);

            $seriesBom = Db::name('series_bom')->where('series_id', $v['series_id'])->where('type', 1)->find();

            if ($calculate) {
                foreach ($calculate as $k2 => $v2) {
                    //如果当前产品的系列未绑定边框,则隐藏边框数据
                    if (isset($seriesBom['one_level']) && $seriesBom['one_level'] == '') {
                        $borderFrame = mb_substr($v2, 0, 2);
                        if ($borderFrame == '边框') {
                            unset($calculate[$k2]);
                        }
                    }
                    //隐藏小门框,扇,中横开料数据
                    $oneData = mb_substr($v2, 0, 1);
                    $twoData = mb_substr($v2, 0, 2);
                    $threeData = mb_substr($v2, 0, 3);
                    $hide = ['小门框', '扇', '中横', '上中横', '下中横', '窄边框', '窄内框', '窄外框'];
                    if (in_array($oneData, $hide) || in_array($twoData, $hide) || in_array($threeData, $hide)) {
                        unset($calculate[$k2]);
                    }
                }
            }
            
            
            $v['calculate'] = $calculate;
            //加入产品的结构图
            $structurePic = Db::name('order_price')->alias('a')->field('c.structure_pic')
                ->join('order_calculation b','a.op_id=b.op_id')
                ->join('structure c','b.structure_id=c.id','left')
                ->where('a.op_id',$v['op_id'])
                ->find();
            $v['structure_pic'] = $structurePic['structure_pic'];

            //如果花件有多个，将其换行显示
            if(substr_count($v['flower'],'*')>1){
                $flowerTemp = explode(',',$v['flower']);
                $new = "";
                foreach ($flowerTemp as $k5 => $v5) {
                    //如果是00
                    if($v5 == '00*0'||$v5 == ''||$v5 == '0*0*0'||$v5 == '0*0'){
                        $new .= "<hr style='height:10px;border-top:2px solid;border-bottom: 2px solid;margin: 0'>";
                        continue;
                    }
                $arr  = [];
				$arr1 = explode('*',$v5);
				foreach($arr1 as $vo1){
					$arr2 = explode('.',$vo1);
					$arr[] = $arr2[0];
				}
				$new .= implode('*',$arr).'</br>';
									//$new .= "$v5".'</br>';
							}
							$v['flower'] = $new;
					}
            $list[] = $v;

        }

        $qrcode = new QrCode();
        $qrstring = $qrcode->setText($order['number'])->writeDataUri();
        $order['qrcode'] = $qrstring;//二维码改成只要生产单号

        $material = Db::name('order_material')->where('order_id',$orderId)->select();
        $this->assign('material',$material);
        $this->assign('order', $order);
        $this->assign('list', $list);
        return $this->fetch('calculate');
    }

    /**
     * 查看产品原材料
     */
    public function readMaterial()
    {
        $omId = input('id/d');
        $material = Db::name('order_material')->where('om_id', $omId)->find();

        $this->assign('info', $material);
        return $this->fetch();
    }
    

    /**
     * 停止订单
     */
    public function stop()
    {
        $id = input('id/d');
        $res = Db::name('order')->where('id',$id)->update(['is_stop'=>1]);
        
        if($res!==false){
            $this->success('暂停成功');
        }
        $this->error('暂停失败,请重试');
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
    
}
