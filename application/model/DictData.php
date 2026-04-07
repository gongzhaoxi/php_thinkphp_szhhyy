<?php
namespace app\model;
use app\model\BaseModel;
use traits\model\SoftDelete;

class DictData extends BaseModel
{
	protected $autoWriteTimestamp = true;
	use SoftDelete;
    protected $deleteTime = 'delete_time';
	

}