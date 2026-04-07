<?php
namespace app\manage\controller;
use think\Controller;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Facade\Env;
use think\facade\Request;
class Method extends Super
{
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
	
	public function initialize(){
		parent::initialize();
	
		if (empty(session('xxencrypt'))){
			$this->redirect('Login/login');
		}
	}
	
	//生成最新的加密秘钥
	public function make_key(){
		$your_code='';
		echo authcode($your_code,"ENCODE");
		exit();
	}
	
    public function update_order(){
    	$list=Db::name("order_attach")->where("fieldname='produce_no'")->select();
    	$index=0;
    	foreach($list as $value){
    		$id=$value['orderid'];
    		Db::name("order")->where("id='$id'")->update(array("unique_sn"=>$value['value']));
    		$index++;
    	}
    	echo $index;
    }
    
    
    public function update_order_attach(){
    	$list=Db::name("order")->where(array())->select();
    	$index=0;
    	foreach($list as $value){
    		$id=$value['id'];
    		$ordertime=date('Y-m-d',$value['ordertime']);
    		$endtime=date('Y-m-d',$value['endtime']);
    		Db::name("order_attach")->where("orderid='$id' and fieldname='ordertime'")->update(array("value"=>$ordertime));
    		Db::name("order_attach")->where("orderid='$id' and fieldname='endtime'")->update(array("value"=>$endtime));
    		$index++;
    	}
    	echo $index;
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
    
    //更新排产单号
    public function update_schedule(){
    	
    	$list=Db::name("schedule_summary")->field("id,schedule_no")->order("addtime asc")->select();
    	$count=0;
    	foreach($list as $value){
    		
    		if($value['schedule_no']==''){
	    		//获取排产单批次号
	    		$schedule_no=$this->schedule_no();
	    		
	    		$sum=array();
	    		$sum['schedule_no']=$schedule_no;
	    		Db::name("schedule_summary")->where("id='{$value['id']}'")->update($sum);
    		}
    		$count++;
    	}
    	echo $count;
    }
    
    //通过bg_schedule表更新对应的flow_check表的schedule_id
    public function update_schedule_id(){
    
    	$list=Db::name("schedule")->field("id,order_id,gx_id")->select();
    	$count=0;
    	foreach($list as $value){
    
    			$sum=array();
    			$sum['schedule_id']=$value['id'];
    			Db::name("flow_check")->where("orderid='{$value['order_id']}' and orstatus='{$value['gx_id']}'")->update($sum);
    		
    		$count++;
    	}
    	echo $count;
    }
    
    //通过bg_schedule表更新对应ordernum
    public function update_schedule_ordernum(){
    
    	$list=Db::name("schedule")->field("id,order_id,ordernum")->select();
    	$count=0;
    	foreach($list as $value){
    		$order=Db::name("order")->where("id='{$value['order_id']}'")->field("id,unique_sn")->find();
    		if($order!==false){
    			$one=array();
    			$one['ordernum']=$order['unique_sn'];
    			Db::name("schedule")->where("id='{$value['id']}'")->update($one);
    
    			$count++;
    		}
    	}
    	echo $count;
    }
    
    //批量更新下单时间
    public function update_ordertime(){
    	$list=Db::name("order")->field("id,gid,ordertime,order_attach")->where(array())->select();
    	//转换日期格式，防止出现年-月-日
    	$toreplace=array('年','月','日');
    	$replace=array('-','-','');
    	$count=0;
    	$now=time();
    	foreach($list as $value){
    		$order_attach=unserialize($value['order_attach']);
    		
    		$update=array();
    		/* if(trim($order_attach['ordertime'])!=''){
    			$ordertime=str_replace($toreplace,$replace, $order_attach['ordertime']);
    			if (empty($ordertime)){
    				$ordertime = time();
    			}else {
    				$ordertime = strtotime($ordertime);
    			}
    		}else{
    			$ordertime = time();
    		}  */
    		
    		$gid=$value['gid'];
    		if(trim($order_attach['qrcode'])!=''){
    			$str=explode("|", $order_attach['qrcode']);
    			$endtime=$str[9];
    			$endtime=str_replace($toreplace,$replace, $endtime);
    			if (empty($endtime)){
    				$doclass=Db::name('doclass')->field("day")->where("id='$gid'")->find();
    				$day='0';
    				if($doclass!==false&&$doclass['day']>0){
    					$day=$doclass['day'];
    				}
    				$endtime =$now+$day*24*60*60;
    			}else {
    				$endtime = strtotime($endtime);
    			}
    			
    			Db::name("order")->where("id='{$value['id']}'")->update(array('endtime'=>$endtime));
    			
    			$datetime=date('Y-m-d',$endtime);
    			Db::name("order_attach")->where("orderid='{$value['id']}' and fieldname='endtime'")->update(array("value"=>$datetime));
    			$count++;
    		}


    	}
    	echo $count;
    }
    
    //读取订单字段数
    public function list_order(){
    	$list=Db::name("order")->field("id,gid,ordertime,addtime,order_attach,unique_sn")->where(array())->order("addtime desc")->select();
    	foreach($list as $order){
    		$order_attach=unserialize($order['order_attach']);
    		$fields=explode("|",$order_attach['qrcode']);
    		$count=count($fields);
    		echo $order['unique_sn']."  ".date('Y-m-d H:i:s',$order['addtime'])."  ".$count;
    		echo '<br/>';
    		
    	}
    }
    
    //根据二维码数据更新一下order_attach表数据
    public function update_from_qrcode(){
    	
    	$fields=Db::name('qrcode_fields')
    	->field('fieldname,explains,orderby,is_system')
    	->where("status='0' and isqrcode='1'")->order("orderby asc,id asc")->select();
    	 
    	$field_order=$field_order1=array();//对后台字段排序
    	if($fields!==false&&count($fields)>0){
    		foreach($fields as $value){
    			$field_order[]=$value['fieldname'];
    			if($value['fieldname']!='winnum'){
    				$field_order1[]=$value['fieldname'];
    			}
    		}
    	}
    	
    	echo '<pre>';
    	print_r($field_order);
    	echo '</pre>';
    	
    	echo '<pre>';
    	print_r($field_order1);
    	echo '</pre>';
    	exit();
    	
    	$now=time();
    	
    	$list=Db::name("order")->field("id,gid,ordertime,order_attach")->where(array())->select();
    	$not_update=array("endtime","ordertime");
    	
    	
    	foreach($list as $value){
    		
    		$attach=unserialize($value['order_attach']);
    		
    		if(trim($attach['qrcode'])!=''){
    			$arr=explode("|", $attach['qrcode']);
    			
    			if(count($arr)>43){
    				
    			}
    			
    			foreach($field_order as $key=>$name){
    				if(!in_array($name, $not_update)){
    					Db::name("order_attach")->where("orderid='{$value['id']}' and fieldname='$name'")->update(array("value"=>$arr[$key]));
    				}
    				continue;
    			}
    			$count++;
    		}
    		
    	}
    	echo $count;
    }
    
    //打印最新版路线工艺的 工艺路线->工艺组->工序
    public function print_newgy(){
    	$lines=Db::name("gx_line")->where(array())->select();
    	foreach($lines as $key=>$value){
    		$group=Db::name("gx_group")->where(array("lid"=>$value['id']))->select();
    		
    		//工序
    		$gp=array();
    		foreach($group as $gk=>$gval){
    			$gx=Db::name("gx_list")->where(array("lid"=>$value['id'],'gid'=>$gval['id']))->select();
    			$gval['gx_list']=$gx;
    			$gp[]=$gval;
    		}
    		
    		$lines[$key]['group']=$gp;
    	}
    	
    	echo '<pre>';
    	print_r($lines);
    	echo '</pre>';
    }
    
    //测试函数
    public function test_getline_from_did(){
    	
    	$list=getdo_gp_gx(915);
    	echo '<pre>';
    	print_r($list);
    	echo '</pre>';
    }
    
    //按组别
    public function print_groups(){
    	//缓存里面的工序组
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	if(!$gx_group){
    		exit("请检查工序组缓存文件gx_group");
    	}
    	$groups=array();//将所有$g_result按cache_id再分组保存
    	
    	$line_id=getline_from_did(107);
    	$line_id=count($line_id)>0?$line_id:array("0");
    	//获取
    	$g_result = Db::name('gx_group')->where("lid in (".implode(",",$line_id).")")->order("id asc")->select();
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
    	
    	$gx_list=Db::name('gx_list')->where("gid in (".implode(",",$group_id).")")->order("orderby asc")->select();
    	$ggx_list=array();//用组id分组数组
    	if($gx_list!==false){
    		foreach($gx_list as $gx_val){
    			$ggx_list[$gx_val['gid']][]=$gx_val;
    		}
    	}
    	
    	//按order排序
    	ksort($groups);
    	
    	print_arr($groups);
    } 
    
    //更新排产单时间到已报工
    public function update_schedule_time(){
    	//目标工序
    	$gx_id=1705;
    	$list=M("flow_check")->where("orstatus='$gx_id'")->order("schedule_id desc")->select();
    	$checks=array();
    	foreach($list as $value){
    		$checks[$value['orderid']][]=$value;
    	}
    	
    	echo '<pre>';
    	print_r($checks);
    	echo '<pre>';
    	
    	
    	$result=array();
    	foreach($checks as $orderid=>$value){
    		if(count($checks[$orderid])<2){
    			continue;
    		}
    		
    		
    		
    		$schedule_id=0;
    		$starttime=0;
    		
    		foreach($checks[$orderid] as $one){
    			if($one['schedule_id']>0){
    				$schedule_id=$one['schedule_id'];
    				$starttime=$one['starttime'];
    				M("flow_check")->where("id='{$one['id']}'")->delete();
    				continue;
    			}
    		
    			$result[$orderid]=array('schedule_id'=>$schedule_id,"starttime"=>$starttime);
    			M("flow_check")->where("id='{$one['id']}'")->update(array('schedule_id'=>$schedule_id,"starttime"=>$starttime));
    		}
    		
    	}
    	
    	echo '<pre>';
    	print_r($result);
    	echo '<pre>';
    	 
    }
    
    //更新排产项目的完成时间和报工者
    public function update_schedule_status(){
   		$gx_ids=array(1703,1712,1713,1715,1705);
   		$checks=$updates=array();
   		$totals=0;
   		foreach($gx_ids as $gx_id){
   			$list=M("flow_check")->where("orstatus='$gx_id' and schedule_id>0 and endtime>0")->order("id asc")->select();
   			
   			foreach($list as $v){
   				$checks[]=$v;
   			}
   			
   			
   			foreach($list as $value){
   				$orid=$value['orderid'];
   				$uid=$value['uid'];
   				$time=$value['endtime'];
   				//更新排产
   				//如果有排产的话，就更新对应订单的排产完成状态
   				$schedule=Db::name('schedule')->field("id,sid")->where("order_id='$orid' and gx_id='$gx_id'")->find();
   				$updates[]=$schedule;
   				if($schedule!==false&&!empty($schedule['sid'])){
   					$id=$schedule['id'];
   					$sc_id=$schedule['sid'];
   					$user=Db::name('login')->where("id='$uid'")->find();
   					Db::name('schedule')->where("id='$id'")->update(array('do_uid'=>$uid,'do_uname'=>$user['uname'],'finished'=>1,'finished_time'=>$time));
   					//查看其它排产单是否已经全部完成
   					$total=Db::name('schedule')->where("sid='$sc_id'")->count();
   					$finished=Db::name('schedule')->where("sid='$sc_id' and finished='1'")->count();
   					if($finished==$total&&$finished>0){
   						//标记改排产单已全部完成
   						Db::name('schedule_summary')->where("id='$sc_id'")->update(array('isfinished'=>1));
   					}
   					
   					$totals++;
   				}
   				
   			}
   			
   			
   		}
   		
   		echo '<pre>';
   		print_r($checks);
   		echo '<pre>';
   		
   		echo '---------------------------------------------------------------';
   		echo '<pre>';
   		print_r($updates);
   		echo '<pre>';
   		echo '---------------------------------------------------------------';
   		echo $totals;
   		exit();
   		
    	
    }
    
    //iphone GBK乱码解码测试
    public function iphone_xcode(){
    	$str='200107-033,ÍõÓýÑó,±¬¿î100¶ÏÇÅÏµÍ³Æ½¿ª´°£¨´°É´Ò»Ìå£©,2020/01/31,2,2320*2230,ÄÚ·úÌ¼²¬ÒøÍâÉ°ÎÆ»Ò,0,ÎÞ¿îÊ½,1,5.1736,º¬µØÌ¨ËøÎ»¿ª1500£¬Íâ¿ªÏÂÐü´°,200107016,1ºÅ´°Ãµ¹åÔ°5-2-601 Ò»Â¥Î÷ÎÔ';
    	$str=iconv("UTF-8","ISO-8859-1",trim($str));
    	$str2=mb_convert_encoding($str, "UTF-8", "GBK");
    	//M("log")->insert(array("log"=>$str));
    	echo $str2;
    	exit();
    }
    
    //查找当天排产的排产单
    public function today_schedule(){
    	$today=timezone_get(1);
    	$start=$today['begin'];
    	$end=$today['end'];
    	
    	$schedule=M("schedule")->where("addtime>=$start and addtime<=$end")->select();
    	//echo '<pre>';
    	//print_r($schedule);
    	//echo '<pre>';
    	//echo '---------------------------------------------------------------';
    	echo count($schedule);
    	echo '---------------------------------------------------------------';
    	//exit();
    	//查找并且添加开始报工记录
    	$total=0;
    	foreach($schedule as $value){
    		$id=$value['order_id'];
    		$uid=$value['uid'];//报工的时候会改变
    		$gx_id=$value['gx_id'];
    		$do_time=$value['do_time'];
    		$schedule_id=$value['id'];
    		$in_data=array('orderid'=>$id,'uid'=>$uid,'orstatus'=>$gx_id,'starttime'=>$do_time,'in_num'=>'0','schedule_id'=>$schedule_id);
    		$one=Db::name('flow_check')->where("orderid='$id' and orstatus='$gx_id'")->find();
    		if($one===false||empty($one['id'])){
    			Db::name('flow_check')->insert($in_data);
    			$total++;
    		}else{
    			echo $schedule_id;
    			echo '<br/>';
    			
    			$orid=$one['orderid'];
   				$uid=$one['uid'];
   				$time=$one['endtime'];
   				//更新排产
   				//如果有排产的话，就更新对应订单的排产完成状态
   				$schedule=Db::name('schedule')->field("id,sid")->where("order_id='$orid' and gx_id='$gx_id'")->find();
   				$updates[]=$schedule;
   				if($schedule!==false&&!empty($schedule['sid'])){
   					$id=$schedule['id'];
   					$sc_id=$schedule['sid'];
   					$user=Db::name('login')->where("id='$uid'")->find();
   					Db::name('schedule')->where("id='$id'")->update(array('do_uid'=>$uid,'do_uname'=>$user['uname'],'finished'=>1,'finished_time'=>$time));
   					//查看其它排产单是否已经全部完成
   					$totals=Db::name('schedule')->where("sid='$sc_id'")->count();
   					$finished=Db::name('schedule')->where("sid='$sc_id' and finished='1'")->count();
   					if($finished==$totals&&$finished>0){
   						//标记改排产单已全部完成
   						Db::name('schedule_summary')->where("id='$sc_id'")->update(array('isfinished'=>1));
   					}
   					

   				}
    		}
    	}
    	
    	echo $total;
    }
    
    
    //更新订单的工艺
    public function update_did(){
    	$olist=M("order")->field("id,gid,unique_sn")->where("id>=562 and id<=577")->select();
    	if(!$olist){
    		exit("没订单");
    	}
    	
	    $dolist=Db::name("doclass")->order("id asc")->select();
		$doclass=array();
		if($dolist!==false&&count($dolist)>0){
			foreach($dolist as $key=>$value){
				$doclass[$value['id']]=$value;
			}
		}
		
		$succes=0;
		foreach($olist as $value){
			$did=$value['gid'];
			$orderid=$value['id'];
			if(isset($doclass[$did])){
				//找出同一系列的同名的工艺路线
				$series_id=$doclass[$did]['series_id'];
				$title=$doclass[$did]['title'];
				echo $title."<br/>";
				$same=M("doclass")->where("series_id='$series_id' and title='$title' and id!='$did'")->find();
				if($same&&!empty($same['id'])){
					M("order")->where("id='$orderid'")->update(array("gid"=>$same['id']));
					echo "相同".$title."------------------".$value['unique_sn']."<br/>";
					$succes++;
				}else{
					echo "不相同".$title."####################<br/>";
				}
			}
		}
		
    	echo "总共:".$succes;
    	
    }
    
    public function speed_test(){
    	echo $start=microtime(true);
    	echo '<br>';
    	$gx=array("冲孔","调试","扇入库");
    	$did=getdid_from_gxname($gx);
    	 
    	echo $end=microtime(true);
    	echo '<br>';
    	echo $end-$start;
    	print_arr($did);
    	 
    }
    
    //批量更新doclass的line_id
    public function update_doclass(){
    	$where="id<=105";
    	$list=M("series")->where($where)->column("id");
    	if(!$list||count($list)<=0){
    		exit("no");
    	}
    	
    	$doclass=M("doclass")->where("series_id in (".implode(",",$list).")")->select();
    	foreach($doclass as $value){
    		if($value['line_id']!=''){
    			$line_id=explode(",", $value['line_id']);
    			if(in_array(12, $line_id)){
    				foreach($line_id as $k=>$val){
    					if(12==$val){
    						$line_id[$k]=28;
    					}
    				}
    				sort($line_id);
    				$line_id=implode(",",$line_id);
    				
    				//echo $value['series_id']." : ".$line_id;
    				//echo '<br/>';
    				M("doclass")->where("id='{$value['id']}'")->update(array("line_id"=>$line_id));
    			}
    		}
    	}
    }
    
    public function repeat_doclass(){
    	
    	$series=M("series")->select();
    	$nseries=array();
    	foreach($series as $value){
    		$nseries[$value['id']]=$value['xname'];
    	}
    	
    	$list=array();
    	$doclass=M("doclass")->field("id,line_id,series_id,title")->select();
    	foreach($doclass as $value){
    		if($value['line_id']!=''){
    			$list[$value['series_id']][]=$value;
    		}
    	}
    	
    	$rep=array();
    	//排除重复
    	foreach($list as $sid=>$value){
    		
    		foreach($value as $one){
    			$line_id=$one['line_id'];
    			$did=$one['id'];
    			foreach($value as $one){
    				if($line_id==$one['line_id']&&$one['id']!=$did){
    					$rep[$nseries[$one['series_id']]][]=$one['series_id']." ".$one['title']." ID:".$one['id'];
    					break;
    				}
    			}
    		}
    		
    		
    	}
    	
    	print_arr($rep);
    	
    }
    
    //金云雀，遍历所有订单，并且根据订单的系列更新对应的doclass
    public function update_order_did(){
    	$orders=M("order")->field("id,gid,series_id")->order("id asc")->select();
    	$slist=M("series")->order("id asc")->select();
    	$series=array();
    	if(!$slist||!$orders){
    		exit("没数据");
    	}
    	foreach($slist as $value){
    		$series[$value['id']]=$value;
    	}
    	$success=0;
    	foreach($orders as $value){
    		$gid=$series[$value['series_id']]['gid'];
    		//echo $gid;
    		//echo '<br/>';
    		M("order")->where("id='{$value['id']}'")->update(array("gid"=>$gid));
    		$success++;
    	}
    	echo $success;
    }
    
    //判断是否有出库工序记录，没则新建
    public function build_outgx(){
    	$orders=M("order")->field("id,gid,outtime")->where("outstatus='1'")->select();
    	//查询入库工序
    	if(!$orders){
    		exit("没订单");
    	}
    	
    	foreach($orders as $value){
    		$orderid=$value['id'];
    		$did=$value['gid'];
    		
    		$lines=getline_from_did($did);
    		if(count($lines)>0){
    			//查询所有的入库工序
    			$sql="a.lid in (".implode(",",$lines).")";
    			$gxlist=Db::name('gx_list')->alias('a')->field('a.id,b.inouts')
    			->join('gx_group b','b.id=a.gid','LEFT')
    			->where("$sql and (b.inouts='1' or b.inouts='2')")
    			->select();
    			
    			//查询入库工序的是否已经全部入库
    			$inlist=$outlist=array();
    			if(!$gxlist||count($gxlist)<=0){
    				continue;
    			}
    			
    			foreach($gxlist as $val){
    				if($val['inouts']==1){
    					$inlist[]=$val;
    				}
    				if($val['inouts']==2){
    					$outlist[]=$val;
    				}
    			}
    		
    			
    			//如果同时有入库和出库工序才需要补齐出库
    			if(count($inlist)>0&&count($outlist)>0){
    				//查询是否都已经报工，并且已经出库
    				$ids=array();
    				foreach($inlist as $val){
    					$ids[]=$val['id'];
    				}
    				$ids_sql=implode(",",$ids);
    				$count=Db::name('flow_check')->where("orderid='$orderid' and orstatus in ($ids_sql) and issend='1'")->select();
    				if(count($count)==count($ids)&&count($count)>0){
    					$uid=1;
    					
    					echo "UID:$uid, 订单ID:".$value['id'];
    					print_arr($inlist);
    					print_arr($outlist);
    					 
    					//查询是否有出库报工
    					foreach($outlist as $val){
    						$tcheck=M("flow_check")->where("orderid='$orderid' and orstatus='{$val['id']}'")->find();
    						if(!$tcheck){
    							$in_data = array();
    							$in_data = ['orderid'=>$orderid,'uid'=>$uid,'orstatus'=>$val['id'],'endtime'=>$value['outtime']];
    							$in_flow = Db::name('flow_check')->insert($in_data);
    						}
    					}
    					
    				}
    				
    			}
    			
  
    		}
    		
    		
    	}
    }
    
    //更新订单面积
    public function update_area(){
    	$area_field="area";//面积字段名
    	$list=M("order_attach")->where("fieldname='$area_field'")->field("orderid,`value`")->group("orderid")->select();
    	foreach($list as $key=>$value){
    		$orderid=$value['orderid'];
    		$area=floatval($value['value']);
    		M("order")->where("id='$orderid'")->update(array('area'=>$area));
    		echo $key."----".$area;
    		echo '<br/>';
    	}
    	
    }
    
    //查找那些订单未排产
    public function notschedule(){
    //所有订单
    	$time=timezone_get(3);
    	$timesql=" and addtime>={$time['begin']} and addtime<={$time['end']} ";
    	$orders=M("order")->field("id,ordernum,unique_sn")->where("repeal!='1' and pause!='1' $timesql ")->select();
    	//所有排产订单
    
    	$gxs=$this->getAllGx();
    	$start_gx=$gxs['start'];
    	$end_gx=$gxs['end'];
    	$order_id=array();
    	$sql=array();
    	$prefix=config("database.prefix");
    	$child_sql="SELECT a.id,a.unique_sn ,c.starttime,c.endtime,c.orstatus FROM `{$prefix}order` as a left join `{$prefix}flow_check`as c on c.orderid=a.id WHERE (c.starttime>0 or c.endtime>0) and a.addtime>={$time['begin']} and a.addtime<={$time['end']} GROUP by a.id order by c.endtime asc,c.starttime asc";
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
    	print_arr($list);
    	foreach($list as $k=>$value){
    		if(($value['starttime']<$time['begin']&&$value['starttime']>0)||$value['endtime']>$time['end']){
    			continue;
    		}
    		$order_id[]=$value['id'];
    	}
    
    echo count($order_id);
    	$title=array('ordernum'=>$this->ordernum_explain,'unique_sn'=>$this->unique_name);
    	
    	$all=$all_id=array();
    	foreach($orders as $value){
    		$all[$value['id']]=$value;
    	}
    	$all_id=array_keys($all);
    	
    	$list=array();
    	foreach($order_id as $id){
    		if(!in_array($id,$all_id)){
    			$list[]=$id;
    		}
    	}
    	print_arr($list);
    }
    
    //获取所有的小工序
    public function getAllGx(){
    	$gx_list=@include APP_DATA.'gx_list.php';
    	//区分是否需报结束
    	$return=$start=$end=array();
    	foreach($gx_list as $value){
    		$id=$value['id'];
    		if($value['state']==1){
    			$start[$id]=$value;
    		}else {
    			$end[$id]=$value;
    		}
    		$return[$id]=$value;
    	}
    	return array("start"=>$start,'end'=>$end,'all'=>$return);
    }
    
    //测试生成中文文件
    public function chineseTest(){
    	$name="数据汇总";
    	$dir=UPLOAD_DIR."csv/";
    	$csvName =$dir.iconv('UTF-8', 'GB18030',$name).".csv";
		
		//$fp = fopen($csvName, 'w');
		
		echo file_exists($csvName);
		exit();
    }
}
