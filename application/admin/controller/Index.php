<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use app\common\lib\Time;
use tree\Tree;

class Index extends Base
{

    /**
     * 登陆
     */
    public function login()
    {
        return $this->fetch();
    }
    
    /**
     * 切换账号--退出
     */
    public function switchUser()
    {
        cookie('uid',null);
        cookie('login_name',null);
        $this->redirect(url('index/login'));
    }
    
    /**
     * 登陆验证
     */
    public function checkLogin()
    {
        $loginName = input('login_name');
        $loginPassword = input('login_password');
        
        $user = Db::name('user')->alias('a')->field('a.*,b.group_id')
                ->join('auth_group_access b','a.id=b.uid','left')
                ->where(['login_name'=>$loginName,'is_disable'=>0])
                ->find();
        if($user){
            $passwd = password($loginPassword);
            if($passwd != $user['login_password']){
                $this->error('密码不正确');
            }
            //写入cookie
            cookie('login_name', $loginName, 7*24*3600);
            cookie('uid', $user['id'], 7*24*3600);
            cookie('group_id',$user['group_id'],7*24*3600);
            cookie('bind_dealer',$user['bind_dealer'],7*24*3600);
            $this->success('登陆成功', '',url('index/index'));
        }else{
            $this->error('此用户不存在或已被禁用');
        }
    }
    
    
    /**
     * 菜单栏
     */
    public function index()
    {        
        //菜单渲染
        $tree = new Tree();
        $group = Db::name('auth_group_access')->alias('a')->field('b.rules')
                        ->join('auth_group b','a.group_id=b.id','left')
                        ->where('uid', $this->uid)
                        ->find();                
        $ruleIds = isset($group['rules'])?$group['rules']:0;
        
        if($ruleIds == "*"){
            $result = Db::name('auth_rule')->where('is_menu',0)->order('sort')->select();
        }else{
            $result = Db::name('auth_rule')->where('is_menu',0)->whereIn('id', $ruleIds)->order('sort')->select();
        }
        $tree->init($result);
        $array = $tree->getTreeArray(0);
        $this->assign('list',$array);  
               
        
        //未处理订单数量
        $noHandle = Db::name('order')->where("status=3 or status2=2 or status2=5")->order('id desc')->count();
        //待发货订单数量
        $waitDelivery = Db::name('order')->where("(status=7 or status2=7) and is_send=0")->count();
        //财务待处理订单
        $financeHandle =  Db::name('order')->where("status=2 or status2=4")->count();
        //财务配送批次订单
        $financeDelivery =  Db::name('order_send')->count();
        
        $tipUrl = ['carorder/nohandle','allorder/waitdelivery','finance/nohandle','finance/delivery']; //需要添加数量提示的菜单
        $tip['carorder/nohandle'] = ['id'=>'no-handle-count','value'=>$noHandle];
        $tip['allorder/waitdelivery'] = ['id'=>'wait-delivery-count','value'=>$waitDelivery];
        $tip['finance/nohandle'] = ['id'=>'finance-no-handle','value'=>$financeHandle];
        $tip['finance/delivery'] = ['id'=>'finance-delivery','value'=>$financeDelivery];
        
        $this->assign('finance',$financeHandle);
        $this->assign('delivery',$waitDelivery);
        $this->assign('no_handle',$noHandle);
        $this->assign('finance_delivery',$financeDelivery);
        $this->assign('tip_url',$tipUrl);
        $this->assign('tip',$tip);
        $this->assign('group_id',$this->group_id);
        return $this->fetch('index2');
    }

    /**
     * 首页
     */
    public function welcome()
    {

        $where = $this->buildCondition('week');

        //本月销售总额
        $monthTime = timezone_get(3);
        $todaymonth = $this->dealerSales($monthTime);
        //上月销售
        $prevMonthTime = timezone_get(9);
        $prevmonth = $this->dealerSales($prevMonthTime);
        //上年本月时间
        $prevYear = strtotime(date("Y-" . date('m') . "-01", strtotime('-1 year')));
        $prevYearEnd = $prevYear + date('t') * 24 * 3600;
        $lastYear = $this->dealerSales(['begin' => $prevYear, 'end' => $prevYearEnd]);

        $dirtyData = Db::name('order_price')->alias('a')
            ->join('order_calculation b','a.op_id=b.op_id','left')
            ->where('a.order_type=0 and b.oc_id is null')
            ->column('a.op_id');
        if($dirtyData && count($dirtyData) <=10)
        {
            Db::name('order_price')->whereIn('op_id',$dirtyData)->delete();
        }

        $this->assign('today_month',$todaymonth);
        $this->assign('prev_month',$prevmonth);
        $this->assign('month_year',$lastYear);
        return $this->fetch();
    }

    /**
     * 经销商按时间汇总
     * @param $time
     */
    public function dealerSales($time)
    {
        $sum = Db::name('order')->where("addtime>={$time['begin']} and addtime<={$time['end']}")
            ->sum('total_price');
        //上月销售前10数据
        $month = Db::name('order')->alias('a')
            ->field('a.*,sum(total_price) as all_price,b.name')
            ->join('dealer b','a.dealer_id=b.id')
            ->where("addtime>={$time['begin']} and addtime<={$time['end']}")
            ->group('dealer_id')
            ->orderRaw('sum(total_price) desc')
            ->limit(10)
            ->select();
        $monthTen = array_sum(array_column($month,'all_price'));//上月前10总数

        return ['sum'=>$sum,'list'=>$month,'ten'=>$monthTen];
    }

    /**
     * 概况统计
     */
    public function count()
    {
        $time = $this->getTime();
        list($todayStart, $todayEnd, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd) = $time;

        $type = input('type');        
        $startTime = input('time');
        if ($type == 'today') {
            $start = $todayStart;
            $end = $todayEnd;
            $count = 1;
        } elseif ($type == 'week') {
            $start = $weekStart;
            $end = $weekEnd;
            $count = 7;
        } elseif ($type == 'month') {
            $start = $monthStart;
            $end = $monthEnd;
            $count = intval(($end - $start) / (24 * 3600));
        } elseif($type == 'year'){
            $start = $yearStart;
            $end = $yearEnd;
            $count = intval(($end - $start) / (24 * 3600));
        }
        
        
        if($type == 'btn'){
            $time = explode('~',$startTime);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+24*3600;
            $count = intval(($end-$start)/(24*3600));
        }
        $where = " addtime between $start and $end";

        $_order = Db::name('order');
        $money = $_order->field("sum(total_price) as total")->where($where)->where('type','<>',4)->find();
        $havePay = Db::name('paid_record')->field("sum(have_pay) as total")->where($where)->find();
        $noPay = round($money['total']-$havePay['total'],2);
        $order = $_order->field("count(id) as total")->where($where)->find();
        $product = $_order->field("sum(count) as total")->where($where)->find();
        $area = $_order->field("sum(area) as total")->where($where)->where("type <> 4 and type <> 5 and type <> 6")->find();
				$material = Db::name('order_material')->where($where)->select();
				$materialParea = array_sum(array_column($material, 'product_area'));
				

        $averageMoney = round($money['total'] / $count, 2);
        $averageHave = round($havePay['total'] / $count, 2);
        $averageNo = round($noPay / $count, 2);
        $averageOrder = round($order['total'] / $count, 2);
        $averageProduct = round($product['total'] / $count, 2);
        $averageArea = round(($area['total'] - $materialParea) / $count, 2);
				
				

        $data = [
            'money' => round($money['total'],2), 'averageMoney' => $averageMoney, 'order' => round($order['total'],2), 'averageOrder' => $averageOrder,
            'product' => round($product['total'],2), 'averageProduct' => $averageProduct, 'area' => round($area['total']-$materialParea,2), 'averageArea' => $averageArea,
            'havePay' => round($havePay['total'],2),'averageHave' => $averageHave,'noPay'=>round($noPay,2),'averageNo' => $averageNo,
        ];
        $this->success('', $data);
    }

    /**
     * 一级系列柱状图
     */
    public function series()
    {
        $type = input('field','week');
        $timesql = $this->buildCondition($type);

        $oneSeries = Db::name('series')->where('parent_id',0)->where('static_show',1)->select();//一级系列
        $series = Db::name('series')->where('static_show',1)->select();
        $data = [];
        foreach ($oneSeries as $k => $v) {
            $child = getChild($series,$v['id']);
            //获取有使用到子系列的产品
            $price = Db::name('order_price')->whereIn('series_id',$child)->where($timesql)->sum('area');
            $data[$k]['name'] = $v['name'];
            $data[$k]['value'] = round($price,2);
        }
        $sort = [];
        //排序
        foreach ($data as $k => $v) {
            $sort[] = $v['value'];
        }
        array_multisort($sort,SORT_DESC,$data);
        $name = array_column($data,'name');
        $value = array_column($data,'value');
        return ['name'=>$name,'value'=>$value];
    }

    /**
     * 业务员柱状图
     */
    public function salesName()
    {
        $type = input('field','week');
        $timesql = $this->buildCondition($type);
        $salesname = Db::name('order')->field('sum(total_price) as price,sales_name')
            ->where($timesql)->group('sales_name')
            ->orderRaw('sum(total_price) desc')
            ->select();
        $name = array_column($salesname,'sales_name');
        $price = array_column($salesname,'price');
        return ['name'=>$name,'value'=>$price];
    }

    /**
     * 系列面积类别占比--饼状图
     */
    public function areaCount()
    {
        $type = input('field','week');
        $where = $this->buildCondition($type);
        $all = Db::name('order_price')->where($where)->sum('area');//符合条件的总面积
        //面积类别占比--饼状图
        $arealist = Db::name('order_price')->alias('a')->field('a.name,sum(area) as area,b.name as series_name')
            ->join('series b','a.series_id=b.id')
            ->where($where)->group('series_id')
            ->orderRaw('sum(area) desc')
            ->limit(10)
            ->select();
        $title = [];
        $data = [];
        $total = 0;//前10条总数
        foreach ($arealist as $k => $v) {
            $name = $v['name'].'-'.$v['series_name'];
            $title[] = $name;
            $data[] = ['value'=>round($v['area'],2),'name'=>$name];
            $total += round($v['area'],2);
        }
        //加入 其他类别
        $data[] = ['value'=>round($all-$total,2),'name'=>'其它'];
        $data = ['title'=>$title,'value'=>$data];

        $this->success('',$data);
    }


    /**
     * 系列价格类别占比--饼状图
     */
    public function priceCount()
    {
        $type = input('field','week');
        $where = $this->buildCondition($type);
        $all = Db::name('order_price')->where($where)->sum('all_price');//符合条件的总价格

        $arealist = Db::name('order_price')->alias('a')->field('a.name,sum(all_price) as all_price,b.name as series_name')
            ->join('series b','a.series_id=b.id')
            ->where($where)->group('series_id')
            ->orderRaw('sum(all_price) desc')
            ->limit(10)
            ->select();
        $title = [];
        $data = [];
        $total = 0;//前10条总数
        foreach ($arealist as $k => $v) {
            $name = $v['name'].'-'.$v['series_name'];
            $title[] = $name;
            $data[] = ['value'=>round($v['all_price'],2),'name'=>$name];
            $total += round($v['all_price'],2);
        }
        //加入 其他类别
        $data[] = ['value'=>round($all-$total,2),'name'=>'其它'];
        $data = ['title'=>$title,'value'=>$data];

        $this->success('',$data);
    }

    /**
     * 本月销售额 回款额
     */
    public function salesBack()
    {
        $monthTime = timezone_get(3);
        $sales = Db::name('order')->where("addtime between {$monthTime['begin']} and {$monthTime['end']}")->sum('total_price');
        $back = Db::name('paid_record')
            ->where("addtime between {$monthTime['begin']} and {$monthTime['end']}")
            ->sum('have_pay');
        $name = ['本月销售订单额','本月实际回款额'];
        $value = [$sales,$back];
        return ['name'=>$name,'value'=>$value];
    }


    /**
     * 构建查询条件where
     * @return string
     */
    public function buildCondition($type)
    {
        $time = $this->getTime();
        list($todayStart, $todayEnd, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd) = $time;

        if ($type == 'today') {
            $start = $todayStart;
            $end = $todayEnd;
        } elseif ($type == 'week') {
            $start = $weekStart;
            $end = $weekEnd;
        } elseif ($type == 'month') {
            $start = $monthStart;
            $end = $monthEnd;
        } elseif($type == 'year'){
            $start = $yearStart;
            $end = $yearEnd;
        }elseif($type == 'btn'){
            $time = explode('~',input('time'));
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+(24*3600);
        }

        return $where = "addtime between $start and $end";;
    }


//    /**
//     * 柱状图统计
//     */
//    public function sales()
//    {
//        $field = input('field');
//        $fieldArray = ['total_price','id','count','area'];
//        if(!in_array($field,$fieldArray)){
//            $this->error('参数错误');
//        }
//
//        //获取当前年数每个月开始的时间戳
//        $time = [];
//        for($i=1;$i<=12;$i++){
//           $time[] = mktime(0, 0, 0, $i, 1,date('Y'));
//        }
//        //加入明年的1月份的时间戳
//        $nextYear =mktime(0,0,0,1,1,date('Y')+1);
//        $result = [];
//        foreach($time as $k => $v){
//            if(isset($time[$k+1])){
//                $end = $time[$k+1];
//            }else{
//                $end = $nextYear;
//            }
//
//            $res = Db::name('order')->field("sum($field) as total")->where("addtime between $v and $end")->find();
//            $result[] = $res['total']?round($res['total'],2):0;
//        }
//        $max = intval(max($result));
//        $this->success('',['data'=>$result,'max'=>$max]);
//    }
//
//    /**
//     * 门店销售统计
//     */
//    public function dealerCount()
//    {
//        $type = input('type');
//        $where = $this->buildCondition($type);
//        //门店销售排行
//        $dealer = Db::name('order')->alias('a')->field('sum(total_price) as total,b.name as dealer_name')
//                    ->join('dealer b','a.dealer_id=b.id')
//                    ->where($where)
//                    ->group('a.dealer_id')
//                    ->order('total desc')
//                    ->limit(10)
//                    ->select();
//        $this->success('',$dealer);
//    }
//
//
//
//    /**
//     * 订单数类别占比--饼状图
//     */
//    public function orderCount()
//    {
//        $type = input('type');
//        $where = $this->buildCondition($type);
//        $orderlist = Db::name('order_price')->alias('a')->field('a.name,sum(count) as count,b.name as series_name')
//                ->join('series b','a.series_id=b.id')
//                ->where($where)->group('series_id')
//                ->order('count desc')
//                ->select();
//        $title = [];
//        $data = [];
//        foreach ($orderlist as $k => $v) {
//            $name = $v['name'].'-'.$v['series_name'];
//            $title[] = $name;
//            $data[] = ['value'=>$v['count'],'name'=>$name];
//        }
//        $data = ['title'=>$title,'value'=>$data];
//        $this->success('',$data);
//
//    }
//
//    /**
//     * 订单产品类别占比--饼状图
//     */
//    public function productCount()
//    {
//        $type = input('type');
//        $where = $this->buildCondition($type);
//        //产品类别占比--饼状图
//        $productlist = Db::name('order_price')->alias('a')->field('a.name,count(series_id) as count,b.name as series_name')
//                ->join('series b','a.series_id=b.id')
//                ->where($where)->group('series_id')
//                ->order('count desc')
//                ->select();
//        $title = [];
//        $data = [];
//        foreach ($productlist as $k => $v) {
//            $name = $v['name'].'-'.$v['series_name'];
//            $title[] = $name;
//            $data[] = ['value'=>$v['count'],'name'=>$name];
//        }
//        $data = ['title'=>$title,'value'=>$data];
//        $this->success('',$data);
//
//    }
//
//
//
//
//
//    /**
//     * 订单占比--饼状图
//     */
//    public function typeCount()
//    {
//        $time = $this->getTime();
//        list($todayStart, $todayEnd, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd) = $time;
//
//        $type = input('type');
//        if ($type == 'today') {
//            $start = $todayStart;
//            $end = $todayEnd;
//        } elseif ($type == 'week') {
//            $start = $weekStart;
//            $end = $weekEnd;
//        } elseif ($type == 'month') {
//            $start = $monthStart;
//            $end = $monthEnd;
//        } elseif($type == 'year'){
//            $start = $yearStart;
//            $end = $yearEnd;
//        }
//
//        //所选时间段的所有产品
//        $list = Db::name('order_price')->alias('a')
//                        ->join('order b','a.order_id=b.id')
//                        ->where("addtime between $start and $end")
//                        ->column('series_id');
//        $allCount = count($list);
//        $number = array_count_values($list); //对数组进行统计
//        $seriesIds = array_keys($number);
//        $seriesName = Db::name('series')->whereIn('id', $seriesIds)->column('name');
//
//        $percent = [];
//        $i = 0;
//        foreach($number as $k => $v){
//            $percent[$i]['name'] = $seriesName[$i];
//            $percent[$i]['value'] = round($v/$allCount,2);
//            $i++;
//        }
//
//        $this->success('',$percent);
//    }
    
    
    /**
     * 获取今天，本周，本月，本年的开始和结束时间
     * @return array
     */
    public function getTime()
    {
        $time = new Time();
        $today = $time->today(); //今天时间
        $week = $time->week(); //本周时间
        $month = $time->month(); //本月时间
        $year = $time->year(); //本年时间

        list($todayStart, $todayEnd) = $today;
        list($weekStart, $weekEnd) = $week;
        list($monthStart, $monthEnd) = $month;
        list($yearStart, $yearEnd) = $year;

        return [$todayStart, $todayEnd, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd];
    }
    
    /**
     * 编辑用户
     */
    public function editUser()
    {
        $id = input('id/d');
        if ($this->request->isPost()) {
            $password = input('login_password');
            $data = input('post.');
            $data['login_password'] = password($password);
            if($password==''){
                unset($data['login_password']);
            }
            
            $res = Db::name('user')->where('id', $id)->update($data);
            $authGroup = Db::name('auth_group_access')->where('uid', $id)->update(['group_id' => $data['depart']]);
            if ($res !== false && $authGroup !== false) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $res = Db::name('user')->where('id', $id)->find();
        $role = Db::name('auth_group')->order('name')->select();
        $dealer = Db::name('dealer')->orderRaw("convert(name using gbk)")->select();

        $this->assign('dealer',$dealer);
        $this->assign('role', $role);
        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        $id = input('id/d');
        if($id == 1){
            $this->_error('不可删除');
        }
        $group = Db::name('auth_group_access')->where('uid',$id)->delete();
        $res = Db::name('user')->where('id',$id)->delete();
        
        write_log("删除用户id为:{$id}的用户", cookie('uid'), cookie('login_name'));
        
        if($res){
            $this->success('删除成功');
        }
        $this->error('删除失败,请重试');
    }


}
