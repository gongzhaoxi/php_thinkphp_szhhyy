<?php
namespace app\admin\services;

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
	    if(isset($query['status']) && $query['status'] !== '') {
			$where['a.status']	= ['=', $query['status']];
		}	
        if(!empty($query['create_time'])) {
			$create_time 			= explode('至',$query['create_time']);
			$where['a.create_time'] = ['between', strtotime(trim($create_time[0])),strtotime(trim($create_time[1]).' 23:59:59')];
		}		
		return $where;
    }
	
	
    /**
     * 查询车间异常信息
     */
    public static function detail($id)
	{
		return OrderFeedback::where('id',$id)->find();
    }	
	

    /**
     * 编辑车间异常
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'车间异常不存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除车间异常
     */
    public static function del($data)
    {
		try{
			OrderFeedback::where('id','in',$data['ids'])->delete();
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
    public static function handel($data)
    {
		try{
			OrderFeedback::where('id','in',$data['ids'])->update(['status'=>1]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }	

}
