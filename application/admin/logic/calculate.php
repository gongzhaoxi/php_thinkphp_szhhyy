<?php

namespace app\admin\logic;

use think\Model;
use think\Db;

/**
 * 标尺算料功能
 */
class calculate extends Model
{

    /**
     * 将系列物料绑定的type作为数组键
     * @param type $data
     */
    public function convertType($data)
    {
        $array = [];
        foreach ($data as $key => $value) {
            $array[$value['type']] = $value;
        }
        return $array;
    }

    /**
     * 文字转换成变量公式
     * @param string $formulaStr 文字公式
     * @param array $bomType 要查询物料池的字段
     * @param array $cinit 查询订单中的字段
     * @return string 变量公式
     */
    public function convert($formulaStr, $bomType = [], $cinit = [])
    {
        $replace = $formulaStr;        
        $formulaStr = str_replace(array('(', ')'), '', $formulaStr); //先去除括号
        $formula = str_replace(array('+', '-', '/', '*', ','), '|', $formulaStr); //将运算符号替换成 |
       
        $settingFormula = config('calculate_setting');
        $bomSetting = config('calculate_bom');
        $flowerSetting = config('flower_bom');
        $formula = explode("|", $formula);  //分割成数组
        krsort($formula); //将数组降序，避免框搭框和小门框搭框重复替换的问题
        //2023-12-08重新调整数组降序问题
        $tmp = [];
		foreach($formula as $vo){
				$tmp[] = ['name'=>$vo,'sort'=>mb_strlen($vo)];
		}
		array_multisort(array_column($tmp,'sort'),SORT_DESC,$tmp);
		$formula = array_column($tmp,'name');
				
        foreach ($formula as $k => $v) {
            //可查询订单表中的数据
            if (isset($settingFormula[$v])) {
                $replace = str_replace($v, $settingFormula[$v], $replace); //将文字替换成变量
                $cinit[$settingFormula[$v]] = ['field' => $settingFormula[$v], 'name' => $v];
            } elseif (isset($bomSetting[$v])) {
                //查询系列--物料池中的数据
                $replace = str_replace($v, $bomSetting[$v], $replace); //转换成公式
                $bomType[$bomSetting[$v]] = $bomSetting[$v];
            } elseif(isset($flowerSetting[$v])){
                //物料花件的最大宽，最大高
                $replace = str_replace($v, $flowerSetting[$v], $replace); //转换成公式
                $cinit[$flowerSetting[$v]] = ['field' => $flowerSetting[$v], 'name' => $v];
            }
            
        } 
        
        return ['replace' => $replace, 'bom_type' => $bomType, 'init_field' => $cinit];
    }

    /**
     * 运行公式
     * @param array $formula 标尺公式
     * @param array $calculateFormula 算料公式
     * @param array $product 订单报价及算料信息
     * @param array $seriesBom 系列物料绑定数组
     * @param array $flowerBom 物料花件
     */
    public function getResult($formula, $calculateFormula, $product, $seriesBom,$flowerBom)
    {
        $cInit = [];
        $array = []; //标尺公式数组
        $bomType = []; //要查询物料池的字段
        foreach ($formula as $k => $v) {
            $res = $this->convert($v['formula'], $bomType, $cInit);
            $array[$k]['field'] = $v['field'];
            $array[$k]['formula'] = $res['replace'];
            $bomType = array_merge($res['bom_type'], $bomType);
			$cInit = array_merge($res['init_field'], $cInit);
//            $flowerBom = array_merge($res['flower_bom'], $flowerBom);
        }
//        dump($array);exit;
        $carray = []; //算料公式数组
        $cbomType = []; //要查询物料池的字段
        
        foreach ($calculateFormula as $k => $v) {
            $res = $this->convert($v['formula'], $cbomType, $cInit);            
            $carray[$k]['name'] = $v['bom'];
            $carray[$k]['formula'] = $res['replace'];
            $carray[$k]['count'] = $v['count'];
            $carray[$k]['export_name'] = $v['export_name'];
            $cbomType = array_merge($res['bom_type'], $cbomType);
            $cInit = array_merge($res['init_field'], $cInit);
        }
        
        $product['take'] = isset($seriesBom[0]['take']) ? $seriesBom[0]['take'] : 0; //间距
        //上下固定高
        $fixed = isset($product['fixed_height'])?unserialize($product['fixed_height']):0; //固定高      
        foreach ($fixed as $k => $v) {
            if ($v['name'] == '上固定高' || $v['name'] == '上固定') {
                $topFixed = (float) $v['fixed'];
            } else {
                $bottomFixed = (float) $v['fixed'];
            }
        }
        $product['top_fixed'] = isset($topFixed)?$topFixed:0;
        $product['bottom_fixed'] = isset($bottomFixed)?$bottomFixed:0;

        //执手宽高
        $hands = unserialize($product['hands']);
        $handsWidth = 0;
        $handsHeight = 0;
        if ($hands) {
            $bomHands = Db::name('bom_hands')->where('id', $hands['id'])->find();
            $handsWidth = $bomHands?$bomHands['width']:0;
            $handsHeight = $bomHands?$bomHands['height']:0; 
        }
        $product['hands_width'] = $handsWidth;
        $product['hands_height'] = $handsHeight;
		
		//$LH = $product['hands_height'];
		
       
        $cexport = $this->formulaInit($seriesBom, $cbomType);  //算料公式 需要查询物料的字段变量 赋值
        eval($cexport);
        $field = config('calculate_field');
        $flowerField = config('flower_field');
        $calculateExport = [];
        
        foreach ($cInit as $k => $v) {
            //为订单中有的数据赋值
            if (isset($field[$v['field']])) {
                $key = $field[$v['field']];
                $keyValue = $product[$key];
                eval("{$v['field']}={$keyValue};");
            }elseif(isset ($flowerField[$v['field']])){
                //为花件的最大宽，最大高赋值
                $key = $flowerField[$v['field']];
                if($flowerBom[$key]!=''){
                    $keyValue = $flowerBom[$key];
                }else{
                    $keyValue = 0;
                }

                eval("{$v['field']}={$keyValue};");
                
            }
        }
        
        $calculateStr = '';  //算料输出字符串
        //执行算料公式
        $flowerStr = '';
        foreach ($carray as $k => $v) {
            $exportName = $v['export_name']!=''?$v['export_name']:$v['name'];  //输出名称
            if (isset($field[$v['formula']])) {
                $key = $field[$v['formula']];
                $keyValue = round($product[$key],0);
                $ccount = trim($v['count'])*trim($product['ccount']);
                $calculateExport[] = "$exportName=$keyValue*{$ccount}";
                $calculateStr .= "$exportName=$keyValue*{$ccount}";
                continue;
            }else {
                //花件公式字符中有两个公式，分割成数组处理
                if ($v['name'] == '花件') {
                    $formula2 = explode(',', $v['formula']);
                    $flowerStr .= ",";
                    $flowerStr .= $v['export_name']!=''?$v['export_name']:'';
                    foreach ($formula2 as $key => $value) {
                        eval("\$result=$value;");
                        $flowerStr .= round($result,0)."*";
                    }
                    $fcount = $v['count']*$product['ccount'];
                    $flowerStr .= "$fcount";
                } else {
                   
                    eval("\$result={$v["formula"]};");
                    $ccount = $v['count']*$product['ccount'];
                    $calculateExport[] = "$exportName=".round($result,0)."*{$ccount}";
                    $calculateStr .= "$exportName=".round($result,0)."*{$ccount}";
                }
            }
        }
        
        $flowerStr = rtrim($flowerStr, '*');$flowerStr = ltrim($flowerStr,',');
        $calculateArray = ['result'=>$calculateExport,'flower'=>$flowerStr];

        //标尺公式 需要查询物料的字段变量 赋值
        $export = $this->formulaInit($seriesBom, $bomType);
//      return ['str'=>$export];
        eval($export);
        
        $rexport = $export;
        
        //执行标尺公式,查询公式字母对应的值
        $export = '';
        $str = '';            
        foreach ($array as $k => $v) {
            //为订单中有的数据赋值
            if (isset($field[$v['formula']])) {
                $key = $field[$v['formula']];
                $keyValue = $product[$key];
                eval("{$v['formula']}={$keyValue};");
                $export .= "{$v['field']}=$keyValue&";
                $rexport .= "{$v['field']}=$keyValue&";
            }elseif(isset($flowerField[$v['formula']])){ 
                //为花件最大宽，最大高赋值
                $key = $flowerField[$v['formula']];
                $keyValue = $flowerBom[$key];
                eval("{$v['formula']}={$keyValue};");
                $export .= "{$v['field']}=$keyValue&";
                $rexport .= "{$v['field']}=$keyValue&";
            }else {
            	$str .= '&&'.$v['formula'];
                eval("\$result={$v["formula"]};");
                $export .= "{$v['field']}=".round($result,0)."&";
                $rexport .= "{$v['field']}=".round($result,0)."&";
            }
        }
        $export = rtrim($export, '&');
        
        return ['export' => $export,'rexport'=>$rexport, 'cexport' => $calculateArray, 'calculate_str' => $calculateStr];
    }

    /**
     * 为要查询物料池的 变量赋值
     * @param type $seriesBom
     * @param type $bomType
     * @return type
     */
    public function formulaInit($seriesBom, $bomType)
    {
        $type = config('bom_type'); //物料绑定的数据 --下拉框中
        $bomWrite = config('bom_write'); //物料绑定的数据 --用户填写的
        $seriesBom = $this->convertType($seriesBom); //将type作为数组键
        $bomExport = '';        
        foreach ($bomType as $k => $v) {
            
            //如果是用户填写的数据，则使用填写的数据，否则使用铝型材的小面
            if (array_key_exists($v, $bomWrite)) {
                $fieldName = $bomWrite[$v];
                $value = isset($seriesBom[1][$fieldName])?$seriesBom[1][$fieldName]:0;
            } else {
                $small = isset($seriesBom[$type[$v]]['small']) ? $seriesBom[$type[$v]]['small'] : 0;
                $value = $small;
            }
            $bomExport .= "$v=$value;";
        }
//        echo $bomExport . '</br>';
        return $bomExport;
    }

}
