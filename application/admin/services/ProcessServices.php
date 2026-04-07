<?php
namespace app\admin\services;

use think\Db;
use app\model\Process;

/**
 * 工序控制器
 */
class ProcessServices extends Base
{

    /**
     * 工序列表
     */
    public static function list($query=[],$limit=10)
    {

		$field = '*';
        $list = Process::where(self::where($query))->field($field)->order(['sort'=>'asc','id'=>'asc'])->paginate($limit);
		return ['code'=>0,'data'=>$list->items(),'extend'=>['count' => $list->total(), 'limit' => $limit]];
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
     * 添加工序
     */
    public static function add($param)
    {
		try {
            Process::create($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询工序信息
     */
    public static function detail($id)
	{
		return Process::where('id',$id)->find();
    }	
	

    /**
     * 编辑工序
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'工序不存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除工序
     */
    public static function del($data)
    {
		try{
			Process::where(self::where($data))->update(['status'=>0]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	/**
     * 所有工序
     */
    public static function all($query=[])
    {
		return Process::where(self::where($query))->field('id,name')->order(['sort'=>'asc','id'=>'asc'])->select();
    }

}
