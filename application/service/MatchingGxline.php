<?php

namespace app\service;

use think\Db;

/**
 * 通过二维码内容 匹配工艺线
 */
class MatchingGxline
{
    /**
     * 二维码匹配工艺线
     * @param array $data 二维码解析后键值对应的数组
     * @return type
     */
    public function index($data)
    {
        $rule = @include APP_DATA.'field_rule.php';
        if(!$rule) {
            $rule = $this->convertField();
        }

        //将空数据转成0
        foreach ($data as $k => $v) {
            if(trim($v) == ''){
                $data[$k] = 0;
            }
        }
        $gxline = [];
        $tempGxline = [];//辅助数组，用于判断组合方式，组合后是否还有工艺线
        foreach ($rule as $k2 => $v2) {

            foreach ($data as $k => $v) {
//                 if($v == ''){
//                     continue;
//                 }
                if($v2['fieldname'] == $k) {
                    $tempRule = explode('|', $v2['rule']);
                    $flag = false;
                    //匹配当前规则是否满足
                    $res = $this->otherMatching($v2['type'], trim($v), $tempRule, $v2['gxline_id']);

                    if ($res && !isset($tempGxline[$v2['gxline_id']])) {
                        $gxline[$v2['gxline_id']] = $v2['gxline_id'];
                        $flag = true;
                    } elseif (isset($tempGxline[$v2['gxline_id']])) {
                        $relateType = $tempGxline[$v2['gxline_id']]['relate_type'];//上一个相同的工艺线 组合方式
                        $relateFlag = $tempGxline[$v2['gxline_id']]['flag'];//上一个相同工艺线 是否符合条件
                        $type = $v2['relate_type'];
                        if ($type == 'and') {
                            //如果上一个相同的工艺线判断 符合条件和当前判断符合条件，则存起来，否则删除
                            if ($relateFlag == true && $res == true) {
                                $gxline[$v2['gxline_id']] = $v2['gxline_id'];
                                $flag = true;
                            } else {
                                unset($gxline[$v2['gxline_id']]);
                                $flag = false;
                            }
                        } else {
                            if ($relateFlag == true || $res == true) {
                                $gxline[$v2['gxline_id']] = $v2['gxline_id'];
                                $flag = true;
                            } else {
                                unset($gxline[$v2['gxline_id']]);
                                $flag = false;
                            }
                        }
                    }
                    $tempGxline[$v2['gxline_id']] = ['gxline_id' => $v2['gxline_id'], 'relate_type' => $v2['relate_type'], 'flag' => $flag];
                }
            }
        }
        $gxline = array_unique($gxline);
        return $gxline;
    }

    

    /**
     * 匹配其他类型的 工艺线 如!=,>,<
     * @param $type 判断符号
     * @param $qrcodeValuee 二维码值
     * @param $rule 规则
     * @param $gxlineId 工艺线id
     * @return array
     */
    public function otherMatching($type,$qrcodeValue,$rule,$gxlineId)
    {
        //如果字段值为''则直接跳过
        if(trim($qrcodeValue) == '' && !in_array($type,['不包含','!='])){
            return false;
        }
        $flag = [];
        foreach ($rule as $k => $v) {
            if(trim($v) == ''){
                continue;
            }
            switch ($type){
                case '=':
                    if($qrcodeValue == $v){
                        $flag[] = $v;
                    }
                    break;
                case '!=':
                    if($qrcodeValue != $v && $v != ''){
                        $temp = ($qrcodeValue != $v);
                        $flag[] = true;
                    }else{
                        $flag[] = false;
                    }
                    break;
                case '包含':
                    if(strpos($qrcodeValue,$v) !== false && $v!=''){
                        $flag[] = $v;
                    }
                    break;
                case '不包含':
                    if(strpos($qrcodeValue,$v) == false && $v!=''){
                        $flag[] = strpos($qrcodeValue,$v);
                    }else{
                        $flag[] = true;
                    }
                    break;
            }
        }
        //如果是不包含,有一个规则是true，则没有此工艺线
        if($type == '不包含'){
            foreach ($flag as $k => $v) {
                if($v !== false){
                    return false;
                }
            }
        }
        //如果是!=
        if($type == '!='){
            foreach ($flag as $k => $v) {
                if($v == false){
                    return false;
                }
            }
        }

        //判断规则里可能有多个判断值，当有一个判断值满足条件时，则含有此工艺线
        if(count($flag) > 0){
            return $gxlineId;
        }
        return false;
    }
    
    /**
     * 处理数据,写入缓存
     * @return array
     */
    public function convertField()
    {       
        $rule = Db::name('field_rule')->field('a.*,b.fieldname')->alias('a')
                ->join('qrcode_fields b','a.field_id=b.id')
                ->join('gx_line c','a.gxline_id=c.id')
                ->order('gxline_id,sort asc')
                ->select();
//        $list = [];
//        foreach ($rule as $k => $v) {
//            $type = $v['type']!=''?$v['type']:'=';
//            $list[$v['fieldname']][] = ['fieldname'=>$v['fieldname'],'type'=>$type,'rule'=>$v['rule'],'gxline_id'=>$v['gxline_id'],'relate_type'=>$v['relate_type']];
//        }
        return $rule;
    }



    /**
     * 将第三方订单字段转换成 系统的二维码字段
     * @param array $qrcode 二维码数组
     * @param array $orignalQrcode 系统的二维码数组
     */
    public function convertThirdField($qrcode,$orignalQrcode)
    {
        $data = [];
        foreach ($orignalQrcode as $k => $v){
            $field = $v['third_fieldname']!=''?$v['third_fieldname']:$v['fieldname'];
            $data[$v['fieldname']] = isset($qrcode[$field])?$qrcode[$field]:'';
        }
        return $data;
    }
}
