<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use app\index\service\IndexExport;

class Export extends Super{
	
	//筛选时候使用
	private $limitFlowId=array();
	
    public function initialize(){
        parent::initialize();
        $uid = session('gid');
        
 		//订单工序筛选
        $user_role=session("user_role");
        $tid=session("tid");
        $sl_gx=$this->role_gx($user_role, $tid);
        $this->assign('endg',$sl_gx);
        
        //获取员工列表
        $man_list=Db::name("login")->where("dimission=0 and del=0")->order("id asc")->select();
        $this->assign('man',$man_list);
    }
    
    //统一返回搜索条件sql
    private function getWhere($needAssign=false){

        $where = "";
        $limit = $this->role_limit();//判断角色显示对应订单记录
        if ($limit != '') {
            $where .= " and " . $limit;
        }
        $color = ctrim(input("param.color"));
        $iswarn = ctrim(input("param.iswarn"));
        $s_date = ctrim(input("param.s_date"));
        $e_date = ctrim(input("param.e_date"));
        $k_date = ctrim(input("param.k_date"));
        $ke_date = ctrim(input("param.ke_date"));
        $xs_date = ctrim(input("param.xs_date"));
        $xe_date = ctrim(input("param.xe_date"));
        $in_date1 = ctrim(input("param.in_date1"));//入库开始时间
        $in_date2 = ctrim(input("param.in_date2"));//入库结束时间
        $warntime_s = ctrim(input("param.warntime_s"));
        $warntime_e = ctrim(input("param.warntime_e"));
//     	$gname = ctrim(input("param.status"));
        $gx_status = intval(input("param.gx_status"));//工序状态
        $gname = input("gname/s");
        $finishgx = input("finish_gx/s");
        $dogx = input("doing_gx/s");
        $order_status = intval(input("param.order_status"));//订单状态
        $senior = intval(input("param.senior"));//标记是否高级搜索
        $xordersn = ctrim(input("param.xordersn"));//包含流水号或客户名称等
        //新增的时间区域
        $timezone = ctrim(input("param.timezone"));
        $nosend = intval(input("param.nosend"));//不显示已发货
        $nointo=intval(input("param.nointo"));//不显示已入库
        $orderby = ctrim(input("param.orderby"));//排序

        $post['timezone'] = $timezone;
        $post['senior'] = $senior;
        $post['color'] = $color;
        $post['iswarn'] = $iswarn;
        $post['s_date'] = $s_date;
        $post['e_date'] = $e_date;
        $post['k_date'] = $k_date;
        $post['ke_date'] = $ke_date;
        $post['xs_date'] = $xs_date;
        $post['xe_date'] = $xe_date;
        $post['in_date1'] = $in_date1;
        $post['in_date2'] = $in_date2;
        $post['warntime_s'] = $warntime_s;
        $post['warntime_e'] = $warntime_e;
//     	$post['status']=$gname;
        $post['gx_status'] = $gx_status;
        $post['gname'] = $gname;
        $post['finishgx'] = $finishgx;
        $post['dogx'] = $dogx;
        $post['order_status'] = $order_status;
        $post['nosend'] = $nosend;
        $post['xordersn'] = $xordersn;
        $post['orderby'] = $orderby;
        $this->assign("post", $post);

        //时间段筛选
        if (!empty($timezone)) {
            $start_date = substr($timezone, 0, 10);
            $end_date = substr($timezone, 11, 10);

            $start_date = ymktime($start_date);
            $end_date = ymktime($end_date) + 24 * 60 * 60 - 1;
            $where .= "and a.ordertime between $start_date and $end_date";
        }

        //日期筛选
        $s_date = $s_date != '' ? ymktime($s_date) : 0;
        $e_date = $e_date != '' ? ymktime($e_date) + 24 * 60 * 60 - 1 : 0;

        if (!empty($s_date) && empty($e_date)) {
            $where .= " and a.ordertime>=$s_date ";
        } elseif (!empty($e_date) && empty($s_date)) {
            $where .= " and a.ordertime<=$e_date ";
        } elseif (!empty($e_date) && !empty($s_date)) {
            $where .= " and a.ordertime between $s_date and $e_date ";
        }

        //交货日期
        if (!empty($k_date)) {
            $k_start = ymktime($k_date);
            $where .= " and a.endtime>=$k_start  ";
        }
        if (!empty($ke_date)) {
            $k_end = ymktime($ke_date);
            $k_end = $k_end + 24 * 60 * 60 - 1;
            $where .= " and a.endtime<=$k_end ";
        }

        //入库日期
        if (!empty($in_date1) || !empty($in_date2)) {
            $where .= " and a.status='1' ";
            if (!empty($in_date1)) {
                $where .= " and a.intime>=" . ymktime($in_date1);
            }
            if (!empty($in_date2)) {
                $where .= " and a.intime<=" . (ymktime($in_date2) + 24 * 60 * 60 - 1);
            }
        }


        //高级搜索
        if (!empty($xordersn)) {
            $where .= " and (a.ordernum like '%$xordersn%' or a.unique_sn like '%$xordersn%' or a.uname	 like '%$xordersn%') ";
        }

        //颜色
        if (!empty($color)) {
            $where .= " and a.color like '%$color%' ";
        }

        //不显示已发货
        if (!empty($nosend)) {
            $where .= " and (a.outstatus!=1 and a.outstatus!=2) ";
        }
        //不显示已入库
        if (!empty($nointo)){
            $where .= " and (a.endstatus!=2) ";
        }
        //预警订单
        if (!empty($iswarn)) {
            $day = M('warm_time')->field("day")->find();
            $facttime = time() + ($day['day'] * 24 * 3600);
            if ($iswarn == 1) {//是
                $where .= " and a.endstatus=1 and a.endtime<$facttime ";
            } else {
                $where .= " and a.endtime>=$facttime ";
            }
        }

        //预警日期
        if (!empty($warntime_s)) {
            $warntime_start = ymktime($warntime_s);
            $where .= " and a.endtime>=$warntime_start  ";
        }
        if (!empty($warntime_e)) {
            $warntime_end = ymktime($warntime_e);
            $warntime_end = $warntime_end + 24 * 60 * 60 - 1;
            $where .= " and a.endtime<=$warntime_end ";
        }

        if (!empty($warntime_s) || !empty($warntime_e)) {
            $where .= " and a.endstatus=1 ";
        }


        //未完成工序
        if (!empty($gname)) {
            $Gx = array();
            if (strpos($gname, ",") !== false) {
                $Gx = explode(",", $gname);
            } else {
                $Gx[] = $gname;
            }
            $Gx = array_unique($Gx);
            $fGx = $nameGx = array();
            foreach ($Gx as $key => $value) {
                if (trim($value) != '') {
                    $f = ctrim($value);
                    $fGx[] = $f;
                    $nameGx[] = "dname='$f'";
                }
            }
            //查找
            $gxs = Db::name("gx_list")->field("id,did,state")->where("1=1 and (" . implode(" or ", $nameGx) . ")")->select();
            $gxId = array();
            foreach ($gxs as $t) {
                $gxId[] = $t['id'];
            }

            //搜索工序状态
            if (count($gxId) <= 0) {
                //没符合的记录，不显示订单
                $where .= " and a.id='0' ";
            } else {
                //查找所有拥有这些工序名的did
//                $dids = getdid_from_gxname($fGx);
                $dids = getlineid_from_gxname($fGx);
                if (count($dids) <= 0) {//没有可用的doclass
                    $where .= " and a.id='0'";
                } else {
                    //查找符合多个工序未开始的订单
                    $lineIdstr = implode('|',$dids);
                    $orderId = M("order")->where("CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'")->column("id");

                    if ($orderId && count($orderId) > 0) {

                        //一次过查询这些订单已经报工的记录
                        $checks = M("flow_check")
                            ->where("orderid in (" . implode(",", $orderId) . ")")
                            ->field("orderid,orstatus")
                            ->select();

                        $oid = array();
                        foreach ($orderId as $id) {
                            $oid[$id] = $id;
                        }
                        //只要有一个已经报工都不返回该订单id
                        foreach ($checks as $value) {
                            if (in_array($value['orstatus'], $gxId)) {
                                unset($oid[$value['orderid']]);
                            }
                        }
                        $oid = count($oid) > 0 ? $oid : array('0');
                        $where .= " and a.id in (" . implode(",", $oid) . ")";
                    } else {
                        $where .= " and a.id='0'";
                    }
                }

            }
        }


        //进行中工序
        if (!empty($dogx)) {
            $doingGx = $dGx = $nameGx = array();
            if (strpos($dogx, ",") !== false) {
                $doingGx = explode(",", $dogx);
            } else {
                $doingGx[] = $dogx;
            }
            $doingGx = array_unique($doingGx);
            foreach ($doingGx as $key => $value) {
                if (trim($value) != '') {
                    $f = ctrim($value);
                    $dGx[] = $f;
                    $nameGx[] = "dname='$f'";
                }
            }

            if (count($dGx) > 0) {
                $gxId = Db::name("gx_list")->where("1=1 and (" . implode(" or ", $nameGx) . ")")->column("id");
                //查找所有拥有这些工序名的did
//                $dids = getdid_from_gxname($dGx);
                $dids = getlineid_from_gxname($dGx);
                if (count($dids) <= 0) {//没有可用的doclass
                    $where .= " and a.id='0'";
                } else {
                    //查找用了限制的$dids工艺路线内的订单--并且查询的多个工序名已完成
                    $lineIdstr = implode('|',$dids);
                    $orderId = M("order")->where("CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'")->column("id");
                    if ($orderId && count($orderId) > 0) {
                        $xs_date = $xs_date != '' ? ymktime($xs_date) : 0;
                        $xe_date = $xe_date != '' ? ymktime($xe_date) + 24 * 60 * 60 : 0;
                        $time_sql = "";
                        if (!empty($xs_date) && empty($xe_date)) {
                            $time_sql .= " and starttime>=$xs_date";
                        } elseif (!empty($xe_date) && empty($xs_date)) {
                            $time_sql .= " and starttime<=$xe_date";
                        } elseif (!empty($xe_date) && !empty($xs_date)) {
                            $time_sql .= " and starttime between $xs_date and $xe_date";
                        }
                        $orders = M("flow_check")
                            ->where("orstatus in (" . implode(",", $gxId) . ") and starttime>0 and endtime=0 and orderid in (" . implode(",", $orderId) . ") $time_sql")
                            ->field("orderid,orstatus")
                            ->select();

                        if ($orders && count($orders) > 0) {
                            $temp = array();
                            foreach ($orders as $ov) {
                                $temp[$ov['orderid']][$ov['orstatus']] = $ov['orstatus'];//每个订单的完成工序
                            }

                            $finGxLen = count($dGx);
                            $oid = array();
                            //遍历订单，必须有符合搜索完成工序相等数量的订单已报工序才返回
                            foreach ($temp as $orderid => $ov) {
                                if ($finGxLen == count($ov)) {
                                    $oid[] = $orderid;
                                }
                            }
                            $oid = count($oid) > 0 ? $oid : array('0');
                            $where .= " and a.id in (" . implode(",", $oid) . ")";

                        } else {
                            $where .= " and a.id='0'";
                        }
                    } else {
                        $where .= " and a.id='0'";
                    }
                }
            }
        }

        //已完成工序
        if (!empty($finishgx)) {
            $finGx = $fGx = $nameGx = array();
            if (strpos($finishgx, ",") !== false) {
                $finGx = explode(",", $finishgx);
            } else {
                $finGx[] = $finishgx;
            }
            $finGx = array_unique($finGx);
            foreach ($finGx as $key => $value) {
                if (trim($value) != '') {
                    $f = ctrim($value);
                    $fGx[] = $f;
                    $nameGx[] = "dname='$f'";
                }
            }

            if (count($fGx) > 0) {
                $gxId = Db::name("gx_list")->where("1=1 and (" . implode(" or ", $nameGx) . ")")->column("id");
                //查找所有拥有这些工序名的did
                $dids = getlineid_from_gxname($fGx);
                if (count($dids) <= 0) {//没有可用的doclass
                    $where .= " and a.id='0'";
                } else {
                    //查找用了限制的$dids工艺路线内的订单--并且查询的多个工序名已完成
                    $lineIdstr = implode('|',$dids);
                    $orderId = M("order")->where("CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'")->column("id");
                    if ($orderId && count($orderId) > 0) {
                        $xs_date = $xs_date != '' ? ymktime($xs_date) : 0;
                        $xe_date = $xe_date != '' ? ymktime($xe_date) + 24 * 60 * 60 : 0;
                        $time_sql = "";
                        if (!empty($xs_date) && empty($xe_date)) {
                            $time_sql .= " and endtime>=$xs_date";
                        } elseif (!empty($xe_date) && empty($xs_date)) {
                            $time_sql .= " and endtime<=$xe_date";
                        } elseif (!empty($xe_date) && !empty($xs_date)) {
                            $time_sql .= " and endtime between $xs_date and $xe_date";
                        }
                        $orders = M("flow_check")
                            ->where("orstatus in (" . implode(",", $gxId) . ") and endtime>0 and orderid in (" . implode(",", $orderId) . ") $time_sql")
                            ->field("orderid,orstatus")
                            ->select();

                        if ($orders && count($orders) > 0) {
                            $temp = array();
                            foreach ($orders as $ov) {
                                $temp[$ov['orderid']][$ov['orstatus']] = $ov['orstatus'];//每个订单的完成工序
                            }

                            $finGxLen = count($fGx);
                            $oid = array();
                            //遍历订单，必须有符合搜索完成工序相等数量的订单已报工序才返回
                            foreach ($temp as $orderid => $ov) {
                                if ($finGxLen == count($ov)) {
                                    $oid[] = $orderid;
                                }
                            }
                            $oid = count($oid) > 0 ? $oid : array('0');
                            $where .= " and a.id in (" . implode(",", $oid) . ")";

                        } else {
                            $where .= " and a.id='0'";
                        }
                    } else {
                        $where .= " and a.id='0'";
                    }
                }
            }
        }
        if (empty($dogx)&&empty($finishgx)&&empty($gname)&&(!empty($xs_date)||!empty($xe_date))){

            $time_sql="1=1 ";
            $xs_date = $xs_date!=''?ymktime($xs_date):0;
            $xe_date = $xe_date!=''?ymktime($xe_date)+24*60*60:0;
            if (!empty($xs_date) && empty($xe_date)){
                $time_sql .= " and endtime>=$xs_date";
            }elseif (!empty($xe_date) && empty($xs_date)){
                $time_sql .= " and endtime<=$xe_date";
            }elseif (!empty($xe_date) && !empty($xs_date)){
                $time_sql .= " and  endtime between $xs_date and $xe_date";
            }
            $gx_status_sql=$time_sql." and endtime>0 and state='0' and status='0'";

            //审核表
            $flow = db::name('flow_check')->field('orderid')->where($gx_status_sql)->select();
            if ($flow){
                $acc=array();
                foreach ($flow as $e){
                    array_push($acc, $e['orderid']);
                }
                $acc_f = implode(",", $acc);
                $where .= " and a.id in ($acc_f)";
            }else{
                $where .= " and a.id='0'";
            }
        }
        //订单状态
        if(!empty($order_status)){
            switch ($order_status){
                case 1://正常
                    $where.=" and a.pause!='1' and a.repeal!='1'";
                    break;
                case 2://暂停
                    $where.=" and a.pause='1'";
                    break;
                case 3://作废
                    $where.=" and a.repeal='1'";
                    break;
            }
        }

        //@hj 2020/03/05 自定义高级搜索
        $searchsql=$this->senior_search("a.");
        if($searchsql!=''){
            $where.=" and ".$searchsql;
        }

        return $where;
    }
    
    
    public function export(){
      
      $ugid = session("gid");
        
       //预警天数
      $day = Db::name('warm_time')->find();
      $gx_list = Db::name("gx_list")->where("isdel=0")->order("orderby asc")->select();
      if (FIX_GX==0){
          $into_gx = Db::name("into_gx")->field("id,name as dname")->order('id asc')->select();
          foreach ($into_gx as $gx){
              array_push($gx_list,$gx);
          }
      }
      //读取字段
      $fields=@include APP_DATA.'qrfield_type.php';
      $allfield=@include APP_DATA.'qrfield.php';
      $fieldList=array();
      $search_field=array();
      $endtime_field='交货日期';
      if(isset($fields['onlist'])){
      	 
      	foreach($fields['onlist'] as $value){
      		$fieldList[$value['fieldname']]=$value;
      	}
      	
      }
      
      if(isset($allfield)){
	      foreach($allfield as $value){
	      	if($value['search']==1){
	      		$search_field[$value['fieldname']]=$value;
	      	}
	      	if($value['fieldname']=='endtime'){
	      		$endtime_field=$value['explains'];
	      	}
	      }
      }
       
      $this->assign('gx_list',$gx_list);
      //高级搜索字段
      $this->assign("searchField",$search_field);
      //交货日期中文名
      $this->assign("endtime_field",$endtime_field);
      $this->assign("hideMenu",1);
      $this->assign('today',date('Y-m-d'));
      return $this->fetch();
    }
    
    /**
     * 导出表数据
     * 分多个sheet来区分不同工序的数据
     *
     */
    public function dataExcel(){
        set_time_limit(0);
        //查询数据
       $uid=session("uid");
       $ugid = session("gid");
       
       $where = '1=1 ';
       $where.=$this->getWhere();
       
       //判断当前的人是否是超级管理员或者有没有权限查看全部订单-否则只可以查看自己和自己添加的组员下面的订单
       //if(1){
       //		$where.=" and a.ugid='$ugid' ";
       //}
        //查询到订单数据
        $orders = Db::name("order")->alias('a')->field('a.*,b.day')
                    ->join('doclass b','b.id=a.gid','LEFT')
                    ->where("$where")->order("a.id desc")->select();
        
        if (empty($orders)){
            $this->error('暂无订单','Export/export');
            exit();
        }
        
        //输出多个工作表的总数组
        //多个工作表名称和个个工作表表头字段
        $titles=array();
        //多个工作表的数据
        $lists=array();
        //存储起多个工艺路线的多个工序组
        $gy=array();
        
        //该报工项目的系统设置显示在列表的表头字段
        $fieldList=array();
        //读取字段
        $fields=@include APP_DATA.'qrfield_type.php';
        if(isset($fields['onlist'])){
        	foreach($fields['onlist'] as $value){
        		$fieldList[$value['fieldname']]=$value;
        	}
        }
        
        //合并订单附表数据
        $orders=order_attach($orders,array("gid"));
        
        //查询所有的工艺路线-一个工艺路线一个worksheet表
        $doclass = Db::name('series')->field("id,xname,gid,isnew")->order('id asc')->select();
        $doclass2=array();//用于存储id=>value新数组
        $worksheet=array();//每个工作表的标题
        foreach($doclass as $key=>$value){
        	$worksheet[$value['gid']]=$value['xname'];
        	$doclass2[$value['gid']]=$value;
        }
        
        //查询每个工作表的所有字段-即每个工作表的所有字段和对应的标题
        foreach($worksheet as $did=>$name){
        	$titles[$name]=array();
        	
        	//1.将项目设置的表头字段推进$titles[$name]
        	foreach($fieldList as $fieldname=>$v){
        		$titles[$name][$fieldname]=$v['explains'];
        	}
        	
        	//2.必须字段放入数组
        	$titles[$name]['system_period']='实际周期';//实际周期
        	$titles[$name]['system_period_request']='要求周期';//要求周期
        	$titles[$name]['system_exception']='异常说明';//异常说明
        	
        	//3.每个工序放入标题-字段名用gx1---gxN。开始和结束用gx1_start,gx1_end...gxN_start,gxN_end
        	if($doclass2[$did]['isnew']==1){
        		$gx=gxlist_from_did($did);
        	}else{
        		$gx = Db::name("gx_list")->where("did='$did'")->order("id asc")->select();
        	}
        	
        	if($gx!==false&&count($gx)>0){
        		
        		//存储到工艺路线，免得循环订单时候再查一次
        		$gy[$did]=$gx;
        		
	        	foreach($gx as $kk=>$gv){
        			$gxfield='gx'.$kk;
        			$titles[$name][$gxfield]=$gv['dname'];
	        		//只报结束
	        		if( $gv['state']==2){
	        			$titles[$name][$gxfield."_end"]="结束时间";
	        		}else{
	        			//报开始和结束
	        			$titles[$name][$gxfield."_start"]="开始时间";
	        			$titles[$name][$gxfield."_end"]="结束时间";
	        		}
	        	}
        	}
        }//end of foreach
       	//所有工作表和每个工作表表头字段在上面已全部找到 
       
        
        //订单数据编排
        //将订单数据按照工艺路线分组
        $olist=array();
        foreach($orders as $key=>$value){
        	$worksheet_name=$worksheet[$value['gid']];
        	$olist[$worksheet_name][]=$value;
        }
        
        //循环订单
        foreach ($olist as $worksheet_name=>$orders){
        	$new=array();//重新返回新订单数据
        	foreach($orders as $k=>$v){
        		
	        	$orderid = $v['id'];
	        	$did=$v['gid'];//工艺线id
	        	if(!isset($gy[$did])){
	        		continue;
	        	}
	        	
	        	$v['system_period_request']=$doclass2[$did]['day'];//要求周期
	        	$v['system_period']='';//实际周期
	        	$v['system_exception']='';//异常说明-没报工即为空
	        	
	        	//工艺线工序
	        	$gx_lis = $gy[$did];

	        	$limit_gx=array();
	        	foreach($gx_lis as $gv){
	        		$limit_gx[]=$gv['id'];
	        	}
	        
	        	//全部报工审核数据
	        	$checks = Db::name("flow_check")->alias("a")->field("orstatus,starttime,endtime,status,state,stext")
	        	->where("orderid=$orderid and orstatus in (".implode(",",$limit_gx).")")->order("id asc")->select();

	        	//转换成以工序ID做键的数组
	        	$flow_check=array();
	        	//记录所有报工的开始和时间,然后当工艺线工序数等于所有报工数则计算实际周期
	        	$timezone=array();
	        	if($checks!==false&&($checks)>0){
		        	foreach($checks as $cv){
		        		$flow_check[$cv['orstatus']]=$cv;
		        		
		        		if($cv['starttime']>0){
		        			$timezone[]=$cv['starttime'];
		        		}
		        		if($cv['endtime']>0){
		        			$timezone[]=$cv['endtime'];
		        		}
		        	}
	        	}
	        	
	        	//获取每个工序的开始和结束时间，总结异常和实际周期和要求周期
	        	$gx_num=count($gx_lis);//工艺线的所有工序数
	        	$check_num = count($flow_check);//所有工序已报工数
	        	
	        	$exception=array();
	        	
	        	//遍历每个工序并返回开始和结束时间-异常-实际周期
	        	foreach ($gx_lis as $kk=>$gv){
	        		
	        		//字段名称gx0..gxN等
	        		$gxfield='gx'.$kk;
	        		//初始化状态
	        		$v[$gxfield]="未开始";
	        		
	        		$starttime=$endtime=0;
	        		
	        		//错误信息
	        		$error=array();
	        		
	        		//有报工记录
	        		if(isset($flow_check[$gv['id']])){
	        			if($flow_check[$gv['id']]['starttime']>0){
	        				$starttime=$flow_check[$gv['id']]['starttime'];
	        			}
	        			if($flow_check[$gv['id']]['endtime']>0){
	        				$endtime=$flow_check[$gv['id']]['endtime'];
	        			}
	        			
	        			if($flow_check[$gv['id']]['state']==1){
	        				$error[]='异常';
	        				$exception[]=$gv['dname']."：".$flow_check[$gv['id']]['stext'].";";
	        			}
	        			
	        			if($flow_check[$gv['id']]['status']==1){
	        				$error[]='超时';
	        			}else if($flow_check[$gv['id']]['state']!=1&&$flow_check[$gv['id']]['status']!=1){
	        				$error[]='正常';
	        			}
	        		}
	        		
	        		//只报结束
	        		if($gv['state']==2){
	        			$v[$gxfield."_end"]=$endtime>0?date('Y-m-d',$endtime):'';//结束时间
	        			if($endtime>0){
	        				$v[$gxfield]="已完成";//状态
	        			}
	        		}else{
	        			//报开始和结束
	        			$v[$gxfield."_start"]=$starttime>0?date('Y-m-d',$starttime):'';//开始时间
	        			$v[$gxfield."_end"]=$endtime>0?date('Y-m-d',$endtime):'';//结束时间
	        			
	        			if($starttime>0){
	        				$v[$gxfield]="进行中";//状态
	        			}
	        			if($endtime>0){
	        				$v[$gxfield]="已完成";//状态
	        			}
	        		}
	        		
	        		if(count($error)>0){
	        			$v[$gxfield].="|".implode("|", $error);
	        		}
	        		
	        	}//end of foreach
	        	
	        	if(count($exception)>0){
	        		$v['system_exception']=implode("",$exception);
	        	}
	        	
	        	//计算实际周期
	        	if($gx_num<=$check_num&&count($timezone)>1){
	        		
	        			sort($timezone);
	        			$last=end($timezone);
	        			$first=$timezone[0];
	        			//计算日期
	        			$v['system_period'] = round(($last-$first)/3600/24);
	        	}
	        	unset($v['gx_schedule']);
	        	unset($v['order_attach']);
	        	//存储进新数组
	        	$new[$k]=$v;
        	}//end of foreach 
        	$worksheet_name=$this->specialchar($worksheet_name);
        	$lists[$worksheet_name]=$new;
        }//end of foreach        
        
        //echo '<pre>';
        //print_r($lists);
       //echo '</pre>';
        //exit();
        
        $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
        $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
        $exceltitle='订单工序数据表'.date('YmdHis',time());
        
        $this->multi_export($titles, $lists, $creator, $exceltitle);

    }
    
    //去除特殊字符
    private function specialchar($str){
    	$search=array("/","*","(",")","\\","[","]");
    	$replace=array("","","","","","","");
    	return str_replace($search, $replace, $str);
    }

    /**
     * 导出订单及工序数据
     */
    public function orderGxlist()
    {
        $where = '1=1 ';
        $where .= $this->getWhere();
        //查询到订单数据
        $orders = Db::name("order")->alias('a')->field('a.*,b.line_id')
                    ->join('doclass b','b.id=a.gid','LEFT')
                    ->where("$where")->order("a.id desc")->select();        
        if (empty($orders)){
            $this->error('暂无订单');
        }
        
        //获取所有订单的did	@hj 2020-05-09 居上好 只导出订单有的工序
        $did=array();
        if($orders&&count($orders)>0){
        	foreach($orders as $value){
        		$did = array_merge($did,explode(',',$value['gxline_id']));
        	}
        }

        $orders = order_attach($orders,array("gid"));

        $titles=array();//表头数据        
        $lists=array();//数据        
        $gy=array();//存储起多个工艺路线的多个工序组
        
        //该报工项目的系统设置显示在列表的表头字段
        $fieldList=array();
        //读取字段
        $fields=@include APP_DATA.'qrfield_type.php';
        if(isset($fields['onlist'])){
        	foreach($fields['onlist'] as $value){
        		$fieldList[$value['fieldname']]=$value;
        	}
        }
        $fieldList['intime']=array('fieldname'=>'intime','explains'=>'入库时间','status'=>0);
        $fieldList['badwrite']=array('fieldname'=>'badwrite','explains'=>'异常汇总','status'=>0);//增加异常汇总
        $fieldList['cause']=array('fieldname'=>'cause','explains'=>'超时原因','status'=>0);
        if(count($did)>0){
        	$lids= array_unique($did);
        }else{
        	$gxline=@include APP_DATA.'lines.php';
        	$lids=array_keys($gxline);
        }
      
        //所有工序名称
        $gxList = Db::name('gx_list')->whereIn("lid",$lids)->order('orderby asc,id asc')->select();
        $gxLineId = [];
        foreach ($gxList as $k => $v) {
            $fieldList[$v['dname']] = ['is_gx'=>1,'explains'=>$v['dname'],'state'=>$v['state']];
            $gxLineId[] = $v['lid'];
        }
        
        //列表数据
        foreach ($orders as $k => $v) {
            $lineIdList = explode(',', $v['gxline_id']);//gx_line id
            $gxId = [];//订单的工序id
            $v['intime']!=''?$orders[$k]['intime'] = date("Y-m-d",$v['intime']):$orders[$k]['intime']='';
            foreach ($gxList as $k2 => $v2) {
                if(in_array($v2['lid'],$lineIdList)){
                    $gxId[] = $v2['id'];
                }
            }

            //获取当前订单的报工工序记录
            $flowCheck = Db::name('flow_check')->alias('a')->field('b.dname,b.state,a.starttime,a.state as badstate,a.stext,a.endtime')
                    ->join('gx_list b','a.orstatus=b.id')
                    ->where("a.orderid={$v['id']}")->whereIn('orstatus', $gxId)
                    ->select();
            //行转成列
            foreach ($flowCheck as $k3 => $v3) {
            	if($v3['state']==1){
            		$orders[$k][$v3['dname'].'_starttime'] = $v3['starttime']!=0?date('Y-m-d',$v3['starttime']):'';
            	}
                $orders[$k][$v3['dname'].'_endtime'] = $v3['endtime']!=0?date('Y-m-d',$v3['endtime']):'';
                if(!isset($orders[$k]['badwrite'])){
                    $orders[$k]['badwrite'] = '';
                }
                if ($v3['badstate']==1){
                    $orders[$k]['badwrite'] .= $flowCheck[$k3]['dname'].'：'.$v3['stext'].';\r\n';
                }
            }
        }
        
        $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
        $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
        $exceltitle='订单汇总集合进度';

       $this->one_export2($fieldList, $orders, $creator, $exceltitle);
    }
    
    public function one_export2($title, $list, $creator, $exceltitle)
    {
		
        $export_array = $line_title_array = $field = array();
        //csv分割输出列表名
        $line_name = array();
        //存储英文字段和中文字段 =表头和数据字段
        foreach ($title as $key => $value) {
            if(isset($value['is_gx'])){
            	if($value['state']==1){
            		$field[] = $key.'_starttime';
            		$line_title_array [] = $key."\r\n开始时间";
            		$line_name[$key.'_starttime'] = $key."\r\n开始时间";
            	}
                 $field[] = $key.'_endtime';
                 $line_title_array [] = $key."\r\n结束时间";
                 $line_name[$key.'_endtime'] = $key."\r\n结束时间";
            }else{
                $field [] = $key;
                $line_title_array [] = $value['explains'];
                $line_name[$key] = $value['explains'];
            }
        }
        

        $doc = array('creator' => $creator, 'title' => $exceltitle,
            'subject' => $exceltitle, 'description' => $exceltitle,
            'keywords' => $exceltitle, 'category' => $exceltitle
        );
        
      
        $new_result = array();
        $new_titles = array();
        for ($i=0;$i<ceil(count($list)/1000);$i++){
            $index = $i*1000;
            $new_result['csv'.$i] = array_slice($list,$index,1000);
            $new_titles['csv'.$i] = $line_name;
        }
        if (count($field)>100){
            export_csv_zip($new_result,$new_titles,array('headTitle'=>$exceltitle));
            exit();
        }else {
            export_csv($list,$field,$line_title_array,array('title'=>$exceltitle));
            exit();
        }
    }

  
    /**
    * 员工数据的导出  
    * 个人的要完成的工序信息
    */
    public function manExcel(){
    $ugid = session("gid");
         $uid = input("staff/d");//员工筛选
         $gx_start = input("gx_start");//报工开始
         $gx_end = input("gx_end");//报工结束
         $gx = input('gx');//工序名称
         $nosend = input("nosend/d");//是否发货
         $nointo = input("nointo/d");//是否全部入库
         $where = '1=1 ';
         $wheres = "1=1";
         $result = array();
         $is_into = FIX_GX;
         $this->assign('msg',input(""));
         if (!empty($uid)){
             $where .= " and b.uid=$uid";
             $wheres .= " and b.uid=$uid";
         }
         if (!empty($gx)){
             $gxid = Db::name('gx_list')->where('dname',$gx)->find();
             empty($gxid)?$where .= " and b.orstatus=0":$where .= " and b.orstatus=".$gxid['id'];
             if ($is_into==0&&empty($gxid)){
                 $gxid = Db::name('into_gx')->where('name',$gx)->find();
                 $gxname = $gxid['name'];
                 $wheres .= " and b.name='$gxname'";
             }
         }
         if (!empty($gx_start)){
             $gx_start = strtotime($gx_start);
             $wheres .= " and UNIX_TIMESTAMP(b.into_time)>=$gx_start";
             $where .= " and b.endtime>=$gx_start";
         }
         if (!empty($gx_end)){
             $gx_end = strtotime('+1 day',strtotime($gx_end));
             $where .= " and b.endtime<$gx_end";
             $wheres .= " and UNIX_TIMESTAMP(b.into_time)<$gx_end"; 
         }
         if (!empty($nosend)){
             $where .= " and a.endstatus<2";
             $wheres .= " and a.endstatus<2";
         }
         if (!empty($nointo)){
             $where .= " and a.intime=0";
             $wheres .= " and a.intime=0";
         }
         //判断当前的人是否是超级管理员或者有没有权限查看全部订单-否则只可以查看自己和自己添加的组员下面的订单
         //if(1){
         //		$where.=" and a.ugid='$ugid' ";
         //}
         if(count($this->limitFlowId)>0){
         	$where.=" and b.id in (".implode(",",$this->limitFlowId).")";
         }
        
         //查出所有的报工数据
         $result = Db::name('order')->alias('a')->field('b.orstatus,b.starttime,b.in_num,b.endtime as bendtime,a.*,c.uname as name')
                    ->join("flow_check b", "b.orderid=a.id","LEFT")
                    ->join("login c", "c.id=b.uid","LEFT")
         			->where($where)->order('b.id asc')->select();
         //动态入库数据
         if ($is_into==0){
             $rest = Db::name('order')->alias('a')->field('b.name as gxname,b.count as in_num,b.into_time as bendtime,a.*,c.uname as name')
                    ->join("into_order_gx b", "b.orderid=a.id","LEFT")
                    ->join("login c", "c.id=b.uid","LEFT")
         			->where($wheres)->order('b.id asc')->select();
             foreach ($rest as $res){
                 array_push($result,$res);
             }
         }
         $doclass_list=@include APP_DATA.'doclass.php';
         $gx_line=@include APP_DATA.'lines.php';
         $gx_list=@include APP_DATA.'gx_list.php';
         $indata=array();
         $indata['doclass']=$doclass_list;
         $indata['gx_line']=$gx_line;
         $indata['gx_list']=$gx_list;
         
         //记录下所有工艺路线对应的小工序
//         $allGxList=array();
//         foreach($doclass_list as $did=>$value){
//         	$arr=array();
//         	$gx=gxlist_from_did_cache($did,$indata);
//         	foreach($gx as $v){
//         		$arr[]=$v['id'];
//         	}
//         	$allGxList[$did]=$arr;
//         }
         unset($indata);
         unset($doclass_list);
         unset($gx_line);
         unset($gx_list);
         
         //工序名称
         $gx = Db::name('gx_list')->where(array())->select();
         $compare_gx=array();
         foreach($gx as $value){
         	$compare_gx[$value['id']]=$value;
         }

         if ($result!==false&&count($result)>0){
             
         	//一次过查询所有的订单附表
         	$orderid=array();
         	foreach ($result as $key=>$res){
         		$orderid[]=$res;
         	}
         	
         	$attachs=array();
         	//每一百个为一组
         	$offset=100;
         	$groups=ceil(count($orderid)/$offset);
         	//$start=microtime(true);
         	for($i=0;$i<$groups;$i++){
         		$_orderid=array();
         		for($k=0;$k<$offset;$k++){
         			$index=$i*$offset+$k;
         			if(isset($orderid[$index])){
         				$_orderid[]=$orderid[$index];
         			}
         		}
         		
         		if(count($_orderid)>0){
         			$all_attach=order_attach($_orderid,array(),true);
         			foreach($all_attach as $value){
         				$attachs[$value['id']]=$value;
         			}
         			unset($all_attach);
         		}
         	}

         	$repeat=array();
         	//获取所有的数据
            foreach ($result as $key=>$res){
            	
            	//删除同一订单，同一工序重复报工
//             	if(isset($repeat[$res['id']][$res['orstatus']])){
//             		unset($result[$key]);
//             		continue;
//             	}else{
//             		$repeat[$res['id']][$res['orstatus']]=1;
//             	}

            	if(empty($res['unique_sn'])||empty($res['id'])){
            		unset($result[$key]);
            		continue;
            	}

            	if(!isset($attachs[$res['id']])){
            		unset($result[$key]);
            		continue;
            	}

            	//判断当前报工记录的工序id是否在该订单的工序内
//            	$did=$res['gid'];
//            	$orderGx=$allGxList[$did];
                $orderGx = combine_gx_line(explode(',',$res['gxline_id']));
            	$orderGx = array_column($orderGx,'id');
            	if(!isset($orderGx)||(!in_array($res['orstatus'], $orderGx)&&!empty($res['orstatus']))){
            		unset($result[$key]);
            		continue;
            	}

            	$order_attach=$attachs[$res['id']];
            	unset($order_attach['gid']);
            	$res=array_merge($res,$order_attach);
            	$result[$key]=$res;
				if(!isset($compare_gx[$res['orstatus']])&&!empty($res['orstatus'])){
					unset($result[$key]);
					continue;
				}
				
            	if($compare_gx[$res['orstatus']]['state']==2){
            		$result[$key]['starttime']='';
            	}else{
            		$result[$key]['starttime'] = $res['starttime']>0?date("Y-m-d",$res['starttime']):'';
            	}
            	$result[$key]['gxnum'] = $res['in_num'];
                $res['orstatus']?$result[$key]['orstatus']=$compare_gx[$res['orstatus']]['dname']:$result[$key]['orstatus']=$res['gxname'];
                $result[$key]['gxendtime'] =$res['bendtime']>0?date("Y-m-d",$res['bendtime']):'';
                $res['orstatus']?'':$result[$key]['gxendtime']=$res['bendtime'];
            }
         }
      
        unset($repeat);
//		unset($allGxList);
         $titles=array("name"=>"员工名称","orstatus"=>"工序",'starttime'=>"开始时间",'gxendtime'=>"结束时间","gxnum"=>"报工数量");
         
         //该报工项目的系统设置显示在列表的表头字段
         $fieldList=array();
         //读取字段
         $fields=@include APP_DATA.'qrfield_type.php';
         if(isset($fields['onlist'])){
         	foreach($fields['onlist'] as $value){
         		$fieldList[$value['fieldname']]=$value['explains'];
         	}
         }
         
         $titles=array_merge($titles,$fieldList);
  
         $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
         $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
         $exceltitle='员工数据统计';
         

        $new_result = array();
         $new_titles = array();
         for ($i=0;$i<ceil(count($result)/1000);$i++){
             $index = $i*1000;
             $new_result['csv'.$i] = array_slice($result,$index,1000);
             $new_titles['csv'.$i] = $titles;
         }
         if (count($fieldList)>100){
             export_csv_zip($new_result,$new_titles,array('headTitle'=>$exceltitle));
             exit();
         }else {
             export_csv($result,array_keys($titles),$titles,array('title'=>$exceltitle));
             exit();
         }
    }
    
    /**
     * 异常报表
     */
    public function exceptionData(){
        $time_start = input("time_start");
        $time_end = input("time_end");
        $isend = input("isending/d");
        $where = " a.state=1";
        $msg = "";
        if (!empty($time_start)){
            $msg .= "$time_start~";
            $time_start = strtotime($time_start);
            $where .= " and a.error_time >=$time_start";
        }
        if (!empty($time_end)){
            $msg .= "$time_end";
            $time_end = strtotime($time_end);
            $where .= " and a.error_time <=$time_end";
        }
        if (!empty($isend)){
            $where .= " and a.isback<2";
        }
        if (empty($msg)){
            $msg = "全部";
        }
        $result=Db::name("flow_check")->alias("a")->field("a.*,b.handle_time,b.text,b.uid as mid,c.dname,d.uname as cname")
                    ->join("check_back b","a.id=b.fid","LEFT")
                    ->join("gx_list c","a.orstatus=c.id")
                    ->join("login d","a.cid=d.id")->where($where)->order("a.error_time asc")->select();
        $list = array();
        if (!empty($result)){
            foreach ($result as $val){
                $row = array();
                $orderid = $val['orderid'];
                $order_msg = order_attach($orderid);
                $row['ordernum'] = $order_msg['ordernum'];
                $row['produce_sn'] = $order_msg['produce_sn'];
                $row['produce_no'] = $order_msg['produce_no'];
                $row['uname'] = $order_msg['uname'];
                $row['gxname'] = $val['dname'];
                $row['cname'] = $val['cname'];
                $row['name'] = '';
                $row['stext'] = $val['stext'];
                $row['text'] = $val['text'];
                $row['error_time'] = date("Y-m-d",$val['error_time']);
                !empty($val['handle_time'])?$row['handle_time']=date("Y-m-d",$val['handle_time']):$row['handle_time']='';
                if (!empty($val['mid'])){
                    $mid = $val['mid'];
                    $bname = Db::name("login")->where("id=$mid")->column("uname");
                    $row['name'] = $bname[0];
                    $row['isend'] = '是';
                }else {
                    $row['isend'] = '否';
                }
                array_push($list,$row);
            }
        }
            //标题
            $all_field = @include APP_DATA.'qrfield.php';
            $title =array();
            foreach ($all_field as $fl){
                $fieldname = $fl['fieldname'];
                $filed_arr = array();
                switch ($fieldname){
                    case 'ordernum':
                        $filed_arr = ['ordernum'=>$fl['explains']] ;
                        $title=array_merge($title,$filed_arr);
                        break;
                    case 'produce_sn':
                        $filed_arr = ['produce_sn'=>$fl['explains']];
                        $title=array_merge($title,$filed_arr);
                        break;
                    case 'produce_no':
                        $filed_arr = ['produce_no'=>$fl['explains']];
                        $title=array_merge($title,$filed_arr);
                        break;
                    case 'uname':
                        $filed_arr = ['uname'=>$fl['explains']];
                        $title=array_merge($title, $filed_arr);
                        break;
                    default:
                        break;
                }
            }
            $titles = ['gxname'=>'异常工序','stext'=>'异常问题','error_time'=>'发现时间','cname'=>'发现者','text'=>'处理方法','handle_time'=>'预计处理完成时间','name'=>'处理者','isend'=>'是否已竣工'];
            $title=array_merge($title,$titles);
            $cow_field = array_keys($title);
            $this->exceptionExcel('',$cow_field,$title,$list,$msg,"异常工序表");
            exit();
    }
    
    /**
     * 异常excel
     */
    public function exceptionExcel($group,$cow_filed,$title,$data,$msg,$excel_name){
        //创建excel对象
        $objExcel = new \PHPExcel();
        //创建导出格式
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel,'Excel5');
        //创建工作表sheet
        $objActSheet = $objExcel->getActiveSheet();
        
        //字母
        //最多导出字段数，可以继续增加
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y', 'Z');
        $Excel_letter = array();
        //动态生成列名
        $length = count($title);
        $need_num = ceil($length/26);
        if ($need_num>1){
            $Excel_letter=$letter;
            for ($i=0;$i<$need_num;$i++){
                for ($s=0;$s<count($letter);$s++){
                    $text = $letter[$i].$letter[$s];
                    array_push($Excel_letter,$text);
                }
            }
        }else {
            $Excel_letter=$letter;
        }
        //表样式设置
        //加边框
        $border = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),
        
            ),
        );
        //合并单元格
        $objActSheet->mergeCells("A1:L1");
        $objActSheet->setCellValue('A1',$msg);
        $num = count($data)+2;
        $objActSheet->getStyle('A1:'.$Excel_letter[count($title)-1].$num)->applyFromArray($border);
        for ($i=0;$i<count($title);$i++){
            $objActSheet->getStyle($Excel_letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objActSheet->getStyle($Excel_letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objActSheet->getStyle($Excel_letter[$i].'1')->getFont()->setBold(true);
            $objActSheet->getStyle($Excel_letter[$i].'2')->getFont()->setBold(true);
            $objActSheet->getColumnDimension($Excel_letter[$i])->setWidth(18);
        
        }
        //字段名称
        $ix=0;
        foreach ($title as $key=>$name){
            $objActSheet->setCellValue($Excel_letter[$ix].'2',$name);
            $ix++;
        }
            
        //赋值
        $a=3;
        for($i=0;$i<count($data);$i++){
                $index = $a+$i;
                for ($s=0;$s<count($cow_filed);$s++){
                    $position = $cow_filed[$s];
                    $objActSheet->setCellValue($Excel_letter[$s].$index,$data[$i][$position]);
                }
        }        
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$excel_name.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');
        
        $objWriter->save('php://output');
        exit();
    }
    
    public function otherData(){
        $gx_start = input("other_start");
        $gx_end = input("other_end");
        $all_field = @include APP_DATA.'qrfield.php';
        $kind = FIX_GX;
        $overtime_finish = input("overtime_finish/d");
        //超时完成工序
        if ($overtime_finish==1){
            $where = "a.status=1";
            $msg = "";
            if (!empty($gx_start)){
                $msg = "$gx_start~";
                $gx_start = strtotime($gx_start);
                $where .= " and a.endtime>=$gx_start";
            }
            if (!empty($gx_end)){
                $msg .= "~$gx_end";
                $gx_end = strtotime($gx_end);
                $where .= " and a.endtime<=$gx_end";
            }
            if (empty($gx_start)&&empty($gx_end)){
                $gx_start = strtotime(date("Y-01-01"));
                $gx_end = strtotime("+1 year",$gx_start);
                $where .= " and a.endtime>=$gx_start and a.endtime<$gx_end";
            }
            $msg==''?$msg='全部':'';
            //超时工序
            $result = Db::name("flow_check")->alias("a")->field("a.*,b.dname,b.worktime,c.uname")->join("gx_list b","a.orstatus=b.id")
                           ->join("login c","a.uid=c.id")->where($where)->order("a.id asc")->select();
            if (!empty($result)){
                $list = array();
                foreach ($result as $val){
                    $orderid = $val['orderid'];
                    $one_arr = array();
                    $one_arr['gxname'] = $val['dname'];
                    $one_arr['cname'] = $val['uname'];
                    $val['endtime']==0?$one_arr['finish_time'] = '':$one_arr['finish_time'] = date("Y-m-d",$val['endtime']);
                    if ($val['starttime']!=0){
                        $day = $val['worktime'];
                        $one_arr['starttime'] = date("Y-m-d",$val['starttime']);
                        $one_arr['demand_time'] = date("Y-m-d",strtotime("+$day day",date("Y-m-d",$val['starttime'])));
                    }else {
                        $one_arr['starttime'] = '';
                        $one_arr['demand_time'] = '';
                    }
                    $order_msg = order_attach($orderid);
                    $one_arr = array_merge($one_arr,$order_msg);
                    array_push($list,$one_arr);
                }
                //字段名
                $title = ['gxname'=>"工序名",'cname'=>'报工人','starttime'=>"开始时间",'finish_time'=>'结束时间','demand_time'=>"要求完成时间",'endtime'=>'订单完成时间'];
                foreach ($all_field as $fl){
                    $title[$fl['fieldname']] = $fl['explains'];
                }
                $cow_field = array_keys($title);
                $this->exceptionExcel('',$cow_field,$title,$list,$msg,"超时完成工序表");
            }else {
                $show_tip = "<div style='color:#fff'><span>暂无数据<span><a href='javascript:history.back(-1)' style='color:#3CB371;margin-left:20px'>返回</a></div>";
                echo $show_tip;
            }
        }
        //按时完成达成率
        if (input("ontime_finish/d")==1){
              $where = "pause=0 and repeal=0 and intime>0";
              $msg = "";
              if (!empty($gx_start)){
                   $msg = "$gx_start~";
                   $gx_start = strtotime($gx_start);
                   $where .= " and endtime>=$gx_start";
               }
               if (!empty($gx_end)){
                   $msg .= "~$gx_end";
                   $gx_end = strtotime($gx_end);
                   $where .= " and endtime<=$gx_end";
                }
               if (empty($gx_start)&&empty($gx_end)){
                   $gx_start = strtotime(date("Y-01-01"));
                   $gx_end = strtotime("+1 year",$gx_start);
                   $where .= " and endtime>=$gx_start and endtime<$gx_end";
               }
               $msg==''?$msg='全部':'';
               $result = Db::name("order")->where($where)->order("endtime asc")->select();
               if (!empty($result)){
                   $list = array();
                   foreach ($result as $val){
                       $data = array();
                       if ($val['intime']>$val['endtime']){
                           $data['isover'] = '是';
                       }else {
                           $data['isover'] = '否';
                       }
                       $data['intime'] = date("Y-m-d",$val['intime']);
                       $data['finish_time'] = date("Y-m-d",$val['endtime']);
                       $order_msg = order_attach($val['id']);
                       $data = array_merge($data,$order_msg);
                       array_push($list,$data);
                   }
                   //列表名
                   $title = ['isover'=>'是否超时完成','intime'=>'整单入库时间','finish_time'=>'订单完成时间'];
                   foreach ($all_field as $fl){
                       $title[$fl['fieldname']]=$fl['explains'];
                   }
                   $cow_field = array_keys($title);
                   $this->exceptionExcel('',$cow_field,$title,$list,$msg,"按时完成达成率订单表");
               }
        }
        //工序人均产值
        if (input("staff_average/d")==1){
            $title = array();
            $where = "1=1";
            foreach ($all_field as $fl){
                $title[$fl['fieldname']] = $fl['explains'];
            }
            if (!empty($gx_start)){
                $gx_start = strtotime($gx_start);
                $where .= " and a.endtime>=$gx_start";
            }
            if (!empty($gx_end)){
                $gx_end = strtotime($gx_end);
                $where .= " and a.endtime<=$gx_end";
            }
            if (empty($gx_start)&&empty($gx_end)){
                $gx_start = strtotime(date("Y-01-01"));
                $gx_end = strtotime("+1 year",$gx_start);
                $where = " a.endtime>=$gx_start and a.endtime<$gx_end";
            }
            $export = new IndexExport();
            $export->dayProduct($title,$where);
        }
        //生产计划达成率
        if (input("reach/d")==1){
            $export = new IndexExport();
            $export->schedule();
        }
        //部分、整单入库订单
        if (input("them_order/d")==1){
            $where = "pause=0 and repeal=0 and status>0";
            $msg = "";
            if (!empty($gx_start)){
                $msg = "$gx_start~";
                $gx_start = strtotime($gx_start);
                $where .= " and endtime>=$gx_start";
            }
            if (!empty($gx_end)){
                $msg .= "~$gx_end";
                $gx_end = strtotime($gx_end);
                $where .= " and endtime<=$gx_end";
            }
            if (empty($gx_start)&&empty($gx_end)){
                $gx_start = strtotime(date("Y-01-01"));
                $gx_end = strtotime("+1 year",$gx_start);
                $where .= " and endtime>=$gx_start and endtime<$gx_end";
            }
            $msg==''?$msg='全部':'';
            $result = Db::name("order")->where($where)->order("id asc")->select();
            if (!empty($result)){
                $list = array();
                foreach ($result as $val){
                    $orderid = $val['id'];
                    $gxline_id = $val['gxline_id'];
                    $gxname = "";
                    $isall = "";
                    $data = array();
                    if ($kind==1){
                        $gx_list = Db::name("gx_list")->alias("a")->field("a.dname,a.id")->join("gx_group b","a.gid=b.id")
                        ->where("a.lid in ($gxline_id) and b.inouts=1")->order("a.orderby asc")->select();
                        foreach ($gx_list as $key=>$gl){
                            $gxid = $gl['id'];
                            $check_data = Db::name("flow_check")->where("orderid=$orderid and orstatus=$gxid")->find();
                            if (!empty($check_data)){
                                $gxname .= $gl['dname']."【".$check_data['in_num']."】/";
                            }
                        }
                    }else {
                        $gx_list = Db::name("into_order_gx")->where("orderid=$orderid")->order("id asc")->select();
                        foreach ($gx_list as $gl){
                            $gxname .= $gl['name']."【".$gl['count']."】/";
                        }
                    }
                    
                    $val['endstatus']==2?$isall='全部':$isall='部分';
                    $data['isall'] = $isall;
                    $data['dname'] = $gxname;
                    $order_msg = order_attach($orderid);
                    $data = array_merge($data,$order_msg);
                    array_push($list,$data);
                }
                //列表名
                $title = ['isall'=>'是否全部入库','dname'=>'已入库内容'];
                foreach ($all_field as $fl){
                    $title[$fl['fieldname']] = $fl['explains'];
                }
                $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
                $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
                $exceltitle='部分/全部入库订单表';
                export_csv($list,array_keys($title),$title,array('title'=>$exceltitle));
                exit();
            }
        }
        //生产中的订单
        if (input("production_order/d")==1){
            $where = "pause=0 and repeal=0 and intime=0";
            $msg = "";
            if (!empty($gx_start)){
                $msg = "$gx_start~";
                $gx_start = strtotime($gx_start);
                $where .= " and endtime>=$gx_start";
            }
            if (!empty($gx_end)){
                $msg .= "~$gx_end";
                $gx_end = strtotime($gx_end);
                $where .= " and endtime<=$gx_end";
            }
            if (empty($gx_start)&&empty($gx_end)){
                $gx_start = strtotime(date("Y-01-01"));
                $gx_end = strtotime("+1 year",$gx_start);
                $where .= " and endtime>=$gx_start and endtime<$gx_end";
            }
            $msg==''?$msg='全部':'';
            $result = Db::name('order')->where($where)->order("id asc")->select();
            if (!empty($result)){
                $list = array();
                foreach ($result as $val){
                    $orderid = $val['id'];
                    $order_msg = order_attach($orderid);
                    array_push($list,$order_msg);
                }
                //列表名称
                $title = array();
                foreach ($all_field as $fl){
                    $title[$fl['fieldname']] = $fl['explains'];
                }
                $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
                $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
                $exceltitle='生产中订单表';
                export_csv($list,array_keys($title),$title,array('title'=>$exceltitle));
                exit();
            }
        }
        
       
    }
    
public function into_order_excel(){
        $starttime = input('into_start');
        $endtime = input('into_end');
        if (empty($starttime) || empty($starttime)){
            $this->error('请选择时间段');
        }
        $date_arr = array();
        $unix_date = array();
        $back_unix = array();
        $data = array();
        $total = array();
        $unix_day = 0;
        $day = 0;
        while (strtotime($endtime)>$unix_day){
            $unix_day = strtotime($starttime)+($day*24*3600);
            $month = date('m',$unix_day).'月'.date('d',$unix_day).'日';
            array_push($date_arr,$month);
            array_push($unix_date,$unix_day);
            $day++;
        }
        $back_unix = $unix_date;
        arsort($back_unix);
        $endtime = strtotime('+1 day',strtotime($endtime))-1;
        $genre = Db::name('series_genre')->order('id asc')->select();
        $order = Db::name('order')->where("pause=0 and repeal=0")->order('id desc')->select();
        $add_order = Db::name('order')->where("pause=0 and repeal=0 and addtime between ".strtotime($starttime)." and $endtime")->order('id desc')->select();
        $finish_order = Db::name('order')->where("pause=0 and repeal=0 and intime between ".strtotime($starttime)." and $endtime")->order('id desc')->select();
        if ($genre){
            foreach ($genre as $key=>$gk){
                $into_data = array();
                $into_data[0] = array();
                $into_data[1] = array();
                $into_data[2] = array();
                $into_data[3] = array();
                $into_data[4] = array();
                $into_data[5] = array();
                $into_data[6] = array();
                //获取绑定的系列
                $series = Db::name('series')->where("gid",$gk['id'])->group('xname')->column('xname');
                foreach ($unix_date as $k=>$ud){
                    $next_time = $ud+(24*3600);
                    $num=$area=$into_num=$into_area=$fact_day=0;
                    $nums = 0;
                    //接单数
                    foreach ($add_order as $ord){
                        $orderid = $ord['id'];
                        if (in_array($ord['pname'],$series)&&$ord['addtime']>=$ud&&$ord['addtime']<$next_time){
//                             $order_detail = order_attach($ord['id']);
                            $num +=  intval($ord['snum']);
                            $area += $ord['area'];
                        }
                    }
                    //入库数&实际周期
                    foreach ($finish_order as $ford){
                        if (in_array($ord['pname'],$series)&&$ord['intime']>=$ud&&$ord['intime']<$next_time){
                            $into_num += $ord['snum'];
                            $into_area += $ord['area'];
                            //计算完成订单周期
                            $finish_time = $ord['intime'];
                            //查找第一报工时间
                            $flow_check = Db::name('flow_check')->where("orderid=$orderid")->order('starttime asc,endtime asc')->find();
                            if ($flow_check['starttime']>0){
                                $fact_day += ceil(($finish_time-$flow_check['starttime'])/(24*3600));
                            }else {
                                $fact_day += ceil(($finish_time-$flow_check['endtime'])/(24*3600));
                            }
                            $nums++;
                        }
                    }
                    $nums>0?$fact_day = round($fact_day/$nums,2):'';
                    array_push($into_data[0],$num);
                    array_push($into_data[1],$area);
                    array_push($into_data[2],$into_num);
                    array_push($into_data[3],$into_area);
                    array_push($into_data[6],$fact_day);
                }
                $add_num=$add_area=$unfinish_num=$unfinish_area=$finish_num=$finish_area=0;
                //未完成订单
                $kc=0;
                foreach ($back_unix as $bux){
                    $next_times = $bux+(24*3600);
                    $add_nums=$add_areas=$unfinish_nums=$unfinish_areas=$finish_nums=$finish_areas=0;
                    foreach ($order as $ord){
                        //未完成
                        if(in_array($ord['pname'],$series)&&$ord['intime']==0&&$kc==0){
                            $unfinish_nums += $ord['snum'];
                            $unfinish_areas += $ord['area'];
                        }
                        //已完成
                        if (in_array($ord['pname'],$series)&&$ord['intime']>=$bux&&$ord['intime']<$next_times){
                            $finish_nums += $ord['snum'];
                            $finish_areas += $ord['area'];
                        }
                        //当天添加
                        if (in_array($ord['pname'],$series)&&$ord['addtime']>=$bux&&$ord['addtime']<$next_times){
                            $add_nums += $ord['snum'];
                            $add_areas += $ord['area'];
                        }
                    }
                
                if ($kc==0){
                    array_push($into_data[4],$add_nums);
                    array_push($into_data[5],$add_areas);
                    $unfinish_num = $unfinish_nums;
                    $unfinish_area = $unfinish_areas;
                }else {
                    $before_num = $unfinish_num+$finish_num-$add_num;
                    $before_area = $unfinish_area+$finish_area-$add_area;
                    array_unshift($into_data[4],$before_num);
                    array_unshift($into_data[5],$before_area);
                    $unfinish_num = $before_num;
                    $unfinish_area = $before_area;
                }
                $add_num = $add_nums;$add_area = $add_areas;$finish_num = $finish_nums;$finish_area = $finish_areas;
                $kc++;
               }
                //计算平均数和总和
                for ($i=0;$i<count($into_data);$i++){
                    if ($i==1){
                        for ($s=0;$s<count($into_data[$i]);$s++){
                            $total[0][$s] += $into_data[$i][$s];
                        }
                    }
                    if ($i==3){
                        for ($s=0;$s<count($into_data[$i]);$s++){
                            $total[1][$s] += $into_data[$i][$s];
                        }
                    }
                    if ($i==5){
                        for ($s=0;$s<count($into_data[$i]);$s++){
                            $total[2][$s] += $into_data[$i][$s];
                        }
                    }
                    $total_num=array_sum($into_data[$i]);
                    $avg = round($total_num/count($into_data[$i]),2);
                    array_unshift($into_data[$i],$total_num,$avg);
                }
                array_push($data,$into_data);
            }
        }else{
            $into_data = array();
            $into_data[0] = array();
            $into_data[1] = array();
            $into_data[2] = array();
            $into_data[3] = array();
            $into_data[4] = array();
            $into_data[5] = array();
            $into_data[6] = array();
            foreach ($unix_date as $k=>$ud){
                $next_time = $ud+(24*3600);
                $num=$area=$into_num=$into_area=$fact_day=0;
                $nums = 0;
                //接单数
                foreach ($add_order as $ord){
                    $orderid = $ord['id'];
                    if ($ord['addtime']>=$ud&&$ord['addtime']<$next_time){
                        //                             $order_detail = order_attach($ord['id']);
                        $num +=  intval($ord['snum']);
                        $ord['area']?$area += $ord['area']:$area += 0;
                    }
                }
                //入库数&实际周期
                foreach ($finish_order as $ford){
                    if ($ord['intime']>=$ud&&$ord['intime']<$next_time){
                        $into_num += $ord['snum'];
                        $into_area += $ord['area'];
                        //计算完成订单周期
                        $finish_time = $ord['intime'];
                        //查找第一报工时间
                        $flow_check = Db::name('flow_check')->where("orderid=$orderid")->order('starttime asc,endtime asc')->find();
                        if ($flow_check['starttime']>0){
                            $fact_day += ceil(($finish_time-$flow_check['starttime'])/(24*3600));
                        }else {
                            $fact_day += ceil(($finish_time-$flow_check['endtime'])/(24*3600));
                        }
                        $nums++;
                    }
                }
                $nums>0?$fact_day = ceil($fact_day/$nums):$fact_day=0;
                array_push($into_data[0],$num);
                array_push($into_data[1],round($area,2));
                array_push($into_data[2],$into_num);
                array_push($into_data[3],round($into_area,2));
                array_push($into_data[6],$fact_day);
            }
            $add_num=0;$add_area=0;$unfinish_num=0;$unfinish_area=0;$finish_num=0;$finish_area=0;
            //未完成订单
            $kc=0;
            foreach ($back_unix as $bux){
                $next_times = $bux+(24*3600);
                $add_nums=0;$add_areas=0;$unfinish_nums=0;$unfinish_areas=0;$finish_nums=0;$finish_areas=0;
                foreach ($order as $ord){
                    //未完成
                    if($ord['intime']==0&&$kc==0){
                        $unfinish_nums += $ord['snum'];
                        $ord['area']?$unfinish_areas += $ord['area']:$unfinish_areas += 0;
                    }
                    //已完成
                    if ($ord['intime']>=$bux&&$ord['intime']<$next_times){
                        $finish_nums += $ord['snum'];
                        $ord['area']?$finish_areas += $ord['area']:$finish_areas += 0;
                    }
                    //当天添加
                    if ($ord['addtime']>=$bux&&$ord['addtime']<$next_times){
                        $add_nums += $ord['snum'];
                        $ord['area']?$add_areas += $ord['area']:$add_areas += 0;
                    }
                }
                
                if ($kc==0){
                    array_push($into_data[4],$unfinish_nums);
                    array_push($into_data[5],round($unfinish_areas,2));
                    $unfinish_num = $unfinish_nums;
                    $unfinish_area = round($unfinish_areas,2);
                }else {
                    $before_num = $unfinish_num+$finish_num-$add_num;
                    $before_area = $unfinish_area+$finish_area-$add_area;
                    array_unshift($into_data[4],$before_num);
                    array_unshift($into_data[5],$before_area);
                    $unfinish_num = $before_num;
                    $unfinish_area = $before_area;
                }
                $add_num = $add_nums;$add_area = round($add_areas,2);$finish_num = $finish_nums;$finish_area = round($finish_areas,2);
                $kc++;
            }
            //计算平均数和总和
            for ($i=0;$i<count($into_data);$i++){
                if ($i==1){
                    for ($s=0;$s<count($into_data[$i]);$s++){
                        $total[0][$s] += $into_data[$i][$s];
                    }
                }
                if ($i==3){
                    for ($s=0;$s<count($into_data[$i]);$s++){
                        $total[1][$s] += $into_data[$i][$s];
                    }
                }
                if ($i==5){
                    for ($s=0;$s<count($into_data[$i]);$s++){
                        $total[2][$s] += $into_data[$i][$s];
                    }
                }
                $total_num=array_sum($into_data[$i]);
                $avg = round($total_num/count($into_data[$i]),2);
                array_unshift($into_data[$i],$total_num,$avg);
            }
            array_push($data,$into_data);
        }
        into_order_excel($data,$total,$date_arr);
    }
    
    /**
     * 打印异常
     */
    public function exceptionTemplate()
    {
    	$this->assign('style','total.css');
    	return $this->fetch('exception');
    }
   
}