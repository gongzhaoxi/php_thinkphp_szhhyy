<?php
namespace app\worker\services;

use think\Db;
use app\model\{Order,OrderProcess,WorkerGroup,Process,OrderFeedback,DictData};


class OrderServices extends Base
{
	public static function index($worker)
    {
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d',strtotime('-1 day'));
		$month_start = date('Y-m-01');
		$data = [];
		$data['today_order_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$today)->sum('b.area'),2);
		$data['today_product_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$today)->sum('b.product_area'),2);
		$data['today_count'] = OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$today)->sum('b.count');
		$data['yesterday_order_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$yesterday)->sum('b.area'),2);
		$data['yesterday_product_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$yesterday)->sum('b.product_area'),2);	
		$data['yesterday_count'] = OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date',$yesterday)->sum('b.count');	
		$data['month_order_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date','>=',$month_start)->sum('b.area'),2);
		$data['month_product_area'] = round(OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date','>=',$month_start)->sum('b.product_area'),2);	
		$data['month_count'] = OrderProcess::alias('a')->join('order b','a.order_id=b.id')->where('a.start_worker',$worker['name'])->where('end_date','>=',$month_start)->sum('b.count');
		return ['code'=>0,'data'=>$data];;
	}

    public static function detail($worker,$number)
    {
		try {
			$group = WorkerGroup::where('id',$worker['group_id'])->find();
			$order = Order::field('*,type as type_text')->where('number',$number)->find();
			if(empty($order['id'])){
				throw new \Exception("订单错误");
			}
			$can_start = false;
			$can_end = false;
			$process = OrderProcess::where('order_id',$order['id'])->where('start_date|end_date','')->order(['sort'=>'asc'])->find();
			if(empty($process['id'])){
				$process = OrderProcess::where('order_id',$order['id'])->order(['sort'=>'desc'])->find();
			}else{
				if(!empty($group['process']) && in_array($process['process_id'],$group['process'])){
					if(!$process['start_date']){
						$can_start = true;
					}
					if($process['start_date'] && !$process['end_date']){
						$can_end = true;
					}	
				}	
			}
			$my_process = [];
			if(!empty($group['process'])){
				$my_process = OrderProcess::where('order_id',$order['id'])->where('process_id','in',$group['process'])->order(['sort'=>'asc'])->select();
			}
			$feedback_type = DictData::where('type_id',1)->where('status',1)->order(['sort'=>'asc','id'=>'asc'])->select();;
			return ['code'=>0,'data'=>['order'=>$order,'process'=>$process,'can_start'=>$can_start,'can_end'=>$can_end,'my_process'=>$my_process,'feedback_type'=>$feedback_type]];
        }catch (\Exception $e){
			return ['msg'=>$e->getMessage(),'code'=>1];
        }
    }
	
    public static function start($worker,$number)
    {
		$res = self::detail($worker,$number);
		if($res['code']){
			return $res;
		}
		if(!$res['data']['can_start']){
			return ['msg'=>'操作失败','code'=>1];
		}
		try {
			OrderProcess::where('id',$res['data']['process']['id'])->update(['start_worker'=>$worker['name'],'start_date'=>date('Y-m-d')]);
			return ['code'=>0,'data'=>[]];
        }catch (\Exception $e){
			return ['msg'=>$e->getMessage(),'code'=>1];
        }
    }	
	
    public static function end($worker,$number)
    {
		$res = self::detail($worker,$number);
		if($res['code']){
			return $res;
		}
		if(!$res['data']['can_end']){
			return ['msg'=>'操作失败','code'=>1];
		}
		try {
			OrderProcess::where('id',$res['data']['process']['id'])->update(['end_worker'=>$worker['name'],'end_date'=>date('Y-m-d')]);
			if(Process::where('id',$res['data']['process']['process_id'])->value('is_end')){
				Order::where('id',$res['data']['order']['id'])->update(['production_status'=>2,'store_date'=>date('Y-m-d')]);
			}
			return ['code'=>0,'data'=>[]];
        }catch (\Exception $e){
			return ['msg'=>$e->getMessage(),'code'=>1];
        }
    }	


    public static function feedback($worker,$number,$data)
    {
		$res = self::detail($worker,$number);
		if($res['code']){
			return $res;
		}
		if(!in_array($data['order_process_id'],array_column($res['data']['my_process'],'id'))){
			return ['msg'=>'操作失败','code'=>1];
		}
		if(OrderFeedback::where('status',0)->where('order_process_id',$data['order_process_id'])->count()){
			return ['msg'=>'已报异常','code'=>1];
		}
		try {
			$data['order_id'] = $res['data']['order']['id'];
			$data['worker'] = $worker['name'];
			OrderFeedback::create($data);
			return ['code'=>0,'data'=>[]];
        }catch (\Exception $e){
			return ['msg'=>$e->getMessage(),'code'=>1];
        }
    }	


}
