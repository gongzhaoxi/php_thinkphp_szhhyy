<?php
namespace app\api\controller;

use think\Db;
use think\Controller;
use app\index\service\IndexExport;
use think;
use app\service\MatchingGxline;
use app\service\PrintYi;

class Wxapi extends Controller{
	
	//整个站点的缓存
	private $site_cache;
	//项目配置
	private $system;
	//二维码字段分隔符
	private $seperator;
	//系统配置使用新的工艺路线
	private $new_gy=true;
	
	public function initialize(){
		$site_cache=@include APP_CACHE_DIR.'site_cache.php';
		$this->site_cache=$site_cache;
		$this->system=$site_cache[PRO_DOMAIN];//项目配置
		//二维码分割符
		if(isset($this->system['seperator'])&&trim($this->system['seperator'])!=''){
			$this->seperator=trim($this->system['seperator']);
		}else{
			$this->seperator="|";
		}
		
		if($this->system['newgx']>=3){
			$this->new_gy=false;
		}
	}

    /**
     * 自动更新订单出入库状态
     */
    public function autoUpdateStatus()
    {
        $produceNo = json_decode(input('produce_no'),true);
        $type = input('type',1);//类型:1未入库、2部分入库、3已完成
        $intime = json_decode(input('intime'),true);//全部入库时间

        if(!is_array($produceNo)){
            $this->_error('格式错误');
        }
        $produceStr = "";
        foreach ($produceNo as $k => $v) {
            $produceStr .= "'$v',";
        }
        $produceStr=rtrim($produceStr,',');;
        switch ($type){
            case 1:
                $update = ['status'=>0,'intime'=>0,'endstatus'=>1];
                $res = Db::name('order')->whereIn('unique_sn',$produceNo)->update($update);
                break;
            case 2:
                $update = ['status'=>1,'intime'=>0,'endstatus'=>1];
                $res = Db::name('order')->whereIn('unique_sn',$produceNo)->update($update);
                break;
            case 3:
                $update = ['status'=>1,'endstatus'=>2];
                $res = Db::name('order')->whereIn('unique_sn',$produceNo)->update($update);
                $sql = "update bg_order set intime=case ";
                foreach ($produceNo as $k => $v) {
                    $tempTime = isset($intime[$k])?strtotime($intime[$k]):time();
                    $sql .= "when unique_sn='$v' then $tempTime \n";
                }
                $sql .= "end where unique_sn in ($produceStr)";
                Db::execute($sql);
                break;
        }

        if($res !== false){
            $this->_success('成功');
        }
        $this->_error('失败');
    }


    /**
     * 自动创单接口
     * @param str $token
     * @param array $str 订单数据,json编码
     */
	public function autoInorder()
    {
        set_time_limit(0);
        $token = input('token');
        $orderData = json_decode(input('strdata'),true);//订单数据二维数组
//        dump($orderData);exit;
        $matchine = new MatchingGxline();

//        $tokenstr = encodeToken();
//        if($token != $tokenstr){
//            $this->_error('token不正确');
//        }
        if(!is_array($orderData)){
            $this->_error('参数错误');
        }

        $fields=Db::name('qrcode_fields')
            ->field('fieldname,explains,orderby,is_system')
            ->where("status='0'")->order("orderby asc,id asc")->select();

        $field_order = array_column($fields,'fieldname');

        $unique_field=PRO_UNIQUE_ORDERFIELD?PRO_UNIQUE_ORDERFIELD:'ordernum';//唯一字段，判断订单是否存在
        //判断唯一字段在二维码的位置
        $unique_pos=0;
        foreach($field_order as $key=>$fieldname){
            if($fieldname==$unique_field){
                $unique_pos=$key;
                break;
            }
        }

        $orignalQrcode = Db::name('qrcode_fields')->where("status='0'")->select();

        //转换字段为系统字段
        $data = [];
        $log = [];
        foreach ($orderData as $k => $v) {
            $converdata = $matchine->convertThirdField($v,$orignalQrcode);
            $data[] = $converdata;
            $log[$k]['unique_sn'] = $converdata[$unique_field];
            $log[$k]['content'] = json_encode($v);
        }

        //插入日志表
        if($log){
            Db::name('post_data')->insertAll($log);
        }
        $now=time();
        //转换日期格式，防止出现年-月-日
        $toreplace=array('年','月','日');
        $replace=array('-','-','');

        if (count($data) > 0){
            for ($i=0; $i<count($data); $i++){

                $data[$i]['ordertime']=str_replace($toreplace,$replace, $data[$i]['ordertime']);
                $data[$i]['endtime']=str_replace($toreplace,$replace, $data[$i]['endtime']);
                $endtime = strtotime(str_replace('T',' ',$data[$i]['endtime']));

                $order_num = $data[$i]['ordernum'];
                $order_time = $data[$i]['ordertime'];
                $series_id=intval($data[$i]['series_id']);//系列id
                $series=ctrim($data[$i]['series']);//系列名称
                if (empty($order_time)){
                    $push_time = time();
                }else {
                    $push_time = strtotime($order_time);
                }

                //分解二维码成数组
                $qrcode_str=implode($this->seperator, $data[$i]);
                $fieldval=$data[$i];
                $unique_sn=htmlentities($fieldval[$unique_field]);//找到对应唯一字段的值

                //订单是否存在
                if(isset($this->system['accessfield'])&&trim($this->system['accessfield'])!=''){
                    $exist=check_unique2($this->system['orderfield'], $this->system['accessfield'],$data[$i]);
                }else{
                    $exist = Db::name('order')->where("unique_sn='$unique_sn'")->find();
                }
                if (!$exist){
                    $gxlineId = $matchine->index($data[$i]);//根据字段内容，匹配工艺线

                    $indata = array('ordernum'=>$data[$i]['ordernum'],'uid'=>1,'ordertime'=>$push_time,'uname'=>$data[$i]['uname'],'gid'=>0,'day'=>0
                    ,'pname'=>$data[$i]['pname'],'color'=>$data[$i]['color'],'series_id'=>$series_id,'series'=>$series,'area'=>$data[$i]['area'],'snum'=>intval($data[$i]['snum']),'endtime'=>$endtime,'addtime'=>time()
                    ,'bhao'=>$data[$i]['bhao'],'address'=>$data[$i]['address'],'unique_sn'=>$unique_sn,'gxline_id'=>implode(',',$gxlineId)
                    );
                    //保存所有数据-日后可能调用
                    $indata['order_attach']=serialize($data[$i]);
                    $result = Db::name('order')->insertGetId($indata);

                    //分解整个二维码字符串存储进订单字段附表内
                    $attachData = [];
                    foreach($fieldval as $k=>$v){
                        //插入数据库
                        $t=array();
                        $t['orderid']=$result;
                        $t['fieldname']=$k;

                        //特别的两个时间-在common.php的order_attach函数也有使用
                        if($t['fieldname']=='ordertime'){
                            $v=date('Y-m-d',$push_time);
                        }else if($t['fieldname']=='endtime'){
                            $v=date('Y-m-d',$endtime);
                        }
                        $t['value']=$v;
                        $attachData[] = $t;

                    }
                    Db::name('order_attach')->insertAll($attachData);
                }

            }
            echo json_encode(array('code'=>0,'ordert'=>$order_time));
        }else{
            echo json_encode(array('code'=>1));
        }

    }


    public function index(){
        $id = intval(input('id'));
        $result = Db::name('login')->where("id=$id")->find();
        if ($result){
            if ($result['del']==0){
                return json_encode(array('code'=>0));
            }else {
                return json_encode(array('code'=>1));
            }
        }
    }
    
    /**
     * 检查项目标识是否合法
     */
    public function checktag(){
    	$tag = ctrim(input("param.tag"));
    	$domain='';
    	if(isset($tag)&&!empty($tag)){
    		$project=$this->project($tag);
    		if($project['code']<1){
    			$domain=$project['result']['domain'];
    			return json_encode(array('status'=>1,'domain'=>$domain,"msg"=>'项目标识正确'));
    		}else{
    			return json_encode(array('status'=>2,'msg'=>"项目标识不存在"));
    		}
    	}else{
    			return json_encode(array('status'=>2,'msg'=>"请提交项目标识"));
    	}    	
    }
    
    /**
    * 所需字段
    * param uname
    * param pwd
    */
    public function login(){
    	//$tag = ctrim(input("param.tag"));
        $account = ctrim(input("param.uname"));
        $pwd = ctrim(input("param.pwd"));
        
        /* $domain='';
        if(isset($tag)&&!empty($tag)){
        	$project=$this->project($tag);
        	if($project['code']<1){
        		$domain=$project['result']['domain'];
        	}else{
        		return json_encode(array('status'=>2,'msg'=>"项目标识不存在"));
        	}
        } */
        
        //判断传输值
        if(!empty($account) && !empty($pwd)){
            $mdpwd = md5($pwd);
        }else {
            return json_encode(array('status'=>2));
        }
        //检验
        $result = Db::name('login')->field("id,uid,uname,user_role,client_name,dimission")->where("uname='$account' and password='$mdpwd' and del=0")->find();
        if ($result!==false&&$result['id']>0){
        	 $result['domain']="";
        	 $status=1;
        	 $msg='';
        	 if($result['dimission']==1){
        	 	$status=2;
        	 	$msg='该账户已离职';
        	 }
            return json_encode(array('status'=>$status,'result'=>$result,'msg'=>$msg,'part_into'=>PART_INTO,'erp_url'=>ERP_URL));
        }else {
            return json_encode(array('status'=>2,'msg'=>'用户名或密码错误'));
        }
    }
    
    /**
     * 验证密码
     * param uname
     * param pwd
     */
    public function checkpass(){
    	$account = ctrim(input("param.uname"));
    	$pwd = ctrim(input("param.pwd"));
    
    	//判断传输值
    	if(!empty($account) && !empty($pwd)){
    		$mdpwd = md5($pwd);
    	}else {
    		return json_encode(array('status'=>2));
    	}
    	//检验
    	$result = Db::name('login')->field("id")->where("uname='$account' and password='$mdpwd' and dimission=0")->find();
    	if ($result!==false&&$result['id']>0){
    		return json_encode(array('status'=>1,'result'=>$result));
    	}else {
    		return json_encode(array('status'=>2));
    	}
    }
	
	/**
	 * * 检测密码强度* 
	 * @param string $pw 密码* 
	 * @return int
	 */
	function checkPwLevel($pw) {
		if (empty ( $pw )) {
			return 0;
		}
		$pattern ['weak'] = '/((^[0-9]{6,})|(^[a-z]{6,})|(^[A-Z]{6,}))$/';
		$pattern ['middle'] = '/((^[0-9,a-z]{6,})|(^[0-9,A-Z]{6,})|(^[a-z,A-Z]{6,}))$/';
		$pattern ['strong'] = '/^[\x21-\x7e,A-Za-z0-9]{6,}/';
		$key = '';
		foreach ( $pattern as $k => $v ) {
			$res = preg_match ( $v, $pw );
			if ($res) {
				$key = $k;
				break;
			}
		}
		switch ($key) {
			case 'weak' ://弱
				return 3;
			case 'middle' ://中
				return 2;
			case 'strong' ://强
				return 1;
			default :
				return 0;
		}
	}
    
    /**
     * 修改密码
     * param pwd 密码
     * param uname 用户名
     */
    public function changepass(){
    	$account = ctrim(input("param.uname"));
    	$pwd = ctrim(input("param.pwd"));
    
    	//判断传输值
    	if(empty($pwd)){
    		return json_encode(array('status'=>2,'msg'=>'请输入密码'));
    	}
    	
    	$pwdLevel=$this->checkPwLevel($pwd);
    	
    	$mdpwd = md5($pwd);
    	//检验
    	$result = Db::name('login')->where("uname='$account'")->update(array("password"=>$mdpwd));
    	if ($result!==false){
    		return json_encode(array('status'=>1,'pwdlevel'=>$pwdLevel));
    	}else {
    		return json_encode(array('status'=>2));
    	}
    }
    
    /**
     * 通过唯一标识获取项目的域名和其他信息
     */
    private function project($tag){
    	$tag = intval($tag);//数字标识
    	$return=array();
    	foreach($this->site_cache as $domain=>$config){
    		if(trim($tag)==trim($config['tag'])){
    			$return['domain']=$domain;
    		}
    	}
    	if(isset($return['domain'])&&!empty($return['domain'])){
    		$code=0;
    	}else{
    		$code=1;
    	}
    	return array('code'=>$code,'result'=>$return);
    }
    
    /**
    *  所属员工工序
    *  @param: (int)uid    
    */
    public function mangx(){
        $id = intval(input('uid'));
        $condition = Db::name('login')->where("id=$id")->find();
        $cond = $condition['tid'];
        $result = Db::name('team_gx')->where("tid=$cond")->find();
        if ($result){
            $str = $result['gx_id'];//旧版工艺路线
            $ngx_id= $result['ngx_id'];//新版工艺路线
            
            $arr=$arr1=array();
            if($ngx_id!=''){
            	$ngx_id=$ngx_id!=''?unserialize($ngx_id):array();
            	$ids=array();
            	foreach($ngx_id as $lid=>$gxid_arr){
            		$ids=array_merge($ids,$gxid_arr);
            	}
            	if(count($ids)>0){
            		$sql="id in (".implode(",",$ids).")";
            		$arr = Db::name('gx_list')->distinct(true)->field("dname")->where($sql)->order('orderby asc')->select();
            	}
            }
            
            if(trim($str)!=''){
            		$sql="id in ({$str})";
            		$arr1 = Db::name('gx_list')->distinct(true)->field("dname")->where($sql)->order('id asc')->select();
            }
           
            $obj=array_merge($arr,$arr1);
            
            if (count($obj)>0){
                return json_encode(array('code'=>0,'result'=>$obj));
            }else {
                return json_encode(array('code'=>1,'msg'=>'工序不存在'));
            }
          }else {
             return json_encode(array('code'=>0,'msg'=>'未绑定班组'));
          }
               
    }
    
    /**
     * Iphone二维码GBK乱码解码
     */
    public function iphone_xcode(){
    	$qrcode=trim(input("qrcode"));
    	
    	if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)<=0) {
    		$qrcode=iconv("UTF-8","ISO-8859-1",trim($qrcode));
    		$qrcode=mb_convert_encoding($qrcode, "UTF-8", "GBK");
    	}
    	echo json_encode(array('txt'=>$qrcode));
    	exit();
    }
    
    /**
    *   系列的获取
    */
    public function getseries(){
        $ser = array();
        $result = Db::name('series')->where('type=0')->order('id asc')->select();
        if ($result){
            array_push($ser, $result);
            $res = Db::name('series')->where('type<>0')->order('id asc')->select();
            array_push($ser, $res);
            echo json_encode(array('code'=>0,'result'=>$ser));
        }else{
            echo json_encode(array('code'=>1));
        }
            
    }
    /**
    * 二级系列获取
    * @param $pid 
    */
    public function series(){
        $id = intval(input('pid'));
        $kind = intval(input('kind'));
        $ser = array();
        if ($kind==0) {
            $result = Db::name('series')->where("type=0")->order('id asc')->select();
            if ($result) {
                array_push($ser,$result);
                
                array_push($ser,array());
                echo json_encode(array('code'=>0,'res'=>$ser));
            }
        }else{
            $result = Db::name('series')->where("pid=$id")->order('id asc')->select();
            echo json_encode(array('code'=>0,'res'=>$result));    
        }
        
        
    }

    /**
    *系列匹配
    *@param $name 
    */
    public function peiseries(){
        $name = input("param.name");
        $ser = array();
        $result = Db::name('series')->where("xname='$name' and type=0")->find();
        if ($result) {
            $id  = $result['id'];
            array_push($ser,array($result));
            $res = Db::name('series')->where("pid=$id")->order('id asc')->select();
            array_push($ser,$res);
            echo json_encode(array('code'=>0,'result'=>$ser,'type'=>0));
        }else{
            echo json_encode(array('code'=>1,'result'=>$name,'type'=>1));
        }
    }
    
    /**
    *   工序获取
    */
    public function getGx(){
    	
    	if($this->new_gy){//新工艺路线
    		$this->getGxNew();
    		exit();
    	}
    	
        $uid = intval(input('param.uid'));
        $result = Db::name('doclass')->where("uid=$uid")->select();
        if ($result){
            echo json_encode(array('code'=>0,'result'=>$result));
        }else {
            echo json_encode(array('code'=>1));
        }
    }
    /**
     *   新版工序获取
     */
    public function getGxNew(){
    	$gid = intval(input('param.gid'));//用户组别
    	$where=array();
    	$where['isnew']='1';
    	$where['isdel']='0';
    	if(!empty($gid)){
    		$where['gid']=$gid;
    	}
    	$result = Db::name('doclass')->where($where)->order("id desc")->select();
    	if ($result){
    		echo json_encode(array('code'=>0,'result'=>$result));
    	}else {
    		echo json_encode(array('code'=>1));
    	}
    }
 
    /**
     * 新版获取系列
     */
    public function getNewSeries(){
    	
    	$keyword=ctrim(input("keyword"));
    	
    	$wheresql="";
    	if(!empty($keyword)){
    		$wheresql="and xname='$keyword'";
    	}
    	
    	$total=Db::name('series')->where("isnew='1' $wheresql")->count();
    	
    	if($total<=0){
    		echo json_encode(array('code'=>1,'result'=>"没有匹配到系列"));
    		exit();
    	}
    	
    	$series = Db::name('series')->where("isnew='1' $wheresql")->order('id desc')->select();
    	
    	if(isset($this->system['noselfy'])&&$this->system['noselfy']==1){
    		//项目配置了不显示自定义路线
    		$doclass=Db::name('doclass')->where("isnew='1' and selfy='0'")->order('used_time desc,id asc')->select();
    	}else{
    		$doclass=Db::name('doclass')->where("isnew='1'")->order('used_time desc,id asc')->select();
    	}
    	
    	foreach($series as $key=>$value){
    		$did=$value['gid'];			//关联的doclass表的id字段-绑定以前的工艺线表的id
    		$lines=$this->getLines($did);//获取默认和可选工艺路线
    		$series[$key]['other']=$lines['other'];//同一系列可选那些路线
    		$series[$key]['default']=array();
    		$one=array();
    		$one['did']=$did;
    		$one['title']=$lines['title'];
    		$one['default']=$lines['default'];
    		$series[$key]['default'][]=$one;
    		
    		//其他默认工序
    		foreach($doclass as $val){
    			if($val['series_id']==$value['id']&&$val['id']!=$did){
    				$lines=$this->getLines($val['id'],$val);//获取默认和可选工艺路线
    				$one=array();
		    		$one['did']=$lines['did'];
		    		$one['title']=$lines['title'];
		    		$one['default']=$lines['default'];
		    		$series[$key]['default'][]=$one;
    			}
    		}
    	}
    	echo json_encode(array('code'=>0,'result'=>$series,'total'=>$total));
    }
    
    /**
     * 根据二维码内容，获取订单的工艺线
     * @param array $qrocde 二维码数组,结构如图所示['xname'=>'123'];
     */
    public function getOrderGxline()
    {
        $qrcode = json_decode(input('qrcode'),true);
        if(!is_array($qrcode)){
            $this->_error('参数错误');
        }
        $matching = new MatchingGxline();
        $gxline = $matching->index($qrcode);
        if(count($gxline) == 0){
            $this->_error('没有匹配到工艺线');
        }
//        $data = ['gxline'=>$gxline,'unique_sn'=>$qrcode['produce_no'],'pname'=>$qrcode['pname']];
        $data = implode(',',$gxline);
        $this->_success('',$data);
    }
    
    //根据物料id获取默认和可选工艺路线
    public function getSeriesLines()
    {
        $series_id = intval(input("id"));//物料系列的id
        $series = Db::name('series')->where("isnew='1' and id='$series_id'")->find();
        if ($series !== false && $series['gid'] > 0) {
            $did = $series['gid'];
        } else {
            $did = 0;//没绑定工艺路线doclass
        }
        $return = $this->getLines($did);
        echo json_encode(array('code' => 0, 'result' => $return));
    }
    
    //获取系统设置的工艺路线
    public function getLine(){
    	$lines=Db::name("gx_line")->order("sort asc")->select();
    	echo json_encode(array('code'=>0,'result'=>$lines));
    }
    
    //根据系列获取工艺路线-返回默认工艺路线和其他可选工艺路线
    //param @$did 是doclass表的id字段值
    //$doclass_data是可选的doclass的记录，减少查询
    private function getLines($did,$doclass_data=array()){
    	if(empty($did)||$did<=0){
    		return array(
    				'did'=>0,
    				'title'=>'',
    				'default'=>array(),
    				'other'=>array()
    		);
    	}
    	
    	if(isset($doclass_data)&&!empty($doclass_data['id'])){
    		$doclass=$doclass_data;
    	}else{
    		$doclass=Db::name('doclass')->where("id='$did'")->field("title,line_id,other_line")->find();
    	}
    	$did=$doclass['id'];
    	//总工艺线名称
    	$title=$doclass['title'];
    	//一次过查询所有工艺线
    	$gx_lines=@include APP_DATA.'lines.php';
    	if(!$gx_lines||count($gx_lines)<=0){
    		$gx_lines=Db::name("gx_line")->where("isdel='0'")->select();
    	}
    	
    	if($doclass['line_id']!=''){
    		$line_id=explode(",",$doclass['line_id']);
    		$default=array();
    		if(count($line_id)>0){
    			sort($line_id);
    			foreach ($line_id as $lid){
    				 foreach($gx_lines as $value){
    				 	if($lid==$value['id']){
    				 		$default[]=$value;
    				 		break;
    				 	}
    				 }
    			}
    		}
    	}else{
    		$default=array();
    	}
    	
    	if($doclass['other_line']!=''){
    		$line_id=explode(",",$doclass['other_line']);
    		$other=array();
    		if(count($line_id)>0){
    			sort($line_id);
    			foreach ($line_id as $lid){
    				foreach($gx_lines as $value){
    					if($lid==$value['id']){
    						$other[]=$value;
    						break;
    					}
    				}
    			}
    		}
    	}else{
    		$other=array();
    	}
    	
    	return array(
    			'did'=>$did,
    			'title'=>$title,
    			'default'=>$default,
    			'other'=>$other
    	);
    }
    
    //传入多个工艺线gx_line的id返回合并后的多个工序路线
    //参数可以是一个数组，也可以是用英文逗号隔开的值字符串
    public function combine_gx(){
    	$line_id=str_replace("，", ",", input("line_id"));
    	$line_id=explode(",", $line_id);
    	
    	if(!is_array($line_id)||count($line_id)<=0){
    		echo json_encode(array('code'=>1,'msg'=>"请输入工艺线"));
    		exit();
    	}
    	
    	$gxs=combine_gx_line($line_id);//返回工艺线
    	
    	echo json_encode(array('code'=>0,'list'=>$gxs));
    }
    
    //新建一个工艺路线组合(即新建一个系列同时绑定一条新工序)
    //param 1. name = series表的xname
    //param 2. line_id = 多个gx_line的id字符串或数组 (小程序选择了默认工艺+可选其他工艺)
    //param 3. uid 当前使用小程序的人的id
    public function makeNewSeries(){
    	
    	$name=input("name");//系列名称
    	$uid=input("uid",'0','intval');
    	$day=input("day",'0','intval');//自定义周期
    	$line_id=str_replace("，", ",", input("line_id"));
    	$line_id=explode(",", $line_id);
    	
    	$now=time();
    	
    	if(!is_array($line_id)||count($line_id)<=0){
    		echo json_encode(array('code'=>1,'msg'=>"请输入工艺线"));
    		exit();
    	}
    	
    	$needUpdate=false;
    	$series_did=0;
    	$series=Db::name('series')->where("isnew='1' and xname='$name'")->find();
    	if($series===false||empty($series['id'])){
    		//物料不存则创建物料
    		$new=array();
    		$new['xname']=$name;
    		$new['gid']='0';
    		$new['pid']='0';
    		$new['isnew']='1';
    		$new['selfy']='1';
    		$new['addtime']=$now;
    		$series_id=Db::name('series')->insertGetId($new);
    		$needUpdate=true;
    	}else{
    		//已有和订单物料名称相同的物料,并且包含的工艺路线不同，则创建一个独立的doclass
    		$series_id=$series['id'];
    		$series_did=$series['gid'];
    	}
    	
    	sort($line_id);
    	
    	//查询是否有相同的doclass记录
    	$line_str=implode(",", $line_id);
    	$one=Db::name("doclass")->where("line_id='$line_str' and series_id='$series_id'")->find();
    	if($one!==false&&$one['id']>0){
    		echo json_encode(array('code'=>0,'msg'=>"有工艺路线可用",'series_id'=>$one['series_id'],'name'=>$one['title'],'did'=>$one['id']));
    		exit();
    	}
    	
    	$combine=combine_line_gx($line_id);
    	$dname=implode(",",$combine);
    	 
    	$days=0;//总周期
    	if($day>0){
    		$days=$day;
    	}else if(count($line_id)>0){
    		//汇总所有工艺线的周期
    		$days=Db::name("gx_line")->where("id in (".implode(",",$line_id).")")->sum("day");
    	}
    	
    	$user=Db::name('login')->where("id='$uid'")->find();
    	$gid=$user['uid'];//上级
    	$tid=$user['tid'];//班组的id
    	 
    	//创建总工艺路线
    	$doclass=array('uid'=>$uid,'gid'=>$gid,'title'=>$name,'dname'=>$dname,"day"=>$days,'addtime'=>$now,'isnew'=>1);
    	$doclass['line_id']=implode(",",$line_id);
    	$doclass['selfy']=1;
    	$doclass['series_id']=$series_id;
    	$did=Db::name("doclass")->insertGetId($doclass);
    	
    	if($did===false){
    		echo json_encode(array('code'=>1,'msg'=>"创建物料新工艺路线失败"));
    		exit();
    	}
    	
    	//更新帮助绑定的工序
    	$gxlist=gxlist_from_did($did);
    	$gx_id=array();
    	foreach($gxlist as $val){
    		$gx_id[$val['lid']][]=$val['id'];
    	}
    
    	if($needUpdate||$series_did<=0){
    		//只有新建的物料系列才有一个doclass绑定，一个series可以有多个doclass记录对应
    		Db::name("series")->where("id='$series_id'")->update(array("gid"=>$did));
    	}
    	
    	//更新缓存
    	gx_cache();

    	$code=0;
    	$msg='创建物料成功';
    	
    	echo json_encode(array('code'=>$code,'msg'=>$msg,'series_id'=>$series_id,'name'=>$name,'did'=>$did));
    	exit();
    }
    
    /**
     *   员工接口
     */
    public function getStaff(){
//         $name = input('uname');
    	$result = Db::name('login')->where("user_role in (2,6)")->select();
    	if ($result){
    		echo json_encode(array('code'=>0,'result'=>$result));
    	}else {
    		echo json_encode(array('code'=>1));
    	}
    }
    public function getStaffs(){
        $name = input('uname');
        $result = Db::name('login')->where("uname like '%$name%' and user_role in (2,6)")->select();
        if ($result){
            echo json_encode(array('code'=>0,'result'=>$result));
        }else {
            echo json_encode(array('code'=>1));
        }
    }

    public function inorder(){
    	
    	$fields=Db::name('qrcode_fields')
    	->field('fieldname,explains,orderby,is_system')
    	->where("status='0' and isqrcode='1'")->order("orderby asc,id asc")->select();
    	
    	$field_order=array();//对后台字段排序
    	if($fields!==false&&count($fields)>0){
    		foreach($fields as $value){
    			$field_order[]=$value['fieldname'];
    		}
    	}
    	
    	$unique_field=PRO_UNIQUE_ORDERFIELD?PRO_UNIQUE_ORDERFIELD:'ordernum';//唯一字段，判断订单是否存在
    	//判断唯一字段在二维码的位置
    	$unique_pos=0;
    	foreach($field_order as $key=>$fieldname){
    		if($fieldname==$unique_field){
    			$unique_pos=$key;
    			break;
    		}
    	}
    	
    	$now=time();
    	
        //录入订单
        $uid = intval(input('uid',1));//当前登录用户的login表的id字段值
        $user=Db::name("login")->where("id='$uid'")->field("uid")->find();
        $ugid=$user['uid'];//用户上级id
        $str = input('strdata');
        $data = json_decode($str,true);


        //转换日期格式，防止出现年-月-日
        $toreplace=array('年','月','日');
        $replace=array('-','-','');
        
        if (!empty($uid) && !empty($str)){
        for ($i=0; $i<count($data); $i++){
        	
            $data[$i]['ordertime']=str_replace($toreplace,$replace, $data[$i]['ordertime']);
            $data[$i]['endtime']=str_replace($toreplace,$replace, $data[$i]['endtime']);
        	
            $order_num = $data[$i]['ordernum'];
            $order_time = $data[$i]['ordertime'];
            $series_id=intval($data[$i]['series_id']);//系列id
            $series=ctrim($data[$i]['series']);//系列名称
            if (empty($order_time)){
                $push_time = time();
            }else {
                $push_time = strtotime($order_time);
            }
            
            //分解二维码成数组
            $qrcode=$data[$i]['qrcode'];
            $fieldval=explode($this->seperator,$qrcode);
            $unique_sn=htmlentities($fieldval[$unique_pos]);//找到对应唯一字段的值
            
            //订单是否存在
            if(isset($this->system['accessfield'])&&trim($this->system['accessfield'])!=''){
            	$exist=check_unique($unique_sn, $qrcode,$this->system);
            }else{
            	$exist = Db::name('order')->where("unique_sn='$unique_sn'")->find();
            }
            if (!$exist){
            	//查询工序的总周期
//            	$gid=$data[$i]['gid'];
//            	$doclass=Db::name('doclass')->field("day,isnew,series_id")->where("id='$gid'")->find();
//            	$day='0';
//            	$isnew='1';
//            	if($doclass!==false&&$doclass['day']>0){
//            		$day=$doclass['day'];
//            		$isnew=$doclass['isnew'];
//            		Db::name('doclass')->where("id='$gid'")->update(array('used_time'=>array('exp','used_time+1')));
//            	}
            	
//            	if(!empty($data[$i]['endtime'])){
//            		$endtime = strtotime($data[$i]['endtime']);
//            	}else{
//            		$endtime =$now+$day*24*60*60;
//            	}
            	
            	//if($doclass&&empty($series_id)){
//            		$series_id=$doclass['series_id'];
            	//}
            	$gxline_type = isset($data[$i]['gxline_type'])&&$data[$i]['gxline_type']==1?$data[$i]['gxline_type']:0;
                $indata = array('ordernum'=>$data[$i]['ordernum'],'uid'=>$uid,'ugid'=>$ugid,'ordertime'=>$push_time,'uname'=>$data[$i]['uname'],'gid'=>0,'day'=>0
                    ,'pname'=>$data[$i]['pname'],'color'=>$data[$i]['color'],'series_id'=>$series_id,'series'=>$series,'area'=>$data[$i]['area'],'snum'=>intval($data[$i]['snum']),'endtime'=>$data[$i]['endtime'],'addtime'=>time()
                    ,'bhao'=>$data[$i]['bhao'],'address'=>$data[$i]['address'],'unique_sn'=>$unique_sn,'gxline_id'=>$data[$i]['gxline_id'],'gxline_type'=>$gxline_type
                );
                
                //开启了新工艺线
                if($isnew==1){
                	$indata['ng']='1';
                }
                
                //保存所有数据-日后可能调用
                $indata['order_attach']=serialize($data[$i]);
                
                $result = Db::name('order')->insertGetId($indata);
                
                //自动创建第一步开始
                //if(isset($this->system['reportorder'])&&$this->system['reportorder']==1){
                	//auto_start($result,$indata['gid'],$uid);
                //}
                $attachData = [];
                //分解整个二维码字符串存储进订单字段附表内
                foreach($fieldval as $k=>$v){
                	//插入数据库
                	$t=array();
                	$t['orderid']=$result;
                	$t['fieldname']=$field_order[$k];
                	
                	//特别的两个时间-在common.php的order_attach函数也有使用
                	if($t['fieldname']=='ordertime'){
                		$v=date('Y-m-d',$push_time);
                	}else if($t['fieldname']=='endtime'){
                		$v=date('Y-m-d',$endtime);
                	}
                	$t['value']=$v;
                	$attachData[] = $t;
                }
                Db::name('order_attach')->insertAll($attachData);
            }
            
        }
        echo json_encode(array('code'=>0,'ordert'=>$order_time));
        }

    }
    
    /**
     * 图片文件上传，返回路径
     */
    public function uploadimg(){
    	
    	include "../thinkphp/library/think/Image.php" ;
    
    	//新建目录和路径
    	$domain=str_replace(".", "", PRO_DOMAIN);
    	$dir=UPLOAD_DIR.$domain."/";
    	if(trim($dir)!=''&&!file_exists($dir)){
    		mk_dir($dir);
    	}
    	
    	$file=request()->file('image');//图片的名称
    	if(!$file){
    		echo json_encode(array('code'=>1,'error'=>"请上传图片"));
    		exit();
    	}
    	$info=$file->validate(['size'=>102400000,'ext'=>'jpg,png,gif,jpeg'])->move($dir);
    	$success=$id=0;
    	$imgs=array();
    	$error=array();
    	if($info){
    		$imgurl=$info->getSaveName();
    		$imgurl=$dir.$imgurl;
    		//压缩图片-------------------------------------
    		$image = think\Image::open($imgurl);
    		// 返回图片的宽度
    		$width = $image->width();
    		// 返回图片的高度
    		$height = $image->height();
    		if($width>1280||$height>1280){
    	
    			$image->thumb(1280, 1280)->save($imgurl);
    		}
    		//压缩图片-------------------------------------
    		 
    		$imgurl=substr($imgurl, 1);
    		//新建记录
    		$in=array();
    		$in['imgurl']=$imgurl;
    		$in['addtime']=time();
    		$id=M("gx_imgs")->insertGetId($in);
    		$success++;
    		//返回带域名的图片路径
    		$imgs[]="https://".PRO_DOMAIN.$imgurl;
    	}else{
    		$error[]=$file->getError();
    	}
    	
    	echo json_encode(array('code'=>0,'success'=>$success,'img'=>$imgs,'error'=>$error,'id'=>$id));
    	exit();
    }
    
    /**图片文件上传
     * 
     */
    public function saveimg(){
    	$orderid=intval(input("orderid"));//订单ID
    	$gxid=intval(input("gxid"));//工序id
    	$imgIds=input("id");//用逗号连接的id
    	
    	$success=0;
    	if(!empty($imgIds)){
    		$imgIds=explode(",", $imgIds);
    		foreach($imgIds as $value){
    			$id=intval($value);
    			M("gx_imgs")->where("id='$id'")->update(array('orderid'=>$orderid,'gxid'=>$gxid));
    			$success++;
    		}
    	}
    	
    	//删除昨天没有用的图片
    	$time=timezone_get(7);
    	$last_day_begin=$time['begin'];
    	$rush=M("gx_imgs")->where("addtime<$last_day_begin and (orderid='0' or gxid='0') ")->select();
    	foreach($rush as $value){
    		@unlink(".".$value['imgurl']);
    		M("gx_imgs")->where("id='{$value['id']}'")->delete();
    	}
    	
    	echo json_encode(array('code'=>0,'success'=>$success,'imgid'=>input("id"),'orderid'=>$orderid,'gxid'=>$gxid));
    	exit();
    
    }
 
    /**
    *   检查订单是否存在
    *   param: string ordername
    *   param: int uid
    */
    public function checkorder(){
        $ordersn = ctrim(input('ordersn'));
        $uid = intval(input('id'));
        $ordersn=htmlentities($ordersn);
        
        //项目有没其他唯一辅助字段
        if(isset($this->system['accessfield'])&&trim($this->system['accessfield'])!=''){
        	$result=check_unique($ordersn, input("qrcode"),$this->system);
        }else{
        	$result = Db::name('order')->where("unique_sn='$ordersn'")->find();
        }
        
        if ($result){ 
            return json_encode(array('code'=>0,'result'=>$result));
        }else {
            return json_encode(array('code'=>1,'result'=>$ordersn));
        }
    }
    
    /**
     *   模糊搜索
     *   param: string ordername
     */
    public function orderlike(){
        
        $starttime = input('starttime');
        $endtime = input('endtime');
        $keyword = ctrim(input('keyword'));
        $uid=intval(input('uid'));
        $wxfield=WX_SHOW_FIELD;
        $wxfield==''?$wxfield='produce_no':'';
        //高级搜索字段
        $fields=@include APP_DATA.'qrfield_type.php';
        $search_field=array();
        if(isset($fields['qrcode'])){
            foreach($fields['qrcode'] as $value){
                if($value['search']==1){
                    $search_field[$value['fieldname']]=input($value['fieldname']);
                }
            }
        }
        $where = '';
        foreach ($search_field as $key=>$val){
            if (!empty($val)){
                $where .= " a.fieldname='$key' and a.value like '%$val%' and ";
            }
        }
        if (!empty($starttime) && empty($endtime)){
            $starttime=strtotime($starttime);
            $where .= "b.ordertime>$starttime and "; 
        }
        if (empty($starttime) && !empty($endtime)){
            $endtime=strtotime($endtime.' 23:59:59');
            $where .= "b.ordertime<$endtime and ";
        }
        if (!empty($starttime) && !empty($endtime)){
            $starttime=strtotime($starttime);
            $endtime = strtotime($endtime.' 23:59:59');
            $where .= " b.ordertime between $starttime and $endtime and ";
        }
        
        //生成只能查看自己的订单@hj 2020/04/13生产
        if(!empty($uid)){
        	
        	$user=M("login")->where("id='$uid'")->field("id,user_role,tid")->find();
        	if($user&&$user['user_role']==2){//生产
        		$tid=$user['tid'];
        		$team_gx=M("team_gx")->field("ngx_id")->where("tid='$tid'")->find();
        		if($team_gx&&!empty($team_gx['ngx_id'])){
        			$ngx_id=unserialize($team_gx['ngx_id']);
        			$gxid=array();
        			foreach($ngx_id as $lid=>$gxid_arr){
        				$gxid=array_merge($gxid,$gxid_arr);
        			}
//        			$dids=array();
//        			if(count($gxid)>0){
//        				$dids=getdid_from_gxid($gxid);
//        			}
        			$gxlineId = Db::name('gx_list')->whereIn('id',$gxid)->column('lid');
        			if(count($gxlineId)<=0){
        				$key='a.orderid';
        				$where .= $key."='0'";//不显示订单
        			}else{
//        				$key='b.gid';
//        				$where .= $key." in (".implode(",",$dids).") and ";//不显示订单
                        $gxlineIdStr = implode('|',$gxlineId);
                        $where .= "CONCAT (',',gxline_id,',') REGEXP ',($gxlineIdStr),' and";//查询有包含某个工艺线
        			}
        		}
        	}
        	
        }
//         return json_encode($where);
//         exit();
        if (!empty($keyword)){
            $wheres = $where."a.value like '%$keyword%' and ";
            $result = Db::name('order_attach')->alias('a')->field('b.id,b.gxline_id,b.unique_sn,b.ordernum')
            			->join("order b","b.id=a.orderid","LEFT")
            			->where("b.pause=0 and $wheres b.repeal=0")
            			->order('b.id asc')
            			->select();
        }else {
            $result = Db::name('order_attach')->alias('a')->field('b.id,b.gxline_id,b.unique_sn,b.ordernum')
            			->join("order b","a.orderid=b.id","LEFT")
                        ->where("b.pause=0 and $where b.repeal=0")->order('b.id asc')->select();
        }
        
        if ($result){
            foreach ($result as $key=>$val){
                $id = $val['id'];
                $field_res = Db::name('order_attach')->where("fieldname='$wxfield' and orderid=$id")->find();
                $result[$key]['wxfield']=$field_res['value'];
            }
            return json_encode(array('code'=>0,'result'=>$result));
        }else {
            return json_encode(array('code'=>1,'result'=>$ordersn));
        }
    }
    
    /**
     *   异常报工
     *   param: string ordername
     *   param: int uid
     */
    public function abnormal(){
        $ordersn = ctrim(input('order'));
        $uid = intval(input('uid'));
        $orderid = intval(input('id'));
        $ordersn=htmlentities($ordersn);
        if (!empty($orderid)){
            $result = Db::name('order')->where("id=$orderid")->find();
        }
        if(isset($this->system['accessfield'])&&trim($this->system['accessfield'])!='' && empty($orderid)){
            $result=check_unique($ordersn, input("qrcode"),$this->system);
        }else if (!isset($this->system['accessfield']) || trim($this->system['accessfield'])=='' && empty($orderid)){
            $result = Db::name('order')->where("unique_sn='$ordersn'")->find();
        }
        if ($result){
            //已报工工序
            $id = $result['id'];
			$did=$result['gid'];
			$ng=$result['ng'];
			$gxlineId = $result['gxline_id'];

			if($ng==1){//新工艺路线
//				$list=gxlist_from_did($did);
				$list = combine_gx_line(explode(',',$gxlineId));
			}else{//旧工艺路线
				$list = Db::name('gx_list')->field('id,dname')
				->where("did='$did'")->order('id asc')->select();
			}
           	//其他通用
           	// $list = Db::name('flow_check')->alias('a')->field('a.*,b.dname')
            //       ->join('gx_list b','b.id=a.orstatus','LEFT')->where("a.orderid=$id and a.state<>1 and a.endtime <>0")->order('a.id asc')->select();
            if ($list){
                foreach ($list as $k=>$c){
                    $list[$k]['dname'] = htmlspecialchars_decode($c['dname']);
                }
                return json_encode(array('code'=>0,'result'=>$list,'orderid'=>$id));
            }else {
                return json_encode(array('code'=>1,'msg'=>'暂无工序'));
            }
            
        }else {
            return json_encode(array('code'=>1,'msg'=>'订单不存在'));
        }


        /*$ordersn = ctrim(input('order'));
        $uid = intval(input('uid'));
        
        $result = Db::name('order')->where("ordernum='$ordersn'")->find();
        if ($result){
            //已报工工序
            $id = $result['id'];
            $list = Db::name('flow_check')->alias('a')->field('a.*,b.dname')
                    ->join('gx_list b','b.id=a.orstatus','LEFT')->where("a.orderid=$id and a.state<>1 and a.endtime <>0")->order('a.id asc')->select();
            if ($list){
                foreach ($list as $k=>$c){
                    $list[$k]['dname'] = htmlspecialchars_decode($c['dname']);
                }
                return json_encode(array('code'=>0,'result'=>$list,'orderid'=>$id));
            }else {
                return json_encode(array('code'=>1,'msg'=>'暂无工序'));
            }
            
        }else {
            return json_encode(array('code'=>1,'msg'=>'订单不存在'));
        }*/
    }
    
    /**
    *  获取订单工序
    * @param: string id  
    * @param: array order
    * 
    */
    public function getOrdergx(){
        $uid = intval(input('id'));
        $list = input('list/a');//订单gxline_id
        $push_gx = array_unique($list);
        if (empty($uid) || empty($list)){
            echo json_encode();
            exit();
        }
        //查询用户属于哪个班组
        $login=Db::name("login")->field("user_role,tid")->where("id='$uid'")->find();
        if($login===false){
        	return json_encode(array('code'=>1,'msg'=>'用户不存在'));
        }else if($login['tid']<=0&&($login['user_role']==2||$login['user_role']==6)){
        	return json_encode(array('code'=>1,'msg'=>'员工未绑定班组'));
        }
        
        $arr_gx=array();
        //所属工序
        $onlygx = Db::name('team_gx')->field('gx_id,ngx_id')->where("tid='{$login['tid']}'")->find();
        if ($onlygx){
            $str_gx = $onlygx['gx_id'];
            if($str_gx!=''){
            	$arr_gx = explode(',', $str_gx);
            	for ($i=0; $i<count($arr_gx); $i++){
            		$arr_gx[$i] = intval($arr_gx[$i]);
            	}
            }
            
            //新版工序-独立字段
            if(trim($onlygx['ngx_id'])!=''){
            	$ngx_id=unserialize($onlygx['ngx_id']);
            	foreach ($ngx_id as $lid=>$arr){
            		foreach($arr as $val){
            			if($val>0){
            				$arr_gx[]=intval($val);
            			}
            		}
            	}
            }
            
        }else {
            return json_encode(array('code'=>1,'msg'=>'请设置班组审核工序'));
            exit();
        }

        $doclass=Db::name("doclass")->field("id,isnew")->where("id in (".implode(",",$push_gx).")")->select();
        $newgy_did=$oldgy_did=array();
        foreach($doclass as $value){
        	if($value['isnew']==1){
        		$newgy_did[]=$value['id'];
        	}else{
        		$oldgy_did[]=$value['id'];
        	}
        }


        $lines = implode(',',$list);
        $result = Db::name('gx_list')->distinct(true)->alias('a')->field('a.id,a.did,a.dname,a.need_num,b.inouts')
            ->join('gx_group b','a.gid=b.id','LEFT')
            ->where("a.lid in ($lines)")->order("a.orderby asc,a.id asc")->select();
        if ($result){
        	
        	$ingx=$outgx=array();
        	$return=array();
            foreach ($result as $k=>$c){
            	if(in_array($c['id'], $arr_gx)){
            		$return[$k]=$c;
            		$return[$k]['dname'] = htmlspecialchars_decode($c['dname']);
            	}
            	
            	if($c['inouts']==1){
            		$t=array();
            		$t['id']=$c['id'];
            		$t['dname']=$c['dname'];
            		$t['need_num']=$c['need_num'];
            		$ingx[]=$t;
            	}
            	if($c['inouts']==2){
            		$t=array();
            		$t['id']=$c['id'];
            		$t['dname']=$c['dname'];
            		$t['need_num']=$c['need_num'];
            		$outgx[]=$t;
            	}
            }

            return json_encode(array('code'=>0,'result'=>$return,'ingx'=>$ingx,'outgx'=>$ingx));
        }else {
            return json_encode(array('code'=>1,'msg'=>'请设置审核工序'));
        }
    }
    
    /**
     *  获取二维码字段设置
     * @param: string id
     * @param: array order
     *
     */
    public function qrfields(){
        //小程序显示单号
        $wxfield=WX_SHOW_FIELD;
        $wxfield==''?$wxfield='produce_no':'';
    	//高级搜索字段
    	$fields=@include APP_DATA.'qrfield.php';
    	$search_field=array();
    	
    	foreach($fields as $value){
    		if($value['search']==1){
    				$search_field[$value['fieldname']]=$value['explains'];
    		}
    	}
    	
    	
    	$fields=Db::name('qrcode_fields')
    	->field('fieldname,explains,orderby,is_system')
    	->where("status='0' and isqrcode='1'")->order("orderby asc,id asc")->select();
    	
    	return json_encode(
    				array('code'=>0,'result'=>$fields,'unique'=>PRO_UNIQUE_ORDERFIELD
    						,'seperator'=>$this->seperator,'secondfield'=>PRO_SECOND_ACCESSFIELD
    				    ,'search_field'=>$search_field,'wxfield'=>$wxfield
    					)
    			);
    }
    /**
     * 
    *   报工接口
    *   param: orderid
    *   param: olstatus
    *   param gid
    *   param: uid、pid
    *
    */
    //修改订单状态
    public function change_order(){
        $orderid = input("list");
        $list = json_decode($orderid,true);
        $gname = htmlspecialchars(input('name'));
        $man=htmlspecialchars(input('man'));//其他报工者
        $condition = intval(input("type")); 
        $uid = intval(input("cid"));
        $inouts=intval(input("inouts"));
        $time = time();
        $in_flow = null;
        $msg = '审核失败';
        //错误的信息
        $error=array();
        //返回订单编号
        $orders=array();
        
        //判断值是否存值
        if (empty($orderid)){
            echo json_encode(array('code'=>1,'msg'=>'缺少参数'));
            exit();
        }
        //查询该订单是否已暂停或失效
        
        //订单状态流-开始按钮
        if ($condition==0){
        	$oids=array();
        	for($i=0; $i<count($list); $i++){
        		$rid = $list[$i]['orderid']?$list[$i]['orderid']:$list[$i]['id'];
        		$oids[]=$rid;
        	}
        	
        	//一次过查询所有订单
        	$orderList=array();
        	if(count($oids)>0){
        		$oList=Db::name('order')->where("id in(".implode(",",$oids).") ")->select();
        		foreach($oList as $value){
        			$orderList[$value['id']]=$value;
        		}
        	}
        	
        	
            //修改订单状态
            $orderID = [];
            for($i=0; $i<count($list); $i++){
                //对应的工序id
                $rid = $list[$i]['orderid']?$list[$i]['orderid']:$list[$i]['id'];
                $orderID[] = $rid;
                $gid = $list[$i]['gid'];
                $in_num=$list[$i]['num']?$list[$i]['num']:'0';//入库数量
                
                $order=isset($orderList[$rid])?$orderList[$rid]:array();
                
//                if($order['ng']==1){
//                	//查询工艺gx_line
//                	$gx_line=getline_from_did($gid);
//                	if(count($gx_line)>0){
//                		//一般都是进入这里
//                		$sql="lid in (".implode(",",$gx_line).") and dname='$gname'";
//                	}else{
//                		$sql="lid >0 and dname='$gname'";
//                	}
//                	$gx = Db::name('gx_list')->where($sql)->find();
//                }else{
//                	$gx = Db::name('gx_list')->where("did='$gid' and dname='$gname'")->find();
//                }
                //工序升级，直接使用订单的工艺线
                $gxlineId = explode(',',$list[$i]['gxline_id']);
                $gx = Db::name('gx_list')->whereIn('lid',$gxlineId)->where('dname',$gname)->find();
                $gx_id = $gx['id'];
               
                $way=1;
                $oag_id=0;
                //开启副工艺线报工-判断是否设置副工艺并且返回oag_id
                /*if(isset($this->system['repeatorder'])&&$this->system['repeatorder']==1){
                	$which=which_report($rid,$gx_id);
                	$way=$which['way'];
                	$oag_id=$which['oag_id'];
                }*/
                
                if(isset($this->system['reportorder'])&&$this->system['reportorder']==1&&$way==1){
                	//检测是否可报工
                	$canReport=check_reported($rid,$list[$i]['gxline_id'],$gx_id);
                	if(!$canReport){
                		$err=array();
                		$err['orderid']=$rid;
                		$err['gx_id']=$gx_id;
                        $err['gx_name']=$gname;
                		$err['msg']="非顺序报工";
                		$error[$rid]=$err;
                		continue;
                	}
                }
                
                if($order['pause']==1||$order['repeal']==1){
                	$err=array();
                	$err['orderid']=$rid;
                	$err['gx_id']=$gx_id;
                	$err['msg']='订单已'.($order['pause']==1?'暂停':'作废');
                	$error[$rid]=$err;
                	continue;
                }
                
                $orders[]=$order['bhao'];
                
                if ($gx){
                	$gx_state=$gx['state'];
                	if($gx_state==2){//只报结束则跳过
                		$msg = '只报结束';
                		continue;
                	}
                    //判断是否已开始
                    $check = Db::name('flow_check')->where("orderid='$rid' and orstatus='$gx_id'")->find();
                    if ($check){
                        if ($check['starttime']>0) {
                            $msg = '工序已开始';
                            continue;
                        }else{
                        	$manstr=($check['man']!=''?($check['man'].",".$man):$man);
                        	$man=str_unique($manstr);
                            $in_data = array();
                            $in_data = array();
                            $inouts==1?$in_data=['uid'=>$uid,'starttime'=>$time,'in_num'=>$in_num,'media'=>'1','store_space'=>$list[$i]['cw'],'note'=>$list[$i]['note'],'man'=>$man,'oag_id'=>$oag_id]:
                            $in_data = ['uid'=>$uid,'starttime'=>$time,'in_num'=>$in_num,'media'=>'1','man'=>$man,'oag_id'=>$oag_id];
                            $checkid = $check['id'];
                            $in_flow = Db::name('flow_check')->where("id=$checkid")->update($in_data);    
                        }
                        
                    }else {
                        //$back_data = Db::name('order')->where("id='$rid'")
                         //            ->update(array('addtime'=>$time));
                        $in_data = array();
                        $inouts==1?$in_data = ['orderid'=>$rid,'uid'=>$uid,'orstatus'=>$gx_id,'starttime'=>$time,'in_num'=>$in_num,'media'=>'1','store_space'=>$list[$i]['cw'],'note'=>$list[$i]['note'],'man'=>$man,'oag_id'=>$oag_id]:
                        $in_data = ['orderid'=>$rid,'uid'=>$uid,'orstatus'=>$gx_id,'starttime'=>$time,'in_num'=>$in_num,'media'=>'1','man'=>$man,'oag_id'=>$oag_id];
                        $in_flow = Db::name('flow_check')->insert($in_data);
                	}
                
                }
            }
            //美加项目写入第三方数据库
            writeThirdDb($orderID);
            
        }
        
        //结束按钮
        if ($condition==1){
        	
        	$oids=array();
        	for($i=0; $i<count($list); $i++){
        		$rid = $list[$i]['orderid']?$list[$i]['orderid']:$list[$i]['id'];
        		$oids[]=$rid;
        	}
        	 
        	//一次过查询所有订单
        	$orderList=array();
        	if(count($oids)>0){
        		$oList=Db::name('order')->where("id in(".implode(",",$oids).") ")->select();
        		foreach($oList as $value){
        			$orderList[$value['id']]=$value;
        		}
        	}

        	$orderID = [];
            for ($i=0; $i<count($list); $i++){
                $orid = $list[$i]['orderid']?$list[$i]['orderid']:$list[$i]['id'];
                $orderID[] = $orid;
                $gid = $list[$i]['gid'];
                $in_num=$list[$i]['num']?$list[$i]['num']:'0';//入库数量
                
                $order=isset($orderList[$orid])?$orderList[$orid]:array();
                
//                if($order['ng']==1){
//                	//查询工艺gx_line
//                	$gx_line=getline_from_did($gid);
//                	if(count($gx_line)>0){
//                		//一般都是进入这里
//                		$sql="lid in (".implode(",",$gx_line).") and dname='$gname'";
//                	}else{
//                		$sql="lid >0 and dname='$gname'";
//                	}
//                	$gx = Db::name('gx_list')->where($sql)->find();
//                }else{
//                	$gx = Db::name('gx_list')->where("did='$gid' and dname='$gname'")->find();//查询该订单是否有该审核流程
//                }
                //工序升级，直接使用订单的工艺线
                $gxlineId = explode(',',$list[$i]['gxline_id']);
                $gx = Db::name('gx_list')->whereIn('lid',$gxlineId)->where('dname',$gname)->find();
                $gx_id = $gx['id'];
                
                $way=1;
                $oag_id=0;
                //开启副工艺线报工-判断是否设置副工艺并且返回oag_id
                /*if(isset($this->system['repeatorder'])&&$this->system['repeatorder']==1){
                	$which=which_report($orid,$gx_id);
                	$way=$which['way'];
                	$oag_id=$which['oag_id'];
                }*/
                
                if(isset($this->system['reportorder'])&&$this->system['reportorder']==1&&$way==1){
                	//检测是否可报工
                	$canReport=check_reported($orid,$list[$i]['gxline_id'],$gx_id);
                	if(!$canReport){
                		$err=array();
                		$err['orderid']=$orid;
                		$err['gx_id']=$gx_id;
                        $err['gx_name']=$gname;
                		$err['msg']="非顺序报工";
                		$error[$orid]=$err;
                		continue;
                	}
                }
                
                if($order['pause']==1||$order['repeal']==1){
                	$err=array();
                	$err['orderid']=$orid;
                	$err['gx_id']=$gx_id;
                	$err['msg']='订单'.($order['pause']==1?'暂停':'作废');
                	$error[$orid]=$err;
                	continue;
                }
                
                $orders[]=$order['unique_sn'];
                
                if($gx){
                	
                	//查询是该工序是否需要开始和结束时间，如果需要开始时间，但又没记录，则返回错误
                	$gx_state=$gx['state'];
                	$work_unit=$gx['work_unit'];//'日/次 需要判断是否超时
                	
                    $check = Db::name('flow_check')->where("orderid=$orid and orstatus=$gx_id ")->find();
                    if($gx_state==1&&$check!==false&&$check['starttime']<=0){
                    	$err=array();
                    	$err['orderid']=$orid;
                    	$err['gx_id']=$gx_id;
                    	$err['gx_name']=$gx['dname'];
                    	$err['msg']='未开始';
                    	$error[$orid]=$err;
                    	continue;
                    }
                    
                    //防止用户重新报工-@hj 2020-03-18 啊法提议不能更新完成时间
                    if($check!==false&&$check['endtime']>0){
                    	$err=array();
                    	$err['orderid']=$orid;
                    	$err['gx_id']=$gx_id;
                    	$err['gx_name']=$gx['dname'];
                    	$err['msg']='已报工';
                    	$error[$orid]=$err;
                    	continue;
                    }
                    
                    //计件所需变量
                    $salary = 0;
                    $nums = 0;
                    $fid = - 1;
                    if (PRO_SALARY == 1) {
                        /* 提成计算start */
                        $formula = Db::name('formula')->where("gxid=$gx_id and text !=''")
                        ->order('sort asc')
                        ->select();
                        $orderdetail = Db::name('order_attach')->where("orderid=$orid")->select();
                        
                        if ($formula) {
                            for ($s = 0; $s < count($formula); $s ++) {
                                $sid = $formula[$s]['sid'];
                                strchr($formula[$s]['text'], '|') ? $pd_content = explode( '|',$formula[$s]['text']) : $pd_content = array(
                                    $formula[$s]['text']
                                );
                                $pd = $formula[$s]['fieldname'];
                                // 订单详情
                                $compare = '';
                                $value = '';
                                foreach ($orderdetail as $dl) {
                                    if ($dl['fieldname'] == $pd) {
                                        $compare = $dl['value'];
                                    }
                                    $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                                }
                                $value .= 'in_num'.'='.$in_num;
                                parse_str($value);
                                foreach ($pd_content as $pc){
                                    if (strpos($compare,$pc)!==false){
                                        if (! empty($sid)) {
                                            $order_data = order_attach($orid);
                                            $second_res = Db::name('se_formula')->where("id=$sid")->find();
                                            $pd = $second_res['fields'];
                                            $se_text = $order_data[$pd];
                                            strchr($second_res['content'], '|') ? $pd_text = explode( '|',$second_res['content']) : $pd_text = array($second_res['content']);
                                            foreach ($pd_text as $pt){
                                                if (strpos($se_text,$pt)!==false) {
                                                    // 计件数量
                                                    $jnum = $formula[$s]['formula_text'];
                                                    $nums = eval("return $jnum;");
                                                    $salary = $nums * $formula[$s]['price'];
                                                    $fid = $formula[$s]['id'];
                                                    break;
                                                }
                                            }
                                            
                                        } else {
                                            // 计件数量
                                            $jnum = $formula[$s]['formula_text'];
                                            $nums = eval("return $jnum;");
                                            $salary = $nums * $formula[$s]['price'];
                                            $fid = $formula[$s]['id'];
                                            break;
                                        }
                                    }
                                }
                                if ($fid>-1){
                                    break;
                                }
                            }
                        }
                        if ($salary == 0) {
                            $formula_l = Db::name('formula')->where("gxid=$gx_id and text =''")->find();
                            if ($formula_l) {
                                $value = '';
                                foreach ($orderdetail as $dl) {
                                    $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                                }
                                $value .= 'in_num'.'='.$in_num;
                                parse_str($value);
                                
                                // 计件数量
                                $jnum = $formula_l['formula_text'];
                                $nums = eval("return $jnum;");
                                $salary = $nums * $formula_l['price'];
                                $fid = $formula_l['id'];
                            }
                            // echo json_encode($formula_l);
                            // exit();
                        }
                        /* 提成计算end */
                    }
                    
                    
                    //只需要报结束时间,新增完成时间记录
                    if ($gx_state==2&&($check===false||empty($check['id']))){
                        
                        $in_data = array();
                        $inouts==1?$in_data = ['orderid'=>$orid,'uid'=>$uid,'orstatus'=>$gx_id,'endtime'=>$time,'in_num'=>$in_num,'media'=>'1','man'=>$man,'store_space'=>$list[$i]['cw'],'note'=>$list[$i]['note'],'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'oag_id'=>$oag_id]:
                        $in_data = ['orderid'=>$orid,'uid'=>$uid,'orstatus'=>$gx_id,'endtime'=>$time,'in_num'=>$in_num,'media'=>'1','man'=>$man,'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'oag_id'=>$oag_id];
                    	$in_flow = Db::name('flow_check')->insert($in_data);
                    	$check=Db::name('flow_check')->where("orderid=$orid and orstatus=$gx_id ")->find();
                    }
                    
                    if ($check){
                    	
                    	//工序组ID
                    	$groupid=$gx['gid'];
                    	$group=Db::name('gx_group')->field('id,lid,inouts,cache_id')->where("id='$groupid'")->find();
                    	//判断该组是否出入库，如果是的话就查询其他流程查询同组其他流程，1是入库，2是出库
                    	$inouts=$group['inouts'];
                    	
    					//判断出入库
    					if($inouts==1||$inouts==2){
    						
    						//判断是否同组工序已出库或者入库--start
    						//查询同组其他工序
    						$isEnd=false;//标记是否全部工序已完成
    						if($order['ng']==1){
    							$line_id=count($gxlineId)>0?$gxlineId:array($group['lid']);
    							$cache_id=$group['cache_id'];//一条doclass由多个gx_line组成，每个gx_line有可能有多个入库组的工序，cache_id是入库组的id标记
    							$samename_group=Db::name('gx_group')
    									->where("lid in (".implode(",",$line_id).") and cache_id='$cache_id'")
    									->column("id");
    							if($samename_group===false&&count($samename_group)<=0){
    								$samename_group=array($groupid);
    							}
    							$brother_gx=Db::name("gx_list")->field("id")->where("gid in (".implode(",",$samename_group).") and id!='$gx_id'")->select();
    						}else{
    							$brother_gx=Db::name("gx_list")->field("id")->where("gid='$groupid' and id!='$gx_id'")->select();
    						}
    						
    						if($brother_gx===false||count($brother_gx)<=0){//没其他工序
    							$isEnd=true;
    						}else{
    							$brother_gx_ids=array();
    							foreach($brother_gx  as $val){
    								$brother_gx_ids[]=$val['id'];
    							}
    							$bsql=implode(",", $brother_gx_ids);
    							//查询订单的其他审核工序是否已完工
    							$brother_flow=Db::name('flow_check')->field("endtime")->where("orderid=$orid and orstatus in ($bsql) and endtime>0")->select();
    							if($brother_flow!==false&&count($brother_flow)==count($brother_gx_ids)){
    								$isEnd=true;
    							}
    						}
    							
    						//查询flow_check同组工序是否已经完成
    							if($inouts==1){//入库
    								$up=array();
    								$up['status']='1';//有一个工序是完成入库就标记订单为已入库
    								if($isEnd){//全部入库工序完成就标记为完成（不预警）
    									$up['endstatus']='2';
    									$up['intime']=$time;
    								}
    								$back_data = Db::name('order')->where("id='$orid'")
    								->update($up);
    							}else if($inouts==2){//出库
    								//$back_data = Db::name('order')->where("id='$orderid'")
    								//->update(array('outstatus'=>1));
    							}
    						
    					}//结束判断出入库是否已完成
                    	
                    	//更新工序完成时间组员数量等
    					$manstr=($check['man']!=''?($check['man'].",".$man):$man);
    					$man=str_unique($manstr);
    					
                    	$in_data = array();
                    	$inouts==1?$in_data = ['uid'=>$uid,'endtime'=>$time,'man'=>$man,'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'oag_id'=>$oag_id,'store_space'=>$list[$i]['cw'],'note'=>$list[$i]['note']]:
                    	$in_data = ['uid'=>$uid,'endtime'=>$time,'man'=>$man,'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'oag_id'=>$oag_id];
                    	if($inouts==1){
                    		$in_data['in_num']=$in_num;
                    	}
                    	$in_flow = Db::name('flow_check')->where("orderid=$orid and orstatus=$gx_id")->update($in_data);
                    	//是否超时
                        $start = $check['starttime'];
                        $num = $gx['work_value'];
                        $over_time = $start+($num*60*60*24);
                        if ($time>$over_time&&$gx_state==1&&$work_unit==7){//需要报开始和结束时间的工序，则提醒超时
                            $in_flow = Db::name('flow_check')->where("orderid=$orid and orstatus=$gx_id")->update(array('status'=>1));
                        }
                        
                        //如果有排产的话，就更新对应订单的排产完成状态
                        $schedule=Db::name('schedule')->field("id,sid")->where("order_id='$orid' and gx_id='$gx_id'")->find();
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
                        }
                    }
                    //美加项目写入第三方数据库
                    writeThirdDb($orderID);
                }//end of  if($gx)
             }//end of for
        }
        
        if(count($error)>0){
        	$code=1;
        }else{
        	$code=0;
        }
        echo json_encode(array('code'=>$code,'msg'=>$msg,'error'=>$error,'orders'=>$orders));
    }
   
    
    /**
    * 异常工序
    * param: id
    *
    */
    
    public function diff(){
        $id = intval(input("param.id"));
        $content = input("param.cont");
        $orderid = intval(input("param.orderid"));
        $uid = intval(input('uid'));
        $now=time();
        if(empty($id)){
        	return json_encode(array('code'=>1,'msg'=>'请选择要报异常的工序'));
        }
        //查询审核工序是否存在
        $exist = Db::name('flow_check')->where("orstatus='$id' and orderid='$orderid'")->find();
    	if ($exist){
            $result = Db::name('flow_check')->where("orstatus='$id' and orderid='$orderid'")->update(array('cid'=>$uid,'state'=>1,'error_time'=>$now,'stext'=>$content));
        }else {
        	$arr = array('cid'=>$uid,'orderid'=>$orderid,'orstatus'=>$id,'stext'=>$content,'state'=>1,'error_time'=>$now);
            $result = Db::name('flow_check')->insert($arr);
        }
        if ($result){
            return json_encode(array('code'=>0,'msg'=>'成功'));
        }else {
            return json_encode(array('code'=>1,'msg'=>'反馈失败'));
        }
        
    }
    
   
    /**
     * 批量出库接口
     */
    public function batch_send(){
    	
    	//订单ID的字符串
    	$orderid = input("param.orderid");
    	//工序名称字符串(数组)
    	$gx_name=input("param.gx_name");
    	//报工者UID
    	$uid=input("param.uid","0","intval");

    	//判断值是否存值
    	if (empty($orderid)||empty($gx_name)){
    		echo json_encode(array('code'=>1,'msg'=>'缺少订单ID参数或工序名称'));
    		exit();
    	}
    	
    	//过滤订单ID
    	$orderid=explode(",",$orderid);
    	$gx_name=explode(",",$gx_name);
    	foreach($gx_name as $key=>$value){
    		$gx_name[$key]=trim($value);
    	}
    	
    	$gx_name=simplode($gx_name);
    	//通过工序名称查询工序的ID
    	/* if($this->new_gy){
    		$sql="id in (".implode(",",$orderid).")";
    		$gids=Db::name("order")->where($sql)->column("gid");
    		$line_sql='';
    		if($gids!==false&&count($gids)>0){
    			$lines=getline_from_did($gids);
    			$line_sql=" and lid in (".implode(",",$lines).")";
    		}
    		//根据工艺路线限制查询工序
    		$gx_list=Db::name('gx_list')->field("id,dname")
    		->where("dname in ($gx_name) and isdel!='1' $line_sql")
    		->select();
    	}else{ */
    		$gx_list=Db::name('gx_list')->field("id,dname")
    		->where("dname in ($gx_name) and isdel!='1'")
    		->select();
    	//}

    	$now=time();
    	
    	if($gx_list===false||count($gx_list)<=0){
    		echo json_encode(array('code'=>1,'msg'=>'没有查询到工序'));
    		exit();
    	}
    
    	$gxids=array();
    	foreach($gx_list as $value){
    		$gxids[]=$value['id'];
    	}

    	$sql="1";
    	if(count($orderid)>0){
    		$sql.=" and orderid in (".implode(",",$orderid).") ";
    	}
    	
    	if(count($gxids)>0){
    		$sql.=" and orstatus in (".implode(",",$gxids).") ";
    	}
    	Db::name('flow_check')
    	->where($sql)
    	->update([
    			'suid'=>$uid,
    			'issend'  => 1,
    			'sendtime' =>$now
    	]);
    	
    	//判读订单的所有入库工序都已经出库，则该订单出库
    	foreach($orderid as $id){
    		
    		$order=Db::name('order')->where("id='$id'")->find();
    		$gid=$order['gid'];//工艺线doclass的id
    		
    		$lines=array();
    		//新版获取多个订单的所有小工序数组（did各不相同）
//    		if($order['ng']==1){
//    			$lines=getline_from_did($gid);
//    			if(count($lines)>0){
//    				//查询所有的入库工序
//    				$sql="a.lid in (".implode(",",$lines).")";
//    				$inlist=Db::name('gx_list')->alias('a')->field('a.id')
//    				->join('gx_group b','b.id=a.gid','LEFT')
//    				->where("$sql and b.inouts='1'")
//    				->select();
//
//    			}else{
//    				$inlist=false;
//    			}
//    		}else{
//	    			//查询所有的入库工序
//	    			$inlist=Db::name('gx_list')->alias('a')->field('a.id')
//	    			->join('gx_group b','b.id=a.gid','LEFT')
//	    			->where("a.did='$gid' and b.inouts='1'")
//	    			->select();
//    		}
            //工序升级，直接使用订单的工艺线
            $lines = explode(',',$order['gxline_id']);
            $inlist=Db::name('gx_list')->alias('a')->field('a.id')
                ->join('gx_group b','b.id=a.gid','LEFT')
                ->whereIn('a.lid',$lines)
                ->where("b.inouts='1'")
                ->select();
    		if($inlist!==false&&count($inlist)>0){
    			
    				//判断要有入库工序报工才可以出库
    				if($order['status']!=1){
    					continue;
    				}
    				//更新订单的状态为已出库
    				Db::name('order')->where("id='$id'")->update(array('outstatus'=>2));
    				
	    			//查询是否都已经报工，并且已经出库
	    			$ids=array();
	    			foreach($inlist as $val){
	    				$ids[]=$val['id'];
	    			}
	    			$ids_sql=implode(",",$ids);
	    			$count=Db::name('flow_check')->where("orstatus in ($ids_sql) and orderid='$id' and issend='1'")->count();
	    			
	    			if($count==count($ids)){
	    				
	    				//更新订单的状态为已出库
	    				Db::name('order')->where("id='$id'")->update(array('outstatus'=>1,'outtime'=>$now));
	    				
	    				//所有出库工序做完，就给出库的工序做一条记录
	    				if($order['ng']==1){
	    					if(count($lines)>0){
	    						//查询所有的入库工序
	    						$sql="a.lid in (".implode(",",$lines).")";
	    						$outlist=Db::name('gx_list')->alias('a')->field('a.id')
		    					->join('gx_group b','b.id=a.gid','LEFT')
		    					->where("$sql and b.inouts='2'")
		    					->select();
	    					}else{
	    						$outlist=array();
	    					}
	    				}else{
		    					$outlist=Db::name('gx_list')->alias('a')->field('a.id')
		    					->join('gx_group b','b.id=a.gid','LEFT')
		    					->where("a.did='$gid' and b.inouts='2'")
		    					->select();
	    				}
	    				
	    				foreach($outlist as $oval){
	    					$in_data = array();
	    					$in_data = ['orderid'=>$id,'uid'=>$uid,'orstatus'=>$oval['id'],'endtime'=>$now];
	    					$in_flow = Db::name('flow_check')->insert($in_data);
	    				}
	    			}
    		}
    		
    	}
    	
    	return json_encode(array('code'=>0,'msg'=>'成功'));
    }
    
    //2019-12-01 新增小程序接口
    //返回用户角色
    public function roles(){
    	$roles=@include_once APP_CACHE_DIR.'roles.php';
    	return json_encode(array('data'=>$roles));
    }
    
    //查询用户信息
    public function userinfo(){
    	//用户的id
    	$id=input("id","0","intval");
    	$user=Db::name("login")->where("id='$id'")->find();
    	$code='1';
    	if($user===false||empty($user['id'])){
    		$code='0';
    	}
    	return json_encode(array('code'=>$code,'userinfo'=>$user));
    }
    
    //从order_attach获取订单数据
    private function order_attach($id){
    	$list=Db::name("order_attach")->field("fieldname,value")->where("orderid='$id'")->select();
    	$return=array();
    	if($list!==false&&count($list)>0){
    		foreach($list as $value){
    			$return[$value['fieldname']]=$value['value'];
    		}
    	}
    
    	return $return;
    }
    
    //订单详细
    public function order_detail(){
    	//订单id
    	$id=input("id","0","intval");
    	//查找订单详细
    	$order=Db::name("order")->where("id='$id'")->find();

    	$uid=input("uid","0","intval");
    	$user=Db::name("login")->field("id,user_role")->where("id='$uid'")->find();
    	
    	if($user===false||empty($user['id'])){
    		return json_encode(array('code'=>'2','msg'=>'当前用户不存在'));
    	}
    	
    	//获取order_attach字段
    	$order_attach=order_attach($id);
    	$order=array_merge($order,$order_attach);
    	
    	if($order===false){
    		return json_encode(array('code'=>'2','msg'=>"订单不存在"));
    	}
    	
    	//查询生产进度-报工进度
    	$gxlist=array();//最终返回的工序或工序组数组
    	/**
    	 * 返回数组形式  $gxlist=> array(
    	 * 							array('name'=>'排产','status'=>'进行中','time'=>'2019-10-16'),
    	 * 							.....
    	 * 						)
    	 * 
    	 */
    	//新版工艺线
//    	$gx=gxlist_from_did($order['gid']);//查询该订单所有工序

        $gx = combine_gx_line(explode(',',$order['gxline_id']));//订单的所有工序
    	if($gx!==false&&count($gx)>0){
    		$gx_id=array();
    		foreach($gx as $key=>$value){
    			$gx_id[]=$value['id'];
    		}
    		$flow_list=array();
    		//全部查询报工记录
    		if(count($gx_id)>0){
    			$list=Db::name("flow_check")->alias('a')->field("a.*,b.uname,c.text,c.handle_time")
    			      ->join("login b", "b.id=a.uid","LEFT")
                      ->join("check_back c","c.fid=a.id","LEFT")
    			      ->where("a.orstatus in(".implode(",",$gx_id).") and a.orderid='$id'")->select();
    			foreach($list as $value){
    				$flow_list[$value['orstatus']]=$value;
    			}
    		}
    		//查询每个工序完成情况
    		foreach($gx as $key=>$value){
    			if(isset($flow_list[$value['id']])){
    				$gx[$key]['flow_check']=$flow_list[$value['id']];//添加报工记录
    			}else{
    				$gx[$key]['flow_check']=array();
    			}
    		}//end of foreach
    		
    		//转换数组成为返回的形式
    		foreach($gx as $key=>$value){
    			
    			$t=array();
    			$t['name']=$value['dname'];
    			$t['uname']=$value['flow_check']['uname'].$value['flow_check']['man'];
    			$t['status']='未开始';
                $t['is_back']=$value['flow_check']['isback'];
                $t['state']=$value['flow_check']['state'];
                $t['cause_back']=$value['flow_check']['text'];
                !empty($value['flow_check']['handle_time'])?$t['handle_time']=date("Y-m-d",$value['flow_check']['handle_time']):$t['handle_time']='';
    			$t['time']='';
    			if(isset($value['flow_check']['id'])&&$value['flow_check']['id']>0){//有报工记录
    				if($value['flow_check']['endtime']>0){
    					$t['status']='已完成';
    					$t['time']=date('Y-m-d',$value['flow_check']['endtime']);
    				}else if($value['flow_check']['starttime']>0){
    					$t['status']='进行中';
    					$t['time']=date('Y-m-d',$value['flow_check']['starttime']);
    				}
    			}
    			//@hj 2020/03/06添加异常显示
    			if($value['flow_check']['state']==1){
    				$t['status']=$t['status']."[异常]".$value['flow_check']['stext'];
    			}
    			$gxlist[]=$t;
    		}//end of foreach
    		
    		
    		
    		if($user['user_role']==4){//客户-只报工序组状态和时间
    			
    			//重置返回的数组
    			$gxlist=array();
    			
    			//查询所有的分组
    			$gp=$gx_gp=array();
    			foreach($gx as $value){
    				$gp[]=$value['gid'];
    				$gx_gp[$value['gid']][]=$value;//按分组组合工序
    			}
    			ksort($gx_gp);
    			
    			if(count($gp)>0){
    				$gp=array_unique($gp);
    				$gp_sql=implode(",", $gp);
    				$gp_list=Db::name("gx_group")->where("id in ($gp_sql)")->order("id asc")->select();
    				//读取缓存分组
    				$gx_group=@include_once APP_DATA.'gx_group.php';
    				$groups=array();
    				foreach($gp_list as $g_val){
    					$cache_id=$g_val['cache_id'];
    					$cache_order=$gx_group[$cache_id]['order'];//组别排序
    					$cache_name=$gx_group[$cache_id]['name'];//组别名称
    					$groups[$cache_order]['name']=$cache_name;
    					$groups[$cache_order]['id'][]=$g_val['id'];
    				}
    				
    				
    				//按分组查询状态
    				foreach($groups as $cache_order=>$value){
   
    					$t=array();
    					$t['name']=$value['name'];
    					$t['uname']=$value['flow_check']['uname'].$value['flow_check']['man'];
    					$t['status']='未开始';
    					$t['time']='';
    					
    					//循环$gx_gp获取每个工序状态
    					$total_end=$total_started=0;
    					$endtime=$starttime=0;
    					$ttotal=0;
    					
    					foreach($value['id'] as $gpid){
    						
    						$ttotal+=count($gx_gp[$gpid]);//每个小组的全部工序
	    					foreach($gx_gp[$gpid] as $gxval){
	    						if($gxval['flow_check']['endtime']>0){
	    							$end=$gxval['flow_check']['endtime'];
	    							if($end>$endtime){
	    								$endtime=$end;//找一个最晚结束的时间
	    							}
	    							$total_end++;
	    						}
	    						if($gxval['flow_check']['starttime']>0){
	    							$start=$gxval['flow_check']['starttime'];
	    							if($starttime<=0||($starttime>0&&$start<$starttime&&$start>0)){
	    								$starttime=$start;//找一个最早开始的时间
	    							}
	    							$total_started++;
	    						}
	    					}
	    					
    					}
    					
    					if($total_end==$ttotal){//全部完成
    						$t['status']='已完成';
    						$t['time']=date('Y-m-d',$endtime);
    					}else if($total_started>0||$total_end>0){//部分已开始
    						$t['status']='进行中';
    						if($starttime>0){
    							$t['time']=date('Y-m-d',$starttime);
    						}else{
    							if($endtime>0){
    								$t['time']=date('Y-m-d',$endtime);
    							}
    						}
    						
    					}
    					
    					$gxlist[$cache_order]=$t;
    				}
    			}
    			ksort($gxlist);
    		}//end of if($user['user_role']==2)
    			
    		
    		
    	}
    	
    	//动态入库
    	if (FIX_GX==0){
    	    $into_data = Db::name('into_order_gx')->alias('a')->field('a.*,b.uname')->join('login b','a.uid=b.id')->where("orderid='$id'")->order("id asc")->select();
    	    if ($into_data){
    	        foreach ($into_data as $k=>$inda){
    	            $data = array();
    	            $data['name']='入库组：'.$inda['name'];
    	            $data['uname']=$inda['uname'];
    	            $data['status']='已完成';
    	            $data['is_back']='';
    	            $data['state']='';
    	            $data['is_into']=1;
    	            $data['cause_back']='';
    	            $data['time']=$inda['into_time'];
    	            $data['store_space']=$inda['store_space'];
    	            $data['count']=$inda['count'];
    	            $data['note']=$inda['note'];
    	            array_push($gxlist,$data);
    	        }
    	        foreach ($into_data as $k=>$inda){
    	            if ($inda['is_out']==1){
    	                $data = array();
    	                $data['name']='出库组：'.$inda['name'];
    	                $data['uname']=$inda['uname'];
    	                $data['status']='已完成';
    	                $data['is_back']='';
    	                $data['state']='';
    	                $data['cause_back']='';
    	                $data['is_into']=1;
    	                $data['time']=$inda['out_time'];
    	                $data['store_space']=$inda['store_space'];
    	                $data['count']=$inda['count'];
    	                $data['note']=$inda['note'];
    	                array_push($gxlist,$data);
    	            }
    	        }
    	    }
    	}
    	$order['gx']=$gxlist;
    	
    	//查询要显示到订单列表的自定义字段和字段值
    	$fields=@include APP_DATA.'qrfield_type.php';
    	$qrfield=@include APP_DATA.'qrfield.php';
    	$fieldList=array();
    	if(isset($fields['onlist'])){
    		foreach($fields['onlist'] as $value){
    			$fieldList[$value['fieldname']]=$value;
    		}
    	}
    	
    	$field_data=array();
    	if(count($fieldList)>0){
    		foreach($fieldList as $name=>$value){
    			if($value['child']==''){
    				$field_data[$name]['value']=$order[$name];
    			}else{
    				$d=array();
    				$childs=explode(",", $value['child']);
    				foreach($childs as $cid){
    					if(isset($qrfield[$cid])){
    						$fieldname=$qrfield[$cid]['fieldname'];
    						$d[]=$order[$fieldname];
    					}
    				}
    				$field_data[$name]['value']=implode(",",$d);
    			}
    			
    			$field_data[$name]['field']=$name;
    			$field_data[$name]['cn']=$value['explains'];
    		}
    	}
    	//自定义字段和数据
    	$order['fieldlist']=$field_data;
    	
    	//订单报工图片
    	$imgs=array();
    	$imgList=M("gx_imgs")->field("imgurl")->where("orderid='$id' and imgurl is not null")->order("id asc")->select();
    	if($imgList){
    		foreach($imgList as $value){
    			if(trim($value['imgurl'])!=''){
    				$imgs[]="https://".PRO_DOMAIN.$value['imgurl'];
    			}
    		}
    	}
    	$order['imgs']=$imgs;
    	
    	return json_encode(array('code'=>'1','order'=>$order));
    }
    
    //查询订单
    public function query_order(){
    	
    	$uid=input("uid","","intval");
    	$uname=input("uname","","ctrim");//搜索的客户名称
    	$ordernum=input("ordernum","","ctrim");
    	$bhao=input("unique_sn","","ctrim");
    	$address = input("address","","ctrim");
    	$produce_sn = input("produce_sn","","ctrim");
    	$field=WX_SHOW_FIELD;
    	$field==''?$field='produce_no':'';
    	//$uid 是当前登录小程序的用户的uid
    	$user=Db::name("login")->where("id='$uid'")->field("id,user_role,master,client_name,custom")->find();
    	if($user===false||empty($user['id'])){
    		return json_encode(array('code'=>'2','msg'=>"您的账号不存在"));
    	}
    	
    	$user_role=$user['user_role'];//用户角色,4.客户，1.超级管理员
    	
    	/*if($user['user_role']!=1){
    		if($uname==''&&$ordernum==''&&$bhao==""){
    			return json_encode(array('code'=>'2','msg'=>"查询条件不能为空"));
    		}
    	}*/
    	
    	$sql=array();
    	
    	if($user_role==4){//精准搜索
    		if($user['client_name']==''){//客户要先绑定名称
    			return json_encode(array('code'=>'2','msg'=>"客户名称没绑定或设置为空"));
    		}
    		$uname = $user['client_name'];
    		if($uname!=''){
    		    $sql[]=" uname='$uname'";
    		}
    	}else {
    	    if($uname!=''){
    	        $sql[]=" uname like '%$uname%'";
    	    }
    	}
    	
    		
    	//跟单和业务一定要绑定客户，否则返回空
    	if($user_role==3||$user_role==5){
    			$custom=trim($user['custom']);
    			if($custom==''){//跟单和业务一定要绑定客户，否则返回空
    				$sql[]="uname='0'";
    			}else{
    				$limit_client=array();
    				if(strpos($custom, ",")!==false){
    					$limit_client=explode(",", $custom);
    				}else{
    					$limit_client[]=$custom;
    				}
    				
    				$sql[]="uname in (".simplode($limit_client).")";
    			}
    	}
    	
    	if($ordernum!=''){//销售单号
    			$sql[]=" ordernum like '%{$ordernum}%'";
    	}
    	
    	if (!empty($address)){//工程地址
    	   $sql[] = " address like '%{$address}%'";    
    	}
    	
    	if($bhao!=''){//订单编号
    			$sql[]=" unique_sn like '%{$bhao}%'";
    	}
    	
    	if (!empty($produce_sn) && $user_role==4){
    	    $where=" a.fieldname='produce_sn' and a.value like '%$produce_sn%'";
    	    $list=Db::name('order_attach')->alias('a')
    	       ->join("order b","a.orderid=b.id")
    	       ->where($where)->order("b.endstatus asc,b.ordertime desc")->column("b.id");
    	    if($list){
    	    	$sql[]=" id in (".implode(",",$list).")";
    	    }else{
    	    	$sql[]=" id ='0'";
    	    }
    	}else if (!empty($produce_sn) && $user_role!=4){
    	    $where=" a.fieldname='produce_sn' and a.value like '%$produce_sn%'";
    	    $list=Db::name('order_attach')->alias('a')
    	    ->join("order b","a.orderid=b.id")
    	    ->where($where)->order("b.ordertime desc,b.endtime desc")->column("b.id");
    	    if($list){
    	    	$sql[]=" id in (".implode(",",$list).")";
    	    }else{
    	    	$sql[]=" id ='0'";
    	    }
    	}
    	
    	$where=$this->senior_search("");
    	if($where!=''){
    		$sql[]=$where;
    	}
    	
    	if(count($sql)>0){
    	    for($i=0;$i<count($sql);$i++){
    	        $sql[$i] = 'a.'.$sql[$i];
    	    }
    		$sql=implode(" and ", $sql);
    	}
    	
    	if($user_role==4 ){
    	    $list=Db::name("order")->alias('a')->field("a.*,b.value")->join("order_attach b","a.id=b.orderid","LEFT")->where("b.fieldname='$field'and $sql")->order("a.endstatus asc,a.ordertime desc")->select();
    	}else if ($user_role!=4){
    	    $list=Db::name("order")->alias('a')->field("a.*,b.value")->join("order_attach b","a.id=b.orderid","LEFT")->where("b.fieldname='$field'and $sql")->order("a.ordertime desc,a.endtime desc")->select();
    	}

    	if($list&&count($list)>0){
    		foreach($list as $k=>$value){
    			$value['inorder']='0';//入库，0是未全部入库，1是全部入库
    			if($value['status']==1&&$value['intime']>0){
    				$value['inorder']='1';
    			}
    			$list[$k]=$value;
    			$list[$k]['wxfield']=$value['value'];
    		}
    	}
    	
    	return json_encode(array('code'=>'1','list'=>$list));
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
    
    //员工已报工订单查询接口
    public function staff_order(){
        $salary = PRO_SALARY;
        $field = WX_SHOW_FIELD;
        $all_field = @include APP_DATA.'qrfield.php';
        $complete_field = array();
        $field==''?$field='produce_no':'';
    	//报工时间段 、工序名 、入库时间段
    	//订单入库状态
    	//报工时间段筛选
    	$s_date = ctrim(input("param.start"));
    	$e_date = ctrim(input("param.end"));
    	$s_date = $s_date!=''?ymktime($s_date):0;
    	$e_date = $e_date!=''?ymktime($e_date)+24*60*60-1:0;
    	
    	//入库时间
    	$ins_date = ctrim(input("param.in_start"));
    	$ine_date = ctrim(input("param.in_end"));
    	$ins_date = $ins_date!=''?ymktime($ins_date):0;
    	$ine_date = $ine_date!=''?ymktime($ine_date)+24*60*60-1:0;
    	
    	//工序名
    	$gxname=ctrim(input("param.gxname"));
    	//筛选自定义显示字段
    	foreach ($all_field as $af){
    	    if ($af['salary_field']==1){
    	        array_push($complete_field,$af);
    	    }
    	}
    	//分页页码
    	$page=intval(input("page"));
    	$page=$page>0?$page:1;
    	
    	
    	//先查询报工记录，得到订单id
    	$offset=20;
    	$start=($page-1)*$offset;
    	
    	$flow_where=array();
    	if($s_date>0){
    		$flow_where[]=" (endtime>=$s_date) ";
    	}
    	
    	if($e_date>0){
    		$flow_where[]=" (endtime<=$e_date) ";
    	}
    	
    	//查询工序id
    	if($gxname!=''){
    		$gxlist=Db::name("gx_list")->where("dname='$gxname'")->column("id");
    		if($gxlist!==false&&count($gxlist)>0){
    			$flow_where[]=" orstatus in (".implode(",",$gxlist).") ";
    		}else{
    			$flow_where[]=" orstatus='0' ";//没有查询到工序，则没有报工记录
    		}
    	}
    	
    	$uid=intval(input("uid"));//用户login表的id
    	$flow_where=count($flow_where)>0?" and ".implode(" and ",$flow_where):'';
    	
    	$flow=Db::name("flow_check")
    			->field("orderid,orstatus,stext,starttime,endtime,state,error_time,status,isback,issend,in_num,sendtime")
    			->where("uid='$uid' $flow_where")->select();
    	$orderid=$gx_flow=array();
    	if($flow!==false&&count($flow)>0){
    		foreach($flow as $value){
    			$orderid[]=$value['orderid'];
    			$gx_flow[$value['orderid']][]=$value;//存储起已报工的数组
    		}
    	}
    	
    	//汇总订单数据,面积，扇数
    	$data=array('order'=>0,'area'=>0,'snum'=>0);
    	
    	if(count($orderid)>0){
    		$sql=" a.id in(".implode(",",$orderid).") ";
    		
    		if($ins_date>0){
    			$sql.=" and a.intime>=$ins_date ";
    		}
    		
    		if($ine_date>0){
    			$sql.=" and a.intime<=$ine_date ";
    		}
    		
    		//订单列表
    		$list=Db::name("order")->alias('a')->join("order_attach b","a.id=b.orderid","LEFT")->where("b.fieldname='$field' and $sql")
    		->field("a.id,a.uname,a.ordernum,a.status,a.outstatus,a.endstatus,a.intime,a.outtime,a.endtime,a.unique_sn,b.value")
    		->order("a.ordertime desc")->limit($start,$offset)->select();
    		$all_gx=array();
    		$gxs=Db::name("gx_list")->field("id,dname")->select();
    		foreach($gxs as $key=>$gx){
    			$all_gx[$gx['id']]=$gx['dname'];
    		}
    		//添加已报工工序
    		foreach($list as $key=>$value){
    			$id=$value['id'];
    			$gx=array();
    			$list[$key]['wxfield']=$value['value'];
    			//保存属于这张订单的多个工序的报工
    			foreach($gx_flow[$value['id']] as $val){
    				
    				$gx_id=$val['orstatus'];
    				$gx_name=$all_gx[$gx_id];
    				$t=array();
    				$t['dname']=$gx_name;
    				$t['starttime']=$val['starttime']>0?date('Y-m-d',$val['starttime']):'';
    				$t['endtime']=$val['endtime']>0?date('Y-m-d',$val['endtime']):'';
    				$gx[]=$t;
    			}
    			$list[$key]['endtime']=$value['endtime']>0?date('Y-m-d',$value['endtime']):'';
    			$list[$key]['gx']=$gx;
    			//自定义显示订单内容
    			$order_content = order_attach($id);
    			foreach ($complete_field as $cf){
    			    $list[$key]['complete'] .= $cf['explains'].'：'.$order_content[$cf['fieldname']].'；';
    			}
    			if($value['status']=='1'&&$value['endstatus']==2){
    				$list[$key]['status']="已入库";
    			}else if($value['status']=='1'&&$value['endstatus']==1){
    				$list[$key]['status']="部分入库";
    			}else{
    				$list[$key]['status']="未入库";
    			}
    		}
    		
    		//汇总统计我的订单数，方数和扇数
    		$orderId=Db::name("order")->alias('a')->where($sql)->column("a.id");
    		
    		if($orderId!==false&&count($orderId)>0){
    			
    			$attach=M("order_attach")->field("fieldname,value")->where("orderid in (".implode(",",$orderId).") and fieldname in ('snum','doornum','area')")->select();
    			if($attach!==false&&count($attach)>0){
    				foreach($attach as $value){
    				if($value['fieldname']=='doornum'){
    						$snum=trim($value['value']);
    						if($snum!=''){
    							$data['snum']+=floatval($snum);
    						}
    					}
    					if($value['fieldname']=='snum'){
    					    $num=trim($value['value']);
    					    if($num!=''){
    					        $data['order']+=floatval($num);
    					    }
    					}
    					if($value['fieldname']=='area'){
    						$area=trim($value['value']);
    						if($area!=''){
    							$data['area']+=round(floatval($area),2);
    						}
    					}
    				}
    			}
    			
    			$data['area']=$data['area']."m²";
    		}
    	
    	}else{
    		$list=array();//不返回订单
    	}
    	

    	return json_encode(array('code'=>'1','list'=>$list,'data'=>$data,'salary'=>$salary));
    }
    
    //全部订单
    public function order_summary(){
    	$sql="pause!='1' and repeal!='1'";
    	$total_order_num=Db::name("order")->where($sql)->count();		//总数量
    	$total_order_area=Db::name("order")->where($sql)->sum("area");	//总方数
    	$notin_sql=$sql." and intime<=0 ";
    	$order_num=Db::name("order")->where($notin_sql)->count();		//未入库总数量
    	$order_area=Db::name("order")->where($notin_sql)->sum("area");	//未入库总方数
    	
    	//超期订单
    	$yestoday=timezone_get(7);
    	$warmwhere.=" and endtime<{$yestoday['end']} ";
    	$warm_order_num=Db::name("order")->where("endstatus='1' ".$warmwhere)->count();		//总数量
    	$warm_order_area=Db::name("order")->where("endstatus='1' ".$warmwhere)->sum("area");	//总方数
    	
    	$return = [];
    	$return['total']=$total_order_num;
    	$return['total_area']=round($total_order_area,2);
    	$return['notin_total']=$order_num;
    	$return['notin_area']=round($order_area,2);
    	$return['over']=$warm_order_num;
    	$return['over_area']=round($warm_order_area,2);
    
    	return json_encode(array('data'=>$return));
    }
    
    //今天/本周/本月/本年的下单量
    public function timezone_order(){
    	
    	//时间段:day,week,month,year
    	$time=ctrim(input("time"));
    	$start = strtotime(input('start'));
    	$end = strtotime(input('end').' 23:59:59');
    	switch($time){
    		case 'day':
    			$timezone=timezone_get(1);
    			$pretimezone=timezone_get(7);
    		break;
    		case 'week':
    			$timezone=timezone_get(2);
    			$pretimezone=timezone_get(8);
    		break;
    		case 'month':
    			$timezone=timezone_get(3);
    			$pretimezone=timezone_get(9);
    		break;
    		case 'year':
    			$timezone=timezone_get(6);
    			$pretimezone=timezone_get(10);
    		break;
    	}
    	
    	$timesql='';
    	if($timezone['begin']>0&&$timezone['end']>0){
    		$timesql="addtime>={$timezone['begin']} and addtime<={$timezone['end']}";
    		$intimesql=" and intime>={$timezone['begin']} and intime<={$timezone['end']} ";
    		$outimesql=" and outtime>={$timezone['begin']} and outtime<={$timezone['end']} ";
    	}
    	
    	if($timesql==''){
    	    $timesql="addtime>={$start} and addtime<={$end}";
    	    $intimesql=" and intime>={$start} and intime<={$end} ";
    	    $outimesql=" and outtime>={$start} and outtime<={$end} ";
    	    $timezone['begin'] = $start;
    	    $timezone['end'] = $end;
    	}
    	
    	//上月下单sql
    	$return=array();
    	//计算总数和方数
    	//总生产中订单量
    	$gxs=$this->getAllGx();
    	$start_gx=$gxs['start'];
    	$end_gx=$gxs['end'];
    	$produce_order=array();
    	$sql=array();
    	$prefix=config("database.prefix");
    	$child_sql="SELECT a.id,a.unique_sn ,c.starttime,c.endtime,c.orstatus FROM `{$prefix}order` as a left join `{$prefix}flow_check`as c on c.orderid=a.id WHERE (c.starttime>0 or c.endtime>0) and a.addtime>={$timezone['begin']} and a.addtime<={$timezone['end']} GROUP by a.id order by c.endtime asc,c.starttime asc";
    	if(count($start_gx)>0){
    		$orstatus=array_keys($start_gx);
    		$sql[]="(starttime>={$timezone['begin']} and starttime<={$timezone['end']} and orstatus in (".implode(",",$orstatus)."))";
    	}
    	if(count($end_gx)>0){
    		$orstatus=array_keys($end_gx);
    		$sql[]="(((starttime>={$timezone['begin']} and starttime<={$timezone['end']}) or (endtime>={$timezone['begin']} and endtime<={$timezone['end']})) and orstatus in (".implode(",",$orstatus)."))";
    	}
    	 
    	if(count($sql)>0){
    		$mainsql="select * from ($child_sql) as temp where ".implode(" or ", $sql);
    	}else{
    		//没设置工序不返回
    		$mainsql="select * from ($child_sql) as temp where id='0'";
    	}
    	 
    	$list=Db::query($mainsql);
    	foreach($list as $k=>$value){
    		if(($value['starttime']<$timezone['begin']&&$value['starttime']>0)||$value['endtime']>$timezone['end']){
    			continue;
    		}
    		$produce_order[]=$value['id'];
    	}
    	
    	if(count($produce_order)<=0){
    		$psql="0";
    	}else{
    		$psql=implode(",",$produce_order);
    	}
    	//对应面积
    	$produce_area=Db::name('order')->where("id in (".$psql.")")->sum("area");
    	$doing_order_num=count($produce_order);
    	$doing_order_area=round($produce_area,2);

    	//入库订单
    	$in_order_num=Db::name("order")->where("1=1 $intimesql")->count();		//总数量
    	$in_order_area=Db::name("order")->where("1=1 $intimesql")->sum("area");	//总方数
    	
    	//出库订单
    	$out_order_num=Db::name("order")->where("1=1 $outimesql")->count();		//总数量
    	$out_order_area=Db::name("order")->where("1=1 $outimesql")->sum("area");	//总方数
 
    	//本
    	$schedule_num=Db::name("schedule_summary")->where("do_time>={$timezone['begin']} and do_time<={$timezone['end']} ")->sum("ordernum");
    	
    	$return=array();

    	$return['doing_order_num']=$doing_order_num;//总生产中单数
    	$return['doing_order_area']=round($doing_order_area,2);//生产中面积

    	$return['in_order_num']=$in_order_num;//本周期入库量
    	$return['in_order_area']=round($in_order_area,2);//本周期入库面积

    	$return['out_order_num']=$out_order_num;//本周期出库单总数
    	$return['out_order_area']=round($out_order_area,2);//本周期出库面积
    	
    	$return['schedule_num']=$schedule_num;//本周期排产量

    
    	return json_encode(array('code'=>'1','data'=>$return));
    }
    
    //获取所有的小工序
    private function getAllGx(){
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
    
    //超时工序
    public function overtime(){
    	
    	$timezone=$this->getTime();
    	 
    	$starttime=$timezone['begin'];
    	$endtime=$timezone['end'];
    	$timesql="";
    	if($starttime>0&&$endtime>0){
    		$timesql=" and endtime>=$starttime and endtime<=$endtime";
    	}
    	
    	//有超时的报工
    	$gx=Db::name("flow_check")->group("orstatus")->where("status='1' $timesql")->column("orstatus");
    	if($gx==false||count($gx)<=0){
    		return json_encode(array('code'=>'2','msg'=>"没有超时报工"));
    	}
    	
    	//加载工序缓存
    	$all_gx=@include_once APP_DATA.'gx_list.php';
    	
    	$data=array();
    	$total=0;
    	//分别查找数量
    	foreach($gx as $id){
    		$count=Db::name("flow_check")->where("status='1' and orstatus='$id' $timesql")->count();
    		$gx_name=$all_gx[$id]['dname'];
    		if(isset($data[$gx_name])){
    			$data[$gx_name]+=$count;
    		}else{
    			$data[$gx_name]=$count;
    		}
    		$total+=$count;
    	}
    	
    	if($total<=0){
    		return json_encode(array('code'=>'2','msg'=>"没有超时报工"));
    	}
    	
    	$output=array();
    	//计算百分比
    	foreach($data as $key=>$val){
    		$t=array();
    		$t['name']=$key;
    		$t['percent']=round($val/$total,2);
    		$output[]=$t;
    	}
    	
    	return json_encode(array('code'=>'1','data'=>$output,'total'=>$total));
    }
    
    //按时完成率
    public function finish_ontime(){
    	
    	$timezone=$this->getTime();
    	 
    	$starttime=$timezone['begin'];
    	$endtime=$timezone['end'];
    	
   		 //入库数量
    	$normal = Db::name('order')->field("area,intime,endtime")->where("status='1' and intime>=$starttime and intime<$endtime")->select();
    	//超时
    	$overtime=0;
    	//准时
    	$ontime=0;
    	//方数
    	$ontime_area = 0;
    	$overtime_area = 0;
    	
    	foreach($normal as $value){
    		$endtime=$value['endtime'];
    		$intime=$value['intime'];
    		if($intime>$endtime){
    			$overtime++;
    			$overtime_area+=$value['area'];
    		}else{
    			$ontime++;
    			$ontime_area+=$value['area'];
    		}
    	}
    	
    	$data=array();
    	$data['ontime']=$ontime;
    	$data['overtime']=$overtime;
    	$data['ontime_area']=round($ontime_area,2);
    	$data['overtime_area']=round($overtime_area,2);
    	return json_encode(array('code'=>'1','data'=>$data));
    }
    
    //工序异常
    public function exceptions(){
    	
    	$timezone=$this->getTime();
    	
    	$starttime=$timezone['begin'];
    	$endtime=$timezone['end'];
    	$timesql="";
    	$timesqls="";
    	if($starttime>0&&$endtime>0){
    		$timesql=" and error_time>=$starttime and error_time<=$endtime";
    		$timesqls=" and a.error_time>=$starttime and a.error_time<=$endtime";
    	}
    	
    	//有异常的报工
    	$gx=Db::name("flow_check")->group("orstatus")->where("state='1' $timesql")->column("orstatus");
    	if($gx==false||count($gx)<=0){
    		return json_encode(array('code'=>'2','msg'=>"没有异常报工"));
    	}
    	
    	//加载工序缓存
    	$all_gx=@include_once APP_DATA.'gx_list.php';
    	
    	$data=array();
    	//分别查找数量
    	foreach($gx as $id){
    	    //总的
    		$count=Db::name("flow_check")->alias('a')->field('a.isback,b.area')
    		      ->join("order b", "a.orderid=b.id","LEFT")->where("a.state='1' and a.orstatus='$id' $timesqls")->select();
    		$gx_name=$all_gx[$id]['dname'];
    		$uncount = 0;
    		$area = 0;
    		$list=array();
    		$list['name']=$gx_name;
    		$list['num']=count($count);
    		foreach ($count as $key=>$vl){
    		    if ($vl['isback']==0){
    		        $uncount++;
    		        $list['un_num']=$uncount;
    		        $list['un_area']+=floatval($vl['area']);
    		    }
    		    $list['area']+=floatval($vl['area']);
    		}
    		$list['un_num']?$list['un_num']:$list['un_num']=0;
    		$list['un_area']?$list['un_area']:$list['un_area']=0;
    		$list['un_area']=round($list['un_area'],2);
    		$list['area']=round($list['area'],2);
    		array_push($data, $list);
    	}

    	
    	
    	return json_encode(array('code'=>'1','data'=>$data));
    }
    
    //获取时间
    private function getTime(){
    	$time=input("time");//day week month year
    	
    	switch($time){
    			case 'day':
    				$timezone=timezone_get(1);
    				break;
    			case 'week':
    				$timezone=timezone_get(2);
    				break;
    			case 'month':
    				$timezone=timezone_get(3);
    				break;
    			case 'year':
    				$timezone=timezone_get(6);
    				break;
    			default:
    				$timezone=timezone_get(1);
    				break;
    	}
    	
    	$starttime = input('start');
    	$endtime = input('end');
    	 
    	if(!empty($starttime)&&!empty($endtime)){
    	
    		$starttime=strtotime($starttime);
    		$endtime=strtotime($endtime);
    		$timezone=array();
    		$timezone['begin']=$starttime;
    		$timezone['end']=$endtime+(24*60*60-1);
    	}
    	
    	return $timezone;
    }
    
    //工序异常详细
    public function exceptions_detail(){
    	
    	$gxname=input("gxname");
    	$all_gx=@include_once APP_DATA.'gx_list.php';
    	$field=WX_SHOW_FIELD;
    	$field==''?$field='produce_no':'';
    	$gxid=array();
    	foreach($all_gx as $key=>$value){
    		if($value['dname']==$gxname){
    			$gxid[]=$value['id'];
    		}
    	}
    	
    	if(count($gxid)<=0){
    		return json_encode(array('code'=>'2','msg'=>'工序不存在'));
    	}
    	
		$timezone=$this->getTime();
    	
    	$starttime=$timezone['begin'];
    	$endtime=$timezone['end'];
    	
    	if($starttime>0&&$endtime>0){
    			$timesql=" and a.error_time>=$starttime and a.error_time<=$endtime";
    	}else {
    	    $timesql = $timezone;
    	}
    	
    	
    	$list=Db::name("flow_check")->alias('a')->field('a.*,b.text,b.handle_time')
    	       ->join("check_back b","a.id=b.fid","LEFT")
    	       ->where("a.state='1' and a.orstatus in (".implode(",",$gxid).") $timesql")->order('a.isback asc')->select();
    	$return=array();//返回的数组
    	if($list){
    		$orderId=array();
    		foreach($list as $value){
    			$orderId[]=$value['orderid'];
    		}
    		//查询所有的生产单
    		$unique_sn=M("order")->alias('a')->field("a.id,a.uname,a.unique_sn,b.value,a.color,a.pname")
    		->join("order_attach b","a.id=b.orderid","LEFT")
    		->where("a.id in (".implode(",", $orderId).") and b.fieldname='$field'")->select();
    		unset($orderId);
    		$orders=array();
    		foreach($unique_sn as $value){
    			$orders[$value['id']]=$value;
    		}
    		
    		foreach($list as $value){
    			$id=$value['orderid'];
    			$t=array();
    			$t['id']=$value['id'];
    			$t['unique_sn']=$orders[$id]['unique_sn'];
    			$t['uname']=$orders[$id]['uname'];
    			$t['gxname']=$gxname;
    			$t['pname']=$orders[$id]['pname'];
    			$t['color']=$orders[$id]['color'];
    			$t['wxfield']=$orders[$id]['value'];
    			$t['error_time']=date('Y-m-d H:i:s',$value['error_time']);
    			$t['stext']=$value['stext'];
    			$t['text']=$value['text'];
    			$t['handle_time']=date('Y-m-d',$value['handle_time']);
    			$t['isback']=$value['isback'];//是否已处理
    			$return[]=$t;
    		}
    	}
    	return json_encode(array('code'=>'1','list'=>$return));
    }
    
    //超期订单
    public function warn_order(){

    	$where = "";
    	$now=time();
    	//$day = Db::name('warm_time')->field('day')->find();
    	//if($day===false||empty($day['day'])){
    	//	return json_encode(array('code'=>'2','msg'=>"请配置预警天数"));
    	//}
    	//$facttime = time()+($day['day']*24*3600);
    	//完成物料筛选
    	$pname=input("pname");
    	if (!empty($pname)){
    	    $gidL = Db::name('series')->where("xname like '%$pname%'")->column('id');
    	    $where = " and series_id in (".implode(",", $gidL).") ";
    	}
		$yestoday=timezone_get(7);
    	$where.= "and endtime<{$yestoday['end']}";
    	
    	
    	//统计-----
    	$total=Db::name('order')->where("endstatus=1 $where")->count();
    	$total_area=Db::name('order')->where("endstatus=1 $where")->sum("area");
    	//统计END-----
    	
    	
    	$step=50;
    	$page=input("page")>0?input("page"):1;
    	$start=($page-1)*$step;
    	$list = Db::name('order')
    	->where("endstatus=1 and status<1 $where")
    	->field("id,uname,ordernum,unique_sn,pname,area")
    	->order('endtime desc')
    	->limit($start,$step)
    	->select();
        
    	//单总数、总面积
    	$data = array();
    	$data['total']=$total;
    	$data['total_area']=round($total_area,2);
    	if ($list){
    	    foreach ($list as $key=>$vl){
    	        //是否存在异常
    	        $id = $vl['id'];
    	        $exist = Db::name('flow_check')->where("orderid=$id and state=1")->find();
    	        $exist?$list[$key]['bad']=1:$list[$key]['bad']=0;
    	    }
    	}
    	return json_encode(array('code'=>'1','data'=>$list,'total'=>$data));
    }
    
    //通过排产批次号查询
    public function schedule_order(){
        $field = WX_SHOW_FIELD;
        $field==''?$field='produce_no':'';
    	$uid = intval(input('uid'));//用户表bg_login的id字段
    	$schedule_no=ctrim(input("schedule_no"));//PC2019，FPC2020...之类的排产批次号
    	if(strpos($schedule_no, "FPC")!==false){
    		//固定批次排产单
    		$schedule=M("fixed_schedule")->where("schedule_no='$schedule_no'")->find();
    		if($schedule===false||empty($schedule['id'])){
    			return json_encode(array('code'=>'2','msg'=>"排产批次号不存在"));
    		}
    		$orderid=unserialize($schedule['orderid']);
    		$list=array();
	    	foreach($orderid as $id){
	    		if(!empty($id)){
	    			$t=array();
	    			$t['order_id']=intval($id);
	    			$list[]=$t;
	    		}
	    	}
    	}else{
    		//
    		$summary=Db::name("schedule_summary")->where("schedule_no='$schedule_no'")->find();
    		if($summary===false||empty($summary['id'])){
    			return json_encode(array('code'=>'2','msg'=>"排产批次号不存在"));
    		}
    		
    		//只返回未完成的排产 @hj 2020-03-17客户提出
    		$list=Db::name("schedule")->where("sid='{$summary['id']}' and finished!='1'")->select();
    		if($list===false||count($list)<=0){
    			return json_encode(array('code'=>'2','msg'=>"无排产记录"));
    		}
    	}
    	
    	$login=Db::name("login")->field("user_role,tid")->where("id='$uid'")->find();
    	if($login===false){
    		return json_encode(array('code'=>1,'msg'=>'用户不存在'));
    	}else if($login['tid']<=0){
    		return json_encode(array('code'=>1,'msg'=>'员工未绑定班组'));
    	}else if($login['tid']!=$summary['tid']){
    		return json_encode(array('code'=>1,'msg'=>'您不属于该排产单设置的负责班组'));
    	}
    	
    	$order=array();
    	foreach($list as $value){
    		if($value['order_id']>0){
    			$order[]=$value['order_id'];
    		}
    	}
    	
    	
    	if(count($order)<=0){
    		return json_encode(array('code'=>1,'msg'=>'没有订单数据'));
    	}
    	//查询订单列表
    	$olist=Db::name('order')->alias('a')->join("order_attach b","a.id=b.orderid","LEFT")->field("a.id,a.ordernum,a.gid,a.series_id,a.unique_sn,b.value,a.gxline_id")->where('a.id','in',$order)->where('b.fieldname','=',$field)->select();
    	
    	return json_encode(array('code'=>0,'result'=>$olist));
    	
    }
    
    /**
     * 各工序产值㎡数
     * @param string $time 时间筛选:默认今天,time值为:day,week,month,year
     */
    public function dayProduct()
    {
//         $export = new IndexExport();
    	$time = $this->getTime();
    	if ($time['begin']>0&&$time['end']>0){
    	    $timesql ="a.endtime>={$time['begin']} and a.endtime<{$time['end']} ";
        }
        
        
        //所选时间段所报工的工序
        $gxList = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')
                ->where($timesql)
                ->group('b.dname')
                ->column('b.dname');
        //统计工序的面积和报工人数(产值=面积/人数)
        $list = [];
        foreach ($gxList as $k => $v) {
            $area = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')
                ->join('order c','a.orderid=c.id')
                ->where('b.dname',$v)
                ->where($timesql)
                ->sum('area');
            $people = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')
                ->where('b.dname',$v)
                ->where($timesql)
                ->group('a.uid')
                ->count();
            $list[$k]['name'] = $v;
            $list[$k]['value'] = round($area/$people,2);
        }
        //排序
        $sort = [];
        foreach ($list as $key => $value) {
            $sort[] = $value['value'];
        }
        array_multisort($sort,SORT_DESC,$list);
        return json_encode(array('code'=>0,'result'=>$list));
    }
    
    /**
     * 工序产能分析占比
     * @param string $time 时间筛选:time值为:day,week,month,year
     */
    public function producing_analize()
    {
//        $export = new IndexExport();
        $time = $this->getTime();        
        //所选时间段所对应的上一段时间
//         $lastTime = $export->getLasttime();
        
        $timesql = "a.endtime>={$time['begin']} and a.endtime<{$time['end']}";
//         $lastTimesql = "a.endtime>={$lastTime['begin']} and a.endtime<{$lastTime['end']}";
    	//所选时间段所报工的工序名称
        $gxName = Db::name('flow_check')->alias('a')
                ->join('gx_list b','a.orstatus=b.id')->where($timesql)
                ->group('dname')
                ->column('dname');
      
        //获取工序对应的面积
        $list = [];
        foreach ($gxName as $k => $v) {
            $list[$k]['name'] = $v;
            $area = Db::name('flow_check')->alias('a')->field('b.dname,c.area')
                ->join('gx_list b','a.orstatus=b.id')
                ->join('order c','a.orderid=c.id')
                ->where($timesql)->where('b.dname',$v)
                ->column('area');
            $list[$k]['area'] = round(array_sum($area),2);
//             $yarea = Db::name('flow_check')->alias('a')->field('b.dname,c.area')
//                 ->join('gx_list b','a.orstatus=b.id')
//                 ->join('order c','a.orderid=c.id')
//                 ->where($lastTimesql)->where('b.dname',$v)
//                 ->column('area');

//             $list[$k]['yarea'] = round(array_sum($yarea),2);
        }
        //排序
        $sort = [];
        foreach ($list as $key => $value) {
            $sort[] = $value['area'];
        }
        array_multisort($sort,SORT_DESC,$list);
        return json_encode(array('code'=>0,'result'=>$list));
    }
    
    /**
     * 生产计划达成率图表
     * @param string $time 时间筛选:time值为:day,week,month,year
     */
    public function schedulePercent()
    {
//         $export = new IndexExport();
        $time = $this->getTime();
        $timesql = "do_time>={$time['begin']} and do_time<={$time['end']}";
        //班组和排产总数
        $cache=@include_once APP_DATA.'team_list.php';
        $team = Db::name('schedule_summary')->field('*,sum(ordernum) as ordernum')->where($timesql)->group('tid')->select();
        foreach ($team as $k => $v) {
            $team[$k]['team_name'] = $this->get_team_link($v['tid'], $cache);
        }        
        //完成数
        $complete = [];
        foreach ($team as $k => $v) {
            $complete[] = Db::name("schedule")->alias('a')->field('a.*')
                    ->join('schedule_summary b','a.sid=b.id')
                    ->where("a.tid='{$v['tid']}' and finished='1'")
                    ->where("a.do_time>={$time['begin']} and a.do_time<={$time['end']}")
                    ->count();
        }
        return json_encode(array('code'=>0,'result'=>['team' => $team,'complete' => $complete]));
    }
    
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
    	return implode("-",$str);
    }
    
    
    /**
     * 系列与颜色图表数据
     * @param string $time 时间筛选:time值为:day,week,month,year
     */
    public function seriesColor()
    {
//         $export = new IndexExport();
        $time = $this->getTime();
        $timesql = "addtime>={$time['begin']} and addtime<={$time['end']}";
        
        $series = Db::name('order')->field('pname as series,count(id) as count')
                ->where($timesql)->group('pname')
                ->order('count desc')->limit(10)->select();
        $color = Db::name('order')->field('color,count(id) as count')
                ->where($timesql)->group('color')
                ->order('count desc')->limit(10)->select();
        return json_encode(array('code'=>0,'result'=>['series' => $series,'color' => $color]));
    }
    
    /**
     * 员工提成工资详情
     *  参数：筛选年份（year）,筛选年月份（month）,筛选是否入库（inx）
     *  参数：员工id,页数page
     */
    public function staffSalary(){
        //小程序显示单号
        $field = WX_SHOW_FIELD;
        $field==''?$field='produce_no':'';
        // 分页页码
        $page = intval(input("page"));
        $page = $page > 0 ? $page : 1;
        $offset = 20;
        $start = ($page - 1) * $offset;
        
        // 年份、年月份筛选
        $year = input('year');
        $month = input('month');
        $uid = intval(input('uid'));
        $uname = input('uname');
        $where = '';
        
        if (! empty($year)) {
            $s_year = strtotime($year . '-01-01');
            $e_year = strtotime($year . '-12-31 23:59:59');
            $where = " and a.endtime between $s_year and $e_year";
        }
        if (! empty($month)) {
            $s_month = strtotime($month . '-01');
            $changedate = explode('-', $month);
            if ($changedate[1] == '12') {
                $changedate[0] = intval($changedate[0]) + 1;
                $changedate[1] = '01-01';
            } else {
                $changedate[1] = intval($changedate[1]) + 1;
                $changedate[1] = $changedate[1] . '-01';
            }
            $e_month = strtotime($changedate[0] . '-' . $changedate[1]);
            $where = " and a.endtime between $s_month and $e_month";
        }
        $back = $this->salary($uid, $where,$uname);
        // 订单
        $order = Db::name('flow_check')->alias('a')
        ->field('a.orstatus,d.price,b.id,b.unique_sn,c.dname,a.num,a.salary,e.value')
        ->join('order b', 'a.orderid=b.id', 'LEFT')
        ->join('gx_list c', 'a.orstatus=c.id', 'LEFT')
        ->join('formula d', 'd.id=a.sid', 'LEFT')
        ->join('order_attach e','b.id=e.orderid','LEFT')
        ->where("a.uid=$uid and a.state=0 and a.endtime<>0 and a.status=0 and e.fieldname='$field' and a.salary!='' or a.man='$uname' $where")
        ->order('a.id asc')
        ->limit($start, $offset)
        ->select();
        
        $total_price = 0;
        $total_num = 0;
        $new_list = [];
        if ($order) {
            // 计算提成
            foreach ($order as $key => $vl) {
                if (!empty($vl['salary'])){
                    $total_price += $vl['salary'];
                    $total_num += $vl['num'];
                    array_push($new_list, $vl);
                }
                
            }
        }
        echo json_encode(array(
            'code' => 0,
            'data' => $new_list,
            'total_num' => $back['total_num'],
            'total_price' => $back['total_price']
        ));
    }
    
    //员工总计件数量、提成
    public function salary($uid,$condition,$uname){
        $order = Db::name('flow_check')->alias('a')
        ->field('a.orstatus,a.salary,a.num,b.id,b.unique_sn')
        ->join('order b', 'a.orderid=b.id', 'LEFT')
        ->where("a.uid=$uid and a.state=0 and a.endtime<>0 and a.status=0 $condition")
        ->order('a.id asc')
        ->select();
        
        $total_price = 0;
        $total_num = 0;
        if ($order) {
            // 计算提成
            foreach ($order as $key => $vl) {
                $total_price += $vl['salary'];
                $total_num += $vl['num'];
            }
        }
        return array(
            'total_price'=>round($total_price,2),'total_num'=>round($total_num,2));
    }
    
    //@2020-05-20 工序完成情况
    public function gxComplete(){
    	//时间段:day,week,month,year
    	$time=ctrim(input("time"));
    	$starttime = input('starttime');
    	$endtime = input('endtime');
    	
    	$timesql=$otimesql="";
    	if(!empty($starttime)||!empty($endtime)){
    		
    		$time1=$time2=array();
    		if(!empty($starttime)){
    			$starttime=strtotime($starttime);
    			$time1[]="endtime>=$starttime";
    			$time2[]="addtime>=$starttime";
    		}
    		
    		if(!empty($endtime)){
    			$endtime=strtotime($endtime)+24*60*60-1;
    			$time1[]="endtime<=$endtime";
    			$time2[]="addtime<=$endtime";
    		}
    		
    		$timesql=implode(" and ",$time1);
    		$otimesql=implode(" and ",$time2);
    		
    	}else if(!empty($time)){
    		
    		switch($time){
    			case 'day':
    				$timezone=timezone_get(1);
    				break;
    			case 'week':
    				$timezone=timezone_get(2);
    				break;
    			case 'month':
    				$timezone=timezone_get(3);
    				break;
    			case 'year':
    				$timezone=timezone_get(6);
    				break;
    		}
    		
    		$starttime=$timezone['begin'];
    		$endtime=$timezone['end'];
    		
    		if($starttime>0&&$endtime>0){
    			$timesql="endtime>=$starttime and endtime<=$endtime";
    		 	$otimesql="addtime>=$starttime and addtime<=$endtime";
    		}
    	}
    	
    	if($timesql==''){
    		return json_encode(array('code'=>'2','msg'=>"请提交时间段"));
    	}
    	
    	//所有工艺路线
    	$line=Db::name("gx_line")->order("id asc")->column("id");
    	if(count($line)>0){
    		$sql=implode(",",$line);
    		//读取下面的所有工序
    		$gx=Db::name("gx_list")->where("lid in ($sql)")->order("orderby asc")->field("id,lid,dname,work_value,work_unit,orderby")->select();
    		if(!$gx||count($gx)<=0){
    			return json_encode(array('code'=>'2','msg'=>"请设置工序"));
    		}
    	}else{
    			return json_encode(array('code'=>'2','msg'=>"请设置工艺路线"));
    	}
    	
    	//存储起lid=>多个工序
    	$line_gxid=array();
    	//存储起每个doclass包含的小工序Id
  		$doclass_gxid=array();
    	$doclass_list=@include APP_DATA.'doclass.php';
    	
    	$gxlist=array();
    	foreach($gx as $value){
    		$id=$value['id'];
    		$lid=$value['lid'];
    		$gxlist[$id]=$value['dname'];
    		$line_gxid[$lid][]=$id;
    	}

//    	foreach($doclass_list as $value){
//    		if(trim($value['line_id'])!=''){
//    			$line_id=explode(",",$value['line_id']);
//    			$t=array();
//    			foreach($line_id as $lid){
//    				if(isset($line_gxid[$lid])){
//    					$t=array_merge($t,$line_gxid[$lid]);
//    				}
//    			}
//    			if(count($t)>0){
//    				$doclass_gxid[$value['id']]=$t;
//    			}
//    		}
//    	}
//
//    	unset($doclass_list);
    	
    	//返回数据
    	$return=array();
    	/**
    	 * array(
    	 * 		'备料'=>array('total'=>100,'finish'=>200,'unfinish'=>800,'start'=>1595220050,'end'=>'1595220100'),...
    	 * )
    	 */
    	
    	$sql=" endtime>0 ";
    	//查询所有的已完成的工序报工并且按工序归类
    	$list=M("flow_check")->where($sql)->field("id,uid,orderid,orstatus,starttime,endtime,issend")->select();
    	
    	$check_list=array();
    	foreach($list as $value){
    		$orderid=$value['orderid'];
    		$gxid=$value['orstatus'];
    		$check_list[$orderid][$gxid]=$value;
    	}
    	
    	unset($list);
    	
    	foreach($gxlist as $gxid=>$name){
    		$return[$name]['finish']=0;
    		$return[$name]['unfinish']=0;
    		$return[$name]['start']=$starttime;
    		$return[$name]['end']=$endtime;
    	}
    	
    	//遍历报工记录
    	foreach($check_list as $orderid=>$value){
    		foreach($value as $gxid=>$check){
    			//在筛选时间段内
    			if($check['endtime']>=$starttime&&$check['endtime']<=$endtime){
    				$gxname=$gxlist[$gxid];
    				$return[$gxname]['finish']+=1;
    			}
    		}
    	}
    	
    	
    	$sql=" repeal='0' and pause='0' ";//订单筛选条件：未完成、不取消、不暂停
    	$all_orders=M("order")->where($sql)->field("id,gid,ordernum,unique_sn,gxline_id")->select();
    	foreach($all_orders as $value){
    		//订单ID
    		$id=$value['id'];
    		$did=$value['gid'];
    		$gxlineId = explode(',',$value['gxline_id']);
    		//该订单的所有小工序
//    		if(!isset($doclass_gxid[$did])){
//    			continue;
//    		}
//    		$gxs=$doclass_gxid[$did];
            $gxs = combine_gx_line($gxlineId);
    		//遍历订单的所有工序
    		foreach($gxs as $gxid){
    			if(isset($gxlist[$gxid['id']])){
    				$gxname=$gxlist[$gxid['id']];
    				 
    				//遍历该订单的报工记录，如果没的话就是未完成
    				if(!isset($check_list[$id][$gxid['id']])){
    					$return[$gxname]['unfinish']+=1;
    				}
    			}
    		}
    	}
	
    	
    	unset($check_list);
    	unset($all_orders);
    	
    	 
    	return json_encode($return);
    	
    }
    
    //工序完成情况详细
    //$start 开始时间戳  $end 结束时间戳
    //$gxname 是工序名称
    public function gxCompleteDetail(){
    	//时间段
    	$start=input("start");
    	$end=input("end");
    	$gxname=ctrim(input("gxname"));
    	$field=WX_SHOW_FIELD;
    	$field==''?$field='produce_no':'';
		//查询工序
    	$gx=Db::name("gx_list")->where("dname='$gxname'")->field("id,lid,dname,work_value,work_unit,orderby")->select();
    	if(!$gx||count($gx)<=0){
    		return json_encode(array('code'=>'2','msg'=>"工序不存在"));
    	}
    	
    	//存储起lid=>多个工序
    	$line_gxid=array();
    	//存储起每个doclass包含的小工序Id
    	$doclass_gxid=array();
    	 
    	$gxlist=array();
    	foreach($gx as $value){
    		$id=$value['id'];
    		$lid=$value['lid'];
    		$gxlist[$id]=$value['dname'];
    		$line_gxid[$lid][]=$id;
    	}
    	
    	$gxid=array_keys($gxlist);
    	
//    	$did=getdid_from_gxid($gxid);
//
//    	if(!$did||count($did)<=0){
//    		return json_encode(array('code'=>'2','msg'=>"工序没有设置到工艺线内"));
//    	}
    	
    	$doclass_list=@include APP_DATA.'doclass.php';
    	$gx_line=@include APP_DATA.'lines.php';
    	$gx_list=@include APP_DATA.'gx_list.php';
    	$indata['doclass']=$doclass_list;
    	$indata['gx_line']=$gx_line;
    	$indata['gx_list']=$gx_list;
    	
//    	foreach($did as $_id){
//    		$did_gx=gxlist_from_did_cache($_id,$indata);
//    		if($did_gx){
//    			foreach($did_gx as $value){
//    				$doclass_gxid[$_id][]=$value['id'];
//    			}
//    		}
//    	}
    	
    	$sql="orstatus in (".implode(",",$gxid).")";
    	
    	/*if(!empty($start)&&$start>0){
    		$sql.=" and endtime>=$start ";
    	}
    	if(!empty($end)&&$end>0){
    		$sql.=" and endtime<=$end ";
    	}*/
    	//查询所有的已完成的工序报工并且按工序归类
    	$list=M("flow_check")->where($sql)->field("id,uid,orderid,orstatus,starttime,endtime,issend")->select();
    	 
    	$check_list=array();
    	foreach($list as $value){
    		$orderid=$value['orderid'];
    		$gxid=$value['orstatus'];
    		$check_list[$orderid][$gxid]=$value;
    	}
    	 
    	unset($list);
    	
    	//查询当前工序的所有订单
        $lineId = getlineid_from_gxid($gxid);
        if(!$lineId||count($lineId)<=0){
    		return json_encode(array('code'=>'2','msg'=>"工序没有设置到工艺线内"));
    	}
        $lineIdstr = implode('|',$lineId);
        $sql=" repeal='0' and pause='0' and CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'";//订单筛选条件：未完成、不取消、不暂停
//    	$sql=" repeal='0' and pause='0' and gid in (".implode(",",$did).")";//订单筛选条件：未完成、不取消、不暂停
    	/*if(!empty($start)&&$start>0){
    		$sql.=" and addtime>=$start ";
    	}
    	if(!empty($end)&&$end>0){
    		$sql.=" and addtime<=$end ";
    	}
    	*/
    	$all_orders=M("order")->alias('a')->join("order_attach b","a.id=b.orderid","LEFT")->where("b.fieldname='$field' and $sql")->field("a.id,a.gid,a.ordernum,a.unique_sn,b.value,a.gxline_id")->select();
    	//查找所有用户
    	$user=M("login")->field("id,uname")->where(array())->select();
    	$users=array();
    	foreach($user as $value){
    		$users[$value['id']]=$value['uname'];
    	}
    	 
    	$infolist =array();
    	$gxid=array_keys($gxlist);
    	foreach($all_orders as $value){
    		//订单ID
    		$id=$value['id'];
    		$did=$value['gid'];
    		//该订单工艺线的所有工序
//    		$gxs=$doclass_gxid[$did];
            $gxlineId = explode(',',$value['gxline_id']);
    		$gxs = combine_gx_line($gxlineId,1);

    		//判断工序是否在订单的工艺线内
    		$in=false;
    		$rightid=0;
    		foreach($gxid as $_id){
    			if(in_array($_id, $gxs)){
    				$in=true;
    				$rightid=$_id;
    				break;
    			}
    		}

    		if($in){
    			$t=array();
    			$t['unique_sn']=$value['unique_sn'];
    			$t['wxfield']=$value['value'];
    			//该订单在筛选时间内完成的工序
    			if(isset($check_list[$id][$rightid])){
    				$endtime=$check_list[$id][$rightid]['endtime'];
    				if($endtime>=$start&&$endtime<=$end){
    					$uid=$check_list[$id][$rightid]['uid'];
    					$t['uname']=$users[$uid];
    					$t['time']=date('Y-m-d H:i:s',$endtime);
    				}else if($endtime<=0){
    					$t['uname']="/";
    					$t['time']="/";
    				}else{
    					continue;
    				}			
    			}else{
    				$t['uname']="/";
    				$t['time']="/";
    			}
    			$infolist[]=$t;
    		}
    	}
    	
    	unset($check_list);
    	unset($all_orders);
    	
    	$return['list']=$infolist;
    	$return['gxname']=$gxname;
    	$time[]=$start>0?date('Y-m-d',$start):'';
    	$time[]=$end>0?date('Y-m-d',$end):'';
    	$return['time']=implode(" - ",$time);
    	
    	$people=array();
    	$finish=$unfinish=0;
    	foreach($infolist as $value){
    		if($value['uname']!='/'){
    			$people[$value['uname']]=$value['uname'];
    		}
    		if($value['time']!='/'){
    			$finish++;
    		}else{
    			$unfinish++;
    		}
    	}
    	$people=count($people);
    	$return['man']=$people;
    	$return['finish']=$finish;
    	$return['unfinish']=$unfinish;
    	
    	return json_encode($return);
    	
    }
    
    //处理异常工序订单
    function handle_unnormal(){
        $fid = intval(input("param.id"));
        $content = ctrim(input("param.text"));
        $time = time();
        $uid=intval(input("id"));
        $date = input("date");
        $date?$date=strtotime($date):$date=0;
        //处理录入
        if (empty($fid) || empty($content)){
            return json_encode(array('code'=>1,'msg'=>'参数缺失'));
            exit();
        }
        $result = M('check_back')->insert(array('fid'=>$fid,'text'=>$content,'addtime'=>$time,'uid'=>$uid,'handle_time'=>$date));
        if ($result){
            $update = M('flow_check')->where("id=$fid")->update(array('isback'=>1));
            return json_encode(array('code'=>0,'msg'=>'保存成功'));
            exit();
        }
        return json_encode(array('code'=>1,'msg'=>'保存失败'));
    }
    
    //异常竣工订单
    function finish_unnormal(){
        $fid = intval(input("param.id"));
        if (empty($fid)){
            return json_encode(array('code'=>1,'msg'=>'参数缺失'));
            exit();
        }
        $update = M('flow_check')->where("id=$fid")->update(array('isback'=>2));
        if ($update){
            return json_encode(array('code'=>0,'msg'=>'保存成功'));
            exit();
        }
        return json_encode(array('code'=>1,'msg'=>'保存失败'));
    }
    //后台限制显示模块
    function limit_model(){
        $uid = input("uid/d");
        $type = input("type/d");
        if ($uid){
            $type==1?
                $result = Db::name("login")->field("wxmodel")->where("id",$uid)->find():
                $result = Db::name("login")->field("wx_function_model")->where("id",$uid)->find();
            return $result?json_encode(array('code'=>0,'data'=>$result)):'';
        }else {
            return json_encode(array('code'=>1,'msg'=>'参数缺失'));
        }
    }

    //小程序标题
    public function wx_title(){
        $title = SITE_NAME;
        return json_encode(array('title'=>$title));
    }


    /**
     * 入库界面的工序名称
     */
    public function gxList()
    {
        $list = Db::name('into_gx')->order('id desc')->select();
        $this->_success('',$list);
    }

    /**
     * 用户可选择的打印机
     * @param type int 类型,1入库打印机，3半成品打印机
     */
    public function selectPrint()
    {
        $type = input('type',1);
//        $print = new \app\index\service\PrintYi();
//        $printList = $print->getPrintStyle($uid);
        $printList =  Db::name('print_style')->field('*,name as dname')->where('type',$type)->select();
        if(!$printList){
            $this->_error('请联系管理员添加打印机');
        }
        $this->_success('',$printList);
    }

    /**
     * 执行打印并入库
     * @param print_type int 种类,1入库打印，2半成品打印,不传默认为1
     * @param uid int 员工id
     * @param string unique_sn 订单编号
     * @param string type 种类
     * @param int count 数量
     * @param int print_id 用户选择的打印机id
     * @param int all_into 是否整单入库:0否,1是
     */
    public function executePrint()
    {
        $printType = input('print_type',1);
        $uid = input('uid');
        $orderNumber = input('unique_sn');
        $type = input('type');
        $count = input('count',1);
        $printId = input('print_id');
//        $allInto = input('all_into',0);

        $data = Db::name('order')->where('unique_sn',$orderNumber)->find();
        $orderid = $data['id'];
        if(!$data){
            $this->_error('未找到此订单');
        }

        //将订单附表数据合并到一起
        $list = order_attach($data['id']);
        $list = array_merge($data,$list);
        $list['type'] = $type;

        //入库打印需执行 入库逻辑，半成品打印只打标签
        if($printType == 1){
            $store = input("cw");
            $note = input("note");
            //查询入库种类表是否存在提交的种类,不存在则添加
            $gxtype = Db::name('into_gx')->where('name',$type)->find();
            $gx_id = $gxtype['id'];
            if(!$gxtype){
                $gx_id=Db::name('into_gx')->insert(['name'=>$type,'addtime'=>time()]);
            }
            //计件所需变量
            $salary = 0;
            $nums = 0;
            $fid = - 1;
            if (PRO_SALARY == 1) {
                /* 提成计算start */
                $formula = Db::name('formula')->where("gxid=$gx_id and text !='' and is_into=1")
                ->order('sort asc')
                ->select();
                $orderdetail = Db::name('order_attach')->where("orderid=$orderid")->select();
                if ($formula) {
                    for ($s = 0; $s < count($formula); $s ++) {
                        $sid = $formula[$s]['sid'];
                        strchr($formula[$s]['text'], '|') ? $pd_content = explode( '|',$formula[$s]['text']) : $pd_content = array(
                            $formula[$s]['text']
                        );
                        $pd = $formula[$s]['fieldname'];
                        // 订单详情
                        $compare = '';
                        $value = '';
                        foreach ($orderdetail as $dl) {
                            if ($dl['fieldname'] == $pd) {
                                $compare = $dl['value'];
                            }
                            $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                        }
                        $value .= 'in_num'.'='.$count;
                        parse_str($value);
                        foreach ($pd_content as $pc){
                            if (strpos($compare,$pc)!==false){
                                if (! empty($sid)) {
                                    //                                     $order_data = order_attach($orderid);
                                    $second_res = Db::name('se_formula')->where("id=$sid")->find();
                                    $pd = $second_res['fields'];
                                    $se_text = $list[$pd];
                                    strchr($second_res['content'], '|') ? $pd_text = explode( '|',$second_res['content']) : $pd_text = array($second_res['content']);
                                    foreach ($pd_text as $pt){
                                        if (strpos($se_text,$pt)!==false) {
                                            // 计件数量
                                            $jnum = $formula[$s]['formula_text'];
                                            $nums = eval("return $jnum;");
                                            $salary = $nums * $formula[$s]['price'];
                                            $fid = $formula[$s]['id'];
                                            break;
                                        }
                                    }
            
                                } else {
                                    // 计件数量
                                    $jnum = $formula[$s]['formula_text'];
                                    $nums = eval("return $jnum;");
                                    $salary = $nums * $formula[$s]['price'];
                                    $fid = $formula[$s]['id'];
                                    break;
                                }
                            }
                        }
                        if ($fid>-1){
                            break;
                        }
                    }
                }
                if ($salary == 0) {
                    $formula_l = Db::name('formula')->where("gxid=$gx_id and text ='' and is_into=1")->find();
                    if ($formula_l) {
                        $value = '';
                        foreach ($orderdetail as $dl) {
                            $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                        }
                        $value .= 'in_num='.$count;
                        parse_str($value);
            
                        // 计件数量
                        $jnum = $formula_l['formula_text'];
                        $nums = eval("return $jnum;");
                        $salary = $nums * $formula_l['price'];
                        $fid = $formula_l['id'];
                    }
                }
                /* 提成计算end */
            }
            //插入订单入库工序表
            $orderGx = ['uid'=>$uid,'orderid'=>$data['id'],'name'=>$type,'count'=>$count,'into_time'=> date('Y-m-d'),'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'store_space'=>$store,'note'=>$note];
            Db::name('into_order_gx')->insert($orderGx);
            //如果此订单状态为未入库，更新订单状态
            if($data['status'] == 0){
                Db::name('order')->where('id',$data['id'])->update(['status'=>1]);
            }
            //如果是整单入库,则将endstatus=2
//            if($allInto == 1){
//                Db::name('order')->where('id',$data['id'])->update(['endstatus'=>2]);
//            }

            $this->_success('入库成功');
        }else{
            $print = new PrintYi();
            $print->setPrint($printId,$orderNumber);
            $res = $print->executePrint($list, $count);//执行打印
            if(!$res){
                $this->_error('打印失败,请重试');
            }
            $this->_success('打印成功');
        }
    }

    /**
     * 获取订单下的可出库工序
     * @param number string 订单编号
     */
    public function outOrderGx()
    {
        $uniqueSn = input('unique_sn');
        $res = Db::name('into_order_gx')->alias('a')->field('a.id,a.name,a.count')->join('order b','a.orderid=b.id')
            ->where('b.unique_sn',$uniqueSn)->where('a.is_out',0)->order('id desc')
            ->select();
        $this->_success('',$res);
    }

    /**
     * 出库操作
     * @param uid int 用户id
     * @param unique_sn string 订单号
     * @param ids array 可出库工序返回的id,json编码的一维数组
     */
    public function outOrder()
    {
        $uid = input('uid');
        $unique_sn = input('unique_sn');
        $id = json_decode(input('ids'),true);
        if(!is_array($id)){
            $this->_error('参数错误');
        }
        if(count($id) == 0){
            $this->_error('请选择出库工序');
        }
        foreach ($id as $k => $v) {
            Db::name('into_order_gx')->where('id',$v)->update(['is_out'=>1,'out_time'=>date('Y-m-d'),'out_uid'=>$uid]);
        }
        $order = Db::name('order')->where("unique_sn",$unique_sn)->find();
        $orderid = $order['id'];
        if($order['endstatus'] == 2){
            //如果此订单已全部入库,查询是否已经全部出库
            $find = Db::name('into_order_gx')->where('orderid',$order)->where('is_out',0)->select();
            $outstatus = count($find)==0?1:2;
            Db::name('order')->where("id",$orderid)->update(array('outstatus'=>$outstatus));//更新订单的状态为全部出库
        }else{
            Db::name('order')->where("id",$orderid)->update(array('outstatus'=>2));//更新订单的状态为部分出库
        }
        $this->_success('出库成功');
    }

    /**
     * 此用户所关联的工序  绑定的切割方案打印机 找不到打印机则不能进入页面
     * @param uid int 用户id
     */
    public function checkPrint()
    {
        $uid = input('uid');
//        $print = new \app\index\service\PrintYi();
//        $res = $print->getPrintStyle($uid, 2);//打印机列表
        $res = Db::name('print_style')->field('*,name as dname')->where('type',2)->select();//打印机列表
        //种类和角度下拉框
        $type = Db::name('print_cut')->group('name')->order('id desc')->select();
        $angle = Db::name('print_cut')->group('angle')->order('id desc')->select();
        if(!$res){
            $this->_error('未找到打印机');
        }
        $this->_success('',['print'=>$res,'type'=>$type,'angle'=>$angle]);
    }

    /**
     * 切割方案列表
     * @param name string 种类搜索字段
     * @param angle string 角度搜索字段
     * @param date string 日期搜索字段
     * @param import_date string 导入日期搜索字段
     */
    public function cutList()
    {
        $nameSearch = input('name');
        $angleSearch = input('angle');
        $dateSearch = input('date');
        $importSearch = input('import_date');

        if($nameSearch){
            $where[] = ['name','=',$nameSearch];
        }
        if($angleSearch){
            $where[] = ['angle','=',$angleSearch];
        }
        if($dateSearch){
            $date = strtotime($dateSearch);
            $where[] = ['date','>=',$date];
            $where[] = ['date','<=',$date+(24*3600-1)];
        }
        if($importSearch){
            $date = strtotime($importSearch);
            $where[] = ['addtime','>=',$date];
            $where[] = ['addtime','<=',$date+(24*3600-1)];
        }

        $where = isset($where)&&count($where)>0?$where:"id=0";
        $list = Db::name('print_cut')->where($where)->order('sort')->select();
        $this->_success('',$list);

    }

    /**
     * 发送打印指令 切割方案
     * @param print_id string json编码的切割方案id数组
     * @param printer_id string 打印机Id
     */
    public function print_cut()
    {
        $cutId = json_decode(input('print_id'),true);//勾选的切割方案id
        $printId = input('printer_id');  //打印机id

        if(!is_array($cutId) || count($cutId)==0){
            $this->_error('参数异常');
        }
        $print = new PrintYi();
        $print->setPrint($printId);
        $data = Db::name('print_cut')->alias('a')->field('a.name,a.size,b.unique_sn,b.color,b.uname,b.pname')
            ->join('order b','a.ordernum=b.unique_sn','left')
            ->whereIn('a.id', $cutId)
            ->order('sort')
            ->select();
        if(count($data)==0){
            $this->_error('导入的订单号与系统中订单号不匹配');
        }
        $res=$print->executeCut($data);
        if($res){
            $this->_success('打印成功');
        }
        $this->_error('打印异常');
    }
    
//员工提成列表
    public function tip_staff(){
        $uid = input('uid/d');
        $data = array();
        $gx_list = @include APP_DATA.'gx_list.php';
        $new_gx_list = array();
        foreach ($gx_list as $gx){
            $new_gx_list[$gx['id']] = $gx;
        }
        if (empty($uid)){
            echo json_encode(array('code'=>1,'msg'=>'参数缺失'));
            exit();
        }
        $tip_list = Db::name('flow_check')->where("uid=$uid and ispay=1")->order('id asc')->select();
        $tip_list_s = Db::name('into_order_gx')->where("uid=$uid and ispay=1")->order('id asc')->select();
        $tip_list = array_merge($tip_list,$tip_list_s);
        if ($tip_list){
            foreach ($tip_list as $tl){
                $list = array();
                $orderid = $tl['orderid'];
                $order_detail = order_attach($orderid);
                $list['id'] = $tl['id'];
                $list['ordernum'] = $order_detail['produce_no'];
                $list['gxname'] = $tl['orstatus']?$new_gx_list[$tl['orstatus']]['dname']:$tl['name'];
                $tl['orstatus']?$list['type']=0:$list['type']=1;
                $list['salary'] = $tl['salary'];
                $list['real_money'] = $tl['real_money'];
                array_push($data,$list);
            }
        }
        echo json_encode(array('code'=>0,'data'=>$data,'num'=>count($data)));
    }
    
    //员工提成确认
    public function agree_salary(){
        $id = input('id/a');
        $time = time();
        if (empty($id)){
            echo json_encode(array('code'=>1,'msg'=>'参数缺失'));
            exit();
        }
        $first_gx = array();
        $two_gx = array();
        foreach ($id as $ids){
            if ($ids['type']==0){
                array_push($first_gx,$ids['id']);
            }else {
                array_push($two_gx,$ids['id']);
            }
        }
        if (count($first_gx)>0){
            $update = Db::name('flow_check')->whereIn('id',implode(',',$first_gx))->update(array('ispay'=>2,'true_time'=>$time));
        }elseif (count($two_gx)>0){
            $update = Db::name('into_order_gx')->whereIn('id',implode(',',$two_gx))->update(array('ispay'=>2,'true_time'=>$time));
        }
        
        echo json_encode(array('code'=>0,'msg'=>'确认成功'));
    }
    
    
}