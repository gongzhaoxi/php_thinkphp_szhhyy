<?php

namespace app\index\model;

use think\Model;

class Order extends Model
{
    public function getAddtimeAttr($value)
    {
        return date('Y-m-d',$value);
    }
    
    public function getEndtimeAttr($value)
    {
        return date('Y-m-d',$value);
    }
    
    public function flowCheck()
    {
        return $this->hasMany('FlowCheck', 'id', 'id');
    }
    
    public function preproduct()
    {
        return $this->hasOne('Preproduct','id','id');
    }
   
}
