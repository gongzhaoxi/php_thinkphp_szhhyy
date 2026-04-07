<?php

namespace app\index\controller;

use excel\Excel;
use think\Db;
use app\index\model\Order;

/**
 * 预生产计划
 */
class Preproduct extends Super
{
    public function initialize()
    {
        parent::initialize();
        $result = $this->check_status('preproduct');
        if(!$result){
            $this->error('未开启此功能');
        }
    }


    public function getwhere()
    {
        $search = input('get.');
        $where = [];
        if($search['endtime'] != ''){
            $where[] = ['a.endtime','=',"$search[endtime]"];
        }
        if($search['gxid'] != ''){
            $where[] = ['a.gxid','=',"$search[gxid]"];
        }
        if($search['unique_sn'] != ''){
            $where[] = ['c.unique_sn','=',"$search[unique_sn]"];
        }
        if($search['uname'] != ''){
            $where[] = ['c.uname','=',"$search[uname]"];
        }
        return $where;
    }


    /**
     * 导出列表数据
     */
    public function export()
    {
        $search = input('get.');
        $today = date('Y-m-d',time());
        $tomorrow = date('Y-m-d',time()+(24*3600));
        $yesterday = date('Y-m-d',time()-(24*3600));
        if(is_array($search) && count($search) > 0){
            $where = $this->getwhere();
            $todayData = count($where) != 0?$this->getBytime('',$where):[];
            $data = $todayData;
        }else{
            $todayData = $this->getBytime($today);
            $tomorrowData = $this->getBytime($tomorrow);
            $yesterdayData = $this->getBytime($yesterday);
            $data = array_merge($todayData,$tomorrowData,$yesterdayData);
        }

        $head = ['预生产日期','工序','预生产单数','完成数','面积','扇数','达成率'];
        $field = ['endtime','dname','pre_count','finished','area','snum','percent'];
        $excel = new Excel();
        $excel->export('预生产计划',$head,$data,$field);
    }

    /**
     * 查看未排产明细
     */
    public function noschedule()
    {
        $search = input('get.');
        $where = [];
        if($search['ordernum'] != ''){
            $where[] = ['ordernum','=',"{$search['ordernum']}"];
        }
        if($search['unique_sn'] != ''){
            $where[] = ['unique_sn','=',"{$search['unique_sn']}"];
        }
        $list = Db::name('order')->alias('a')->field('a.ordernum,a.unique_sn,a.pre_note,a.id')->join('preproduct b','a.id=b.orderid','left')
            ->where('b.orderid is null')
            ->where($where)
            ->order('id desc')
            ->paginate(60,false,['query'=>input('get.')]);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('search',$search);
        return $this->fetch();
    }
    /**
     * 预览导出数据
     *
     */
    public function review_excel(){
        $addtime = input('addtime');
        $ordernum = input('ordernum');
        $unique_sn = input('unique_sn');
        $this->assign('addtime',$addtime);
        $this->assign('ordernum',$ordernum);
        $this->assign('unique_sn',$unique_sn);
        $menu = Db::name('series_genre')->order('id asc')->select();
        $this->assign('menu',$menu);
        return $this->fetch();
    }
    
    /**
     * 异步加载导出数据
     */
    public function ajax_review_data(){
        if (request()->isAjax()){
            $addtime = input('addtime');
            $ordernum = input('ordernum');
            $unique_sn = input('unique_sn');
            $type = input("type");
            $gx_list = @include APP_DATA.'gx_list.php';
            $unit_list = @include APP_DATA.'ab_unit.php';
            $menu = Db::name('series_genre')->order('id asc')->select();
            $where = "";
            $sql_text = "";
            $data = array();
            $title = array();
            $unit_arr = array();
            $gx_arr = array();
            if (empty($addtime)){
                return ['code'=>1,'msg'=>'请选择排产日期！'];
                exit();
            }
            foreach ($gx_list as $gl){
                $gx_arr[$gl['id']] = $gl;
            }
            foreach ($unit_list as $ul){
                $unit_arr[$ul['id']] = $ul;
            }
    
            $starttime = strtotime(substr($addtime,0,10));
            $endtime = strtotime(substr($addtime,12).' 23:59:59');
            $day = ceil(($endtime-$starttime)/86400);
            $where .= " addtime between $starttime and $endtime";
    
            if (!empty($ordernum)){
                $order = Db::name('order_attach')->where("fieldname='ordernum' and value=$ordernum")->find();
                $order_id = $order['orderid'];
                $where .= " and orderid = $order_id";
                $sql_text = "sales_number=$ordernum and ";
            }
            if (!empty($unique_sn)){
                $order = Db::name('order_attach')->where("fieldname='produce_no' and value=$unique_sn")->find();
                $order_id = $order['orderid'];
                $where .= " and orderid = $order_id";
                $sql_text .= " product_no=$unique_sn";
            }
            if ($type==1){
                //预排产汇总
                $condition = Db::name('preproduct')->where($where)->group('id')->column('id');
                $result = Db::name('preproduct_gx')->whereIn('pre_id',implode(',',$condition))->order('id asc')->select();
                if ($result){
                    $vc=$this->total_plan_data($result,$day);
                    $data = $vc['data'];
                }
            }elseif ($type==2){
                $ct=$this->glass_data($sql_text);
                $data = $ct['data'];
                $title = $ct['title'];
            }else{
                $id = input("id/d");
                if ($id==0){
                    $result = Db::name('preproduct')->where("$where")->order('id asc')->select();
                }else {
                    $series_arr = Db::name('series')->where('gid',$id)->group('xname')->column('xname');
                    $orderid_arr = Db::name('order')->whereIn("pname",implode(',',$series_arr))->group('id')->column('id');
                    if (empty($series_arr)){
                        $result = Db::name('preproduct')->where("$where")->order('id asc')->select();
                    }else {
                        if (empty($orderid_arr)){
                            $this->error('没有数据！');
                            exit();
                        }
                        $result = Db::name('preproduct')->where("$where and orderid in (".implode(',',$orderid_arr).")")->order('id asc')->select();
                    }
                    
                }
                if (empty($result)){
                    $this->error('没有数据！');
                    exit();
                }
                $genre=$this->pre_genre_data($result,$gx_arr);
                $data = $genre['data'];
                $title = $genre['title'];
            }
            return ['code'=>0,'data'=>$data,'date'=>$addtime,'title'=>$title];
        }
    }
    
    public function glass_data($sql_text){
        $data = array();
        $title = array();
        $headarr = ['status_text'=>'状态','batch'=>'批次','sales_number'=>'销售单号','product_no'=>'生产单号','specs'=>'规格','book_count'=>'订购数','back_count'=>'回厂数','type'=>'种类'];
        $extendField = Db::name('glass_field')->select();
        foreach ($extendField as $k => $v) {
            $headarr[$v['key']] = $v['value'];
        }
        $result = Db::name('glass_plan')->where($sql_text)->order('id asc')->select();
        if ($result){
            foreach ($result as $k=>$res){
                $result[$k] = array_merge($res,json_decode($res['extension'],true));
                if ($res['book_count']>$res['back_count'] && $res['back_count']>0){
                    $result[$k]['status_text'] = '部分回';
                }else if($res['back_count']==0){
                    $result[$k]['status_text'] = '未回';
                }else{
                    $result[$k]['status_text'] = '已回';
                }
            }
            $data = $result;
        }
        $title = $headarr;
        return ['data'=>$data,'title'=>$title];
    }
    public function genre_excel_data($where,$gx_arr){
        $data = array();
        $title = array();
        $time = time();
        $list = array();
        $menu = Db::name('series_genre')->order('id asc')->select();
        if ($menu){
            foreach ($menu as $mu){
                $gx_line = array();
                $series_arr = Db::name('series')->where('gid',$mu['id'])->group('xname')->column('xname');
                $orderid_arr = Db::name('order')->whereIn("pname",implode(',',$series_arr))->group('id')->column('id');
                if (empty($series_arr)){
                    $result = Db::name('preproduct')->where("$where")->order('id asc')->select();
                }else {
                    if (empty($orderid_arr)){
                        $this->error('没有数据！');
                        exit();
                    }
                    $result = Db::name('preproduct')->where("$where and orderid in (".implode(',',$orderid_arr).")")->order('id asc')->select();
                }
                if (empty($result)){
                    $this->error('没有数据！');
                    exit();
                }
                foreach ($result as $k=>$res){
                    $list[$k] = array();
                    $orderid = $res['orderid'];
                    $order = Db::name('order')->where('id',$orderid)->find();
                    $gx_line=array_merge($gx_line,explode(',',$order['gxline_id']));
                    $order_detail = order_attach($orderid);
                    $list[$k]['bad_cause'] = '';
                    $list[$k]['over_cause'] = $order['cause']?$order['cause']:'';
                    $list[$k]['schedule_time'] = $res['start_date'];
                    $list[$k]['ordernum'] = $order_detail['ordernum'];
                    $list[$k]['product_no'] = $order_detail['produce_no'];
                    $list[$k]['area'] = $order_detail['area'];
                    $list[$k]['width'] = $order_detail['width'];
                    $list[$k]['height'] = $order_detail['height'];
                    $list[$k]['pname'] = $order_detail['pname'];
                    $list[$k]['uname'] = $order_detail['uname'];
                    $list[$k]['color'] = $order_detail['color'];
                    $list[$k]['snum'] = $order_detail['snum'];
                    $list[$k]['doornum'] = $order_detail['doornum'];
                    $list[$k]['screenwin'] = $order_detail['screenwin'];
                    $list[$k]['fixedglassnum'] = $order_detail['fixedglassnum'];
                    $list[$k]['addtime'] = date("Y-m-d",$res['addtime']);
                    $list[$k]['plan_time'] = 0;
                    $list[$k]['fact_time'] = 0;
                    $list[$k]['send_time'] = '';
                    $schedule_gx_list = Db::name('preproduct_gx')->where('pre_id',$res['id'])->select();
                    $list[$k]['gx_list'] = array();
    
                    foreach ($schedule_gx_list as $kc=>$sgl){
                        $gx_detail = array();
                        $gxid = $sgl['gxid'];
                        $order_id = $sgl['orderid'];
                        $gx_detail['plan_finish_time'] = $sgl['endtime']=='1970-01-01'?'':$sgl['endtime'];
                        $gx_detail['finish_time'] = '';
                        $gx_detail['isover'] = '否';
                        $gx_check = Db::name('flow_check')->where("orstatus=$gxid and orderid=$order_id")->find();
                        if ($gx_check){
                        if ($gx_check['endtime']>0){
                            $gx_detail['finish_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                            $gx_check['endtime']<$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }else {
                            $time>$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }
                            $gx_check['state']>0?$list[$k]['bad_cause'] .= $gx_check['stext'].'；':'';
                        }else {
                            $time>strtotime('+1 day',strtotime($sgl['endtime']))?$gx_detail['isover'] = '是':'';
                        }
                        if (strtotime($sgl['endtime'])>strtotime($list[$k]['plan_time'])){
                            $list[$k]['plan_time'] = $sgl['endtime'];
                            if ($gx_check['endtime']>0){
                                $list[$k]['fact_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                            }
                        }
                        $list[$k]['gx_list'][$sgl['gxid']] = $gx_detail;
                    }
                }
                array_push($data,$list);
                $gx_line = array_unique($gx_line);
                //获取工序标题
                $titles = Db::name('gx_list')->whereIn('lid',implode(',',$gx_line))->order('orderby asc')->select();
                array_push($title,$titles);
                unset($list);
                unset($gx_line);
            }
    
        }else {
            $gx_line = array();
            $result = Db::name('preproduct')->where("$where")->order('id asc')->select();
            foreach ($result as $k=>$res){
                $list[$k] = array();
                $orderid = $res['orderid'];
                $order = Db::name('order')->where('id',$orderid)->find();
                $gx_line=array_merge($gx_line,explode(',',$order['gxline_id']));
                $order_detail = order_attach($orderid);
                $list[$k]['bad_cause'] = '';
                $list[$k]['over_cause'] = $order['cause']?$order['cause']:'';
                $list[$k]['schedule_time'] = $res['start_date'];
                $list[$k]['ordernum'] = $order_detail['ordernum'];
                $list[$k]['product_no'] = $order_detail['produce_no'];
                $list[$k]['area'] = $order_detail['area'];
                $list[$k]['width'] = $order_detail['width'];
                $list[$k]['height'] = $order_detail['height'];
                $list[$k]['pname'] = $order_detail['pname'];
                $list[$k]['uname'] = $order_detail['uname'];
                $list[$k]['color'] = $order_detail['color'];
                $list[$k]['snum'] = $order_detail['snum'];
                $list[$k]['doornum'] = $order_detail['doornum'];
                $list[$k]['screenwin'] = $order_detail['screenwin'];
                $list[$k]['fixedglassnum'] = $order_detail['fixedglassnum'];
                $list[$k]['addtime'] = date("Y-m-d",$res['addtime']);
                $list[$k]['plan_time'] = 0;
                $list[$k]['fact_time'] = 0;
                $list[$k]['send_time'] = '';
                $schedule_gx_list = Db::name('preproduct_gx')->where('pre_id',$res['id'])->select();
                $list[$k]['gx_list'] = array();
    
                foreach ($schedule_gx_list as $kc=>$sgl){
                    $gx_detail = array();
                    $gxid = $sgl['gxid'];
                    $order_id = $sgl['orderid'];
                    $gx_detail['plan_finish_time'] = $sgl['endtime']=='1970-01-01'?'':$sgl['endtime'];
                    $gx_detail['finish_time'] = '';
                    $gx_detail['isover'] = '否';
                    $gx_check = Db::name('flow_check')->where("orstatus=$gxid and orderid=$order_id")->find();
                    if ($gx_check){
                    if ($gx_check['endtime']>0){
                            $gx_detail['finish_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                            $gx_check['endtime']<$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }else {
                            $time>$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }
                        $gx_check['state']>0?$list[$k]['bad_cause'] .= $gx_check['stext'].'；':'';
                    }else {
                        $time>strtotime('+1 day',strtotime($sgl['endtime']))?$gx_detail['isover'] = '是':'';
                    }
                    if (strtotime($sgl['endtime'])>strtotime($list[$k]['plan_time'])){
                        $list[$k]['plan_time'] = $sgl['endtime'];
                        if ($gx_check['endtime']>0){
                            $list[$k]['fact_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                        }
                    }
                    $list[$k]['gx_list'][$sgl['gxid']] = $gx_detail;
                }
            }
            array_push($data,$list);
            $gx_line = array_unique($gx_line);
            //获取工序标题
            $titles = Db::name('gx_list')->whereIn('lid',implode(',',$gx_line))->order('orderby asc')->select();
            array_push($title,$titles);
        }
    
        return ['data'=>$data,'title'=>$title];
    }
    public function pre_genre_data($result,$gx_arr){
        $data = array();
        $title = array();
        $time = time();
         
        $list = array();
        $gx_line = array();
        if (!empty($result)){
            foreach ($result as $k=>$res){
                $list[$k] = array();
                $orderid = $res['orderid'];
                $order = Db::name('order')->where('id',$orderid)->find();
                $gx_line=array_merge($gx_line,explode(',',$order['gxline_id']));
                $order_detail = order_attach($orderid);
                $list[$k]['bad_cause'] = '';
                $list[$k]['over_cause'] = $order['cause']?$order['cause']:'';
                $list[$k]['schedule_time'] = $res['start_date'];
                $list[$k]['ordernum'] = $order_detail['ordernum'];
                $list[$k]['product_no'] = $order_detail['produce_no'];
                $list[$k]['area'] = $order_detail['area'];
                $list[$k]['width'] = $order_detail['width'];
                $list[$k]['height'] = $order_detail['height'];
                $list[$k]['pname'] = $order_detail['pname'];
                $list[$k]['uname'] = $order_detail['uname'];
                $list[$k]['color'] = $order_detail['color'];
                $list[$k]['snum'] = $order_detail['snum'];
                $list[$k]['doornum'] = $order_detail['doornum'];
                $list[$k]['screenwin'] = $order_detail['screenwin'];
                $list[$k]['fixedglassnum'] = $order_detail['fixedglassnum'];
                $list[$k]['addtime'] = date("Y-m-d",$res['addtime']);
                $list[$k]['plan_time'] = 0;
                $list[$k]['fact_time'] = 0;
                $list[$k]['send_time'] = '';
                $schedule_gx_list = Db::name('preproduct_gx')->where('pre_id',$res['id'])->select();
                $list[$k]['gx_list'] = array();
            
                foreach ($schedule_gx_list as $kc=>$sgl){
                    $gx_detail = array();
                    $gxid = $sgl['gxid'];
                    $order_id = $sgl['orderid'];
                    $gx_detail['plan_finish_time'] = $sgl['endtime'];
                    $gx_detail['finish_time'] = '';
                    $gx_detail['isover'] = '否';
                    $gx_check = Db::name('flow_check')->where("orstatus=$gxid and orderid=$order_id")->find();
                    if ($gx_check){
                    if ($gx_check['endtime']>0){
                            $gx_detail['finish_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                            $gx_check['endtime']<$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }else {
                            $time>$gx_check['starttime']+$gx_arr[$gxid]['work_value']*(24*3600)?$gx_detail['isover']='是':'';
                        }
                        $gx_check['state']>0?$list[$k]['bad_cause'] .= $gx_check['stext']:'';
                    }else {
                        $time>strtotime('+1 day',strtotime($sgl['endtime']))?$gx_detail['isover'] = '是':'';
                    }
                    if (strtotime($sgl['endtime'])>strtotime($list[$k]['plan_time'])){
                        $list[$k]['plan_time'] = $sgl['endtime'];
                        if ($gx_check['endtime']>0){
                            $list[$k]['fact_time'] = date('Y-m-d H:i:s',$gx_check['endtime']);
                        }
                    }
                    $list[$k]['gx_list'][$sgl['gxid']] = $gx_detail;
                }
            }
        }
        $data=$list;
        $gx_line = array_unique($gx_line);
        //获取工序标题
        $title = Db::name('gx_list')->whereIn('lid',implode(',',$gx_line))->order('orderby asc')->select();
        return ['data'=>$data,'title'=>$title];
    }
    public function total_plan_data($result,$day){
        $gx_list = @include APP_DATA.'gx_list.php';
        $unit_list = @include APP_DATA.'ab_unit.php';
        $menu = Db::name('series_genre')->order('id asc')->select();
        $data = array();
        $title = array();
        $unit_arr = array();
        $gx_arr = array();
        foreach ($gx_list as $gl){
            $gx_arr[$gl['id']] = $gl;
        }
        foreach ($unit_list as $ul){
            $unit_arr[$ul['id']] = $ul;
        }
        if (empty($menu)){
            $list_arr = array();
            $list = array();
            if (!empty($result)){
                foreach ($result as $res){
                    if (!isset($list[$res['gxid']])){
                        $list[$res['gxid']] = array();
                        $list[$res['gxid']]['kname'] = '默认类别';
                        $list[$res['gxid']]['gxname'] = $gx_arr[$res['gxid']]['dname'];
                        $list[$res['gxid']]['final_value'] = $gx_arr[$res['gxid']]['worktime'];
                        $list[$res['gxid']]['unit'] = $unit_arr[$gx_arr[$res['gxid']]['work_unit']]['label'];
                        $list[$res['gxid']]['num'] = 0;
                        $list[$res['gxid']]['area'] = 0;
                        $list[$res['gxid']]['b_num'] = 0;
                        $list[$res['gxid']]['s_num'] = 0;
                        $list[$res['gxid']]['finish_num'] = 0;
                        $list[$res['gxid']]['finish_area'] = 0;
                        $list[$res['gxid']]['finish_b_num'] = 0;
                        $list[$res['gxid']]['finish_s_num'] = 0;
                        $list[$res['gxid']]['finish_man'] = 0;
                        $list[$res['gxid']]['day_value'] = 0;
                        $list[$res['gxid']]['hour_value'] = 0;
                        $list[$res['gxid']]['value'] = 0;
                        $list[$res['gxid']]['people'] = 0;
                    }
                    $orderid = $res['orderid'];
                    $gxid = $res['gxid'];
                    $order_detail = order_attach($res['orderid']);
                    $list[$res['gxid']]['num'] += $order_detail['snum']?$order_detail['snum']:0;
                    $list[$res['gxid']]['area'] += round($order_detail['area'],2);
                    $list[$res['gxid']]['b_num'] += $order_detail['doornum']?$order_detail['doornum']:0;
                    $list[$res['gxid']]['s_num'] += $order_detail['screenwin']?$order_detail['screenwin']:0;
                    //检索该排产工序是否完成
                    $finish = Db::name('flow_check')->where("orderid=$orderid and orstatus=$gxid")->find();
                    if ($finish){
                        $list[$res['gxid']]['finish_num'] += $order_detail['snum']?$order_detail['snum']:0;
                        $list[$res['gxid']]['finish_area'] += round($order_detail['area'],2);
                        $list[$res['gxid']]['finish_b_num'] += $order_detail['doornum']?$order_detail['doornum']:0;
                        $list[$res['gxid']]['finish_s_num'] += $order_detail['screenwin']?$order_detail['screenwin']:0;
                        $list[$res['gxid']]['finish_man']++;
                    }
                }
                
                //计算汇总值
                foreach ($list as $k=>$lt){
                    $list[$k]['day_value'] = round($list[$k]['finish_area']/$day,2);
                    $list[$k]['hour_value'] = round($list[$k]['day_value']/8,2);
                    $list[$k]['value'] = $list[$k]['area']-$list[$k]['finish_value'];
                    $list[$k]['day_value']*$list[$k]['finish_man']==0?'':$list[$k]['people'] = intval($list[$k]['value']/($list[$k]['day_value']*$list[$k]['finish_man']));
                    $list_arr['total_num'] += $lt['num'];
                    $list_arr['total_area'] += round($lt['area'],2);
                    $list_arr['total_b_num'] += $lt['b_num'];
                    $list_arr['total_s_num'] += $lt['s_num'];
                    $list_arr['total_finish_num'] += $lt['finish_num'];
                    $list_arr['total_finish_area'] += round($lt['finish_area'],2);
                    $list_arr['total_finish_bnum'] += $lt['finish_b_num'];
                    $list_arr['total_finish_snum'] += $lt['finish_s_num'];
                    $list_arr['total_man'] += $lt['finish_man'];
                    $list_arr['total_day_value'] += $list[$k]['day_value'];
                    $list_arr['total_hour_value'] += $list[$k]['hour_value'];
                    $list_arr['total_value'] += $list[$k]['value'];
                    $list_arr['total_people'] += $list[$k]['people'];
                }
                $list_arr['list'] = $list;
                empty($list_arr)?'':array_push($data,$list_arr);
            }
        }else {
            //先筛选物料类别订单
            foreach ($menu as $mu){
                $list_arr = array();
                $list = array();
                $gid = $mu['id'];
                $series_arr = Db::name('series')->where('gid',$gid)->group('xname')->column('xname');
                $orderid_arr = Db::name('order')->whereIn("pname",implode(',',$series_arr))->group('id')->column('id');
                if (!empty($result)){
                    foreach ($result as $res){
                        if (in_array($res['orderid'],$orderid_arr)){
                            if (!isset($list[$res['gxid']])){
                                $list[$res['gxid']] = array();
                                $list[$res['gxid']]['kname'] = $mu['name'];
                                $list[$res['gxid']]['gxname'] = $gx_arr[$res['gxid']]['dname'];
                                $list[$res['gxid']]['final_value'] = $gx_arr[$res['gxid']]['worktime'];
                                $list[$res['gxid']]['unit'] = $unit_arr[$gx_arr[$res['gxid']]['work_unit']]['label'];
                                $list[$res['gxid']]['num'] = 0;
                                $list[$res['gxid']]['area'] = 0;
                                $list[$res['gxid']]['b_num'] = 0;
                                $list[$res['gxid']]['s_num'] = 0;
                                $list[$res['gxid']]['finish_num'] = 0;
                                $list[$res['gxid']]['finish_area'] = 0;
                                $list[$res['gxid']]['finish_b_num'] = 0;
                                $list[$res['gxid']]['finish_s_num'] = 0;
                                $list[$res['gxid']]['finish_man'] = 0;
                                $list[$res['gxid']]['day_value'] = 0;
                                $list[$res['gxid']]['hour_value'] = 0;
                                $list[$res['gxid']]['value'] = 0;
                                $list[$res['gxid']]['people'] = 0;
                            }
                            $orderid = $res['orderid'];
                            $gxid = $res['gxid'];
                            $order_detail = order_attach($res['orderid']);
                            $list[$res['gxid']]['num'] += $order_detail['snum']?$order_detail['snum']:0;
                            $list[$res['gxid']]['area'] += round($order_detail['area'],2);
                            $list[$res['gxid']]['b_num'] += $order_detail['doornum']?$order_detail['doornum']:0;
                            $list[$res['gxid']]['s_num'] += $order_detail['screenwin']?$order_detail['screenwin']:0;
                            //检索该排产工序是否完成
                            $finish = Db::name('flow_check')->where("orderid=$orderid and orstatus=$gxid")->find();
                            if ($finish){
                                $list[$res['gxid']]['finish_num'] += $order_detail['snum']?$order_detail['snum']:0;
                                $list[$res['gxid']]['finish_area'] += round($order_detail['area'],2);
                                $list[$res['gxid']]['finish_b_num'] += $order_detail['doornum']?$order_detail['doornum']:0;
                                $list[$res['gxid']]['finish_s_num'] += $order_detail['screenwin']?$order_detail['screenwin']:0;
                                $list[$res['gxid']]['finish_man']++;
                            }
                        }
                    }
                    //计算汇总值
                    foreach ($list as $k=>$lt){
                        $list[$k]['day_value'] = round($list[$k]['finish_area']/$day,2);
                        $list[$k]['hour_value'] = round($list[$k]['day_value']/8,2);
                        $list[$k]['value'] = $list[$k]['area']-$list[$k]['finish_value'];
                        $list[$k]['day_value']*$list[$k]['finish_man']==0?'':$list[$k]['people'] = intval($list[$k]['value']/($list[$k]['day_value']*$list[$k]['finish_man']));
                        $list_arr['total_num'] += $lt['num'];
                        $list_arr['total_area'] += round($lt['area'],2);
                        $list_arr['total_b_num'] += $lt['b_num'];
                        $list_arr['total_s_num'] += $lt['s_num'];
                        $list_arr['total_finish_num'] += $lt['finish_num'];
                        $list_arr['total_finish_area'] += round($lt['finish_area'],2);
                        $list_arr['total_finish_bnum'] += $lt['finish_b_num'];
                        $list_arr['total_finish_snum'] += $lt['finish_s_num'];
                        $list_arr['total_man'] += $lt['finish_man'];
                        $list_arr['total_day_value'] += $list[$k]['day_value'];
                        $list_arr['total_hour_value'] += $list[$k]['hour_value'];
                        $list_arr['total_value'] += $list[$k]['value'];
                        $list_arr['total_people'] += $list[$k]['people'];
                    }
                    $list_arr['list'] = $list;
                    empty($list_arr)?'':array_push($data,$list_arr);
                }
            }
        }
        return ['data'=>$data,'title'=>$title];
    }
    /**
     * 导出预生产计划汇总
     */
    public function  export_plan_data(){
        $addtime = input('addtime');
        $ordernum = input('ordernum');
        $unique_sn = input('unique_sn');
        $gx_list = @include APP_DATA.'gx_list.php';
        $unit_list = @include APP_DATA.'ab_unit.php';
        $menu = Db::name('series_genre')->order('id asc')->select();
        $where = "";
        $sql_text = "";
        $data = array();
        $title = array();
        $unit_arr = array();
        $gx_arr = array();
        if (empty($addtime)){
            $this->error('请选择排产日期！');
            exit();
        }
        foreach ($gx_list as $gl){
            $gx_arr[$gl['id']] = $gl;
        }
        foreach ($unit_list as $ul){
            $unit_arr[$ul['id']] = $ul;
        }
    
        $starttime = strtotime(substr($addtime,0,10));
        $endtime = strtotime(substr($addtime,12).' 23:59:59');
        $day = ceil(($endtime-$starttime)/86400);
        $where .= " addtime between $starttime and $endtime";
    
        if (!empty($ordernum)){
            $order = Db::name('order_attach')->where("fieldname='ordernum' and value=$ordernum")->find();
            $order_id = $order['orderid'];
            $where .= " and orderid = $order_id";
            $sql_text = "sales_number=$ordernum and ";
        }
        if (!empty($unique_sn)){
            $order = Db::name('order_attach')->where("fieldname='produce_no' and value=$unique_sn")->find();
            $order_id = $order['orderid'];
            $where .= " and orderid = $order_id";
            $sql_text .= " product_no=$unique_sn";
        }
        //汇总表
        $condition = Db::name('preproduct')->where($where)->group('id')->column('id');
        $result = Db::name('preproduct_gx')->whereIn('pre_id',implode(',',$condition))->order('id asc')->select();
        if ($result){
            $vc=$this->total_plan_data($result,$day);
            $data = $vc['data'];
        }
        //玻璃表
        $ct=$this->glass_data($sql_text);
        $glass_data = $ct['data'];
        $glass_title = $ct['title'];
        //物料类别表
         
        $genre=$this->genre_excel_data($where,$gx_arr);
        $genre_data = $genre['data'];
        $genre_title = $genre['title'];
        //            echo json_encode($genre_title);
        //             exit();
        schedule_plan_excel($genre_data,$glass_data,$data,$genre_title,$glass_title,$addtime);
        //             return ['code'=>0,'msg'=>'导出成功'];
    }
    /**
     * 保存订单备注
     */
    public function savePrenote()
    {
        $id = input('id');
        $prenote = input('pre_note');

        $res = Db::name('order')->where('id',$id)->update(['pre_note'=>$prenote]);
        if($res !== false){
            $this->_success('操作成功');
        }
        $this->_error('操作失败');
    }

    /**
     * 列表
     */
    public function index()
    {
        $search = input('get.');
        $today = date('Y-m-d',time());
        $tomorrow = date('Y-m-d',time()+(24*3600));
        $yesterday = date('Y-m-d',time()-(24*3600));
        if(is_array($search) && count($search) > 0){

            $where = $this->getwhere();
            $todayData = count($where) != 0?$this->getBytime('',$where):[];
        }else{
            $todayData = $this->getBytime($today);
            $tomorrowData = $this->getBytime($tomorrow);
            $yesterdayData = $this->getBytime($yesterday);
        }
        $gxlist = Db::name('gx_list')->where('lid','>',0)->select();
        $searchStr ="";
        foreach ($search as $k => $v){
            $searchStr .= "&$k=$v";
        }
        $allorder = Db::name('order')->count();//总单数
        $scheduled = Db::name('preproduct')->alias('a')->field('a.*,b.ordernum,b.unique_sn,b.uname,b.color,b.pname')
            ->join('order b','a.orderid=b.id')
            ->where('a.status',1)
            ->count();//已排数

        $this->assign('allorder',$allorder);
        $this->assign('scheduled',$scheduled);
        $this->assign('gx_list',$gxlist);
        $this->assign('today',$todayData);
        $this->assign('tomorrow',$tomorrowData);
        $this->assign('yesterday',$yesterdayData);
        $this->assign('search',$search);
        $this->assign('search_str',$searchStr);
        return $this->fetch();
    }


    /**
     * 获取指定时间 按预生产时间分组的统计情况
     */
    public function getBytime($date,$where='')
    {
        if($where == ''){
            //默认今天，明天，昨天的数据
            $list = Db::name('preproduct_gx')->field('a.*,b.dname,count(a.id) as pre_count')->alias('a')
                    ->join('gx_list b','a.gxid=b.id')
                    ->join('order c','a.orderid=c.id')
                    ->join('preproduct d','a.pre_id=d.id')
                    ->where('a.endtime',$date)
                    ->where('d.status',1)
                    ->group('gxid')
                    ->order('b.orderby')
                    ->select();
            $allOrderId = Db::name('preproduct_gx')->where('endtime',$date)->select();
        }else{
            //否则使用筛选的数据
             $array = Db::name('preproduct_gx')->field('a.*,b.dname')->alias('a')
                    ->join('gx_list b','a.gxid=b.id')
                    ->join('order c','a.orderid=c.id')
                    ->join('preproduct d','a.pre_id=d.id')
                    ->where($where)
                    ->where('d.status',1)
                    ->order('b.orderby')
                    ->select();
            $list = [];
            //按预生产日期+工序id 汇总
            foreach ($array as $k => $v) {
                $keys = $v['endtime'].$v['gxid'];
                $list[$keys] = $v;               
                if(isset($temp[$keys])){                    
                    $list[$keys]['pre_count'] = count($temp[$keys])+1;
                }else{
                    $list[$keys]['pre_count'] = 1;
                }
                $temp[$keys][] = $v;
                
            }
            $allOrderId = $array;
        }
    
        $orderId = [];
        foreach ($allOrderId as $key => $value) {
            $orderId[$value['gxid'].$value['endtime']][] = $value['orderid'];
        }
        foreach ($list as $k => $v) {
            $orderid = isset($orderId[$v['gxid'].$v['endtime']])?$orderId[$v['gxid'].$v['endtime']]:[];
            $finished = Db::name('flow_check')->alias('a')
                    ->whereIn('a.orderid',$orderid)->where('orstatus',$v['gxid'])->where('endtime!=0')
                    ->count();
            //获取报异常数
            $gxid = $v['gxid'];
            $endtime = strtotime($v['endtime']);
            $endtime_e = strtotime("+1 day",$v['endtime']);
            $num = Db::name('flow_check')->where("orstatus=$gxid and state=1 and error_time>=$endtime and error_time<$endtime_e")->count();
            $areaSnum = Db::name('order')->field('sum(area) as area,sum(snum) as snum')->whereIn('id',$orderid)->find();
            $list[$k]['finished'] = $finished;
            $list[$k]['badnum'] = $num;
            $list[$k]['area'] = $areaSnum['area'];
            $list[$k]['snum'] = Db::name('order_attach')->whereIn('orderid',$orderid)->where('fieldname','doornum')->sum('value');
            $percent = $v['pre_count']!=0?round($finished/$v['pre_count'],2):0;
            $list[$k]['percent'] = ($percent*100).'%';
        }
        return $list;
    }


    /**
     * 预生产计划明细
     */
    public function detail()
    {
        $endtime = input('endtime');
        $gxid = input('gxid');
        $search = input('get.');
        $where = [];
        if($search['ordernum'] != ''){
            $where[] = ['ordernum','=',"$search[ordernum]"];
        }
        if($search['unique_sn'] != ''){
            $where[] = ['unique_sn','=',"$search[unique_sn]"];
        }
        if($search['state'] != ''){
            $where[] = ['unique_sn','=',"$search[state]"];
        }
        
        $all = Db::name('preproduct_gx')->alias('a')->field('a.gxid,a.orderid,b.unique_sn,b.ordernum,b.id')
                ->join('order b','a.orderid=b.id')
                ->join('preproduct c','a.pre_id=c.id')
                ->where(['a.endtime'=>$endtime,'a.gxid'=>$gxid,'c.status'=>1])
                ->where($where)
                ->select();
        $all = order_attach($all);
        $sort = [];
        foreach ($all as $k => $v) {
            $res = Db::name('flow_check')->alias('a')->field('a.*,b.uname')
                    ->join('login b','a.uid=b.id')->where(['orstatus'=>$v['gxid'],'orderid'=>$v['orderid']])->find();
            $all[$k]['endtime'] = $res['endtime'];
            $all[$k]['state'] = $res['state'];
            $all[$k]['uname'] = $res['uname'];
            $all[$k]['fid'] = $res['id'];
            $sort[] = $res['endtime'];
        }
        array_multisort($sort,SORT_ASC,$all,SORT_ASC);//排序

        $ordernum = Db::name('preproduct_gx')->alias('a')->field('b.unique_sn,b.ordernum')
                ->join('order b','a.orderid=b.id')
                ->where(['a.endtime'=>$endtime,'gxid'=>$gxid])
                ->column('ordernum');
        $uniqueSn = Db::name('preproduct_gx')->alias('a')->field('b.unique_sn,b.ordernum')
                ->join('order b','a.orderid=b.id')
                ->where(['a.endtime'=>$endtime,'gxid'=>$gxid])
                ->column('unique_sn');
        
        $this->assign('list',$all);
        $this->assign('ordernum',$ordernum);
        $this->assign('unique_sn',$uniqueSn);
        $this->assign('search',$search);
        return $this->fetch();
    }

    /**
     * 刷新单个 及后面的工序
     */
    public function oneRefresh()
    {
        $orderid = input('orderid');
        $gxid = input('gxid');
        $startDate = input('start_date');//结束时间
        $pregxId = input('id');//预生产计划工序表的id

        $current = Db::name('preproduct_gx')->where('orderid',$orderid)->where('gxid',$gxid)->find();
        $endGx = Db::name('flow_check')->where('orderid',$orderid)->column('orstatus');//已报工的订单
        //全部工序id
        $orderGx = Db::name('preproduct_gx')->where('orderid',$orderid)->column('gxid');
        //订单的同级工序id
        $startGx = Db::name('preproduct_gx')->field('gxid as gx_id,level')->where('orderid',$orderid)->where('level','>=',$current['level'])->select();//同级工序
        $startId = array_column($startGx,'gx_id');

        $gxValue =  @include APP_DATA.'gx_list.php';
        $startDate = date('Y-m-d',strtotime($startDate));
        $list = $this->calculateGx($startId, $orderGx,$current['level'],'',[$startDate]);//计算结束时间

        //将订单工序中未绑定在 固定流水里的  放在最后面
        $lastResult = [];
//        if(count($list) > 1) {
//            $lastGx = $this->getNobindGx($orderGx);
//            //将订单工序中 没头没尾的放在最后一个已第一个开始时间计算
//            $resultGx = array_merge(array_column($list,'gx_id'),$lastGx);//已经计算的工序+未绑定的工序
//            $noBeforeAfter = array_diff($orderGx,$resultGx);//没头没尾的工序
//            if($noBeforeAfter){
//                $lastGx = array_merge($lastGx,$noBeforeAfter);
//            }
//            $maxlevel = max(array_column($list, 'level'));
//            $maxEnd = max(array_column($list, 'endtime'));
//
//            $lastResult = $this->calculateGx($lastGx, $orderGx, $maxlevel + 1, '', [$maxEnd]);
//
//
//        }
        $allResult = array_merge($list, $lastResult);
        $list = $this->decr($allResult);//将结束时间减 1天
        //执行更新
        try {
            $preproductGx = model('PreproductGx');
            foreach ($list as $k => $v) {
                //所选工序未报结束的工序 id大于当前预生产计划工序表id 才更新数据
                if(!in_array($v['gx_id'],$endGx)) {
                    $res = Db::name('preproduct_gx')->where(['orderid' => $orderid, 'gxid' => $v['gx_id']])->where('id','>=',$current['id'])
                        ->update(['endtime' => $v['endtime']]);
                }
            }
            $this->_success('刷新成功');
        } catch (\Exception $e) {
            $this->_error('操作失败');
        }
    }

//    public function allrefresh()
//    {
//        set_time_limit(0);
//        $sql="SELECT `a`.*,`b`.`ordernum`,`b`.`unique_sn`,`b`.`uname`,`b`.`color`,`b`.`pname` FROM `bg_preproduct` `a` INNER JOIN `bg_order` `b` ON `a`.`orderid`=`b`.`id` INNER JOIN `bg_preproduct_gx` `c` ON `a`.`id`=`c`.`pre_id` WHERE  `c`.`endtime` = '1970-01-01'  AND `a`.`status` = '1' GROUP BY `a`.`id` ORDER BY id desc";
//        $list = Db::query($sql);
//        foreach ($list as $k => $v) {
//            $id = $v['orderid'];
//            $startDate = $v['start_date'];
//
////        $ordergx = Db::name('preproduct_gx')->field('gxid as gx_id,level')->where("orderid",$id)->order('level')->select();
//            $order = Db::name('order')->where('id',$id)->find();
//            $ordergx = Db::name('gx_list')->field('*,id as gx_id')->whereIn('lid',$order['gxline_id'])->select();
//            $orderGx = array_column($ordergx,'gx_id'); //订单的全部工序id
//            $startId = Db::name('preproduct_gx')->where("orderid",$id)->where('level',0)->column('gxid');   //订单第一个开始的id
//            $result = $this->calculateGx($startId, $orderGx,0,$startDate);//计算结束时间
//
//            //将订单工序中未绑定在 固定流水里的  放在最后面,已第一个开始的时间计算
//            $lastGx = $this->getNobindGx($orderGx);
//            //将订单工序中 没头没尾的放在最后一个已第一个开始时间计算
//            $resultGx = array_merge(array_column($result,'gx_id'),$lastGx);//已经计算的工序+未绑定的工序
//            $noBeforeAfter = array_diff($orderGx,$resultGx);//没头没尾的工序
//            if($noBeforeAfter){
//                $lastGx = array_merge($lastGx,$noBeforeAfter);
//            }
//            $maxlevel = max(array_column($result,'level'));
//            $maxEnd = max(array_column($result,'endtime'));
//            $lastResult = $this->calculateGx($lastGx, $orderGx,$maxlevel+1,'',[$startDate]);
//
//            $allResult = array_merge($result,$lastResult);
//            $list = $this->decr($allResult);//将结束时间减 1天
//
//            //结果工序可能有重复值,去除重复值，取第一次出现的时间
//            $newlist = [];
//            $tempDistinct = [];
//            foreach ($list as $k => $v) {
//                if(!in_array($v['gx_id'],$tempDistinct)){
//                    $newlist[$v['gx_id']] = $v;
//                }
//                $tempDistinct[] = $v['gx_id'];
//            }
//
//                $preproduct = model('Preproduct')->save(['start_date'=>$startDate,'convert_time'=>$startDate], ['orderid'=>$id]);
//                $preId = Db::name('preproduct')->where('orderid',$id)->find();
//                $oldId = Db::name('preproduct_gx')->where('orderid',$id)->column('gxid');//以前预计划的工序id
//                foreach ($newlist as $k => $v) {
//                    //查询是否已存在
//                    $find = Db::name('preproduct_gx')->where(['orderid'=>$id,'gxid'=>$v['gx_id']])->find();
//                    if($find){
//                        $res = Db::name('preproduct_gx')->where(['orderid'=>$id,'gxid'=>$v['gx_id']])->update(['endtime'=>$v['endtime']]);
//                    }else{
//
//                        $res = Db::name('preproduct_gx')->insert([
//                            'pre_id' => $preId['id'],'orderid' => $id, 'gxid' => $v['gx_id'], 'endtime' => $v['endtime'], 'level' => $v['level']
//                        ]);
//                    }
//
//                }
//                //订单里有删除工序，则删除预排产的工序
//                $newlistId = array_column($newlist,'gx_id');
//                $delgx = array_diff($oldId,$newlistId);
//                if($delgx){
//                    Db::name('preproduct_gx')->where('orderid',$id)->whereIn('gxid',$delgx)->delete();
//                }
//        }
//
//    }

    /**
     * 刷新全部工序时间
     */
    public function refresh()
    {
        $id = input('id');
        $startDate = input('start_date');
        
//        $ordergx = Db::name('preproduct_gx')->field('gxid as gx_id,level')->where("orderid",$id)->order('level')->select();
        $order = Db::name('order')->where('id',$id)->find();
        $ordergx = Db::name('gx_list')->field('*,id as gx_id')->whereIn('lid',$order['gxline_id'])->select();
        $orderGx = array_column($ordergx,'gx_id'); //订单的全部工序id
        $startId = Db::name('preproduct_gx')->where("orderid",$id)->where('level',0)->column('gxid');   //订单第一个开始的id
        $result = $this->calculateGx($startId, $orderGx,0,$startDate);//计算结束时间

        //将订单工序中未绑定在 固定流水里的  放在最后面,已第一个开始的时间计算
        $lastGx = $this->getNobindGx($orderGx);
        //将订单工序中 没头没尾的放在最后一个已第一个开始时间计算
        $resultGx = array_merge(array_column($result,'gx_id'),$lastGx);//已经计算的工序+未绑定的工序
        $noBeforeAfter = array_diff($orderGx,$resultGx);//没头没尾的工序
        if($noBeforeAfter){
            $lastGx = array_merge($lastGx,$noBeforeAfter);
        }
        $maxlevel = max(array_column($result,'level'));
        $maxEnd = max(array_column($result,'endtime'));
        $lastResult = $this->calculateGx($lastGx, $orderGx,$maxlevel+1,'',[$startDate]);

        $allResult = array_merge($result,$lastResult);
        $list = $this->decr($allResult);//将结束时间减 1天

        //结果工序可能有重复值,去除重复值，取第一次出现的时间
        $newlist = [];
        $tempDistinct = [];
        foreach ($list as $k => $v) {
            if(!in_array($v['gx_id'],$tempDistinct)){
                $newlist[$v['gx_id']] = $v;
            }
            $tempDistinct[] = $v['gx_id'];
        }

        //执行更新
        try {
            $preproduct = model('Preproduct')->save(['start_date'=>$startDate,'convert_time'=>$startDate], ['orderid'=>$id]);
            $preId = Db::name('preproduct')->where('orderid',$id)->find();
            $oldId = Db::name('preproduct_gx')->where('orderid',$id)->column('gxid');//以前预计划的工序id
            foreach ($newlist as $k => $v) {
                //查询是否已存在
                $find = Db::name('preproduct_gx')->where(['orderid'=>$id,'gxid'=>$v['gx_id']])->find();
                if($find){
                    $res = Db::name('preproduct_gx')->where(['orderid'=>$id,'gxid'=>$v['gx_id']])->update(['endtime'=>$v['endtime']]);
                }else{

                    $res = Db::name('preproduct_gx')->insert([
                        'pre_id' => $preId['id'],'orderid' => $id, 'gxid' => $v['gx_id'], 'endtime' => $v['endtime'], 'level' => $v['level']
                    ]);
                }

            }
            //订单里有删除工序，则删除预排产的工序
            $newlistId = array_column($newlist,'gx_id');
            $delgx = array_diff($oldId,$newlistId);
            if($delgx){
                Db::name('preproduct_gx')->where('orderid',$id)->whereIn('gxid',$delgx)->delete();
            }
            $this->_success('刷新成功');
        } catch (\Exception $e) {
            $this->_error('操作失败');
        }

        
    }
    
    /**
     * 编辑预生产计划时间
     */
    public function edit()
    {
        $status = input('status',0);
        $where = $this->getEditWhere();
        $list = Db::name('preproduct')->alias('a')->field('a.*,b.ordernum,b.unique_sn,b.uname,b.color,b.pname')
                ->join('order b','a.orderid=b.id')
                ->join('preproduct_gx c','a.id=c.pre_id')
                ->where($where)
                ->where('a.status',$status)
                ->group('a.id')
                ->order('id desc')
                ->paginate('',false,['query'=> input('get.')]);     
        $array = $list->all();
        foreach ($list as $k => $v) {
            $pregxlist = Db::name('preproduct_gx')->alias('a')->field('a.*,b.dname')->join('gx_list b','a.gxid=b.id')
                ->where("a.pre_id",$v['id'])->order('b.orderby asc')
                ->select();
            //查询工序是否已报工，已报工的工序不能编辑
            $gxid = array_column($pregxlist,'gxid');
            $flowCheck = Db::name('flow_check')->where('orderid',$v['orderid'])->whereIn('orstatus',$gxid)->column('orstatus');
            foreach ($pregxlist as $k2 => $v2){
                if(in_array($v2['gxid'],$flowCheck)){
                    $pregxlist[$k2]['edit'] = false;
                }else{
                    $pregxlist[$k2]['edit'] = true;
                }
            }
            $array[$k]['pregxlist'] = $pregxlist;
        }
        $gxlist = Db::name('gx_list')->where('lid','>',0)->select();
        $this->assign('list',$array);
        $this->assign('page',$list->render());
        $this->assign('search', input('get.'));
        $this->assign('gx_list',$gxlist);
        $template = $status == 1?'convert_list':'edit';
        return $this->fetch($template);
    }
    
    public function getEditWhere()
    {
        $startDate = input('search_start_date');
        $endtime = input('endtime');//工序预生产 预完成时间
        $gxid = input('gxid');
        $ordernum = input('ordernum');
        $uniqueSn = input('unique_sn');
        $uname = input('uname');
        $pname = input('pname');
                
        $where = [];
        if($startDate != ''){
            $where[] = ['a.start_date','=',$startDate];
        }
        if($endtime != ''){
            $where[] = ['c.endtime','=',"$endtime"];
        }
        if($gxid != ''){
            $where[] = ['c.gxid','=',$gxid];
        }
        if($ordernum != ''){
            $where[] = ['b.ordernum','=',$ordernum];
        }
        if($uniqueSn != ''){
            $where[] = ['b.unique_sn','=',$uniqueSn];
        }
        if($uname != ''){
            $where[] = ['b.uname','=',$uname];
        }
        if($pname != ''){
            $where[] = ['b.pname','=',$pname];
        }
        return $where;
    }
    
    /**
     * 添加预生产计划
     */
    public function add_preproduct_plan()
    {
        if(request()->ispost()){
            $status = input('status');
            $orderId = input('data/a');
            $startDate = input('start_date');//第一个工序的预开始时间
            $order = Db::name('order')->whereIn('id', $orderId)->select();
            $exist = Db::name('preproduct')->group('orderid')->column('orderid');
            $firstGx = $this->getFirstGx();//固定工序流水后台设置的 可第一个开始的工序
            $gxValue =  @include APP_DATA.'gx_list.php';
            $tips = [];
            try{
                foreach ($order as $k => $v) {
                    //如果是已存在的，则跳过
                    if(in_array($v['id'],$exist)){
                        continue;
                    }

                    $lineIds = explode(',', $v['gxline_id']);
                    $orderGx = $this->getOrdergx($lineIds); //订单的全部工序id
                    $startId = array_unique(array_intersect($firstGx, $orderGx));   //订单第一个开始的id

                    //如果因订单工序原因引起 找不到第一个开始的工序，则使用左边的为第一个开始的工序
                    if(count($startId) == 0){
                        $first = $this->getFirstGx(1);
                        $startId = array_unique(array_intersect($first, $orderGx));
                    }
                    $result = $this->calculateGx($startId, $orderGx,0,$startDate);//计算结束时间

                    if($result) {
                        //将订单工序中未绑定在 固定流水里的  放在最后面已第一个开始时间计算
                        $lastGx = $this->getNobindGx($orderGx);
                        //将订单工序中 没头没尾的放在最后一个已第一个开始时间计算
                        $resultGx = array_merge(array_column($result,'gx_id'),$lastGx);//已经计算的工序+未绑定的工序
                        $noBeforeAfter = array_diff($orderGx,$resultGx);//没头没尾的工序
                        if($noBeforeAfter){
                            $lastGx = array_merge($lastGx,$noBeforeAfter);
                        }

                        $maxlevel = max(array_column($result, 'level'));
                        $maxEnd = max(array_column($result, 'endtime'));
                        $lastResult = $this->calculateGx($lastGx, $orderGx, $maxlevel + 1, '', [$startDate]);

                        $allResult = array_merge($result, $lastResult);
                        $allResult = $this->decr($allResult);//将结束时间减 1天

                        $insertData = ['orderid' => $v['id'], 'start_date' => $startDate, 'addtime' => time()];
                        //如果选的是有效排产
                        if ($status == 2) {
                            $insertData['status'] = 1;
                            $insertData['convert_time'] = $startDate;
                        }
                        $preproductId = Db::name('preproduct')->insertGetId($insertData);
                        $insertGxEnd = [];
                        //结果工序可能有重复值,去除重复值，取第一次出现的时间
                        $tempDistinct = [];
                        foreach ($allResult as $k2 => $v2) {
                            if(!in_array($v2['gx_id'],$tempDistinct)){
                                $insertGxEnd[] = ['pre_id' => $preproductId, 'orderid' => $v['id'], 'gxid' => $v2['gx_id'], 'endtime' => $v2['endtime'], 'level' => $v2['level']];
                            }
                            $tempDistinct[] = $v2['gx_id'];
                        }
                        $res = Db::name('preproduct_gx')->insertAll($insertGxEnd);
                    }else{
                        $tips[] = "$v[unique_sn]";
                    }
                }
                $msg = count($tips)>0?'保存成功,'.implode(',',$tips).'找不到第一个开始工序':'保存成功';
                $this->_success("$msg");
            }catch (\Exception $e){
                $this->_error('保存失败,请重试');
            }

            return;
        }
        return $this->fetch();
    }
    

    
    /**
     * 递归计算订单的整个  工序流程
     * @param array $startId 开始工序Id
     * @param array $orderGx 订单所有工序id
     * @param int $level 级别
     * @param string $startDate 开始计算时间
     * @param array $prevEnd2 上一个工序的结束时间
     * @return array
     */
    public function calculateGx($startId,$orderGx,$level=0,$startDate='',$prevEnd2=[])
    {

        $array = [];
        $child = [];
        $gxValue =  @include APP_DATA.'gx_list.php';
        $prevEnd = $prevEnd2;
        $prevEnd2 = [];
        foreach ($startId as $k => $v) {

            //如果是第一个 开始工序
            if($level == 0){
                $temp['gx_id'] = $v;
                $temp['level'] = $level;
                $endtime = date('Y-m-d', strtotime($startDate) + ($gxValue[$v]['work_value'] * 24 * 3600));
                $temp['endtime'] = $endtime;
                $tempchild = $this->getChild($v, $orderGx);
                $array[] = $temp;
                //如果当前工序有下级，存储预计划时间,作为下级工序的开始时间
                if(count($tempchild) > 0){
                    foreach ($tempchild as $k5 => $v5) {
                        $prevEnd2[] = $endtime;
                    }
                }
            }else{
                //获取同级工序，即并列工序
                $siblings = $this->getSibling($v,$orderGx);
                $calculated = array_column($array,'gx_id');
                //两次遍历会有重复值，如果是已经计算过的，则跳过
                if(in_array($v,$calculated)){
                    continue;
                }

                //如果没有并列工序,则加入当前工序
                if(!$siblings){
                    $siblings[] = $v;
                }
                //计算并列结束时间，最大的时间为下一个开始的时间
                $tempEndtime = [];
                $tempchild = [];
                foreach ($siblings as $k2 => $v2) {
                    if(count($prevEnd) == 1){
                        $prev = $prevEnd[0];
                    }else{
                        $prev = $prevEnd[$k];
                    }
                    $endtime = date('Y-m-d', strtotime($prev) + ($gxValue[$v2]['work_value'] * 24 * 3600));
                    $tempEndtime[] = $endtime;

                    $tempchild = array_merge($tempchild,$this->getChild($v, $orderGx));
                    $array[] = ['gx_id'=>$v2,'level'=>$level,'endtime'=>$endtime];
                }
                $maxEndtime = max($tempEndtime);//并列工序取最大的时间

                //处理prevEnd 即下一个工序的开始时间
                foreach ($tempchild as $k3 => $v3) {
                    $prevEnd2[] = $maxEndtime;
                }
            }

            $child = array_merge($child,$tempchild);

        }
        if(is_array($child) && count($child) > 0 ){
            $level += 1;
            $array = array_merge($array, $this->calculateGx($child, $orderGx, $level,'',$prevEnd2));
        }

        return $array;
    }

    /**
     * 将计算的结束时间，减一天
     * @param $data
     * @return mixed
     */
    public function decr($data)
    {
        foreach ($data as $k => $v) {
            $data[$k]['endtime'] = date('Y-m-d',strtotime($v['endtime'])-10);
        }
        return $data;
    }

    /**
     * 计算预生产完成时间
     * @param $gx
     * @param $startDate
     * @return array
     */
//    public function calculate($result,$startDate)
//    {
//        //计算出来的整体工作流程有重复值，去除重复值，去最后一个
//        $delDistinct = [];
//        foreach ($result as $k => $v) {
//            $delDistinct[$v['gx_id']] = $v;
//        }
//        //按级别 排序
//        $level = [];
//        foreach ($delDistinct as $k => $v) {
//            $level[] = $v['level'];
//        }
//        array_multisort($level,SORT_ASC,$delDistinct);
//
//        $gxValue =  @include APP_DATA.'gx_list.php';
//        //计算完成时间
//        $array = [];
//        foreach ($delDistinct as $k => $v) {
//            $array[$k]['gx_id'] = $v['gx_id'];
//            $array[$k]['level'] = $v['level'];
//            if(isset($endtime)){
//                //如果有并列工序，以最大的计算
//                $maxendtime = isset($temp[$v['level']-1])?max($temp[$v['level']-1]):$endtime;
//                $endtime = date('Y-m-d', strtotime($maxendtime) + ($gxValue[$v['gx_id']]['work_value'] * 24 * 3600));
//            }else {
//                $endtime = date('Y-m-d', strtotime($startDate) + ($gxValue[$v['gx_id']]['work_value'] * 24 * 3600));
//            }
//            $array[$k]['endtime'] = $endtime;
//            $templevel = $v['level'];
//            $temp[$v['level']][] = $endtime;
//        }
//        return $array;
//    }

    /**
     * 获取订单的同级工序
     * @param $gxid 工序id
     * @param $orderGx 订单的所有工序id
     * @return array
     */
    public function getSibling($gxid,$orderGx)
    {
        $fixedGx = @include APP_DATA.'fix_gx_id.php';
        $sibling = [];
        foreach ($fixedGx as $k => $v) {
            if (in_array($gxid, $v['parent'])) {
                $sibling = array_intersect($orderGx, $v['parent']);
            }
        }
        return $sibling;
    }

    /**
     * 获取固定流水 工序的下一个工序
     * @param int $gxid 工序id
     * @param array $orderGx 订单的所有工序
     */
    public function getChild($gxid,$orderGx)
    {
        $fixedGx = @include APP_DATA.'fix_gx_id.php'; 
        $next = [];
        foreach ($fixedGx as $k => $v) {
            if(in_array($gxid, $v['parent'])){                
                $next = array_merge($next, array_intersect($orderGx, $v['child']));  
            }
        }
        return $next;
    }
    
    /**
     * 获取可第一个开始的工序
     */
    public function getFirstGx($display='')
    {
        $pid = Db::name('fixed_gx')->where('pid',0)->column('gx_id');//pid=0即在左边的工序
        //如果因订单工序原因引起 找不到第一个开始的工序，则使用左边的为第一个开始的工序
        if($display == 1){
            return $pid;
        }
        $fixedGx = @include APP_DATA.'fix_gx_id.php';
        foreach ($pid as $k => $v) {
            //删除绑定父级的工序
            $flag = true;
            foreach ($fixedGx as $k2 => $v2) {
                if(in_array($v,$v2['child'])){
                    $flag = false;
                }
            }
//            $res = Db::name('fixed_gx')->where('gx_id',$v)->where('pid','<>',0)->find();
            if($flag == false){
                unset($pid[$k]);
            }
        }

        return $pid;
    }
    
    /**
     * 获取订单 没在固定流水绑定的 工序
     * @param array $ordergx 订单工序Id
     */
    public function getNobindGx($ordergx)
    {
        $fixedGx = @include APP_DATA.'fix_gx_id.php'; 
        $nobind = [];
        foreach ($ordergx as $k => $v) {
            $flag = true;
            foreach ($fixedGx as $k2 => $v2) {
                if(in_array($v, $v2['parent']) || in_array($v, $v2['child'])){
                   $flag = false; 
                   continue;
                }
            }
            if($flag == true){
                $nobind[] = $v;
            }
        }
        return $nobind;
    }
    
    /**
     * 获取订单的所有工序
     * @param array $lineId 工艺线id
     * @return array
     */
    public function getOrdergx($lineId)
    {
        $gxlist = @include APP_DATA.'gx_list.php';        
        $gxArray = [];//工艺线下的工序
        foreach ($gxlist as $k => $v) {
            if(in_array($v['lid'], $lineId)){
                $gxArray[] = $v['id'];
            }
        }
        return $gxArray;
    }
    
    
    /**
     * 筛选订单
     */
    public function filter_order()
    {
        $type = input('type',1);
        $custom = input('custom');
        $produceno = input('unique_sn');
        $dateField = $type == 1?'addtime':'endtime';
        //未报过工的订单
        $query = Order::alias('a')->field('a.*')->join('flow_check b','a.id=b.orderid','left')->where('b.id is null')
            ->whereNotIn('a.id',function($query){
                $query->field('orderid')->name('preproduct');
             });
        if($custom != ''){
           $query->where('a.uname',"$custom"); 
        }
        if($produceno != ''){
            $query->where('a.unique_sn','like',"%$produceno%");
        }
        $data = $query->order('a.unique_sn desc')->select()->toarray();
        $uniqueDate = array_unique(array_column($data, $dateField));//不重复的时间
        $list = [];
        foreach ($uniqueDate as $k => $v) {
            $list[$k]['date'] = $v;
            $uniqueSn = [];
            foreach ($data as $k2 => $v2) {
                if($v == $v2[$dateField]){
                    $uniqueSn[] = ['unique_sn'=>$v2['unique_sn'],'id'=>$v2['id']];
                }
            }
            $list[$k]['data'] = $uniqueSn;
        }
        if(count($list) == 0){
            $this->_error('没有此条件未报过工的订单');
        }
        $this->_success('',$list);
    }

    /**
     * 已添加汇总
     */
    public function collect_list()
    {
        $addtime = input('addtime');
        $ordernum = input('ordernum');
        $uniquesn = input('unique_sn');
        $where = "a.status=1";
        if($addtime != ''){
            $time = explode('~',$addtime);
            $start = $time[0];
            $end =  $time[1];
            $where .= " and a.convert_time>='$start' and a.convert_time<='$end'";
        }
        if($ordernum != ''){
            $where .= " and b.ordernum like '%$ordernum%'";
        }
        if($uniquesn != ''){
            $where .= " and b.unique_sn like '%$uniquesn%'";
        }

        $list = Db::name('preproduct')->field('a.addtime,a.convert_time,count(a.id) as count')->alias('a')
            ->join('order b','a.orderid=b.id')
            ->where($where)
            ->group('a.convert_time')->order('a.id desc')
            ->paginate('',false,['query'=>input('get.')]);
        $array = $list->all();

        foreach ($array as $k => $v) {
            $date = date('Y-m-d H:i:s',$v['addtime']);
            $order = Db::name('preproduct')->alias('a')->field('b.*')->join('order b','a.orderid=b.id')->where('a.convert_time',$v['convert_time'])->select();
            $order = order_attach($order);
            $orderid = array_column($order,'id');
            $area = array_sum(array_column($order,'area'));//面积
            $doornum = array_sum(array_column($order,'doornum'));//玻扇
            $screenwin = array_sum(array_column($order,'screenwin'));//纱扇
            $product = Db::name('flow_check')->whereIn('orderid',$orderid)->group('orderid')->count();//生产中
            $exception = Db::name('flow_check')->whereIn('orderid',$orderid)->where('state',1)->group('orderid')->count();//异常单数
            $allcount = 0;
            foreach ($order as $k2 => $v2) {
                if($v['endstatus'] == 2){
                    $allcount += 1;
                }
            }
            $temp = ['date'=>$date,'area'=>$area,'doornum'=>$doornum,'screenwin'=>$screenwin,'product'=>$product,'exception'=>$exception,'allcount'=>$allcount,
                'count'=>$v['count'],'addtime'=>$v['addtime'],'convert_time'=>$v['convert_time']];
            $array[$k] = $temp;
        }

        $this->assign('page',$list->render());
        $this->assign('list',$array);
        $this->assign('search',input('get.'));
        return $this->fetch();
    }

    /**
     * 导出预生产计划 汇总详细
     */
    public function export_collect()
    {
        $type = input('type');
        //如果是筛选导出
        if($type == 'filter'){
            $addtime = input('addtime');
            $ordernum = input('ordernum');
            $uniquesn = input('unique_sn');
            $where = "a.status=1";
            if($addtime != ''){
                $time = explode('~',$addtime);
                $start = $time[0];
                $end =  $time[1];
                $where .= " and a.convert_time>='$start' and a.convert_time<='$end'";
            }
            if($ordernum != ''){
                $where .= " and b.ordernum='$ordernum'";
            }
            if($uniquesn != ''){
                $where .= " and b.unique_sn='$uniquesn'";
            }

            $sql = Db::name('preproduct')->field('a.addtime,a.convert_time,a.orderid')->alias('a')
                ->join('order b','a.orderid=b.id')
                ->where($where)
                ->order('a.id desc')
                ->buildSql();
            $orders = Db::name('order')->alias('a')->field('a.*,c.convert_time')->join($sql.' c','a.id=c.orderid')->select();
            $orderid = array_column($orders,'id');
        }else {
            $addtime = input('convert_time');//转换成有效排产日期
            $orderid = Db::name('preproduct')->where('convert_time', $addtime)->column('orderid');
            $orders = Db::name('order')->field("*")->whereIn('id',$orderid)->select();
        }

        $orders = order_attach($orders);

        //异常原因
        $exception = Db::name('flow_check')->whereIn('orderid',$orderid)->where('state',1)->select();
        //订单id为键，汇总异常原因
        $exceptionList = [];
        foreach ($exception as $k => $v) {
            $exceptionList[$v['orderid']][] = $v['stext'];
        }
        
        //订单报工数据
        $flowcheck = Db::name('flow_check')->whereIn('orderid',$orderid)->select();
        //整理数据
        $flowcheckList = [];
        foreach ($flowcheck as $k => $v) {
            $flowcheckList[$v['orderid']][$v['orstatus']] = ['starttime'=>$v['starttime'],'endtime'=>$v['endtime']];
        }
        //订单工序预生产时间
        $preflow = Db::name('preproduct_gx')->whereIn('orderid',$orderid)->select();
        $preflowList = [];
        foreach ($preflow as $k => $v) {
            $preflowList[$v['orderid']][$v['gxid']] = $v['endtime'];
        }

        //头部工序名称
        $allgxid = Db::name('preproduct_gx')->alias('a')->field('a.*,b.dname')->join('gx_list b','a.gxid=b.id')->whereIn('orderid',$orderid)->group('gxid')->column('gxid');
        //按工序orderby 进行排序
        $gxlist = Db::name('gx_list')->whereIn('id',$allgxid)->order('orderby asc')->select();
        $gxname = array_column($gxlist,'dname');
        $gxid = array_column($gxlist,'id');
        $headArr = ['排产日期','销售单号','生产单号','面积','物料名称','客户名称','颜色','玻扇数','纱扇数','异常原因','超时原因','整单入库时间'];
        $headArr = array_merge($headArr,$gxname);

        //数据
        $list = [];
        $gxfield = [];
        foreach ($orders as $k => $v) {
            $excep = isset($exceptionList[$v['id']])?implode(',',$exceptionList[$v['id']]):'';
            $intime = $v['intime']!=0?date('Y-m-d H:i:s',$v['intime']):'';
            $convertTime = $type == 'filter'?$v['convert_time']:$addtime;
            $orderData = ['addtime'=>$convertTime,'ordernum'=>$v['ordernum'],'unique_sn'=>$v['unique_sn'],'area'=>$v['area'],'pname'=>$v['pname'],
                    'uname'=>$v['uname'],'color'=>$v['color'],
                    'doornum'=>$v['doornum'],'screenwin'=>$v['screenwin'],'exception'=>$excep,'over'=>$v['cause'],'intime'=>$intime
                ];
            $list[$k] = $orderData;
            //订单数据里 加入报工的开始时间和完成时间
            foreach ($gxid as $k2 => $v2) {
                $gxstart = isset($flowcheckList[$v['id']][$v2])?$flowcheckList[$v['id']][$v2]['satrttime']:'';
                $pre = isset($preflowList[$v['id']][$v2])?$preflowList[$v['id']][$v2]:'';
                $gxend =  isset($flowcheckList[$v['id']][$v2])?$flowcheckList[$v['id']][$v2]['endtime']:'';
                $list[$k][$v2.'start'] = $gxstart!=''&&$gxstart>0?date('Y-m-d H:i:s',$gxstart):'';
                $list[$k][$v2.'pre'] = $pre!=''?$pre:'';
                $list[$k][$v2.'end'] = $gxend!=''&&$gxend>0?date('Y-m-d H:i:s',$gxend):'';
                //表头字段只需存储一次
                if($k == 0) {
                    $gxfield[] = $v2 . 'start';
                    $gxfield[] = $v2 . 'pre';
                    $gxfield[] = $v2 . 'end';
                }
            }

        }
        $field = ['addtime','ordernum','unique_sn','area','pname','uname','color','doornum','screenwin','exception','over','intime'];
        $field = array_merge($field,$gxfield);
        $this->export_c('已排产汇总列表',$headArr,$list,$field);
    }

    /**
     * excel表格导出
     * @param string $fileName 文件名称
     * @param array $headArr 表头名称
     * @param array $data 要导出的数据
     * @param array $field 数据字段数组
     * @param string $time 第一行要显示的文字
     */
    public function export_c($fileName = '', $headArr = [], $data = [],$field=[],$time='')
    {
        $fileName .= "_" . date("Y_m_d", time());
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties();

        $key = 0;
        foreach ($headArr as $k=>$v) {
            //将列数字转换为字母
            $colum = \PHPExcel_Cell::stringFromColumnIndex($key);

            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //前12列合并行
            if($key <=11) {
                $objPHPExcel->getActiveSheet()->mergeCells("{$colum}1:{$colum}2"); // 合并行
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . 1, $v)->getColumnDimension($colum)->setWidth(20);
                $key += 1;
            }else{
                //合并列
                $next = \PHPExcel_Cell::stringFromColumnIndex($key+2);
                $objPHPExcel->getActiveSheet()->mergeCells("{$colum}1:{$next}1");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . 1, $v)->getColumnDimension($colum)->setWidth(60);
                $key += 3;
            }
        }
        //写入标题 开始日期，预计结束日期等 表头文字
        $tempText = ['开始日期','预计完成日期','实际完成日期'];
        $o = 0;
        for ($i=0;$i<$key;$i++) {
            $colum = \PHPExcel_Cell::stringFromColumnIndex($i);
            if($i > 11){
                $write = $tempText[$o];
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . 2, $write)->getColumnDimension($colum)->setWidth(20);
                if($o >= 2){
                    $o = 0;
                }else{
                    $o++;
                }
            }
        }


        $line = 3; //从第几行开始写入数据
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { //行写入
            $span = 0;
            foreach ($rows as $keyName => $value) {// 列写入
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objPHPExcel->getActiveSheet()->getStyle($j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objActSheet->setCellValue($j . $line, $rows[$field[$span]]);

                $span++;
            }
            $line++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName. '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 预生产排产
     */
    public function schedule()
    {
        return $this->fetch('gxindex');
    }

    //按工序分组显示
    public function ajax_listgx()
    {
        $gx = Db::name('gx_list')->whereIn('id',function ($query){
            $query->name('preproduct_gx')->group('gxid')->field('gxid');
        })->order('orderby asc')->select();

        //根据工序名称组合工序ID
        //$list 结构:array('dname工序名'=>array('id1','id2'..))
        $gxlist=array();
        foreach($gx as $key=>$value){
            $gxlist[$value['dname']][]=$value['id'];
        }

        //搜索
        $where.="a.pause!=1 and a.repeal!='1' ";

        $fix_gx_id=@include APP_DATA.'fix_gx_id.php';
        $needCheckFix=false;
        if($fix_gx_id&&count($fix_gx_id)>0){
            $needCheckFix=true;//有设置固定工序就需要检测工序下的订单前工序是否已完成
        }

        $allorderId = Db::name('preproduct')->column('orderid');//添加到预生产计划的订单id
        //查询下面的所有订单
        $list=array();
        foreach($gxlist as $dname=>$gxids){
            $list[$dname]['num']=0;
            $list[$dname]['area']=0;
            $list[$dname]['snum']=0;

            $line_id=Db::name('gx_list')->whereIn("id",$gxids)->column("lid");
            $lineIdstr = implode('|',$line_id);
            $sql = " and CONCAT (',',gxline_id,',') REGEXP ',($lineIdstr),'";
            $orderList=M("order")
                ->alias("a")
                ->field("a.id,a.gid,a.ordertime,a.unique_sn,a.gx_schedule,a.endtime,a.isurgent,a.addtime,a.gxline_id")
                ->where($where.$sql)->whereIn('id',$allorderId)->order("a.isurgent desc,a.addtime asc,a.unique_sn desc")
                ->select();

            if($orderList){

                $orderId = array_column($orderList,'id');
                //查找所有订单的已完成的报工记录，已完成的就不显示未排产工序
                $flowCheck=M("flow_check")
                    ->field("id,orderid,orstatus,endtime")
                    ->whereIn("orderid",$orderId)->where("orstatus>0 and (endtime>0 or starttime>0)")
                    ->select();
                $checkList=$checkListGxId=array();
                foreach($flowCheck as $value){
                    $oid=$value['orderid'];
                    $checkList[$oid][]=$value;
                    if($value['endtime']>0){
                        //存起订单所有完成报工的工序id
                        $checkListGxId[$oid][]=$value['orstatus'];
                    }
                }


                foreach($orderList as $k=>$value){

                    $gx_schedule=$value['gx_schedule'];
                    if($gx_schedule!=''){
                        $gx_schedule=unserialize($gx_schedule);
                        if(in_array($dname, $gx_schedule)){
                            //删去该工序名称已在订单内排产的订单记录
                            unset($orderList[$k]);
                        }
                    }

                    $orderid=$value['id'];
                    if(isset($checkList[$orderid])){
                        foreach($checkList[$orderid] as $li){
                            //如果该订单的该工序已报工，则删除该订单
                            if(in_array($li['orstatus'], $gxids)){
                                unset($orderList[$k]);
                            }
                        }
                    }

                    //该工序的前工序是否已经完成，未完成则不显示
                    if($needCheckFix){
                        $order_gxs = combine_gx_line(explode(',',$value['gxline_id']));
                        if(!$order_gxs){
                            unset($orderList[$k]);
                            continue;
                        }
                        //获取当前工序名的id
                        $current_gx_id=0;
                        //当前订单所有工序的id
                        $gxs_id=array();
                        foreach($order_gxs as $val){
                            $gx_id=$val['id'];
                            $gxs_id[]=$gx_id;
                            //在当前工序名的工序id数组内
                            if(in_array($gx_id, $gxids)){
                                $current_gx_id=$gx_id;
                            }
                        }

                        if($current_gx_id==0){
                            unset($orderList[$k]);
                            continue;
                        }

                        //查找这个工序是否有设置工作流和找到父工序
                        $parent=fixed_parent($fix_gx_id,$current_gx_id);
                        if(!$parent['isSetting']||($parent['isSetting']&&count($parent['parent'])<=0)){
                            //不需要检测
                            continue;
                        }

                        $before_gx=$parent['parent'];

                        //查询前工序是否在当前订单的工序里面
                        foreach($before_gx as $bk=>$gx_id){
                            if(!in_array($gx_id, $gxs_id)){
                                unset($before_gx[$bk]);
                            }
                        }

                        if(count($before_gx)<=0){
                            continue;//没上级工序可以显示
                        }else{

                            if(isset($checkListGxId[$orderid])){
                                $finish_parent=0;
                                foreach($checkListGxId[$orderid] as $gx_id){
                                    if(in_array($gx_id, $before_gx)){
                                        $finish_parent++;
                                    }
                                }
                                if($finish_parent!=count($before_gx)){
                                    unset($orderList[$k]);
                                    continue;
                                }
                            }

                        }

                    }

                }//end of foreach
                $now=time();
                $daySecond=24*60*60;
                $orderId=array();
                foreach($orderList as $k=>$value){
                    if($value['endtime']>0){
                        $value['day']=floor(($value['endtime']-$now)/$daySecond);
                    }else{
                        $value['day']=0;
                    }
                    $orderId[]=$value['id'];
                    $orderList[$k]=$value;
                }

                //按时间分组
                $groupby=input("groupby");
                if(!empty($groupby)){
                    $times = Db::name('preproduct_gx')->whereIn('orderid',$orderId)->whereIn('gxid',$gxids)->select();
                    //遍历$orderList 将预生产日期作为键
                    foreach($orderList as $k=>$value){
                        $id=$value['id'];
                        foreach($times as $val){
                            if($id==$val['orderid']){
                                $orderList[$k]['endtime']=strtotime($val['endtime']);
                                break;
                            }
                        }
                    }

                    //对订单进行分组
                    $newList=array();
                    foreach($orderList as $value){
                        $time=date('Y-m-d',$value['endtime']);
                        $newList[$time][]=$value;
                    }
                    $orderList=$newList;
                }

                //统计面积和扇数
                if(count($orderId)>0){
                    $area=M("order")->where("id in (".implode(",",$orderId).")")->sum("area");
                    $snum=M("order")->where("id in (".implode(",",$orderId).")")->sum("snum");
                    $list[$dname]['num']=count($orderList);
                    $list[$dname]['area']=round($area,2);
                    $list[$dname]['snum']=round($snum,2);
                    $list[$dname]['orders']=$orderList;
                }else{
                    $list[$dname]['orders']=array();
                }
            }else{
                $list[$dname]['orders']=array();
            }
        }



        return array('status'=>'1','list'=>$list);

    }
    
    /**
     * 计算排产值
     * 传值：orderid
     */
    public function calculate_schedule(){
        $orderId = input("order/a");
        $startDate = input("time/s");
        $order = Db::name('order')->whereIn('id', $orderId)->select();
        $exist = Db::name('preproduct')->group('orderid')->column('orderid');
        $gx_list = Db::name('gx_list')->where("isdel=0")->order("orderby asc")->select();
        $firstGx = $this->getFirstGx();//固定工序流水后台设置的 可第一个开始的工序
        $gxValue =  @include APP_DATA.'gx_list.php';
        $unit = @include APP_DATA.'ab_unit.php';
        $list = array();
        $title = array('count'=>0,'area'=>0,'bs_num'=>0,'ss_num'=>0);
        $new_gx_list = [];
        $new_unit = [];
        //整理工序数据
        foreach ($gx_list as $gl){
            $new_gx_list[$gl['id']]=$gl;
        }
        foreach ($unit as $tl){
            $new_unit[$tl['id']] = $tl;
        }
        try{
            foreach ($order as $k => $v) {
                //如果是已存在的，则跳过
                if(in_array($v['id'],$exist)){
                    continue;
                }
                $i++;
                //汇总可计算的订单
                $order_data = order_attach($v['id']);
                $title['area'] += floatval($order_data['area']);
                $title['bs_num'] += floatval($order_data['doornum']);
                $title['ss_num'] += floatval($order_data['screenwin']);
                $title['count']++;
                $lineIds = explode(',', $v['gxline_id']);
                $orderGx = $this->getOrdergx($lineIds); //订单的全部工序id
                $startId = array_unique(array_intersect($firstGx, $orderGx));   //订单第一个开始的id
        
                //如果因订单工序原因引起 找不到第一个开始的工序，则使用左边的为第一个开始的工序
                if(count($startId) == 0){
                    $first = $this->getFirstGx(1);
                    $startId = array_unique(array_intersect($first, $orderGx));
                }
                $result = $this->calculateGx($startId, $orderGx,0,$startDate);//计算结束时间
        
                if($result) {
                    //将订单工序中未绑定在 固定流水里的  放在最后面已第一个开始时间计算
                    $lastGx = $this->getNobindGx($orderGx);
                    //将订单工序中 没头没尾的放在最后一个已第一个开始时间计算
                    $resultGx = array_merge(array_column($result,'gx_id'),$lastGx);//已经计算的工序+未绑定的工序
                    $noBeforeAfter = array_diff($orderGx,$resultGx);//没头没尾的工序
                    if($noBeforeAfter){
                        $lastGx = array_merge($lastGx,$noBeforeAfter);
                    }
        
                    $maxlevel = max(array_column($result, 'level'));
                    $maxEnd = max(array_column($result, 'endtime'));
                    $lastResult = $this->calculateGx($lastGx, $orderGx, $maxlevel + 1, '', [$startDate]);
        
                    $allResult = array_merge($result, $lastResult);
                    $allResult = $this->decr($allResult);//将结束时间减 1天
//                     $insertData = ['orderid' => $v['id'], 'start_date' => $startDate, 'addtime' => time()];
//                     $preproductId = Db::name('preproduct')->insertGetId($insertData);
                    $insertGxEnd = [];
                    //结果工序可能有重复值,去除重复值，取第一次出现的时间
                    $tempDistinct = [];
                    foreach ($allResult as $k2 => $v2) {
                        if(!in_array($v2['gx_id'],$tempDistinct)){
//                             $insertGxEnd[] = ['pre_id' => $preproductId, 'orderid' => $v['id'], 'gxid' => $v2['gx_id'], 'endtime' => $v2['endtime'], 'level' => $v2['level']];
                            //查找当前工序、当前订单的已预排产数据
                            if (!isset($list[$v2['gx_id']])&&!isset($list[$v2['gx_id']]['already'])&&!isset($list[$v2['gx_id']]['val'])){
                                $list[$v2['gx_id']] = array();
                            }
                            $endtime = $v2['endtime'];
                            $gxid = $v2['gx_id'];
                            $index = $k2-1;
                            $schedule_pre = Db::name("preproduct_gx")->where("endtime=$endtime and gxid=$gxid")->count();//以往预排产工序
                            $k2==0?$list[$v2['gx_id']]['starttime'] = $startDate:$list[$v2['gx_id']]['starttime'] = date("Y-m-d",strtotime($allResult[$index]['endtime'])-($new_gx_list[$gxid]['work_value']*24*3600));
                            $list[$v2['gx_id']]['name'] = $new_gx_list[$gxid]['dname'];
                            $list[$v2['gx_id']]['unit'] = $new_gx_list[$gxid]['worktime'].'('.$new_unit[$new_gx_list[$gxid]['work_unit']]['label'].')';
                            $list[$v2['gx_id']]['already'] += $new_gx_list[$gxid]['worktime']*floatval($order_data[$new_unit[$new_gx_list[$gxid]['work_unit']]['field']])*$schedule_pre;
                            $list[$v2['gx_id']]['val'] += $new_gx_list[$gxid]['worktime']*floatval($order_data[$new_unit[$new_gx_list[$gxid]['work_unit']]['field']]);
                        }
                        $tempDistinct[] = $v2['gx_id'];
                    }
                }else{
                    $tips[] = "$v[unique_sn]";
                }
            }
            return ['code'=>0,'msg'=>'计算成功','list'=>$list,'title'=>$title];
        }catch (\Exception $e){
            return ['code'=>1,'msg'=>'计算失败'];
        }
    }
    
    /**
     * 计算排产值
     */
    public function calculate(){
        $pre_schedule = input("order/s");
        if (empty($pre_schedule)){
            exit();
        }
        $pre_schedule = explode(",",$pre_schedule);
        $preproduct_order = Db::name('preproduct')->whereIn('id',$pre_schedule)->select();
        $gx_list = Db::name('gx_list')->where("isdel=0")->order("orderby asc")->select();
        $gxValue =  @include APP_DATA.'gx_list.php';
        $unit = @include APP_DATA.'ab_unit.php';
        $list = array();
        $title = array('count'=>0,'area'=>0,'bs_num'=>0,'ss_num'=>0);
        $new_gx_list = [];
        $new_unit = [];
        //整理工序数据
        foreach ($gx_list as $gl){
            $new_gx_list[$gl['id']]=$gl;
        }
        foreach ($unit as $tl){
            $new_unit[$tl['id']] = $tl;
        }
        try{
            foreach ($preproduct_order as $k => $v) {
                $pre_id = $v['id'];
                //汇总可计算的订单
                $order_data = order_attach($v['orderid']);
                $title['area'] += floatval($order_data['area']);
                $title['bs_num'] += floatval($order_data['doornum']);
                $title['ss_num'] += floatval($order_data['screenwin']);
                $title['count']++;
                
                //计算排产值
                $preproduct_list = Db::name('preproduct_gx')->where('pre_id',$pre_id)->select();
                foreach ($preproduct_list as $kc=>$val){
                    $data = array();
                    $orderid = $val['orderid'];
                    $gxid = $val['gxid'];
                    //预生产时间
                    $endtime = strtotime($val['endtime']);
                    $cn_endtime = $val['endtime'];
                    if (!isset($list[$val['gxid'].$endtime])){
                        $list[$val['gxid'].$endtime] = array();
                    }
                    
                    $starttime = date("Y-m-d",$endtime-(24*3600*$new_gx_list[$val['gxid']]['work_value']));
                    $list[$val['gxid'].$endtime]['starttime'] = $starttime;
                    $list[$val['gxid'].$endtime]['name'] = $new_gx_list[$val['gxid']]['dname'];
                    $list[$val['gxid'].$endtime]['unit'] = $new_gx_list[$val['gxid']]['worktime']."(".$new_unit[$new_gx_list[$val['gxid']]['work_unit']]['label'].")";
    //                 //其他订单预排产
                    
    //                 //该工序排产值
                    $list[$val['gxid'].$endtime]['val'] += $new_gx_list[$val['gxid']]['worktime']*$order_data[$new_unit[$new_gx_list[$val['gxid']]['work_unit']]['field']];
                    $count = Db::name("preproduct_gx")->where("pre_id<>$pre_id and gxid=$gxid and endtime='$cn_endtime'")->count();
                    $list[$val['gxid'].$endtime]['other_val'] = $count*$new_gx_list[$val['gxid']]['worktime']*$order_data[$new_unit[$new_gx_list[$val['gxid']]['work_unit']]['field']];
                }
            }
            /* return ['code'=>0,'msg'=>'计算成功','list'=>$list,'title'=>$title]; */
            $this->assign('list',$list);
            $this->assign('title',$title);
        }catch (\Exception $e){
            $this->assign('list',array());
        }
        return $this->fetch();
    }
    /**
     * 转化成有效排产
     */
    public function convert()
    {
        $id = input('id/a');
        $convertTime = input('time/a');

        try{
            foreach ($id as $k => $v) {
                Db::name('preproduct')->where('id',$v)->update(['status'=>1,'convert_time'=>$convertTime[$k]]);
            }
            $this->_success('操作成功');
        }catch (\Exception $e){
            $this->_error('操作失败');
        }

    }

    /**
     * 删除有效排产
     */
    public function del_convert()
    {
        $id = input('id/d');
        $res = Db::name('preproduct')->where('id',$id)->update(['status'=>0,'convert_time'=>'0000-00-00']);
        if($res !== false){
            $this->_success('操作成功');
        }
        $this->_error('操作失败');
    }

    /**
     * 删除生成的预计划
     */
    public function del_plan()
    {
        $id = input('id/d');
        $res = Db::name('preproduct')->where('id',$id)->delete();
        if($res !== false){
            Db::name('preproduct_gx')->where('pre_id',$id)->delete();
            $this->_success('操作成功');
        }
        $this->_error('操作失败');
    }

}
