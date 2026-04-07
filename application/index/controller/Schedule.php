<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Facade\Env;
use think\facade\Request;
class Schedule extends Super
{
	//循环获取多级班组数组
	private $team;
	//唯一字段名称
	private $unique_field;
	//唯一字段中文名称
	private $unique_name;
	//ordernum的中文名
	private $ordernum_explain;
	
    public function initialize(){
        parent::initialize();
        if(PRO_PAICHAN!=1){
        	exit("当前项目未开启排产功能");
        }
        
        $action = strtolower(Request::action());
        $navi[$action]='me';
        $this->assign("navi",$navi);
        
        //唯一字段
        $unique_field="";
        if(isset($this->system['orderfield'])&&!empty($this->system['orderfield'])){
        	$unique_field=$this->system['orderfield'];
        }else{
        	$unique_field="ordernum";
        }
        $this->assign("unique_field",$unique_field);
        $this->unique_field=$unique_field;
        
        //读取字段
        $fields=@include APP_DATA.'qrfield_type.php';
        $allfield=@include APP_DATA.'qrfield.php';
        $fieldList=array();
        if(isset($fields['onlist'])){
        
        	foreach($fields['onlist'] as $value){
        		$fieldList[$value['fieldname']]=$value;
        	}
        
        }
        
        $search_field=array();
        $searchData=array();
        //唯一字段中文名称
        $unique_field_name="";
        if(isset($fields['qrcode'])){

            foreach($fields['qrcode'] as $value){
                if($value['fieldname']=='ordernum'){
                    $this->ordernum_explain=$value['explains'];
                }
                if($value['fieldname']==$unique_field){
                    $unique_field_name=$value['explains'];
                    continue;
                }
            }

        }
        foreach ($allfield as $val){
            if($val['search']==1){
				$search_field[$val['fieldname']]=$val['explains'];
				$searchData[$val['fieldname']]=$val;
            }
        }
        //高级搜索字段
		$this->assign("searchField",$search_field);
		$this->assign("searchData",$searchData);
        //显示唯一字段名
        $this->assign("unique_field_name",$unique_field_name);
        $this->unique_name=$unique_field_name;
        //显示列表字段
        $this->assign("fieldList",$fieldList);
        
        //排序列表
        $orderby=array();
        foreach($fieldList as $fieldname=>$value){
        	$order=$fieldname."|asc";
        	$orderby[$order]=$value['explains']."_升序";
        	$order=$fieldname."|desc";
        	$orderby[$order]=$value['explains']."_降序";
        }
        $this->assign("fieldListOrderby",$orderby);
    }
    
    //排产首页
    public function index(){
    	$uid = session('gid');
    	//订单工序筛选--当前用户组添加的工序
    	$gx_list = Db::name('gx_list')->distinct(true)->field("dname")->order("orderby asc,id asc")->select();
    	if($gx_list===false||count($gx_list)<=0){
    		$gx_list=array();
    	}
    	$this->assign('gx_list',$gx_list);
    	
    	//颜色
    	$color=Db::name("order")->field("color")->group("color")->select();
    	if($color!==false){
    		$this->assign('color',$color);
    	}
  
    	//切换按订单汇总还是按工序汇总模板
    	$template="index";
    	if(!empty(input("schedule_group"))){
    		$template="schedule_group";
    	} else if(!empty(input("schedule_order"))){
			$template="schedule_order";
		}
    	
    	return $this->fetch($template);
    }
    
    //公共搜索
    private function getWhere($include_finish=false){
    	
    	if(!$include_finish){
    		$where = "a.endstatus!='2' and a.pause!=1 and a.repeal!='1'";
    	}else{
    		$where = "1=1";
    	}
    	
    	$limit=$this->role_limit();//判断角色显示对应订单记录
    	if($limit!=''){
    		$where.=" and ".$limit;
    	}
    	
    	$color = ctrim(input("param.color"));
    	$s_date = ctrim(input("param.s_date"));
    	$e_date = ctrim(input("param.e_date"));
    	$xs_date = ctrim(input("param.xs_date"));
    	$xe_date = ctrim(input("param.xe_date"));
    	$gname = htmlspecialchars(input("param.gname"));//工序名称
    	$color= ctrim(input("param.color"));//颜色
    	$gx_status=intval(input("param.gx_status"));//工序状态
    	$order_status=intval(input("param.order_status"));//订单状态
    	$orderby=ctrim(input("param.orderby"));//排序方式
    	
    	//日期筛选
    	$s_date = $s_date!=''?ymktime($s_date):0;
    	$e_date = $e_date!=''?ymktime($e_date)+24*60*60-1:0;
    	 
    	if (!empty($s_date) && empty($e_date)){
    		$where .= "and a.ordertime>=$s_date";
    	}elseif (!empty($e_date) && empty($s_date)){
    		$where .= "and a.ordertime<=$e_date";
    	}elseif (!empty($e_date) && !empty($s_date)){
    		$where .= "and a.ordertime between $s_date and $e_date";
    	}
    	 
    	//颜色
    	if (!empty($color)){
    		$where .= " and a.color like '%$color%' ";
    	}
    	 
    	//预警订单
    	if(!empty($iswarn)){
    		$day = Db::name('warm_time')->field("day")->find();
    		$facttime = time()+($day['day']*24*3600);
    		if($iswarn==1){//是
    			$where .= " and a.endstatus=1 and a.endtime<$facttime ";
    		}else{
    			$where .= " and a.endtime>=$facttime ";
    		}
    	}
    	 
    	if (!empty($gname)&&!empty($gx_status)){
    		$Gx=array();
    		if(strpos($gname, ",")!==false){
    			$Gx=explode(",", $gname);
    		}else{
    			$Gx[]=$gname;
    		}
    		$Gx=array_unique($Gx);
    		$fGx=$nameGx=array();
    		foreach($Gx as $key=>$value){
    			if(trim($value)!=''){
    				$f=ctrim($value);
    				$fGx[]=$f;
    				$nameGx[]="dname like'%$f%'";
    			}
    		}
    		//查找
    		$gxs = Db::name("gx_list")->field("id,did,state")->where("1=1 and (".implode(" or ",$nameGx).")")->select();
    		$gxId = array();
    		foreach ($gxs as $t){
    			$gxId[]=$t['id'];
    		}
    		
    		//搜索工序状态
    		if($gx_status==1){//未开始
    			if(count($gxId)<=0){
    				//没符合的记录，不显示订单
    				$where .= " and a.id='0' ";
    			}else{
    				//查找所有拥有这些工序名的did
//    				$dids=getdid_from_gxname($fGx);

                    //从工序id获取工艺线Id
                    $gxlineId = getlineid_from_gxname($fGx);
                    if(count($gxlineId)<=0){//没有可用的doclass
    						$where .= " and a.id='0'";
    				}else{
    					//查找符合多个工序未开始的订单
//    					$orderId=M("order")->where("gid in (".implode(",",$dids).")")->column("id");
    				    $more_sql = "";
                        foreach ($gxlineId as $key=>$gl){
                            if ($key>0){
                                $more_sql .= "or";
                            }
                            $more_sql .= " CONCAT (',',gxline_id,',') REGEXP ',($gl),' ";
                        }
                        $orderId=M("order")->where("$more_sql")->column("id");

    					if($orderId&&count($orderId)>0){
    						
    						//一次过查询这些订单已经报工的记录
    						$checks=M("flow_check")
    						->where("orderid in (".implode(",",$orderId).")")
    						->field("orderid,orstatus")
    						->select();
    						
    						$oid=array();
    						foreach($orderId as $id){
    							$oid[$id]=$id;
    						}
    						//只要有一个已经报工都不返回该订单id
    						foreach($checks as $value){
    							if(in_array($value['orstatus'], $gxId)){
    								unset($oid[$value['orderid']]);
    							}
    						}
    						$oid=count($oid)>0?$oid:array('0');
    						$where .= " and a.id in (".implode(",",$oid).")";
    					}else{
    						$where .= " and a.id='0'";
    					}
    				}
    					
    			}
    			 
    		}
    	}
    	 
    	//完成工序
    	$finGxName=input("finishGx");
    	if(!empty($finGxName)){
    		$finGx=$fGx=$nameGx=array();
    		if(strpos($finGxName, ",")!==false){
    			$finGx=explode(",", $finGxName);
    		}else{
    			$finGx[]=$finGxName;
    		}
    		$finGx=array_unique($finGx);
    		foreach($finGx as $key=>$value){
    			if(trim($value)!=''){
    				$f=ctrim($value);
    				$fGx[]=$f;
    				$nameGx[]="dname='$f'";
    			}
    		}
    		
    		if(count($fGx)>0){
    			$gxId = Db::name("gx_list")->where("1=1 and (".implode(" or ",$nameGx).")")->column("id");
    			//查找所有拥有这些工序名的did
    			$dids=getlineid_from_gxname($fGx);
    			if(count($dids)<=0){//没有可用的doclass
    				$where .= " and a.id='0'";
    			}else{
    				//查找用了限制的$dids工艺路线内的订单--并且查询的多个工序名已完成
    			     $more_sql = "";
                    foreach ($dids as $key=>$ds){
                        if ($key>0){
                            $more_sql .= "or";
                        }
                        $more_sql .= " CONCAT (',',gxline_id,',') REGEXP ',($ds),' ";
                    }
                    $orderId = M("order")->where("$more_sql")->column("id");
    				if($orderId&&count($orderId)>0){
    					$orders=M("flow_check")
    							->where("orstatus in (".implode(",",$gxId).") and endtime>0 and orderid in (".implode(",",$orderId).")")
    							->field("orderid,orstatus")
    							->select();
    					
    					if($orders&&count($orders)>0){
    						$temp=array();
    						foreach($orders as $ov){
    							$temp[$ov['orderid']][$ov['orstatus']]=$ov['orstatus'];//每个订单的完成工序
    						}
    						
    						$finGxLen=count($fGx);
    						$oid=array();
    						//遍历订单，必须有符合搜索完成工序相等数量的订单已报工序才返回
    						foreach($temp as $orderid=>$ov){
    							if($finGxLen==count($ov)){
    								$oid[]=$orderid;
    							}
    						}
    						$oid=count($oid)>0?$oid:array('0');
    						$where .= " and a.id in (".implode(",",$oid).")";
    					}else{
    						$where .= " and a.id='0'";
    					}
    				}else{
    						$where .= " and a.id='0'";
    				}
    			}
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
    	
    	return $where;
    }
    
    
    //获取订单
    public function ajax_list(){
    	
    	$where.=$this->getWhere(false);
		
		$page=input("page")?input("page"):1;
    	$offset=50;
    	$start=$offset*($page-1);
    	
    	$order_sql="endtime asc";
    	//排序方式
    	$orderby=input("orderby");
    	
    	//@hj 2020/03/05 自定义高级搜索
    	$searchsql=$this->senior_search("a.");
    	if($searchsql!=''){
    		$where.=" and ".$searchsql;
    	}

    	//获取订单列表
    	if(empty($orderby)||$orderby=='day|asc'||$orderby=='day|desc'||$orderby=='id|desc'){
    		$orderby=explode("|",$orderby);
    		$order_sql=$orderby[0]." ".$orderby[1];
    		$order_lis = Db::name('order')->alias("a")
    		->where("$where and a.endstatus!=2")->order($order_sql)->limit($start,$offset)->select();
    	}else{
    		$prefix=config("database.prefix");//数据库前缀
    		$ofield=explode("|",ctrim(input("orderby")));//排序字段
    		$order_name=$ofield[0];
    		$order_act=$ofield[1];
    		//$order_act_p=$order_act=='asc'?'desc':'asc';
    		$order_lis = Db::name('order')->field("a.*")->alias("a")->join("(SELECT `orderid`,`value` FROM `".$prefix."order_attach` WHERE `fieldname`='$order_name' ORDER by `value` $order_act) c ","c.orderid=a.id","LEFT")
    		->where("$where and a.endstatus!=2")->order("c.`value` $order_act")->limit($start,$offset)->select();
    	}
    	//echo Db::name('order')->getLastSql();
    	//exit();
    	//读取缓存
    	if($order_lis===false||count($order_lis)<=0){
    		return array('status'=>'2','msg'=>'无订单结果');
    	}
    	
    	$orderId=array();
    	foreach($order_lis as $value){
    		$orderId[]=$value['id'];
    	}
    	//查找所有订单的已完成的报工记录，已完成的就不显示未排产工序
    	$flowCheck=M("flow_check")
    				->field("id,orderid,orstatus")
    				->where("orderid in (".implode(",",$orderId).") and orstatus>0 and (endtime>0 or starttime>0)")
    				->select();
    	$checkList=array();
    	if($flowCheck!==false&&count($flowCheck)>0){
    		foreach($flowCheck as $value){
    			$oid=$value['orderid'];
    			$checkList[$oid][]=$value;
    		}
    	}
    	//合并字段
    	$order_lis=order_attach($order_lis);
    	
    	foreach ($order_lis as $key=>$order){
    			$orderid = $order['id'];
    			$did = $order['gid'];
    			$day=$order['day'];
    			
    			if($order['ng']==1){//使用新工艺线下的单
    			    $line_id = explode(',',$order['gxline_id']);
    				$gx_list= combine_gx_line($line_id);
    			}else{
    				$gx_list = Db::name('gx_list')->where("did=$did")->order("orderby asc")->select();
    			}
    		
    			//筛选出那些未排产的工序
    			$gx_schedule=$order['gx_schedule'];
    			if($gx_schedule!=''){
    				$gx_schedule=unserialize($gx_schedule);
    				foreach($gx_list as $k=>$value){
    					if(in_array($value['dname'], $gx_schedule)){
    						unset($gx_list[$k]);
    					}
    				}
    			}
    			
    			//如果该订单某个工序已经完成报工就不显示未排产工序
    			if(isset($checkList[$orderid])){
    				foreach($checkList[$orderid] as $li){
    					foreach($gx_list as $k=>$value){
	    					if($li['orstatus']==$value['id']){
	    						unset($gx_list[$k]);
	    						break;
	    					}
    					}
    				}
    			}
    			if(count($gx_list)>0){
    				$order_lis[$key]['gx_list']=$gx_list;
    			}else{
    				//unset($order_lis[$key]);
    			}
    			
    	}
    	
    	return array('status'=>'1','list'=>$order_lis);
    }
    
    //按工序分组显示
    public function ajax_listgx(){
    
    	//所有工艺路线
    	$line=Db::name("gx_line")->order("id asc")->column("id");
    	if(count($line)>0){
    		$sql=implode(",",$line);
    		//读取下面的所有工序
    		$gx=Db::name("gx_list")->where("lid in ($sql)")->order("orderby asc")->field("id,lid,dname,work_value,work_unit,orderby")->select();
    		if(!$gx||count($gx)<=0){
    			return array('status'=>'2','msg'=>'请设置工序');
    		}
    	}else{
    		return array('status'=>'2','msg'=>'请设置工艺路线');
    	}
    	
    	//根据工序名称组合工序ID
    	//$list 结构:array('dname工序名'=>array('id1','id2'..))
    	$gxlist=array();
    	foreach($gx as $key=>$value){
    		$gxlist[$value['dname']][]=$value['id'];
    	}

    	//搜索
    	$where.=$this->getWhere(false);
    	$searchsql=$this->senior_search("a.");
    	if($searchsql!=''){
    		$where.=" and ".$searchsql;
    	}
    	
    	//缓存
    	$doclass_list=@include APP_DATA.'doclass.php';
    	$gx_line=@include APP_DATA.'lines.php';
    	$gx_list=@include APP_DATA.'gx_list.php';
    	$indata=array();
    	$indata['doclass']=$doclass_list;
    	$indata['gx_line']=$gx_line;
    	$indata['gx_list']=$gx_list;
    	
    	$fix_gx_id=@include APP_DATA.'fix_gx_id.php';
    	$needCheckFix=false;
    	if($fix_gx_id&&count($fix_gx_id)>0){
    		$needCheckFix=true;//有设置固定工序就需要检测工序下的订单前工序是否已完成
    	}

    	$allgxlist = @include APP_DATA.'gx_list.php';
    	//查询下面的所有订单
    	$list=array();
    	foreach($gxlist as $dname=>$gxids){
    		$list[$dname]['num']=0;
    		$list[$dname]['area']=0;
    		$list[$dname]['snum']=0;

//    		$did=getdid_from_gxid($gxids);
//    		if(count($did)>0){
//    			$sql=" and a.gid in (".implode(",",$did).")";
                $line_id=Db::name('gx_list')->whereIn("id",$gxids)->column("lid");
                $lineIdstr = implode('|',$line_id);
                $sql = " and CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'";
    			$orderList=M("order")
    						->alias("a")
    						->field("a.id,a.gid,a.ordertime,a.unique_sn,a.gx_schedule,a.endtime,a.isurgent,a.addtime,a.gxline_id")
    						->where($where.$sql)->order("a.isurgent desc,a.addtime asc,a.unique_sn desc")
    						->select();

    			if($orderList){
    				
    				$orderId=array();
    				foreach($orderList as $value){
    					$orderId[]=$value['id'];
    				}
    				//查找所有订单的已完成的报工记录，已完成的就不显示未排产工序
    				$flowCheck=M("flow_check")
    				->field("id,orderid,orstatus,endtime")
    				->where("orderid in (".implode(",",$orderId).") and orstatus>0 and (endtime>0 or starttime>0)")
    				->select();
    				$checkList=$checkListGxId=array();
    				if($flowCheck!==false&&count($flowCheck)>0){
    					foreach($flowCheck as $value){
    						$oid=$value['orderid'];
    						$checkList[$oid][]=$value;
    						if($value['endtime']>0){
    							//存起订单所有完成报工的工序id
    							$checkListGxId[$oid][]=$value['orstatus'];
    						}
    					}
    				}
    				
    				
    				foreach($orderList as $k=>$value){
    					
	    				$gx_schedule=$value['gx_schedule'];
	    				if($gx_schedule!=''){
	    					$gx_schedule=unserialize($gx_schedule);
	    					if(in_array($dname, $gx_schedule)){
	    						//删去该工序名称已在订单内排产的订单记录
	    						unset($orderList[$k]);
	    					}
	    				}
	    				
	    				$orderid=$value['id'];
	    				if(isset($checkList[$orderid])){
	    					foreach($checkList[$orderid] as $li){
	    						//如果该订单的该工序已报工，则删除该订单
	    						if(in_array($li['orstatus'], $gxids)){
	    							unset($orderList[$k]);
	    						}
	    					}
	    				}
	    				
	    				//该工序的前工序是否已经完成，未完成则不显示
	    				if($needCheckFix){

	    					//当前订单的doclass的id
//	    					$gid=$value['gid'];
//	    					//获取该订单的工艺线的所有小工序
//	    					$order_gxs=gxlist_from_did_cache($gid,$indata);
                            $order_gxs = combine_gx_line(explode(',',$value['gxline_id']),0,$allgxlist);
	    					if(!$order_gxs){
	    						unset($orderList[$k]);
	    						continue;
	    					}
	    					//获取当前工序名的id
	    					$current_gx_id=0;
	    					//当前订单所有工序的id
	    					$gxs_id=array();
	    					foreach($order_gxs as $val){
	    						$gx_id=$val['id'];
	    						$gxs_id[]=$gx_id;
	    						//在当前工序名的工序id数组内
	    						if(in_array($gx_id, $gxids)){
	    							$current_gx_id=$gx_id;
	    						}
	    					}

	    					if($current_gx_id==0){
	    						unset($orderList[$k]);
	    						continue;
	    					}

	    					//查找这个工序是否有设置工作流和找到父工序
	    					$parent=fixed_parent($fix_gx_id,$current_gx_id);
	    					if(!$parent['isSetting']||($parent['isSetting']&&count($parent['parent'])<=0)){
	    						//不需要检测
	    						continue;
	    					}

	    					$before_gx=$parent['parent'];

	    					//查询前工序是否在当前订单的工序里面
	    					foreach($before_gx as $bk=>$gx_id){
	    						if(!in_array($gx_id, $gxs_id)){
	    							unset($before_gx[$bk]);
	    						}
	    					}

	    					if(count($before_gx)<=0){
	    						continue;//没上级工序可以显示
	    					}else{

	    						if(isset($checkListGxId[$orderid])){
	    							$finish_parent=0;
	    							foreach($checkListGxId[$orderid] as $gx_id){
	    								if(in_array($gx_id, $before_gx)){
	    									$finish_parent++;
	    								}
	    							}
	    							if($finish_parent!=count($before_gx)){
	    								unset($orderList[$k]);
	    								continue;
	    							}
	    						}

	    					}

	    				}
	    				
    				}//end of foreach
    				
    				$now=time();
    				$daySecond=24*60*60;
    				$orderId=array();
    				foreach($orderList as $k=>$value){
    					if($value['endtime']>0){
    						$value['day']=floor(($value['endtime']-$now)/$daySecond);
    					}else{
    						$value['day']=0;
    					}
    					$orderId[]=$value['id'];
    					$orderList[$k]=$value;
    				}
    				
    				//按时间分组
    				$groupby=input("groupby");
    				if(!empty($groupby)){
    					$name=$groupby;
    					if($name=='ordertime'){//单据日期比较特殊，有可能是subtime或ordertime
    						$isSub=find_field("subtime");
    						if($isSub){
    							$name='subtime';
    						}else{
    							$isOrder=find_field("ordertime");
    							if($isOrder){
    								$name='ordertime';
    							}
    						}
    					}
    					
    					//要查附表-可以是其他字段，在$name=='subtime'后添加||即可
    					if($name=='subtime'){
    						if(count($orderId)>0){
    							$times=M("order_attach")
    									->field("orderid,`value`")
    									->where("fieldname='$name' and orderid in (".implode(",",$orderId).")")
    									->select();
    							if($times){
    								//遍历$orderList覆盖订单日期ordertime
    								foreach($orderList as $k=>$value){
    									$id=$value['id'];
    									foreach($times as $val){
    										if($id==$val['orderid']){
    											$orderList[$k][$name]=strtotime($val['value']);
    											break;
    										}
    									}
    								}
    							}	
    						}
    					}
    					
    					//对订单进行分组
    					$newList=array();
    					foreach($orderList as $value){
    						$time=date('Y-m-d',$value[$name]);
    						$newList[$time][]=$value;
    					}
    					$orderList=$newList;
    				}
    				
    				//统计面积和扇数
    				if(count($orderId)>0){
    					$area=M("order")->where("id in (".implode(",",$orderId).")")->sum("area");
    					$snum=M("order")->where("id in (".implode(",",$orderId).")")->sum("snum");
    					$list[$dname]['num']=count($orderId);
    					$list[$dname]['area']=round($area,2);
    					$list[$dname]['snum']=round($snum,2);
    					$list[$dname]['orders']=$orderList;
    				}else{
    					$list[$dname]['orders']=array();
    				}
    			}else{
    				$list[$dname]['orders']=array();
    			}
//    		}else{
//    				$list[$dname]['orders']=array();//没有工艺did，所以没订单
//    		}
    	}
    	return array('status'=>'1','list'=>$list);
    	
    }
    public function add_schedule_group(){
        $ids=input("data");
        if($ids==''){
            exit("请提交订单ID");
        }
        $data = json_decode($ids);
        $group_list = array();
        foreach ($data as $key=>$val){
            $arr = [];
            $oids=explode(",",$val->orderid);
            $order_id=array();
            foreach($oids as $value){
                $order_id[]=intval($value);
            }
             
            if(count($order_id)<=0){
                exit("请提交订单ID");
            }
             
            $sql=implode(",",$order_id);
            //查询选择了的订单
            $list=Db::name("order")->where("id in ($sql)")->order("endtime asc")->select();
             
            //订单数量，面积和扇数
            $order_count=0;
            $square=0;
            $fans=0;
            $doornum=0;
            $isSub=find_field("subtime");
            //合并字段
            $list=order_attach($list);
             
            foreach($list as $key=>$order){
                $order_count++;
                $square+=floatval($order['area']);
				$fans+=floatval($order['snum']);
				$doornum+=floatval($order['doornum']);
                if($isSub){
                    $list[$key]['ordertime']=$order['subtime'];
                }
            }
    
            //显示对应班组和工序
            $gxid = $val->gxid;
            $group_id = $val->group_id;
            $dname = Db::name('gx_list')->field('id,dname')->where("id=$gxid")->find();
            $group = Db::name('team')->field('id,team_name,pid')->where("id=$group_id")->find();
            $group_p = Db::name('team')->field('team_name')->where('id',$group['pid'])->find();
            $arr['order_count'] = $order_count;
            $arr['square'] = $square;
            $arr['fans'] = $fans;
            $arr['doornum'] = $doornum;
            $arr['list'] = $list;
            $arr['gx_list'] = $dname;
            $arr['team'] = $group;
            $arr['team_main'] = $group_p;
            array_push($group_list,$arr);
            //             $this->assign("list",$list);
            //             $this->assign("order_count",$order_count);
            //             $this->assign("square",$square);
            //             $this->assign("fans",$fans);
        }
        //         echo json_encode($group_list);
        //         exit();
        $this->assign('group_list',$group_list);
         
         
        //查询所有的工序小组
        //获取所有班组数组
        //         $this->team=array();
        //         $this->get_team('0');
        //         $this->assign("team",$this->team);
    
        //读取字段
        $fields=@include APP_DATA.'qrfield.php';
        $fieldList=array();
        if($fields){
            foreach($fields as $value){
                $fieldList[$value['fieldname']]=$value;
                if($value['fieldname']=='subtime'){
                    $fieldList['ordertime']=$value;
                }
            }
        }
        //显示列表字段
        $this->assign("fieldList",$fieldList);
    
        //有提交工序参数
        //         $dname=input("dname");
        //         $select_tid=0;//当前自动选择班组
        //         $limitTeam=array();//限定班组
        //         $limitGx=array();//限制工序
        //         if(!empty($dname)){
    
        //             //班组绑定工序
        //             $teams=M("team")->order("id asc")->select();
        //             $allteam=array();
        //             foreach($teams as $k=>$value){
        //                 $allteam[$value['id']]=$value;
        //             }
        //             $team_gx=M("team_gx")->field("id,tid,ngx_id")->select();
        //             $team=array();
        //             foreach($team_gx as $value){
        //                 $ngx_id=unserialize($value['ngx_id']);
        //                 foreach($ngx_id as $lid=>$arr){//lid=>array(15,16...)多工序
        //                     foreach($arr as $id){
        //                         $team[$value['tid']][]=$id;
        //                     }
        //                 }
        //             }
    
        //             $gxs=M("gx_list")->field("id")->where("dname='$dname'")->select();
        //             $limitGx[]=$dname;
        //             if($gxs&&count($gxs)>0){
         
        //                 foreach($gxs as $value){
        //                     foreach($team as $tid=>$gxid){
        //                         if(in_array($value['id'], $gxid)){
        //                             if(isset($allteam[$tid])){
        //                                 $select_tid=$tid;
        //                                 $limitTeam[$tid]=$allteam[$tid];
        //                             }
    
        //                         }
        //                     }
        //                 }
        //                 $this->assign("limitTeam",$limitTeam);
        //             }
        //         }
        //         $this->assign("select_tid",$select_tid);
        //         $this->assign("limitGx",$limitGx);
         
        return $this->fetch();
    }
    
    //按班组分组汇总
    public function ajax_list_group(){
    //所有班组
        $group=Db::name("team_gx")->order("id asc")->column("tid");
        if(count($group)>0){
            $sql=implode(",",$group);
            $group_gx=Db::name("team")->alias('a')->field("a.id,a.team_name,b.ngx_id")->join("team_gx b","a.id=b.tid")->whereIn('a.id',$sql)->order("a.id asc")->select();
        }else{
            return array('status'=>'2','msg'=>'请设置班组工序');
        }

        //根据工序名称组合工序ID
        //$list 结构:array('dname工序名'=>array('id1','id2'..))
        $group_list=array();
        foreach($group_gx as $key=>$value){
            $gx_arr=unserialize($value['ngx_id']);
            $gx_ids = array();
            foreach ($gx_arr as $gr){
                foreach ($gr as $lg){
                    array_push($gx_ids,$lg);
                }
            }
            $group_gx[$key]['gx_id'] = [];
            if(count($gx_ids)>0){
                $group_gx[$key]['gx_id']=$gx_ids;
            }
        }
        //搜索
        $where.=$this->getWhere(false);
        $searchsql=$this->senior_search("a.");
        if($searchsql!=''){
            $where.=" and ".$searchsql;
        }
         
        //缓存
        $gx_list=@include APP_DATA.'gx_list.php';
        $new_gx_list=array();
         foreach ($gx_list as $gls){
             $new_gx_list[$gls['id']] = $gls;
         }
//         $fix_gx_id=@include APP_DATA.'fix_gx_id.php';
//         $needCheckFix=false;
//         if($fix_gx_id&&count($fix_gx_id)>0){
//             $needCheckFix=true;//有设置固定工序就需要检测工序下的订单前工序是否已完成
//         }

        //查询下面的所有订单
        $list=array();
        foreach ($group_gx as $k=>$gl){
            $list[$gl['team_name']] = array();
            foreach($gl['gx_id'] as $key=>$gxids){
                $data = array();
                $data['num']=0;
                $data['area']=0;
                $data['snum']=0;
                $gxname=$new_gx_list[$gxids]['dname'];
//                $line_id=Db::name('gx_list')->whereIn("id",$gxids)->column("lid");
                $line_id = getlineid_from_gxid($gxids,$gx_list);
                $lineIdstr = implode('|',$line_id);
                $sql = " and CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'";
                $orderList=M("order")
                ->alias("a")
                ->field("a.id,a.gid,a.ordertime,a.unique_sn,a.gx_schedule,a.endtime,a.isurgent,a.addtime,a.gxline_id")
                ->where($where.$sql)->order("a.isurgent desc,a.addtime asc,a.unique_sn asc")
                ->select();
            
                if($orderList){
            
                				$orderId=array();
                				foreach($orderList as $value){
                				    $orderId[]=$value['id'];
                				}
                				//查找所有订单的已完成的报工记录，已完成的就不显示未排产工序
                				$flowCheck=M("flow_check")
                				->field("id,orderid,orstatus,endtime")
                				->where("orderid in (".implode(",",$orderId).") and orstatus>0 and (endtime>0 or starttime>0)")
                				->select();
                				$checkList=$checkListGxId=array();
                				if($flowCheck!==false&&count($flowCheck)>0){
                				    foreach($flowCheck as $value){
                				        $oid=$value['orderid'];
                				        $checkList[$oid][]=$value;
                				        if($value['endtime']>0){
                				            //存起订单所有完成报工的工序id
                				            $checkListGxId[$oid][]=$value['orstatus'];
                				        }
                				    }
                				}
            
            
                				foreach($orderList as $k=>$value){
                				    	
                				    $gx_schedule=$value['gx_schedule'];
                				    if($gx_schedule!=''){
                				        $gx_schedule=unserialize($gx_schedule);
                				        if(in_array($gxname, $gx_schedule)){
                				            //删去该工序名称已在订单内排产的订单记录
                				            unset($orderList[$k]);
                				        }
                				    }
                				     
                				    $orderid=$value['id'];
                				    if(isset($checkList[$orderid])){
                				        foreach($checkList[$orderid] as $li){
                				            //如果该订单的该工序已报工，则删除该订单
                				            $arr_gx = array($gxids);
                				            if(in_array($li['orstatus'], $arr_gx)){
                				                unset($orderList[$k]);
                				            }
                				        }
                				    }
                				     
                				    //该工序的前工序是否已经完成，未完成则不显示
//                 				    if($needCheckFix){
//                 				        $order_gxs = combine_gx_line(explode(',',$value['gxline_id']),0,$gx_list);
//                 				        if(!$order_gxs){
//                 				            unset($orderList[$k]);
//                 				            continue;
//                 				        }
//                 				        //获取当前工序名的id
//                 				        $current_gx_id=0;
//                 				        //当前订单所有工序的id
//                 				        $gxs_id=array();
//                 				        foreach($order_gxs as $val){
//                 				            $gx_id=$val['id'];
//                 				            $gxs_id[]=$gx_id;
//                 				            $arr_gx = array($gxids);
//                 				            //在当前工序名的工序id数组内
//                 				            if(in_array($gx_id, $arr_gx)){
//                 				                $current_gx_id=$gx_id;
//                 				            }
//                 				        }

//                 				        if($current_gx_id==0){
//                 				            unset($orderList[$k]);
//                 				            continue;
//                 				        }

//                 				        //查找这个工序是否有设置工作流和找到父工序
//                 				        $parent=fixed_parent($fix_gx_id,$current_gx_id);
//                 				        if(!$parent['isSetting']||($parent['isSetting']&&count($parent['parent'])<=0)){
//                 				            //不需要检测
//                 				            continue;
//                 				        }

//                 				        $before_gx=$parent['parent'];

//                 				        //查询前工序是否在当前订单的工序里面
//                 				        foreach($before_gx as $bk=>$gx_id){
//                 				            if(!in_array($gx_id, $gxs_id)){
//                 				                unset($before_gx[$bk]);
//                 				            }
//                 				        }

//                 				        if(count($before_gx)<=0){
//                 				            continue;//没上级工序可以显示
//                 				        }else{

//                 				            if(isset($checkListGxId[$orderid])){
//                 				                $finish_parent=0;
//                 				                foreach($checkListGxId[$orderid] as $gx_id){
//                 				                    if(in_array($gx_id, $before_gx)){
//                 				                        $finish_parent++;
//                 				                    }
//                 				                }
//                 				                if($finish_parent!=count($before_gx)){
//                 				                    unset($orderList[$k]);
//                 				                    continue;
//                 				                }
//                 				            }

//                 				        }

//                 				    }
                				     
                				}//end of foreach
            
                				$now=time();
                				$daySecond=24*60*60;
                				$orderId=array();
                				foreach($orderList as $k=>$value){
                				    if($value['endtime']>0){
                				        $value['day']=floor(($value['endtime']-$now)/$daySecond);
                				    }else{
                				        $value['day']=0;
                				    }
                				    $orderId[]=$value['id'];
                				    $orderList[$k]=$value;
                				}
            
                				//按时间分组
                				$groupby=input("groupby");
                				if(!empty($groupby)){
                				    $name=$groupby;
                				    if($name=='ordertime'){//单据日期比较特殊，有可能是subtime或ordertime
                				        $isSub=find_field("subtime");
                				        if($isSub){
                				            $name='subtime';
                				        }else{
                				            $isOrder=find_field("ordertime");
                				            if($isOrder){
                				                $name='ordertime';
                				            }
                				        }
                				    }
                				    	
                				    //要查附表-可以是其他字段，在$name=='subtime'后添加||即可
                				    if($name=='subtime'){
                				        if(count($orderId)>0){
                				            $times=M("order_attach")
                				            ->field("orderid,`value`")
                				            ->where("fieldname='$name' and orderid in (".implode(",",$orderId).")")
                				            ->select();
                				            if($times){
                				                //遍历$orderList覆盖订单日期ordertime
                				                foreach($orderList as $k=>$value){
                				                    $id=$value['id'];
                				                    foreach($times as $val){
                				                        if($id==$val['orderid']){
                				                            $orderList[$k][$name]=strtotime($val['value']);
                				                            break;
                				                        }
                				                    }
                				                }
                				            }
                				        }
                				    }
                				    	
                				    //对订单进行分组
                				    $newList=array();
                				    foreach($orderList as $value){
                				        $time=date('Y-m-d',$value[$name]);
                				        $newList[$time][]=$value;
                				    }
                				    $orderList=$newList;
                				}
            
                				//统计面积和扇数
                				if(count($orderId)>0){
                				    $area=M("order")->where("id in (".implode(",",$orderId).")")->sum("area");
                				    $snum=M("order")->where("id in (".implode(",",$orderId).")")->sum("snum");
                				    $data['num']=count($orderId);
                				    $data['area']=round($area,2);
                				    $data['snum']=round($snum,2);
                				    $data['gxname']=$gxname;
                				    $data['gxid'] = $gxids;
                				    $data['group_id'] = $gl['id'];
                				    $data['gx_list'] = $orderList;
                				    array_push($list[$gl['team_name']],$data);
                				}else{
                				    continue;
                				}
                }else{
                				continue;
                }
            }
        }
         
        return array('status'=>'1','list'=>$list);
    }
    
    //显示添加排产单
    public function add_schedule(){
    	
    	$ids=input("orderid");
    	if($ids==''){
    		exit("请提交订单ID");
    	}
    	$oids=explode(",",$ids);
    	$order_id=array();
    	foreach($oids as $value){
    		$order_id[]=intval($value);
    	}
    	
    	if(count($order_id)<=0){
    		exit("请提交订单ID");
    	}
    	
    	$sql=implode(",",$order_id);
    	//查询选择了的订单
    	$list=Db::name("order")->where("id in ($sql)")->order("endtime asc")->select();
    	
    	//订单数量，面积和扇数
    	$order_count=0;
    	$square=0;
    	$fans=0;
    	
    	$isSub=find_field("subtime");
    	//合并字段
    	$list=order_attach($list);
    	
    	foreach($list as $key=>$order){
    		$order_count++;
    		$square+=floatval($order['area']);
    		$fans+=floatval($order['snum']);
    		$doornum+=floatval($order['doornum']);
    		if($isSub){
    			$list[$key]['ordertime']=$order['subtime'];
    		}
    	}
    	
    	$this->assign("list",$list);
    	$this->assign("order_count",$order_count);
    	$this->assign("square",$square);
    	$this->assign("fans",$fans);
    	$this->assign("doornum",$doornum);
    	
    	
    	//查询所有的工序小组
    	//获取所有班组数组
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("team",$this->team);

    	//读取字段
    	$fields=@include APP_DATA.'qrfield.php';
    	$fieldList=array();
    	if($fields){
    		foreach($fields as $value){
    			$fieldList[$value['fieldname']]=$value;
    			if($value['fieldname']=='subtime'){
    				$fieldList['ordertime']=$value;
    			}
    		}
    	}
    	//显示列表字段
    	$this->assign("fieldList",$fieldList);

    	//有提交工序参数
    	$dname=input("dname");
    	$select_tid=0;//当前自动选择班组
    	$limitTeam=array();//限定班组
    	$limitGx=array();//限制工序
    	if(!empty($dname)){
    		
    		//班组绑定工序
    		$teams=M("team")->order("id asc")->select();
    		$allteam=array();
    		foreach($teams as $k=>$value){
    			$allteam[$value['id']]=$value;
    		}
    		$team_gx=M("team_gx")->field("id,tid,ngx_id")->select();
    		$team=array();
    		foreach($team_gx as $value){
    			$ngx_id=unserialize($value['ngx_id']);
    			foreach($ngx_id as $lid=>$arr){//lid=>array(15,16...)多工序
    				foreach($arr as $id){
    					$team[$value['tid']][]=$id;
    				}
    			}
    		}
    		
    		$gxs=M("gx_list")->field("id")->where("dname='$dname'")->select();
    		$limitGx[]=$dname;
    		if($gxs&&count($gxs)>0){
    			
    			foreach($gxs as $value){
    				foreach($team as $tid=>$gxid){
    					if(in_array($value['id'], $gxid)){
    						if(isset($allteam[$tid])){
    							$select_tid=$tid;
    							$limitTeam[$tid]=$allteam[$tid];
    						}
    						
    					}
    				}
    			}
    			$this->assign("limitTeam",$limitTeam);
    		}
    	}
    	$this->assign("select_tid",$select_tid);
    	$this->assign("limitGx",$limitGx);
    	
    	return $this->fetch();
    }
    
    //循环获取班组层级数据
    public function get_team($id){
    	$list=Db::name("team")->where("pid='$id'")->select();
    	if($list!==false&&count($list)>0){
    		$this->team[$id]=$list;
    		foreach($list as $value){
    			$pid=$value['id'];
    			$this->get_team($pid);
    		}
    	}
    	return;
    }
    
    //ajax获取班组的所有工序和产能等返回
    public function ajax_teaminfo(){
    	
    	$tid=intval(input("id"));
    	$day=ctrim(input("day"));//安排生产日期
    	
    	$team=Db::name("team")->where("id='$tid'")->find();
    	$gx=Db::name("team_gx")->where("tid='$tid'")->find();
    	if($gx===false||count($gx)<=0){
    		return array('status'=>2,'msg'=>'小组没有绑定工序','data'=>array('gx_list'=>array()));
    	}
    	//加载工序缓存
    	//$all_gx=@include_once APP_DATA.'gx_list.php';
    	$all_gx=array();
    	$gx_list=Db::name("gx_list")->where("1=1")->order('id asc')->select();
    	foreach($gx_list as $value){
    		$all_gx[$value['id']]=$value;
    	}
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	
    	//返回班组绑定的所有工序
    	$gx_list=array();
    	
    	if($gx['gx_id']!=''){
    		$gx_ids=explode(",",$gx['gx_id']);
    		//工序名
    		foreach($gx_ids as $id){
    			if(isset($all_gx[$id])){
    				$dname=$all_gx[$id]['dname'];
    				$gx_list[$dname]=$dname;
    			}
    		}
    	}
    	
    	//新逻辑
    	if(!empty($gx['ngx_id'])){
    			$ngx_id=unserialize($gx['ngx_id']);
    			foreach($ngx_id as $lid=>$arr){//lid=>array(15,16...)多工序
    				foreach($arr as $id){
    					if(isset($all_gx[$id])){
    						$dname=$all_gx[$id]['dname'];
    						$gx_list[$dname]=$dname;
    					}
    				}
    			}
    	}
    	
    	$compare_gx=array();
    	//比较订单绑定工艺路线的工序
    	$orderid=input("orderid/a");
    	if(count($orderid)>0){
    		$sql=" id in (".implode(",",$orderid).")";
    		$list=Db::name("order")->field("gid,gx_schedule,ng,gxline_id")->where($sql)->select();
    		if($list!==false&&count($list)>0){
    			//读取缓存
    			
    			foreach($list as $value){
    				$gx_schedule=array();
    				if($value['gx_schedule']!=''){
    					$gx_schedule=unserialize($value['gx_schedule']);//已经排产的工序
    				}

    				$lineId = explode(',',$value['gxline_id']);
    				$gxlist = combine_gx_line($lineId);
    				
    				foreach($gxlist as $value){
    					$dname=trim($value['dname']);
    					if(!in_array($dname, $gx_schedule)){
    						$compare_gx[$dname]=$dname;//订单内含有的工序
    					}
    				}
    			}
    		}
    	}
    	foreach($gx_list as $dname){
    		if(!key_exists($dname, $compare_gx)){
    			unset($gx_list[$dname]);
    		}
    	}
    	
		//订单数量，面积和扇数
    	$order_count=0;
    	$square=0;
    	$fans=0;
    	
    	if(!empty($day)){
	    	//选择的那天开始时间
	    	$start_time=strtotime($day);
	    	//当天结束之间
	    	$end_time=$start_time+60*60*24-1;
	    	//查询该小组当天已安排的订单
	    	$orders=Db::name("schedule")->field("order_id")->where("tid='$tid' and do_time>=$start_time and do_time<=$end_time")->select();
	    	if($orders!==false&&count($orders)>0){
	    		$order_count=count($orders);
	    		$order_ids=array();
	    		foreach($orders as $value){
	    			$order_ids[]=$value['order_id'];
	    		}
	    		
	    		//求订单的面积和扇数
	    		if(count($order_ids)>0){
	    			$sql=implode(",",$order_ids);
	    			$os=Db::name("order")->field("area,snum")->where("id in ($sql)")->select();
	    			if($os!==false&&count($os)>0){
	    				foreach($os as $value){
	    					$square+=floatval($value['area']);
	    					$fans+=floatval($value['snum']);
	    				}
	    			}
	    		}
	    	}
    	}
    	
    	//剩余产能
    	$left=0;
    	if($team['unit']==1){
    		//面积
    		$left=$team['day_ab']-$square;
    	}else if($team['unit']==2){
    		//扇数
    		$left=$team['day_ab']-$fans;
    	}
    	
    	//返回数据
    	$return=array();
    	//小组产能
    	$return['ability']=$team['day_ab'];
    	//单位
    	$return['unit']=$ab_unit[$team['unit']]['label'];
    	//剩余产能
    	$return['left']=$left;
    	$return['order_count']=$order_count;
    	$return['square']=$square;
    	$return['fans']=$fans;
    	$return['gx_list']=$gx_list;
    	
    	return array('status'=>1,'msg'=>'ok','data'=>$return);
    }
    
    //补0
    private function patch_zero($str){
    	$max_length=5;
    	$strlen=strlen($str);
    	if($strlen<$max_length){
    		$patchlen=$max_length-$strlen;
    		$tstr='';
    		for($i=0;$i<$patchlen;$i++){
    			$tstr.='0';
    		}
    		$str=$tstr.$str;
    	}
    	return $str;
    }
    
    //生成唯一的排产单号
    private function schedule_no(){
    	$now=time();
    	//查询今天内最大的号码--十万位
    	$today=timezone_get(1);
    	$begin=$today['begin'];
    	$end=$today['end'];
    	
    	$next_no="00001";
    	$max=Db::name("schedule_summary")->field("schedule_no")->where("addtime>=$begin and addtime<=$end")->order("schedule_no desc")->find();
    	if($max!==false&&$max['schedule_no']!=''){
    		$max_no=substr($max['schedule_no'],10);
    		$max_no=intval($max_no)>0?intval($max_no)+1:1;
    		$next_no=$this->patch_zero(strval($max_no));
    	}
    	return "PC".date('Ymd').$next_no;
    }
   
    //保存排产
    public function save_schedule(){
    	$now=time();
    	
    	$uid=session("uid");//当前用户的id
    	$tid=intval(input("tid"));//班组ID
    	$gxid=intval(input("gxid"));//工序ID
    	$gxname=ctrim(input("gxname"));//工序名称
    	$do_time=ctrim(input("do_time"));//生产日期
    	$do_time=$do_time!=''?ymktime($do_time):'';
    	if($do_time==''){
    		return array('status'=>2,'msg'=>'请提交生产日期');
    	}
    	
    	if(empty($tid)){
    		return array('status'=>2,'msg'=>'请选择工序班组');
    	}
    	
    	if(empty($gxname)){
    		return array('status'=>2,'msg'=>'请选择负责工序');
    	}

    	//过滤ID
    	$ids=input("post.ids/a");//订单ID
    	$orderid=array();
    	foreach($ids as $value){
    		$orderid[]=$value;
    		
    	}
    	
    	$square=0;
    	$fans=0;
    	$order=array();
    	$o_fans=$o_square=array();
    	if(count($orderid)>0){
    		$sql=implode(",",$orderid);
    		$os=Db::name("order")->field("id,ordernum,gid,is_schedule,gx_schedule,unique_sn,ng,gxline_id")->where("id in ($sql)")->select();
    		if($os!==false&&count($os)>0){
    			$os=order_attach($os);
    			foreach($os as $value){
    				$square+=floatval($value['area']);
    				$fans+=floatval($value['snum']);
    				$order[$value['id']]=$value;
    				$o_square[$value['id']]=floatval($value['area']);
    				$o_fans[$value['id']]=floatval($value['snum']);
    			}
    		}
    	}
    	
    	$urgent=input("post.urgent/a");//加急
    	$orderby=input("post.orderby/a");//排序
    	
    	//获取排产单批次号
    	$schedule_no=$this->schedule_no();
    	   
    	$sum=array();
    	$sum['schedule_no']=$schedule_no;
    	$sum['uid']=$uid;
    	$sum['tid']=$tid;
    	$sum['gx_name']=$gxname;
    	$sum['ordernum']=count($orderid);
    	$sum['square']=$square;
    	$sum['fans']=$fans;
    	$sum['do_time']=$do_time;
    	$sum['addtime']=$now;
    	$sumid=Db::name("schedule_summary")->insertGetId($sum);//主表ID
    	
    	$num=0;//记录安排订单数量
    	foreach($orderid as $id){
    		
    		//查找对应订单的工序的id-因为提交过来的是统一一个工序名
//    		$did=$order[$id]['gid'];//工艺路线的id
    		$ng=$order[$id]['ng'];//使用新工艺逻辑
    		//查询对应工序的id
    		//获取line
    		$line_id=$order[$id]['gxline_id'];
    		if($line_id){
    				$gx = Db::name('gx_list')->field('id')->where("lid in ($line_id) and dname='$gxname'")->find();
    		}else{
    				$gx['id']='0';
    		}

    		$gx_id = $gx['id']?$gx['id']:'0';
    		
    		if($gx_id=='0'){
    			continue;
    		}
    		
			//判断是否已经排产
			$flowCheck=M("flow_check")->field("id")->where("orderid='$id' and orstatus='$gx_id'")->find();
			if($flowCheck&&!empty($flowCheck['id'])){
				$square=$square-$o_square[$id];
				$fans=$fans-$o_fans[$id];
				continue;//已有报工则跳过
			}
    		
    		$one=array();
    		$one['tid']=$tid;
    		$one['sid']=$sumid;
    		$one['order_id']=$id;
    		$one['ordernum']=$order[$id]['unique_sn'];
    		$one['gx_id']=$gx_id;
    		$one['gx_name']=$gxname;
    		$one['uid']=$uid;
    		$one['do_time']=$do_time;
    		$one['urgent']=$urgent[$id];
    		$one['orderby']=$orderby[$id];
    		$one['addtime']=$now;
    		$ok=Db::name("schedule")->insertGetId($one);
    		if($ok!==false){
    			
    			//判断当前工序是否要报开始和结束，如果是则自动开始,用排产日期作为开始时间
    			$in_data=array('orderid'=>$id,'uid'=>$uid,'orstatus'=>$gx_id,'starttime'=>$do_time,'in_num'=>'0','schedule_id'=>$ok);
    			$one=Db::name('flow_check')->where("orderid='$id' and orstatus='$gx_id'")->find();
    			if($one!==false&&!empty($one['id'])){
    				//刷新报工开始时间
    				Db::name('flow_check')->where("id='{$one['id']}'")->update(array("starttime"=>$do_time));
    			}else{
    				Db::name('flow_check')->insert($in_data);
    			}
    			
    			
    			//更新原订单已安排生产的工序
    			$new_gx_schedule=array();
    			$new_gx_schedule[]=$gxname;
    			
    			if($order[$id]['gx_schedule']!=''){
    				$gx_schedule=unserialize($order[$id]['gx_schedule']);
    				$new_gx_schedule=array_merge($new_gx_schedule,$gx_schedule);
    			}
    			
    			$new_gx_schedule=serialize(array_unique($new_gx_schedule));
    			
    			//更新订单被安排的工序
    			Db::name("order")->where("id='$id'")->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule));
    	
    			$num++;
    		}
    	}
    	
    	if($num!=count($orderid)){	
    		$square=$square>0?$square:0;
    		$fans=$fans>0?$fans:0;
    		Db::name("schedule_summary")->where("id='$sumid'")->update(array('ordernum'=>$num,'square'=>$square,'fans'=>$fans));
    		if($num<=0){
    			Db::name("schedule_summary")->where("id='$sumid'")->delete();
    			return array('status'=>2,'msg'=>'所有订单的工序已排产');
    		}
    	}
    	//美加项目写入第三方数据库
        writeThirdDb($orderid);
    	return array('status'=>1,'msg'=>'已安排生产订单',"data"=>$num);
    	
    }
    
    //班组排产
    public function save_group_schedule(){
        $now=time();
        $data = input('data/a');
        $uid=session("uid");//当前用户的id
        foreach ($data as $dt){
            $tid=intval($dt['tid']);//班组ID
            $gxid=intval($dt['gxid']);//工序ID
            $gxname=ctrim($dt['gxname']);//工序名称
            $do_time=ctrim($dt["do_time"]);//生产日期
            $do_time=$do_time!=''?ymktime($do_time):'';
            if($do_time==''){
                return array('status'=>2,'msg'=>'请提交生产日期');
            }
             
            if(empty($tid)){
                return array('status'=>2,'msg'=>'请选择工序班组');
            }
             
            if(empty($gxname)){
                return array('status'=>2,'msg'=>'请选择负责工序');
            }
    
            //过滤ID
            $ids=$dt['ids'];//订单ID
            $orderid=array();
            foreach($ids as $value){
                $orderid[]=$value;
            }
             
            $square=0;
            $fans=0;
            $order=array();
            $o_fans=$o_square=array();
            if(count($orderid)>0){
                $sql=implode(",",$orderid);
                $os=Db::name("order")->field("id,ordernum,gid,is_schedule,gx_schedule,unique_sn,ng,gxline_id")->where("id in ($sql)")->select();
                if($os!==false&&count($os)>0){
                    $os=order_attach($os);
                    foreach($os as $value){
                        $square+=floatval($value['area']);
                        $fans+=floatval($value['snum']);
                        $order[$value['id']]=$value;
                        $o_square[$value['id']]=floatval($value['area']);
                        $o_fans[$value['id']]=floatval($value['snum']);
                    }
                }
            }
             
            $urgent=$dt['urgent'];//加急
            $orderby=$dt['orderby'];//排序
             
            //获取排产单批次号
            $schedule_no=$this->schedule_no();
    
            $sum=array();
            $sum['schedule_no']=$schedule_no;
            $sum['uid']=$uid;
            $sum['tid']=$tid;
            $sum['gx_name']=$gxname;
            $sum['ordernum']=count($orderid);
            $sum['square']=$square;
            $sum['fans']=$fans;
            $sum['do_time']=$do_time;
            $sum['addtime']=$now;
            $sumid=Db::name("schedule_summary")->insertGetId($sum);//主表ID
             
            $num=0;//记录安排订单数量
            foreach($orderid as $id){
    
                //查找对应订单的工序的id-因为提交过来的是统一一个工序名
                //    		$did=$order[$id]['gid'];//工艺路线的id
                $ng=$order[$id]['ng'];//使用新工艺逻辑
                //查询对应工序的id
                //获取line
                $line_id=$order[$id]['gxline_id'];
                if($line_id){
                    $gx = Db::name('gx_list')->field('id')->where("lid in ($line_id) and dname='$gxname'")->find();
                }else{
                    $gx['id']='0';
                }
    
                $gx_id = $gx['id']?$gx['id']:'0';
    
                if($gx_id=='0'){
                    continue;
                }
    
                //判断是否已经排产
                $flowCheck=M("flow_check")->field("id")->where("orderid='$id' and orstatus='$gx_id'")->find();
                if($flowCheck&&!empty($flowCheck['id'])){
                    $square=$square-$o_square[$id];
                    $fans=$fans-$o_fans[$id];
                    continue;//已有报工则跳过
                }
    
                $one=array();
                $one['tid']=$tid;
                $one['sid']=$sumid;
                $one['order_id']=$id;
                $one['ordernum']=$order[$id]['unique_sn'];
                $one['gx_id']=$gx_id;
                $one['gx_name']=$gxname;
                $one['uid']=$uid;
                $one['do_time']=$do_time;
                $one['urgent']=$urgent[$id];
                $one['orderby']=$orderby[$id];
                $one['addtime']=$now;
                $ok=Db::name("schedule")->insertGetId($one);
                if($ok!==false){
                     
                    //判断当前工序是否要报开始和结束，如果是则自动开始,用排产日期作为开始时间
                    $in_data=array('orderid'=>$id,'uid'=>$uid,'orstatus'=>$gx_id,'starttime'=>$do_time,'in_num'=>'0','schedule_id'=>$ok);
                    $one=Db::name('flow_check')->where("orderid='$id' and orstatus='$gx_id'")->find();
                    if($one!==false&&!empty($one['id'])){
                        //刷新报工开始时间
                        Db::name('flow_check')->where("id='{$one['id']}'")->update(array("starttime"=>$do_time));
                    }else{
                        Db::name('flow_check')->insert($in_data);
                    }
                     
                     
                    //更新原订单已安排生产的工序
                    $new_gx_schedule=array();
                    $new_gx_schedule[]=$gxname;
                     
                    if($order[$id]['gx_schedule']!=''){
                        $gx_schedule=unserialize($order[$id]['gx_schedule']);
                        $new_gx_schedule=array_merge($new_gx_schedule,$gx_schedule);
                    }
                     
                    $new_gx_schedule=serialize(array_unique($new_gx_schedule));
                     
                    //更新订单被安排的工序
                    Db::name("order")->where("id='$id'")->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule));
                     
                    $num++;
                }
            }
             
            if($num!=count($orderid)){
                $square=$square>0?$square:0;
                $fans=$fans>0?$fans:0;
                Db::name("schedule_summary")->where("id='$sumid'")->update(array('ordernum'=>$num,'square'=>$square,'fans'=>$fans));
                if($num<=0){
                    Db::name("schedule_summary")->where("id='$sumid'")->delete();
                    return array('status'=>2,'msg'=>'所有订单的工序已排产');
                }
            }
            //美加项目写入第三方数据库
            writeThirdDb($orderid);
        }
    
        return array('status'=>1,'msg'=>'已安排生产订单',"data"=>$num);
    }
    
    //已排产订单
    public function scheduled(){
    	
    	//获取所有班组数组-下拉选择用
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("team",$this->team);
    	
    	//查已入库订单
    	$team=input("team",'0','intval');
    	$ordersn = ctrim(input('param.ordersn'));
    	$s_date = ctrim(input("param.s_date"));
    	$e_date = ctrim(input("param.e_date"));
    	$order_status=input("order_status",'0','intval');
   
    	$post['team']=$team;
    	$post['ordersn']=$ordersn;
    	$post['s_date']=$s_date;
    	$post['e_date']=$e_date;
    	$post['order_status']=$order_status;
    	$this->assign("post",$post);
    	
    	$wheres = array();
    	
    	//日期筛选
    	if (!empty($s_date) && !empty($e_date) && isset($s_date) && isset($e_date)){
    		$s_date = strtotime($s_date);
    		$e_date = strtotime($e_date);
    		$wheres[] = " do_time between $s_date and $e_date ";
    	}
    	if (!empty($ordersn)){
    		$order_id=Db::name("schedule")->field("sid,order_id")->where("ordernum like '%$ordersn%'")->select();
    		$ids=array();
    		foreach($order_id as $value){
    			$ids[]=$value['sid'];
    		}
    		if(count($ids)>0){
    			$wheres[]= " id in (".implode(",",$ids).")";
    		}else{
    			$wheres[]= " id='0'";//不存在
    		}
    	}
		
		if(!empty($order_status)){
			if($order_status==1){
				$wheres[]= " isfinished='1'";
			}else{
				$wheres[]= " isfinished='0'";
			}
		}
		
		if(!empty($team)){
				$wheres[]= " tid='$team'";
		}
    	
    	$sql=implode(" and ",$wheres);
    	
    	$result = Db::name('schedule_summary')
    	->where("$sql")->order('id desc')->paginate(20,false,['query' => request()->param()]);

    	$page = $result->render();
    	$this->assign('page',$page);
    	$this->assign('total',$result->total());
    	//echo $sql;
    	$list=$result->all();
    	if ($list!==false&&count($list)>0){
    		
    		$cache=@include_once APP_DATA.'team_list.php';
    		//查询未完成的订单
    		foreach($list as $key=>$value){
    			$list[$key]['list']=array();
    			$un_count=0;
    			$unfinished=Db::name("schedule")->where("sid='{$value['id']}' and finished!='1'")->select();
    			if($unfinished!==false&&count($unfinished)>0){
    				$list[$key]['list']=$unfinished;
    				$un_count=count($unfinished);
    			}
    			
    			//未完成数
    			$list[$key]['unfinished']=$un_count;
    			
    			//完成数
    			$finished=$value['ordernum']-$un_count;
    			$list[$key]['finished']=$finished;
    			//强制完成
				if($finished==$value['ordernum']&&$value['isfinished']!='1'){
					Db::name('schedule_summary')->where("id='{$value['id']}'")->update(array('isfinished'=>'1'));
				}
    			//获取班组
    			$list[$key]['team']=$this->get_team_link($value['tid'], $cache);
    		}
    	
    		$this->assign('list',$list);
    	}
    	
    	return $this->fetch();
    }
    
    //新版已排产
    public function scheduled_new(){
    	
    	//获取所有班组数组-下拉选择用
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("team",$this->team);
    	
    	$where=array();
    	
    	//默认今天内
    	$day=timezone_get(1);
    	$start=$day['begin'];
    	$end=$day['end'];
    	
    	$one_day=24*60*60;
    	$y_start=$start-$one_day;
    	$y_end=$end-$one_day;
    	
    	//范围包括昨天的派工工序
    	//$where['time_zone']="do_time>=$y_start and do_time<=$end";
    
    	if(count($where)>0){
    		$dname_where=implode(" and ", $where);
    	}else{
    		$dname_where=array();
    	}
    	
    	$gxname_list=M("schedule")->where($dname_where)->field("gx_id,gx_name")->group("gx_name")->select();
    	$this->assign("gxname_list",$gxname_list);
    	
    	return $this->fetch();
    }
    
    //新版已排产
    public function ajax_scheduled(){
    
    	$where=array();
    	$istoday=false;//标记是今天
    	$today=date('Y-m-d',time());
    	$day=input("day");//今天，明天或后天或自选时间
    	if(!empty($day)&&$today!=$day){
    		//当天开始时间
    		$start=ymktime($day);//换算成时间戳
    		//结束时间
    		$end=$start+23*60*60-1;//当天截止时间
    	}else{
    		//默认今天内
    		$day=timezone_get(1);
    		$start=$day['begin'];
    		$end=$day['end'];
    		$istoday=true;
    	}
    	
    	/*今天:未完成订单是昨天+今天未完成订单数
    	 * 明天和后天之后的完成率都为0（因为当天完成数肯定为0）,总数按当天的排产数统计就可以，未完成订单是派工到当天并且未完成的订单
    	 */
    	
    	$one_day=24*60*60;
    	$y_start=$start-$one_day;
    	$y_end=$end-$one_day;
    
    	$this->assign("day",date('Y-m-d',$start));
    
    	//显示工序名列表
    	$unique_sn=input("unique_sn");
    	if(!empty($unique_sn)){
    		$order=M("order")->field("id")->where("unique_sn='$unique_sn'")->find();
    		if($order!==false&&$order['id']>0){
    			$where[]="order_id='{$order['id']}'";
    		}
    	}
    
    	//底部选择项目名称
    	$gx_name=input("gx_name");
    	if(!empty($gx_name)){
    		$where[]="gx_name='$gx_name'";
    	}
		
    	$today_urgent=array();
    	$yestoday_unfinish=array();
    	$today_finish=array();
    	$today_unfinish=array();
		//记录所有的主排产ID
		$schedualID=array();
		//记录所有排产项目ID
		$ItemId=array();
    	
    	//返回排产项目字段
    	$fields="id,sid,order_id,ordernum,gx_id,gx_name,do_time,urgent,finished,finished_time,do_uid,do_uname";
    	
    	//未完成的排产--------------------------------------------------------------------------------------------
    	$where['finish_status']="finished!='1'";//未完成
    	$list_sql=array();
    	if($istoday){
	    	//昨天未完成排产项目
	    	//$where['time_zone']="do_time>=$y_start and do_time<=$y_end";
	    	$where['time_zone']="do_time<=$y_end";//昨天之前所有项目
	    	$list_sql=implode(" and ", $where);
	    	
	    	$yestoday_unfinish=M("schedule")->where($list_sql)->field($fields)->select();
	    	if($yestoday_unfinish!==false&&count($yestoday_unfinish)>0){
				foreach($yestoday_unfinish as $value){
					$schedualID[]=$value['sid'];
					$ItemId[]=$value['id'];
				}
	    		$this->assign("yestoday_unfinish",$yestoday_unfinish);
	    	}
    	}
 
    	//当天未完成
    	$where['time_zone']="do_time>=$start and do_time<=$end";
    	$list_sql=implode(" and ", $where);
    	
    	$today_unfinish=M("schedule")->where($list_sql)->field($fields)->select();
    	if($today_unfinish!==false&&count($today_unfinish)>0){
	    		//找出加急未完成的
	    		foreach($today_unfinish as $key=>$value){
					$schedualID[]=$value['sid'];
					$ItemId[]=$value['id'];
	    			if($value['urgent']){
	    				$today_urgent[]=$value;
	    				unset($today_unfinish[$key]);
	    			}
	    		}
	    		$this->assign("today_urgent",$today_urgent);
	    		$this->assign("today_unfinish",$today_unfinish);
    	}else{
    		$today_unfinish=array();
    	}
    	//未完成的排产--------------------------------------------------------------------------------------------
    	
    	//当天已完成----------------------------------------------------------------------------------------------
    	unset($where['time_zone']);
    	$where['finish_status']="finished='1' and finished_time>=$start and finished_time<=$end";
    	$list_sql=implode(" and ", $where);
    	 
    	$today_finish=M("schedule")->where($list_sql)->field($fields)->select();
    	if($today_finish!==false&&count($today_finish)>0){
			foreach($today_finish as $value){
				$schedualID[]=$value['sid'];
			}
    		$this->assign("today_finish",$today_finish);
    	}else{
    		$today_finish=array();
    	}
    	//当天已完成--------------------------------------------------------------------------------------------
    	
    	//总和-当前工序所有的排产数量
    	$total=count($yestoday_unfinish)+count($today_unfinish)+count($today_finish);
    	//完成
    	$finish=count($today_finish);
    	//完成率
    	if($total>0){
    		$finish_rate=ceil(($finish/$total)*100);
    	}else{
    		$finish_rate="0";
    	}
    	
    	$complete=array();
    	$complete['total']=$total;
    	$complete['finish']=$finish;
    	$complete['finish_rate']=$finish_rate;
    	
    	$this->assign("complete",$complete);
		
		$schedualID=array_unique($schedualID);
		$this->assign("schedualID",implode(",",$schedualID));
		
		$ItemId=array_unique($ItemId);
		$this->assign("ItemId",implode(",",$ItemId));
		
    	echo $this->fetch();
    }
    
    //新版完成排产单
    public function finished(){
    	$where=array();
    	
    	//默认昨天内
    	$day=timezone_get(7);
    	$start=$day['begin'];
    	$end=$day['end'];
    	
    	$one_day=24*60*60;
    	
    	//默认昨天内所有的派工的工序
    	//$where['time_zone']="do_time>=$start and do_time<=$end";
    
    	if(count($where)>0){
    		$dname_where=implode(" and ", $where);
    	}else{
    		$dname_where=array();
    	}
    	
    	$gxname_list=M("schedule")->where($dname_where)->field("gx_id,gx_name")->group("gx_name")->select();
    	$this->assign("gxname_list",$gxname_list);
    	
    	return $this->fetch();
    }
    
    //新版完成排产列表
    public function ajax_finished(){
    
    	$where=array();
    
    	$day=input("day");//昨天或前天或自选时间
    	if(!empty($day)){
    		//当天开始时间
    		$start=ymktime($day);//换算成时间戳
    		//结束时间
    		$end=$start+23*60*60-1;//当天截止时间
    	}else{
    		//默认今天内
    		$day=timezone_get(7);
    		$start=$day['begin'];
    		$end=$day['end'];
    	}
    	 
    	$one_day=24*60*60;
    	$y_start=$start-$one_day;
    	$y_end=$end-$one_day;
    
    	$this->assign("day",date('Y-m-d',$start));
    
    	//显示工序名列表
    	$unique_sn=input("unique_sn");
    	if(!empty($unique_sn)){
    		$order=M("order")->field("id")->where("unique_sn='$unique_sn'")->find();
    		if($order!==false&&$order['id']>0){
    			$where[]="order_id='{$order['id']}'";
    		}
    	}
    
    	//底部选择项目名称
    	$gx_name=input("gx_name");
    	if(!empty($gx_name)){
    		$where[]="gx_name='$gx_name'";
    	}
    
    	$today_finish=array();
    	$today_unfinish=array();
    	 
    	//返回排产项目字段
    	$fields="id,order_id,ordernum,gx_id,gx_name,do_time,urgent,finished,finished_time,do_uid,do_uname";
    	 
    	//其他当天未完成
    	$where['time_zone']="do_time>=$start and do_time<=$end";
    	$where['finish_status']="finished!='1'";
    	$list_sql=implode(" and ", $where);
    	 
    	$today_unfinish=M("schedule")->where($list_sql)->field($fields)->select();
    	if($today_unfinish!==false&&count($today_unfinish)>0){
    		$this->assign("today_unfinish",$today_unfinish);
    	}else{
    		$today_unfinish=array();
    	}
    	 
    	//当天已完成
    	$where['finish_status']="finished='1'";
    	$where["time_zone"]="finished_time>=$start and finished_time<=$end";//限制完成时间再当天
    	$list_sql=implode(" and ", $where);
    
    	$today_finish=M("schedule")->where($list_sql)->field($fields)->select();
    	if($today_finish!==false&&count($today_finish)>0){
    		$this->assign("today_finish",$today_finish);
    	}else{
    		$today_finish=array();
    	}
    	 
    	//总和-当前工序所有的排产数量
    	$total=count($today_finish)+count($today_unfinish);
    	//完成
    	$finish=count($today_finish);
    	//完成率
    	if($total>0){
    		$finish_rate=ceil(($finish/$total)*100);
    	}else{
    		$finish_rate="0";
    	}
    	 
    	$complete=array();
    	$complete['total']=$total;
    	$complete['finish']=$finish;
    	$complete['finish_rate']=$finish_rate;
    	 
    	$this->assign("complete",$complete);
    	 
    	echo $this->fetch();
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
    
    //保存转班组
    public function save_change(){
    	$now=time();
    	$uid=session("uid");//当前用户的id
    	//之前的排产记录ID
    	$schedule_id=input("schedule_id",'0','intval');
    	$tid=intval(input("tid"));//新班组ID
    	$do_time=ctrim(input("do_time"));//新生产日期
    	$do_time=$do_time!=''?ymktime($do_time):'';
    	if($do_time==''){
    		return array('status'=>2,'msg'=>'请提交生产日期');
    	}
    	 
    	if(empty($tid)){
    		return array('status'=>2,'msg'=>'请选择工序班组');
    	}
    	
    	$schedule=Db::name("schedule")->where("id='$schedule_id'")->find();
    	if($schedule===false||empty($schedule['id'])){
    		return array('status'=>2,'msg'=>'以往排产记录不存在');
    	}
    	
    	$schedule_summary=Db::name("schedule_summary")->where("id='{$schedule['sid']}'")->find();
    	if($schedule_summary===false||empty($schedule_summary['id'])){
    		return array('status'=>2,'msg'=>'以往排产主记录不存在');
    	}
    	
    	//重新统计订单的面积是扇数
    	$orderid=$schedule['order_id'];
    	$ordernum=$schedule['ordernum'];
    	$gx_id=$schedule['gx_id'];
    	$gx_name=$schedule['gx_name'];
    	$urgent=$schedule['urgent'];
    	$orderby='1';
    	$square=0;
    	$fans=0;
    	$os=Db::name("order")->field("id,ordernum,gid,is_schedule,gx_schedule,ng")->where("id='$orderid'")->find();
    	if($os!==false&&count($os)>0){
    		$attach=order_attach($os['id']);
    		$square=floatval($attach['area']);
    		$fans=floatval($attach['snum']);
    	}
    	
    	//获取排产单批次号
    	$schedule_no=$this->schedule_no();
    	
    	//插入新排产记录
    	$main=array();
    	$main['schedule_no']=$schedule_no;
    	$main['uid']=$uid;
    	$main['tid']=$tid;
    	$main['gx_name']=$gx_name;
    	$main['ordernum']=1;
    	$main['square']=$square;
    	$main['fans']=$fans;
    	$main['isfinished']='0';
    	$main['do_time']=$do_time;
    	$main['addtime']=$now;
    	$sumid=Db::name("schedule_summary")->insertGetId($main);//主表ID
    	
    	if($sumid===false){
    		return array('status'=>2,'msg'=>'新建排产单失败，请重试');
    	}
    	
    	//插入附表记录
    	$one=array();
    	$one['tid']=$tid;
    	$one['sid']=$sumid;
    	$one['order_id']=$orderid;
    	$one['ordernum']=$ordernum;
    	$one['gx_id']=$gx_id;
    	$one['gx_name']=$gx_name;
    	$one['uid']=$uid;
    	$one['do_uid']='';
    	$one['finished']='0';
    	$one['finished_time']='0';
    	$one['do_time']=$do_time;
    	$one['urgent']=$urgent;
    	$one['orderby']=$orderby;
    	$one['addtime']=$now;
    	$ok=Db::name("schedule")->insert($one);
    	if($ok===false){
    		Db::name("schedule_summary")->where("id='$sumid'")->delete();
    		return array('status'=>2,'msg'=>'新建排产单附表失败，请重试');
    	} 
    	//更新原订单已安排生产的工序
    	$new_gx_schedule=array();
    	$new_gx_schedule[]=$gx_name;
    		 
    	if($os['gx_schedule']!=''){
    			$gx_schedule=unserialize($os['gx_schedule']);
    			$new_gx_schedule=array_merge($new_gx_schedule,$gx_schedule);
    	}
    		 
    	$new_gx_schedule=serialize(array_unique($new_gx_schedule));
    		 
    	//更新订单被安排的工序
    	Db::name("order")->where("id='$orderid'")->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule)); 
    	Db::name("schedule")->where("id='$schedule_id'")->delete();

    	//更新报工记录
        Db::name('flow_check')->where('orderid',$orderid)->where('orstatus',$gx_id)->update(['starttime'=>$do_time]);

    	//还要更新旧的排产单是否已经完成状态
    	$sc_id=$schedule['sid'];
    	//查看其它排产单是否已经全部完成
    	$total=Db::name('schedule')->where("sid='$sc_id'")->count();
    	$finished=Db::name('schedule')->where("sid='$sc_id' and finished='1'")->count();
    	if($finished==$total&&$finished>0){
    			//标记改排产单已全部完成
    			Db::name('schedule_summary')->where("id='$sc_id'")->update(array('isfinished'=>1));
    	}else if($total<=0){
    			Db::name('schedule_summary')->where("id='$sc_id'")->delete();
    	}
    	return array('status'=>1,'msg'=>'转班组成功');
    }
    
    //排产单完成情况详细
    public function schedule_view(){
    	//排产单主表id
    	$id=input("id","0","intval");
    	//查找所有的排产记录
    	$list=Db::name('schedule')
    			->where("sid='$id'")
    			->select();
    	$gx_id=0;
    	if($list!==false&&count($list)>0){
    		$order_id=array();
    		foreach($list as $key=>$value){
    			$order_id[]=$value['order_id'];
    		}
    		$orders=Db::name("order")->where("id in (".implode(",",$order_id).")")->group("unique_sn")->order("-unique_sn desc")->select();
    		$orders=order_attach($orders);
 
    		$order=array();
    		foreach($orders as $value){
                $result=combine_gx_line(explode(",",$value['gxline_id']));
                $orderid=$value['id'];
                foreach ($result as $k=>$res){
                    $gxid=$res['id'];
                    $flow_text=Db::name('flow_check')->where("orstatus=$gxid and orderid=$orderid")->find();
                    if ($flow_text){
                        $flow_text['starttime']==0?'':$result[$k]['starttime']=date('Y-m-d',$flow_text['starttime']);
                       $flow_text['endtime']==0?'':$result[$k]['endtime']=date('Y-m-d',$flow_text['endtime']);
                    }
                }
                $res_list[$value['id']]=$result;
    			$order[$value['id']]=$value;
    		}

    		foreach($list as $key=>$value){
    			$order_id=$value['order_id'];
    			$list[$key]['uname'] = $order[$order_id]['uname'];
    			$list[$key]['pname'] = $order[$order_id]['pname'];
    			$list[$key]['area']=round($order[$order_id]['area'],2);
    			$list[$key]['snum']=round($order[$order_id]['snum'],2);
                $list[$key]['gx_list']=$res_list[$order_id];
    			$gx_id=$value['gx_id'];
    		}
    		$this->assign("list",$list);
    	}
		
    	//查询工序
    	if($gx_id>0){
    		$gx=M("gx_list")->where("id='$gx_id'")->find();
    		if($gx){
    			$this->assign("gx",$gx);
    		}
    	}
    	
    	return $this->fetch();
    }
    
    //批量显示打印排产单
    public function schedule_prints(){
    	$id=trim(input("id"));
    	if(strpos($id, ",")!==false){
    		$ids=explode(",",$id);
    	}else{
    		$ids[]=intval($id);
    	}
    	sort($ids);
    	if(count($ids)>0){
    		$this->assign("ids",$ids);
    	}
    	return $this->fetch();
    }
    
    //排产单完成情况详细
    public function schedule_print(){
    	
    	require '../vendor/phpqrcode/phpqrcode.php';
    	$cache=@include_once APP_DATA.'team_list.php';
    	//排产单主表id
    	$id=input("id","0","intval");
    	$schedule=Db::name("schedule_summary")->alias("a")->field("a.*,b.uname")->join("login b","a.uid=b.id")->where("a.id='$id'")->find();
    	$schedule['team']=$this->get_team_link($schedule['tid'], $cache);
    	
    	//读取排产打印单显示字段
    	$fieldList=array();
    	//读取字段
    	$fields=@include APP_DATA.'qrfield_type.php';
    	if(isset($fields['scheduallist'])){
    		foreach($fields['scheduallist'] as $value){
    			$fieldList[$value['fieldname']]=$value;
    		}
    	}
    	//显示排产字段
    	$this->assign("fieldList",$fieldList);
    	
    	if($schedule['schedule_no']!=''){
    		$base64=qrcode($schedule['schedule_no'],3);
    		$schedule_no='data:image/jpeg;base64,'.$base64;
    		$this->assign("schedule_no",$schedule_no);
    	}
    	
    	$unfinish_sql="";
    	if(!empty(input("unfinish"))){//只打印未完成的排产单
    		$unfinish_sql=" and finished!='1'";
    	}
    	//查找附表
    	//查找所有的订单Id
    	$order_id=Db::name("schedule")->where("sid='$id' $unfinish_sql")->column("order_id");
    	//查找所有的订单
    	$orders=array();
    	if($order_id!==false&&count($order_id)>0){
    		$sql="id in (".implode(",",$order_id).")";
    		$orders=Db::name("order")->where($sql)->group("unique_sn")->order("-unique_sn desc")->select();
    		if($orders!==false&&count($orders)>0){
    			
    			$orders=order_attach($orders);
    			foreach($orders as $key=>$value){
    				
    				//获取最原始创单二维码数据
    				$order_attach=unserialize($value['order_attach']);
    				$qrcode=$order_attach['qrcode'];

    				//获取二维码base64
    				//$base64=qrcode($qrcode);
    				//$orders[$key]['qrcode']='data:image/jpeg;base64,'.$base64;
    			}
    		}
    	}
    	
    	
    	$this->assign("orders",$orders);
    	$this->assign("schedule",$schedule);
    	
    	$multiple=input("multiple");
    	if(!empty($multiple)){					//ajax输出单个排产单打印-用于多个排产单打印
    		echo $this->fetch("schedule_multiple");
    		exit();
    	}else{									//单个排产单打印
    		return $this->fetch();
    	}
    }
    
    //删除排产单项目
    public function del_schedual_item(){
    	$id = intval(input("param.id"));
    	$one=Db::name("schedule")->where(array('id'=>$id))->find();
    	if(!$one){
    		return array('status'=>2,'msg'=>'该项目已不存在!');
    	}
    	if($one['finished']==1){
    		return array('status'=>2,'msg'=>'该项目已报工不能删除!');
    	}
    	 
    	//删除
    	$data_del=Db::name("schedule")->where(array('id'=>$id))->delete();
    	if ($data_del!==false){
    
    		//删除已开始的工序
    		Db::name("flow_check")->where("schedule_id='$id'")->delete();
    
    		$sid=$one['sid'];
    		$orderid=$one['order_id'];
    		//更新订单已经有那些项目排产
    		$list=Db::name("schedule")->where("order_id='$orderid'")->select();
    		if($list!==false&&count($list)>0){
    			$gx_schedule=array();
    			foreach($list as $val){
    				$gx_schedule[]=$val['gx_name'];
    			}
    			$gx_schedule=array_unique($gx_schedule);
    			$new_gx_schedule=serialize($gx_schedule);
    			//更新订单被安排的工序
    			Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule));
    		}else{
    			Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>'0','gx_schedule'=>''));
    		}
    		
    		$this->del_scheduals(array($sid));
    
    		return array('status'=>1);
    
    	}else{
    		return array('status'=>2,'msg'=>'删除失败，请重试');
    	}
    }
    
    //批量删除排产单项目
    public function del_schedual_items(){
    	
    	$id = input("param.id");
    	$ids=array();
    	if(strpos($id, ",")){
    		$ids=explode(",",$id);
    	}else{
    		$ids[]=intval($id);
    	}
    	
    	foreach($ids as $k=>$val){
    		if(intval($val)<=0){
    			unset($ids[$k]);
    		}
    	}
    	
    	if(count($ids)<=0){
    		return array('status'=>2,'msg'=>'请提交要删除的排产项目');
    	}
    	
    	$list=Db::name("schedule")->where("id in (".implode(",",$ids).")")->select();
    	
    	if(!$list||(is_array($list)&&count($list)<=0)){
    		return array('status'=>2,'msg'=>'排产项目不存在!');
    	}
    	
    	$order_list=array();
    	$main_id=array();//存储schedule_summary表的id
    	//已报工的就不删除
    	foreach($list as $key=>$value){
    		if($value['finished']==1){//已报工就不删除
    			unset($list[$key]);
    			continue;
    		}
    		$main_id[$value['sid']]=$value['sid'];
    		$order_list[$value['order_id']][]=$value;
    	}
    	
    	if(count($order_list)<=0){
    		return array('status'=>2,'msg'=>'所有排产项目已报工');
    	}
    	
    	$suc_ordernum=$item_num=0;
    	//遍历所有的排产子项目
    	foreach($order_list as $orderid=>$items){
    		$schedule_id=array();//所有排产子项目的id
    		foreach($items as $value){
    			$schedule_id[]=$value['id'];
    		}
    		
    		$data_del=false;
    		if(count($schedule_id)>0){
    			$item_num+=count($schedule_id);
    			$data_del=Db::name("schedule")->where("id in (".implode(",",$schedule_id).")")->delete();
    		}
    		
    		if ($data_del!==false){
    			//更新订单已经有那些项目排产
    			$list=Db::name("schedule")->where("order_id='$orderid'")->select();
    			if($list!==false&&count($list)>0){
    				$gx_schedule=array();
    				foreach($list as $val){
    					$gx_schedule[]=$val['gx_name'];
    				}
    				$gx_schedule=array_unique($gx_schedule);
    				$new_gx_schedule=serialize($gx_schedule);
    				//更新订单被安排的工序
    				Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule));
    			}else{
    				Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>'0','gx_schedule'=>''));
    			}
    	
    			$suc_ordernum++;
    		}
    	}
    	
    	//判断主记录下面还有没有小记录
    	$this->del_scheduals($main_id);
    	
    	return array('status'=>1,'success'=>$suc_ordernum,'item'=>$item_num);
    }
    
    public function getgxdata(){
        if (request()->isAjax()){
            $gx_name = input("id");
            $time = input("day");
            $sto_time = strtotime($time);
            $end_tiem = strtotime("+1 day",$sto_time);
            $date = date("Y-m-d",$sto_time+(24*3600));
            //单位表
            $unit_list = @include APP_DATA.'ab_unit.php';
            //获取该工序详情
            $gx_detai = Db::name("gx_list")->where("dname='$gx_name'")->find();
            $return_data = [];//返回数组
            $return_data['day_value'] = $gx_detai['work_value'].'/'.$unit_list[$gx_detai['work_unit']]['label'];
            $return_data['order_num'] = 0;
            $return_data['area'] = 0;
            $return_data['snum'] = 0;
            $return_data['s_num'] = 0;
            //该工序当前时间已排产订单数据
            $data = Db::name("schedule")->field('order_id as id')->where("gx_name='$gx_name' and do_time>=$sto_time and do_time<$end_tiem and finished=0")->select();
            if (!empty($data)){
                $result=order_attach($data);
                $return_data['order_num'] = count($data);
                foreach ($result as $k=>$val){
                    $return_data['area'] += floatval($val['area']);
                    $return_data['snum'] += floatval($val['snum']);
                    $return_data['s_num'] += floatval($val['screenwin']);
                }
                return ['code'=>0,'data'=>$return_data];
                exit();
            }
            return ['code'=>0,'data'=>$return_data];
        }
    }
    
    //删除排产单
    public function del_schedual(){
    	$id = intval(input("param.id"));
    	$where['id']=$id;
    	$data_del = Db::name("schedule_summary")->where($where)->delete();
    	if ($data_del!==false){
    		
    		$schedulelist=Db::name("schedule")->where(array('sid'=>$id))->field("id,order_id,gx_name")->select();
    		$schedule_id=array();
    		//更新订单是否已排产和已排产工序
    		foreach($schedulelist as $value){
    			$orderid=$value['order_id'];
    			$list=Db::name("schedule")->where("order_id='$orderid' and sid!=$id")->select();
    			if($list!==false&&count($list)>0){
    				$gx_schedule=array();
    				foreach($list as $val){
    					$gx_schedule[]=$val['gx_name'];
    				}
    				$gx_schedule=array_unique($gx_schedule);
    				$new_gx_schedule=serialize($gx_schedule);
    				//更新订单被安排的工序
    				Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>1,"gx_schedule"=>$new_gx_schedule));
    			}else{
    				Db::name("order")->where(array('id'=>$orderid))->update(array("is_schedule"=>'0','gx_schedule'=>''));
    			}
    			
    			$schedule_id[]=$value['id'];
    		}
    		
    		Db::name("schedule")->where(array('sid'=>$id))->delete();
    		//删除已开始的工序
    		Db::name("flow_check")->where("schedule_id in (".implode(",",$schedule_id).")")->delete();

    		return array('status'=>1);
    		
    	}else{
    		return array('status'=>2);
    	}
    }
    
    //固定流水作业-设置加急单
    public function seturgent(){
    	$uid = session('gid');
    	//订单工序筛选--当前用户组添加的工序
    	$gx_list = Db::name('gx_list')->distinct(true)->field("dname")->where("cid=$uid")->order("orderby asc,id asc")->select();
    	if($gx_list===false||count($gx_list)<=0){
    		$gx_list=array();
    	}
    	$this->assign('gx_list',$gx_list);
    	//颜色
    	$color=Db::name("order")->field("color")->group("color")->select();
    	if($color!==false){
    		$this->assign('color',$color);
    	}
    	
    	return $this->fetch();
    }
    
    //ajax获取订单
    public function ajax_urgent(){
    	
    	$where.=$this->getWhere();
    	
    	$page=input("page")?input("page"):1;
    	$offset=20;
    	$start=$offset*($page-1);
    	 
    	$order_sql="endtime asc";
    	//排序方式
    	if(!empty($orderby)&&$orderby!=''){
    		$orderby=explode("_",$orderby);
    		$order_sql=$orderby[0]." ".$orderby[1];
    	}
    	
    	//@hj 2020/03/05 自定义高级搜索
    	$searchsql=$this->senior_search("a.");
    	if($searchsql!=''){
    		$where.=" and ".$searchsql;
    	}
    	
    	//获取订单列表
    	$order_lis = Db::name('order')->alias("a")
    	->where("$where")->order($order_sql)->limit($start,$offset)->select();
    	//echo Db::name('order')->getLastSql();
    	//exit();
    	//读取缓存
    	if($order_lis===false||count($order_lis)<=0){
    		echo '';
    		exit();
    	}
    	
    	//缓存里面的工序组
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	if(!$gx_group){
    		exit("请检查工序组缓存文件gx_group");
    	}
    	
    	//合并字段
    	$opc=order_attach($order_lis);
    	 
    	if($opc){
    		foreach ($opc as $key=>$order){
    			$orderid = $order['id'];
    			$gid = $order['gid'];
    			$status = $order['status'];
    			$outstatus=$order['outstatus'];
    		
    			$order_id[]=$orderid;
    		
    			$gxL = array();
    			$opc[$key]['iswarm'] = 0;
    		
    			//预警
    			if ($day){
    				$facttime = time()+($day['day']*24*3600);
    				if ($facttime>=strtotime($order['endtime']) && $order['endstatus']==1&&$order['pause']==0&&$order['repeal']==0){
    					$opc[$key]['iswarm'] = 1;
    				}
    			}
    		
    			$groups=array();//将所有$g_result按cache_id再分组保存
                $line_id = explode(',', $order['gxline_id']);
    			$line_id=count($line_id)>0?$line_id:array("0");
    			//获取
    			$g_result = Db::name('gx_group')->whereIn("lid",$line_id)->order("id asc")->select();
    			$group_id=array();
    			foreach($g_result as $g_val){
    				$group_id[]=$g_val['id'];
    				 
    				$cache_id=$g_val['cache_id'];
    				$cache_order=$gx_group[$cache_id]['order'];//组别排序
    				$cache_name=$gx_group[$cache_id]['name'];//组别名称
    				$groups[$cache_order]['name']=$cache_name;
    				$groups[$cache_order]['id'][]=$g_val['id'];
    			}
    			if(count($group_id)<=0){
    				$group_id=array("0");
    			}
    			$gx_list=Db::name('gx_list')->where("gid in (".implode(",",$group_id).")")->order("orderby asc,id asc")->select();
    			$ggx_list=array();//用组id分组数组
    			if($gx_list!==false){
    				foreach($gx_list as $gx_val){
    					$ggx_list[$gx_val['gid']][]=$gx_val;
    				}
    			}
    		
    			//按order排序
    			ksort($groups);
    		
    			foreach($groups as $gk=>$va){
    		
    				$child_group_id=$va['id'];//每个gx_line下面的同名的多个小组别的id
    				$child_group=array();
    				foreach($child_group_id as $cc){//$cc 是group的id
    					foreach ($g_result as $kc=>$row){
    						if($row['id']==$cc){
    							$child_group[]=$row;
    						}
    					}
    				}
    		
    				$crr = array();
    				$tcrr=array();
    				foreach ($child_group as $kc=>$row){
    					$id = $row['id'];
    					//获取是否出库的
    					$inouts=$row['inouts'];
    					 
    					$lid = $ggx_list[$id];
    					 
    					if ($lid){
    		
    						//获取所有报工记录
    						$checks=$this->order_flow_check($lid,$orderid);
    						 
    						//组合工序数组
    						$lid=combine_order_flow($orderid, $lid, $checks, $inouts, $outstatus);
    						 
    						foreach($lid as $lv){
    							$tcrr[]=$lv;
    						}
    						 
    						$crr[0]=$tcrr;
    					}	//end of if
    					 
    				}//end of for
    		
    				if(count($crr)>0){
    					$crr=$this->sortgx($crr);
    					$gxL[]=$crr;
    				}
    				 
    			}
    		
    			//工序的嵌入
    			if (!isset($opc[$key]['dname'])){
    				$opc[$key]['dname'] = array();
    			}
    			$opc[$key]['dname'] = $gxL;
    		}
    	}
    	
    	$this->assign('orderl',$opc);
    	 
    	echo $this->fetch();
    	exit();
    }
    
    //设置加急
    public function isurgent(){
    	$id=intval(input("id"));
    	$state=intval(input("state"));
    	$ok=M("order")->where("id='$id'")->update(array('isurgent'=>$state));
    	if($ok){
    		$status=1;
    	}else{
    		$status=0;
    	}
    	return array('status'=>$status);
    }
    
    //固定流水作业-打印排产单
    public function print_fixed(){
    	
    	return $this->fetch();
    }
    
    //生成唯一的固定排产单号
    private function fix_schedule_no(){
    	$now=time();
    	//查询今天内最大的号码--十万位
    	$today=timezone_get(1);
    	$begin=$today['begin'];
    	$end=$today['end'];
    	 
    	$next_no="00001";
    	$max=Db::name("fixed_schedule")->field("schedule_no")->where("addtime>=$begin and addtime<=$end")->order("schedule_no desc")->find();
    	if($max!==false&&$max['schedule_no']!=''){
    		$max_no=substr($max['schedule_no'],10);
    		$max_no=intval($max_no)>0?intval($max_no)+1:1;
    		$next_no=$this->patch_zero(strval($max_no));
    	}
    	return "FPC".date('Ymd').$next_no;
    }
    
    //添加固定工序排产单
    public function save_fixed_schedule(){
    	$now=time();
    	 
    	$uid=session("uid");//当前用户的id
    	$gxname=ctrim(input("gxname"));//工序名称
    	
    	//过滤ID
    	$ids=input("post.ids/a");//订单ID
    	$orderid=array();
    	foreach($ids as $value){
    		$id=intval($value);
    		if(!empty($id)){
    			$orderid[]=$id;
    		}
    	}
    	
    	if(count($orderid)<=0){
    		//没有订单
    		return array("status"=>'2');
    	}
    	
    	sort($orderid);
    	
    	$list=M("fixed_schedule")->where("gx_name='$gxname'")->select();
    	//判断是否有一摸一样的排产单是就返回id不是则新建
    	$sid=0;//返回的排产单
    	$same=false;//找出有相同工序名和相同订单项目的记录
    	if($list&&count($list)>0){
    		foreach($list as $value){
    			$ids=unserialize($value['orderid']);
    			if(count($ids)==count($orderid)){
    				$diff=false;
    				foreach($ids as $id){
    					if(!in_array($id, $orderid)){
    						$diff=true;
    						break;
    					}
    				}
    				if(!$diff){//没有出现不相同的订单id,即全部订单id相同
    					$same=$value['id'];
    				}else{
    					continue;
    				}
    			}
    		}//end of foreach
    	}
    	
    	if(!$same){
    		$one=array();
    		$one['schedule_no']=$this->fix_schedule_no();
    		$one['uid']=$uid;
    		$one['gx_name']=$gxname;
    		$one['orderid']=serialize($orderid);
    		$one['addtime']=$now;
    		$sid=M("fixed_schedule")->insertGetId($one);
    	}else{
    		$sid=$same;
    	}
    	
    	return array("status"=>'1','sid'=>$sid);
    	
    }
    
    //打印固定排产单
    public function fixed_schedule_print(){
    	 
    	require '../vendor/phpqrcode/phpqrcode.php';
    	$cache=@include_once APP_DATA.'team_list.php';
    	//排产单主表id
    	$id=input("id","0","intval");
    	$schedule=Db::name("fixed_schedule")->where("id='$id'")->find();
    	
    	//读取排产打印单显示字段
    	$fieldList=array();
    	//读取字段
    	$fields=@include APP_DATA.'qrfield_type.php';
    	if(isset($fields['scheduallist'])){
    		foreach($fields['scheduallist'] as $value){
    			$fieldList[$value['fieldname']]=$value;
    		}
    	}
    	//显示排产字段
    	$this->assign("fieldList",$fieldList);
    	 
    	if($schedule['schedule_no']!=''){
    		$base64=qrcode($schedule['schedule_no'],3);
    		$schedule_no='data:image/jpeg;base64,'.$base64;
    		$this->assign("schedule_no",$schedule_no);
    	}
    	 
    	$unfinish_sql="";
    	if(!empty(input("unfinish"))){//只打印未完成的排产单
    		$unfinish_sql=" and finished!='1'";
    	}
    	//查找附表
    	//查找所有的订单Id
    	$order_id=unserialize($schedule['orderid']);
    	//查找所有的订单
    	$orders=array();
    	$square=$fans=$ordernum=0;
    	if($order_id!==false&&count($order_id)>0){
    		$sql="id in (".implode(",",$order_id).")";
    		$orders=Db::name("order")->where($sql)->order("isurgent desc,endtime asc")->select();
    		if($orders!==false&&count($orders)>0){
    			 
    			foreach($orders as $key=>$value){
    				$square+=floatval($value['area']);
    				$fans+=floatval($value['snum']);
    				$ordernum++;
    				//获取最原始创单二维码数据
    				//$order_attach=unserialize($value['order_attach']);
    				//$qrcode=$order_attach['qrcode'];
    				
    				//获取二维码base64
    				//$base64=qrcode($qrcode);
    				//$orders[$key]['qrcode']='data:image/jpeg;base64,'.$base64;
    			}
    			
    			$orders=order_attach($orders);
    		}
    	}
    	 
    	 
    	$this->assign("orders",$orders);
    	$this->assign("schedule",$schedule);
    	$this->assign("square",$square);
    	$this->assign("fans",$fans);
    	$this->assign("ordernum",$ordernum);
    	//单个排产单打印
    	return $this->fetch();
    	
    }
    
    //导出那些订单未排产
    public function notschedule(){
    	//所有订单
    	$orders=M("order")->field("id,ordernum,unique_sn")->where("repeal!='1' and pause!='1'")->select();
    	//所有排产订单
    	$time=timezone_get(6);
    	$gxs=$this->getAllGx();
    	$start_gx=$gxs['start'];
    	$end_gx=$gxs['end'];
    	$order_id=array();
    	$sql=array();
    	$prefix=config("database.prefix");
    	$child_sql="SELECT a.id,a.unique_sn ,c.starttime,c.endtime,c.orstatus FROM `{$prefix}order` as a left join `{$prefix}flow_check`as c on c.orderid=a.id WHERE (c.starttime>0 or c.endtime>0) GROUP by a.id order by c.endtime asc,c.starttime asc";
    	if(count($start_gx)>0){
    		$orstatus=array_keys($start_gx);
    		$sql[]="(starttime>={$time['begin']} and starttime<={$time['end']} and orstatus in (".implode(",",$orstatus)."))";
    	}
    	if(count($end_gx)>0){
    		$orstatus=array_keys($end_gx);
    		$sql[]="(((starttime>={$time['begin']} and starttime<={$time['end']}) or (endtime>={$time['begin']} and endtime<={$time['end']})) and orstatus in (".implode(",",$orstatus)."))";
    	}
    	
    	if(count($sql)>0){
    		$mainsql="select * from ($child_sql) as temp where ".implode(" or ", $sql);
    	}else{
    		//没设置工序不返回
    		$mainsql="select * from ($child_sql) as temp where id='0'";
    	}
    	
    	$list=Db::query($mainsql);
    	foreach($list as $k=>$value){
    		if(($value['starttime']<$time['begin']&&$value['starttime']>0)||$value['endtime']>$time['end']){
    			continue;
    		}
    		$order_id[]=$value['id'];
    	}
    
    	
    	$title=array('ordernum'=>$this->ordernum_explain,'unique_sn'=>$this->unique_name);
    	
    	$list=array();
    	foreach($orders as $value){
    		if(!in_array($value['id'],$order_id)){
    			$list[]=$value;
    		}
    	}
    	if(count($list)<=0){
    		exit("今年内没有找到未排产订单");
    	}
    	$site_cache=@include (APP_CACHE_DIR.'site_cache.php');
    	$creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
    	$exceltitle='未排产订单';
    	 
    	$this->one_export($title, $list, $creator, $exceltitle);
    }
}
