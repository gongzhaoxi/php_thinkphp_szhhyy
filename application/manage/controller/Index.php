<?php
namespace app\manage\controller;
use think\Controller;
use think\Db;
use think\facade\Request;
class Index extends Super
{
	
	private $sites;
	//定义要复制到新项目的公用的缓存文件
	private $copy_cache_files=array(
			'ab_unit.php','gx_group.php'
	);
	
    public function initialize(){
        parent::initialize();
        
    	if (empty(session('xxencrypt'))){
           $this->redirect('Login/login');
        }
        
     	$site_cache=@include APP_CACHE_DIR.'site_cache.php';
     	$this->sites=($site_cache!==false&&is_array($site_cache))?$site_cache:array();
     	$this->assign("sites",$this->sites);
    }
    
    public function index(){
    	
    	$this->assign("list",$this->sites);
    	//站点列表
    	return $this->fetch();
    }
    
    //通过ID获取站点
    private function getSiteById($id){
    	foreach($this->sites as $value){
    		if($id==$value['id']){
    			return $value;
    		}
    	}
    	return false;
    }
    
    //生成随机编码
    private function randCode($len){
    	$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // characters to build the password from
    	$string='';
    	for(;$len>=1;$len--)
    	{
    		$position=rand()%strlen($chars);
    		$string.=substr($chars,$position,1);
    	}
    	return $string;
    }
    
    //找到可以用的最大一个唯一标识
    private function getTag(){
    	$tag=array();
    	foreach($this->sites as $value){
    		if(isset($value['tag'])&&!empty($value['tag'])){
    			$tag[]=$value['tag'];
    		}
    	}
    	
    	$find=false;
    	while(!$find){
    		$code=$this->randCode(4);
    		if(!in_array($code, $tag)){
    			$find=true;
    			return $code;
    		}
    		
    	}
    }
    
    //检测是否存在 ，可用则返回true，否则返回false
    private function checkTag($tag,$siteid){
    	foreach($this->sites as $value){
    		if(isset($value['tag'])&&$value['tag']==$tag&&$siteid!=$value['id']){
    			return false;//已经有其他站点占用了
    		}
    	}
    	 
    	return true;
    }
    
    //添加站点
    public function add_site(){

    	if(!empty(input("id"))){
    		$id=intval(input("id"));
    		$one=$this->getSiteById($id);
    		if($one['menu']==''){
    			$one['menu']=array();
    		}
    		$this->assign("one",$one);
    	}else{
    		$tag=$this->getTag();
    		$this->assign("tag",$tag);
    	}
    	
    	return $this->fetch();
    }
    
    //保存站点
    public function save_site(){
    	$data=input('post.');
    	$data['addtime']=date('Y-m-d H:i:s',time());
    	$data['domain']=str_replace("www.", "", $data['domain']);
    	$data['menu']=input('post.menu/a');
    	//$data['password']=authcode($data['password'],"ENCODE");//加密
    	if(!empty($data['id'])){	//编辑
    		
    		$this->sites[$data['domain']]=$data;
    	}else{						//新增
    		
    		if(count($this->sites)>0){
    			$max_id=count($this->sites);
    		}else{
    			$max_id=0;
    		}
    		
    		$add_id=$max_id+1;
    		$data['id']=$add_id;
    		$this->sites[$data['domain']]=$data;
    	}
    	
    	$can=$this->checkTag($data['tag'],$data['id']);
    	if(!$can){
    		return array('status'=>'2','msg'=>'项目标识已占用，请使用其他标识');
    	}
    	
    	
    	//新建项目的缓存目录
    	project_dir($data['domain']);
    	
    	//复制必须要的缓存文件
    	copy_cache($this->copy_cache_files,$data['domain']);
    	
    	//写入缓存
    	site_cache($this->sites);
    	return array('status'=>'1');	
    }
    
    //删除站点
    public function delete_site(){
    	if(!empty(input("id"))){
    		$id=intval(input("id"));
    		$site=$this->getSiteById($id);
    		$domain=str_replace(".", "", $site['domain']);
    		if(trim($domain)!=''){
    			$target=APP_CACHE_DIR.$domain;
    			if(file_exists($target)){
    				@del_dir($target);
    			}
    		}
    		$domain=$site['domain'];
    		unset($this->sites[$domain]);
    		//写入缓存
    		site_cache($this->sites);
    		return array('status'=>'1');
    	}else{
    		return array('status'=>'2','msg'=>'请提交要删除的站点ID');
    	}
    	
    }
    
    //显示SQL指令执行
    public function commend(){
    	return $this->fetch();
    }
    
    
    //执行sql
    public function execute_sql(){
    	error_reporting(0);
    	//$sql="ALTER TABLE `bg_qrcode_fields` ADD `scheduallist` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '显示在排查单列表' AFTER `onlist`;";
    	//$sql="ALTER TABLE `bg_qrcode_fields` ADD `scheduleorder` MEDIUMINT(5) UNSIGNED NULL DEFAULT '0' COMMENT '排产单排序' AFTER `scheduallist`;";
    	//循环所有的站点，读取数据库并执行sql
    	$site_cache=@include APP_CACHE_DIR.'site_cache.php';
    	
    	$sql=input("sql");
    	$sql_arr=explode(";",$sql);
    	
    	if(count($sql_arr)<=0){
    		return array('status'=>'2','msg'=>'请提交要执行的SQL语句');
    	}
    	
    	$sqlcount=$sites=$total=0;
    	foreach($sql_arr as $sql){
    		if($sql==''){
    			continue;
    		}
    		$sites=0;
    		foreach($site_cache as $value){
    			$config=array();
    			$config['type']='mysql';
    			$config['hostname']='localhost';
    			$config['database']=$value['dbname'];
    			$config['username']=$value['account'];
    			$config['password']=$value['password'];
    		
    			@db("order",$config)->query($sql);
    			$sites++;
    			$total++;
    		}
    		
    		$sqlcount++;
    	}
    	
    	
    	return array('status'=>'1','sql'=>$sqlcount,'site'=>$sites,"total"=>$total);
    	
    }
    
    //批量复制文件
    public function copyfile(){
    	return $this->fetch();
    }
    
    //执行将cache文件夹下面的指定文件复制文件到各个项目文件夹下
    public function docopy(){
    	$filestr=input("file");
    	$arr=explode("|",$filestr);
    	$files=array();
    	foreach($arr as $value){
    		if($value==''){
    			continue;
    		}
    		$files[]=trim($value);
    	}
    	
    	$num=0;
    	if(count($files)>0){
    		foreach($this->sites as $value){
    			copy_cache($files,$value['domain']);
    			$num++;
    		}
    	}
    	
    	return array('status'=>'1',"total"=>$num);
    }
}
