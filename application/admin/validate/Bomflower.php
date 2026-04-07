<?php

namespace app\admin\validate;

use think\Validate;

class Bomflower extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:bomFlower',
         'pic'  => 'require',
         'name'  => 'require',
         'min_width' => 'require',
         'min_height' => 'require',
         'max_width' => 'require',
         'max_height' => 'require',
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',
        'pic.require' => '图片不能为空'
    ];
    protected $scene = [
        'add'  => ['code','name','big','small'],
    ];
    
}