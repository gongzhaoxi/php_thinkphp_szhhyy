<?php
namespace app\model;

use think\Model;


class BaseModel extends Model
{
    public function getDeleteTimeAttr($value, $data)
    {
		if(!$value){
			return '';
		}
		if(is_numeric($value)){
			return date('Y-m-d H:i:s',$value);
		}
        return $value;
    }
	
}