<?php

namespace app\admin\validate;

use think\Validate;

class DictData extends Validate
{
    protected $rule = [
		'name'  => 'require',
		'sort' 	=> 'require',
    ];
    protected $message = [
        'name.require' => '数据名称不能为空',
        'name.unique' => '数据名称已存在',
        'sort.require' => '排序不能为空'
    ];

    
}