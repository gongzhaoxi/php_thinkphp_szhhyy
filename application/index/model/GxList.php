<?php

namespace app\index\model;

use think\Model;

class GxList extends Model
{
    public function prelist()
    {
        return $this->hasOne('PreproductGx','gxid','id');
    }
}
