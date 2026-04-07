<?php

namespace app\admin\validate;

use think\Validate;

class Structure extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:structure',
         'path_url' => 'unique:structure'
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',
        'path_url'    => '标尺脚本名称已存在'
    ];

    
}