<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Vitual extends Controller
{
    public function index()
    {   
        $monthTime = timezone_get(3);      
        $day = floor((time()-$monthTime['begin'])/(24*3600));//本月已过天数
        if(request()->ispost()){      
                       
            $todayReceive = input('today_receive');
            $todayArea = input('today_area');
            $monthReceive = input('month_receive');
            $monthArea = input('month_area');
                      
            $torder = $todayReceive+rand(1, 3);//当天接单数
            $tarea = round($todayArea+(2 + mt_rand()/mt_getrandmax()),2);//当天接单面积
            $morder = $monthReceive+($torder-$todayReceive);//本月接单数
            $marea = $monthArea+($tarea-$todayArea);//本月接单面积
            
            return ['torder'=>round($torder,2),'tarea'=> round($tarea,2),'morder'=>round($morder,2),'marea'=>round($marea,2)];
        } 
        $month = rand(127, 200)*$day;
        $monthArea = rand(254, 400)*$day;
        $this->assign('month',$month);
        $this->assign('month_area',$monthArea);
        return $this->fetch();
    }
}
