<?php
namespace app\pad\controller;
use think\Controller;
use think\Session;
use think\Url;
use think\Db;
use think\facade\Request;

class Super extends Controller{
    
	//项目配置
	public $system;
	//搜索字段
	public $searchField;
	
	
    function __construct(){
        header("Cache-control: private");
        parent::__construct();
    }
 
    /* 初始化操作 */
    public function initialize(){
    	
    	$site_cache=@include APP_CACHE_DIR.'site_cache.php';
    	$tag=input("tag");
    	if(empty($tag)||$tag!=$site_cache[PRO_DOMAIN]['tag']){
    		exit("禁止访问");
    	}
    	
    	$this->system=$site_cache[PRO_DOMAIN];
    	
        $action = Request::action();
        $controller = Request::controller();
        $name = $controller.'/'.$action;
        $this->assign("controller",$controller);
        $this->assign("current_position",$name);
        $this->assign("tag",$tag);
        
        $fields=@include APP_DATA.'qrfield_type.php';
        $search_field=array();
        foreach($fields['qrcode'] as $value){
        	if($value['search']==1){
        		$search_field[$value['fieldname']]=$value['explains'];
        	}
        }
        
        $this->searchField=$search_field;
    }
    
    //根据系统设置的高级搜索字段查找订单数据
    //$table_prefix 是表的前缀
    public function senior_search($table_prefix=''){
    	$where="";
    	$input=input("");
    	$fields=@include APP_DATA.'qrfield_type.php';
    	$search_field=array();
    	if(isset($fields['qrcode'])){
    		foreach($fields['qrcode'] as $value){
    			$search_field[]=$value['fieldname'];
    		}
    	}
    
    	if(count($search_field)>0){
    		$condition=array();
    		foreach($search_field as $field){
    			if(isset($input[$field])&&!empty($input[$field])){
    				$condition[$field]=htmlspecialchars(trim($input[$field]));
    			}
    		}
    		//执行搜索订单附表
    		if(count($condition)>0){
    			//页面显示
    			$this->assign("searchpost",$condition);
    
    			$search=array(' ','，','|');
    			$replace=array(',',',',',');
    
    			$orderid=array();
    			$allid=array();
    			foreach($condition as $field=>$value){
    				if(
    						strpos($value, ",")!==false||strpos($value, " ")!==false
    						||strpos($value, "，")!==false||strpos($value, "|")!==false
    				){
    					$sql="`fieldname`='$field'";
    					$value=str_replace($search, $replace, $value);
    					$arr=explode(",", $value);
    					$tsql=array();
    					foreach($arr as $a){
    						$tsql[]=" `value` like '%$a%' ";
    					}
    					if(count($tsql)>0){
    						$sql.=" and (".implode(" or ",$tsql).")";
    					}
    
    				}else{
    					$sql="`fieldname`='$field' and `value` like '%$value%'";
    				}
    				$result=M("order_attach")->where($sql)->column("orderid");
    					
    				if($result!==false&&count($result)>0){
    					$orderid[$field]=$result;
    					foreach($result as $id){
    						$allid[$id]=$id;
    					}
    				}else{
    					//只要有一个不符合条件的就可以返回0
    					return $where.=$table_prefix."id='0' ";
    				}
    			}
    			if(count($orderid)>0){
    				/**
    				 * 数据结构:
    				 * 	$orderid['ordernum']=array(1,3,4);
    				 $orderid['produc_sn']=array(1,2,4);
    				 $orderid['uname']=array(2,4,5);
    				 */
    				//循环，查找有交集的订单id
    				//array_intersect()函数要写明参数
    				$final_id=array();//最终有交集的订单ID数组
    				foreach($orderid as $field=>$ids){
    					$temp=$orderid;//复制值
    					unset($temp[$field]);//不用遍历当前字段
    					foreach($ids as $id){
    						$isExist=true;
    						foreach($temp as $temp_ids){
    							if(!in_array($id, $temp_ids)){
    								$isExist=false;//只有有一个字段内没有这个订单ID就删除这个订单ID
    								break;
    							}
    						}
    						if(!$isExist){
    							if(isset($allid[$id])){
    								unset($allid[$id]);
    							}
    						}
    					}
    				}
    					
    				if(count($allid)>0){
    					$allid=array_unique($allid);
    					$where.=$table_prefix."id in (".implode(",",$allid).") ";
    				}else{
    					$where.=$table_prefix."id='0' ";//没有符合查询条件的结果
    				}
    					
    			}else{
    				$where.=$table_prefix."id='0' ";//没有符合查询条件的结果
    			}
    
    		}
    			
    	}
    
    	return $where;
    }
    
    //获取某订单各个工序的报工记录
    //$gxs是所有工序的二维数组，$orderid是订单的id
    public function order_flow_check($gxs,$orderid){
    	if(!$gxs||!is_array($gxs)){
    		return array();
    	}
    	//工序id
    	$fids=array();
    	foreach ($gxs as $kc=>$gx){
    		$fids[]=$gx['id'];
    	}
    	if(count($fids)<=0){
    		$fids=array("0");
    	}
    	//一次过查询该订单工序报工记录
    	$flow_check=Db::name('flow_check')
    	->where("orderid='$orderid' and orstatus in (".implode(",",$fids).")")
    	->select();
    	$checks=array();
    	if($flow_check!==false){
    		foreach($flow_check as $fval){
    			$fkey=$orderid."_".$fval['orstatus'];
    			$checks[$fkey]=$fval;
    		}
    	}
    
    	return $checks;
    }
 
}