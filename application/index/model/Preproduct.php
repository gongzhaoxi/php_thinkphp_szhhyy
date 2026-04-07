<?php

namespace app\index\model;

use think\Model;

class Preproduct extends Model
{
    public function pregxlist()
    {
        return $this->hasMany('PreproductGx','pre_id');
    }
   
    public function order()
    {
        return $this->belongsTo('order','orderid','id')->bind(['ordernum','unique_sn','uname','color','pname']);
    }
}
