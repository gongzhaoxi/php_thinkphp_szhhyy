<?php

namespace app\admin\validate;

use think\Validate;

class Process extends Validate
{
    protected $rule = [
		'name'  => 'require|unique:process',
		'sort' 	=> 'require',
    ];
    protected $message = [
        'name.require' => '工序名称不能为空',
        'name.unique' => '工序名称已存在',
        'sort.require' => '排序不能为空'
    ];

    
}