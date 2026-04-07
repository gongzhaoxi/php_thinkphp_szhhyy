<?php
namespace app\admin\services;

use think\Db;
use app\model\{WorkerGroup,Worker};

/**
 * 员工控制器
 */
class WorkerServices extends Base
{

    /**
     * 员工列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = '*';
        $list = Worker::where(self::where($query))->field($field)->order(['id'=>'asc'])->paginate($limit);
		$data = $list->items();
		$group = WorkerGroup::column('name','id');
		foreach($data as &$vo){
			$vo['group_name'] = !empty($group[$vo['group_id']])?$group[$vo['group_id']]:'';
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
     * 添加员工
     */
    public static function add($param)
    {
		try {
            Worker::create($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询员工信息
     */
    public static function detail($id)
	{
		return Worker::where('id',$id)->find();
    }	
	

    /**
     * 编辑员工
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'员工不存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除员工
     */
    public static function del($data)
    {
		try{
			Worker::where(self::where($data))->update(['delete_time'=>time()]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	/**
     * 所有员工
     */
    public static function all($query=[])
    {
		return Worker::where(self::where($query))->field('id,name')->order(['id'=>'asc'])->select();
    }

}
