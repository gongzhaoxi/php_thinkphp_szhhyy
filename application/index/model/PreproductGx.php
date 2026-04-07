<?php

namespace app\index\model;

use think\Model;

class PreproductGx extends Model
{
    public function preproduct()
    {
        return $this->belongsTo('Preproduct', 'pre_id', 'id');
    }
   
    public function gxlist()
    {
        return $this->belongsTo('gx_list','gxid','id')->bind('dname');
    }
}
