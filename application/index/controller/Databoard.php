<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Facade\Env;
use think\facade\Request;

class Databoard extends Controller
{
    private $monitor;
    private $teamid;//班组ID
    private $teamnum;//显示班组数
    
    private $all_did;//doclass的id数组，记录某个小工序被包含在那些订单的gid内
    private $all_order;//记录有某个小工序的订单ID数组
    private $sql;
    private $field;//订单返回字段
    private $check_field;//报工记录返回字段
    private $orderby;//订单查询排序
    private $parent;//上级工序数组
    private $gx_checks;//工序已报工记录
    private $parent_checks;//上级工序报工
    private $parent_finish_checks;//上级都完成的报工记录
    private $parent_num;//记录每个订单当前工序的上级数量
    protected $style;//当前看板的style值
	
    public function initialize(){
    	//判断是否有看板功能
    	$site_cache=@include (APP_CACHE_DIR.'site_cache.php');
    	if(isset($site_cache[PRO_DOMAIN])){
    		if($site_cache[PRO_DOMAIN]['databoard']!=1){
    			exit("该项目没开启数据看板功能");
    		}
    	}else{
    		exit("该项目没有配置域名选项");
    	}
    	
        parent::initialize();
    }
    
    //数据看板
    public function index(){
    	
    	$no=input("id")?input("id"):'A';
    	$no=strtoupper(ctrim($no));
    	$this->assign("no",$no);
    	
    	//根据屏幕显示组
    	$id=$this->monitor;
    	$monitor=Db::name("monitor")->where("code='$no'")->find();
    	$teamid=$monitor['teamid'];
    	if($teamid==''){
    		$this->assign("error","请在屏幕设置处绑定班组");
    	}
        if(!$monitor){
            exit('屏幕代码错误');
        }
        
    	//查找对应班组
    	$team_list=Db::name("team")->where("id in ($teamid)")->order("id asc")->select();
    	$this->assign("team_list",$team_list);
    	
    	//判断显示几个小组
    	$length=count($team_list);
    	$this->assign("groupNumber",$length);
    	
    	
    	return $this->fetch();
    }
    
    //单独一个看板-iframe
    public function team_view(){
    	
    	$id=input("id",0,'intval');//班组ID
    	$teamnum=input("team",0,'intval');//显示班组数，1-3
    	$teamnum=$teamnum?$teamnum:1;
    	
    	$this->teamnum=$teamnum;
    	
    	if(empty($id)){
    		$this->assign("error","缺少班组ID参数");
    	}
    	
    	//换成
    	$cache=@include_once APP_DATA.'team_list.php';
    	
    	//查询班组
    	$team=Db::name("team")->where("id='$id'")->find();
    	$team['team_link']=$this->get_team_link($id, $cache);
    	
    	$this->assign("team",$team);
    	
    	//首次查询
    	$this->teamid=$id;
    	$data=$this->ajax_list();
    	    	    	
    	$no=input("no")?input("no"):'A';
    	$monitor=Db::name("monitor")->where("code='$no'")->find();
        $template_map = ['1' => 'one','3' => 'one','4'=> 'all','5'=>'one','6'=>'exception','7'=>'multiples','8'=>'one2','9'=>'auto_ps',
            '10'=>'fixed_flow','11'=>'fixed_flow','12'=>'fixed_flow'];//样式与模板对应关系
        $template = $template_map[$monitor['style']];
        
    	$style=$monitor['style']>0?$monitor['style']:1;
    	$monitor_style=@include APP_CACHE_DIR.'monitor.php'; //读取屏幕样式缓存--默认1
    	$style=$monitor[$style]['css'];           
    	$this->assign("style",$style);
    	
    	$this->assign("data",$data);
    	$this->assign("teamid",$this->teamid);
    	$this->assign("teamnum",$this->teamnum);
    	$this->assign('no',$no);
    	return $this->fetch($template);
    }
    
    //获取班组二级
    //$tid是班组的ID,$cache 是班组的缓存
    private function get_team_link($tid,$cache){
    	$str=array();
    	if(isset($cache[$tid])){
    		$str[]=$cache[$tid]['team_name'];
    		if($cache[$tid]['pid']>0){
    			$pid=$cache[$tid]['pid'];
    			$str[]=$cache[$pid]['team_name'];
    		}
    	}
    	$str=array_reverse($str);
    	return implode(" - ",$str);
    }
    
    /**
     * 获取数据
     */
    public function ajax_list(){
    	
    	$id=input("id",0,'intval');//班组ID
        
    	//查询是否有子班组
        $teamList = Db::name('team')->where('pid',$id)->column('id');
        //如果有子班组，则轮流使用子班组id
        $childNumber = input('child_number',0,'intval');//子班组id索引
        if(count($teamList)>0){                       
            if($childNumber> (count($teamList)-1)){
                $childNumber = 0;
            }
            $id = $teamList[$childNumber];
            $childNumber = $childNumber+1;
        }
        //班组名称
        $teamName = Db::name('team')->where('id',$id)->find();
        
    	if(empty($id)){
    		$id=$this->teamid;
    	}

    	$teamnum=input("team",0,'intval');//显示班组数，1-3
    	if(empty($teamnum)){
    		$teamnum=$this->teamnum;
    	}
    	
    	//昨天日期@hj 2019-02-29 改加上昨天未完成
    	$yestoday=timezone_get(7);//昨天

    	$today=timezone_get(1);//今天
    	
    	$tommorrow=array();		//明天
    	$tommorrow['begin']=$today['end']+1;
    	$tommorrow['end']=$today['end']+24*60*60;
    	
    	$dftommorrow=array();		//后天
    	$dftommorrow['begin']=$tommorrow['end']+1;
    	$dftommorrow['end']=$tommorrow['end']+24*60*60;
    	
    	//查询三天排产数@hj 2020/03/24修改
    	$today_do=Db::name("schedule")->where("do_time<={$today['end']} and tid='$id' and finished!='1'")->count();
    	$tommorrow_do=Db::name("schedule")->where("do_time>={$tommorrow['begin']} and do_time<={$tommorrow['end']}  and tid='$id'")->count();
    	$dftommorrow_do=Db::name("schedule")->where("do_time>={$dftommorrow['begin']} and do_time<={$dftommorrow['end']} and tid='$id'")->count();
    	
    	//查询当天完成量
    	$today_finished=Db::name("schedule")
    					->where("finished='1' and finished_time>={$today['begin']} and finished_time<={$today['end']} and tid='$id'")
    					->count();
    	//当天未完成量
    	$today_unfinish=$today_do-$today_finished;
    	$today_unfinish=$today_unfinish>0?$today_unfinish:0;
    	$finish_rate=0;
    	if($today_do>0){
    		$finish_rate=round($today_finished/$today_do,2)*100;
    	}
		
    	//当天未完成订单列表@hj 2019-02-29 改加上昨天和以前未完成
    	$today_unlist=Db::name("schedule")->field("ordernum,urgent,do_uname,finished_time,do_time")->order("urgent desc")
    				->where("do_time<={$today['end']} and finished!='1' and tid='$id'")
    				->select();
    	
    	//当天完成订单列表
    	$today_finlist=Db::name("schedule")->field("ordernum,urgent,do_uname,finished_time")
    				->order("urgent desc")
    				->where("finished='1' and finished_time>={$today['begin']} and finished_time<={$today['end']} and tid='$id'")
    				->select();
    	foreach($today_finlist as $key=>$value){
    		$today_finlist[$key]['finished_time']=date('H:i',$value['finished_time']);
    	}
    	
    	//只显示一屏-需要计算明后两天完成率
    	$tomorrow_rate=$dftomorrow_rate=0;
    	if($teamnum==1){
    		$tommorrow_finished=Db::name("schedule")->where("do_time>={$tommorrow['begin']} and do_time<={$tommorrow['end']} and finished='1'  and tid='$id'")->count();
    		$dftommorrow_finished=Db::name("schedule")->where("do_time>={$dftommorrow['begin']} and do_time<={$dftommorrow['end']} and finished='1'  and tid='$id'")->count();
    		
    		if($tommorrow_do>0){
    			$tomorrow_rate=round($tommorrow_finished/$tommorrow_do,2)*100;
    		}
    		
    		if($dftommorrow_do>0){
    			$dftomorrow_rate=round($dftommorrow_finished/$dftommorrow_do,2)*100;
    		}
    	}
    	
    	//分解是否昨天的单@hj 2019-02-29 改加上昨天未完成
    	if($today_unlist!==false&&count($today_unlist)){
    		foreach($today_unlist as $key=>$value){
    			$today_unlist[$key]['isyes']='0';
    			if($value['do_time']<$today['begin']){
    				$today_unlist[$key]['isyes']=1;
    			}
    		}
    	}
    	
    	$return=array();
    	$return['today']=array(
                            'date'=>date('Y-m-d',$today['begin']),'total'=>$today_do,
                            'finish'=>$today_finished,'unfinish'=>$today_unfinish,
                            'finish_rate'=>round($finish_rate,2),'unlist'=>$today_unlist,'finlist'=>$today_finlist
                            );//今天的数据，包含订单列表
    	
    	$return['tommorrow']=array('date'=>date('Y-m-d',$tommorrow['begin']),'total'=>$tommorrow_do,'finish_rate'=>$tomorrow_rate);
    	$return['dftommorrow']=array('date'=>date('Y-m-d',$dftommorrow['begin']),'total'=>$dftommorrow_do,'finish_rate'=>$dftomorrow_rate);
    	$return['child_number'] = $childNumber;
    	$return['id'] = $id;
        $return['team_name'] = isset($teamName['team_name'])?$teamName['team_name']:'';
    	return $return;
    }
    
    /**
     * 排产屏模板(用于跳转)
     */
    public function one()
    {
        $id=input("id",0,'intval');//班组ID
    	$teamnum=input("team",0,'intval');//显示班组数，1-3
    	$teamnum=$teamnum?$teamnum:1;
    	
    	$this->teamnum=$teamnum;
    	
    	if(empty($id)){
    		$this->assign("error","缺少班组ID参数");
    	}
    	
    	//换成
    	$cache=@include_once APP_DATA.'team_list.php';
    	
    	//查询班组
    	$team=Db::name("team")->where("id='$id'")->find();
    	$team['team_link']=$this->get_team_link($id, $cache);
    	
    	$this->assign("team",$team);
    	
    	//首次查询
    	$this->teamid=$id;
    	$data=$this->ajax_list();    	    	    	        
        
    	$monitor_style=@include APP_CACHE_DIR.'monitor.php'; //读取屏幕样式缓存--默认1
    	$style=$monitor[3]['css'];           
    	$this->assign("style",$style);
    	
    	$this->assign("data",$data);
    	$this->assign("teamid",$this->teamid);
    	$this->assign("teamnum",$this->teamnum);
    	return $this->fetch();
    }
    
    /**
     * 异常屏模板(用于跳转)
     */
    public function exceptionTemplate()
    {
        $this->assign('no',input('no/s'));
        $this->assign('style','total.css');
        return $this->fetch('exception');
    }
    
    /**
     * 排产屏+异常屏(互相跳转)
     */
    public function multiples()
    {
        $no = input('no');
        $page = input('page',0);
        
        $monitor = Db::name('monitor')->where('code',$no)->find();        
        $teamId_list = $monitor['teamid_list']!=''?explode(',', $monitor['teamid_list']):[];
        $data = $teamId_list;
        $data[] = 'exception';       
        if($page > count($data)-1){
            $page = 0;
        }
        $teamId = $data[$page];
        $page = $page+1;
        
         
        return ['team_id'=>$teamId,'page'=>$page];
    }
    
    /**
     * 异常屏数据
     */
    public function exception()
    {
        //上月总异常
        $lastMonthTime = timezone_get(9);
        $lastSql = "state=1 and error_time>={$lastMonthTime['begin']} and error_time<{$lastMonthTime['end']}";
        $lastMonthCount = Db::name('flow_check')->where($lastSql)->count();
        //本月总异常
        $currentTime = timezone_get(3);
        $currentSql = "state=1 and error_time>={$currentTime['begin']} and error_time<{$currentTime['end']}";
        $currentCount =  Db::name('flow_check')->where($currentSql)->count();
        //本年异常数
        $yearTime = timezone_get(6);
        $yearSql = "state=1 and error_time>={$yearTime['begin']} and error_time<{$yearTime['end']}";
        $yearCount = Db::name('flow_check')->where($yearSql)->count();
        //未处理异常数
        $noHandleSql = "state=1 and isback=0";
        $noHandle = Db::name('flow_check')->where($noHandleSql)->count();
        //获取屏幕代码
        $code=input('no');
        //自定义显示字段
        $monitor = Db::name('monitor')->where('code',$code)->find();
        !empty($monitor['display_field'])?$field=$monitor['display_field']:$field='produce_no';
        //列表数据
        $list = Db::name('flow_check')->alias('a')
                ->field("b.unique_sn,b.uname,FROM_UNIXTIME(a.error_time,'%Y-%m-%d') as error_time,c.dname,a.stext,d.value,e.handle_time,b.pname,b.series")
                ->join("order b",'a.orderid=b.id')
                ->join('gx_list c','a.orstatus=c.id')
                ->join("order_attach d","a.orderid=d.orderid")
                ->join("check_back e","a.id=e.fid","LEFT")
                ->where("a.state=1 and a.isback<>2 and d.fieldname='$field'")
                ->order('a.id desc')
                ->select();
        //数组分页
        $total = count($list);//总条数
        $pagesize = input('pagesize/d',10);//每页数量
        $maxPage = ceil($total/$pagesize);//总共几页
        $page = input('page/d',1);
        if($page>$maxPage){
            $page = 1;
        }
        $offset = ($page-1)*$pagesize;
        $list = array_slice($list, $offset, $pagesize);//数组分页
        
        return ['last' => $lastMonthCount,'current' => $currentCount,'year' => $yearCount,'no_handle' => $noHandle,'list' => $list,'page' => $page];
    }
    
    /**
     * 自动派单屏--今昨汇总
     */
    public function auto_send()
    {
        $no = input("no");
        $page = input('page',0);
        //后台屏幕里用户选择的工序名
        $monitor = Db::name("monitor")->where("code='$no'")->find();   
        $gxname = explode(',', $monitor['gx_list']);        

        $dname = isset($gxname[$page])?$gxname[$page]:$gxname[0];
        $page = count($gxname)>($page+1)?$page+1:0;
        $yesterdayTime = timezone_get(7);
        $todayTime = timezone_get(1);
        
        $gxList = Db::name('gx_list')->where('dname',$dname)->find();
        if(!$gxList){
            return [];
        }
        //昨天数据
        $yesterday = $this->getDnameOrder($yesterdayTime);//昨天及之前的所有数据，完成数据
        $yesterdayAll = $yesterday['all'];
        $yesterdayNo = $yesterday['complete'];
        $gxAllCount = isset($yesterdayAll[$dname])?count($yesterdayAll[$dname]):0;//当前工序昨天及之前的所有数据
        $gxNoCount = isset($yesterdayNo[$dname])?count($yesterdayNo[$dname]):0;//当前工序昨天及之前的所有完成数据
        $ynoFinish = $gxAllCount-$gxNoCount;//当前工序昨天的未完成量
        $ycomplete = Db::name('flow_check')
                ->where("endtime>={$yesterdayTime['begin']} and endtime<={$yesterdayTime['end']}")
                ->where('orstatus',$gxList['id'])
                ->count();//当前工序昨天的完成量
        $yall = $ynoFinish+$ycomplete;//当前工序昨天总量
        $ypercent = $yall!=0?(round($ycomplete/$yall,2)*100).'%':"0%";
        
        //今天数据
        $today = $this->getDnameOrder($todayTime);
        $todayAll = $today['all'];
        $todayNo = $today['complete'];
        $gxAllCount = isset($todayAll[$dname])?count($todayAll[$dname]):0;//当前工序今天及之前的所有数据
        $gxNoCount = isset($todayNo[$dname])?count($todayNo[$dname]):0;//当前工序今天及之前的所有完成数据
        $noFinish = $gxAllCount-$gxNoCount;//当前工序今天的未完成量
        $complete = Db::name('flow_check')
                ->where("endtime>={$todayTime['begin']} and endtime<={$todayTime['end']}")
                ->where('orstatus',$gxList['id'])
                ->count();//当前工序今天的完成量
//        $gid = Db::name('doclass')->where("find_in_set({$gxList['lid']},line_id)")->column('id');//含当前工序的doclass id
        //当前工序今天接单量
        $todayReceive = Db::name('order')->where("find_in_set({$gxList['lid']},gxline_id)")->where("addtime>={$todayTime['begin']} and addtime<{$todayTime['end']}")->count();
        $all = $noFinish+$complete;//当前工序今天总量
        $allText = "往:".($all-$todayReceive).'/今:'.$todayReceive;
        $percent = $all!=0?(round($complete/$all,2)*100).'%':'0%';
        
        //往期 获取报工表中当前工序已完成的订单号
        $beforeOrderid = Db::name('flow_check')->where("endtime>0 and orstatus={$gxList['id']}")->group('orderid')->column('orderid');
        //当前工序往期未完成订单号
        $before = Db::name('order')->where("find_in_set({$gxList['lid']},gxline_id)")->whereNotIn('id', $beforeOrderid)
                ->where("addtime<{$todayTime['begin']} and repeal=0")->order('endtime<NOW()')
                ->column('unique_sn');        
        //当前工序今天已完成订单Id
        $currentOrderid = Db::name('flow_check')
                ->where("endtime>={$todayTime['begin']} and orstatus={$gxList['id']}")
                ->group('orderid')->column('orderid');
        //今天未完成订单号
        $todayList = Db::name('order')->where("find_in_set({$gxList['lid']},gxline_id)")
                ->where("addtime>={$todayTime['begin']} and addtime<={$todayTime['end']} and endstatus=1 and repeal=0")
                ->whereNotIn('id', $currentOrderid)
                ->order('endtime desc')
                ->column('unique_sn');        
        
        $all = ['yall'=>$yall,'ycomplete'=>$ycomplete,'yno'=>$ynoFinish,'ypercent'=>$ypercent,
                'all'=>$allText,'complete'=>$complete,'no'=>$noFinish,'percent'=>$percent
               ];
        return ['dname'=>$gxList['dname'],'all'=>$all,'before'=>$before,'today_list'=>$todayList,'page'=>$page];
    }
    
    
    /**
     * 总屏数据
     */
    public function ajax_all()
    {
        //上月
        $last = timezone_get(9); //上月开始和结束时间戳
        $lastDay = date('t',$last['begin']);//上月天数
        $timesql = "addtime>={$last['begin']} and addtime<{$last['end']}";
        $lastMonthNo = Db::name('order')->where($timesql)->where("intime=0")->count();//上月未入库数量
        $lastMonthComplete = Db::name('order')->field('sum(area) as area,count(id) as count')->where($timesql)->where("intime!=0")->find();       
        $lastMonthCount = $lastMonthComplete['count'];//上月入库数量
        $lastArea = round($lastMonthComplete['area']/$lastDay,1);//上月产均值

        $lastPeople = Db::name('flow_check')->where("endtime>={$last['begin']} and endtime<{$last['end']}")->group('uid')->select();
        $lastPeople = round(count($lastPeople)/$lastDay,1);//人均数
        
        //本月
        $currentTime = timezone_get(3);
        $currentDay = floor((time()-$currentTime['begin'])/(24*3600));//本月经过的天数
        $currentDay = $currentDay==0?1:$currentDay;
        $timesql = "addtime>={$currentTime['begin']} and addtime<{$currentTime['end']}";
        $currentMonthNo = Db::name('order')->where($timesql)->where("intime=0")->count();//本月未入库数量
        $currentMonthComplete = Db::name('order')->field('sum(area) as area,count(id) as count')->where($timesql)->where("intime!=0")->find();       
        $currentMonthCount = $currentMonthComplete['count'];//本月入库数量
        $currentArea = round($currentMonthComplete['area']/$currentDay,1);//本月产均值

        $currentPeople = Db::name('flow_check')->where("endtime>={$currentTime['begin']} and endtime<{$currentTime['end']}")->group('uid')->select();
        $currentPeople = round(count($currentPeople)/$currentDay,1);//人均数
        
        //本年
        $yearTime = timezone_get(6);
        $yearDay = floor((time()-$yearTime['begin'])/(24*3600)); //本年经过的天数    
        $timesql = "addtime>={$yearTime['begin']} and addtime<{$yearTime['end']}";
        $yearNo = Db::name('order')->where($timesql)->where("intime=0")->count();//本年未入库数量
        $yearComplete = Db::name('order')->field('sum(area) as area,count(id) as count')->where($timesql)->where("intime!=0")->find();       
        $yearCount = $yearComplete['count'];//本年入库数量
        $yearArea = round($yearComplete['area']/$yearDay,1);//本年产均值

        $yearPeople = Db::name('flow_check')->where("endtime>={$yearTime['begin']} and endtime<{$yearTime['end']}")->group('uid')->select();
        $yearPeople = round(count($yearPeople)/$yearDay,1);//人均数
        
        
        $no = input("no",'A');
    	$monitor = Db::name("monitor")->where("code='$no'")->find();           
        //数据统计
        $yesterdayTime = timezone_get(7);
        $todayTime = timezone_get(1);
        $list = $this->getHandleList($yesterdayTime,$monitor,$todayTime);
        $total = count($list);//总条数
        $pagesize = input('pagesize/d',10);//每页数量
        $maxPage = ceil($total/$pagesize);//总共几页
        $page = input('page/d',1);
        if($page>$maxPage){
            $page = 1;
        }
        $offset = ($page-1)*$pagesize;
        $list = array_slice($list, $offset, $pagesize);//数组分页
        
        $data = [
            'last_month_no' => $lastMonthNo+$lastMonthCount,'last_month_count' => $lastMonthCount,'last_area' => $lastArea,'last_people' => $lastPeople,
            'month_no' => $currentMonthNo+$currentMonthCount, 'month_count' => $currentMonthCount, 'area' => $currentArea,'people'=>$currentPeople,
            'year_no' => $yearNo+$yearCount, 'year_count' => $yearCount, 'year_area' => $yearArea, 'year_people' => $yearPeople,
            'list' => $list,'page' => $page
        ];
        return $data;
    }
    
    /**
     * 获取总屏幕昨天和今天的数据
     * @param array $yesterdayTime 昨天开始时间和结束时间戳
     * @param array $monitor monitor表数据
     * @param array $todayTime 今天开始时间和结束时间戳
     * @return array
     */
    protected function getHandleList($yesterdayTime,$monitor,$todayTime)
    {
        $gxArray = explode(',', $monitor['gx_list']);//列表要显示的工序名称
        $line=Db::name("gx_line")->order("id asc")->column("id");
        $gxArray = Db::name('gx_list')->whereIn("lid",$line)->whereIn('dname', $gxArray)->group('dname')->order('orderby asc,id asc')->column('dname');//总屏工序名按后台设置的顺序排序
        $yesterday = Db::name('flow_check')->alias('a')->field('a.*,b.dname,c.area,d.value')
                     ->join('gx_list b','a.orstatus=b.id')
                     ->join('order c','a.orderid=c.id')
                     ->join('order_attach d','c.id=d.orderid')
                     ->where('d.fieldname','snum')
                     ->whereIn('dname', $monitor['gx_list'])
                     ->where(function($query) use ($yesterdayTime){
                         $query->where("a.endtime>={$yesterdayTime['begin']} and a.endtime<{$yesterdayTime['end']}");
                         
                     })
                     ->select();
        $today = Db::name('flow_check')->alias('a')->field('a.*,b.dname,c.area,d.value')
                     ->join('gx_list b','a.orstatus=b.id')
                     ->join('order c','a.orderid=c.id')
                     ->join('order_attach d','c.id=d.orderid')
                     ->where('d.fieldname','snum')
                     ->whereIn('dname', $monitor['gx_list'])
                     ->where(function($query) use ($todayTime){
                         $query->where("a.endtime>={$todayTime['begin']} and a.endtime<{$todayTime['end']}");
                     })
                     ->select();
        
        $yresult = $this->getDnameOrder($yesterdayTime);        
        $yesterdayAll = $yresult['all'];//昨天之前的所有工序订单
        $yesterdayComplete = $yresult['complete'];//昨天之前已完成的订单数
        
        $result = $this->getDnameOrder($todayTime);
        $todayAll = $result['all'];//含今天之前的所有工序订单
        $todayComplete = $result['complete'];//含今天之前已完成的订单数
        
        $list = [];
        foreach ($gxArray as $k => $v) {
            //昨天数据           
            $noComplete = [];//未完成
            $complete = [];//已完成
            $area = [];//面积
            $people = [];//人数
            $snum = [];//扇数
            foreach ($yesterday as $k2 => $v2) {
                if($v == $v2['dname']){
                    $complete[$v2['orderid']] = $v2['orderid'];
                    $area[$v2['orderid']] = $v2['area'];
                    $people[$v2['uid']] = $v2['uid'];
                    $snum[$v2['orderid']] = $v2['value'];
                }
            }
           
            $area = count($people)==0?0:round(array_sum($area)/count($people),1);
            $snum = round(array_sum($snum),1);
            $list[$k]['yname'] = $v;
            $finish = isset($yesterdayComplete[$v])?count($yesterdayComplete[$v]):0;
            $total = isset($yesterdayAll[$v])?count($yesterdayAll[$v]):0;
            $noCompleteCount = ($total-$finish)>0?($total-$finish):0;
            $list[$k]['yno_complete'] = $noCompleteCount+count($complete); //总订单=昨天时间及之前所有未完成订单+昨天一天已完成订单
            $list[$k]['ycomplete'] = count($complete);
            $list[$k]['yarea'] = $area;
            $list[$k]['ysnum'] = $snum;
            $list[$k]['ypeople'] = count($people);
            
            //今天数据
            $noComplete = [];//未完成
            $complete = [];//已完成
            $area = [];//面积
            $people = [];//人数
            $snum = [];//扇数
            foreach ($today as $k3 => $v3) {
                if($v == $v3['dname']){
                    $complete[$v3['orderid']] = $v3['orderid']; 
                    $area[$v3['orderid']] = $v3['area'];
                    $people[$v3['uid']] = $v3['uid'];
                    $snum[$v3['orderid']] = $v3['value'];
                }
            }
            $area = count($people)==0?0:round(array_sum($area)/count($people),1);
            $snum = round(array_sum($snum),1);
            $finish = isset($todayComplete[$v])?count($todayComplete[$v]):0;
            $total = isset($todayAll[$v])?count($todayAll[$v]):0;
            $noCompleteCount = ($total-$finish)>0?($total-$finish):0;
            $list[$k]['no_complete'] = $noCompleteCount+count($complete);
            $list[$k]['complete'] = count($complete);
            $list[$k]['area'] = $area;
            $list[$k]['snum'] = $snum;
            $list[$k]['people'] = count($people);
        }
        
        return $list;
    }
    
    /**
     * 获取以工序名为键，值为订单id的数组
     * @param array $time 开始时间和结束时间戳
     */
    private function getDnameOrder($time)
    {
        //工序名称
        $allGxList = Db::name('gx_list')->where("lid",">",0)->select();        
        //所选时间的所有订单
        $res = Db::name('order')->alias('a')->field('a.id,a.gxline_id')
//                    ->join('doclass b','a.gid=b.id')
                    ->where("a.addtime<={$time['end']} and repeal=0")
                    ->select();
        $list = [];;
        foreach ($res as $k => $v) {
            $lineIds = explode(',', $v['gxline_id']);//当前订单line_id
            $gxList = [];            
            //通过line_id获取工序名称
            foreach ($allGxList as $k3 => $v3) {
                if(in_array($v3['lid'],$lineIds)){
                    $dname = $v3['dname'];
                    $gxList[] = $dname;
                    $list[$dname][$v['id']] = $v['id'];
                }
            }                       
        }
        //所选时间的完成订单
        $compelte = Db::name('flow_check')->alias('a')->field('a.*,b.dname')
                    ->join('gx_list b','a.orstatus=b.id')
                    ->where("a.endtime<={$time['end']} and a.endtime!=0")
                    ->select();              
        //处理数组,将工序名称作为键,值为对应的订单id
        $com = [];
        foreach ($compelte as $key => $value) {
            $com[$value['dname']][$value['orderid']] = $value['orderid'];
        }
//        dump($com);exit; 
        return ['all' => $list,'complete' => $com];
    }
    
    //某工序固定流水屏-记录当前跳转到第几页，只用作ajax返回不显示
    public function fixed_flow(){
    	$no = input('no');
    	$page = input('page',0);
    	
    	$monitor = Db::name('monitor')->where('code',$no)->find();
    	$gx_list = $monitor['gx_list']!=''?explode(',', $monitor['gx_list']):[];
    	$data = $gx_list;

    	//如果style=10则加入异常屏
        if($monitor['style'] == 10) {
            $data[] = 'exception';//跳转到异常屏
        }
    	if($page > count($data)-1){
    		$page = 0;
    	}
    	$gx_id = $data[$page];
    	$page = $page+1;
    
    	return ['gx_id'=>$gx_id,'page'=>$page];
    }
    
    //单个固定工序显示屏幕
    public function fixed_gx(){

    	$id=input("id",0,'intval');//工序的ID区分相同名的工序
    	$no=ctrim(input("no"));//屏幕代码
    	if(empty($id)){
    		$this->assign("error","缺少工序ID参数");
    	}

    	$gxlist_cache=@include APP_DATA.'gx_list.php'; //工序缓存
    	$gx=$gxlist_cache[$id];

    	$monitor_style=@include APP_CACHE_DIR.'monitor.php'; //读取屏幕样式缓存--默认1
    	$style=$monitor[10]['css'];
    	$this->assign("style",$style);

    	$monitor=M("monitor")->where("code='$no'")->find();

    	$this->assign("gx",$gx);
    	$this->assign("monitor",$monitor);
        $this->assign('no',$no);
    	return $this->fetch();
    }
    
    //ajax获取固定工序屏数据-返回给 fixed_gx 屏幕
    public function ajax_fixed_flow(){
    	//两年内订单统计
    	$timestamp=strtotime("-2 year");
    	$gx_id=input("id",0,'intval');//工序的ID区分相同名的工序
    	$field=ctrim(input("field"));//显示订单的字段
    	$this->all_did=0;
    	$this->all_order=array();
    	$this->sql="repeal='0' and pause='0' and addtime>=$timestamp ";//订单筛选条件：未完成、不取消、不暂停
    	$this->field="id,gid,unique_sn,endtime,isurgent,addtime,gxline_id";//订单需要查找的字段
		$this->check_field="id,orderid,orstatus,endtime";
		$this->orderby='isurgent desc,id desc';
		$this->parent_finish_checks=array();
		$monitor = Db::name('monitor')->where('code',input('no'))->find();
		$this->style = $monitor['style'];
    	$time=timezone_get(1);
    	$start_time=$time['begin'];//今天开始时间戳
    	$time=timezone_get(7);//昨天
    	$yesterady_time=$time['begin'];//昨天开始时间戳
    	$yyesterady_time=$yesterady_time-24*60*60;//前天开始时间戳
    	//总单量 = 工序所有未完成总数量
    	//可产量 = 工序未完成 而 上工序已完成 的总数量
    	//今加量 = 今天00：00后上工序完成流到当前工序数量
    	//昨流量 = 今天00：00前上工序完成数 - 昨天00：00前工序完成数 - 昨天工序报工数
    	
    	/**
    	 *	昨天：
    		可生产量 = 今天00：00前上工序完成数 - 昨天00：00前工序完成数
    		已完成量 = 昨天工序报工数
    		未完成量 = 今天00：00前上工序完成数 - 昨天00：00前工序完成数 - 昨天工序报工数
			今天：
			可生产量 = 当前工序未完成 而 上工序已完成 的总数
			已完成量 = 今天工序报工数
			未完成量 = 当前工序未完成 而 上工序已完成 的总数 - 今天工序报工数
    	 */
    	
    	$all_num=0;//总单量
    	$can_num=0;//可产量
    	$today_num=0;//今加量
    	$yestoday_num=0;//昨流量
    	
    	$yestoday=array('can_num'=>0,'finish'=>0,'unfinish'=>0,'rate'=>'0%');//昨天情况
    	$today=array('can_num'=>0,'finish'=>0,'unfinish'=>0,'rate'=>'0%');//今天情况
    	
    	//查找所有含有工序的订单
//    	if(count($this->all_did)>0){
//    		$this->all_order=M("order")->field($this->field)->where($this->sql." and gid in (".implode(",",$this->all_did).")")->order($this->orderby)->select();
            $lineId = getlineid_from_gxid($gx_id);
            $lineIdstr = implode('|',$lineId);
            $this->all_order=M("order")->field($this->field)->where($this->sql." and CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'")->order($this->orderby)->select();
//    	}
    	$total_num=$this->all_order!==false?count($this->all_order):0;

    	//计算总单量
    	if($total_num>0){
    		$all_order_id=array();
    		foreach($this->all_order as $value){
    			$all_order_id[]=$value['id'];
    		}
    		//已报工完成该工序的订单
    		$checks=M("flow_check")
    				->field($this->check_field)
    				->where("orstatus='$gx_id' and endtime>0 and orderid in (".implode(",",$all_order_id).")")
    				->group("orderid")
    				->select();

    		$all_num=$total_num;
    		$this->gx_checks=$checks;
    		
    		if($checks){
    			$gx_checks=array();
    			//过滤不存在的订单
    			foreach($checks as $k=>$value){
    				if(!in_array($value['orderid'], $all_order_id)){
    					unset($checks[$k]);
    				}
    				$gx_checks[$value['orderid']]=$value;
    			}
    			$checks_num=count($checks);
    			$all_num=($total_num-$checks_num)>0?($total_num-$checks_num):0;
    			$this->gx_checks=$gx_checks;
    			unset($checks);
    		}
    		
    	}else{
    			$all_num=0;
    			$this->gx_checks=[];
    	}
    	
    	//获取上级
    	$this->get_fixed_parent($gx_id);
    	
    	//右侧订单数据--返回今天该工序未完成的全部订单
    	$right_list=array();
    	$_orders=$this->fixed_today_order($gx_id);
    	//今天可产量（包含过往未完成该工序的订单）
    	if($_orders['EmptyOrder']=='1'){
    		$can_num=0;
    	}else if($_orders['EmptyOrder']=='2'){//没设置固定工序
    		//右侧订单数据=全部订单减去已报工的订单
    		$all_order=$this->all_order;
    		$now=time();
    		$daySecond=24*60*60;
    		//预警日期
    		$day = M('warm_time')->field('day')->find();
    		$warntime =$now+($day['day']*24*3600);
    		foreach($all_order as $k=>$value){
    		    $color='#44de46';//默认绿色,预警蓝色，超期红色
    		    //计算日期
    		    if($value['endtime']>0){
    		        $value['day']=floor(($value['endtime']-$now)/$daySecond);
    		        if($value['endtime']<$now){
    		            $color='#da657a';//超时
    		        }else if($value['endtime']<$warntime){
    		            $color='#1ca4ef';//预警
    		        }
    		    }else{
    		        $value['day']='0';
    		    }
    		    $value['color']=$color;
    		    $all_order[$k]=$value;
    		    //
    		    if($all_order&&$this->gx_checks){
    		        if(isset($this->gx_checks[$value['id']])){
    		            unset($all_order[$k]);
    		        }
    		        
    		    }
    		}
    		$can_num=count($all_order);
    		$right_list=$all_order;
    		
    	}else{
    		//有上级工序并且已完成，且当前工序未完成的订单
    		$can_num=count($_orders['orders']);
    		$right_list=$_orders['orders'];
    	}
    	
    	//显示自定义字段
    	if(is_array($right_list)&&count($right_list)>0&&$field!=''){
    		$attach_orderid=array();
    		foreach($right_list as $value){
    			$attach_orderid[]=$value['id'];
    		}
    		$attach_orderid=array_unique($attach_orderid);
    		//查找字段值
    		$values=M("order_attach")->field("orderid,value")->where("orderid in (".implode(",",$attach_orderid).") and fieldname='$field'")->select();
    		$order_value=array();
    		foreach($values as $value){
    			$order_value[$value['orderid']]=$value['value'];
    		}
    		//替换right_list的unique_sn
    		foreach($right_list as $k=>$value){
    			if(isset($order_value[$value['id']])){
    				$right_list[$k]['unique_sn']=$order_value[$value['id']];
    				//有可能重复的编码
    				foreach($right_list as $kk=>$val){
    					if($val['id']!=$value['id']&&$val['unique_sn']==$order_value[$value['id']]){
    						unset($right_list[$kk]);
    						break;
    					}
    				}
    			}
    		}
    		
    		unset($attach_orderid);
    		unset($values);
    		
    	}
    	
    	$yes_num=$yyes_num=$todayFlowCheck=$yesFlowCheck=0;//今天和昨天该工序报工数
    	if($this->gx_checks){
    		foreach($this->gx_checks as $k=>$value){
    			if($value['endtime']>=$yesterady_time&&$value['endtime']<$start_time){
    				$yesFlowCheck++;//昨天该工序报工数
    			}
    			if($value['endtime']>=$start_time){
    				$todayFlowCheck++;//今天该工序报工数
    			}
    			if($value['endtime']<$yesterady_time){
    				$yyes_num++;//昨天之前该工序报工完成的数量
    			}
    		}
    	}

    	//没设置固定工序或没上级
    	if(!$this->parent||!$this->parent['isSetting']||($this->parent['isSetting']&&count($this->parent['parent'])<=0)){
    		
    		//今加量--今天新增有该工序所有的订单（包括完成和未完成该工序）-如果要减去已完成的就减去$this->gx_checks的数量
    		foreach($this->all_order as $k=>$value){
    			if($value['addtime']>$start_time){
    				$today_num++;
    			}
    		}
    		
    		//昨流量=昨天内新增有该工序的订单数-昨天该工序报工数
    		foreach($this->all_order as $k=>$value){
    			if($value['addtime']<$start_time){
    				$yes_num++;//今天前含有改工序的订单数
    			}
    		}

    		//今天前所有该工序已报工数
    		foreach($this->gx_checks as $gc){
    			if($gc['endtime']<$start_time){
    				$gx_finish_num++;
    			}
    		}
    		
    		//昨流量
    		$yestoday_num=($yes_num-$gx_finish_num)>=0?($yes_num-$gx_finish_num):0;

    	}else if($this->parent['isSetting']&&count($this->parent['parent'])>0&&count($this->parent_finish_checks)>0){
    	
    		//今天前上工序都完成的数量
    		$parent_finish_num=0;
    		//今天前当前工序已经报工数量
    		$gx_finish_num=0;
    		//昨天未完成量=$parent_finish_num-$gx_finish_num;
    		
    		foreach($this->parent_finish_checks as $oid=>$value){
    			//上级工序完成个数
    			$intime_num=0;
    			foreach($value as $gxid=>$check){
    				if($check['endtime']<$start_time){
    					$intime_num++;
    				}
    			}
    			
    			if(isset($this->parent_num[$oid])&&$intime_num==$this->parent_num[$oid]){
    				$parent_finish_num++;
    			}
    		}
    		
    		//今天前当前工序完成报工数量
    		foreach($this->gx_checks as $gc){
    				if($gc['endtime']<$start_time){
    					$gx_finish_num++;
    				}
    		}
    		
			//昨天未完成
    		$yestoday_num=($parent_finish_num-$gx_finish_num)>0?($parent_finish_num-$gx_finish_num):0;
    	}
 		
   
    	//昨天------------------------------------------------------------------------
    	$yestoday['can_num']=$yesFlowCheck+$yestoday_num;
    	//昨天该工序报工数
    	$yestoday['finish']=$yesFlowCheck;
    	//未完成量=昨天订单数-昨天完成数
    	$yestoday['unfinish']=$yestoday_num;
    	
    	//达成率
    	if($yestoday['can_num']>0){
    		$yestoday['rate']=round(($yesFlowCheck/$yestoday['can_num'])*100,1)."%";
    	}
    	//昨天------------------------------------------------------------------------
    	
    	//今天------------------------------------------------------------------------
    	$today['can_num']=$can_num+$todayFlowCheck;
    	$today['finish']=$todayFlowCheck;
    	$today['unfinish']=($today['can_num']-$todayFlowCheck)>0?($today['can_num']-$todayFlowCheck):0;
    	if($today['can_num']>0){
    		$today['rate']=round(($todayFlowCheck/$today['can_num'])*100,1)."%";
    	}
    	//今天------------------------------------------------------------------------
    	$today_num=$today['can_num']-$yestoday['unfinish'];
    	$today_num=$today_num>0?$today_num:0;
    	return array('all_num'=>$all_num,'can_num'=>$can_num,'today_num'=>$today_num
    					,'yestoday_num'=>$yestoday_num,"yestoday"=>$yestoday,'today'=>$today
    					,'right_list'=>$right_list
    				);
    }
    
    //获取上级工序
    public function get_fixed_parent($gx_id){
    	
    	//获取他的上级工序和上级工序的进行状态
    	$fix_gx_id=@include APP_DATA.'fix_gx_id.php';
    	if(!$fix_gx_id||count($fix_gx_id)<=0){
    		//没设置固定工作流工序可以随便报工
    		$this->parent=false;
    		return;
    	}
    	
    	$parent=fixed_parent($fix_gx_id,$gx_id);
    	
    	$this->parent=$parent;
    	
    }
    
    //当天该工序可进行订单（包括以往未报工完成）-右侧订单列表
    //$gx_id 是工序ID
    //$parent 是$this->parent在上一步更新
    //$gx_checks 该工序已报工的记录
    public function fixed_today_order($gx_id){
    	
    	//返回的所有订单数组
    	$orders=array();
    	//记录所有父工序报工记录
    	$time_check=array();

    	$sql=$this->sql;
    	$limitSql="";
    	$parent=$this->parent;
    	
    	//查找这个工序是否有设置工作流和找到父工序
    	if($parent===false){//没设置固定工序
    		
    		return array("EmptyOrder"=>'2');
    		
    	}else if(!$parent['isSetting']||($parent['isSetting']&&count($parent['parent'])<=0)){
    		//该工序是最顶级工序，有这些工序的订单都可以进行
    		//或有设置到固定工作流，并且没上级工序则可报工

    		//在入口时候已经查询过，通过gid查询订单
    		if($this->all_order&&count($this->all_order)>0){
    			$orders=$this->all_order;
    		}else{
    			return array("EmptyOrder"=>'1');
    		}
    	
    	}else if($parent['isSetting']&&count($parent['parent'])>0){
    		
    		$doclass_list=@include APP_DATA.'doclass.php';
    		$gx_line=@include APP_DATA.'lines.php';
    		$gx_list=@include APP_DATA.'gx_list.php';
    		$indata['doclass']=$doclass_list;
    		$indata['gx_line']=$gx_line;
    		$indata['gx_list']=$gx_list;
    		//获取所有订单的
    		$all_order_gx=array();
    		$all_order_id=array();
    		foreach($this->all_order as $k=>$value){
    			$arr=array();
//    			$gx=gxlist_from_did_cache($value['gid'],$indata);
    			$gxlineId = explode(',',$value['gxline_id']);
                $gx = combine_gx_line($gxlineId);
                foreach($gx as $v){
    				$arr[]=$v['id'];
    			}
    			$all_order_gx[$value['id']]=$arr;
    			$all_order_id[]=$value['id'];
    		}
    		//设置了上级工序，并且多个上级工序已完成的订单
    		$before_gx=array_unique($parent['parent']);
    		if(count($all_order_id)>0){
    			$order_check=M("flow_check")
    						->field($this->check_field)
    						->where("orstatus in (".implode(",",$before_gx).") and endtime>0 and orderid in (".implode(",",$all_order_id).")")
    						->select();
    		}else{
    			$order_check=false;
    		}
    		if(!$order_check||count($order_check)<=0){
    			//上级工序没任何报工返回2
    			return array("EmptyOrder"=>'1');
    		}
    	
    		//循环报工记录，看每个订单的上级报工记录是否和当前工序的上级数量相等
    		$records=array();
    		foreach($order_check as $value){
    			$oid=$value['orderid'];
    			$orstatus=$value['orstatus'];
    			$records[$oid][$orstatus]=$orstatus;
    			$time_check[$oid][$orstatus]=$value;
    		}
    	
    		
    		//执行对比，有相同报工数的订单则返回(说明上级工序已全部完成)
    		$orderid=array();
    		foreach($records as $oid=>$orstatus){
    			if(!isset($all_order_gx[$oid])){
    				continue;
    			}
    			//查询每个订单实际上有多少个上级工序
    			$parent_num=0;
    			foreach($before_gx as $b){
    				
    					if(in_array($b, $all_order_gx[$oid])){
    						$parent_num++;
    					}
    				
    			}
    			
    			if($parent_num>0&&count($orstatus)==$parent_num){
    				$orderid[]=$oid;
    				$this->parent_finish_checks[$oid]=$time_check[$oid];//记录父工序都完工的报工记录
    				$this->parent_num[$oid]=$parent_num;
    			}
    		}
    	
    		if(count($orderid)<=0){
    			return array("EmptyOrder"=>'1');
    		}
    		
    		//返回上级工序已完成的订单和报工记录
    		$limitSql=" and id in (".implode(",", $orderid).")";
    		$orders=M("order")->field($this->field)->where($this->sql.$limitSql)->order($this->orderby)->select();
    	}
    	 
    	if(count($orders)<=0){
    		return array("EmptyOrder"=>'1');
    	}
    	//查询所有的用该工艺线的未完成订单
    	$now=time();
    	$daySecond=24*60*60;
    	
    	//删除该工序已报工的订单
    	if($this->gx_checks){
    		foreach($orders as $k=>$value){
    			$oid=$value['id'];
    			if(isset($this->gx_checks[$oid])){
    				unset($orders[$k]);
    			}
    		}
    	}

    	//订单后面的时间,如果是预计划看板
    	if($this->style == 12){
            $preproduct = Db::name('preproduct_gx')->where('gxid',$gx_id)->whereIn('orderid',$orderid)->select();//工序的预生产时间
            //整理数据
            $orderEndtime = [];
            foreach ($preproduct as $k => $v) {
                $orderEndtime[$v['orderid']] = strtotime($v['endtime']);
            }
            foreach ($orders as $k => $value) {
                $color = '#44de46';//默认绿色,预警蓝色，超期红色
                //计算日期
                if (isset($orderEndtime[$value['id']])) {
                    $value['day'] = floor(($orderEndtime[$value['id']] - $now) / $daySecond);
                } else {
                    $value['day'] = '0';
                }
                $value['color'] = $color;
                $orders[$k] = $value;
            }
        }else {
            //预警日期
            $day = M('warm_time')->field('day')->find();
            $warntime = $now + ($day['day'] * 24 * 3600);
            foreach ($orders as $k => $value) {
                $color = '#44de46';//默认绿色,预警蓝色，超期红色
                //计算日期
                if ($value['endtime'] > 0) {
                    $value['day'] = floor(($value['endtime'] - $now) / $daySecond);
                    if ($value['endtime'] < $now) {
                        $color = '#da657a';//超时
                    } else if ($value['endtime'] < $warntime) {
                        $color = '#1ca4ef';//预警
                    }
                } else {
                    $value['day'] = '0';
                }
                $value['color'] = $color;
                $orders[$k] = $value;
            }
        }
    	
    	//记录当前工序的所有父工序报工的记录
    	//$this->parent_checks=$time_check;
    	
    	return array("EmptyOrder"=>'0','orders'=>$orders,"parent_checks"=>$time_check);
    	
    }
    
    
}
