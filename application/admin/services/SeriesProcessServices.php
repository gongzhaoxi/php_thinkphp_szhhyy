<?php
namespace app\admin\services;

use think\Db;
use app\model\{SeriesProcess,Process,Worker};

/**
 * 系列工序控制器
 */
class SeriesProcessServices extends Base
{

    /**
     * 系列工序列表
     */
    public static function list($query=[],$limit=10)
    {
		$field = '*';
        $list = SeriesProcess::where(self::where($query))->field($field)->order(['sort'=>'asc','id'=>'asc'])->paginate($limit);
		$data = $list->items();
		$process = Process::column('name','id');
		$series = Db::name('series')->field('id,name')->where('parent_id',0)->where('name','<>','')->column('name','id');
		foreach($data as &$vo){
			$vo['process_name'] = empty($process[$vo['process_id']])?'':$process[$vo['process_id']];		
			$vo['series_name'] = empty($series[$vo['series_id']])?'':$series[$vo['series_id']];	
			
		}
		return ['code'=>0,'data'=>$data,'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }
	
    /**
     * 获取查询条件
     */	
	public static function where($query=[])
    {
        $where = [];
        if(!empty($query['ids'])) {
			$where['id'] 	= ['in', $query['ids']];
		}	
	    if(isset($query['process_id']) && $query['process_id'] !== '') {
			$where['process_id']= ['=', $query['process_id']];
		}	
	    if(isset($query['series_id']) && $query['series_id'] !== '') {
			$where['series_id']= ['=', $query['series_id']];
		}		
		return $where;
    }
	

    /**
     * 添加系列工序
     */
    public static function add($param)
    {
		$process_ids = SeriesProcess::where('series_id',$param['series_id'])->whereIn('process_id',$param['process_id'])->column('process_id');
		if($process_ids){
			return ['msg'=>Process::whereIn('id',$process_ids)->value('name').'工序已存在','code'=>1];
		}
		try {
			$process_ids = is_array($param['process_id'])?$param['process_id']:explode(',',$param['process_id']);
			foreach($process_ids as $vo){
				SeriesProcess::create(array_merge($param,['process_id'=>$vo]));
			}
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }
	
    /**
     * 查询系列工序信息
     */
    public static function detail($id)
	{
		return SeriesProcess::where('id',$id)->find();
    }	
	

    /**
     * 编辑系列工序
     */
    public static function edit($param)
    {
		$model = self::detail($param['id']??0);
		if(empty($model['id'])){
			return ['msg'=>'系列工序不存在','code'=>1];
		}
		if(SeriesProcess::where('series_id',$param['series_id'])->where('process_id',$param['process_id'])->where('id','<>',$model['id'])->count()){
			return ['msg'=>'数据已存在','code'=>1];
		}
		try {
			$model->save($param);
        }catch (\Exception $e){
			return ['msg'=>'操作失败'.$e->getMessage(),'code'=>1];
        }
    }

    /**
     * 删除系列工序
     */
    public static function del($data)
    {
		try{
			SeriesProcess::where(self::where($data))->delete();
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>0];
        }
    }
	
	/**
     * 所有系列工序
     */
    public static function all($query=[])
    {
		return SeriesProcess::where(self::where($query))->field('id,name')->order(['id'=>'asc'])->select();
    }
	
	
    public static function series()
    {
		return Db::name('series')->field('id,name')->where('parent_id',0)->where('name','<>','')->order(['sort'=>'asc','id'=>'asc'])->select();
    }	
	

}
