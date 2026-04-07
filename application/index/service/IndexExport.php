<?php

namespace app\index\service;

use think\Controller;
use think\Db;

/**
 * 首页的数据导出
 */
class IndexExport extends Controller
{
    
    /**
     * 导出生产计划达成率
     */
    public function schedule()
    {
        $field = ['day'=>'时间','ordernum' => '排产量','finish' => '完成量','unfinish' => '未完成'];
        $title['工序达成率'] = ['gx_name'=>'工序','ordernum'=>'排产量','complete'=>'完成量','no_complete'=>'未完成','percent'=>'计划达成率'];
        $title['本月'] = $field;
        $title['本年'] = $field;
       
        //工序达成率
        $gxsql = "SELECT gx_name,ordernum,(select count(*) from bg_schedule where sid=a.id and finished=1) as complete FROM `bg_schedule_summary` as a order by a.id desc"; 
        $gxList = Db::query($gxsql);
        foreach($gxList as $k => $v){
            $gxList[$k]['no_complete'] = $v['ordernum']-$v['complete'];
            $gxList[$k]['percent'] = (round($v['complete']/$v['ordernum'],2)*100).'%';
        }
        
        //本月总统计
        $month = timezone_get(3);//本月开始和结束时间
        $times = [];//本月每一天的开始和结束时间戳
        for($t=$month['begin'];$t<=$month['end'];$t+=24*3600){
            $end = $t+(24*3600-1);
            $times[] = ['begin'=>$t,'end'=>$end];
        }

        $scheduleMaster = Db::name('schedule_summary')->where("do_time>={$month['begin']} and do_time<{$month['end']}")->select();
        //获取排产表id
        $sid = [];
        foreach ($scheduleMaster as $key => $value) {
            $sid[] = $value['id'];
        }
        $sids = implode(',', $sid);        
        //获取当前月排产附表数据
        $schedule = Db::name('schedule')->alias('a')->field('a.*,b.do_time')->join('schedule_summary b','a.sid=b.id')
                ->whereIn('sid', $sids)
                ->where('a.finished',1)
                ->select();
        $list = $this->getTotalSchedule($times, $scheduleMaster, $schedule);
        
        
        //本年总统计
        $yearTimes = [];
        for($i=1;$i<=12;$i++){
            $now = time();
            $begin = mktime(0, 0,0, $i, 1, date('Y', $now));
            $end = mktime(23, 59, 59, $i, date('t', $now), date('Y', $now))-1;
            $yearTimes[] = ['begin' => $begin,'end' => $end];
        }
        
        $yearStart = timezone_get(6);
        $scheduleMaster = Db::name('schedule_summary')->where("do_time>={$yearStart['begin']} and do_time<{$yearStart['end']}")->select();
        //获取排产表id
        $sid = [];
        foreach ($scheduleMaster as $key => $value) {
            $sid[] = $value['id'];
        }
        $sids = implode(',', $sid);        
        //获取当前年排产附表数据
        $schedule = Db::name('schedule')->alias('a')->field('a.*,b.do_time')->join('schedule_summary b','a.sid=b.id')
                ->whereIn('sid', $sids)
                ->where('a.finished',1)
                ->select();
        $yearList = $this->getTotalSchedule($yearTimes, $scheduleMaster,$schedule);
        
        $data['工序达成率'] = $gxList;
        $data['本月'] = $list;
        $data['本年'] = $yearList;
        
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '生产计划达成率';
        $this->multi_export($title, $data, $creator, $exceltitle);
    }
    
    /**
     * 获取生产计划完成率的总统计数据
     * @param array $times 各段时间的开始和结束时间
     * @param array $scheduleMaster 排产主表数据
     * @param array $schedule 排产副表数据
     */
    public function getTotalSchedule($times,$scheduleMaster,$schedule)
    {
        $list = [];//本月排产每天统计值
        foreach($times as $k => $v){
            $list[$k]['day'] = $k+1;
            
            //叠加当天的订单总排产量
            $ordernum = 0;
            foreach ($scheduleMaster as $k2 => $v2) {
                if($v2['do_time']>=$v['begin'] && $v2['do_time']<$v['end']){
                    $ordernum = $v2['ordernum']+$ordernum;
                }
            }
            $list[$k]['ordernum'] = $ordernum;
            //叠加当天的排产完成数
            $finish = 0;
            foreach ($schedule as $k3 => $v3) {
                if($v3['do_time']>=$v['begin'] && $v3['do_time']<$v['end']){
                    $finish += 1;
                }
            }
            $unfinish = $ordernum-$finish;
            $list[$k]['finish'] = $finish;
            $list[$k]['unfinish'] = $unfinish;
        }
        return $list;
    }
    
    /**
     * 导出系列颜色
     * @param array $field 订单表头字段
     */
    public function series($field)
    {
        $time = $this->getTime();
               
        //如果订单头存在color字段，则先删除，在添加到前面
        if(isset($field['color'])) unset ($field['color']);
        $series['pname'] = '系列';
        $series['color'] = '颜色';
        $field = array_merge($series,$field);
        $title['系列颜色汇总订单'] = $field;

        $data = Db::name('order')
                ->where("addtime>={$time['begin']} and addtime<={$time['end']}")
                ->order('convert(pname using gbk),convert(color using gbk)')
                ->select();

        $data = $this->mergeField($data);
        $list['系列颜色汇总订单'] = $data;
        
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '系列颜色汇总订单';
        $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        $this->multi_export($title, $list, $creator, $exceltitle,$timeString);
    }
    
    /**
     * 将订单附表的自定义字段合并到订单数组里
     * @param array $data 订单主表数组
     */
    public function mergeField($data)
    {
    	$data=order_attach($data);
        
        return $data;
    }
    
    /**
     * 获取接单与入库数量的总统计时间（表头数据）
     */
    public function getReceriveTime()
    {
        $timeType = input('time');
        $dateList = [];//文字显示
        $timeList = [];//时间戳显示
        //如果不是年和自选区间，则使用日为单位
        if(!in_array($timeType, ['year','selft'])){
            $time = $this->getTime();           
            for ($date=$time['begin'];$date<=$time['end'];$date+=(24*3600)){
                $dateList[] = ltrim(date('m',$date),0).'月'. ltrim(date('d',$date),0).'日';
                $timeList[] = ['begin'=>$date,'end'=>$date+(24*3600),'text'=>ltrim(date('m',$date),0).'月'. ltrim(date('d',$date),0).'日'];
            }
        }else{
            //如果是年,则使用月为单位
            if($timeType == 'year'){
                for($i=1;$i<=12;$i++){
                    $now = time();
                    $begin = mktime(0, 0,0, $i, 1, date('Y', $now));
                    $end = mktime(23, 59, 59, $i, date('t', $now), date('Y', $now))-1;
                    $dateList[] = $i.'月';
                    $timeList[] = ['begin'=>$begin,'end'=>$end,'text'=>$i.'月'];
                }
            }elseif($timeType == 'self'){
                $time = $this->getTime();
                $value = ($time['end']-$time['begin'])/(24*3600);
                $unit = $value>=31?'month':'day';
                //如果自选时间区间大于31则使用月为单位，否则日为单位
                if($unit == 'month'){
                    for($i=date('Y',$time['begin']);$i<=date('Y',$time['end']);$i++){
                        $dateList[] = $i.'月';
                        $begin = mktime(0, 0,0, $i, 1, date('Y', $now));
                        $end = mktime(23, 59, 59, $i, date('t', $now), date('Y', $now))-1;
                        $timeList[] = ['begin'=>$begin,'end'=>$end,'text'=>$i.'月'];
                    }
                }else{
                    for ($date=$time['begin'];$date<=$time['end'];$date+=(24*3600)){
                        $dateList[] = ltrim(date('m',$date),0).'月'. ltrim(date('d',$date),0).'日';
                        $timeList[] = ['begin'=>$date,'end'=>$date+(24*3600),'text'=>ltrim(date('m',$date),0).'月'. ltrim(date('d',$date),0).'日'];
                    }
                }
            }
        }
        return ['text'=>$dateList,'value'=>$timeList];
    }
    
    /**
     * 获取接单与入库数量的总统计数据
     * @param string $pnamefield 物料名称
     */
    public function getReceiveData($pnamefieldString,$timesql,$order,$dateList)
    {
        $pnamefield = explode(',', $pnamefieldString);
        //当组名不等于其它时
        if($pnamefield[0] != '其它'){
            $sql = $timesql." and (pname like '%{$pnamefield[0]}%')";
            if(count($pnamefield)>=2){
                $sql .= " and (pname like '%{$pnamefield[1]}%')";
            }               
        }else{
            $pnameType = ['平开门','推拉门','吊趟门','平开窗','窗纱一体'];
            $sql = $timesql;
            foreach ($pnameType as $key => $value) {
                $sql .= " and (pname not like '%{$value}%')";
            }
        }
        
        $pname = Db::name('order')->where($sql)->group('pname')->column('pname');
        
        $data = [];        
        foreach ($pname as $k => $v) {//物料名称
            $data[$k]['team'] = $pnamefieldString;
            $data[$k]['pname'] = $v;      
            $tempReceiveAll = 0;//汇总接单数量
            $tempIntoAll = 0;//汇总入库数量
            foreach ($dateList['value'] as $k2 => $v2) {//多个时间段
                $tempReceive = 0;//接单数量
                $tempInto = 0;//入库数量
                foreach ($order as $k3 => $v3) {//全部订单数据
                    //如果物料名称相同
                    if($v == $v3['pname']){
                        if($v3['addtime']>=$v2['begin'] && $v3['addtime']<=$v2['end']){
                            $tempReceive += 1;//累加接单数量
                            $tempReceiveAll += 1;
                        }
                        if($v3['intime']>=$v2['begin'] && $v3['intime']<=$v2['end']){
                            $tempInto += 1;//累加入库数量
                            $tempIntoAll += 1;
                        }
                    }
                }
                $data[$k][$v2['text'].'receive'] = $tempReceive;
                $data[$k][$v2['text'].'into'] = $tempInto;
            }
            $data[$k]['汇总receive'] = $tempReceiveAll;
            $data[$k]['汇总into'] = $tempIntoAll;
        }
        //统计汇总数据
        $total = ['team'=>$pnamefieldString,'pname'=>'汇总'];
        $allRr = 0;
        $allIn = 0;
        foreach ($dateList['value'] as $k => $v) {
            $rr = array_sum(array_column($data,$v['text'].'receive'));//将二维数组转成一维数组后进行累加
            $in = array_sum(array_column($data,$v['text'].'into'));
            $total[$v['text'].'receive'] = $rr;
            $total[$v['text'].'into'] = $in;
            $allRr += $rr;
            $allIn += $in;
        }
        $total['汇总receive'] = $allRr;
        $total['汇总into'] = $allIn;
        $data[] = $total;

        return $data;
    }
    
    /**
     * 导出接单与入库数量
     */
    public function receiveProduct($all,$field)
    {
        //<!--物料总统计开始>
        $dateList = $this->getReceriveTime();//处理总统计筛选时间        
        $totalField = ['team'=>'班组','pname'=>'物料名称'];
        foreach ($dateList['text'] as $key => $value) {
            $totalField[$value] = ['is_column'=>1,'explains'=>$value];
        }
        $totalField['汇总'] = ['is_column'=>1,'explains'=>'汇总'];
             
        $time = $this->getTime();
        $timesql = "addtime>={$time['begin']} and addtime<={$time['end']}";        
        $order = Db::name('order')->where($timesql)->select();//所选时间段的所有订单
        $pnameList = ['平开门','推拉门,吊趟门','平开窗,窗纱一体','推拉门','其它'];
        foreach ($pnameList as $k => $v) {
            $title[$v] = $totalField;
            $list[$v] = $this->getReceiveData($v, $timesql, $order,$dateList);
        }
        //<!--物料总统计结束>

      
        $title['接单订单'] = $field;
        $intoFiled = array_merge(['intime'=>'订单完成时间'],$field);
        $title['入库订单'] = $intoFiled;
        $receiveData =  Db::name('order')->alias('a')
                ->where("addtime>={$time['begin']} and addtime<={$time['end']}")
                ->select();
        
        $list['接单订单'] = $this->mergeField($receiveData);
        //入库订单
        $productData = Db::name('order')->where("intime>={$time['begin']} and intime<{$time['end']}")->select();
        $productData = $this->mergeField($productData);
        //格式化入库时间
        foreach ($productData as $key => $value) {
            $productData[$key]['intime'] = $value['intime']!=0?date('Y-m-d',$value['intime']):'';
        }
        $list['入库订单'] = $productData;
        
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '接单与入库订单';
        $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        $this->multi_export($title, $list, $creator, $exceltitle,0,[0,1,2,3,4,5,6]);
    }
    
    
    /**
     * 导出各工序产值㎡数
     * @param array $field 订单表头字段
     */
    public function dayProduct($field,$where="")
    {
        $time = $this->getTime();            
        
        $timesql = "a.endtime>={$time['begin']} and a.endtime<{$time['end']}";
        if (!empty($where)){
            $timesql = $where;
        }
    	//所选时间段所报工的工序名称
        $gxName = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')
                ->where($timesql)
                ->group('b.dname')
                ->column('b.dname');
        
        $title = [];
        $list = [];
        foreach ($gxName as $k => $v) {
            
            $order = Db::name('flow_check')->alias('a')->field('c.*')
                ->join('gx_list b','a.orstatus=b.id')
                ->join('order c','a.orderid=c.id')
                ->where('b.dname',$v)
                ->where($timesql)
                ->group('c.id')
                ->select();
            $v = str_replace('/', '', $v);//创建sheet时名称不能带/
            $title[$v] = $field;
            $order = $this->mergeField($order);
                               
            $list[$v] = $order;
            
        }
        
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '工序产能分析';
        $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        $this->multi_export($title, $list, $creator, $exceltitle,$timeString);
    }
    
    /**
     * 获取今日,本周,本月,今年及自定义时间
     */
    public function getTime()
    {
        $timezone=input("time");// day week month year self(自选区间)
    	$timezone=empty($timezone)?'day':$timezone;
    	switch($timezone){
    		case 'day':
    			$time=timezone_get(1);
    			break;
    		case 'week':
    			$time=timezone_get(2);
    			break;
    		case 'month':
    			$time=timezone_get(3);
    	
    			break;
    		case 'year':
    			$time=timezone_get(6);
    			break;
    		case 'self':
    			$zone=trim(input("zone"));
    			if(!empty($zone)){
    				$zone=str_replace(" - ","",$zone);
    				$begin=substr($zone, 0,10);
    				$end=substr($zone,10,10);
    				$time['begin']=ymktime($begin);
    				$time['end']=ymktime($end)+24*60*60-1;
    			}
    		break;
    	}
        return $time;
    }
    
    /**
     * 获取本周，本月，本年，本日 所对应的上周，上月。。。时间
     * @return array
     */
    public function getLasttime()
    {
        $timeType = input('time');            
        //所选时间段所对应的上一段时间
        switch ($timeType) {            
            case 'day':
                $lastTime = timezone_get(7);
                break;
            case 'week':
                $lastTime = timezone_get(8);
                break;
            case 'month':
                $lastTime = timezone_get(9);
                break;
            case 'year':
                $lastTime = timezone_get(10);
                break;
                case 'self':
                    $zone=trim(input("zone"));
                    if(!empty($zone)){
                    				$zone=str_replace(" - ","",$zone);
                    				$begin=substr($zone, 0,10);
                    				$end=substr($zone,10,10);
                    				$lastTime['begin']=ymktime($begin);
                    				$lastTime['end']=ymktime($end)+24*60*60-1;
                    }
                    break;
        }
        return $lastTime;
    }
    
    /**
     * 工序状态分析导出
     * @param array $field 订单表头字段
     * @param array $flowData 工艺线总统计数据
     */
    public function procedure($field,$flowData,$all)
    {
        $time = $this->getTime(); 

        $timesql="";
    	if($time['begin']>0&&$time['end']>0){
    		$timesql=" and a.endtime>={$time['begin']} and a.endtime<={$time['end']} ";
    	}
        
        $title['工序超时订单'] = $field;
        $title['完成率分析'] = $field;
        $title['工序异常订单'] = array_merge(['exception'=>'异常情况'],$field);
        $overtime = $this->getOvertimeData($timesql); //超时工序的订单数据

        $compelte = $this->getComplete($timesql); //完成率订单数据  
       	$exception = $this->getExceptionData($timesql);//异常订单数据

        //工艺线统计
        $ftitle['总工艺线概况'] = ['name'=>'工艺线名称','order_count' => '订单量','time' => '总用时/小时'];
        $ffield = ['actual_time' => '实际用时/天','require_time' => '要求时间/天']; //为订单头添加两个字段
        $ffield = array_merge($ffield,$field);
        //工艺线附表
        $sheet = [];
        foreach ($flowData as $key => $value) {
            $sheet[$value['name']] = $ffield;
        }
        $ftitle = array_merge($ftitle,$sheet);
        $title = array_merge($title,$ftitle);
        
        $totalData['总工艺线概况'] = $flowData;
        $data = $this->getFlow($flowData,$all['orderid'],$all['day']); 
        
        $list = array_merge($totalData,$data);
        
        $exportData['工序超时订单'] = $overtime;
        $exportData['完成率分析'] = $compelte;   
        $exportData['工序异常订单'] = $exception;  
        
        $list = array_merge($exportData,$list);
               
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '工序状态分析';
        $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        //$this->multi_export($title, $list, $creator, $exceltitle,$timeString);
        
        export_csv_zip($list,$title,array('title'=>$exceltitle,'headTitle'=>$timeString));
        exit();
    }

    /**
     * 工序状态分析--工艺线用时数据
     * @param array $flowdata 工艺线总统计数据
     * @param array $orderid 每条工艺线下的订单id
     * @param array $day 每条工艺线规定的完成时间：单位 天
     */
    public function getFlow($flowData,$orderid,$day)
    {
        $time = $this->getTime();
        //键为工序名的所有订单
        $order = [];
        $orderids = [];        
        foreach($flowData as $k => $v){
            $orderId = isset($orderid[$v['name']])?implode(',', $orderid[$v['name']]):0;
            if($orderId !=0){
                $orderids[] = $orderId;
            }           
            $nn = Db::name('order')
                    ->whereIn('id', $orderId)
                    ->select();
            $order[$v['name']] = $this->mergeField($nn);
        }
        
        $flowCheck = Db::name('flow_check')->field('orderid,starttime,endtime')->whereIn('orderid', implode(',', $orderids))->group('orderid')->select();       
        //将订单id作为键,值为第一次报工时间
        $check = [];
        foreach ($flowCheck as $key => $value) {     
            //如果第一次报工开始时间不为空则使用 开始时间 否则 结束时间
            $check[$value['orderid']] = $value['starttime']>0?$value['starttime']:$value['endtime'];
        }

        //添加实际用时,要求时间字段
        $list = [];
        foreach($order as $k => $v){
            foreach ($v as $k2 => $v2) {
                $actuallyTime = ($v2['intime']-$check[$v2['id']])/(24*3600);
                $v[$k2]['actual_time'] = $actuallyTime>0?round($actuallyTime,2):0;
                $v[$k2]['require_time'] = $day[$k];
            }
            $list[$k] = $v;
        }
        
        return $list;
    }
    
    /**
     * 工序状态分析--获取异常订单数据
     */
     public function getExceptionData($timesql)
    {
        //获取异常的工序
        $list = Db::name("flow_check")
    	->alias("a")
    	->join("gx_list b","b.id=a.orstatus","LEFT")
    	->field("a.orderid,b.dname,a.stext")
    	->where("a.state='1' $timesql")
    	->select();  
        $gxType = [];//异常工序名称及所用工序的订单id
        $exception = [];//异常情况
        foreach($list as $k => $v){
            $gxType[$v['dname']][] = $v['orderid'];
            if($v['stext']!=''){
                $exception[$v['orderid']][] = $v['stext'];
            }
        }
        //按数量降序排序的中间数组
        $sort = [];
        foreach($gxType as $k => $v){
            $sort[$k] = count($v);
        }
        arsort($sort);
        
        $overtime = [];
        foreach($sort as $k => $v){
            $overtime[] = ['merge_title' => $k];//合并表格显示的文字
            $orderIds = implode(',', $gxType[$k]);
            $data = Db::name('order')->whereIn('id', $orderIds)->select();
            $data=$this->mergeField($data);
            foreach ($data as $k2 => $v2) {
                $exceptArray = isset($exception[$v2['id']])?$exception[$v2['id']]:[];
                $data[$k2]['exception'] = implode('/', $exceptArray);
            }
            $overtime = array_merge($overtime,$data);
        }
        $handle = $overtime;
        return $handle;
    }
    
    /**
     * 工序状态分析--获取完成率订单数据
     */
    public function getComplete($timesql)
    {
        //超时报工
        $overtime = Db::name("flow_check")->alias('a')->field('b.*')
                ->join('order b','a.orderid=b.id')
                ->group("a.orderid")
                ->where("a.status='1' $timesql")            
                ->select();
        //准时报工
        $time = Db::name("flow_check")->alias('a')->field('b.*')
                ->join('order b','a.orderid=b.id')
                ->where("a.status=0 $timesql")
                ->group("a.orderid")
                ->select();

        $time=$this->mergeField($time);
        $overtime=$this->mergeField($overtime);
        $data[] = ['uname' => '准时报工'];
        $data = array_merge($data,$time);
        $title[] = ['uname' => '超时报工'];
        $overdata = array_merge($title,$overtime);
        $data = array_merge($data,$overdata);
        $handle = $data;
        return $handle;
    }
    
    /**
     * 工序状态分析--获取超时工序订单数据
     */
    public function getOvertimeData($timesql)
    {
        //获取超时的工序
        $list = Db::name("flow_check")
    	->alias("a")
    	->join("gx_list b","b.id=a.orstatus","LEFT")
    	->field("a.orderid,b.dname")
    	->where("a.status='1' $timesql")
    	->select();    
        $gxType = [];//超时工序名称及所用工序的订单id
        foreach($list as $k => $v){
            $gxType[$v['dname']][] = $v['orderid'];
        }
        //按数量降序排序的中间数组
        $sort = [];
        foreach($gxType as $k => $v){
            $sort[$k] = count($v);
        }
        arsort($sort);
        
        $overtime = [];
        foreach($sort as $k => $v){
            $overtime[] = ['uname' => $k];//合并表格显示的文字
            $orderIds = implode(',', $gxType[$k]);
            $data = Db::name('order')->whereIn('id', $orderIds)->select();
            $data=$this->mergeField($data);
            $overtime = array_merge($overtime,$data);
        }
        $handle = $overtime;
        return $handle;
    }
    
    /**
     * 员工产能排名导出
     * @param array $allField 员工总的统计字段
     * @param array $allData 员工总的数据，也是员工数据
     * @param array $field 订单表头字段
     */
    public function ranking($field)
    {           
        $time = $this->getTime();
        
        $timesql = '';
        if($time['begin']>0 && $time['end']>0){
            $timesql=" and b.endtime>={$time['begin']} and b.endtime<={$time['end']} ";
        }
        
        //<--员工总统计开始-->
        
        //先获取用户有绑定的班组和对应的工序
        $team = Db::name('login')->alias('a')->field('a.id,a.uname,b.team_name,c.ngx_id')
                ->join('team b','a.tid=b.id')
                ->join('team_gx c','a.tid=c.tid')
                ->where("a.user_role=2 and a.tid!=0")
                ->select();
        $gxdata = @include_once APP_DATA.'gx_list.php';
        
        $i = 0;
        $all = [];
        foreach ($team as $k => $v) {
            $gxid = unserialize($v['ngx_id']);
            foreach ($gxid as $k2 => $v2) {
                $tempGxid = $v2[0];
                $all[$i]['id'] = $v['id'];
                $all[$i]['uname'] = $v['uname'];
                $all[$i]['team_name'] = $v['team_name'];
                $all[$i]['gxid'] = $tempGxid;
                $all[$i]['gx_name'] = $gxdata[$tempGxid]['dname'];
                $i++;
            }
           
        }

        //计算订单数量和面积
        foreach ($all as $key => $value) {
            $orderid = Db::name('flow_check')->where("uid=$value[id] and orstatus=$value[gxid]")->group('orderid')->column('orderid');
            $count = count($orderid);
            $alist = Db::name('order')->whereIn('id',$orderid)->column('area');
            $uarea=0;
            if($alist){
            	foreach($alist as $val){
            		$uarea+=floatval($val);
            	}
            }
            $all[$key]['count'] = $count;
            $all[$key]['area'] = round($uarea,2);
        }
        //排序
        $teamName = [];
        $num = [];
        $area = [];
        $gxname = [];
        foreach ($all as $key => $value) {
            $num[] = $value['count'];
            $teamName[] = $value['team_name'];
            $gxname[] = $value['gx_name'];
            $area[] = $value['area'];
        }
        array_multisort($teamName,SORT_DESC,$gxname,SORT_DESC,$num,SORT_DESC,$area,SORT_DESC,$all);
        //写入排名
        $i = 1;
        foreach ($all as $key => $value) {
            $all[$key]['ranking'] = $i;
            if(isset($all[$key+1]['gxid'])&&$value['gxid']!=$all[$key+1]['gxid']){
                $i = 0;
            }
            $i++;
        }
        //<--员工总统计结束-->
        
        //获取员工产量排名,只用于排序,所得结果不准确
        $user = Db::query("SELECT * ,(select count(*)  from bg_flow_check b where b.uid=a.id $timesql) as count FROM `bg_login` a where user_role='2' order by count desc");
        //所选时间段的生产订单
        $order = Db::name('order')->alias('a')->field('a.*,b.uid as uuid')
                ->join('flow_check b','a.id=b.orderid')
                ->where("1=1 $timesql")
                ->select(); 

        $order = $this->mergeField($order);
        
        //整理订单数据
        $orders = [];
        foreach ($order as $k => $v) {
            $orders[$v['uuid']][] = $v;
        }
        
        $title['产能排名'] = ['team_name'=>'班组','gx_name'=>'工序','uname'=>'员工姓名','count'=>'订单数量','area'=>'面积','ranking'=>'排名'];//表头字段
        $list['产能排名'] = $all;  
        $i=1;//sheet名拼接数字,避免数组键重复
        //foreach ($user as $k => $v) {
           // $title[$v['uname'].$i] = $field;
         //   $list[$v['uname'].$i] = (is_array($orders[$v['id']]))?$orders[$v['id']]:[];
         //   $i +=1;
        //}
        
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '员工产能排名';
        if($time['begin']>0 && $time['end']>0){
            $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        }else{
            $timeString = 0;
        }
        
        $this->multi_export($title, $list, $creator, $exceltitle,$timeString);
    }

    /**
     * 工序产能分析导出
     * @param array $field 订单表头字段
     */
    public function producting($field)
    {
        $map = ['day'=>'天','week'=>'周','month'=>'月','year'=>'年'];
        $unit = $map[input('time')];
        $time = $this->getTime();        
        $lastTime = $this->getLasttime();        
        
        $timesql = "a.endtime>={$time['begin']} and a.endtime<{$time['end']}";
        $lastTimesql = "a.endtime>={$lastTime['begin']} and a.endtime<{$lastTime['end']}";
    	//所选时间段所报工的工序名称
        $gxName = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')->where($timesql)
                ->group('dname')
                ->column('dname');

        $title = [];
        $list = [];
        $listTitle = ['merge_title'=>'本'.$unit];
        $listLastTitle = ['merge_title'=>'上'.$unit];
        foreach ($gxName as $k => $v) {
            
            //所选时间段与对应的上段时间数据
            $order = Db::name('flow_check')->alias('a')->field('c.*,a.endtime as fendtime')
                ->join('gx_list b','a.orstatus=b.id')
                ->join('order c','a.orderid=c.id')
                ->where('b.dname',$v)
                ->where(function($query) use ($timesql,$lastTimesql){
                    $query->where($timesql)->whereOr($lastTimesql);
                })
                ->group('c.id')
                ->select();
            $v = str_replace('/', '', $v);//创建sheet时名称不能带/
            $title[$v] = $field;
            $order = $this->mergeField($order);
            
            //处理本段时间和上段数据
            $current = [];
            $last = [];
            $current[] = $listTitle;
            $last[] = $listLastTitle;
            foreach ($order as $k2 => $v2) {
                if($v2['fendtime']>=$time['begin'] && $v2['fendtime']<$time['end']){
                    $current[] = $v2;
                }else{
                    $last[] = $v2;
                }
            }
            //将本段和上段时间的订单合并
            $data = array_merge($current,$last);                     
            $list[$v] = $data;
            
        }
       
        $site_cache = @include (APP_CACHE_DIR . 'site_cache.php');
        $creator = $site_cache[PRO_DOMAIN]['sitename']; //excel作者用站点名称
        $exceltitle = '工序产能分析';
        $timeString = date('Y/m/d',$time['begin']).'--'.date('Y/m/d',$time['end']);
        $this->multi_export($title, $list, $creator, $exceltitle,$timeString);
    }

    /**
     * 导出多个工作表,并存储键和值的关系
     * @param array $titles 多个工作表标题的集合,array(工作表名=>一维数组) 的二维数组  键为sheet名称,值位表头名称
     * @param array $lists 多个工作表的数据的集合,array(工作表名=>二维数组) 的三维数组  键为sheet名称
     * @param string $creator 是excel文档创建者
     * @param string $exceltitle 导出的excel文件名称
     * @param string $timestring 是否显示第一行为时间段
     * @param string $mergeTitle 是否需要跨行合并和同时合并列
     */
    public function multi_export($titles, $lists, $creator, $exceltitle, $timestring=0,$mergeTitle=[])
    {

        $line_title_arrays = $fields = array();

        //存储英文字段和中文字段 =表头和数据字段
        foreach ($titles as $tab => $title) {
            $line_title_array = $field = array();

            foreach ($title as $key => $value) {
                if(count($mergeTitle) > 0 && isset($value['is_column'])){
                    $field[] = $key.'receive';
                    $field[] = $key.'into';
                }else{
                    $field [] = $key;
                }
                
                $line_title_array [] = $value;
            }
            $line_title_arrays[$tab] = $line_title_array;
            $fields[$tab] = $field;
        }
        
        $doc = array('creator' => $creator, 'title' => $exceltitle,
            'subject' => $exceltitle, 'description' => $exceltitle,
            'keywords' => $exceltitle, 'category' => $exceltitle
        );
        //存储各个工作表数据
        $this->export_excel_multiple($lists, $fields, $line_title_arrays, $doc,$timestring,$mergeTitle);
    }

    //导出多个工作表数据
    //$tabList 是array(工作表名=>二维数组) 的维数组
    //$doc 要都出的文件基本信息数组
    //$field是要导出的字段名称的数组 array(工作表名=>二维数组)
    //$line_title是导出的excel的第一行标题数组，不是数据 array(工作表名=>二维数组)
    //$ex 要导出的excel 文件的版本默认是2007,可以使用2003版本的
    //$jumpurl 是默认跳转的页面
    //$un_need 不需要导出的字段名
    //$field 字段数目应该要与$line_title的长度是一样的
    public function export_excel_multiple($tabList, $field, $line_title, $doc, $time=0,$merge_title=[],$un_need = array(), $ex = '2007')
    {

        if ($tabList === false || count($tabList) <= 0) {
            return false;
        }

        //最多导出60个字段，可以继续增加
        $Excel_letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
            'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF',
            'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP',
            'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ',
            'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT',
            'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD',
            'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN',
            );

        $objExcel = new \PHPExcel ();

        //设置导出文档的文件基本属性
        $objExcel->getProperties()->setCreator($doc ['creator']);
        $objExcel->getProperties()->setLastModifiedBy($doc ['creator']);
        $objExcel->getProperties()->setTitle($doc ['title']);
        $objExcel->getProperties()->setSubject($doc ['subject']);
        $objExcel->getProperties()->setDescription($doc ['description']);
        $objExcel->getProperties()->setKeywords($doc ['keywords']);
        $objExcel->getProperties()->setCategory($doc ['category']);

        $tabIndex = 0;
        foreach ($tabList as $title => $list) {

            if ($tabIndex > 0) {
                $objExcel->createSheet();
            }

            $objExcel->setActiveSheetIndex($tabIndex); //第一个工作表
            $first = 1;
            //如果需要设置第一行为时间段
            if($time!=0){                
                $lastColumn = count($line_title[$title]);
                $objExcel->getActiveSheet()->mergeCells("A1:{$Excel_letter[$lastColumn - 1]}1");
                $objExcel->getActiveSheet()->setCellValue("A1", $time);
                //设置水平居中
                $objExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $first = 2;
            }
            //设置表头--即Excel的第一行数据
            $mergeColumn = 0;//需要合并列的索引
            $gxText = ['接单','入库'];
            foreach ($line_title[$title] as $key => $value) {
                
                //如果需要合并
                if($tabIndex<count($merge_title) && count($merge_title)>0){
                    if (isset($value['is_column']) && $value['is_column'] == 1) {
                        //合并列
                        $mergeColumn = $mergeColumn == 0 ? $key : $mergeColumn;
                        $start = $Excel_letter[$mergeColumn] . '1'; //通过获取列index获取它的列名
                        $next = $Excel_letter[$mergeColumn + 1] . '1'; //获取下一个列名
					
                        $objExcel->getActiveSheet()->mergeCells("{$start}:{$next}"); // 合并
                        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$mergeColumn] . "1", $value['explains']); //$key 格式是:A1 $value是字段的中文名称
                        $objExcel->getActiveSheet()->getStyle($Excel_letter[$mergeColumn] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        //写入接单和入库文字
                        $gxTextIndex = $gxTextIndex > 1 ? 0 : $gxTextIndex;
                        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$mergeColumn] . "2", $gxText[0]); 
                        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$mergeColumn + 1] . "2", $gxText[1]); 
                        $objExcel->getActiveSheet()->getStyle($Excel_letter[$mergeColumn] . "2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objExcel->getActiveSheet()->getStyle($Excel_letter[$mergeColumn + 1] . "2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $mergeColumn += 2;
                    } else {

                        //先设置水平和垂直居中，再合并行
                        $start = $Excel_letter[$key] . '1';
                        $next = $Excel_letter[$key] . '2'; //获取下一个列名
						
                        $objExcel->getActiveSheet()->mergeCells("{$start}:{$next}"); // 合并
                        $objExcel->getActiveSheet()->getStyle("$start")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objExcel->getActiveSheet()->getStyle("$start")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
                        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$key] . "1", $value); //$key 格式是:A1 $value是字段的中文名称
                    }
                    $first = 2;
                }else{
                    
                    $objExcel->getActiveSheet()->setCellValue($Excel_letter [$key] . $first, $value); //$key 格式是:A1 $value是字段的中文名称
                }
            }
            $start_line = $first+1; //从第几行开始写入数据，一般是从第二行开始

            
            /* ----------写入内容------------- */
            foreach ($list as $key => $value) {
                $line = $start_line;
                for ($k = 0; $k < count($field[$title]); $k ++) {
                    if (!in_array($field[$title] [$k], $un_need)) {
                        //不输出指定的字符串
                        $objExcel->getActiveSheet()->getStyle($Excel_letter [$k])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objExcel->getActiveSheet()->setCellValue($Excel_letter [$k] . $line, $value [$field[$title] [$k]]);
                        $objExcel->getActiveSheet()->getStyle($Excel_letter [$k] . $line)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }
                 
                    //如果需要合并单元格
                    if (isset($value['merge_title'])) {
                        $lastColumn = count($field[$title]);
                        $objExcel->getActiveSheet()->mergeCells("A{$line}:{$Excel_letter[$lastColumn - 1]}{$line}");
                        $objExcel->getActiveSheet()->setCellValue("A{$line}", $value['merge_title']);
                        //设置水平居中
                        $objExcel->getActiveSheet()->getStyle("A{$line}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }
                }
                $start_line ++; //移动到下一行
            }

            // 高置列的宽度  $Excel_letter[$i] 代表的该列的名称 例如 A B C ...
            $max_column = $mergeColumn!=0?$mergeColumn:count($line_title[$title]);
            for ($i = 0; $i < $max_column; $i ++) {

                $objExcel->getActiveSheet()->getColumnDimension($Excel_letter [$i])->setWidth(20); //默认宽度是15
            }

            $objExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&BPersonal cash register&RPrinted on &D');
            $objExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objExcel->getProperties()->getTitle() . '&RPage &P of &N');

            // 设置页方向和规模
            $objExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
            $objExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $objExcel->setActiveSheetIndex($tabIndex);
            $objExcel->getActiveSheet()->setTitle("$title");

            $tabIndex++;
        }

        $timestamp = "_" . date("YmdHis", time());
        if ($ex == '2007') { //导出excel2007文档
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
            
        } else { //导出excel2003文档
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
            $objWriter->save('php://output');
            
        }
    }

}
