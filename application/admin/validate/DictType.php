<?php

namespace app\admin\validate;

use think\Validate;

class DictType extends Validate
{
    protected $rule = [
		'name'  => 'require|unique:dict_type',
    ];
    protected $message = [
        'name.require' => '名称不能为空',
        'name.unique' => '名称已存在',
    ];

    
}