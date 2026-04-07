<?php

namespace app\worker\controller;

use think\Controller;
use think\Db;


/**
 * 基类控制器
 */
class Base extends Controller
{

    protected $worker = [];   
	protected $noLogin = [];

    protected function _initialize()
    {
		$worker = cache($this->request->header('token'));
        if(empty($worker['id']) && !in_array(strtolower($this->request->controller() . '/' . $this->request->action()), $this->noLogin)) {
            $this->getJson(['code'=>1,'msg'=>'请先登录']);
        }
		$this->worker = $worker;
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
