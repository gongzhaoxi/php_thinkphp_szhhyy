<?php
namespace app\manage\controller;
use think\Controller;
use think\Session;
use think\Url;
use think\Db;
use think\facade\Request;

class Super extends Controller{
    
    function __construct(){
        header("Cache-control: private");  // history.back返回后输入框值丢失问题
        parent::__construct();
    }
 
    /* 初始化操作 */
    public function initialize(){
//         require '../extend/src/Auth.php';
//         $auth = new \Auth();
//         $uid = session('gid');
//         $yid = session('uid');
//         $mster = session('master');
        $action = Request::action();
        $controller = Request::controller();
   
        //不需要登录的Controller
//         $nologin_controller = array('Login','Wxapi');
//         if (!in_array($controller,$nologin_controller) && !$uid){
//             $this->error('请登录', 'Login/login');
//             exit();
//         }
        
        $name = $controller.'/'.$action;
        //不需要验证
//         $nocheck = array();
//         if (in_array($name, $nocheck) || $mster==1){
//             $result = true;
//         }else{
//             $result = $auth->check($name, $yid);
//         }
//         if (!$result){
//             $this->error('您没有权限','Index/index');
//         }
        
        $this->assign("controller",$controller);
        $this->assign("current_position",$name);
        
    }
    
    //
    
}