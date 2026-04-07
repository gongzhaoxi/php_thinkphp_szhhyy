<?php
namespace app\model;
use app\model\BaseModel;
use traits\model\SoftDelete;

class SeriesProcess extends BaseModel
{
	protected $autoWriteTimestamp = true;
	//use SoftDelete;
    //protected $deleteTime = 'delete_time';

	public function process()
    {
        return $this->belongsTo('Process','process_id');
    }
	

}