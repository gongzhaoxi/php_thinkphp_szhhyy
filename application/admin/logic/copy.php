<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
use tree\Tree;

/**
 * 复制功能
 */
class copy extends Model
{
    protected function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 复制组合单
     * @param type $groupId 组合单id
     */
    public function groupOrder($groupId)
    {
        //插入组合单表
        $time = time();
        $groupSql = "insert into erp_order_group(order_id,width,height,area,price_count,calculate_count,total_price,addtime) select order_id,width,height,area,price_count,calculate_count,total_price,$time from erp_order_group where og_id=$groupId";
        Db::name('order_group')->execute($groupSql);
        $ogId = Db::name('order_group')->getLastInsID('og_id');
        
        $product = Db::name('order_price')->alias('a')
                        ->join('order_calculation b','a.op_id=b.op_id')
                        ->where('a.og_id',$groupId)
                        ->select();
        $price = [];
        $calculate = [];
        foreach($product as $k => $v){
            $priceData = [
                'order_id'=>$v['order_id'],'series_id'=>$v['series_id'],'name'=>$v['name'],'material'=>$v['material'],'flower_type'=>$v['flower_type'],
                'flower_id'=>$v['flower_id'],'flower_pic'=>$v['flower_pic'],'color_name'=>$v['color_name'],'technology'=>$v['technology'],'flower'=>$v['flower'],
                'alum_color'=>$v['alum_color'],'flower_color'=>$v['flower_color'],'alum_color_id'=>$v['alum_color_id'],'flower_color_id'=>$v['flower_color_id'],
                'alum_name'=>$v['alum_name'],'flower_name'=>$v['flower_name'],'yarn_color'=>$v['yarn_color'],'yarn_price'=>$v['yarn_price'],
                'yarn_thickness'=>$v['yarn_thickness'],'window'=>$v['window'],'arc_height'=>$v['arc_height'],'arc_length_count'=>$v['arc_length_count'],
                'order_type'=>$v['order_type'],'og_id'=>$ogId,'addtime'=>time()
                ];
            $opId = Db::name('order_price')->insertGetId($priceData);
            $calculateData = ['op_id'=>$opId,'spacin'=>$v['spacing'],'lock_position'=>$v['lock_position']];
            $res = Db::name('order_calculation')->insert($calculateData);
        }
        if($opId && $res){
            return true;
        }
        return false;
    }
    
    
    
    /**
     * 递归获取子类及自身数组
     * @param array $result 数组
     * @param int $id 要获取子类的父id
     * @return array
     */
    public function getChild($result,$id=0,$level=0)
    {       
        $child = [];
        if(is_array($result)){
            foreach ($result as $k => $v) {
                //加上自身id数组
                if($level == 0 && $id == $v['id']){
                    $v['level'] = $level;
                    $child[] = $v;
                }
                if($v['parent_id'] == $id){     
                    $v['level'] = $level+1; //加入树层级
                    $child[] = $v;
                    $child = array_merge($child,$this->getChild($result, $v['id'],$level+1));
                }
            }
        }
        return $child;
    }
    
    /**
     * 系列复制功能
     * @param int $seriesId 要复制的系列id
     * @return bool true/false
     */
    public function series($result,$seriesId)
    {
        $list = $this->getChild($result, $seriesId);
        //旧父id与新父id的对应关系
        $pid[$list[0]['parent_id']] = $list[0]['parent_id'];$ssql = '';
        foreach($list as $k =>$v){
            if(isset($pid[$v['parent_id']])){
                $insetPid = $pid[$v['parent_id']];
            }else{
                $insetPid = $prev;
            }
            $data = ['parent_id'=>$insetPid,'name'=>$v['name'],'min_area'=>$v['min_area'],'price'=>$v['price']];

            $prev= Db::name('series')->insertGetId($data);
            $pid[$v['parent_id']] = $insetPid;   
            
            //系列纱网
            $yarnSqlSql = "insert into erp_series_yarn (yarn_id,price,series_id) select yarn_id,price,$prev from erp_series_yarn where series_id={$v['id']};";
            Db::execute($yarnSqlSql);
            //系列结构
            $structureSql = " insert into erp_series_structure (structure_id,`name`,`price`,series_id,srf_id,scf_id,ruler_name,calculate_name) "
                    . "select structure_id,`name`,`price`,$prev,srf_id,scf_id,ruler_name,calculate_name from `erp_series_structure` where series_id={$v['id']};";
            Db::execute($structureSql);
            //系列把手位
            $handsSql = "insert into `erp_series_hands` (hands_id,`price`,series_id) select hands_id,`price`,$prev from `erp_series_hands` where series_id={$v['id']};";
            Db::execute($handsSql);
            //系列花件
            $flowerSql = "insert into `erp_series_flower` (flower_id,`name`,`price`,series_id) select flower_id,`name`,`price`,$prev from `erp_series_flower` where series_id={$v['id']};";
            Db::execute($flowerSql);
            //系列物料绑定
            $bomSql = "insert into `erp_series_bom` (`type`,one_level,two_level,take,frame_take_fan,small_frame,small_fan,series_id,ZK_B_KDK,ZK_B_KDS) select `type`,one_level,two_level,take,frame_take_fan,small_frame,small_fan,ZK_B_KDK,ZK_B_KDS,$prev from "
                    . "`erp_series_bom` where series_id={$v['id']};";
                    Db::execute($bomSql);
            //系列颜色
            $colorSql = "insert into `erp_series_color` (`level`,`type`,color_id,relation,all_relation,`price`,series_id) select "
                    . "`level`,`type`,color_id,relation,all_relation,`price`,$prev from `erp_series_color` where series_id={$v['id']};";
                    Db::execute($colorSql);
//            $ssql .= $yarnSql.$structureSql.$flowerSql.$bomSql.$colorSql;
            
        }
        if($prev){
            return true;
        }
        return false;
    }
    
}
