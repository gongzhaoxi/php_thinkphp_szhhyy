<?php

namespace app\admin\validate;

use think\Validate;

class Bomhands extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:bomHands',
         'name'  => 'require',
         'width'  => 'require',
         'height' => 'require',
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',

    ];
    
}