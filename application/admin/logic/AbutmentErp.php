<?php

namespace app\admin\logic;

use think\Db;

/**
 * 对接erp 逻辑类
 */
class AbutmentErp
{
    /**
     * 获取ERP扣库存的 sql
     * @param $orderId 订单id
     * @return string
     */
    public function getStockSql($orderId)
    {
        $defalut = $this->getMaterialStock($orderId);
        $color = $this->getColorMaterialStock($orderId);
        $data = array_merge($defalut,$color);
        //考虑颜色和不考虑颜色有相同的编码时，将扣的库存数相加
        $temp = [];
        foreach ($data as $k => $v) {
            $temp[$v['guid']][] = $v['stock'];
        }
        $list = [];
        foreach ($temp as $k => $v) {
            $stock = array_sum($v);
            $list[] = ['guid'=>$k,'stock'=>$stock];
        }
        if($data){
            $guid = array_column($list,'guid');
            //拼接sql 批量更新
            $sql = "update materialmx set StockNum= case MaterialGuID ";
            foreach ($list as $k => $v) {
                $sql .= "when '{$v['guid']}' then StockNum-{$v['stock']} ";
            }
            $sql .= " end where MaterialGuID in ('".implode("','",$guid)."')";
        }else{
            $sql = "select * from materialmx limit 1";
        }

        return $sql;
    }

    /**
     * 获取不考虑颜色的  库存数
     * @param $orderId 订单id
     * @return array
     */
    public function getMaterialStock($orderId)
    {
        //先获取此条订单 系列绑定的物料编码
        $data = Db::name('series_stock_material')->alias('a')
            ->field('a.*,b.product_area,b.op_id')
            ->join('order_price b','a.series_id=b.series_id')
            ->whereIn('b.order_id',$orderId)
            ->whereIn('b.order_type',[0,3])
            ->where('a.is_color',0)
            ->order('b.op_id')
            ->select();
        if(!$data){
            return [];
        }
        $number = array_column($data,'material_number');
        $db2 = Db::connect('database.db2');
        //中间表的数据
        $thirdMaterial = $db2->table('material')->field('MaterialID,MaterialGuID,Length')->whereIn('MaterialID',$number)->select();
        //以物料编码为键
        $lengthArray = [];
        $attach = [];//中间表的 物料编码-物料id
        foreach ($thirdMaterial as $k => $v) {
            $lengthArray[$v['MaterialID']] = $v['Length'];
            $attach[$v['MaterialID']] = $v['MaterialGuID'];
        }
        $list = [];
        foreach ($data as $k => $v) {
            $tempLength = isset($lengthArray[$v['material_number']])?$lengthArray[$v['material_number']]:0;
            if($tempLength == 0){
                continue;
            }
            //库存 = (产品面积*(1平方米单位含量/1000))/中间表的长度
            $stock = round(($v['product_area']*($v['unit_content']/1000))/$tempLength,0);
            //相同物料编码的汇总起来
            $list[$v['material_number']][] = $stock;
        }
        //在累加
        $update = [];
        foreach ($list as $k => $v) {
            $sum = array_sum($v);
            $update[] = ['guid'=>$attach[$k],'stock'=>$sum];
        }
        return $update;
    }

    /**
     * 获取考虑整体颜色的 库存数
     * @param $orderId 订单id
     * @return array
     */
    public function getColorMaterialStock($orderId)
    {
        //先获取此条订单 考虑整体颜色 系列绑定的物料编码
        $data = Db::name('series_stock_material')->alias('a')
            ->field('a.*,b.product_area,c.code,b.alum_color')
            ->join('order_price b','a.series_id=b.series_id')
            ->join('bom_color c','b.alum_color_id=c.id','left')
            ->whereIn('b.order_id',$orderId)
            ->whereIn('b.order_type',[0,3])
            ->where('a.is_color',1)
            ->select();
        if(!$data){
            return [];
        }
        $number = [];
        foreach ($data as $k => $v) {
            //判断颜色是普通烤漆或特色烤漆时 不拼接编码
            $colorArray = unserialize($v['alum_color']);
            $flag = true;
            foreach ($colorArray as $k2 => $v2) {
                if(in_array($v2,['特殊烤漆','普通烤漆'])){
                    $flag = false;
                    break;
                }
            }
            if($flag){
                $number[] = $v['material_number'].'-'.$v['code'];//考虑整体颜色的 拼接颜色编码 去查中间库
            }else{
                $number[] = $v['material_number'];
            }
        }
        $db2 = Db::connect('database.db2');
        //中间表的数据
        $thirdMaterial = $db2->table('material')->field('MaterialID,MaterialGuID,Length')->whereIn('MaterialID',$number)->select();
        //以物料编码为键
        $lengthArray = [];
        $attach = [];//中间表的 物料编码-物料id
        foreach ($thirdMaterial as $k => $v) {
            $lengthArray[$v['MaterialID']] = $v['Length'];
            $attach[$v['MaterialID']] = $v['MaterialGuID'];
        }
        $list = [];
        foreach ($data as $k => $v) {
            $tempLength = isset($lengthArray[$v['material_number']])?$lengthArray[$v['material_number']]:0;
            if($tempLength == 0){
                continue;
            }
            //库存 = (产品面积*(1平方米单位含量/1000))/中间表的长度
            $stock = round(($v['product_area']*($v['unit_content']/1000))/$tempLength,0);
            //相同物料编码的汇总起来
            $list[$v['material_number']][] = $stock;
        }
        //在累加
        $update = [];
        foreach ($list as $k => $v) {
            $sum = array_sum($v);
            $update[] = ['guid'=>$attach[$k],'stock'=>$sum];
        }
        return $update;
    }


}