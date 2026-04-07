<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;

class Databoard extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function getlist()
    {
        $time = date('Y-m-d',time());
        //列表数据
        $list = Db::name('order_send')->alias('a')
            ->field('c.*,a.is_send,a.logistics_name,a.logistics_numbers,a.driver_name,a.driver_phone')
            ->join('order_send_detail b','a.id=b.sid')
            ->join('order c','b.order_id=c.id')
            ->where('a.send_date',$time)
            ->order('a.id asc,c.sort asc,c.id asc')
            ->select();
        $finishedCount = 0;
        $finishedArea = 0;
        $map = ['0'=>'自送','1'=>'物流','2'=>'自提','3'=>'请车','4'=>'快递'];
        //处理数据
        foreach ($list as $k => $v) {
            $list[$k]['send_type'] = $map[$v['is_send']];
            if(in_array($v['is_send'],[0,3])){
                $sendName = $v['driver_name'];
            }elseif(in_array($v['is_send'],[1,4])){
                $sendName = $v['logistics_name'];
            }else{
                $sendName = '';
            }
            $list[$k]['send_name'] = $sendName;
            $list[$k]['is_finished'] = $v['sign_time']!=0?'已送达'.date('H:i',$v['sign_time']):'未送达';
            $list[$k]['back'] = $v['sign_time']!=0?'finished':'';
            if($v['sign_time'] != 0){
                $finishedCount += 1;
                $finishedArea += $v['area'];
            }
        }

        $total = count($list);//总条数
        $pagesize = input('pagesize/d',10);//每页数量
        $maxPage = ceil($total/$pagesize);//总共几页
        $page = input('page/d',1);
        if($page>$maxPage){
            $page = 1;
        }
        $offset = ($page-1)*$pagesize;
        $pagelist = array_slice($list, $offset, $pagesize);//数组分页

        //本日统计数据
        $todayCount = count($list);
        $todayArea = array_sum(array_column($list,'area'));
        $data = [
            'list'=>$pagelist,'page'=>$page,'today_count'=>$todayCount,'today_area'=>round($todayArea,2),
            'today_finished'=>$finishedCount,'finished_area'=>round($finishedArea,2)
        ];

        $header = $this->total();
        $data = array_merge($data,$header);
        return $data;
    }

    /**
     * 获取头部数据
     */
    public function total()
    {
        //上月配送数据
        $lastMonth = timezone_get(9);
        $lastMonthData = Db::name('order_send')->alias('a')->field('c.*')
            ->join('order_send_detail b','a.id=b.sid')
            ->join('order c','b.order_id=c.id')
            ->where("a.send_date between '".date('Y-m-d',$lastMonth['begin'])."' and '".date('Y-m-d',$lastMonth['end'])."'")
            ->select();
        $lastMonthCount = count($lastMonthData);//上月配送数量
        $lastMonthArea = array_sum(array_column($lastMonthData,'area'));//上月总面积
        //上月完成数据
        $lastFinshied = Db::name('order')->field('coalesce(sum(area),0) as area,count(id) as count')
            ->where("sign_time between {$lastMonth['begin']} and {$lastMonth['end']}")->find();

        //本月配送量和面积
        $month = timezone_get(3);
        $monthData = Db::name('order_send')->alias('a')->field('c.*')
            ->join('order_send_detail b','a.id=b.sid')
            ->join('order c','b.order_id=c.id')
            ->where("a.send_date between '".date('Y-m-d',$month['begin'])."' and '".date('Y-m-d',$month['end'])."'")
            ->select();
        $monthCount = count($monthData);//本月配送数量
        $monthArea = array_sum(array_column($monthData,'area'));//本月总面积
        //本月完成数据
        $finshied = Db::name('order')->field('coalesce(sum(area),0) as area,count(id) as count')
            ->where("sign_time between {$month['begin']} and {$month['end']}")->where('is_send=1')->find();

        //本年配送
        $year = timezone_get(6);
        $yearData = Db::name('order_send')->alias('a')
            ->field('coalesce(sum(area),0) as area,count(c.id) as count')
            ->join('order_send_detail b','a.id=b.sid')
            ->join('order c','b.order_id=c.id')
            ->where("a.send_date between '".date('Y-m-d',$year['begin'])."' and '".date('Y-m-d',$year['end'])."'")
            ->find();
        //本年完成
        $yearFinshed = Db::name('order')->field('coalesce(sum(area),0) as area,count(id) as count')
            ->where("sign_time between {$year['begin']} and {$year['end']} and is_send=1")
            ->find();

        return [
            'last_month_count'=>$lastMonthCount,'last_month_area'=>round($lastMonthArea,2),'last_finished'=>$lastFinshied['count'],
            'last_finished_area'=>round($lastFinshied['area'],2),'month_count'=>$monthCount,'month_area'=>round($monthArea,2),
            'month_finished'=>$finshied['count'],
            'month_finished_area'=>round($finshied['area'],2),'year_count'=>$yearData['count'],'year_area'=>round($yearData['area'],2),
            'year_finished'=>$yearFinshed['count'], 'year_finished_area'=>round($yearFinshed['area'],2)
        ];
    }

}