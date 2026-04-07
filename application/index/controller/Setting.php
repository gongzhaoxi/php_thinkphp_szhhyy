<?php
namespace app\index\controller;

use app\service\MatchingGxline;
use think\facade\Request;
use think\Db;
use think\db\Where;
use excel\Excel;

class Setting extends Super{
    
	//循环获取多级班组数组
	private $team;
	//全部的工艺路线doclass数据
	private $doclass;
	
    public function initialize(){
        parent::initialize();
        //角色
        //获取角色
        $yid = session('uid');
        $uid = session("gid");
        
        $role_li = Db::name('auth_group')->where("uid=$uid")->order("id asc")->select();
        if ($role_li){
            $this->assign('role_l',$role_li);
        }else {
            $this->assign('role_l',array());
        }
        
        //父级规则
        $main_rule = Db::name('auth_rule')->where("pid=0")->order('id asc')->select();
        if ($main_rule){
            $this->assign('mrule',$main_rule);
        }
        
        //所有规则
        //         获取规则
        $rule_li = Db::name('auth_rule')->where("pid=0")->order('id asc')->select();
        //二级目录
        if($rule_li){
            foreach ($rule_li as $k=>$q){
                $bid = $q['id'];
                //查询二级目录
                $se_rule = Db::name('auth_rule')->where("level=$bid")->order("sort asc")->select();
                $rule_li[$k]['second'] = $se_rule;
            }
            $this->assign('rule_li',$rule_li);
        }else {
            $this->assign('rule_li',array());
        }
    
        $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
    	if(isset($site_cache[PRO_DOMAIN])){
    		$this->assign("pro_setting",$site_cache[PRO_DOMAIN]);
    	}
    }
    public function setting(){
        //角色缓存
        $roles=@include_once APP_CACHE_DIR.'roles.php';
       
        $uid = session("uid");
        $cid = session('gid');
        
        $sql="del='0' ";
        $role=input("role",'0','intval');
        $uname=input("uname",'','ctrim');
        //用户角色
        if(!empty($role)){
        	$sql.=" and user_role='$role'";
        }
        //用户名
        if(!empty($uname)){
        	$sql.=" and uname='$uname'";
        }
        
        $this->assign("get",input(""));
        
        //获取班组缓存
        $teams=@include_once APP_DATA.'team_list.php';
        
        $user = Db::name("login")->where($sql)->order("id asc")->paginate(20,false,['query' => request()->param()]);
        $page = $user->render();
        $this->assign('page',$page);
        $user=$user->all();
        if ($user){ 
        	$groups=Db::name("auth_group")->field("id,title")->select();
        	$groupsList=array();
        	foreach($groups as $value){
        		$groupsList[$value['id']]=$value;
        	}
            //权限 
            foreach ($user as $k=>$bl){
                $roleid = $bl['role'];
                //所属角色名
                $role_name = isset($groupsList[$roleid])?$groupsList[$roleid]['title']:'';
                $user[$k]['role_name'] = $role_name;
                $user[$k]['team_name']='';
                $user[$k]['team_name']=$teams[$bl['tid']]['team_name'];
                //角色是否有离职可选,非客户都可以选离职
                $user[$k]['showDismission']=1;
               	if($bl['user_role']==4){
               		$user[$k]['showDismission']=0;
               	}
            }
            
            $this->assign("user",$user);
        }else {
            $this->assign("user",array());
        }
        
        //顶部右侧按钮
        $buttons=array(
        		array('label'=>'添加用户','class'=>'add-user')
        );
        
        //获取所有班组数组
        $this->team=array();
        $this->get_team('0');
        $this->assign("team",$this->team);
        
        $this->assign("buttons",$buttons);
        $this->assign("roles",$roles);

        return $this->fetch();
    }
    
    /**
     * 离职状态切换
     */
    public function change_dismission(){
    	$uid=intval(input("uid"));
    	$user=Db::name("login")->where("id='$uid'")->find();
    	if($user['dimission']!=1){
    		$dismision=1;
    		$msg='已离职';
    	}else{
    		$dismision=0;
    		$msg='离职';
    	}
    	$ok=Db::name("login")->where("id='$uid'")->update(array("dimission"=>$dismision));
    	if($ok){
    		$status=1;
    	}else{
    		$status=0;
    	}
    	return ['status'=>$status,'msg'=>$msg];
    }
    
    /**
     * 绑定客户和系列中间页
     */
    public function bind_middle()
    {
        $this->assign('id', input('id/d'));
        return $this->fetch();
    }
    
    /**
     * 绑定客户
     */
    public function bind_custom()
    {
        $uid = input('id');
        if(request()->isPost()){
            $custom = input('custom/a');            
            $res = Db::name('login')->where('id',$uid)->update(['custom' => implode(',', $custom)]);
            if($res !== false){
                return ['status' => 0];
            }
            
            return ['status' => 1];
        }
        //当前用户所绑定的客户
        $user = Db::name('login')->where('id',$uid)->find();
        $binded = explode(',', $user['custom']);        
        //从订单表里筛选客户名称
        $custom = Db::name('order')->field('uname')->distinct('uname')->select();   
        $this->assign('custom',$custom);
        $this->assign('uid',$uid);
        $this->assign('binded',$binded);
        
        return $this->fetch();
    }
    
    /**
     * 导入客户 新
     */
    public function import_custom()
    {
        $id = input('id/d');
        $file = $this->request->file('file');        
        if(!$file){
            $this->error('请先上传文件');
        }
        $info = $file->move( './uploads');
        $filePath = './uploads/' . $info->getSaveName();  
        $data = $this->read($filePath);
        unset($data[0]);        
        $list = [];
        foreach ($data as $key => $value) {
                $list[] = $value[0];
        }
        $customs = implode(',', $list);
        $res=Db::name('login')->where('id',$id)->update(['custom'=>$customs]);
        @unlink($filePath);
        if($res!==false){
            $this->success('导入成功');
        }
        $this->error('导入失败');
    }
    
    /**
     * 查看导入的客户
     */
    public function read_import_custom()
    {
        $uid = input('id');

        //当前用户所绑定的客户
        $user = Db::name('login')->where('id',$uid)->find();
        $binded = explode(',', $user['custom']);        

        $this->assign('uid',$uid);
        $this->assign('binded',$binded);
        
        return $this->fetch();
    }
    
    /**
     * 读取表格里的原始数据
     * @param $filePath 表格路径
     * @return array
     */
    public function read($filePath,$sheet=0)
    {
        if (!$filePath) {
            $errmsg = "请上传文件";
            $this->_error($errmsg);
        }

        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($filePath)) {
                    $errmsg = '未知的数据格式';
                    $this->error($errmsg);
                }
            }
        }

//        $PHPReader->setReadDataOnly(true); //忽略格式，只读取文本
        $PHPExcel = $PHPReader->load($filePath); //加载文件
        $currentSheet = $PHPExcel->getSheet($sheet);  //读取文件中的第一个工作表
        $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);

        $data = [];
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            $row = [];
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $row[] = is_null($val) ? '' : $val;
            }

            $data[] = $row;
        }
        unset($currentSheet);
        unset($PHPReader);
        unset($PHPExcel);
        return $data;
    }
    
    /**
     * 绑定系列
     */
    public function bind_series()
    {
        $uid = input('id');
        if(request()->isPost()){
            $series = input('series/a');            
            $res = Db::name('login')->where('id',$uid)->update(['series' => implode(',', $series)]);
            if($res !== false){
                return ['status' => 0];
            }
            
            return ['status' => 1];
        }
        //当前用户所绑定的客户
        $user = Db::name('login')->where('id',$uid)->find();
        $binded = explode(',', $user['series']);        
        //从订单表里筛选客户名称
        $series = Db::name('series')->where('pid',0)->select();   
        $this->assign('series',$series);
        $this->assign('uid',$uid);
        $this->assign('binded',$binded);
        
        return $this->fetch();
    }
    
    public function role_set(){
         //获取角色
        $uid = session('gid');
        $role_li = Db::name('auth_group')->where("uid=$uid")->order("id asc")->select();
        if ($role_li){
            //转为数组
            foreach($role_li as $key=>$vl){
                $role_li[$key]['rules'] = explode(",", $role_li[$key]['rules']);
                $xl = $role_li[$key]['rules'];
                $arr = array();
                for ($i=0;$i<count($xl);$i++){
                    $xl[$i] = intval($xl[$i]);
                    array_push($arr, $xl[$i]);
                }
                $role_li[$key]['rules'] = $arr;
            }
            $this->assign('role_li',$role_li);
        }else {
            $this->assign('role_li',array());
        }
  
        //顶部右侧按钮
        $buttons=array(
        		array('label'=>'添加权限分类','class'=>'add-user')
        );
        
        $this->assign("buttons",$buttons);
        
        return $this->fetch();
    }
    
    //工序分配
    public function allocation(){
        $uid = session("gid");
        
        $user = Db::name("login")->where("uid=$uid and del=0")->order("id asc")->select();
        if ($user){
            foreach ($user as $k=>$val){
                //用户负责工序
                $pid = $val['id'];
                $condition = array();
                $gx = Db::name('auth_gx')->where("pid=$pid")->find();
                $user[$k]['gx_l'] = array(); 
                if ($gx){ 
                   array_push($condition, $gx['gx_id']);
                
                   if (!empty($gx['gx_id'])){
                    $condition = implode(",", $condition);
                    //工序名
                    $gname_res = Db::name("gx_list")->distinct(true)->field("dname")
                                ->where("id in ($condition)")->select();
                    if ($gname_res){
                        $user[$k]['gx_l'] = $gname_res;
                    }
                    }else {
                        $user[$k]['gx_l'] = array();
                    }
                }
 
            }
            
            $this->assign('user',$user);  
        }
        return $this->fetch();
    }
    public function auth_set(){
//         获取规则
        $rule_li = Db::name('auth_rule')->where("pid=0")->order('id asc')->select();
        //二级目录
        if($rule_li){
        foreach ($rule_li as $k=>$q){
            $bid = $q['id'];
            //查询二级目录
            $se_rule = Db::name('auth_rule')->where("level=$bid")->order("sort asc")->select();
            $rule_li[$k]['second'] = $se_rule;
        }
        }
        $this->assign('rule',$rule_li);
        return $this->fetch();
    }
    
    //添加用户
    public function inuser(){
        $uid = session("uid");
        $arr = array();
        $userName = ctrim(input("param.user"));
        $pwd = ctrim(input("param.pwd"));
        $client_name= ctrim(input("param.client_name"));
        $user_role = intval(input("param.user_role"));
        $role_id = intval(input("param.role_id"));
        $tid = intval(input("param.tid"));
        $nologin = intval(input("param.nologin"));
        $atime = time();
        $pid = intval(input("param.id"));
        $pwds = md5($pwd);
        $wxmodel = input("wxmodel/s");
        $wxfunction = input("wxfunction/s");

        if (!empty($pid)){
            if (empty($pwd) || $pwd==null){
                $arr = ['role'=>$role_id];
            }else {
                $pwde = md5($pwd);
                $arr = ['role'=>$role_id,'password'=>$pwds];
            }
            $arr['tid']=$tid;
            $arr['user_role']=$user_role;
            $arr['client_name']=$client_name;
            $arr['nologin']=$nologin;
            $arr['wxmodel']=$wxmodel;
            $arr['wx_function_model']=$wxfunction;
            //更新
            $insert_user = Db::name('login')->where("id=$pid")->update($arr);
        }else {
            //用户名是否存在
            $exist = Db::name('login')->where("uname='$userName'")->find();
            if (!$exist){
                //uid是上级的意思
                $arr = ['uid'=>$uid,'uname'=>$userName,'password'=>$pwds,'role'=>$role_id,'user_role'=>$user_role,'client_name'=>$client_name,'tid'=>$tid,'nologin'=>$nologin,'addtime'=>$atime,'wxmodel'=>$wxmodel,'wx_function_model'=>$wxfunction];
            }else {
                echo json_encode(array('status'=>2,'msg'=>'用户名已存在'));
                exit();
            }
            //录入
            $insert_user = Db::name('login')->insertGetId($arr);
        }
        
        $userId = $pid!=0?$pid:$insert_user;
        if ($insert_user!==false){
            echo json_encode(array('status'=>1,'id'=>$userId));
        }
    }
    //添加角色
    public function inrole(){
        $uid = session('gid');
        $role_name = ctrim(input("param.rname"));
        $own_rule = input("param.groud");
        $pid = input("param.id");
        $arr = array();
        
        if (empty($role_name) || empty($own_rule) || !isset($own_rule)){
            $this->error('参数不全',"Setting/role_set");
            exit();
        }
        if (!empty($pid)){
            $role_in = Db::name('auth_group')->where("id=$pid")->update(array('rules'=>$own_rule,'title'=>$role_name));
        }else {
        $arr=['uid'=>$uid,'title'=>$role_name,'rules'=>$own_rule];
        /* 角色录入 */
        $role_in = DB::name('auth_group')->insert($arr);
        }
        if ($role_in){
           return ['status'=>1];
        }else {
            return ['status'=>0];
        }
    }
    //录入权限规则
    public function inrule(){
        if (Request::isAjax()){
            $path = ctrim(input("param.path"));
            $desc = ctrim(input("param.des"));
            $pid = intval(input("param.pid"));
            $cid = intval(input("param.id"));
            $jh = array();
            
            if ($pid==0){
                $jh = ['name'=>$path,'title'=>$desc,'pid'=>$pid,'level'=>$pid];
            }else {
                $jh = ['name'=>$path,'title'=>$desc,'pid'=>1,'level'=>$pid];
            }
            if (!empty($cid)){
                $re_lis = Db::name('auth_rule')->where("id=$cid")->update($jh);
            }else {
                $re_lis = Db::name('auth_rule')->insert($jh);
            }
            
            if ($re_lis){
                echo json_encode(array('status'=>1));
            }
        }
    }
    
    //修改用户信息
    public function change_user(){
        $uid = intval(input("param.id"));
        //查询用户信息
       
        $msg = Db::name('login')->where("id=$uid")->find();
        if ($msg){
            $this->assign('msg',$msg);
            $this->assign("wxmodel",explode(",",$msg['wxmodel']));
            $this->assign("wxfunction",explode(",",$msg['wx_function_model']));
        }
        
        //获取所有班组数组
        $this->team=array();
        $this->get_team('0');
        $this->assign("team",$this->team);
        
        //角色缓存
        $roles=@include_once APP_CACHE_DIR.'roles.php';
        $this->assign("roles",$roles);
        
        return $this->fetch();
       
    }
    
    //修改角色信息
    public function change_role(){
        $rid = intval(input("param.id"));
        //查询角色
        $roles = Db::name('auth_group')->where("id=$rid")->find();
        if ($roles){
            $this->assign('roles',$roles);
        }else {
            $this->assign('roles',array());
        }
        return $this->fetch();
    }
    
    //修改规则
    public function change_auth(){
        $aid = intval(input("param.id"));
        //查询规则
        $rules = Db::name('auth_rule')->where("id=$aid")->find();
        if ($rules){
            $this->assign('rls',$rules);
        }
        return $this->fetch();
    }
    
    public function change_gx(){
    	
    	//工序id
    	$gc = Db::name('doclass')->field('id,title,day,isnew,line_id,other_line,selfy')->where("isdel=0")->order("isnew desc,id asc")->select();
    	if ($gc!==false&&count($gc)>0){
    		$this->assign('xid',$gc);
    	}else{
    		$this->assign('xid',array());
    	}
    	
    	//工序分配
    	$gx_class = Db::name("gx_list")->where("1=1")->order('id asc')->select();
    	if ($gx_class){
    		foreach ($gx_class as $c=>$v){
    			$gx_class[$c]['dname'] = htmlspecialchars_decode($v['dname']);
    		}
    		$this->assign('gx_class',$gx_class);
    	}
    	
        $id = intval(input('param.uid'));
        
        $person = Db::name('login')->alias("a")->field("a.*,b.gx_id")
                  ->join("auth_gx b","b.pid=a.id","LEFT")->where("a.id=$id")->find();
        $person['gx_id'] = explode(",", $person['gx_id']);
        foreach ($person['gx_id'] as $key=>$vl){
            $person['gx_id'][$key] = intval($person['gx_id'][$key]);
        }
        if ($person){
            $this->assign('person',$person);
        }
        return $this->fetch();
    }
    public function ingx(){
        $uid = session("gid");
        $data = input("param.groud");
        $mid = intval(input('param.mid'));
        
        $data = json_decode($data);
        $adtime = time();
        //处理数据
        $li = implode(",", $data);
        //是否存在
        $decide = Db::name('auth_gx')->where("pid=$mid")->find();
        if ($decide){
//             $de_arr = explode(",", $decide['gx_id']);
//             $al_arr = array_merge($data,$de_arr);
//             $re_arr = array_unique($al_arr);
            //更新工序
            $in_gx = Db::name('auth_gx')->where("pid=$mid")->update(array('gx_id'=>$li,'addtime'=>$adtime));
        }else {
            $vl = array('pid'=>$mid,'gx_id'=>$li,'addtime'=>$adtime);
            $in_gx = Db::name('auth_gx')->insert($vl);    
        }
            
        if ($in_gx){
            echo json_encode(array('status'=>1,'arr'=>$data));
        }
        
    }
    
    public function del(){
        $uid = session('gid');
        $kid = intval(input("param.kid"));
        $kind = intval(input("param.kind"));
        $table_name = "";
        $where = "";
        $url = "";
        switch ($kind){
            case 0:
                $master = intval(input("param.mst"));
                if ($master==1){
                    return ['code'=>2,'msg'=>'管理员账号'];
                    exit();
                }else {
                    $table_name="login";
                    $where = "id=$kid";
                    $url = "Setting/setting";
                    $data_del = Db::name($table_name)->where($where)->update(array('del'=>1));
                    if ($data_del){
                        return ['code'=>0];
                    }
                    exit();
                }
                
                case 1:
                    $table_name="auth_gx";
                    $where = "pid=$kid";
                    $url = "Setting/allocation";
                    break;
                case 2:
                    $table_name="auth_group";
                    $where = "id=$kid";
                    $url = "Setting/role_set";
                    break;
                case 3:
                    $table_name="auth_rule";
                    $where = "id=$kid";
                    $url = "Setting/auth_set";
                    break;
                    case 4:
                        $table_name="series";
                        $where = "id=$kid";
                        $url = "Setting/series";
                        break;
                    default:
                        $table_name="";
                        break;
        }
        $data_del = Db::name($table_name)->where($where)->delete();
        if ($data_del){
            return ['code'=>0];
        }
    }
    
  	//旧版工序  ###################################################################################
    public function workpro(){
    	
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
        $uid = session('gid');
        //查找工序列表
        $result = Db::name('doclass')->field('id,title,isdel')->where("uid=$uid")->order('id asc')->select();
        if ($result){
            foreach ($result as $k=>$v){
                $id = $v['id'];
                $result[$k]['list'] = array();
                $res = Db::name('gx_list')->where("did=$id")->order('id asc')->select();
                if ($res){
                    $result[$k]['list'] = $res;
                }
                
                //查找是否有订单，有订单不可以修改和删除
               	$isedit=true;
                $order= Db::name('order')->field("id")->where("gid=$id")->find();
               	if($order!==false&&$order['id']>0){
               		$isedit=false;
               	}
               	$result[$k]['isedit']=$isedit;
            }
        }
        $this->assign('data',$result);
        
        //顶部右侧按钮
        $buttons=array(
        		array('label'=>'添加工艺路线','class'=>'addx')
        );
        
        $this->assign("buttons",$buttons);
        
        return $this->fetch();
    }
    
    //工序的启用、禁用
    public function warm(){
       $type=input("param.type");
       $id=input("param.id");
       if ($type==0){
           $arr = array('isdel'=>1);
       }else{
           $arr = array('isdel'=>0);
       }
       //更新
       $update = Db::name('doclass')->where("id=$id")->update($arr);
       if ($update){
           return [
               'code'=>0,
               'msg'=>'成功'
           ];
           
       }else {
           return [
               'code'=>1,
               'msg'=>'失败'
           ];
       }
    }
    //添加工序
    public function work_add(){
    	
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
    	$template="work_edit";
    	$this->assign("work_add",1);
    	return $this->fetch($template);
    }
    
    //工序修改
    public function work_edit(){
    	
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
        $id=intval(input("param.id"));
        $result=Db::name('doclass')->where("id=$id")->find();
        
        //查找是否已经下单,不能删除已有工序
        $order=Db::name('order')->where("gid=$id")->find();
        $editable=1;
        $template="work_edit";
        if($order!==false&&$order['id']>0){
        	$editable=2;
        	$template="work_edit_simple";
        }
        
        if ($result){
            $sel = Db::name('gx_list')->where("did=$id")->order('id asc,gid asc')->select();
            //查询所有的分组
            $gp=array();
            foreach($sel as $value){
            	$gp[]=$value['gid'];
            }
           
			$gp_list=array();
			if(count($gp)>0){
				$gp=array_unique($gp);
				$gp_sql=implode(",", $gp);
				$gp_list=Db::name("gx_group")->where("id in ($gp_sql)")->order("id asc")->select();
				foreach($gp_list as $value){
					$gp_list[$value['id']]=$value;
				}
			}
            $this->assign('gp_list',$gp_list);
            
            //将所有工序按分组、并列ID排列
            $gx_list=array();
            foreach($sel as $value){
            	$gx_list[$value['gid']][$value['rid']][]=$value;
            }

            $result['gl'] = $gx_list;
            $result['gx_count'] = count($sel);
            $this->assign('list',$result);
        }
        
        $this->assign('editable',$editable);
        return $this->fetch($template);
    }
    
    //工序编辑
    public function editgx(){
    	 
    	$uid = session('gid');
    	$gid = intval(input("param.id"));//工艺线ID
    	$day = intval(input('param.day'));//工艺线日期
    	$title = ctrim(input("param.title"));//工艺线名称
    	$dname=trim(input("param.dname"));//工艺线里面保存所有小工序的名称
    	$groups=$_POST['groups'];//分组的数组
    	$gxs=$_POST['gxs'];//小工序的数组
    
    
    	if ($gid==null || !isset($gid) || $gid==''){
    		return ['code'=>2,'msg'=>'工艺线ID参数不完整'];
    	}
    	 
    	$gb = Db::name('doclass')->where("id=$gid")->update(array('dname'=>$dname,'title'=>$title,'day'=>$day));
    	//修改订单的总周期
    	if($gb!==false&&$day>0){
    		//更新所有订单的周期
    		Db::name("order")->where("gid='$gid'")->update(array("day"=>$day));
    	}
    
    	$ok=false;
    	//修改所有组
    	if(!empty($groups)){
    		foreach($groups as $value){
    			$gid=$value['id'];
    			$name=$value['name'];
    			$state=$value['state'];
    			$ok=Db::name("gx_group")->where("id='$gid'")->update(array("name"=>$name,'inouts'=>$state));
    		}
    	}
    
    	//修改小工序
    	if(!empty($gxs)){
    		foreach($gxs as $value){
    			$id=$value['id'];
    			$name=$value['name'];
    			$work_value=$value['day'];//变成量值-以前是工时日
    			$work_unit=$value['unit'];//量值单位
    			$state=$value['state'];
    			$need_num=$value['need_num'];
    			$ok=Db::name("gx_list")->where("id='$id'")->update(array("dname"=>$name,'work_value'=>$work_value,'work_unit'=>$work_unit,'state'=>$state,'need_num'=>$need_num));
    		}
    	}
    
    	if($ok!==false){
    		//缓存
    		gx_cache();
    		return ['code'=>1,'msg'=>'成功'];
    	}else {
    		return ['code'=>2,'msg'=>'失败'];
    	}
    
    }
    
    //工序编辑2(未下订单可以重新删除添加)
    public function editgx2(){
    
    	$uid = session('gid');
    	$did = intval(input("param.id"));//工艺线ID
    	$day = intval(input('param.day'));//工艺线日期
    	$title = ctrim(input("param.title"));//工艺线名称
    	$groups=$_POST['groups'];//分组的数组
    	$addtime=time();//时间
    	$alldname=array();
    	 
    	if(empty($groups)||count($groups)<=0){
    		return ['code'=>2,'msg'=>'没有提交工序组等数据'];
    	}
    
    	/* if ($did==null || !isset($did) || $did==''){
    		return ['code'=>2,'msg'=>'工艺线ID参数不完整'];
    	} */
    	 
    	//查找订单防止已经有订单了，则阻止修改
    	//     	$order=Db::name('order')->where("gid=$did")->find();
    	//     	if($order!==false&&$order['id']>0){
    	//     		return ['code'=>2,'msg'=>'该工序已经有订单不能做修改'];
    	//     	}
    	
    	
    	
    	if(!empty($did)){
    		//删除所有的group 和colum gxlist等
    		$gp=Db::name('gx_group')->field("id")->where("did='$did'")->select();
    		$gpids=array();
    		foreach($gp as $value){
    			$gpids[]=$value['id'];
    		}
    		
    		//删除工序组
    		Db::name('gx_group')->where("did='$did'")->delete();
    		//删除colum
    		if(count($gpids)>0){
    			Db::name('gx_cloum')->where("gid in (".implode(",",$gpids).")")->delete();
    		}
    		//删除小工序
    		Db::name('gx_list')->where("did='$did'")->delete();
    	}else{
				// 新增加一条工序路线
			$gxnames = array ();
			foreach ( $groups as $value ) {
				$blgx = $value ['gxs']; // 并列工序
				foreach ( $blgx as $k => $gxs ) {
					foreach ( $gxs as $gval ) {
						// 记录所有的工序名
						$dname = $gval ['name'];
						$gxnames [] = trim ( $dname );
					}
				}
			}
			
			$gxnames = implode ( ",", $gxnames );
			$did = Db::name ( 'doclass' )->insertGetId ( array (
					'dname' => $gxnames,
					'uid'=>$uid,
					'title' => $title,
					'day' => $day,
					'addtime'=>$addtime
			) );
			if ($did === false || empty ( $did )) {
				return [ 
						'code' => 2,
						'msg' => '新建工序失败，请重试' 
				];
			}
    		
    		
    	}
    	
    	
    	 
    	$ok=false;
    	//修改所有组
    	if(!empty($groups)){
    		foreach($groups as $value){
    			$name=$value['name'];
    			$state=$value['state'];
    			if(empty($name)){
    				continue;
    			}
    			//获取组ID
    			$groupid = Db::name('gx_group')->insertGetId(array('did'=>$did,'name'=>$name,'inouts'=>$state));
    			if(empty($value['gxs'])){
    				continue;
    			}
    			$blgx=$value['gxs'];//并列工序
    			foreach($blgx as $k=>$gxs){
    				$rid = Db::name('gx_cloum')->insertGetId(array('gid'=>$groupid));
    				foreach($gxs as $gval){
    					if(empty($gval['name'])){
    						continue;
    					}
    					$dname=$gval['name'];
    					$work_value=$gval['day'];//变成量值-以前是工时日
    					$work_unit=$gval['unit'];//量值单位
    					$gxstate=$gval['state'];
    					$gxneed_num=intval($gval['need_num']);
    					$insert_arr = array();
    					$insert_arr = ['did'=>$did,'cid'=>$uid,'rid'=>$rid,'gid'=>$groupid,'dname'=>htmlspecialchars($dname),'work_value'=>intval($work_value),'work_unit'=>intval($work_unit),'state'=>intval($gxstate),'need_num'=>$gxneed_num,'addtime'=>$addtime];
    					$gx_ins = Db::name('gx_list')->insert($insert_arr);
    					//存储工序名
    					$alldname[]=trim($dname);
    				}
    			}
    
    
    		}
    	}
    
    	$alldname=implode(",", $alldname);
    	$ok = Db::name('doclass')->where("id='$did'")->update(array('dname'=>$alldname,'title'=>$title,'day'=>$day));
    	 
    	//修改订单的总周期
    	if($ok!==false&&$day>0){
    		//更新所有订单的周期
    		Db::name("order")->where("gid='$did'")->update(array("day"=>$day));
    	}
    
    	if($ok!==false){
    		//缓存
    		gx_cache();
    		return ['code'=>1,'msg'=>'成功'];
    	}else {
    		return ['code'=>2,'msg'=>'失败'];
    	}
    
    }
    
    //工序删除
    public function work_del(){
    	$did=intval(input("param.id"));
    	$order= Db::name('order')->field("id")->where("gid='$did'")->find();
    	if($order!==false&&$order['id']>0){
    		return ['status'=>'2','msg'=>'该工序下已有订单数据不能删除'];
    	}
    	
    	//删除工序
    	Db::name('doclass')->where("id='$did'")->delete();
    	$groups=Db::name('gx_group')->where("did='$did'")->select();
    	$del_gp=array();
    	if($groups!==false&&count($groups)>0){
    		foreach($groups as $val){
    			$del_gp[]=$val['id'];
    		}
    	}
    	//删除工序下面的组
    	Db::name('gx_group')->where("did='$did'")->delete();
    	if(count($del_gp)>0){
    		$del_sql=implode(",",$del_gp);
    		Db::name('gx_cloum')->where("gid in ($del_sql) ")->delete();
    	}
    	//删除每一个细工序
    	Db::name('gx_list')->where("did='$did'")->delete();
    	
    	//缓存
    	gx_cache();
    	
    	return ['status'=>'1','msg'=>'工序删除成功'];
    }
    //旧版工序  ###################################################################################
    
    //系列设置
    public function series(){
        if (Request::instance()->isAjax()){
            $name = ctrim(input("param.title"));
            $type = intval(input("param.type"));
            
            if (empty($name) || empty($type)){
                return ['code'=>1];
                exit();
            }
            if ($type==-1){
                $arr = array('xname'=>$name,'addtime'=>time());
            }else{
                $gid = intval(input('gid'));
                $arr = array('xname'=>$name,'type'=>1,'pid'=>$type,'gid'=>$gid,'addtime'=>time());
            }
            $indata = Db::name('series')->insert($arr);
            if ($indata){
                return ['code'=>0];
            }
            exit();
        }
        
        //工序id
        $gc = Db::name('doclass')->field('id,title,day,isnew,line_id,other_line,selfy')->where("isdel=0")->order("isnew desc,id asc")->select();
        if ($gc!==false&&count($gc)>0){
        	$this->assign('xid',$gc);
        }else{
        	$this->assign('xid',array());
        }
        
        //父级系列
        $fseries = Db::name('series')->where("type=0")->select();
        if ($fseries){
        	$this->assign('flis',$fseries);
        }else{
        	$this->assign('flis',array());
        }
        
        $where="";
        $keyword=input("keyword");
        if(!empty($keyword)){
        	//搜索一级或者二级有相关关键词的系列， 并获取pid
        	$list=Db::name('series')->where("xname like '%$keyword%'")->select();
        	$ids=array();
        	if(count($list)>0){
        		foreach($list as $value){
        			if($value['pid']<=0){
        				$ids[]=$value['id'];
        			}else{
        				$ids[]=$value['pid'];
        			}
        		}
        	}else{
        		$ids[]='0';
        	}
        	if(count($ids)>0){
        		$where.=" and id in (".implode(",",$ids).")";
        	}
        	$this->assign("get",input(""));
        }
        //获取系列
        $result = Db::name('series')->where("type='0' $where")->order('id asc')->paginate(20,true,['query' => request()->param()]);
        $page = $result->render();
        $result=$result->all();
        if ($result){
            foreach ($result as $key=>$res){
                $pid = $res['id'];
                $result[$key]['second'] = '';
                $obj = Db::name('series')->alias('a')->field('a.*,b.title')
                        ->join('doclass b','b.id=a.gid','LEFT')->where("pid=$pid")->order('a.id asc')->select();
                if ($obj){
                    $result[$key]['second'] = $obj;
                }
            }
            $this->assign('list',$result);
        }else {
            $this->assign('list',array());
        }
        
        //顶部右侧按钮
        $buttons=array(
        		array('label'=>'添加物料系列','class'=>'add-user')
        );
        
        $this->assign("buttons",$buttons);
        $this->assign('page',$page);
        
        return $this->fetch();
    }
    
    public function series_edit(){
        $id = intval(input("param.id"));
        if (Request::instance()->isAjax()){
            $name = ctrim(input("param.title"));
            $type = intval(input('type'));
            
            if (empty($name)){
                return ['code'=>1];
                exit();
            }
            $arr = array();
            if ($type==0){
                $arr = array('xname'=>$name);
            }else {
                $gid = intval(input("param.gid"));
                $arr = array('xname'=>$name,'gid'=>$gid);
            }
            $updata = Db::name('series')->where("id=$id")->update($arr);
            return ['code'=>0];
        }else {
        	//工序id
        	$gc = Db::name('doclass')->field('id,title,day,isnew,line_id,other_line,selfy')->where("isdel=0")->order("isnew desc,id asc")->select();
        	if ($gc!==false&&count($gc)>0){
        		$this->assign('xid',$gc);
        	}else{
        		$this->assign('xid',array());
        	}
            $result = Db::name('series')->where("id=$id")->find();
            $this->assign('list',$result);
        }
        return $this->fetch();
    }
    
    //重新获取某一个系列
    public function serirs_get(){
    	$id = intval(input("param.id"));
    	$result = Db::name('series')->where("id=$id")->find();
    	//判断是否一级系列，不是一级则获取对应工序
    	if($result===false||$result['id']<=0){
    		return ['status'=>'2','msg'=>'系列不存在'];
    	}
    	$result['parent']='';
    	$result['gxname']='';
    	if($result['pid']>0){
    		$parent=Db::name('series')->where("id='{$result['pid']}'")->find();
    		if($parent!==false&&!empty($parent['xname'])){
    			$result['parent']=$parent['xname'];
    		}
    	}
    	
    	//查找工序
    	$result['gxname']='';
    	if($result['gid']>0){
    		$gx=Db::name('doclass')->where("id='{$result['gid']}'")->find();
    		if($gx!==false&&!empty($gx['title'])){
    			$result['gxname']=$gx['title'];
    		}
    	}
    	
    	return ['status'=>'1','data'=>$result];
    }
    
    //循环获取下级分类
    private function copy_childSeries($pid,$newPid){
    	$childs=Db::name('series')->where("pid=$pid")->select();
    	if($childs!==false&&count($childs)>0){
    		foreach($childs as $key=>$value){
    			$id=$value['id'];
    			unset($value['id']);
    			$value['pid']=$newPid;//更新新的ID
    			Db::name('series')->insert($value);
    			$newId = Db::name('series')->getLastInsID();
    			$this->copy_childSeries($id,$newId);
    		}
    	}
    	return;
    }
    
    //复制分类及其下级分类
    public function copy_series(){
    	$id = intval(input("param.id"));
    	$this->series_childs=array();
    	$series=Db::name('series')->where("id=$id")->find();
    	
    	if($series===false||empty($series['id'])){
    		return ['status'=>'2','msg'=>'系列不存在'];
    	}
    	
    	unset($series['id']);
    	Db::name('series')->insert($series);
    	$newId = Db::name('series')->getLastInsID();
    	
    	//获取下级分类，并复制记录，更新父pid
    	$this->copy_childSeries($id,$newId);
    	
    	return ['status'=>'1','msg'=>'复制成功'];
    }
    
    //测试导入
    public function import_series_test(){
    	return $this->fetch();
    }
    
    //执行导入
    public function doseries_import_test(){
    	 
    	if (!isset ( $_FILES ['import'] ) || ($_FILES ['import'] ['error'] != 0)) {
    		exit("请上传导入文件");
    	}
    	 
    	$result = $this->use_phpexcel ( $_FILES ["import"] ["tmp_name"] );
    	 
    	$uid=session("uid");
    	$user=Db::name('login')->where("id='$uid'")->find();
    	$gid=$user['uid'];//上级
    	 
    	$allSheetContent=$result["data"];
    	$now=time();
    	$success_num=0;
    	for($s=0;$s<count($allSheetContent);$s++){
    		$execl_data = $allSheetContent[$s]["Content"];
    		unset ( $execl_data [1] );//表头标题，不是数据
    		 
    		foreach ( $execl_data as $k => $v ) {
    
    			foreach ( $v as $key => $value ) { 						//删除空值，如果不处理会无法插入
    				if ($value == "") {
    					$v [$key] = "";
    				}
    				$v [$key] = trim ( $value );
    			}
    			 
    			$column=count($v);//列数
    			$end_column=$column-1;
    			 
    			//判断是否双数
    			if($column%2!=0){
    				exit("列数不为双数，数据匹配会错误");
    			}
    			 
    			$name=$v[0];//系列名称
    			$other=$v[$end_column];//其他可用工艺线
    			 
    			$series_id=$this->make_series($name,true);
    			 
    			$d_groups=($column-2)/2;
    			 
    			for($i=0;$i<$d_groups;$i++){
    
    				$name_index=$i*2+1;
    				$line_index=$i*2+2;
    				 
    				$do_name=$v[$name_index];
    				$do_line_id=explode(",",str_replace("，", ",", $v[$line_index]));
    				 
    				if($do_name==''||count($do_line_id)<=0){
    					echo $do_name."空"."<br/>";
    					continue;
    				}
    				 
    				
    			}
    			 
    			 
    			$success_num++;
    			 
    		}
    	}
    	 
    	echo $success_num;
    	exit();
    
    	 
    }
    
    //返回系列
    private function make_series($name,$debug=false){
    	$now=time();
    	$exist=M("series")->where("xname='$name'")->find();
    	if($exist!==false&&$exist['id']>0){
    		$series_id=$exist['id'];
    		if($debug){
    			echo "".$name."<br/>";
    		}
    	}else{
    		$new=array();
    		$new['xname']=$name;
    		$new['gid']='0';
    		$new['pid']='0';
    		$new['isnew']='1';
    		$new['selfy']='0';
    		$new['addtime']=$now;
    		$series_id=Db::name('series')->insertGetId($new);
    	}
    	
    	return $series_id;
    }
    //导出模板
    public function downloads(){
        $titles = array('series_name'=>'物料名称','genre_id'=>'类别id');
        $lists = array(['series_name'=>'平开窗','genre_id'=>1]);
        $site_cache=@include (APP_CACHE_DIR.'site_cache.php');
        $creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
        $exceltitle='物料导入模板';
         
        $this->one_export($titles, $lists, $creator, $exceltitle);
    }
    //显示导入
    //导入系列有关系的表:bg_series、bg_doclass
    public function import_series(){
    	return $this->fetch();
    }
    
    //执行导入
    public function doseries_import(){
    	
    	if (!isset ( $_FILES ['excel'] ) || ($_FILES ['excel'] ['error'] != 0)) {
    		exit("请上传导入文件");
    	}
    	
    	$result = $this->use_phpexcel ( $_FILES ["excel"] ["tmp_name"] );
    	
    	$uid=session("uid");
    	$user=Db::name('login')->where("id='$uid'")->find();
    	$gid=$user['uid'];//上级
    	
    	$allSheetContent=$result["data"];
    	$now=time();
    	$success_num=0;
    	$title=['xname','gid'];
    	
    	for($s=0;$s<count($allSheetContent);$s++){
        	$execl_data = $allSheetContent[$s]["Content"];
        	unset ( $execl_data [1] );//表头标题，不是数据
        	$data = array();
        	foreach ($execl_data as $k => $v){
        	    $list = array();
        	    foreach ($v as $key=>$value){
        	        if (empty($value)){
        	            $list[$title[$key]] = '';
        	        }
        	        $list[$title[$key]] = trim($value);
        	    }
        	    array_push($data,$list);
        	}
        	$result_ins = Db::name('series')->insertAll($data);
    	}
    	$this->success('导入成功','import_series'); 	
    }
    
    //导出物料系列和路线
    public function export_series(){
    	//查询所有的新版的系列
    	$series=M("series")->where("isnew='1'")->order("id asc")->select();
    	//查询所有的doclass
    	$doclass=M("doclass")
    			->field("id,series_id,title,day,line_id,other_line")
    			->where("`series_id` > 0")
    			->order("id asc")
    			->select();
    	if(!$series){
    		echo '请设置物料';
    		exit();
    	}
    
    	
    	//物料=>多条工艺路线
    	$list=array();
    	
    	foreach($series as $key=>$value){
    		$series_id=$value['id'];
    		$t=array();
    		$t['xname']=$value['xname'];//系列名称
    		$t['doclass']=array();
    		$t['other_line']=array();
    		foreach($doclass as $dval){
    			if($dval['series_id']==$series_id){
    				$t['doclass'][]=$dval;
    				if($dval['other_line']!=''){
    					$other_line=explode(",",$dval['other_line']);
    					$t['other_line']=array_merge($t['other_line'],$other_line);
    				}
    			}
    		}
    		$t['other_line']=implode(",",array_unique($t['other_line']));
    		$list[$series_id]=$t;
    	}
    	
    	//遍历找出每个系列下面，doclass工艺路线最多的
    	//每组就是:默认工艺线名称 工艺线ID,用英文逗号连接,总周期（天）
    	$max_groups=0;
    	foreach($list as $id=>$value){
    		$doclass_num=count($value['doclass']);
    		if($doclass_num>$max_groups){
    			$max_groups=$doclass_num;
    		}
    	}
    	
    	//再一次根据最多的组，定出表头
    	$title=array("id"=>'系列ID',"xname"=>'物料/系列名称');
    	$title_group=array();
    	for($i=0;$i<$max_groups;$i++){
    		$gindex=$i+1;
    		$title_group["name_".$gindex]="默认工艺线名称".$gindex;
    		$title_group["lineid_".$gindex]="工艺线ID,用英文逗号连接";
    		$title_group["day_".$gindex]="总周期（天）";
    	}
    	
    	$title=array_merge($title,$title_group);
    	$title['other_line']="其他所属物料工艺ID,用英文逗号连接";

    	//最终的导出的数组
    	$export_list=array();
    	
    	foreach ($list as $id=>$value){
    		$t=array();
    		$t['id']=$id;
    		$t['xname']=$value['xname'];
    		$doclass=$value['doclass'];
    		//根据$title_group设置数据
    		foreach($doclass as $index=>$val){
    			$gindex=$index+1;
    			$t['name_'.$gindex]=$val['title'];
    			$t['lineid_'.$gindex]=$val['line_id'];
    			$t['day_'.$gindex]=$val['day'];
    		}
    		$t['other_line']=$value['other_line'];
    		$export_list[]=$t;
    	}
    	
    	$creator=$site_cache[PRO_DOMAIN]['sitename'];//excel作者用站点名称
    	$exceltitle='物料系列导入模板';
    	$this->one_export($title, $export_list,$creator,$exceltitle);
    }
    
    
    
    //新版工艺线开始  ###################################################################################
    //新版工艺线包含表
    //doclass\gx_group\gx_line\gx_list
    
    //$line_id是工艺线gx_line的 id字段值
    //判断工艺线是否已下单-已下单返回true
    private function isordered($line_id){
    	
    	$dids=Db::name('doclass')->where("FIND_IN_SET($line_id,line_id)")->order('id asc')->column("id");
    	
    	if(count($dids)>0){
    		$sql=implode(",",$dids);
    		$order= Db::name('order')->field("id")->where("gid in ($sql)")->find();
    		if($order!==false&&$order['id']>0){
    			return true;
    		}
    	}
    	
    	return false;
    }
    
    public function gx_list(){
        if(request()->ispost()){
            $id = input('id/a');
            $sort = input('sort/a');

            //更新排序
            foreach ($id as $k => $v) {
                Db::name('gx_line')->where('id',$v)->update(['sort'=>$sort[$k]]);
            }
            $this->success('修改成功');
            return;
        }


    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	if (GLASS_PLAN==0){
    	    $where = "id>'2'";
    	}
    	//查找工序列表
    	$result = Db::name('gx_line')->field('id,title,isdel,sort')->where($where)->order('sort asc')->select();
    	if ($result){
    		foreach ($result as $k=>$v){
    			$id = $v['id'];
    			$result[$k]['list'] = array();
    			$res = Db::name('gx_list')->where("lid=$id")->order('orderby asc,id asc')->select();
    			if ($res){
    				$result[$k]['list'] = $res;
    			}
    			
    			//查找是否有订单，有订单不可以修改和删除
    			$isedit=true;
    			
    			if($v['id']!=''&&$v['id']>0){
    				//查找有使用过该工艺线合并的总路线
    				$isordered=$this->isordered($v['id']);
    				$isedit=!$isordered;
    			}
    			
    			$result[$k]['isedit']=$isedit;
    		}
    	}
    	$this->assign('data',$result);
    
    	//顶部右侧按钮
    	$buttons=array(
    			array('label'=>'添加工艺路线','class'=>'addx'),
    			array('label'=>'工序顺序设置','class'=>'gxorder'),
                array('label'=>'工序组设置','class'=>'gxgroup')
    	);
    	
    	if(isset($this->system['reportorder'])&&$this->system['reportorder']==1){
    		$buttons[]=array('label'=>'固定流水工序','class'=>'fix_gx');
    	}
    
    	$this->assign("buttons",$buttons);
    
    	return $this->fetch();
    }

    /**
     * 工序组设置
     */
    public function gx_group()
    {
        if(request()->ispost()){
            $data = input('post.group_name/a');
            $inouts = input('post.inouts/a');
            $order = input('post.order/a');
            $list = [];
            $i = 1;
            foreach ($data as $k => $v) {
                $list[$i] = ['name'=>$v,'inouts'=>$inouts[$k],'order'=>$order[$k]];
                $i++;
            }
            cache_write('gx_group','gx_group',$list);
            $this->_success('设置成功');
            return;
        }
        $list = include APP_DATA.'gx_group.php';
        $this->assign('list',$list);
        return $this->fetch();
    }
    
    //工艺线的启用、禁用
    public function gx_disable(){
    	$type=input("param.type");
    	$id=input("param.id");
    	if ($type==0){
    		$arr = array('isdel'=>1);
    	}else{
    		$arr = array('isdel'=>0);
    	}
    	//更新
    	$update = Db::name('gx_line')->where("id=$id")->update($arr);
    	if ($update){
    		return [
    				'code'=>0,
    				'msg'=>'成功'
    		];
    		 
    	}else {
    		return [
    				'code'=>1,
    				'msg'=>'失败'
    		];
    	}
    }
    //添加工序
    public function gx_add(){
    
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
    	//固定工序组缓存
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	$this->assign("gx_group",$gx_group);
    
    	$template="gx_edit";
    	$this->assign("work_add",1);
    	return $this->fetch($template);
    }
    
    //工序修改
    public function gx_edit(){
    
    	//产能单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	//固定工序组缓存
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	$this->assign("gx_group",$gx_group);
    
    	$id=intval(input("param.id"));
    	$result=Db::name('gx_line')->where("id=$id")->find();
    
    	//查找是否已经下单,不能删除已有工序
    	$isordered=$this->isordered($id);
    	$editable=1;
    	$template="gx_edit";
    	//下过单就不能删除，只能做简单修改
    	if($isordered){
    		$editable=2;
    		$template="gx_edit_simple";
    	}
    
    	if ($result!==false){
    		$gplist=Db::name('gx_group')->field("id,cache_id")->where("lid=$id")->order('id asc')->select();
    		$groups=array();
    		foreach($gplist as $value){
    			$groups[$value['id']]=$value['cache_id'];
    		}
    		
    		$gx_list = Db::name('gx_list')->where("lid=$id")->order('orderby asc,id asc')->select();
    		
    		//转换组-将$gx_list的gid转换成缓存里面的键
    		foreach($gx_list as $key=>$value){
    			$gx_list[$key]['gid']=$groups[$value['gid']];
    		}
    		
    		$result['gx_list'] = $gx_list;
    		$result['gx_count'] = count($gx_list);
    		$this->assign('list',$result);
    	}
    	
    	$this->assign('editable',$editable);
    	return $this->fetch($template);
    }
    
    //工序编辑-已有订单不能修改组页面
    public function save_gx(){
    
    	$uid = session('uid');
    	$lid = intval(input("param.id"));//工艺线ID gx_line表
    	$day = intval(input('param.day'));//工艺线日期
    	$title = ctrim(input("param.title"));//工艺线名称
    	$dname=trim(input("param.dname"));//工艺线里面保存所有小工序的名称
    	//$groups=$_POST['groups'];//分组的数组
    	$gxs=input("gxs/a");//小工序的数组
    
    	if ($lid==null || !isset($lid) || $lid==''){
    		return ['code'=>2,'msg'=>'工艺线ID参数不完整'];
    	}
    	
    	//只可以修改名称
    	$gb = Db::name('gx_line')->where("id=$lid")->update(array('title'=>$title));
    
    	//固定工序组缓存
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	//查询每条工艺线的组
    	$gp_list=Db::name('gx_group')->where("lid='$lid'")->select();
    	
    	$new_groups=array();//存储新ID
    	foreach($gp_list as $key=>$value){
    		$cache_id=$value['cache_id'];
    		$new_groups[$cache_id]=$value['id'];
    	}
    	
    	$ok=false;
    	//修改小工序
    	if(!empty($gxs)){
    		foreach($gxs as $value){
    			$id=$value['id'];
    			$name=$value['name'];
    			$work_value=$value['day'];//变成量值-以前是工时日
    			$work_unit=$value['unit'];//量值单位
    			$state=$value['state'];//报开始和结束
    			$group=$new_groups[$value['group']];//组
    			$need_num=$value['need_num'];
    			$in=array('cid'=>$uid,"dname"=>$name,'work_value'=>$work_value,'gid'=>$group,'lid'=>$lid
    					,'work_unit'=>$work_unit,'state'=>$state
    					,'need_num'=>$need_num);
    			if(!empty($id)){
    				$ok=Db::name("gx_list")->where("id='$id'")->update($in);
    			}else{
    				$ok=Db::name("gx_list")->insert($in);
    			}
    			
    		}
    	}
    
    	if($ok!==false){
    		//缓存
    		gx_cache();
    		return ['code'=>1,'msg'=>'成功'];
    	}else {
    		return ['code'=>2,'msg'=>'失败'];
    	}
    
    }
    
    //工序编辑2(未下订单可以重新删除添加)
    public function save_gx2(){
    
    	$uid = session('uid');
    	$gid=session('gid');
    	$lid = intval(input("param.id"));//工艺线ID
    	$day = intval(input('param.day'));//工艺线日期
    	$title = ctrim(input("param.title"));//工艺线名称
    	$groups=$_POST['groups'];//分组的数组
    	$addtime=time();//时间
    	$alldname=array();
    
    	if(empty($groups)||count($groups)<=0){
    		return ['code'=>2,'msg'=>'没有提交工序组等数据'];
    	}
		
    	//寄存原有的该工艺线下面的所有小工序的id
    	$line_gx_id=array();
    	$input_gx_id=array();//客户修改后提交的小工序的ID
    	if(!empty($lid)){
    		//删除工序组
    		Db::name('gx_group')->where("lid='$lid'")->delete();
    		//删除旧的不在新添加或修改小工序内的小工序
    		if(!empty($groups)){
    			//查找该工艺线下面的工序
    			$line_gx=M("gx_list")->where("lid='$lid'")->select();
    			foreach($line_gx as $val){
    				$line_gx_id[]=$val['id'];
    			}
    			foreach($groups as $gxs){
	    			foreach($gxs as $gval){
	    				$input_gx_id[]=$gval['id'];
	    			}
    			}
    			$input_gx_id=array_unique($input_gx_id);
    			//判断以前的工序id不在新的工序id里面就删除
    			foreach($line_gx_id as $gxid){
    				if(!in_array($gxid, $input_gx_id)){
    					M("gx_list")->where("id='$gxid'")->delete();
    				}
    			}
    		}
    		Db::name ( 'gx_line' )->where("id='$lid'")->update(array('title'=>$title,'day'=>$day));
    	}else{
    		// 新增加一条工序路线
    		$lid = Db::name ( 'gx_line' )->insertGetId ( array (
    				'uid'=>$uid,
    				'title' => $title,
    				'day' => $day,
    				'addtime'=>$addtime
    		) );
    		if ($lid === false || empty ( $lid )) {
    			return [
    					'code' => 2,
    					'msg' => '新建工序失败，请重试'
    			];
    		}
    	}
    
    
    	//固定工序组缓存
    	$gx_group=@include_once APP_DATA.'gx_group.php';
    	
    	$ok=false;
    	//修改所有组
    	if(!empty($groups)){
    		
    		//将缓存gx_group.php内的所有组添加到数据库内，每一个工艺线都如此
    		//区别在于-每条工艺线的组就固定是缓存内的组别数量，用isnew 和cache_id区分
    		$new_groups=array();//存储新ID
    		foreach($gx_group as $key=>$value){
    			$data=array('lid'=>$lid,'name'=>$value['name'],'inouts'=>$value['inouts'],'isnew'=>'1','cache_id'=>$key);
    			$groupid = Db::name('gx_group')->insertGetId($data);
    			$new_groups[$key]=$groupid;
    		}
    		
    		//$groups 这个只是post数据组，并不是分组的意思
    		foreach($groups as $gxs){
    				foreach($gxs as $gval){
    					if(empty($gval['name'])){
    						continue;
    					}
    					$gxid=$gval['id'];//新增的值为0，修改有值
    					$dname=$gval['name'];
    					$work_time=$gval['worktime'];//制作天数
    					$work_value=$gval['day'];//变成量值-以前是工时日
    					$work_unit=$gval['unit'];//量值单位
    					$gxstate=$gval['state'];//报开始和结束
    					$group=$new_groups[$gval['group']];//组-新组的id
    					$gxneed_num=intval($gval['need_num']);//数量
    					if(empty($gxid)||$gxid<=0){
    						$insert_arr = array('cid'=>$uid,'gid'=>$group,'lid'=>$lid
    								,'dname'=>htmlspecialchars($dname),'work_value'=>intval($work_value)
    								,'work_unit'=>intval($work_unit),'state'=>intval($gxstate),'need_num'=>$gxneed_num
    								,'addtime'=>$addtime,'worktime'=>intval($work_time)
    						);
    						$gx_ins = Db::name('gx_list')->insert($insert_arr);
    					}else{
    						$update_arr = array('cid'=>$uid,'gid'=>$group,'lid'=>$lid
    								,'dname'=>htmlspecialchars($dname),'work_value'=>intval($work_value)
    								,'work_unit'=>intval($work_unit),'state'=>intval($gxstate),'need_num'=>$gxneed_num
    								,'addtime'=>$addtime,'worktime'=>intval($work_time)
    						);
    								 Db::name('gx_list')->where("id='$gxid'")->update($update_arr);
    					}
    					
    					//存储工序名
    					$alldname[]=trim($dname);
    				}//end of foreach
    		}//end of foreach
    	}
    
    	//更新上级doclass表工艺路线的工序名和根据工序排序排列
    	$doclass=Db::name('doclass')->where("FIND_IN_SET($lid,line_id)")->order('id asc')->select();
    	foreach($doclass as $value){
    		$did=$value['id'];
    		$line_id=explode(",", $value['line_id']);
    		$combine=combine_line_gx($line_id);
    		$dname=implode(",",$combine);//新的工序路线
    		
    		$days=0;//总周期
    		if(count($line_id)>0){
    			//汇总所有工艺线的周期
    			$days=Db::name("gx_line")->where("id in (".implode(",",$line_id).")")->sum("day");
    		}
    		
    		$doclass=array('uid'=>$uid,'gid'=>$gid,'dname'=>$dname,"day"=>$days);
			Db::name("doclass")->where("id='$did'")->update($doclass);
    	}
    	
    	$ok=true;
    	
    	if($ok!==false){
    		//缓存
    		gx_cache();
    		return ['code'=>1,'msg'=>'成功'];
    	}else {
    		return ['code'=>2,'msg'=>'失败'];
    	}
    
    }
    
    //工序删除
    public function gx_del(){
    	$lid=intval(input("param.id"));
    	
    	$isordered=$this->isordered($lid);
    	if(in_array($lid,[1,2])){
            return ['status'=>'2','msg'=>'系统必须工艺线，不能删除'];
        }

    	if($isordered){
    		return ['status'=>'2','msg'=>'该工序下已有订单数据不能删除'];
    	}
    	
    	//如果已经绑定物料则不能删除
    	$doclass=Db::name('doclass')->where("FIND_IN_SET($lid,line_id) or FIND_IN_SET($lid,other_line)")->find();
    	if($doclass!==false&&$doclass['id']>0){
    		return ['status'=>'2','msg'=>'该工序已绑定物料，请先删除物料'];
    	}
    
    	//删除工序
    	Db::name('gx_line')->where("id='$lid'")->delete();
    	
    	//删除工序下面的组
    	Db::name('gx_group')->where("lid='$lid'")->delete();
    	
    	//删除每一个细工序
    	Db::name('gx_list')->where("lid='$lid'")->delete();
    
    	//缓存
    	gx_cache();
    
    	return ['status'=>'1','msg'=>'工序删除成功'];
    }
    
    //工序顺序调整
    public function gx_order(){
    	//所有工艺路线
    	$line=Db::name("gx_line")->order("id asc")->column("id");
    	if(count($line)>0){
    		$sql=implode(",",$line);
    		//读取下面的所有工序
    		$gx=Db::name("gx_list")->where("lid in ($sql)")->group("dname")->order("orderby asc,id asc")->field("dname,orderby")->select();
    		if($gx!==false&&count($gx)>0){
    			$this->assign("gx",$gx);
    		}
    	}
    	
    	return $this->fetch();
    }
    
    //保存顺序
    public function save_order(){
    	
    	$gx=input("data");
    	$gx_arr=json_decode($gx);
    	//所有工艺路线
    	$line=Db::name("gx_line")->order("id asc")->column("id");
    	$count=0;
    	if(count($line)>0){
    		$sql=implode(",",$line);
    		//读取下面的所有工序
    		foreach($gx_arr as $name=>$order){
    			$update=array();
    			$update['orderby']=$order!=''?$order:'0';
    			Db::name("gx_list")->where("lid in ($sql) and dname='$name'")->update($update);
    			$count++;
    		}

    	}
    	
    	gx_cache();
    	return ['status'=>'1','count'=>$count];
    }
    
    
    //新版工序结束  ###################################################################################
    
    //新版工序固定工作流开始  ###################################################################################
    public function fix_gx(){
    	
    	//读取所有的工序
    	$gx_list=M("gx_list")->alias('a')->field('a.*,b.cache_id')->join('gx_group b','a.gid=b.id')->order("orderby asc")->select();
    	$group=@include APP_DATA.'gx_group.php';
    	//读取所有工艺线-有些客户可能用相同的工序名，加上工艺线名称好辨认
    	$gx_line=M("gx_line")->order("id asc")->select();
    	$gxline=array();
    	foreach($gx_line as $value){
    		$gxline[$value['id']]=$value['title'];
    	}
    	
    	$gxlist=array();
    	if($gx_list){
    	    foreach ($group as $k=>$gp){
    	        if (!isset($gxlist[$gp['name']])){
    	            $gxlist[$gp['name']] = array();
    	        }
    	        foreach($gx_list as $value){
    	            if ($k==$value['cache_id']){
    	                $value['line_name']='';
    	                if($value['lid']>0){
    	                    $value['line_name']=$gxline[$value['lid']];
    	                }
    	                array_push($gxlist[$gp['name']],$value);
    	            }
    	        }
    	    }
    	}
    	$this->assign("gx_list",$gxlist);
    	
    	$list=M("fixed_gx")->order("orderby asc,pid asc")->select();
    	$need_update=false;
    	$group_gx=array();//已经分组的工序
    	foreach ($list as $value){
    		if($value['gx_orderby']==''||$value['gx_orderby']=='0'){
    			//删除没有分组的，不要脏数据
    			M("fixed_gx")->where("id='{$value['id']}'")->delete();
    			$need_update=true;
    		}else{
    			
    			$t=array();
    			$t['gx_name']=$gxlist[$value['gx_id']]['dname'];
    			$t['gx_id']=$value['gx_id'];
                $group_gx[$value['gx_orderby']]['orderby']=$value['orderby'];
    			if($value['pid']=='0'){
    				//父
    				$group_gx[$value['gx_orderby']]['parent'][]=$t;
    			}else{
    				//子
    				$group_gx[$value['gx_orderby']]['child'][]=$t;
    			}
    			
    		}
    	}
    	
    	$this->assign("group_gx",$group_gx);
    	
    	if($need_update){
    		fix_gx_cache();
    	}
    	return $this->fetch();
    }
    
    //保存工作流
    public function save_fix_gx(){
    	
    	$data=input("data/a");
    	$delall=input("delall");//是否清除所有
    	/**
    	 * array(
    	 * 		'分组名称1'=>array(
    	 * 					'orderby'=>0
    	 * 					'list'=>array('1',...),//1是工序的id
    	 * 				)
    	 * )
    	 */
    	if($delall){
    		//清空固定工序
    		$prefix=config("database.prefix");
    		Db::execute("TRUNCATE `".$prefix."fixed_gx`");
    		return ['status'=>'1','msg'=>'设置成功'];
    	}
    	
    	//查询数据表现有工序
    	$list=M("fixed_gx")->order("id asc")->select();
    	
    	$uid=session("uid");
    	$now=time();
    	
    	$in_data=array();
    	$all_gx=array();//记录所有提交的工序ID
    	//按顺序排序
    	foreach($data as $value){
    	
    		if(empty($value['gxid'])){
    			return ['status'=>'2','msg'=>'工作流组中请设置工序'];
    			exit();
    		}
    		
    			$gx_id=$value['gxid'];
    			$t=array();
    			$t['gx_id']=$value['gxid'];
    			$t['pid']=$value['pid'];
    			$t['gx_orderby']=$value['gxorderby'];
    			$t['uid']=$uid;
                $t['orderby']=$value['orderby'];
    			$t['addtime']=$now;
    			
    			$exist=false;
    			foreach($list as $val){
    				//和旧数据同组，同工序，
    				if($val['gx_id']==$t['gx_id']&&$t['gx_orderby']==$val['gx_orderby']){
    					$exist=true;
    					break;
    				}
    			}
    			
    			
    			if($exist){
    				//更新
    				M("fixed_gx")->where("gx_id='$gx_id' and gx_orderby='{$t['gx_orderby']}'")->update($t);
    			}else{
    				//新建
    				M("fixed_gx")->insertGetId($t);
    			}
    			
    			
    			$d=array();
    			$d['gx_id']=$gx_id;
    			$d['gx_orderby']=$t['gx_orderby'];
    			$all_gx[]=$d;
    		
    	}
    	
    	//对比所有工序的所有组
    	$all=array();
    	foreach($all_gx as $val){
    		$all[$val['gx_id']][]=$val['gx_orderby'];
    	}
    	
    	//删除不存在的组或工序
    	$del_gx=array();
    	foreach($list as $value){
    		
    		$gx_id=$value['gx_id'];
    		if(!isset($all[$gx_id])){		//没有在新的工序内，则删除
    			$del_gx[]=$value['id'];
    		}
    		
    		//有工序但都没有分组的就删除
    		if(isset($all[$gx_id])&&!in_array($value['gx_orderby'], $all[$gx_id])){
    			$del_gx[]=$value['id'];
    		}
    		
    	}
    	
    	if(count($del_gx)>0){
    		M("fixed_gx")->where("id in (".implode(",",$del_gx).")")->delete();
    	}
    	
		//更新缓存
    	fix_gx_cache();
    	return ['status'=>'1','msg'=>'保存成功'];
    }
    
    //新版工序固定工作流结束  ###################################################################################
    
    //新版物料###########################################################################################
    //系列设置
    public function series_new(){

    	$where="";
    	$keyword=input("keyword");
    	if(!empty($keyword)){
    		$where.="a.xname like '%$keyword%'";
    	}
    	//获取系列
    	$result = Db::name('series')->alias('a')->field('a.*,b.name')->join('series_genre b','a.gid=b.id','LEFT')->where("$where")->order('a.id desc')->paginate(20,false,['query' => request()->param()]);
    	$page = $result->render();
    	$result=$result->all();
    	if ($result){
    		$this->assign('list',$result);
    	}else {
    		$this->assign('list',array());
    	}
    
    	//顶部右侧按钮
    	$buttons=array(
    			array('label'=>'导出物料','class'=>'export-series'),
    			array('label'=>'添加物料','class'=>'add-user')
    	);
    	$this->assign("buttons",$buttons);
    	$this->assign('page',$page);
    	return $this->fetch();
    }
    
    public function series_edit_new(){
    	$id = intval(input("param.id"));
    	//获取物料类别
    	$series_kind = Db::name('series_genre')->order('id asc')->select();
    	$this->assign('series_genre',$series_kind);
    	if (!empty($id)){
    	    $series = Db::name('series')->where('id',$id)->find();
    	    $this->assign('series',$series);
    	}
    	return $this->fetch();
    }
    
    //保存系列绑定多个工艺路线组合
    public function save_series(){
    	$name = input('name');
    	$gid = input('gid/d',0);
    	$id = input('id/d');
    	if (empty($name)){
    	    return ['code'=>1,'msg'=>'参数缺失'];
    	}
    	if (!empty($id)){
    	    $result = Db::name('series')->where('id',$id)->update(array('xname'=>$name,'gid'=>$gid));
    	}else {
    	    $result = Db::name('series')->insert(array('xname'=>$name,'gid'=>$gid));
    	}
    	return ['code'=>0,'msg'=>'保存成功'];
    }
    
    //删除新系列
    public function series_del(){
    	$id=intval(input("id"));
    	$one=Db::name('series')->where("id=$id")->find();
    	$ok=Db::name('series')->where("id=$id")->delete();
    	if($ok!==false){
    		Db::name('doclass')->where("series_id='$id'")->delete();
    		return array('status'=>1);
    	}
    	gx_cache();
    	return array('status'=>0);
    }
    //物料类别
    public function series_genre(){
        //获取类别列表
        $list = Db::name("series_genre")->order("id asc")->select();
        if (!empty($list)){
            $this->assign('list',$list);
        }else {
            $this->assign('list',array());
        }
        return $this->fetch();
    }
    //类别编辑
    public function series_genre_edit(){
        if (request()->isAjax()){
            $id = input('id/d');
            $name = input('name/s');
            if (!empty($id)){
                $into_data = Db::name("series_genre")->where('id',$id)->update(array('name'=>$name));
            }else {
                $time = time();
                $into_data = Db::name("series_genre")->insert(array('name'=>$name,'addtime'=>$time));
            }
            return ['code'=>0];
            exit();
        }else {
            $id = input("id/d");
            if (!empty($id)){
                $result = Db::name("series_genre")->where('id',$id)->find();
                $this->assign("genre",$result);
            }
        }
    
        return $this->fetch();
    }
    public function  series_genre_del(){
        $id = input("id/d");
        if (empty($id)){
            return ['code'=>1,'msg'=>'参数缺失'];
            exit();
        }
        $del_data = Db::name('series_genre')->where("id",$id)->delete();
        return ['code'=>0];
    }
    //复制分类及其下级分类
    public function copy_series_new(){
    	$id = intval(input("param.id"));
    	$series=Db::name('series')->where("id=$id")->find();
    	 
    	if($series===false||empty($series['id'])){
    		return ['status'=>'2','msg'=>'系列不存在'];
    	}
    	 
    	unset($series['id']);
    	$sid=Db::name('series')->insertGetId($series);
    	
    	$doclass=Db::name('doclass')->where("series_id='$id'")->order("id asc")->select();
    	foreach($doclass as $key=>$value){
    		unset($value['id']);
    		$value['series_id']=$sid;
    		$did = Db::name('doclass')->insertGetId($value);
    		if($key<=0){
    			Db::name('series')->where("id='$sid'")->update(array('gid'=>$did));
    		}
    	}
    	gx_cache();
    	return ['status'=>'1','msg'=>'复制成功'];
    }
    
    //删除工艺路线
    public function delet_doclass(){
    	$did=intval(input("id"));
    	if(empty($did)){
    		return array('status'=>2,'msg'=>'请提交有效的工艺路线ID');
    	}
    	$order=Db::name("order")->field("id,unique_sn")->where("gid='$did'")->find();
    	if($order!==false&&$order['id']>0){
    		return array('status'=>2,'msg'=>'该工艺路线已有订单，唯一编号:'.$order['unique_sn'].'，不能删除');
    	}else{
    		Db::name("doclass")->where("id='$did'")->delete();
    		return array('status'=>1,'msg'=>'删除成功');
    	}
    	gx_cache();
    }
    
    //新版系列###########################################################################################
    
    public function warmtime(){
        $kind = intval(input("param.kind"));
        if (Request::instance()->isAjax()){
            $day = intval(input("param.day"));
            $id = intval(input("param.id"));
            if ($kind==0){
                $indata = Db::name('warm_time')->insert(array('day'=>$day));
            }else {
                $updata = Db::name('warm_time')->where("id=$id")->update(array('day'=>$day));
            }
            return ['code'=>0];
        }else {
            $result = Db::name('warm_time')->find();
            if ($result){
                $this->assign('list',$result);
            }else {
                $this->assign('list',array());
            }
            
        }
        return $this->fetch();
    }

    /**
     * 导出第三方字段 --美加项目使用
     */
    public function downloadPostData()
    {
        $excel = new Excel();
        $list = Db::name('post_data')->limit(10)->select();
        if(count($list) == 0){
            $this->error('暂时没有第三方数据');
        }
        $data = [];
        foreach ($list as $k => $v) {
            $temp = json_decode($v['content'],true);
            $data[] = $temp;
        }
        $title = array_keys($data[0]);
        $excel->export('第三方字段原始数据',$title,$data,$title);
    }

    //二维码字段设置
    public function qrcode_field(){
    	
    	$host=strtolower($_SERVER['HTTP_HOST']);
    	$domain=str_replace("www.","", $host);
    	$site_cache=@include APP_CACHE_DIR.'site_cache.php';
    	$lockfield=0;//是否锁定不让改字段
    	if(is_array($site_cache)){
    		$lockfield=$site_cache[$domain]['lockfield'];
    	}
    	$this->assign("lockfield",$lockfield);
    	
    	
    	$field_type=@include APP_CACHE_DIR.'field_type.php';
    	$this->assign("field_type",$field_type);
    	
    	$list=Db::name("qrcode_fields")->order("isqrcode desc,orderby asc")->select();
    	$this->assign("list",$list);
    	
    	if($lockfield!=1){
    		//顶部右侧按钮
    		$buttons=array(
    				array('label'=>'添加字段','class'=>'add-user')
    		);
    		 
    		$this->assign("buttons",$buttons);
    	}
    	
    	//是否有排产功能
    	$this->assign("paichan",PRO_PAICHAN);
        $this->assign('host',$_SERVER['HTTP_HOST']);

    	return $this->fetch();
    }
    
    //显示编辑字段
    public function show_field(){
    	
    	$list=Db::name("qrcode_fields")->order("isqrcode desc,orderby asc")->select();
    	$this->assign("list",$list);
    	
    	$childs=array();
    	
    	$id = intval(input("param.id"));
    	if(!empty($id)){
    		$one=Db::name('qrcode_fields')->where("id=$id")->find();
    		
    		if($one['child']!=''){
    			$childs=explode(",",$one['child']);
    		}
    		
    		$this->assign("one",$one);
    	}
    	
    	$this->assign("childs",$childs);
    	//是否有排产功能
    	$this->assign("paichan",PRO_PAICHAN);
    	
    	$field_type=@include APP_CACHE_DIR.'field_type.php';
    	$this->assign("field_type",$field_type);
    	
    	$required_field=config("required_field");
    	//print_r($required_field);
    	$this->assign("required_field",$required_field);
    	
    	return $this->fetch();
    }
    
    //添加字段
    public function add_field(){
    	$uid = session("uid");
    	$field_name = trim(input("param.field_name"));
    	$third_fieldname = input('third_fieldname');
    	$explains= ctrim(input("param.explains"));
    	$type= ctrim(input("param.type"));
    	$orderby = ctrim(input("param.orderby"));
    	$listorder = ctrim(input("param.listorder"));
    	$scheduleorder = ctrim(input("param.scheduleorder"));
    	$child = input("param.child");
    	$now = time();
    	$id = intval(input("param.id"));
    	$is_system = intval(input("param.is_system"));
    	$status = intval(input("param.status"));
    	$onlist = intval(input("param.onlist"));
    	$salary_field = intval(input("param.salary_field"));
    	$search = intval(input("param.search"));
    	$scheduallist = intval(input("param.scheduallist"));
    	$isqrcode = intval(input("param.isqrcode"));
    	$id = intval(input("param.id"));
    	
    	$arr = array();
    	$arr['fieldname']=$field_name;
        $arr['third_fieldname']=$third_fieldname;
    	$arr['explains']=$explains;
    	$arr['type']=$type!=''?$type:'text';
    	$arr['orderby']=$orderby;
    	$arr['listorder']=$listorder;
    	$arr['is_system']=$is_system;
    	$arr['status']=$status;
    	$arr['onlist']=$onlist;
    	$arr['salary_field']=$salary_field;
    	$arr['search']=$search;
    	$arr['isqrcode']=$isqrcode;
    	$arr['scheduallist']=$scheduallist;
    	$arr['scheduleorder']=$scheduleorder;
    	$arr['child']=$child;
    	$arr['uid']=$uid;
    	$arr['addtime']=$now;
    	
    	if(empty($orderby)&&$isqrcode==1){
    		return array('status'=>2,'msg'=>'请输入二维码排序');
    	}
    	
    	if(!empty($orderby)){
    		$sql='';
    		if(!empty($id)){
    			$sql=" and id!='$id'";
    		}
    		$one=Db::name('qrcode_fields')->field("id")->where("orderby=$orderby $sql")->find();
    		if($one!==false&&$one['id']>0){
    			return array('status'=>2,'msg'=>'二维码排序数字已被占用，请保持唯一');
    		}
    	}
    	
    	if(!empty($listorder)){
    		$sql='';
    		if(!empty($id)){
    			$sql=" and id!='$id'";
    		}
    		$one=Db::name('qrcode_fields')->field("id")->where("listorder=$listorder $sql")->find();
    		if($one!==false&&$one['id']>0){
    			return array('status'=>2,'msg'=>'列表排序数字已被占用，请保持唯一');
    		}
    	}
    	
    	if(!empty($scheduleorder)){
    		$sql='';
    		if(!empty($id)){
    			$sql=" and id!='$id'";
    		}
    		$one=Db::name('qrcode_fields')->field("id")->where("scheduleorder=$scheduleorder $sql")->find();
    		if($one!==false&&$one['id']>0){
    			return array('status'=>2,'msg'=>'排产字段排序数字已被占用，请保持唯一');
    		}
    	}
    	
    	if (!empty($id)){
    		$ok = Db::name('qrcode_fields')->where("id=$id")->update($arr);
    	}else {
    		//录入
    		$ok = DB::name('qrcode_fields')->insert($arr);
    	}
    
    	if ($ok!==false){
    		qrfield_cache();
    		return array('status'=>1);
    	}else{
    		return array('status'=>2);
    	}
    }
    
    //删除字段
    public function del_field(){
    	$uid = session('gid');
    	$id = intval(input("param.id"));
    	$where['id']=$id;
    	$data_del = Db::name("qrcode_fields")->where($where)->delete();
    	if ($data_del!==false){
    		qrfield_cache();
    		return ['code'=>1];//成功
    	}else{
    		return ['code'=>2];
    	}
    }
    
    /**
     * 字段绑定工艺线
     */
    public function field_bind_rule()
    {
        $field = input('field');
        $gxline = input('lines');

        $where = "1=1";
        if($field != ''){
            $where .= " and field_id=$field";
        }
        if($gxline != ''){
            $where .= " and gxline_id=$gxline";
        }
        $list = Db::name('field_rule')->alias('a')->field('a.*,b.fieldname,b.explains,c.title')->join('qrcode_fields b','a.field_id=b.id')
                ->join('gx_line c','a.gxline_id=c.id')
                ->where($where)
                ->order('gxline_id asc,sort asc')
                ->paginate('',false,['query'=> input('get.')]);
        $data = $list->all();        
        foreach ($list as $k => $v) {
            $gxlist = Db::name('gx_list')->where('lid',$v['gxline_id'])->order('orderby asc')->column('dname');
            $data[$k]['gx_text'] = implode('->', $gxlist);
        }
        
        //筛选下拉数据
        $field = Db::name('field_rule')->alias('a')->field('a.*,b.fieldname,b.explains')
                ->join('qrcode_fields b','a.field_id=b.id')
                ->group('a.field_id')
                ->select();
        $line = Db::name('gx_line')->select();

        //顶部右侧按钮
    	$buttons=array(
            array('label'=>'添加规则','class'=>'add-rule'),
            array('label'=>'导入规则','class'=>'import-rule'),
    	);
        $this->assign('list',$data);
        $this->assign('buttons',$buttons);
        $this->assign('page',$list->render());
        $this->assign('field',$field);
        $this->assign('line',$line);
        $this->assign('search', input('get.'));
        return $this->fetch();
    }
    
    /**
     * 添加字段绑定工艺线
     */
    public function add_field_rule()
    {
        $id = input('id');
        if(request()->ispost()){
            $data = input('post.');
            if($id > 0){
                unset($data['id']);
                $res = Db::name('field_rule')->where('id',$id)->update($data);
            }else{
                $res = Db::name('field_rule')->insert($data);
            }
            $matchine = new MatchingGxline();
            $list = $matchine->convertField();
            cache_write("filed_rule","field_rule",$list);
            if($res!==false){
                $this->_success('保存成功');
            }
            $this->_error('保存失败,请重试');
            return;
        }
        
        $res = Db::name('field_rule')->where('id',$id)->find();
        $field = Db::name('qrcode_fields')->order('id asc')->select();
        $gxline = Db::name('gx_line')->where("id>'2'")->order('id asc')->select();
        $this->assign('gxline',$gxline);
        $this->assign('field',$field);
        $this->assign('res',$res);
        $this->assign('id',$id);
        return $this->fetch();
    }
    
    /**
     * 删除-字段绑定工艺线
     */
    public function del_field_rule()
    {
        $id = input('id/d');
        $res = Db::name('field_rule')->where('id',$id)->delete();
        if($res){
            $this->_success('删除成功');
        }
        $this->_error('删除失败');
    }
    
    /**
     * 导入--字段绑定工艺线
     */
    public function import_field_rule()
    {
        if(request()->ispost()){
            $file = $this->request->file('file');
            $originalName = $file->getInfo('name');
            $extension = strtolower(pathinfo($originalName)['extension']);
            $arrAllowedExtensions = ['xls','xlsx'];
            if (!in_array($extension, $arrAllowedExtensions) || $extension == 'php') {
                $this->_error('非法文件类型');
            }
            $info = $file->move('./uploads');
            if(!$info){
                $this->_error('文件上传失败');
            }
            $excel = new Excel();
            $data = $excel->read2('./uploads/'.$info->getSaveName(), ['fieldname','type','rule','gxline_id']);
            $allfield = Db::name('qrcode_fields')->select();
            $fieldId = [];
            foreach ($allfield as $k => $v) {
                $fieldId[$v['fieldname']] = $v['id'];
            }
            $insert = [];
            foreach ($data as $k => $v) {
                $gxlineID = explode('|', $v['gxline_id']);
                foreach ($gxlineID as $k2 => $v2) {
                    $fieldTemp = isset($fieldId[$v['fieldname']])?$fieldId[$v['fieldname']]:0;
                    if($fieldTemp != 0){
                        $insert[] = ['field_id'=>$fieldId[$v['fieldname']],'type'=>$v['type'],'rule'=>$v['rule'],'gxline_id'=>$v2];
                    }
                }
            }
            $res = Db::name('field_rule')->insertAll($insert);
            if($res){
                //写入缓存
                $matchine = new MatchingGxline();
                $list = $matchine->convertField();
                cache_write("filed_rule","field_rule",$list);
                $this->_success('导入成功');
            }
            $this->_error('导入失败');
            return;

        }
        return $this->fetch();
    }
    
    //班组管理
    public function team(){
    	//获取所有班组数组
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("list",$this->team);
    	
    	//查找所有的权限组
    	$gs=Db::name("auth_group")->order("id asc")->select();
    	$groups=array();
    	foreach($gs as $value){
    		$groups[$value['id']]=$value;
    	}
    	$this->assign("groups",$groups);
    	
    	//顶部右侧按钮
    	$buttons=array(
    			array('label'=>'添加班组','class'=>'add-team')
    	);
    	
    	$this->assign("buttons",$buttons);
    	
    	//单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
    	return $this->fetch();
    }
    
    //循环获取班组层级数据
    public function get_team($id){
    	//$uid=session("uid");
    	//$list=Db::name("team")->where("pid='$id' and uid='$uid'")->select();
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
    
    //显示添加班组
    public function add_team(){
    	$id=intval(input("id"));
    	$one=array();
    	if(!empty($id)){
    		$one=Db::name("team")->where("id='$id'")->find();
    	}
    	$this->assign("one",$one);
    	
    	//获取所有班组数组
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("list",$this->team);
    	 
    	//查找所有的权限组
    	$gs=Db::name("auth_group")->order("id asc")->select();
    	$groups=array();
    	foreach($gs as $value){
    		$groups[$value['id']]=$value;
    	}
    	$this->assign("groups",$groups);
    	//单位缓存
    	$ab_unit=@include_once APP_DATA.'ab_unit.php';
    	$this->assign("ab_unit",$ab_unit);
    	
    	
    	return $this->fetch();
    }
    
    //添加字段
    public function save_team(){
    	
    	$uid=session("uid");
    	$id=intval(input("param.id"));
    	$team_name = ctrim(input("param.team_name"));
    	$pid= intval(input("param.pid"));
    	$auth_group = intval(input("param.auth_group"));
    	$day_ab = floatval(input("param.day_ab"));
    	$ab_unit = intval(input("param.ab_unit"));
    	
    	$now = time();
    	 
    	$arr = array();
    	$arr['team_name']=$team_name;
    	$arr['pid']=$pid;
    	$arr['auth_id']=$auth_group;
    	$arr['day_ab']=$day_ab;
    	$arr['unit']=$ab_unit;
    	$arr['uid']=$uid;
    	$arr['addtime']=$now;
    	
    	if (!empty($id)){
    		if($pid==$id){
    			return array('status'=>2,'msg'=>'不能选自己为上级班组');
    		}
    		//修改
    		$ok = Db::name('team')->where("id=$id")->update($arr);
    		$msg='修改成功';
    	}else {
    		//新增
    		$ok = DB::name('team')->insert($arr);
    		$msg='添加成功';
    	}
    
    	if ($ok!==false){
    		
    		//循环所有的班组，如果上级id不存在的就更新为0
    		$list=Db::name('team')->order("id asc")->select();
    		$tids=array();
    		foreach($list as $value){
    			$tids[]=$value['id'];
    		}
    		
    		foreach($list as $value){
    			if(!in_array($value['pid'], $tids)){
    				Db::name('team')->where("id='{$value['id']}'")->update(array('pid'=>'0'));
    			}
    		}
    		
    		team_cache();
    		
    		return array('status'=>1,'msg'=>$msg);
    	}else{
    		return array('status'=>2,'msg'=>'操作失败,请重试');
    	}
    }
    
    
    //删除班组
    public function del_team(){
    	$id = intval(input("param.id"));
    	$where['id']=$id;
    	$data_del = Db::name("team")->where($where)->delete();
    	if ($data_del!==false){
    		team_cache();
    		return array('status'=>1);
    	}else{
    		return array('status'=>2);
    	}
    }
    
    
    //班组绑定工序
    public function teambind(){

    	//加载工序缓存
    	$all_gx=array();
    	$gx_list=Db::name("gx_list")->where("1=1")->order('orderby asc,id asc')->select();
    	foreach($gx_list as $value){
    		$all_gx[$value['id']]=$value;
    	}
    	
    	//获取本人添加的所有班组数组
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("list",$this->team);
    	//班组
    	$uid=session("uid");
    	//$team_list=Db::name("team")->where("uid='$uid'")->order("id asc")->select();
    	$team_list=Db::name("team")->order("id asc")->select();
    	$team=array();
    	if($team_list!==false&&count($team_list)>0){
    		foreach($team_list as $value){
    			$team[$value['id']]=$value;
    		}
    	}

    	foreach ($team as $k=>$val){
    			//班组负责工序
    			$gx = Db::name('team_gx')->where("tid ='{$val['id']}'")->find();
    			$team[$k]['gx_l'] = array();
    			if ($gx){
    				
    				if (!empty($gx['gx_id'])||!empty($gx['ngx_id'])){
    					$gx_ids=explode(",",$gx['gx_id']);
    					//工序名-旧逻辑
    					$gname_res = array();
    					foreach($gx_ids as $id){
    						if(isset($all_gx[$id])){
    							$dname=$all_gx[$id]['dname'];
    							$gname_res[$dname]=$dname;
    						}
    					}
    					//新逻辑
    					if(!empty($gx['ngx_id'])){
    						$ngx_id=unserialize($gx['ngx_id']);
    						foreach($ngx_id as $did=>$arr){//did=>array(15,16...)多工序
    							foreach($arr as $id){
    								if(isset($all_gx[$id])){
    									$dname=$all_gx[$id]['dname'];
    									$gname_res[$dname]=$dname;
    								}
    							}
    						}
    					}
    					$team[$k]['gx_list'] = $gname_res;
    					
    				}else {
    					$team[$k]['gx_list'] = array();
    				}
    			}
    	}//end of foreach
    	

    	$this->assign('team',$team);
    	
    	return $this->fetch();
    }
    
    //班组工序编辑
    public function team_gx(){

    	//所有可组合的工艺路线
        $where = "";
    	if (GLASS_PLAN==0){
    	    $where = "id>'2'";
    	}
    	$gx_line=Db::name("gx_line")->where($where)->order('id asc')->select();
    
    	if($gx_line!==false&&count($gx_line)>0){
    		foreach($gx_line as $key=>$value){
    			$gx_list = Db::name("gx_list")->where("lid='{$value['id']}'")->order('orderby asc,id asc')->select();
    			$gx_line[$key]['gx_list']=$gx_list;
    		}
    		$this->assign("gx_line",$gx_line);
    	}
    	
    	//工序分配
    	$id = intval(input('param.tid'));//班组的ID
    	$person = Db::name('team')->alias("a")->field("a.*,b.gx_id,b.ngx_id")
    	->join("team_gx b","b.tid=a.id","LEFT")->where("a.id=$id")->find();
    	$person['gx_id'] = $person['gx_id']!=''?explode(",", $person['gx_id']):array();
    	$person['ngx_id'] = trim($person['ngx_id'])!=''?unserialize($person['ngx_id']):array();
    	foreach ($person['gx_id'] as $key=>$vl){
    		$person['gx_id'][$key] = intval($vl);
    	}
    	foreach ($person['ngx_id'] as $lid=>$arr){
    		$person['ngx_id'][$lid]= $arr;
    	}
    	
    	//查找父分类
    	if($person['pid']>0){
    			$parent=Db::name('team')->where("id='{$person['pid']}'")->find();
    			$this->assign('parent',$parent);
    	}
    	$this->assign('person',$person);
    	return $this->fetch();
    }
    
    //保存绑定
    public function team_gxbind(){
    	$uid = session("uid");
    	$tid = intval(input('param.tid'));
    	$data = input("param.groud/a");
    	$newgy=input('param.newgy/a');
    	$adtime = time();
    	//处理数据
    	if(isset($data)&&!empty($data)){
    		$li = implode(",", $data);
    	}else{
    		$li = '';
    	}
    	
    	$ngx_id=array();
    	if(!empty($newgy)&&is_array($newgy)){
    		foreach($newgy as $value){
    			$ngx_id[$value['lid']][]=$value['gxid'];
    		}
    	}
    	$ngx_id=serialize($ngx_id);
    	//是否存在
    	$decide = Db::name('team_gx')->where("tid=$tid")->find();
    	if ($decide){
    		$in_gx = Db::name('team_gx')->where("tid=$tid")->update(array('gx_id'=>$li,'ngx_id'=>$ngx_id,'addtime'=>$adtime));
    	}else {
    		$vl = array('tid'=>$tid,'gx_id'=>$li,'ngx_id'=>$ngx_id,'addtime'=>$adtime);
    		$in_gx = Db::name('team_gx')->insert($vl);
    	}
    
    	if ($in_gx){
    		echo json_encode(array('status'=>1,'arr'=>$data));
    	}
    
    }
    
    //删除工序组绑定
    public function del_teamgx(){
    	$id = intval(input("param.id"));
    	$where['tid']=$id;
    	$data_del = Db::name("team_gx")->where($where)->delete();
    	if ($data_del!==false){
    		return array('status'=>1);
    	}else{
    		return array('status'=>2);
    	}
    }
    
    //班组绑定屏幕
    public function team_monitor(){
    	 
    	//班组
    	$team_list=Db::name("team")->order("id asc")->select();
    	$team=array();
    	if($team_list!==false&&count($team_list)>0){
    		foreach($team_list as $value){
    			$team[$value['id']]=$value;
    		}
    	}
    	
    	//查询所有的屏幕
    	$monitors=Db::name("monitor")->order("code asc")->select();
    	//并入班组
    	if($monitors!==false&&count($monitors)>0){
    		foreach($monitors as $key=>$value){
    			$monitors[$key]['team_name']=$team[$value['teamid']]['team_name'];
    		}
    	}
    	
    	$this->assign('monitors',$monitors);
    	
    	//屏幕样式缓存
    	$monitor_style=@include_once APP_CACHE_DIR.'monitor.php';
    	$this->assign('monitor_style',$monitor_style);
    	
    	//顶部右侧按钮
    	$buttons=array(
    			array('label'=>'添加屏幕','class'=>'add-monitor')
    	);
    	 
    	$this->assign("buttons",$buttons);
    	
    	return $this->fetch();
    }
    
    //编辑班组绑定屏幕
    public function edit_monitor(){
    
    	$id=input('param.id',0,'intval');
    	//班组
    	//获取所有班组数组
    	$this->team=array();
    	$this->get_team('0');
    	$this->assign("team",$this->team);
    	
    	//屏幕样式缓存
    	$monitor_style=@include_once APP_CACHE_DIR.'monitor.php';
    	$this->assign('monitor_style',$monitor_style);
    	
    	//已选班组id
    	$team_id=array();
        $this->assign('gx_list',[]);
    	//编辑
    	if(!empty($id)){
    		$monitor=Db::name("monitor")->where("id='$id'")->find();
    		if($monitor['teamid']!=''){
    			$team_id=explode(",", $monitor['teamid']);
    		}
                $gxlist = $monitor['gx_list']!=''?explode(',', $monitor['gx_list']):[];
                $teamList = $monitor['teamid_list']!=''?explode(',', $monitor['teamid_list']):[];
                $this->assign('gx_list',$gxlist);
                $this->assign('team_list',$teamList);
    		$this->assign("monitor",$monitor);                    
    	}
    	
        //所有的工序
        $gx = Db::name('gx_list')->where('isdel',0)->group('dname')->select();
        $this->assign('gx',$gx);
        
    	$this->assign("team_id",$team_id);
    	
    	
    	//工艺线-工序数组
    	$gx_list=@include APP_DATA.'gx_list.php';
    	 
    	//读取所有工艺线-有些客户可能用相同的工序名，加上工艺线名称好辨认
    	$gx_line=@include APP_DATA.'lines.php';
    	$gxline=array();
    	foreach($gx_line as $value){
    		$gxline[$value['id']]=$value['title'];
    	}
    	 
    	$line_gxlist=array();
    	if($gx_list){
    		foreach($gx_list as $value){
    			$value['line_name']='';
    			if($value['lid']>0){
    				$value['line_name']=$gxline[$value['lid']];
    			}
    			$line_gxlist[$value['id']]=$value;
    		}
    	}
    	$this->assign("line_gxlist",$line_gxlist);
    	
    	//选择字段
    	$qrcodeList=array();
    	$fields=@include APP_DATA.'qrfield.php';
    	
    	foreach($fields as $value){
        	$qrcodeList[$value['fieldname']]=$value['explains'];
       	}    		
    	
    	$this->assign("qrcodeList",$qrcodeList);
    	
    	return $this->fetch();
    }
    
    //保存屏幕
    public function save_monitor(){
    	
    	$id = intval(input('param.id'));
    	$code = input("param.code",'','ctrim');
    	$vname = input("param.vname",'','ctrim');
    	$teamid = intval(input('param.team_id'));
    	$display_field = ctrim(input('param.display_field'));
    	$style = intval(input('param.style'));
    	$addtime = time();
    	$gxList = input('gx_list/a');
        $monitorType = input('monitor_type');
        $teamList = input('team_list/a');

    	if(count(input("team_id/a"))>3){
    		echo json_encode(array('status'=>2,'msg'=>"最多绑定三个小组"));
    		exit();
    	}
    	
    	if($code==''||$teamid==''){
    		echo json_encode(array('status'=>2,'msg'=>"请填写屏幕代码和选择班组"));
    		exit();
    	}
    
        $gxList = is_array($gxList)&&count($gxList)>0?implode(',', $gxList):'';
        $teamList = is_array($teamList)&&count($teamList)>0?implode(',', $teamList):'';
    	$data = array('code'=>strtoupper($code),'vname'=>$vname,'teamid'=>$teamid,'display_field'=>$display_field,'style'=>$style,'addtime'=>$addtime,'monitor_type'=>$monitorType,
                    'teamid_list' => $teamList,'gx_list'=>$gxList,
                );
           
    	//编辑
    	if(!empty($id)){

                
    		$monitor = Db::name('monitor')->where("id=$id")->update($data);
    	}else{
    		$monitor = Db::name('monitor')->insert($data);
    	}
    
    	if ($monitor!==false){
    		echo json_encode(array('status'=>1,'msg'=>'保存成功'));
    	}
    	exit();
    }
    
    //删除屏幕
    public function del_monitor(){
    	$id = intval(input("param.id"));
    	$where['id']=$id;
    	$data_del = Db::name("monitor")->where($where)->delete();
    	if ($data_del!==false){
    		return array('status'=>1);
    	}else{
    		return array('status'=>2);
    	}
    }
    
    //更新站点缓存
    public function clear_cache(){
    	gx_cache();//工艺路线和工序缓存
    	team_cache();//班组缓存
    	qrfield_cache();//字段缓存
    	fix_gx_cache();//固定流工序
    	field_rule_cache();//字段绑定工艺线缓存
    	//排产字段缓存
    	schedule_cache();
    	//时产值添加缓存
    	hour_cache();
    	//其他要覆盖的缓存文件
    	$copy_cache_files=array(
    			'ab_unit.php'
    	);
    	
    	//复制必须要的缓存文件
    	copy_cache($copy_cache_files,PRO_DOMAIN);
    	
    }

    /**
     * 班组打印机和样式列表
     */
    public function team_bind_print()
    {
        $list = Db::name('print_style')->alias('a')->field('a.*')->select();

        //顶部右侧按钮
        $buttons=array(
            array('label'=>'添加打印机','class'=>'addx'),
        );

        $print_style = @include_once APP_DATA.'print_style.php';
        $this->assign('print_style',$print_style);
        $this->assign("buttons",$buttons);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 添加打印机
     */
    public function add_print()
    {
        $id = input('id/d');
        if(request()->ispost()){
            $data = input('post.');
//            $gxid = implode(',', input('gxid/a'));
//            $data['gxid'] = $gxid;
            if($id>0){
                $res = Db::name('print_style')->where('id',$id)->update($data);
            }else{
                $data['addtime'] = time();
                Db::name('print_style')->insert($data);
            }
            if($res!==false){
                return ['status'=>1,'msg'=>'保存成功'];
            }
            return ['status'=>0,'msg'=>'保存失败'];
        }

        $print = Db::name('print_style')->where('id',$id)->find();
        $into = Db::name('gx_list')->field('a.id,a.dname,b.title')->alias('a')->join('gx_line b','a.lid=b.id')->where("a.lid",'>','0')->select();
//        $into = Db::name('gx_list')->field('a.id,a.dname')->alias('a')->where("a.lid",'>','0')->select();
        $print_style = @include_once APP_DATA.'print_style.php';

        $this->assign('print_style',$print_style);
        $this->assign('one',$print);
        $this->assign('into_gx',$into);
        return $this->fetch();
    }

    /**
     * 选择班组异步加载工序
     */
//    public function ajax_gx()
//    {
//        $teamid = input('tid/d');
//
//        $gxdata = @include_once APP_DATA . 'gx_list.php';
//        $res = Db::name('team_gx')->where('tid', $teamid)->find();
//        if ($res) {
//            $gxid = unserialize($res['ngx_id']);
//            $data = [];
//            $i = 0;
//            foreach ($gxid as $k2 => $v2) {
//                $tempGxid = $v2[0];
//                $data[$i]['gxid'] = $v2[0];
//                $data[$i]['gx_name'] = $gxdata[$tempGxid]['dname'];
//                $i++;
//            }
//            return $data;
//        }
//        return [];
//    }

    /**
     * 切割方案列表
     */
    public function import_list()
    {
        $list = Db::name('print_cut')->group('batch')->order('batch desc')->paginate();
        $this->assign('list',$list);

        //顶部右侧按钮
        $buttons=array(
            array('label'=>'导入切割方案','class'=>'addx'),
        );
        $this->assign("buttons",$buttons);
        return $this->fetch();
    }


    /**
     * 导入切割方案
     */
    public function import_cut()
    {
        return $this->fetch();
    }

    /**
     * 删除切割方案
     */
    public function del_import()
    {
        $batch = input('id');
        $res = Db::name('print_cut')->where('batch',$batch)->delete();
        if($res){
            return ['code'=>0,'msg'=>'删除成功'];
        }
        return ['code'=>1,'msg'=>'删除失败'];
    }

    /**
     * 查看切割方案
     */
    public function read_import()
    {
        $batch = input('id');
        $res = Db::name('print_cut')->where('batch',$batch)->order('sort asc')->select();
        $this->assign('list',$res);
        return $this->fetch();
    }


}




