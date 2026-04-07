<?php

namespace app\worker\controller;

use think\Db;
use app\worker\services\{LoginServices};

class Login extends Base
{

	protected $noLogin = ['login/index'];

    /**
     * 用户登录
     * @param string $name 账号
     * @param string $password 密码
     */
    public function index()
    {
		$data 		= $this->request->only(['username','password']);
		$validate 	= $this->validate($data, 'Login');
		if($validate !== true) {
			$this->error($validate);
		}
		return $this->getJson(LoginServices::login($data));
    }

}
