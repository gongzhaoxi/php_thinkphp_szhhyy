<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use auth\Auth;
use tree\Tree;

/**
 * 基类控制器
 */
class Base extends Controller
{

    protected $uid = 0; //用户id   
    protected $group_id = 0; //用户组id
    protected $bind_dealer = 0;//绑定的经销商Id

    protected function _initialize()
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $noLogin = ['index/login', 'index/checklogin','user/addrule']; //无需登陆的方法

        $controllerName = $this->request->controller();
        $actionName = $this->request->action();
        $path = strtolower($controllerName . '/' . $actionName);

        if (in_array($path, $noLogin)) {
            return;
        }
        if (cookie('uid') == null) {
            $this->redirect(url('index/login'));
        }

        $this->uid = cookie('uid');
        $this->group_id = cookie('group_id');
        $this->bind_dealer = cookie('bind_dealer');
        $auth = new Auth();
        $check = $auth->check($path, $this->uid);
        if ($check !== true) {
            $this->_error('你没有权限访问');
        }
    }

    /**
     * 获取左侧菜单列表
     */
    public function getMenu()
    {
        $tree = new Tree();
        $group = Db::name('auth_group_access')->alias('a')->field('b.rules')
                ->join('auth_group b', 'a.group_id=b.id', 'left')
                ->where('uid', $this->uid)
                ->find();
        $ruleIds = isset($group['rules']) ? $group['rules'] : 0;

        $result = Db::name('auth_rule')->where('is_menu', 0)->whereIn('id', $ruleIds)->order('sort')->select();
        $tree->init($result);
        $array = $tree->getTreeArray(0);
        $this->assign('list', $array);
//        dump($array);exit;
        return $this->fetch('index/index2');
    }
	
	public function getJson($json){
		$code 	= $json['code']??0;
		$msg 	= $json['msg']??'操作成功';
		$data 	= $json['data']??[];
		$extend = $json['extend']??[];
		$url 	= $json['url']??'';
		$result = [
			'url'	=> $url,
            'msg' 	=> $msg,
            'code'  => $code,
            'time' 	=> time()
        ];
        if (!empty($data)) {
            $result['data'] = $data;
        }
        if (!empty($extend)) {
            foreach ($extend as $k => $v) {
                $result[$k] = $v;
            }
        }
		exit(json_encode($result));
	}
}
