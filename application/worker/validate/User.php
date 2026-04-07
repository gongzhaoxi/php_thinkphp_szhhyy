<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
         'login_name'  => 'require|unique:user',
         'login_password' => 'require',
    ];
    protected $message = [
        'login_name.require' => '登陆账户不能为空',
        'login_name.unique' => '此账户已存在',
        'login_password.require' => '登陆密码不能为空'
    ];

    
}