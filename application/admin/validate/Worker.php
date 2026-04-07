<?php

namespace app\admin\validate;

use think\Validate;

class Worker extends Validate
{
    protected $rule = [
		'username'  => 'require|unique:worker',
		'password'  => 'require',
		'name'  	=> 'require',
    ];
    protected $message = [
        'username.require' => '用户名工号不能为空',
        'username.unique' => '用户名工号已存在',
		'password.require' => '密码不能为空',
		'name.require' => '真实姓名不能为空',
    ];

    
}