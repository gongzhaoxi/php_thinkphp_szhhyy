<?php
namespace app\pad\controller;
use think\Controller;
use think\Db;
use think\facade\Request;
class Index extends Super
{
	
	private $where;
	
    public function index(){
    	
    	$users=M("login")->where("user_role='2' and del='0'")->order("id asc")->select();
    	$this->assign("users",$users);
    	
    	//高级搜索字段
    	$this->assign("searchField",$this->searchField);
    	//站点列表
    	return  $this->fetch();
    }
    
    //搜索附表
    private function search_attach($field,$value){
    	$orderid=M("order_attach")->where("fieldname='$field' and value like '%$value%'")->column("orderid");
    	if($orderid!==false&&count($orderid)>0){
    		return $orderid;
    	}else{
    		return array('0');
    	}
    }
    
    //搜索
    public function search(){
    	
    	$where=array();
    	//非暂停或废弃或完成订单
    	$where[]="pause!='1' and repeal!='1' and endstatus!=2 ";
    	
    	//@hj 2020/03/05 自定义高级搜索
    	$searchsql=$this->senior_search("");
    	if($searchsql!=''){
    		$where[]=$searchsql;
    	}
    	 
    	$this->where=implode(" and ",$where);
    	
    	$list=$this->ajax_list();
    	
    	if($list!==false){
    		$this->assign("list",$list);
    	}else{
    		echo 'none';
    		exit();
    	}
    	
    	echo $this->fetch("list");
    }
    
    //获取订单数据
    public function ajax_list(){
    	
    	$page=input("page")?input("page"):1;
    	$offset=15;
    	$start=$offset*($page-1);
    	//根据销售单号获取分组
    	if(!isset($this->system['orderbyfield'])||empty($this->system['orderbyfield'])){
    		$ordernum=M("order")->field("ordernum,uname,ordertime")->where($this->where)->group("ordernum")->order("id desc")->limit($start,$offset)->select();
    	}else{
    		$prefix=config("database.prefix");//数据库前缀
    		$ofield=$this->system['orderbyfield'];//排序字段
    		$ordernum=M("order")->field("a.ordernum,a.uname,a.ordertime")->alias("a")
    					->join("(SELECT `orderid`,`value` FROM `".$prefix."order_attach` WHERE `fieldname`='$ofield' ORDER by `value` desc) c ","c.orderid=a.id","LEFT")
    					->where($this->where)->group("a.ordernum")->order("c.`value` desc")->limit($start,$offset)->select();
    	}
    	if($ordernum===false||count($ordernum)<=0){
    		return false;
    	}
    
    	//根据销售编号查询订单
    	$order=array();
    	foreach($ordernum as $val){
    		$t=M("order")->where("ordernum='{$val['ordernum']}' and endstatus!=2")->order("unique_sn asc")->select();
    		if($t!==false&&count($t)>0){
    			$one=array();
    			$one['ordernum']=$val['ordernum'];
    			$one['uname']=$val['uname'];
    			$one['ordertime']=$val['ordertime']>0?date('Y-m-d',$val['ordertime']):'--';
    			$t=order_attach($t);
    			foreach($t as $value){
    				if(isset($value['orderstatus'])){
    					$one['orderstatus']=$value['orderstatus'];
    					break;
    				}
    			}
    			$one['list']=$t;
    			$order[]=$one;
    		}
    		continue;
    	}
    	
    	//首读缓存
    	$doclass=@include APP_DATA.'doclass.php';
    	if($doclass===false||count($doclass)<=0){
    		$doclass_list=M("doclass")->order("id asc")->select();
    		$doclass=array();
    		foreach($doclass_list as $key=>$value){
    			$doclass[$value['id']]=$value;
    		}
    	}
    	
    	$gx_line=@include APP_DATA.'lines.php';
    	if($gx_line===false||count($gx_line)<=0){
    		$lines=M("gx_line")->order("id asc")->select();
    		$gx_line=array();
    		foreach($lines as $key=>$value){
    			$gx_line[$value['id']]=$value;
    		}
    	}
    	
    	$gx_list=@include APP_DATA.'gx_list.php';
    	if($gx_list===false||count($gx_list)<=0){
    		$gxs=M("gx_list")->order("orderby asc")->select();
    		$gx_list=array();
    		foreach($gxs as $key=>$value){
    			$gx_list[$value['id']]=$value;
    		}
    	}
    	
    	$series=@include APP_DATA.'series.php';
    	if($series===false||count($series)<=0){
    		$series_list=M("series")->order("id asc")->select();
    		$series=array();
    		foreach($series_list as $key=>$value){
    			$series[$value['id']]=$value;
    		}
    	}
    	
    	$indata=array();
    	$indata['doclass']=$doclass;
    	$indata['gx_line']=$gx_line;
    	$indata['gx_list']=$gx_list;
    	
    	//根据gid 工艺总表查询对应系列和所有的工序
    	foreach($order as $key=>$one){
    		foreach ($one['list'] as $k=>$value){
	    			$orderid=$value['id'];//订单Id
	    			$did=$value['gid'];//doclass表的id
	    			//查找系列
	    			$series_id=$value['series_id'];//订单自带
	    			if($series_id==0||empty($series_id)){
	    				$series_id=$doclass[$did]['series_id'];
	    			}
	    			
	    			$series_name=$series[$series_id]['xname'];//重新查找一次-怕设置已改，但是订单没更新
	    			$one['list'][$k]['series_name']=$series_name;
	    			
	    			//读取所有的工序
	    			$gxs=gxlist_from_did_cache($did,$indata);
	    			//读取所有报工情况
	    			if($gxs){
	    				$checks=$this->order_flow_check($gxs, $orderid);
	    				//每个小工序的完成/异常情况
	    				foreach($gxs as $kc=>$val){
	    					$gxid = $val['id'];
	    					//查找对应工序的审核时间
	    					$fkey=$orderid."_".$gxid;
	    					$flow = isset($checks[$fkey])?$checks[$fkey]:false;
	    					
		    					$gxs[$kc]['fid'] = 0;
		    					$gxs[$kc]['endtime'] = 0;
		    					$gxs[$kc]['isbad'] = 0;
		    					$gxs[$kc]['error_time'] = 0;
		    					$gxs[$kc]['isover'] = 0;
		    					$gxs[$kc]['text'] = "";
		    					$gxs[$kc]['starttime'] = 0;
		    					$gxs[$kc]['name'] = "";
		    					$gxs[$kc]['isback'] = 0;
		    					$gxs[$kc]['fid'] = 0;
		    					$gxs[$kc]['gxid'] = $fid;
		    					$gxs[$kc]['orderid'] = $orderid;
		    					$gxs[$kc]['bname'] = '';
	    					
	    					if ($flow){
	    						$gxs[$kc]['fid'] = $flow['id'];
	    						$gxs[$kc]['endtime'] = $flow['endtime'];//报工结束时间
	    						$gxs[$kc]['isbad'] = $flow['state'];
	    						$gxs[$kc]['error_time'] = $flow['error_time'];
	    						$gxs[$kc]['isover'] = $flow['status'];
	    						$gxs[$kc]['text'] = $flow['stext'];
	    						$gxs[$kc]['starttime'] = $flow['starttime'];
	    						$gxs[$kc]['name'] = $flow['uname'];
	    						$gxs[$kc]['isback'] = $flow['isback'];
	    						$gxs[$kc]['content'] = $flow['text'];
	    						$gxs[$kc]['gxid'] = $gxid;
	    						$gxs[$kc]['orderid'] = $orderid;
	    					}
	    				}
	    			}
	    			$one['list'][$k]['gx_list']=$gxs?$gxs:array();
    		}
    		$order[$key]=$one;
    	}
    	
    	return $order;
    }
    
    //检查当前报工者是否可以对当前小工序报工
    public function check_mygx(){
    	
    	$uid=intval(input("uid"));
    	$gxid=intval(input("gxid"));
    	
    	$user=M("login")->field("id,tid")->where("id='$uid'")->find();
    	if($user===false||$user['tid']<=0||empty($user['tid'])){
    		return array('status'=>'0','msg'=>'该用户不能对该工序报工，未绑定班组');
    	}
    	
    	$team_gx=M("team_gx")->where("tid='{$user['tid']}'")->field("ngx_id")->find();
    	
    	if($team_gx===false||empty($team_gx['ngx_id'])){
    		return array('status'=>'0','msg'=>'该班组未绑定工序');
    	}
    	
    	$ngx_id=unserialize($team_gx['ngx_id']);
    	$arr_gx=array();
    	foreach ($ngx_id as $lid=>$arr){
    		foreach($arr as $val){
    			if($val>0){
    				$arr_gx[]=intval($val);
    			}
    		}
    	}
    	
    	if(in_array($gxid,$arr_gx)){
    		return array('status'=>'1');
    	}else{
    		return array('status'=>'0','msg'=>'该用户不能对该工序报工');
    	}
    }
    
    /**
     *   异常报工
     *   param: string ordername
     *   param: int uid
     */
    public function diff(){
       
    	$list= input("list/a");
        $uid = intval(input('uid'));
        $content = input("content");
        
        $now=time();
        
        //除客户外都可以报异常
        $user=M("login")->field("user_role")->where("id='$uid'")->find();
        if($user['user_role']==3){
        	 return array('status'=>0,'msg'=>'客户不能报异常');
        }
        
        
        $return=array();
        
        foreach($list as $value){
        	$gxid = $value['gxid'];
        	$orderid = $value['orderid'];
        	//查询审核工序是否存在
        	$exist = Db::name('flow_check')->where("orstatus='$gxid' and orderid='$orderid'")->find();
        	$txt=$excep_text=$dtime='';
        	if ($exist){
        		$result = Db::name('flow_check')
        						->where("orstatus='$gxid' and orderid='$orderid'")
        						->update(array('cid'=>$uid,'state'=>1,'error_time'=>$now,'stext'=>$content));
        		
        		if($exist['endtime']>0){
        			$txt="完成";
        			$excep_text='异常';
        			if($exist['status']==1){
        				$excep_text.="|超时";
        			}
        			$dtime=date('Y-m-d',$exist['endtime']);
        		}else{
        			$txt="未开始";
        			$excep_text='异常';
        		}
        	}else {
        		$arr = array('cid'=>$uid,'orderid'=>$orderid,'orstatus'=>$gxid,'stext'=>$content,'state'=>1,'error_time'=>$now);
        		$result = Db::name('flow_check')->insert($arr);
        		$txt="未开始";
        		$excep_text='异常';
        	}
        	
        	$key=$orderid."_".$gxid;
        	$return[$key]['txt']=$txt;
        	$return[$key]['excep']=$excep_text;
        	$return[$key]['time']=$dtime;
        	
        }

        if ($result!==false){
            return array('status'=>1,'msg'=>'成功','list'=>$return);
        }else {
            return array('status'=>0,'msg'=>'反馈失败');
        }
        
    }
    
 	//修改报工
    public function change_order(){
        $list = input("list/a");
        $uid = intval(input("uid"));
        $time = time();
        $in_flow = null;
        $msg = '审核失败';
        //错误的信息
        $error=array();
        //返回订单编号
        $return=array();
        
        //判断值是否存值
        if (empty($list)){
            echo json_encode(array('code'=>1,'msg'=>'缺少参数'));
            exit();
        }
			
		// 结束
		for($i = 0; $i < count ( $list ); $i ++) {
			$orid = $list [$i] ['orderid'];
			$gxid=$list [$i] ['gxid'];
			$in_num = '0'; // 入库数量
			$order = Db::name ( 'order' )->where ( "id='$orid'" )->find ();
			$gid = $order['gid'];//doclass表的的id
			
			$sql = "id='$gxid'";
			$gx = Db::name ( 'gx_list' )->where ( $sql )->find ();
			
			$gx_id = $gx ['id'];
			
			/*if(isset($this->system['reportorder'])&&$this->system['reportorder']==1){
			 //检测是否可报工
			 $canReport=check_reported($orid,$gid,$gx_id);
			 if(!$canReport){
			 $err=array();
			 $err['orderid']=$orid;
			 $err['gx_id']=$gx_id;
			 $err['msg']="非顺序报工";
			 $error[$rid]=$err;
			 continue;
			 }
			 }*/
			
			if ($order ['pause'] == 1 || $order ['repeal'] == 1) {
				$err = array ();
				$err ['orderid'] = $orid;
				$err ['gx_id'] = $gx_id;
				$err ['msg'] = '订单已' . ($order ['pause'] == 1 ? '暂停' : '作废');
				$error [$orid] = $err;
				continue;
			}
			
			if ($gx) {
				
				// 查询是该工序是否需要开始和结束时间，如果需要开始时间，但又没记录，则返回错误
				$gx_state = $gx ['state'];
				$work_unit = $gx ['work_unit']; // '日/次 需要判断是否超时
				
				$check = Db::name ( 'flow_check' )->where ( "orderid=$orid and orstatus=$gx_id " )->find ();
				
				// 只需要报结束时间,新增完成时间记录
				if ($check === false || empty ( $check ['id'] )) {
					$in_data = array ( 
							'orderid' => $orid,
							'uid' => $uid,
							'orstatus' => $gx_id,
							'endtime' => $time,
							'in_num' => $in_num,
							'media' => '1'
					);
					$fid = Db::name ( 'flow_check' )->insertGetId( $in_data );
					$check = Db::name ( 'flow_check' )->where ( "id='$fid'" )->find ();
				}else{
					$in_data = array ( 
							'uid' => $uid,
							'endtime' => $time,
					);
					$fid=$check['id'];
					Db::name ( 'flow_check' )->where ( "id='$fid'" )->update ( $in_data );
				}
				
				if ($check) {
					
					// 工序组ID
					$groupid = $gx ['gid'];
					$group = Db::name ( 'gx_group' )->field ( 'id,lid,inouts,cache_id' )->where ( "id='$groupid'" )->find ();
					// 判断该组是否出入库，如果是的话就查询其他流程查询同组其他流程，1是入库，2是出库
					$inouts = $group ['inouts'];
					
					// 判断出入库
					if ($inouts == 1 || $inouts == 2) {
						
						// 判断是否同组工序已出库或者入库--start
						// 查询同组其他工序
						$isEnd = false; // 标记是否全部工序已完成
						// 查询工艺gx_line
						$gx_line = getline_from_did ( $gid );
						$line_id=count($gx_line)>0?$gx_line:array($group['lid']);
						$cache_id = $group ['cache_id']; // 一条doclass由多个gx_line组成，每个gx_line有可能有多个入库组的工序，cache_id是入库组的id标记
						$samename_group = Db::name ( 'gx_group' )->where ( "lid in (" . implode ( ",", $line_id ) . ") and cache_id='$cache_id'" )->column ( "id" );
						if ($samename_group === false && count ( $samename_group ) <= 0) {
							$samename_group = array (
									$groupid 
							);
						}
						$brother_gx = Db::name ( "gx_list" )->field ( "id" )->where ( "gid in (" . implode ( ",", $samename_group ) . ") and id!='$gx_id'" )->select ();
						
						if ($brother_gx === false || count ( $brother_gx ) <= 0) { // 没其他工序
							$isEnd = true;
						} else {
							$brother_gx_ids = array ();
							foreach ( $brother_gx as $val ) {
								$brother_gx_ids [] = $val ['id'];
							}
							$bsql = implode ( ",", $brother_gx_ids );
							// 查询订单的其他审核工序是否已完工
							$brother_flow = Db::name ( 'flow_check' )->field ( "endtime" )->where ( "orderid=$orid and orstatus in ($bsql) and endtime>0" )->select ();
							if ($brother_flow !== false && count ( $brother_flow ) == count ( $brother_gx_ids )) {
								$isEnd = true;
							}
						}
						
						// 查询flow_check同组工序是否已经完成
						if ($inouts == 1) { // 入库
							$up = array ();
							$up ['status'] = '1'; // 有一个工序是完成入库就标记订单为已入库
							if ($isEnd) { // 全部入库工序完成就标记为完成（不预警）
								$up ['endstatus'] = '2';
								$up ['intime'] = $time;
							}
							$back_data = Db::name ( 'order' )->where ( "id='$orid'" )->update ( $up );
						} else if ($inouts == 2) { // 出库
							                      // $back_data = Db::name('order')->where("id='$orderid'")
							                      // ->update(array('outstatus'=>1));
						}
					} // 结束判断出入库是否已完成
					  
					// 更新工序完成时间组员数量等
					/*$manstr = ($check ['man'] != '' ? ($check ['man'] . "," . $man) : $man);
					$man = str_unique ( $manstr );
					
					$in_data = array ();
					$in_data = [ 
							'uid' => $uid,
							'endtime' => $time,
							'man' => $man 
					];
					if ($inouts == 1) {
						$in_data ['in_num'] = $in_num;
					}
					$ok = Db::name ( 'flow_check' )->where ( "orderid=$orid and orstatus=$gx_id" )->update ( $in_data );
					*/
					// 是否超时
					/*
					$start = $check ['starttime'];
					$num = $gx ['work_value'];
					$over_time = $start + ($num * 60 * 60 * 24);
					if ($time > $over_time && $gx_state == 1 && $work_unit == 7) { // 需要报开始和结束时间的工序，则提醒超时
						$in_flow = Db::name ( 'flow_check' )->where ( "orderid=$orid and orstatus=$gx_id" )->update ( array (
								'status' => 1 
						) );
					}*/
					
					$txt="完成";
					$excep_text=array();
					if($check['state']==1){
						$excep_text[]='异常';
					}
					if($check['status']==1){
						$excep_text[]="超时";
					}
					$dtime=date('Y-m-d',$time);
					
					$key=$orid."_".$gx_id;
					$return[$key]['txt']=$txt;
					$return[$key]['excep']=implode("|",$excep_text);
					$return[$key]['time']=$dtime;
					
					
				}
			} // end of if($gx)
		}//end of for
        
        
        if(count($error)>0){
        	$code=0;
        }else{
        	$code=1;
        	$msg="报工成功";
        }
        echo json_encode(array('code'=>$code,'msg'=>$msg,'error'=>$error,'list'=>$return));
    }
    
	//ajax获取字段关键词搜索
	public function ajax_orderattach(){
		$keyword=ctrim(input("keyword"));
		$field=ctrim(input("field"));
		$list=M("order_attach")->field("orderid,fieldname,value")->where("fieldname='$field' and `value` like '%$keyword%'")->group("value")->select();
		if(empty($keyword)){
			echo json_encode(array('status'=>2,'msg'=>'请输入关键词'));
			exit();
		}
		if(empty($field)){
			echo json_encode(array('status'=>2,'msg'=>'请提交字段名称'));
			exit();
		}
		
		if($list!==false&&count($list)>0){
			echo json_encode(array('status'=>1,'msg'=>'ok','list'=>$list));
		}else{
			echo json_encode(array('status'=>3,'msg'=>'no data'));
		}
		exit();
	}
    
  
}
