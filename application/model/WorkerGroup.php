<?php
namespace app\model;
use app\model\BaseModel;
use traits\model\SoftDelete;

class WorkerGroup extends BaseModel
{
	protected $autoWriteTimestamp = true;
	use SoftDelete;
    protected $deleteTime = 'delete_time';

	public function setProcessAttr($value,$data){
		return is_array($value)?implode(',',$value):$value;
	}
	
	public function getProcessAttr($value,$data){
		return $value?explode(',',$value):[];
	}	
	
	public function setMonitorAttr($value,$data){
		return is_array($value)?implode(',',$value):$value;
	}
	
	public function getMonitorAttr($value,$data){
		return $value?explode(',',$value):[];
	}	
	

}