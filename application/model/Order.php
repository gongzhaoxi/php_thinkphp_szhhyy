<?php
namespace app\model;
use app\model\BaseModel;

class Order extends BaseModel
{

	public function process()
    {
        return $this->hasMany('OrderProcess','order_id')->order('sort','asc');
    }
	
	public function price()
    {
        return $this->hasMany('OrderPrice','order_id')->order('op_id','asc');
    }
	
	public function getTypeTextAttr($value,$data)
    {
		$arr = config('order_type');
        return $arr[$value]??'';
    }	
	
	public function paidRecord()
    {
        return $this->hasMany('PaidRecord','order_id');
    }	
	
}