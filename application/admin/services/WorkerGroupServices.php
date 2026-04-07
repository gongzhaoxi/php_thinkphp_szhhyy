<?php
namespace app\admin\services;

use think\Db;
use app\model\{WorkerGroup,Process,Worker};

/**
 * 班组控制器
 */
class WorkerGroupServices extends Base
{

    /**
     * 班组列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = '*';
        $list = WorkerGroup::where(self::where($query))->field($field)->order(['id'=>'asc'])->paginate($limit);
		$data = $list->items();
		$process = Process::column('name','id');
		$worker = Worker::column('name','id');
		foreach($data as &$vo){
			$process_name = [];
			foreach($vo['process'] as $v){
				if(!empty($process[$v])){
					$process_name[] = $process[$v];
				}
			}
			$vo['process_name'] = implode(',',$process_name);		
			$monitor_name = [];
			foreach($vo['monitor'] as $v){
				if(!empty($worker[$v])){
					$monitor_name[] = $worker[$v];
				}
			}
			$vo['monitor_name'] = implode(',',$monitor_name);
			
		}
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
    /**
     * 获取查询条件
     */	
	public static function where($query=[])
    {
        $where = [];
        if(!empty($query['name'])) {
			$where['name'] 	= ['like', "%".$query['name']."%"];
		}
        if(!empty($query['ids'])) {
			$where['id'] 	= ['in', $query['ids']];
		}	
	    if(isset($query['status']) && $query['status'] !== '') {
			$where['status']= ['=', $query['status']];
		}	
		return $where;
    }
	

    /**
     * 添加班组
     */
    public static function add($param)
    {
		try {
            WorkerGroup::create($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询班组信息
     */
    public static function detail($id)
	{
		return WorkerGroup::where('id',$id)->find();
    }	
	

    /**
     * 编辑班组
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'班组不存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除班组
     */
    public static function del($data)
    {
		try{
			WorkerGroup::where(self::where($data))->update(['delete_time'=>time()]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	/**
     * 所有班组
     */
    public static function all($query=[])
    {
		return WorkerGroup::where(self::where($query))->field('id,name')->order(['id'=>'asc'])->select();
    }

}
