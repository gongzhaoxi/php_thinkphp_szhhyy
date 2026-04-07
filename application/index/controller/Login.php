<?php
namespace app\index\controller;
use think\Controller;
use think\captcha\Captcha;
use think\Db;
class Login extends Controller{
    public function initialize(){
        parent::initialize();
        if (!empty(session('uid'))){
           $this->redirect('Index/index');
        }
    }
    public function login(){
    //项目配置
    $site_cache=@include APP_CACHE_DIR.'site_cache.php';
    $this->system=$site_cache[PRO_DOMAIN];
    //判断是否已经关站
    if($this->system['status']!=1){
    		exit("站点已关闭");
    }
      return $this->fetch();
    }
    
    public function register(){
        
       return $this->fetch();
    }
    
    public function log(){
        $uname = ctrim(input("param.account"));
        $pwd = ctrim(input("param.pwd"));
        $md_pwd = md5($pwd);
        $code = ctrim(input("param.code"));
        //不为空
        if (empty($uname)){
            $this->error('账户不为空','Login/login');
        }else if (empty($pwd)){
            $this->error('密码不为空','Login/login');
        }elseif (empty($code)){
            $this->error('验证码不为空','Login/login');
        }
        
        $captcha = new \think\captcha\Captcha();
        $code_true = $captcha->check($code);
        if ($code_true==false){
            $this->error('验证码错误','Login/login');
        }
        //匹配数据库
        $result = Db::name('login')->where("uname='$uname' and password='$md_pwd' and del='0'")->find();
        if ($result){
        	if($result['nologin']==1){
        		$this->error('该账户限制登录！',url('Login/login'));
        		exit();
        	}
            session('uid',$result['id']);
            session('gid',$result['uid']);
            session('role',$result['role']);//角色
            session('user_role',$result['user_role']);//用户角色
            session('name',$result['uname']);
            session('master',$result['master']);
            session('tid',$result['tid']);
            
            $url='Login/login';//没权限跳转地址
            
            $index_url='index/index';
            $index_id=Db::name('auth_rule')->field("id")->where("name='$index_url'")->find();//默认首页的规则的id
            //查询权限
            $rules=Db::name('auth_group')->where("id='{$result['role']}'")->find();
            if($rules!==false&&$rules['rules']!=''){
            	$rules=explode(",",$rules['rules']);
            	if(in_array($index_id['id'], $rules)){
            		$url=$index_url;
            	}else{
            		$url="index/index";
            	}
            }
            session('default_url',$url);

            //如果总后台有开启  半成品出入库
            if(ERP_URL != '' && PART_INTO == 1){
                $this->loginErp($result['uname']);//登录erp物控的key
            }
            $this->redirect($url);
        }else {
            $this->error('账户或密码错误！',url('Login/login'));
        }
    }

    /**
     * 用于登录erp物控
     */
    public function loginErp($uname)
    {
        $token = setToken();
        $key = file_get_contents("http://".ERP_URL."/api/login/baogongLogin?host=".$_SERVER['HTTP_HOST']."&uname=".$uname.'&token='.$token);
        if($key){
            session('erp_login_key',$key);
        }
    }

    public function reg(){
        $account = ctrim(input("param.account"));
        $pwd = ctrim(input("param.password"));
        $pwdto = ctrim(input("param.passwordto"));
        
        if (empty($account)){
            $this->error('账户为空！',url('Login/register'));
        }
        if (empty($pwd)){
            $this->error('密码为空！',url('Login/register'));
        }
        if ($pwd !== $pwdto){
            $this->error('密码不一致！',url('Login/register'));
        }   
            $inpwd = md5($pwd);
            $indate = time();
            //出查询是否重复
            $repest = Db::name('login')->where("uname='$account'")->select();
            if ($repest){
                $this->error('账户已存在！',url('Login/register'),2000);
                exit();
            }
            $result = Db::name('login')->insertGetId(array('uname'=>$account,'password'=>$inpwd,'addtime'=>$indate,'master'=>1));
            if ($result){
                $update = Db::name('login')->where("id=$result")->update(array('uid'=>$result));
                $this->success('注册成功',url('Login/login'));
        }
    }
    
//     修改密码
    public function change_pwd(){
        $uname = ctrim(input("param.uname"));
        $pwd = ctrim(input("param.pwd"));
        
    }
    
    /**
     * 验证码获取
     */
    public function vertify()
    {
        $captcha = new Captcha();
        $captcha->useZh=false;
        $captcha->codeSet='abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $captcha->fontSize=22;
        $captcha->useCurve=true;
        $captcha->useNoise=false;
        $captcha->imageH=50;
        $captcha->imageW=150;
        $captcha->length=4;
        $captcha->reset=true;
        return $captcha->entry();
    }
    
    //批量更新订单的series_id
    public function update_sid(){
    	$doclass=M("doclass")->field("id,series_id")->order("id asc")->select();
    	if(!$doclass){
    		exit("没设置工艺路线");
    	}
    	$do=array();
    	foreach($doclass as $value){
    		$do[$value['id']]=$value;
    	}
    	
    	$orders=M("order")->order("id asc")->field("id,gid")->select();
    	if(!$orders){
    		exit("没订单");
    	}
    	
    	$count=0;
    	foreach($orders as $value){
    		if(isset($do[$value['gid']])){
    			$sid=$do[$value['gid']]['series_id'];
    			M("order")->where("id='{$value['id']}'")->update(array("series_id"=>$sid));
    			$count++;
    		}
    	}
    	
    	echo $count;
    }
}