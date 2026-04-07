<?php

namespace app\worker\validate;

use think\Validate;

class Login extends Validate
{
    protected $rule = [
		'username|用户名'  => 'require',
		'password|密码' 	=> 'require',
    ];
	
    protected $message = [

    ];
    
}