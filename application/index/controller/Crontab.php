<?php
namespace app\index\controller;
use think\Controller;
use think\captcha\Captcha;
use think\Db;
class Crontab extends Controller{
	
    //定时任务
    public function crontab(){
    	set_time_limit(0);
    	//超时工序
    	$this->overtime_gx();
    	
    	//删除没用工序
    	$this->auto_delete_flowheck();
    	
    	$this->write_log();
    	
    	echo json_encode(array('code'=>1,'msg'=>'执行成功'));
    	exit();
    }
    
    //日志
    public function write_log(){
    	$log=array();
    	$log['txt']="定时任务:".date('Y-m-d H:i:s',time());
    	M("log")->insertGetId($log);
    }
    
    //将超时工序写入缓存
    private function overtime_gx(){
    	//筛选按日为单位并且是要报开始和结束的工序
    	$gx_list=M("gx_list")->field("id,gid,lid,dname,work_value")->where("work_unit='7' and lid>0 and state='1'")->select();
    	if(!$gx_list){
    		$overtime_report=array();
    		overtime_report($overtime_report);
    	}
    		
    	$ids=$gxs=array();
    	foreach($gx_list as $value){
    		$ids[]=$value['id'];
    		$gxs[$value['id']]=$value;
    	}
    	if(count($ids)<=0){
    		$ids[]='0';
    	}
    		
    	$field="id,orderid,orstatus,starttime,endtime,state,status";
    	$sql="orstatus in (".implode(",",$ids).") and starttime>0";
    		
    	$total_check=M("flow_check")
    	->field($field)
    	->where($sql)
    	->count();
    		
    	$group=0;//每组，每组1000个记录
    	$offset=1000;
    		
    	if($total_check>0){
    		$group=ceil($total_check/$offset);
    	}
    		
    	//使用缓存
    	$gx_list=@include APP_DATA.'gx_list.php';
    		
    	$now=time();
    	//所有返回的信息
    	$cache=array();
    	//记录超时订单号
    	$ov_data=array();
    	for($i=0;$i<$group;$i++){
    		$start=$i*$offset;
    		//筛选所有报工记录在$gx_list中的工序
    		$flowCheck=M("flow_check")->field($field)->where($sql)->limit($start,$offset)->select();
    
    		if($flowCheck&&count($flowCheck)>0){
    			foreach($flowCheck as $value){
    				$id=$value['orderid'];
    				$gx_id=$value['orstatus'];
    				$work_value=$gxs[$gx_id]['work_value'];
    				if($work_value>0){
	    				$startTime=$value['starttime'];//开始时间
	    				$endTime=$value['endtime'];//报工的日期
	    				$limitDay=strtotime("+".$work_value." day",$startTime);
	    				$limitTime=strtotime(date('Y-m-d',$limitDay))+24*60*60-1;//正常完成时间
	    				if(empty($endTime)&&!empty($startTime)){//未报结束
	    					if($now>=$limitTime){
	    						//超时
	    						$ov_data[$id][]=$value['orstatus'];
	    					}
	    				}else if(!empty($endTime)&&$endTime>=$limitTime){
	    					//超时
	    					$ov_data[$id][]=$value['orstatus'];
	    				}
    				}
    			}
    				
    			//超时信息查询订单数据
    			if(count($ov_data)>0){
    				$ids=array_keys($ov_data);
    				$ids=count($ids)>0?$ids:array('0');
    				$orderList=M("order")->where("id in (".implode(",",$ids).")")->field("id,unique_sn")->select();
    				if($orderList&&count($orderList)>0){
    					foreach($orderList as $value){
    						$id=$value['id'];
    						$t=array();
    						$t['orderid']=$id;
    						$t['unique_sn']=$value['unique_sn'];
    						$t['over_gx']=array();
    						if(isset($ov_data[$id])){
    							foreach($ov_data[$id] as $gxid){
    								$t['over_gx'][$gxid]=$gx_list[$gxid]['dname'];
    							}
    						}
    						$cache[]=$t;
    					}
    				}
    			}
    		}//end of if
    
    	}//end of for
    		
    	overtime_report($cache);
    
    }
    
    //删除没用的报工
    public function auto_delete_flowheck(){
    	
    	$start=microtime(true);
    	//先查询订单
    	$orders=M("order")->field("id,gid,gxline_id")->order("id asc")->select();
    	$deleteNum=0;
    	foreach($orders as $value){
    		
//    		$gxs=gxlist_from_did_cache($value['gid']);
    		$lineId = explode(',',$value['gxline_id']);
    		$gxs = combine_gx_line($lineId);
    		if(!$gxs){
    			continue;
    		}
    		
    		$gx_ids=array();
    		foreach($gxs as $val){
    			$gx_ids[]=$val['id'];
    		}
    		
    		//查询所有的报工记录
    		$flow=M("flow_check")->field("id,orstatus")->where("orderid='{$value['id']}'")->select();
    		if(!$flow){
    			continue;
    		}
    		
    		foreach($flow as $val){
    			if(!in_array($val['orstatus'], $gx_ids)){
    				//删除没有用的工序
    				M("flow_check")->where("id='{$val['id']}'")->delete();
    				$deleteNum++;
    			}
    		}
    		
    	}
    	
    	$end=microtime(true);
    	
    	echo $end-$start;
    	echo '<br/>';
    	echo "删除数量:".$deleteNum;
    	
    	
    	
    }
}