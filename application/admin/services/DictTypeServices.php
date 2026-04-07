<?php
namespace app\admin\services;

use think\Db;
use app\model\{DictType};

/**
 * 字典控制器
 */
class DictTypeServices extends Base
{

    /**
     * 字典列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = '*';
        $list = DictType::where(self::where($query))->field($field)->order(['id'=>'asc'])->paginate($limit);
		$data = $list->items();
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
		return $where;
    }
	

    /**
     * 添加字典
     */
    public static function add($param)
    {
		try {
            DictType::create($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询字典信息
     */
    public static function detail($id)
	{
		return DictType::where('id',$id)->find();
    }	
	

    /**
     * 编辑字典
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'字典不存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除字典
     */
    public static function del($data)
    {
		try{
			DictType::where(self::where($data))->update(['delete_time'=>time()]);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	/**
     * 所有字典
     */
    public static function all($query=[])
    {
		return DictType::where(self::where($query))->field('id,name')->order(['id'=>'asc'])->select();
    }

}
