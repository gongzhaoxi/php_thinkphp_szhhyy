<?php

namespace app\admin\validate;

use think\Validate;

class Bomfive extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:bomAluminum',
         'name'  => 'require',
         'unit'  => 'require',
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',
    ];

    
}