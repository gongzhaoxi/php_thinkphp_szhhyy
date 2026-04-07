<?php

namespace app\admin\validate;

use think\Validate;

class Production extends Validate
{
    protected $rule = [
		'process_id'  => 'require|array',
		'order_id|订单'  => 'require',
    ];
    protected $message = [
        'process_id.require' => '工序不能为空',
        'process_id.array' => '工序不能为空',
    ];

    
}