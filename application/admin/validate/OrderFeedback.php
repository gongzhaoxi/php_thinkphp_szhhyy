<?php

namespace app\admin\validate;

use think\Validate;

class OrderFeedback extends Validate
{
    protected $rule = [
		'order_process_id'  => 'require',
		'type'  => 'require',
    ];
    protected $message = [
        'name.require' => '班组名称不能为空',
        'name.unique' => '班组名称已存在',
    ];

    
}