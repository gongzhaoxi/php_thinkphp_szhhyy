<?php
namespace app\worker\services;

use think\Db;
use app\model\{OrderFeedback,Process,Worker};

/**
 * 车间异常控制器
 */
class OrderFeedbackServices extends Base
{

    /**
     * 车间异常列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = 'a.*,b.dealer,b.number,c.process_name';
        $list = OrderFeedback::alias('a')->join('order b','a.order_id=b.id')->join('order_process c','a.order_process_id=c.id')->where(self::where($query))->field($field)->order(['id'=>'asc'])->paginate($limit);
		$data = $list->items();
		
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
    /**
     * 获取查询条件
     */	
	public static function where($query=[])
    {
        $where = [];
		$where['a.status']	= ['=', 0];
		
        if(!empty($query['dealer'])) {
			$where['b.dealer'] 	= ['like', "%".$query['dealer']."%"];
		}
        if(!empty($query['number'])) {
			$where['b.number'] 	= ['like', "%".$query['number']."%"];
		}
        if(!empty($query['process_name'])) {
			$where['c.process_name'] 	= ['like', "%".$query['process_name']."%"];
		}
        if(!empty($query['type'])) {
			$where['a.type'] 	= ['like', "%".$query['type']."%"];
		}		
        if(!empty($query['worker'])) {
			$where['a.worker'] 	= ['like', "%".$query['worker']."%"];
		}			
        if(!empty($query['ids'])) {
			$where['a.id'] 		= ['in', $query['ids']];
		}	
        if(!empty($query['create_time'])) {
			$create_time 			= explode('至',$addtime);
			$where['a.create_time'] = ['>=', strtotime(trim($create_time[0]))];
			$where['a.create_time'] = ['<=', strtotime(trim($create_time[1]).' 23:59:59')];
		}		
		return $where;
    }
}
