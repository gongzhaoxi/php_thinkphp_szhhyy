<?php
namespace app\admin\services;

use think\Db;
use app\model\{Order,Process,Series,SeriesProcess,OrderProcess,WorkerGroup};

/**
 * 班组控制器
 */
class ProductionServices extends Base
{

    /**
     * 班组列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = '*';
        $list = Order::where(self::where($query))->field($field)->order(['id'=>'desc'])->paginate($limit);
		$data = $list->items();
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
    /**
     * 获取查询条件
     */	
	public static function where($query=[])
    {
        $where 				= [];
		$where[''] 			= Db::raw(' status = 4 or status2 = 6 ');
        if(!empty($query['number'])) {
			$where['number']= ['like', "%".$query['number']."%"];
		}
        if(!empty($query['ids'])) {
			$where['id'] 	= ['in', $query['ids']];
		}	
	    if(isset($query['production_status']) && $query['production_status'] !== '') {
			$where['production_status']= ['=', $query['production_status']];
		}
        if(!empty($query['production_date'])) {
			$production_date = is_array($query['production_date'])?$query['production_date']:explode('至',$query['production_date']);
			$where['production_date'] 	= ['between', [trim($production_date[0]),trim($production_date[1])]];
		}		
		return $where;
    }
	

    /**
     * 添加班组
     */
    public static function add($param)
    {
		try {
            Production::create($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询班组信息
     */
    public static function detail($id)
	{
		$order =  Order::with(['price'])->where('id',$id)->find()->toArray();
		$order_process = OrderProcess::field('process_id as id,process_name as name,start_date,end_date,start_worker,end_worker')->where('order_id',$order['id'])->order(['sort'=>'asc','id'=>'asc'])->select();
		if(empty($order_process)){
			$series_id = $order['price'][0]['series_id'];
			$series = Series::where('id',$series_id)->find();
			while(!empty($series['parent_id'])){
				$series_id = $series['parent_id'];
				$series = Series::where('id',$series_id)->find();
			}
			$order_process = SeriesProcess::alias('a')->join('process b','a.process_id = b.id','LEFT')->field('b.id,b.name')->where('a.series_id',$series_id)->order(['b.sort'=>'asc','b.id'=>'asc'])->select();
		}		
		$order['process'] = $order_process;
		return $order;
    }
	

    /**
     * 编辑班组
     */
    public static function edit($param)
    {
		$order 			= Order::with(['price'])->where('id',$param['order_id'])->find();
		if(empty($order['id'])){
			return ['msg'=>'订单不存在','code'=>1];
		}
		try {
			$order_process 	= OrderProcess::where('order_id',$order['id'])->order(['sort'=>'asc','id'=>'asc'])->column('id','process_id');
			$process 		= Process::where('id','in',$param['process_id'])->column('name','id');
			$group 			= WorkerGroup::field('id,name,process')->select();
			$add 			= [];
			$edit 			= [];
			foreach($param['process_id'] as $k=>$vo){
				$group_name = '';
				foreach($group as $item){
					if(in_array($vo,$item['process'])){
						$group_name = $item['name'];
						break;
					}
				}
				if(empty($order_process[$vo])){
					$add[]	= ['sort'=>$k,'group_name'=>$group_name,'process_id'=>$vo,'process_name'=>$process[$vo],'order_id'=>$order['id'],'order_price_id'=>$order['price'][0]['op_id'],'series_id'=>$order['price'][0]['series_id']];
				}else{
					$edit[]	= ['sort'=>$k,'group_name'=>$group_name,'id'=>$order_process[$vo],'process_name'=>$process[$vo],'order_price_id'=>$order['price'][0]['op_id'],'series_id'=>$order['price'][0]['series_id']];
				}
			}
			$delete 		= array_diff(array_keys($order_process),$param['process_id']);
			if($add){
				(new OrderProcess)->saveAll($add);
			}
			if($edit){
				(new OrderProcess)->saveAll($edit);
			}
			if($delete){
				OrderProcess::where('process_id','in',$delete)->where('order_id','=',$order['id'])->delete();
			}
			if($order['production_status'] == 0){
				$order->save(['production_status'=>1,'production_date'=>date('Y-m-d'),'store_date'=>'']);
			}
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }


    public static function del($data)
    {
		try{
			Order::where('id','in',$data['ids'])->update(['production_status'=>0,'production_date'=>'','store_date'=>'']);
			OrderProcess::where('order_id','in',$data['ids'])->delete();
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }


    public static function process($query=[],$limit=10)
    {
		$field = '*';
        $list = OrderProcess::where('order_id',$query['order_id'])->field($field)->order(['id'=>'desc'])->paginate($limit);
		$data = $list->items();
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
	

    public static function start($id,$worker)
    {
		try{
			OrderProcess::where('id',$id)->update(['start_date'=>date('Y-m-d'),'worker'=>$worker]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
    public static function end($id,$worker)
    {
		try{
			$order_process = OrderProcess::where('id',$id)->find();
			$order_process->save(['end_date'=>date('Y-m-d'),'worker'=>$worker]);
			if(Process::where('id',$order_process['process_id'])->value('is_end')){
				Order::where('id',$order_process['order_id'])->update(['production_status'=>2,'status'=>7,'store_date'=>date('Y-m-d')]);
			}
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	public static function processDetail($id)
	{
		$model =  OrderProcess::where('id',$id)->find();
		return $model;
    }
	
	
    public static function report($param)
    {
		$model 			= OrderProcess::where('id',$param['id'])->find();
		if(empty($model['id'])){
			return ['msg'=>'数据不存在','code'=>1];
		}
		try {
			$model->save($param);
			if(empty($model['start_date']) && Process::where('id',$model['process_id'])->value('is_end')){
				Order::where('id',$model['order_id'])->update(['production_status'=>1,'status'=>4]);
			}
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
	public static function stat($query=[],$limit=10)
    {
		$field = 'a.start_date,a.end_date,a.start_worker,a.process_name,a.end_worker,a.group_name,b.area as order_area,b.number,b.type,b.dealer,b.send_address,b.production_date,b.store_date,c.name,c.material,c.color_name,c.product_area,c.area as price_area,c.count,c.flower,c.window_type_a,c.escape_type_a,c.yarn_color';
        $list = OrderProcess::alias('a')->join('order b','a.order_id=b.id')->join('order_price c','a.order_price_id=c.op_id')
		->where(self::statWhere($query))->field($field)->order(['a.id'=>'desc'])->paginate($limit);
		$data = $list->items();
		$order_type = config('order_type');
		foreach($data  as &$vo){
			$vo = $vo->toArray();
			$yarn_color = $vo['yarn_color']?unserialize($vo['yarn_color']):[];
			$vo['yarn_color'] = $yarn_color['name']??'';
			$flower = $vo['flower']?unserialize($vo['flower']):[];
			$vo['flower'] = $flower['name']??'';
			$vo['type'] = $order_type[$vo['type']]??'';
		}
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
	
	public static function statWhere($query=[])
    {
        $where 				= [];
        if(!empty($query['number'])) {
			$where['b.number']= ['like', "%".$query['number']."%"];
		}
	    if(isset($query['production_status']) && $query['production_status'] !== '') {
			$where['b.production_status']= ['=', $query['production_status']];
		}	
		if(!empty($query['store_date'])) {
			$store_date 	= explode('至',$query['store_date']);
			$where['b.store_date'] = [['>=', (trim($store_date[0]))],['<=', (trim($store_date[1]))]];
		}	
		return $where;
    }
	
}
