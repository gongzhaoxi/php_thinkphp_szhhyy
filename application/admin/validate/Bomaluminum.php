<?php

namespace app\admin\validate;

use think\Validate;

class Bomaluminum extends Validate
{
    protected $rule = [
         'code'  => 'require|unique:bomAluminum',
         'name'  => 'require',
         'big'  => 'require',
         'small' => 'require',
    ];
    protected $message = [
        'code.require' => '物料编码不能为空',
        'code.unique' => '物料编码已存在',
        'name.require' => '物料名称不能为空',
        'big.gt' => '大面不能为空',
        'small.require' => '小面不能为空',
    ];
    protected $scene = [
        'add'  => ['code','name','big','small'],
    ];
    
}