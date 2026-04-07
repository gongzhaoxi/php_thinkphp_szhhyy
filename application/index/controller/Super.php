<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Url;
use think\Db;
use think\facade\Request;

class Super extends Controller{
    
	//项目配置
	public $system;
	//项目菜单
	public $sysmenu;
	//二维码字段分隔符
	public $seperator;
	//系统配置使用新的工艺路线
	public $new_gy=true;
	
    function __construct(){
        header("Cache-control: private");  // history.back返回后输入框值丢失问题
        parent::__construct();
    }
 
    /* 初始化操作 */
    public function initialize(){
        require '../extend/src/Auth.php';
        $auth = new \Auth();
        $uid = session('gid');
        $yid = session('uid');
        $mster = session('master');
        $action = Request::action();
        $controller = Request::controller();
   
        //不需要登录的Controller
        $nologin_controller = array('Login','Wxapi');
        if (!in_array($controller,$nologin_controller) && !$uid){
            $this->redirect("Login/login");
            exit();
        }
        
        $name = $controller.'/'.$action;
        $this->assign("controller",$controller);
        $this->assign("action",$action);
        $this->assign("current_position",$name);
        //不需要验证
        $nocheck = array('Index/loginout');
        $noLimitController=array();
        if (in_array($name, $nocheck) || $mster==1||in_array($controller, $noLimitController)){
            $result = true;
        }else{
            $result = $auth->check($name, $yid);
        }
        if (!$result){
        	$url= session('default_url');
        	$url=$url?$url:'Login/login';
            $this->error('您没有权限',$url);
        }
        
        //项目配置
        $site_cache=@include APP_CACHE_DIR.'site_cache.php';
        $this->system=$site_cache[PRO_DOMAIN];
        //判断是否已经关站
        if($this->system['status']!=1){
        	exit("站点已关闭");
        }
        
        //二维码分割符
        if(isset($this->system['seperator'])&&trim($this->system['seperator'])!=''){
        	$this->seperator=trim($this->system['seperator']);
        }else{
        	$this->seperator="|";
        }
        
        if($this->system['newgx']>=3){
        	$this->new_gy=false;//不适用新工艺路线
        }
        
        
        $headMenu=array("index","orders","them","out","warm","setting");
        if(isset($this->system['menu'])&&is_array($this->system['menu'])){
        	$menu=$this->system['menu'];
        }else if(!isset($this->system['menu'])||$this->system['menu']==''){
        	$menu=$headMenu;
		}
        $this->sysmenu=$menu;
        $this->assign("sysmenu",$menu);
    }

    /**
     * 检测功能模块是否 在总后台开启
     * @param $module
     */
    public function check_status($module)
    {
        $flag = true;
        if(isset($this->system[$module]) && $this->system[$module] == 0){
            $flag = false;
        }

        return $flag;
    }

    public function check_auth($uid,$action_name,$controller){
        //用户的权限
        
    }
    
    //读取Excel导入数据
	//使用PHPEXCEL 读取文件插入数据库
	public function use_phpexcel($file,$isUnlink=true) {
		
		if (! file_exists ( $file )) { //如果没有上传文件或者文件错误
			return array ("error" => 1 );
		}
		//spl_autoload_register ( array ('Think', 'autoload' ) ); //必须的，不然ThinkPHP和PHPExcel会冲突
		$PHPExcel = new \PHPExcel ();
		$PHPReader = new \PHPExcel_Reader_Excel2007 (); //测试能不能读取2007excel文件
		if (! $PHPReader->canRead ( $file )) {
			$PHPReader = new \PHPExcel_Reader_Excel5 (); //测试能不能读取2003excel文件
			if (! $PHPReader->canRead ( $file )) {
				return array ("error" => 2 );
			}
		
		}
		$PHPReader->setReadDataOnly ( true );
		$PHPExcel = $PHPReader->load ( $file );
		$SheetCount = $PHPExcel->getSheetCount ();
		for($i = 0; $i < $SheetCount; $i ++) { // 可以导入一个excel文件的多个工作区
			$currentSheet = $PHPExcel->getSheet ( $i ); //当前工作表
			$allColumn = $this->ExcelChange ( $currentSheet->getHighestColumn () ); //当前工作表总共有多少列
			$allRow = $currentSheet->getHighestRow (); //当前工作表的行数
			$array [$i] ["Title"] = $currentSheet->getTitle ();
			$array [$i] ["Cols"] = $allColumn;
			$array [$i] ["Rows"] = $allRow;
			$arr = array ();
			for($currentRow = 1; $currentRow <= $allRow; $currentRow ++) {
				$row = array ();
				for($currentColumn = 0; $currentColumn < $allColumn; $currentColumn ++) {
					
					if ($currentSheet->getCellByColumnAndRow ( $currentColumn, $currentRow )->getValue () instanceof PHPExcel_RichText) {
						$row [$currentColumn] = $currentSheet->getCellByColumnAndRow ( $currentColumn, $currentRow )->getValue ()->getRichTextElements ();
					} else {
						$row [$currentColumn] = $currentSheet->getCellByColumnAndRow ( $currentColumn, $currentRow )->getValue ();
					}
				
				}
				$arr [$currentRow] = $row;
			}
			$array [$i] ["Content"] = $arr;
		}
		
		unset ( $currentSheet );
		unset ( $PHPReader );
		unset ( $PHPExcel );
		if($isUnlink==true){
			unlink ( $file );
		}
		return array ("error" => 0, "data" => $array ); //返回数据
	}
	//导入的辅助函数
	public function ExcelChange($str) { //配合Execl批量导入的函数
		$len = strlen ( $str ) - 1;
		$num = 0;
		for($i = $len; $i >= 0; $i --) {
			$num += (ord ( $str [$i] ) - 64) * pow ( 26, $len - $i );
		}
		return $num;
	}
	

	//单一表导出
	//$list 是已封装好的数据  $title 是 英文字段=>'中文标题' 表头
	//$creator 是excel文档创建者,$exceltitle是导出的excel文件名称
	public function one_export($title,$list,$creator,$exceltitle){
		 
		$export_array=$line_title_array=$field=array();
	
		//存储英文字段和中文字段 =表头和数据字段
		foreach ( $title as $key => $value ) {
			$field [] = $key;
			$line_title_array [] = $value;
		}
		 
		$doc = array ('creator' => $creator, 'title' => $exceltitle,
				'subject' => $exceltitle, 'description' => $exceltitle,
				'keywords' =>$exceltitle, 'category' => $exceltitle
		);
		export_excel($list,$field,$line_title_array,$doc);
	}
	
	//导出多个工作表
	//$titles 是多个工作表标题的集合,$lists是多个工作表的数据的集合，$titles和$lists的键都用工作表名称
	//$creator 是excel文档创建者,$exceltitle是导出的excel文件名称
	public function multi_export($titles,$lists,$creator,$exceltitle){
		 
		$line_title_arrays=$fields=array();
		 
		//存储英文字段和中文字段 =表头和数据字段
		foreach($titles as $tab=>$title){
			$line_title_array=$field=array();
			 
			foreach ( $title as $key => $value ) {
				$field [] = $key;
				$line_title_array [] = $value;
			}
			$line_title_arrays[$tab]=$line_title_array;
			$fields[$tab]=$field;
		}
		 
		$doc = array ('creator' => $creator, 'title' => $exceltitle,
				'subject' => $exceltitle, 'description' => $exceltitle,
				'keywords' =>$exceltitle, 'category' => $exceltitle
		);
		//存储各个工作表数据
		export_excel_multiple($lists,$fields,$line_title_arrays,$doc);
	
	}
	
	
	
	//导出多个工作表Excel测试
	/*     public function export_data(){
	 $title=['dname'=>'工序名称','worktime'=>'工作周期','addtime'=>'添加日期'];
	
	  
	 $export_arrays=$line_title_arrays=$fields=array();
	  
	 $tabs=array("工作表1",'工作表2','工作表3');
	  
	 //存储英文字段和中文字段 =表头和数据字段
	 foreach($tabs as $tab){
	 $line_title_array=$field=array();
	
	 foreach ( $title as $key => $value ) {
	 $field [] = $key;
	 $line_title_array [] = $value.$tab;
	 }
	 $line_title_arrays[$tab]=$line_title_array;
	 $fields[$tab]=$field;
	 }
	  
	 $list=Db::name("gx_list")->limit(10)->select();
	  
	 foreach($tabs as $tab){
	 $export_array=array();
	 foreach($list as $key=>$value){
	  
	 $value['addtime']=date('Y-m-d',$value['addtime']);
	 $export_array[]=$value;
	  
	 }
	 $export_arrays[$tab]=$export_array;
	 }
	  
	 $doc = array ('creator' => 'Weiyuan', 'title' => '微元科技', 'subject' => '数据导出', 'description' => '数据导出', 'keywords' => '数据导出', 'category' => '数据导出' );
	 export_excel_multiple($export_arrays,$fields,$line_title_arrays,$doc);
	 } */
	
	//新建一个工艺路线组合(即新建一个系列同时绑定一条新工序)
	//param 1. $name = series表的xname
	//param 2. line_id = 多个gx_line的id字符串或数组 (小程序选择了默认工艺+可选其他工艺)
	//param 3. uid 当前使用小程序的人的id
	public function makeNewSeries($name,$line_id,$set_day=0){
		 
		//系列名称
		$uid=session("uid");
		if(!is_array($line_id)){
			$line_id=explode(",", $line_id);
		}
		
		$now=time();
		 
		if(!is_array($line_id)||count($line_id)<=0){
			return array('code'=>1,'msg'=>"请输入工艺线");
		}
		 
		//标记是否新增了系列和doclass
		$addSe=$addDo=false;
		$needUpdate=false;
		$series_did=0;
		$series=Db::name('series')->where("isnew='1' and xname='$name'")->find();
		if($series===false||empty($series['id'])){
			//创建系列
			$new=array();
			$new['xname']=$name;
			$new['gid']='0';
			$new['pid']='0';
			$new['isnew']='1';
			$new['selfy']='1';
			$new['addtime']=$now;
		
			$series_id=Db::name('series')->insertGetId($new);
			$addSe=true;
			$needUpdate=true;
		}else{
			$series_id=$series['id'];
			$series_did=$series['gid'];
		}
		
		sort($line_id);
		 
		//查询是否有相同的doclass记录
		$line_str=implode(",", $line_id);
		$one=Db::name("doclass")->where("line_id='$line_str' and series_id='$series_id'")->find();
		if($one!==false&&$one['id']>0){
			return array('code'=>0,'msg'=>"有工艺路线可用",'series_id'=>$one['series_id'],'name'=>$one['title'],'did'=>$one['id']);
		}
		 
		$combine=combine_line_gx($line_id);
		$dname=implode(",",$combine);
		 
		$days=0;//总周期
		if($set_day>0){
			$days=$set_day;
		}
		else if(count($line_id)>0){
			//汇总所有工艺线的周期
			$days=Db::name("gx_line")->where("id in (".implode(",",$line_id).")")->sum("day");
		}
	
		$user=Db::name('login')->where("id='$uid'")->find();
		$gid=$user['uid'];//上级
		 
		//创建总工艺路线
		$doclass=array('uid'=>$uid,'gid'=>$gid,'title'=>$name,'dname'=>$dname,"day"=>$days,'addtime'=>$now,'isnew'=>1);
		$doclass['line_id']=implode(",",$line_id);
		$doclass['selfy']=1;
		$doclass['series_id']=$series_id;
		$did=Db::name("doclass")->insertGetId($doclass);
		$addDo=true;
		 
		if($did===false){
			return array('code'=>1,'msg'=>"创建物料新工艺路线失败");
		}
		
		//更新缓存
		gx_cache();
		 
		$code=0;
		$msg='创建物料成功';
		if($needUpdate||$series_did<=0){
			Db::name("series")->where("id='$series_id'")->update(array("gid"=>$did));
		}
		 
		return array('code'=>$code,'msg'=>$msg,'series_id'=>$series_id,'name'=>$name,'did'=>$did,'addSe'=>$addSe,'addDo'=>$addDo);
	}
	
	//获取某订单各个工序的报工记录
	//$gxs是所有工序的二维数组，$orderid是订单的id
	//$oag是 order_attach_gx的id,重新报工的表的id
	public function order_flow_check($gxs,$orderid,$oag=''){
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
		//是否指定副工艺线
		if(!empty($oag)&&$oag>0){
			$attach_sql=" and oag_id='$oag' ";
		}else if($oag=='-1'){
			$attach_sql=" and (oag_id='0' or oag_id>0)";
		}else{
			$attach_sql=" and oag_id='0' ";
		}
		//一次过查询该订单工序报工记录
		$flow_check=Db::name('flow_check')
					->where("orderid='$orderid' and orstatus in (".implode(",",$fids).") $attach_sql")
					->order("id asc")->select();
		$checks=array();
		if($flow_check!==false){
			foreach($flow_check as $fval){
				$fkey=$orderid."_".$fval['orstatus'];
				$checks[$fkey]=$fval;
			}
		}
		
		return $checks;
	}
	
	//根据系统设置的高级搜索字段查找订单数据
	//$table_prefix 是表的前缀
	public function senior_search($table_prefix=''){
		$where="";
		$input=input("");
		$fields=@include APP_DATA.'qrfield.php';
		$search_field=array();
		foreach($fields as $v){
			if($v['search']==1){
				$search_field[]=$v['fieldname'];
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
	
	//遍历多个排产主单，如果他下面没有schedule子项目就删除主单
	//$ids 是schedule_summary主表的id
	public function del_scheduals($ids){
		foreach($ids as $id){
			$count=M("schedule")->where("sid='$id'")->count();
			if($count<=0){
				M("schedule_summary")->where("id='$id'")->delete();
			}else{
				//更新原主单的订单数，扇数等
				$list=M("schedule")->where("sid='$id'")->select();
				$orderid=array();
				foreach($list as $value){
					$orderid[$value['order_id']]=$value['order_id'];
				}
	
				$square=0;
				$fans=0;
				if(count($orderid)>0){
					$sql=implode(",",$orderid);
					$os=Db::name("order")->field("id,ordernum,gid,is_schedule,gx_schedule,unique_sn,ng")->where("id in ($sql)")->select();
					if($os!==false&&count($os)>0){
						$os=order_attach($os);
						foreach($os as $value){
							$square+=floatval($value['area']);
							$fans+=floatval($value['snum']);
						}
					}
	
					$sum=array();
					$sum['ordernum']=count($orderid);
					$sum['square']=$square;
					$sum['fans']=$fans;
					$sumid=Db::name("schedule_summary")->where("id='$id'")->update($sum);//主表ID
				}
				 
				 
			}
		}//end of foreach
	}
	
	//对订单工序进行排序
	public function sortgx($gxlist){
		$list=array();
		foreach($gxlist[0] as $k=>$one){
			$orderby=$one['orderby'];
			if(isset($list[$orderby])){
				$list[]=$one;
			}else{
				$list[$orderby]=$one;
			}
		}
		ksort($list);
		return array($list);
	}
	
	//根据角色限制可搜索结果
	public function role_limit(){
		$limit="";
		$uid=session("uid");
		$me=M("login")->where("id='$uid'")->find();
		$user_role=$me['user_role'];
		if($user_role==2){//生产
	
			//通过班组绑定的gx_id,返回所有doclass表的id
			if(empty($me['tid'])){
				$limit="a.id='0'";//未绑定班组不能查看订单
				return $limit;
			}
	
			$team_gx=M("team_gx")->field("ngx_id")->where("tid='{$me['tid']}'")->find();
			if($team_gx===false||empty($team_gx['ngx_id'])){
				$limit="a.id='0'";//班组未绑定工序不能查看订单
				return $limit;
			}
	
			$ngx_id=$team_gx['ngx_id'];
			$ngx_id=unserialize($ngx_id);
	
			$gxid=array();
			foreach($ngx_id as $lid=>$gxid_arr){
				$gxid=array_merge($gxid,$gxid_arr);
			}
	
			//返回生成的可以查看的所有doclass
			$dids=getdid_from_gxid($gxid);
	
			if(count($dids)>0){
				$limit="a.gid in(".implode(",",$dids).")";
			}else{
				$limit="a.gid ='0'";//不显示订单
			}
	
	
		}else if($user_role==3||$user_role==5){//业务和跟单
	
			$custom=$me['custom'];
			if($custom==''){//跟单和业务一定要绑定客户，否则返回空
				$limit="a.id='0'";
			}else{
				$limit_client=array();
				if(strpos($custom, ",")!==false){
					$limit_client=explode(",", $custom);
				}else{
					$limit_client[]=$custom;
				}
	
				$limit="a.uname in (".simplode($limit_client).")";
			}
	
	
		}else if($user_role==4){//客户
			if($me['client_name']!=''){
				$limit="a.uname='{$me['client_name']}' ";
			}else{
				$limit="a.id='0'";//没设置客户名称不能显示订单
			}
	
	
		}
		 
		return $limit;
	}
	
	/*@2020/04/11 hj 新增订单报工必须按顺序报工-------------------------------------*/
	//$orderid 是订单id,$did是订单的gid字段(doclass表id),$gxid是要报工的工序的id
	//返回true 则通过，false为不通过
	public function check_gx_order($orderid,$did,$gxid){
		//项目不用检查报工顺序，则直接返回true
		if(!isset($this->system['reportorder'])||!$this->system['reportorder']){
			return true;
		}
		 
		//检测是否可报工
		$canReport=check_reported($orderid,$did,$gxid);
		return $canReport;
	}
	
	//导入或手动添加订单的时候，自动将第一个工序开始
	//$orderid 是订单id,$did是订单的gid字段(doclass表id)
	public function create_report($orderid,$did){
		//项目检查报工顺序才执行
		if(isset($this->system['reportorder'])&&$this->system['reportorder']==1){
			return auto_start($orderid,$did);
		}
		return false;
	}
	/*@2020/04/11 hj 新增订单报工必须按顺序报工-------------------------------------*/
	
	/**
	 * 获取角色对应的可用工序
	 * @hj 2020-05-14
	 */
	public function role_gx($user_role,$tid){
		if($user_role==2){//生产
			$team_gx=M("team_gx")->field("ngx_id")->where("tid='$tid'")->find();
			if($team_gx&&!empty($team_gx['ngx_id'])){
				$ngx_id=unserialize($team_gx['ngx_id']);
				$gxid=array();
				foreach($ngx_id as $lid=>$gxid_arr){
					$gxid=array_merge($gxid,$gxid_arr);
				}
				if(count($gxid)>0){
					$sl_gx = Db::name('gx_list')
					->where("id in (".implode(",", $gxid).")")
					->field("dname")
					->group("dname")
					->order("orderby asc,id asc")
					->select();
				}
			}
		
			if(!isset($sl_gx)||!$sl_gx){
				$sl_gx=array();
			}
		}else{//其他角色
			$sl_gx = Db::name('gx_list')
			->field("dname")
			->group("dname")
			->order("orderby asc,id asc")
			->select();
		}
		return $sl_gx;
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
        
     
	
}