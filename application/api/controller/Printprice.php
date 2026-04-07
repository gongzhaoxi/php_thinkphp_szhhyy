<?php

namespace app\api\controller;

use think\Db;

class Printprice extends \think\Controller
{
    public function index()
    {
        $number = input('number');
        $order = Db::name('order')->where('number', $number)->find();
        $havePay = Db::name('paid_record')->where('order_id',$order['id'])->where('type',1)->sum('have_pay');

        if(!$order){
            $this->error('此订单不存在');
        } 
        $orderId = $order['id'];
        $typeMap = ['1'=>'标准','2'=>'加急','3'=>'样板','4'=>'返修单' ,'5' => '单剪网', '6' => '单切料', '7' => '工程', '8' => '重做', '9' => '样板2'];
        //订单基本信息
        $headerInfo = ['dealer'=>$order['dealer'],'phone'=>$order['phone'],'date'=>date('Y-m-d',$order['addtime']),'send_address'=>$order['send_address'],
                    'order_number'=>$order['number'],'out_time'=>$order['end_time'],'qrcode'=>$_SERVER['HTTP_HOST'].$order['qrcode'],'sales_name'=>$order['sales_name'],
                    'have_pay'=>$havePay,'no_pay'=>round($order['total_price']-$order['have_pay'],2),'order_type'=>$typeMap[$order['type']]
                ];
//        Db::name('test_log')->insert(['para_number'=>$number,'out_number'=>$order['number']]);
        
        //订单产品
        $product = Db::name('order_price')->alias('a')->field('a.*,b.oc_id')
                ->join('order_calculation b','a.op_id=b.op_id','left')
                ->where(['a.order_id'=>$orderId])
                ->where("order_type!=3")
                ->order('a.order_type asc,a.og_id asc,a.op_id asc')
                ->select();
        foreach ($product as $k => $v) {
            if($v['order_type'] == 0 && empty($v['oc_id'])){
                unset($product[$k]);
            }
        }

        $series = Db::name('series')->select();
        $data = [];
        //处理数据,订单产品数据
        $z = 0;
        foreach($product as $k => $v){
            //根据系列里的是否显示,隐藏某个物料名称
            $temp = $this->getParentName($series,$v['series_id']);     //获取当前系列的所有父级数组       
            $material = $product[$k]['material'];
            foreach ($temp as $k2 => $v2) {
                //如果系列属性为，报价单中不显示，则替换成空
                if($v2['price_show'] == 1){
                    $material = str_replace($v2['name'], '', $material);                    
                }
            }
            $material = str_replace(['//','///','///'], '/', $material);                  
            $product[$k]['material'] = trim($material,'/');
            
            $materialTemp = "";//材质里是否拼接花件和铝型材和纱网
            if($v['flower_type']){ $materialTemp .= '/'.$v['flower_type']; }  
            //查询如果有绑定铝型材,则拼接铝型材的大面和小面
            $aluminum = Db::name('series_bom')->alias('a')->field('b.*')->join('bom_aluminum b','a.two_level=b.id')
                    ->where('series_id',$v['series_id'])->where('a.type',2)->where("one_level!=''")
                    ->find();  
    //         if($aluminum){
				// if($v['series_id'] <> 0){
				// 	$materialTemp .= '/'."{$aluminum['small']}"."*"."{$aluminum['big']}";
				// }
    //         }
			// 2024/8/7  麦健法 修改的，根据系列 “只有窗花” 才显示 对应型材的显示 “大小面”
			$type = Db::name('series')->where('id',$v['series_id'])->find();
			// end //
			
            $yarn_color = unserialize($v['yarn_color']);
            if($yarn_color['name'] != ''){ $materialTemp .='/网'.$yarn_color['name'];}
            
            $data[$k]['k'] = $z+1;
            $data[$k]['material'] = trim($material,'/').$materialTemp;
			// 2024/8/7  麦健法 修改的，根据系列 “只有窗花” 才显示 对应型材的显示 “大小面”
			if($type['type'] == 1){
				$data[$k]['material'] .= '/'."{$aluminum['small']}"."*"."{$aluminum['big']}";
			}
			// end //
            $data[$k]['color_name'] = $v['color_name'];
            $data[$k]['unit'] = '㎡';
            $data[$k]['all_width'] = $v['all_width'];
            $data[$k]['all_height'] = $v['all_height'];
            $data[$k]['count'] = $v['count'];
            $data[$k]['area'] = $v['area'];
			
			// $data[$k]['type'] = $type['type'];
			
            
            //价格和其他加价项
            $price = [];
            $price[] = '单价:'.$v['price'];
            $priceList = unserialize($v['other_add_price']);
            if(is_array($priceList) && count($priceList)>0){
                foreach($priceList as $k3 => $v3){
                    $price[] = $v3['descript'];
                }
            }
            $data[$k]['price'] = implode(',', $price);
            $data[$k]['rebate'] = $v['rebate'];
            
            $rebate = [];
            $rebate[] = $v['rebate_price'];
            //折后价和其它折后价
            $priceList = unserialize($v['other_add_price']);
            if(is_array($priceList) && count($priceList)>0){
                foreach($priceList as $k4 => $v4){
                    $rebate[] = $v4['value'];
                }
            }
            $data[$k]['rebate_price'] = implode(',', $rebate);
            $data[$k]['all_price'] = $v['all_price'];
            $z++;
        }
        
        //订单原材料
        $material = Db::name('order_material')->where('order_id', $orderId)->select();
        //处理数据,订单原材料
        $index = count($data)+1;
        $list = [];
        foreach ($material as $k => $v) {
            $list[$k]['k'] = $index;
            $list[$k]['material'] = $v['type'];
            $list[$k]['color_name'] = $v['color'];
            $list[$k]['unit'] = '㎡';
            $list[$k]['all_width'] = $v['width'];
            $list[$k]['all_height'] = $v['height'];
            $list[$k]['count'] = $v['count'];
            $list[$k]['area'] = $v['area'];
            $list[$k]['price'] = '单价:'.$v['price'];
            $list[$k]['rebate'] = $v['rebate'];
            $list[$k]['rebate_price'] = $v['rebate_price'];
            $list[$k]['all_price'] = $v['all_price'];
            $index++;
        }
        $lists = array_merge($data,$list);
        
        //报价单备注
        $note = '';
        if($order['note'] != ''){ $note.='订单备注'.$order['note'].'  ';}
        foreach($product as $k=>$v){
            if($v['note']!=''){  $note.='编号'.($k+1).$v['note'];}
        }

        $total = ['count'=>$order['count'],'area'=>$order['area'],'total_price'=>$order['total_price']];
        $array = ['header_info'=>$headerInfo,'data'=>$lists,'note'=>$note,'total'=>$total];
        
        $this->success('',$array);
    }
    
    /**
     * 递归获取系列所有父id的数组
     * @param array $series 系列数组,int $seriesId 系列id
     * @return array 父级数组（包含自身）
     */
    public function getParentName($series,$seriesId)
    {
        $arr = [];
        foreach($series as $v) {
            if($v['id'] == $seriesId){
                $arr[] = $v;
                $arr = array_merge(self::getParentName($series,$v['parent_id']),$arr);
            }
        }
        return $arr;
    }

    /**
     * 更新入库状态
     */
    public function updateStatus()
    {
        $number = input('number/a');
        if(!is_array($number) || !$number){
            $this->error('参数异常');
        }
        $res = Db::name('order')->whereIn('number',$number)->update(['status'=>7,'status2'=>7,'intime'=>time()]);
        if($res !== false){
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }
}
