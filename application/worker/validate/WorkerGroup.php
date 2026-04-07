<?php

namespace app\admin\validate;

use think\Validate;

class WorkerGroup extends Validate
{
    protected $rule = [
		'name'  => 'require|unique:worker_group',
    ];
    protected $message = [
        'name.require' => '班组名称不能为空',
        'name.unique' => '班组名称已存在',
    ];

    
}