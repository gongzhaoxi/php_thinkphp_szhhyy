<?php
namespace app\manage\controller;
use think\Controller;
use think\Db;
class Login extends Controller{
    public function initialize(){
        parent::initialize();
        if (!empty(session('xxencrypt'))){
          // $this->redirect('Index/index');
        }
    }
    public function login(){
      return $this->fetch();
    }
    
    public function dologin(){
        $password=input("password");
        $config_pass=config("manage_key");
        $config_pass=authcode($config_pass,"DECODE");
        if ($config_pass==$password){
            session('xxencrypt',$password);
            $this->success('登录成功',url('Index/index'),array(),0);
        }else {
            $this->error('授权秘钥错误！',url('Login/login'));
        }
    }
    
    //退出登录
    public function logout(){
    	session('xxencrypt',null);
    	$this->success('退出成功',url('Login/login'),array(),0);
    }
}