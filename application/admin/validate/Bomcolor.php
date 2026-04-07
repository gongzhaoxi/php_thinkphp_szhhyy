<?php

namespace app\admin\validate;

use think\Validate;

class Bomcolor extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:bomColor',
         'name'  => 'require',
         'attr'  => 'require',
         'pic'  => 'require',
         'is_self'  => 'require',
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',

    ];
    
}