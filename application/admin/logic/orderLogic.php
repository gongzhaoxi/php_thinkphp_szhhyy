<?php

namespace app\admin\logic;

use think\Console;
use think\Model;
use think\Db;

/**
 * 订单
 */
class orderLogic extends Model
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取表单数组的不为空的最后一个值
     * @param array $array 数组
     * @return int | string
     */
    public function getLastValue($array)
    {
        $lastId = 0;
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if ($value != '') {
                    $lastId = $value;
                }
            }
        }
        return $lastId;
    }

    /**
     * 递归获取当前id的所有父级数组
     * @param array $array 数组
     * @return array 键为id，值为名称的二维数组
     */
    public function getParentArray($array, $parentId = 0)
    {
        $parent = [];
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                if ($v['id'] == $parentId) {
                    $parent[$v['id']] = $v['name'];
                    $parent = $this->getParentArray($array, $v['parent_id'])+$parent;
                }
            }
        }
        return $parent;
    }

    /**
     * 获取多级栏目的数组
     * @param array $all 要获取父id的表所有数据
     * @param array $pid 当前值的数组
     * @return array 
     */
    public function getSortParent($all, $pid)
    {
        $parents = $this->getParentArray($all, $pid['parent_id']); //得到父级数组
        $self[$pid['id']] = $pid['name']; //自身数组
        $parents = $parents + $self;
//        ksort($parents); //合并数组并按键名排序
        return $parents;
    }

    /**
     * 计算价格和面积
     * @param array $data 表单提交的数据
     * @param int $series 系列id
     * @param int $alumColorId 铝型颜色id
     * @param int $flowerColorId 花件颜色id
     * @return array 
     */
    public function calculatePrice($data, $series, $alumColorId, $flowerColorId)
    {
        $allWidth = $data['all_width'] + intval($data['left_fly']) + intval($data['right_fly']);  //总宽
        $allHeight = $data['all_height'] + intval($data['top_fly']) + intval($data['bottom_fly']); //总高
        $arcHeight = $data['arc_height']; //弧高
        $windowType = $data['window_type_a']; //窗型   常规,飘窗,圆弧,内弧(拱)形窗,外弧(拱)形窗,内弧(拱)形护栏,外弧(拱)形窗护栏
        $escapeType = $data['escape_type_a']; //逃生窗 默认：没有逃生窗，有逃生窗
        $windowFace = $data['window']; //飘窗面数
        $arcLengthCount = $data['arc_length_count']; //弧长数量
        //纱网价格,近期有发现 yarn_color为空，而价格不为空的情况，故在此过滤下
        if($data['yarn_color'] == ''){
            $yarnPrice = 0;
        }else {
            $yarnPrice = $data['yarn_price'] != '' ? $data['yarn_price'] : 0;
        }
        $fivePrice = $data['five_price'] != '' ? floatval($data['five_price']) : 0; //五金价格
        $data['five_count'] = $data['five_count']?:0;

        $seriesRes = Db::name('series')->where('id', $data['series_id'])->find();
        $minArea = $seriesRes['min_area']; //最小面积
        $seriesPrice = $seriesRes['price']; //系列价格
        $seriesType = $seriesRes['type']; //系列类型   // 1 = 窗花,2 = 室内护栏,3 = 室外护栏,4 = 纱门,5 = 纱窗,6 = 围栏
        //弧长
//        if ($arcHeight == 0) {
//            $arcLength = 0;
//        } else {
//            $arcLength = 2 * ($allWidth / 2 / (sin(3.14159 - atan($allWidth / 2 / $arcHeight) * 2))) * (3.14159 - atan($allWidth / 2 / $arcHeight) * 2) / 1000;
//        }
        $arcLength = $data['arc_height']; //弧长即用弧高字段  -- 不改数据表字段了
        
        //产品面积 
        $productArea = round($allWidth * $allHeight / 1000000, 2);
        //报价面积 
        if ($productArea > $minArea) {
            $offerArea = $productArea;
        } else {
            $offerArea = $minArea;
        }
        //室内护栏--报价面积
        if ($seriesType == 2) {
            if ($allHeight <= 1000) {
            	if($allWidth <= 800){
            		$offerArea = round(800 / 1000, 2);  
            	} else {
            		$offerArea = round($allWidth / 1000, 2); 
            	}             
            }
            if ($allHeight > 1000) {
                if ($productArea > $minArea) {
                    $offerArea = $productArea;
                } else {
                    $offerArea = $minArea;
                }
            }
        }
        //室外护栏--报价面积
        if ($seriesType == 3) {
            if ($allHeight <= 1200) {
                if($allWidth <= 1000){
                	$offerArea = round(1000 / 1000, 2);
                } else {
                	$offerArea = round($allWidth / 1000, 2);
                }
            }
            if ($allHeight > 1200) {
                if ($productArea > $minArea) {
                    $offerArea = $productArea;
                } else {
                    $offerArea = $minArea;
                }
            }
        }
        //围栏--报价面积
        if($seriesType == 6){
            //单价上减10元
            if($allHeight<=1000){
               $seriesPrice=$seriesPrice; 
            }
            if($allHeight>1000&&$allHeight<=1100){
               $seriesPrice=$seriesPrice-10; 
            }
            if($allHeight>1100&&$allHeight<=1200){
               $seriesPrice=$seriesPrice-20; 
            }
            if($allHeight>1200&&$allHeight<=1300){
               $seriesPrice=$seriesPrice-30; 
            }
            if($allHeight>1300&&$allHeight<=1400){
               $seriesPrice=$seriesPrice-40; 
            }
            if($allHeight>1400&&$allHeight<=1500){
               $seriesPrice=$seriesPrice-50; 
            }
            if($allHeight>1500&&$allHeight<=1600){
               $seriesPrice=$seriesPrice-60; 
            }
            if($allHeight>1600&&$allHeight<=1700){
               $seriesPrice=$seriesPrice-70; 
            }
            if($allHeight>1700){
               $seriesPrice=$seriesPrice-80; 
            }
            if ($allHeight < 1100) {
                $offerArea = round($allWidth / 1000, 2);
            }
            if ($allHeight >= 1100) {
                if ($productArea > $minArea) {
                    $offerArea = $productArea;
                } else {
                    $offerArea = $minArea;
                }
            }
        }

        $otherAdd = [];       //存储非常规窗型和五金加价数据
        //五金加价
        if ($data['five_id'] != '') {
            $windowAddPrice = $fivePrice * $data['five_count'];
            if($windowAddPrice !=0){
            	$otherAdd[] = ['descript' => '五金加价:' . $fivePrice . '*' . $data['five_count'], 'value' => round($windowAddPrice, 2)];
            }  
        }

        // 非常规窗型加价 
        if ($windowType == '飘窗') {
            $windowAddPrice = $windowFace * 20;

            $otherAdd[] = ['descript' => '飘窗加价:' . $windowFace . '*' . '20', 'value' => $windowAddPrice];
        } 
        elseif ($windowType == '圆弧') {
            $lawangPrice = 0; //拉弯费
            $windowAddPrice = $arcLength / 1000 * 20 * $arcLengthCount + $lawangPrice;

            // $otherAdd[] = ['descript' => '拉弯加价:' . $lawangPrice, 'value' => $lawangPrice];
            $otherAdd[] = ['descript' => '圆弧长加价:' . round($arcLength / 1000, 2) . "M*20*$arcLengthCount", 'value' => round($arcLength / 1000 * 20 * $arcLengthCount, 2)];
        } elseif ($windowType == '内弧(拱)形窗' || $windowType == '外弧(拱)形窗') {
            $lawangPrice = 0; //拉弯费
            $windowAddPrice = $arcLength / 1000 * 20 * $arcLengthCount + $lawangPrice;

            // $otherAdd[] = ['descript' => '拉弯加价:' . $lawangPrice, 'value' => $lawangPrice];
            $otherAdd[] = ['descript' => '拱弧长加价:' . round($arcLength / 1000, 2) . "M*20*$arcLengthCount", 'value' => round($arcLength / 1000 * 20 * $arcLengthCount, 2)];
        } elseif ($windowType == '内弧(拱)形护栏') {
            $lawangPrice = 0; //拉弯费
            $windowAddPrice = $arcLength / 1000 * 20 * $arcLengthCount + $lawangPrice;

            // $otherAdd[] = ['descript' => '拉弯加价:' . $lawangPrice, 'value' => $lawangPrice];
            $otherAdd[] = ['descript' => '拱弧长加价:' . round($arcLength / 1000, 2) . "M*20*$arcLengthCount", 'value' => round($arcLength / 1000 * 20 * $arcLengthCount, 2)];
        } elseif ($windowType == '外弧(拱)形窗护栏') {
            $lawangPrice = 0; //拉弯费
            $windowAddPrice = $arcLength / 1000 * 50 * $arcLengthCount + $lawangPrice;
            
            // $otherAdd[] = ['descript' => '拉弯加价:' . $lawangPrice, 'value' => $lawangPrice];
            $otherAdd[] = ['descript' => '拱弧长加价:' . round($arcLength / 1000, 2) . "M*50*$arcLengthCount", 'value' => round($arcLength / 1000 * 50 * $arcLengthCount, 2)];
        } 
        else {
            $windowAddPrice = 0;
        }
        
        //逃生窗加价
        $escapePrice = 0;
        if($escapeType == '有逃生窗'){
            $escapePrice = 30;
            $otherAdd[] = ['descript' => '逃生窗加价:' . $escapePrice, 'value' => $escapePrice];
        }


        //颜色价格
        $alumDiyPrice = $data['alum_name_price'] ? $data['alum_name_price'] : 0; //铝型材自定义的颜色价格
        $flowerDiyPrice = $data['flower_name_price'] ? $data['flower_name_price'] : 0; //花件自定义的颜色价格
        //如果是特殊烤漆，则使用客户填写的价格
        if ($alumDiyPrice > 0 || $flowerDiyPrice > 0) {
            if ($alumDiyPrice >= $flowerDiyPrice) {
                $colorPrice = $alumDiyPrice;
            } else {
                $colorPrice = $flowerDiyPrice;
            }
            // $otherAdd[] = ['descript' => '颜色加价:' . $colorPrice."*".$offerArea, 'value' => round($colorPrice*$offerArea,2)];
        } else {
            //否则使用系列的价格
            $alum = Db::name('series_color')->where(['series_id' => $series, 'type' => 1, 'color_id' => $alumColorId])->find();
            $flower = Db::name('series_color')->where(['series_id' => $series, 'type' => 2, 'color_id' => $flowerColorId])->find();
            $colorPrice = 0;
            //如果铝型颜色合花件颜色同时存在相同的,则用价格高的，否则使用铝型颜色价格
            if ($alum && $flower) {
                //比较铝型颜色和花件颜色大小，按价格高的计算
                if ($alum['price'] >= $flower['price']) {
                    $colorPrice = $alum['price'];
                } else {
                    $colorPrice = $flower['price'];
                }
            }else{
                 $colorPrice = $alum['price'];
            }
            // $otherAdd[] = ['descript' => '颜色加价:' . $colorPrice."*".$offerArea, 'value' => round($colorPrice*$offerArea,2)];
        }
        //花件价格
        $seriesFlower = Db::name('series_flower')->where(['series_id' => $series,'flower_id' => $data['flower_id']])->find();
        $seriesFlowerPrice = isset($seriesFlower['price'])?$seriesFlower['price']:0;

        //防护栏
        if($data['guardrail'] == 1){
			$guardrailPrice = round($data['guardrail_price'] * $data['guardrail_num'], 2);
			$otherAdd[] = ['descript' => '防护栏:' . $data['guardrail_price'] . '*' . $data['guardrail_num'], 'value' => $guardrailPrice];
        }else{
			$guardrailPrice = 0;
		}

        // 单价
        $price = isset($data['price'])?$data['price']:($seriesPrice + $colorPrice + $yarnPrice+$seriesFlowerPrice);
        //折扣价
        $rebatePrice = round($price * $data['rebate'], 2);       
        //总报价价格
        $totalSum = (($offerArea * $rebatePrice) + $windowAddPrice + $escapePrice + ($fivePrice * $data['five_count'])) * $data['count']+ $guardrailPrice;
        //其他额外加价
        $extra = ($windowAddPrice + $escapePrice + ($fivePrice * $data['five_count']))*$data['count'];

        return ['price' => $price, 'all_price' => $totalSum, 'area' => round($offerArea * $data['count'], 2), 'product_area' => round($productArea * $data['count'], 2),
            'rebate_price' => $rebatePrice, 'other_add' => $otherAdd,'all_width' => $allWidth,'all_height' => $allHeight,'extra' => round($extra,2)
        ];
    }

    /**
     * 计算订单总价
     * @param int $orderId 订单id
     */
    public function orderPrice($orderId)
    {
        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')
                ->where('order_id', $orderId)
                ->where('order_type != 3')
                ->find();
        $materialPrice = Db::name('order_material')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')
                ->where('order_id', $orderId)
                ->find();
        $allPrice = $totalPrice['all_price'] + $materialPrice['all_price'];
        $count = $totalPrice['count'] + $materialPrice['count'];
        $area = $totalPrice['area'] + $materialPrice['area'];
        $productArea = $totalPrice['product_area'] + $materialPrice['product_area'];
        $data = ['all_price' => round($allPrice, 2), 'count' => round($count, 2), 'area' => round($area, 2), 'product_area' => round($productArea, 2),
            'total_price' => round($allPrice, 2),
        ];
        return $data;
    }

    /**
     * 添加订单信息
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function addInfo($data, $orderId, $orderType = 0)
    {
        //固定高数组
        $fixed = [];

        if (isset($data['fixed'])) {
            $fixedName = $data['fixed_name'];
            foreach ($data['fixed'] as $k => $v) {
                $fixed[] = ['name' => $fixedName[$k], 'fixed' => $v];
            }
        }
        $fixed = serialize($fixed);

        //把手位数组
        $handsText = $data['hands_text'] != '请选择' ? $data['hands_text'] : '';
        $hands = ['name' => $handsText, 'id' => $data['hands']];
        $hands = serialize($hands);

        $priceData = $this->getPriceData($data, $orderId, $orderType); //获取要插入的报价数组
        $priceData['addtime'] = time();
        $priceId = Db::name('order_price')->insertGetId($priceData);

        //插入订单提示表
        Db::name('order_tips')->insert(['op_id' => $priceId, 'min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        $flower = ['id'=>$data['flower_ids'],'name'=>$data['flowers'],'pic'=>$data['flower_pics']];
        $caculationData = [
            'op_id' => $priceId, 'bottom_spacing' => $data['bottom_spacing'],'spacing' => $data['spacing'], 'structure' => $data['structure'], 'fixed_height' => $fixed, 'hands' => $hands,
            'lock_position' => $data['lock_position'], 'structure_id' => $data['structure_id'],'right_bottom_spacing' => $data['right_bottom_spacing'],
            'left_to_middle' => $data['left_to_middle'],'bottom_fixed_spacing'=>$data['bottom_fixed_spacing'],
            'bottom_vertical_spacing'=>$data['bottom_vertical_spacing'],'cal_note'=>$data['cal_note'],'right_to_middle'=>$data['right_to_middle'],
            'center_row'=>$data['center_row_spacing'],'center_left'=>$data['center_left_spacing'],'center_right'=>$data['center_right_spacing'],'center_to_left'=>$data['middle_to_left'],
            'center_to_right'=>$data['middle_to_right'],'flower_types'=>$data['flowers'],'flower_ids'=>$data['flower_ids'],'flower_pics'=>$data['flower_pics'],'flowers'=>serialize($flower)
        ];
        $caculationRes = Db::name('order_calculation')->insert($caculationData);

        //更新总价
//        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')->where('order_id', $orderId)->find();
        $totalPrice = $this->orderPrice($orderId);
        Db::name('order')->where('id', $orderId)->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => $totalPrice['product_area'], 'count' => $totalPrice['count']]);
        if ($caculationRes && $priceId) {
            return true;
        }
        return false;
    }

    /**
     * 编辑订单信息
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function editInfo($data, $orderId)
    {
        $opId = $data['op_id'];       
        $priceData = $this->getPriceData($data, $orderId);
        $priceId = Db::name('order_price')->where('op_id', $opId)->update($priceData);

        //查询是否有了提示，有则更新，无则添加
        $findTips = Db::name('order_tips')->where('op_id', $opId)->find();
        if ($findTips) {
            Db::name('order_tips')->where('op_id', $opId)->update(['min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        } else {
            Db::name('order_tips')->insert(['op_id' => $opId, 'min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        }

        //固定高数组
        $fixed = [];
        if (isset($data['fixed'])) {
            $fixedName = $data['fixed_name'];
            foreach ($data['fixed'] as $k => $v) {
                $fixed[] = ['name' => $fixedName[$k], 'fixed' => $v];
            }
        }
        $fixed = serialize($fixed);

        //把手位数组
        $handsText = $data['hands_text'] != '请选择' ? $data['hands_text'] : '';
        $hands = ['name' => $handsText, 'id' => $data['hands']];
        $hands = serialize($hands);

        $flower = ['id'=>$data['flower_ids'],'name'=>$data['flowers'],'pic'=>$data['flower_pics']];
        $caculationData = [
            'op_id' => $opId, 'spacing' => $data['spacing'],'bottom_spacing' => $data['bottom_spacing'], 'structure' => $data['structure'], 'fixed_height' => $fixed, 'hands' => $hands,
            'lock_position' => $data['lock_position'], 'structure_id' => $data['structure_id'], 'structure_id' => $data['structure_id'],'right_bottom_spacing' => $data['right_bottom_spacing'],
            'left_to_middle' => $data['left_to_middle'],'bottom_fixed_spacing'=>$data['bottom_fixed_spacing'],
            'bottom_vertical_spacing'=>$data['bottom_vertical_spacing'],'cal_note'=>$data['cal_note'],'right_to_middle'=>$data['right_to_middle'],
            'center_row'=>$data['center_row_spacing'],'center_left'=>$data['center_left_spacing'],'center_right'=>$data['center_right_spacing'],'center_to_left'=>$data['middle_to_left'],
            'center_to_right'=>$data['middle_to_right'],'flower_types'=>$data['flowers'],'flower_ids'=>$data['flower_ids'],'flower_pics'=>$data['flower_pics'],'flowers'=>serialize($flower)
        ]; 
        $caculationRes = Db::name('order_calculation')->where('op_id', $opId)->update($caculationData);

        //更新总价
//        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')->where('order_id', $data['order_id'])->find();
        $totalPrice = $this->orderPrice($data['order_id']);
        Db::name('order')->where('id', $data['order_id'])->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => round($totalPrice['product_area'], 2), 'count' => $totalPrice['count']]);
        if ($caculationRes !== false && $priceId !== false) {
            return true;
        }
        return false;
    }


    /**
     * 车间编辑订单信息,不改价格有关数据
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool
     */
    public function carEditInfo($data, $orderId)
    {
        $opId = $data['op_id'];
        $priceData = $this->getPriceData($data, $orderId);
        $priceId = Db::name('order_price')->where('op_id', $opId)->update([
            'all_width'=>$priceData['all_width'],'all_height'=>$priceData['all_height'],'product_area'=>$priceData['product_area']
        ]);

        //查询是否有了提示，有则更新，无则添加
        $findTips = Db::name('order_tips')->where('op_id', $opId)->find();
        if ($findTips) {
            Db::name('order_tips')->where('op_id', $opId)->update(['min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        } else {
            Db::name('order_tips')->insert(['op_id' => $opId, 'min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        }

        //固定高数组
        $fixed = [];
        if (isset($data['fixed'])) {
            $fixedName = $data['fixed_name'];
            foreach ($data['fixed'] as $k => $v) {
                $fixed[] = ['name' => $fixedName[$k], 'fixed' => $v];
            }
        }
        $fixed = serialize($fixed);

        //把手位数组
        $handsText = $data['hands_text'] != '请选择' ? $data['hands_text'] : '';
        $hands = ['name' => $handsText, 'id' => $data['hands']];
        $hands = serialize($hands);
        $flower = ['id'=>$data['flower_ids'],'name'=>$data['flowers'],'pic'=>$data['flower_pics']];
        $caculationData = [
            'op_id' => $opId, 'spacing' => $data['spacing'],'bottom_spacing' => $data['bottom_spacing'], 'structure' => $data['structure'], 'fixed_height' => $fixed, 'hands' => $hands,
            'lock_position' => $data['lock_position'], 'structure_id' => $data['structure_id'], 'structure_id' => $data['structure_id'],'right_bottom_spacing' => $data['right_bottom_spacing'],
            'left_to_middle' => $data['left_to_middle'],'bottom_fixed_spacing'=>$data['bottom_fixed_spacing'],
            'bottom_vertical_spacing'=>$data['bottom_vertical_spacing'],'cal_note'=>$data['cal_note'],'right_to_middle'=>$data['right_to_middle'],
            'center_row'=>$data['center_row_spacing'],'center_left'=>$data['center_left_spacing'],'center_right'=>$data['center_right_spacing'],'center_to_left'=>$data['middle_to_left'],
            'center_to_right'=>$data['middle_to_right'],'flower_types'=>$data['flowers'],'flower_ids'=>$data['flower_ids'],'flower_pics'=>$data['flower_pics'],'flowers'=>serialize($flower)
        ];
        $caculationRes = Db::name('order_calculation')->where('op_id', $opId)->update($caculationData);

        //更新总产品面积
        $totalPrice = $this->orderPrice($data['order_id']);
        Db::name('order')->where('id', $data['order_id'])->update([ 'product_area' => round($totalPrice['product_area'], 2), 'count' => $totalPrice['count']]);
        if ($caculationRes !== false && $priceId !== false) {
            return true;
        }
        return false;
    }

    /**
     * 添加手工单
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function addHandsOrder($data, $orderId)
    {
        //添加报价信息
        $priceData = $this->getPriceData($data, $orderId, 1);
        $priceData['addtime'] = time();
        $priceId = Db::name('order_price')->insertGetId($priceData);
        $structure = Db::name('order_calculation')->insert(['op_id' => $priceId, 'structure' => $data['structure']]);

        //算料结果
        $cName = $data['c_name'];
        $cMaterial = $data['c_material'];
        $cSize = $data['c_size'];
        $cCount = $data['c_count'];
        $calculate = [];
        $allCalculate = [];
        foreach ($cName as $k => $v) {
            $calculate[] = $v . '=' . $cSize[$k] . '*' . $cCount[$k];
            $allCalculate[] = ['c_name' => $v, 'c_material' => $cMaterial[$k], 'c_size' => $cSize[$k], 'c_count' => $cCount[$k]];
        }
        $res = Db::name('order_result')->insert(['order_id' => $orderId, 'op_id' => $priceId, 'all_data' => serialize($allCalculate),
            'calculate_size' => serialize($calculate), 'addtime' => time(), 'is_hand' => 1, 'path' => '/upload/' . $data['structure']
        ]);

        //更新总价
//        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')->where('order_id', $orderId)->find();
        $totalPrice = $this->orderPrice($orderId);
        Db::name('order')->where('id', $orderId)->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => $totalPrice['product_area'], 'count' => $totalPrice['count']]);
        if ($priceId && $structure && $res) {
            return true;
        }
        return false;
    }

    /**
     * 编辑手工单
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function editHandsOrder($data, $orderId)
    {
        $priceData = $this->getPriceData($data, $orderId, 1);
        unset($priceData['order_id']);
        $price = Db::name('order_price')->where('op_id', $data['op_id'])->update($priceData);
        $structure = Db::name('order_calculation')->where('op_id', $data['op_id'])->update(['structure' => $data['structure']]);

        //算料结果
        $cName = $data['c_name'];
        $cMaterial = $data['c_material'];
        $cSize = $data['c_size'];
        $cCount = $data['c_count'];
        $calculate = [];
        $allCalculate = [];
        foreach ($cName as $k => $v) {
            $calculate[] = $v . '=' . $cSize[$k] . '*' . $cCount[$k];
            $allCalculate[] = ['c_name' => $v, 'c_material' => $cMaterial[$k], 'c_size' => $cSize[$k], 'c_count' => $cCount[$k]];
        }
        $res = Db::name('order_result')->where('op_id', $data['op_id'])->update(['all_data' => serialize($allCalculate), 'calculate_size' => serialize($calculate), 'path' => '/upload/' . $data['structure']]);

        //更新总价
//        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')->where('order_id', $orderId)->find();
        $totalPrice = $this->orderPrice($orderId);
        Db::name('order')->where('id', $orderId)->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => $totalPrice['product_area'], 'count' => $totalPrice['count']]);
        if ($price !== false && $structure !== false && $res !== false) {
            return true;
        }
        return false;
    }

    /**
     * 添加组合单
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function addGroup($data, $orderId, $orderType = 0)
    {
        //固定高数组
        $fixed = [];

        if (isset($data['fixed'])) {
            $fixedName = $data['fixed_name'];
            foreach ($data['fixed'] as $k => $v) {
                $fixed[] = ['name' => $fixedName[$k], 'fixed' => $v];
            }
        }
        $fixed = serialize($fixed);

        //把手位数组
        $handsText = $data['hands_text'] != '请选择' ? $data['hands_text'] : '';
        $hands = ['name' => $handsText, 'id' => $data['hands']];
        $hands = serialize($hands);

        $priceData = $this->getPriceData($data, $orderId, $orderType); //获取要插入的报价数组
        //插入组合单表
        $ogId = $data['og_id'];
        if (!$data['og_id']) {
            $priceCount = $orderType == 2 ? 1 : 0;
            $calculateCount = $orderType == 3 ? 1 : 0;
            $ogId = Db::name('order_group')->insertGetId([
                'order_id' => $orderId, 'width' => $priceData['all_width'], 'height' => $priceData['all_height'], 'area' => $priceData['area'],
                'total_price' => $priceData['all_price'], 'price_count' => $priceCount, 'calculate_count' => $calculateCount, 'addtime' => time()
            ]);
        }
        $priceData['og_id'] = $ogId;
        $priceData['addtime'] = time();
        $priceData['all_price'] = $orderType==3?0:$priceData['all_price'];
        $priceData['area'] = $orderType==3?0:$priceData['area'];
        $priceId = Db::name('order_price')->insertGetId($priceData);

        //插入订单提示表
        Db::name('order_tips')->insert(['op_id' => $priceId, 'min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        $flower = ['id'=>$data['flower_ids'],'name'=>$data['flowers'],'pic'=>$data['flower_pics']];
        $caculationData = [
            'op_id' => $priceId, 'spacing' => $data['spacing'],'bottom_spacing' => $data['bottom_spacing'], 'structure' => $data['structure'], 'fixed_height' => $fixed, 'hands' => $hands,
            'lock_position' => $data['lock_position'], 'structure_id' => $data['structure_id'] ,'structure_id' => $data['structure_id'],
            'right_bottom_spacing' => $data['right_bottom_spacing'],
            'left_to_middle' => $data['left_to_middle'],'bottom_fixed_spacing'=>$data['bottom_fixed_spacing'],
            'bottom_vertical_spacing'=>$data['bottom_vertical_spacing'],'cal_note'=>$data['cal_note'],'right_to_middle'=>$data['right_to_middle'],
            'flower_types'=>$data['flowers'],'flower_ids'=>$data['flower_ids'],'flower_pics'=>$data['flower_pics'],'flowers'=>serialize($flower)
        ];
        $caculationRes = Db::name('order_calculation')->insert($caculationData);

        if ($data['og_id'] != 0) {
            $totalGroup = Db::name('order_price')->field('sum(all_width) as width,sum(all_height) as height,sum(area) as area,sum(count) as pcount,'
                            . 'sum(count) as ccount,sum(all_price) as all_price,sum(product_area) as product_area')
                    ->where('og_id', $data['og_id'])
                    ->where('order_type',2)
                    ->find();
            $totalCaculate = Db::name('order_price')->where(['og_id'=>$data['og_id'],'order_type'=>3])->count();
            //更新组合单总信息
            $group = Db::name('order_group')->where('og_id', $data['og_id'])->update([
                'width' => $totalGroup['width'], 'height' => $totalGroup['height'], 'area' => round($totalGroup['area'], 2), 'product_area' => round($totalGroup['product_area'], 2),
                'total_price' => $totalGroup['all_price'], 'price_count' => $totalGroup['pcount'], 'calculate_count' => $totalCaculate,
            ]);
        }

        //组合单的算料信息产品 不算入总价
        if($orderType != 3){
            $totalPrice = $this->orderPrice($orderId);
            Db::name('order')->where('id', $orderId)->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => $totalPrice['product_area'], 'count' => $totalPrice['count']]);
        }
        if ($caculationRes && $priceId) {
            return $ogId;
        }
        return false;
    }

    /**
     * 编辑组合单
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @return bool 
     */
    public function editGroup($data, $orderId)
    {
        $opId = $data['op_id'];
        $orderType = $data['order_type'];
        $priceData = $this->getPriceData($data, $orderId);
        unset($priceData['order_type']);

        $priceData['all_price'] = $orderType==3?0:$priceData['all_price'];
        $priceData['area'] = $orderType==3?0:$priceData['area'];
        $priceId = Db::name('order_price')->where('op_id', $opId)->update($priceData);

        //查询是否有了提示，有则更新，无则添加
        $findTips = Db::name('order_tips')->where('op_id', $opId)->find();
        if ($findTips) {
            Db::name('order_tips')->where('op_id', $opId)->update(['min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        } else {
            Db::name('order_tips')->insert(['op_id' => $opId, 'min' => $data['tips_min'], 'max' => $data['tips_max'], 'name' => $data['tips_fixed']]);
        }

        //固定高数组
        $fixed = [];
        if (isset($data['fixed'])) {
            $fixedName = $data['fixed_name'];
            foreach ($data['fixed'] as $k => $v) {
                $fixed[] = ['name' => $fixedName[$k], 'fixed' => $v];
            }
        }
        $fixed = serialize($fixed);

        //把手位数组
        $handsText = $data['hands_text'] != '请选择' ? $data['hands_text'] : '';
        $hands = ['name' => $handsText, 'id' => $data['hands']];
        $hands = serialize($hands);
        $flower = ['id'=>$data['flower_ids'],'name'=>$data['flowers'],'pic'=>$data['flower_pics']];
        $caculationData = [
            'op_id' => $opId, 'spacing' => $data['spacing'],'bottom_spacing' => $data['bottom_spacing'], 'structure' => $data['structure'], 'fixed_height' => $fixed, 'hands' => $hands,
            'lock_position' => $data['lock_position'], 'structure_id' => $data['structure_id'] ,'structure_id' => $data['structure_id'],
            'right_bottom_spacing' => $data['right_bottom_spacing'],
            'left_to_middle' => $data['left_to_middle'],'bottom_fixed_spacing'=>$data['bottom_fixed_spacing'],
            'bottom_vertical_spacing'=>$data['bottom_vertical_spacing'],'cal_note'=>$data['cal_note'],'right_to_middle'=>$data['right_to_middle'],
            'center_row'=>$data['center_row_spacing'],'center_left'=>$data['center_left_spacing'],'center_right'=>$data['center_right_spacing'],'center_to_left'=>$data['middle_to_left'],'center_to_right'=>$data['middle_to_right']
            ,'flower_types'=>$data['flowers'],'flower_ids'=>$data['flower_ids'],'flower_pics'=>$data['flower_pics'],'flowers'=>serialize($flower)
        ];
        $caculationRes = Db::name('order_calculation')->where('op_id', $opId)->update($caculationData);

        $totalGroup = Db::name('order_price')->field('sum(all_width) as width,sum(all_height) as height,sum(area) as area,sum(count) as pcount,'
                        . 'sum(case when order_type=3 then 1 else 0 end) as ccount,sum(all_price) as all_price,sum(product_area) as product_area')
                ->where('og_id', $data['og_id'])
                ->where('order_type',2)
                ->find();
        $totalCaculate = Db::name('order_price')->where(['og_id'=>$data['og_id'],'order_type'=>3])->count();
        //更新组合单总信息
        $group = Db::name('order_group')->where('og_id', $data['og_id'])->update([
            'width' => $totalGroup['width'], 'height' => $totalGroup['height'], 'area' => round($totalGroup['area'], 2), 'product_area' => round($totalGroup['product_area'], 2),
            'total_price' => $totalGroup['all_price'], 'price_count' => $totalGroup['pcount'], 'calculate_count' => $totalCaculate,
        ]);

        //更新总价
//        $totalPrice = Db::name('order_price')->field('sum(all_price) as all_price,sum(count) as count,sum(area) as area,sum(product_area) as product_area')->where('order_id', $data['order_id'])->find();
        $totalPrice = $this->orderPrice($data['order_id']);
        Db::name('order')->where('id', $data['order_id'])->update(['total_price' => $totalPrice['all_price'], 'area' => $totalPrice['area'], 'product_area' => $totalPrice['product_area'], 'count' => $totalPrice['count']]);
        if ($caculationRes !== false && $priceId !== false) {
            return true;
        }
        return false;
    }

    /**
     * 获取报价信息数组(整理好可插入数据表的数)
     * @param array $data 表单提交的数组
     * @param int $orderId 所属的订单id
     * @param int $orderType 订单类型
     */
    public function getPriceData($data, $orderId, $orderType = 0)
    {
        //系列数组
        $tech = $this->getLastValue($data['tech']);  //系列最后一个id
        $seriesPid = Db::name('series')->where('id', $tech)->find();
        $parent = '';
        if ($seriesPid) {
            $series = Db::name('series')->select();
            $parent = $this->getSortParent($series, $seriesPid); //系列序列化数组

            $material = ''; //材质名称
            foreach ($parent as $key => $value) {
                $material .= $value . '/';
            }
            $material = rtrim($material, '/');            
            $parent = serialize($parent);
        }

        //花件数组
        $flowerArray[$data['flower_id']] = ['name' => $data['flower'], 'pic' => $data['flower_pic']];
        $flower = serialize($flowerArray);  //花件序列化数组
        //铝型颜色数组
        $alumColorId = $this->getLastValue($data['alum_color']); //铝型颜色最后一个id
        $alumPid = Db::name('bom_color')->where('id', $alumColorId)->find();
        $color = Db::name('bom_color')->select();
        $alumColor = '';
        if ($alumPid) {
            $colorFirst = $alumPid['name']; //颜色
            $alumColor = $this->getSortParent($color, $alumPid);
            $alumColor = serialize($alumColor);
        } elseif ($alumColorId == '-1') {
            $colorFirst = '特殊烤漆';
            $alumColor = serialize(['-1' => '特殊烤漆']); //如果是特殊烤漆
        }

        //花件颜色数组
        $flowerColorId = $this->getLastValue($data['flower_color']);
        $flowerPid = Db::name('bom_color')->where('id', $flowerColorId)->find();
        $flowerColor = '';
        $flowerFirst = '';
        if ($flowerPid) {
            $flowerFirst = $flowerPid['name'];
            $flowerColor = $this->getSortParent($color, $flowerPid);
            $flowerColor = serialize($flowerColor);
        } elseif ($flowerColorId == '-1') {
            $flowerFirst = '特殊烤漆';
            $flowerColor = serialize(['-1' => '特殊烤漆']); //如果是特殊烤漆
        }else{
            $flowerColor = $this->getSortParent($color,0);
            $flowerColor = serialize($flowerColor);
        }

        //纱网数组
        $yarnText = $data['yarn_text'] != '请选择' ? $data['yarn_text'] : '';
        $yarn = ['name' => $yarnText, 'id' => $data['yarn_color']];
        $yarn = serialize($yarn);

		if(isset($data['guardrail'])){
			$data['guardrail'] = $data['guardrail'];
			if($data['guardrail'] == 1){
				$data['guardrail_price'] = $data['guardrail_price'];
				$data['guardrail_num'] = $data['guardrail_num'];
			}else{
				$data['guardrail_price'] = 0;
				$data['guardrail_num'] = 0;
			}
		}

        //计算价格
        $price = $this->calculatePrice($data, $data['series_id'], $alumColorId, $flowerColorId);

        $name = $data['name'];  //产品名称
//        $alumName = isset($data['alum_color']) ? $data['alum_name'] : $alumPid['name'];
//        $flowerName = isset($data['flower_color']) ? $data['flower_name'] : $flowerPid['name'];

        $colorName = $colorFirst . $data['alum_name'] . '/' . $flowerFirst . $data['flower_name'];
        $area = $price['area'];
        $priceData = [
            'name' => $name, 'material' => $material, 'flower_type' => $data['flower'], 'color_name' => $colorName, 'flower_pic' => $data['flower_pic'],
            'order_id' => $orderId, 'series_id' => $data['series_id'], 'technology' => $parent, 'flower' => $flower, 'alum_color' => $alumColor, 'flower_color' => $flowerColor,
            'alum_color_id' => $alumColorId, 'flower_color_id' => $flowerColorId, 'flower_id' => $data['flower_id'], 'alum_name_price' => $data['alum_name_price'], 'flower_name_price' => $data['flower_name_price'],
            'alum_name' => $data['alum_name'], 'flower_name' => $data['flower_name'], 'yarn_price' => $data['yarn_price'], 'yarn_color' => $yarn, 'yarn_thickness' => $data['yarn_thickness'],
            'five_id' => $data['five_id'], 'five_count' => $data['five_count'], 'five_price' => $data['five_price'],
            'window_type_a' => $data['window_type_a'], 'escape_type_a' => $data['escape_type_a'],'top_fly' => $data['top_fly'], 'bottom_fly' => $data['bottom_fly'], 'left_fly' => $data['left_fly'],
            'right_fly' => $data['right_fly'], 'other_add_price' => serialize($price['other_add']),
            'window' => $data['window'], 'arc_height' => $data['arc_height'], 'arc_length_count' => $data['arc_length_count'], 'all_width' => $price['all_width'],
            'all_height' => $price['all_height'], 'count' => $data['count'], 'note' => $data['note'], 'price' => $price['price'], 'all_price' => $price['all_price'],
            'area' => $area, 'product_area' => $price['product_area'], 'rebate' => $data['rebate'], 'rebate_price' => $price['rebate_price'], 'order_type' => $orderType,'position' => $data['position'],
            'diy_pic' => $data['diy_pic'],'guardrail' => $data['guardrail'],'guardrail_price' => $data['guardrail_price'],'guardrail_num' => $data['guardrail_num']
        ];

        return $priceData;
    }

    /**
     * 获取产品报价信息--(用户编辑产品)
     * @param type $data
     * @return type
     */
    public function getProductInfo($data)
    {
        //系列数组       
        $seriesArray = unserialize($data['technology']);
        $seriesCount = count($seriesArray);
        $seriesId = array_keys($seriesArray);
        $seriesAll = Db::name('series')->whereIn('parent_id', implode(',', $seriesId) . ',0')->select(); //当前系列的所有数组
        $series = $this->getHandleArray($seriesAll, $seriesArray, $seriesId);

        //如果系列为2,3级，则添加数组，使此前端能循环5级出来
        if (count($series) == 4) {
            $series = array_merge($series, ['-1' => 1]);
        }elseif(count($series) == 3){
            $series = array_merge($series, ['-1' => 1,'-2'=>1]);
        }elseif(count($series) == 2){
            $series = array_merge($series, ['-1' => 1,'-2'=>1,'-3'=>1]);
        }

        //花件
        $flower = unserialize($data['flower']);

        //铝型颜色
        $alumColorArray = unserialize($data['alum_color']);
        $alumColorId = array_keys($alumColorArray);
        $alumColorAll = Db::name('series_color')->alias('a')->field('b.*')
                ->join('bom_color b','a.color_id=b.id')
                ->where(['type'=>1,'a.series_id'=>$data['series_id']])
                ->select(); //铝型颜色的所有数组    

        $alumColor = $this->getHandleArray($alumColorAll, $alumColorArray, $alumColorId);
        $alumColorCount = count($alumColor);
        //添加两个无用数组,确保循环出来的html有3级
        $alumColor = $alumColor + ['1' => 1, '2' => 2];


        //花件颜色
        $flowerColorArray = unserialize($data['flower_color']);
        $flowerColorId = is_array($flowerColorArray) ? array_keys($flowerColorArray) : [];
        $flowerColorAll = Db::name('series_color')->alias('a')->field('b.*')
                ->join('bom_color b','a.color_id=b.id')
                ->where(['type'=>2,'a.series_id'=>$data['series_id']])
                ->select(); //花件颜色的所有数组  
        if (!is_array($flowerColorArray)) {
            $flowerColor = [];
            $flowerColorCount = 0;
            $flowerColor = ['1' => 1, '2' => 2, '3' => 3];
        } else {
            $flowerColor = $this->getHandleArray($flowerColorAll, $flowerColorArray, $flowerColorId);
            $flowerColorCount = count($flowerColor);
            //添加两个无用数组,确保循环出来的html有3级
            $flowerColor = $flowerColor + ['1' => 1, '2' => 2];
        }

        //所属系列
        end($seriesArray);
        $belongSeries = key($seriesArray);

        //纱网      
        $yarnArray = unserialize($data['yarn_color']);
        $yarnColor = Db::name('series_yarn')->alias('a')->field('a.*,b.name')
                ->join('bom_yarn b', 'a.yarn_id=b.id')
                ->where('a.series_id', $belongSeries)
                ->select();

        //五金     
        $five = Db::name('series_five')->alias('a')->field('a.*,b.name')
                ->join('bom_five b', 'a.five_id=b.id')
                ->where('a.series_id', $belongSeries)
                ->select();

        //固定高
        $fixedHeight = unserialize($data['fixed_height']);

        //把手位
        $handsArray = unserialize($data['hands']);
        $hands = Db::name('series_hands')->alias('a')->field('a.*,b.name')
                ->join('bom_hands b', 'a.hands_id=b.id')
                ->where('a.series_id', $belongSeries)
                ->select();
        $data = ['series' => $series, 'series_count' => $seriesCount, 'alum_color_array' => $alumColorArray, 'alum_color' => $alumColor, 'alum_color_count' => $alumColorCount,
            'flower_color' => $flowerColor, 'flower_color_array' => $flowerColorArray, 'flower' => $flower, 'flower_color_count' => $flowerColorCount, 'yarn_color' => $yarnColor,
            'fixed_height' => $fixedHeight, 'hands' => $hands, 'series_id' => $belongSeries, 'yarn_id' => $yarnArray['id'], 'hands_id' => $handsArray['id'],
            'hands_array' => $handsArray, 'five' => $five,
        ];
        return $data;
    }

    /**
     * 编辑产品--获取处理后的多级栏目数组(用于前端遍历输出)
     * @param array $all 所有数组
     * @param array $uarray 反序列化后的数组
     * @param array $ids 反序列化后的键数组
     * @return array
     */
    public function getHandleArray($all, $uarray, $ids)
    {
        $array = [];
        $array[0] = [];
        foreach ($ids as $kc=>$res){
            $array[$res] = [];
        }
        foreach ($all as $k => $v) {
            //判断是否选中
            if (in_array($v['id'], $ids)) {
                $v['selected'] = 'selected';
            } else {
                $v['selected'] = '';
            }
            foreach ($uarray as $k2 => $v2) {
                //第一条
                if ($v['parent_id'] == 0) {
                    $array[0][] = $v;
                    break;
                } elseif ($k2 == $v['parent_id']) {
                    $array[$k2][] = $v;
                }
            }
        }

        return $array;
    }

    /**
     * 读取订单产品详情
     * @param int $opId 订单产品价格表id
     */
    public function readProduct($opId)
    {
        $product = Db::name('order_price')->alias('a')
                ->join('order_calculation b', 'a.op_id=b.op_id')
                ->where('a.op_id', $opId)
                ->find();
        $fixedHeight = isset($product['fixed_height']) ? unserialize($product['fixed_height']) : '';
        $hands = isset($product['hands']) ? unserialize($product['hands']) : '';

        return ['info' => $product, 'fixed' => $fixedHeight, 'hands' => $hands];
    }

    /**
     * 表格数据直接修改
     * @param string $table 表名
     * @param string $field 要更新的字段名
     * @param string $value 要更新的值
     * @param int $idName 表的主键名称
     * @param int $idValue 表的主键id值
     * @param int $orderId 订单id
     * @param array $data 表格所有字段键值
     * @param bool $isGroup 是否是组合单的产品
     * @return boolean
     */
    public function editPrice($table, $field, $value, $idName, $idValue, $orderId, $data, $isGroup)
    {
        //飘窗特殊情况,宽高减去 飘窗的左右上下 飘数
        $data['all_width'] = $data['all_width']-$data['left_fly']-$data['right_fly'];
        $data['all_height'] = $data['all_height']-$data['top_fly']-$data['bottom_fly'];
        //如果更新的字段是单价，则同时更新单价，折后价和总价
        if ($field == 'price') {

            $rebatePrice = $value * $data['rebate']; //折后价
            //调用价格计算方法，获取额外加价
            $other = $this->calculatePrice($data, $data['series_id'], $data['alum_color_id'], $data['flower_color_id']);
            $otherPrice = $other['extra'];
            //总价=价格+额外加价,extra为总的额外加价
            $total = round($data['area'] * $rebatePrice, 2)+$otherPrice;
            $productUpdate = ['price' => $value, 'rebate_price' => $rebatePrice, 'all_price' => $total];
        } elseif($field == 'rebate'){
            //如果更新的字段是折扣，则同时更新折扣，折后价和总价
            $price = $this->calculatePrice($data, $data['series_id'], $data['alum_color_id'], $data['flower_color_id']);
            $productUpdate = ['rebate' => $value,'rebate_price' => $price['rebate_price'], 'all_price' => $price['all_price']];
        }elseif ($field == 'rebate_price') {

            //调用价格计算方法，获取额外加价
            $other = $this->calculatePrice($data, $data['series_id'], $data['alum_color_id'], $data['flower_color_id']);
            $otherPrice = $other['extra'];
            //如果更新的字段是折后价，则同时更新折后价和总价
            $total = round($other['area'] * $value, 2)+$otherPrice;
            $productUpdate = ['rebate_price' => $value, 'all_price' => $total];
        } elseif ($field == 'all_width' || $field == "all_height" || $field == 'count' || $field == 'position') {
            //如果更新的字段是宽或高,则同时更新宽或高,报价面积，产品面积，总金额

            //调用计算价格函数
            $price = $this->calculatePrice($data, $data['series_id'], $data['alum_color_id'], $data['flower_color_id']);
            $total = $price['all_price'];
            $productUpdate = ["$field" => $value, 'area' => $price['area'], 'product_area' => $price['product_area'], 'all_price' => $total, 'other_add_price' => serialize($price['other_add'])];
        }

        //如果是 组合单的 算料产品，价格为0
        if($isGroup == 3){
            $productUpdate['all_price'] = 0;
            $productUpdate['area'] = 0;
        }
        //更新订单产品价格
        $res = Db::name($table)->where($idName, $idValue)->update($productUpdate);


        //如果是组合单的产品，则还需更新组合单数据
        if ($isGroup) {
            $group = Db::name('order_price')->field('sum(all_width) as width,sum(all_height) as height,sum(area) as area,sum(product_area) as product_area,sum(all_price) as total_price')
                    ->where("order_id=$orderId and (order_type=2)")
                    ->find();
            $priceCount = Db::name('order_price')->where(['order_id' => $orderId, 'order_type' => 2])->sum('count');
            $calculateCount = Db::name('order_price')->where(['order_id' => $orderId, 'order_type' => 3])->sum('count');
            if ($group && $data['order_type'] != 3) {
                $updateData = [
                    'width' => $group['width'], 'height' => $group['height'], 'area' => $group['area'], 'product_area' => $group['product_area'],
                    'price_count' => $priceCount, 'calculate_count' => $calculateCount, 'total_price' => $group['total_price']
                ];
                Db::name('order_group')->where('og_id', $data['og_id'])->update($updateData);
            }
        }


        //调用计算总订单金额函数，更新订单总金额
        $total = $this->orderPrice($orderId);
        if ($total && $data['order_type'] != 3) {
            $orderPrice = Db::name('order')->where('id', $orderId)
                    ->update([
                'total_price' => round($total['all_price'], 2), 'area' => round($total['area'], 2), 'product_area' => round($total['product_area'], 2),
                'count' => $total['count']
            ]);
        }

        if ($res !== false) {
            return $total['all_price'].'-'.$productUpdate['all_price'];
        }
        return false;
    }

}
