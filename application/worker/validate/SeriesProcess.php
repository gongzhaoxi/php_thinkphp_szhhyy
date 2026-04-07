<?php

namespace app\admin\validate;

use think\Validate;

class SeriesProcess extends Validate
{
    protected $rule = [
		'series_id'  => 'require',
		'process_id'  => 'require',
		'sort'  => 'require',
    ];
    protected $message = [
        'name.require' => '班组名称不能为空',
        'name.unique' => '班组名称已存在',
    ];

    
}