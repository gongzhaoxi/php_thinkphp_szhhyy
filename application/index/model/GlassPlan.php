<?php

namespace app\index\model;

use think\Model;
use think\Db;

class GlassPlan extends Model
{
    protected $json = ['extension'];
    
    public function getAddtimeAttr($value)
    {
        return date('Y-m-d H:i',$value);
    }
    
    public function getExtensionAttr($value)
    {
        return json_decode($value,true);
    }

    public function getStatusTextAttr($value,$data)
    {
        if($data['book_count'] <= $data['back_count']){
            return '已回';
        }elseif($data['book_count'] > $data['back_count'] && $data['back_count'] != 0){
            return '已回部分';
        }else{
            return '未回';
        }
    }

    public function getStatusValueAttr($value,$data)
    {
        if($data['book_count'] <= $data['back_count']){
            return '1';
        }elseif($data['book_count'] > $data['back_count'] && $data['back_count'] != 0){
            return '2';
        }else{
            return '3';
        }
    }
    
    /**
     * 将自定义字段转换成列
     * @param type $data
     */
    public function mergeField($data)
    {
        $data = $data->toArray();
        $array = [];
        foreach ($data as $k => $v) {
            $array[] = array_merge($v,$v['extension']);
        }
        return $array;
    }
    
    /**
     * 列表数据
     */
    public function index($where)
    {
        $sql = $this->field('*,sum(book_count) as all_book,sum(back_count) as all_back,count(id) as all_count')
                ->group('batch')->order('id desc')
                ->buildSql();
        $data = $this->table($sql.' c')->where($where)->paginate('',FALSE,['query'=> input('get.')])                
            ->each(function($item,$key){
                if($item->all_book <= $item->all_back){
                    $item->status = 3;
                }elseif($item->all_back != 0){
                    $item->status = 2;
                }else{
                    $item->status = 1;
                }
                
            });            
        return $data;
    }
}
