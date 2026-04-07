<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Facade\Env;
use think\facade\Request;

class Salary extends Super{

    public function initialize(){
        parent::initialize();
        if(PRO_SALARY!=1){
            exit("当前项目未开启排产功能");
        }
        //员工列表
        $staff_list = Db::name('login')->where('del<1')->select();
        $this->assign('man',$staff_list);

        //工序列表
        $gx_list = @include APP_DATA.'gx_list.php';
        if (FIX_GX==0){
            $into_gx_list = Db::name('into_gx')->order('id asc')->select();
            $gx_list=array_merge($gx_list,$into_gx_list);
        }
        $this->assign('gxlist',$gx_list);
    }

    //计件管理
    public function salary(){
        //筛选条件
        $uname = ctrim(input('uname'));
        $entry = input('entry/d');
        $produce_no = ctrim(input('pdn'));
        $ordernum = ctrim(input('salenum'));
        $date = ctrim(input('date'));
        $dates = ctrim(input('dates'));
        $where = '';
        $wheres = '';
        $condition = '';
        if (!empty($uname)){
            $where = " and a.uname like '%$uname%'";
        }
        if (!empty($produce_no)){
            $condition = " and b.unique_sn like '%$produce_no%'";
        }
        if (!empty($ordernum)){
            $condition .= " and b.ordernum like '%$ordernum%'";
        }
        if (!empty($entry)){
            $condition .= " and b.status=$entry";
        }

        if (!empty($date)){
            $arr_date = explode('-',$date,4);
            $s_date = strtotime($arr_date[0].'-'.$arr_date[1].'-'.$arr_date[2].'00:00:00');
            $e_date = strtotime($arr_date[3].'23:59:59');
            $wheres = $condition."  and a.into_time between $s_date and $e_date";
            $condition .= " and a.endtime between $s_date and $e_date";
        }
        if (!empty($dates)){
            $arr_dates = explode('-',$dates,4);
            $s_dates = strtotime($arr_dates[0].'-'.$arr_dates[1].'-'.$arr_dates[2].' 00:00:00');
            $e_dates = strtotime($arr_dates[3].' 23:59:59');
            $condition .= " and b.intime between $s_dates and $e_dates";
            $wheres .= " and b.intime between $s_dates and $e_dates";
        }
        //flow_check完成的数据
        $result = Db::name('login')->alias('a')->field('a.id,a.uname,b.orstatus,b.uid,c.dname,b.sid')
            ->join('flow_check b','a.id=b.uid','LEFT')
            ->join('gx_list c','c.id=b.orstatus','LEFT')
            ->where("a.del<>1 and b.endtime<>0 and b.salary!='' and b.ispay>1 $where")
            ->group('b.uid,b.orstatus,b.sid')->select();
        $res=$result;
        $total_salary = 0;
        if (!empty($res)){
            foreach ($res as $key=>$vl){
                $fid = $vl['orstatus'];
                $uid = $vl['uid'];
                $uname = $vl['uname'];
                $sid = $vl['sid'];
                //所属审核订单
                $obj = Db::name('flow_check')->alias('a')->field('a.*,b.unique_sn,c.price,c.unitname')
                ->join('order b','a.orderid=b.id','LEFT')
                ->join('formula c','a.sid=c.id','LEFT')
                ->where("a.orstatus=$fid and a.uid=$uid and a.sid=$sid and a.salary!='' or a.man='$uname' $condition")->select();
            
                //             该员工该工序总提成
                for ($i=0;$i<count($obj);$i++){
                    if (!empty($obj[$i]['salary'])){
                        $res[$key]['total'] += $obj[$i]['salary'];
                        $res[$key]['jnum'] += $obj[$i]['num'];
                        $res[$key]['ordersn'] .= $obj[$i]['unique_sn'].' | ';
                        $res[$key]['price']?$res[$key]['price']:$res[$key]['price']=$obj[$i]['price'];
                        $res[$key]['unitname']?$res[$key]['unitname']:$res[$key]['unitname']=$obj[$i]['unitname'];
                        $total_salary += $obj[$i]['salary'];
                    }
                }
            }
        }
        
        //动态入库提成
        if(FIX_GX==0){
            $rest = Db::name('login')->alias('a')->field('a.id,a.uname,b.name as dname,b.uid,b.sid')
            ->join('into_order_gx b','a.id=b.uid','LEFT')
            ->where("a.del<>1 and b.into_time<>0 and b.salary!='' and b.ispay>1 $where")
            ->group('b.uid,b.name,b.sid')->select();
            if (!empty($rest)){
                foreach ($rest as $key=>$vl){
                    $fname = $vl['dname'];
                    $uid = $vl['uid'];
                    $uname = $vl['uname'];
                    $sid = $vl['sid'];
                    //所属审核订单
                    $obj = Db::name('into_order_gx')->alias('a')->field('a.*,b.unique_sn,c.price,c.unitname')
                    ->join('order b','a.orderid=b.id','LEFT')
                    ->join('formula c','a.sid=c.id','LEFT')
                    ->where("a.name='$fname' and a.uid=$uid and a.sid=$sid and a.salary!='' $wheres")->select();
                
                    //             该员工该工序总提成
                    for ($i=0;$i<count($obj);$i++){
                        if (!empty($obj[$i]['salary'])){
                            $rest[$key]['total'] += $obj[$i]['salary'];
                            $rest[$key]['jnum'] += $obj[$i]['num'];
                            $rest[$key]['ordersn'] .= $obj[$i]['unique_sn'].' | ';
                            $rest[$key]['price']?$rest[$key]['price']:$rest[$key]['price']=$obj[$i]['price'];
                            $rest[$key]['unitname']?$rest[$key]['unitname']:$rest[$key]['unitname']=$obj[$i]['unitname'];
                            $total_salary += $obj[$i]['salary'];
                        }
                    }
                }
                $res = array_merge($res,$rest);
            }
        }
        //总提成
        //订单表、订单数
        $this->assign('get',input(""));
        $this->assign('list',$res);
        $this->assign('totalprice',$total_salary);
        return $this->fetch();
    }
    //员工提成详情
    public function salarydetail(){
        $uid = intval(input('uid'));
        $gxid = intval(input('did'));
        $sid = intval(input('sid'));
        $gxname = input('gxname');
        $uname = input('uname');

        //查询条件
        $entry = input('entry/d');
        $produce_no = ctrim(input('pdn'));
        $ordernum = ctrim(input('salenum'));
        $date = ctrim(input('date'));
        $dates = ctrim(input('dates'));
        $where = '';

        if (!empty($produce_no)){
            $where = " and b.unique_sn like '%$produce_no%'";
        }
        if (!empty($ordernum)){
            $where .= " and b.ordernum like '%$ordernum%'";
        }
        if (!empty($entry)){
            $where .= " and b.status=$entry";
        }

        if (!empty($date)){
            $arr_date = explode('-',$date,4);
            $s_date = strtotime($arr_date[0].'-'.$arr_date[1].'-'.$arr_date[2].'00:00:00');
            $e_date = strtotime($arr_date[3].'23:59:59');
            $gxid?$where .= " and a.endtime between $s_date and $e_date":$where .= " and a.into_time between $s_date and $e_date";
        }
        if (!empty($dates)){
            $arr_dates = explode('-',$dates,4);
            $s_dates = strtotime($arr_dates[0].'-'.$arr_dates[1].'-'.$arr_dates[2].' 00:00:00');
            $e_dates = strtotime($arr_dates[3].' 23:59:59');
            $where .= " and b.intime between $s_dates and $e_dates";
        }
        //所有字段列
        $allfield=@include APP_DATA.'qrfield.php';
        array_unshift($allfield,array('explains'=>'报工时间','fieldname'=>'into_time'),array('explains'=>'入库时间','fieldname'=>'intime'));
        $this->assign("fieldList",$allfield);
        
        if (!empty($gxid)){
            //订单数据
            $order = Db::name('flow_check')->alias('a')->field('a.endtime,b.intime,b.id,a.salary')
            ->join('order b','a.orderid=b.id','LEFT')
            ->where("a.orstatus=$gxid and a.uid=$uid and a.sid=$sid and a.state=0 and a.endtime<>0 and a.status=0 or a.man='$uname' $where")
            ->order('b.id desc')->select();
            $arr_res = [];
            for ($i=0;$i<count($order);$i++){
                if (!empty($order[$i]['salary'])){
                    $id = $order[$i]['id'];
                    $result = order_attach($id);
                    if ($result){
                        $endtime = date('Y-m-d',$order[$i]['endtime']);
                        $order[$i]['intime']==0?$intime=$order[$i]['intime']:$intime = date('Y-m-d',$order[$i]['intime']);
                        $result['into_time'] = $endtime;
                        $result['intime'] = $intime;
                        array_push($arr_res,$result);
                    }
            
                }
            }
        }else {
            $order = Db::name('into_order_gx')->alias('a')->field('a.into_time,b.intime,b.id,a.salary')
            ->join('order b','a.orderid=b.id','LEFT')
            ->where("a.name='$gxname' and a.uid=$uid and a.sid=$sid and a.into_time<>0 $where")
            ->order('b.id desc')->select();
            $arr_res = [];
            for ($i=0;$i<count($order);$i++){
                if (!empty($order[$i]['salary'])){
                    $id = $order[$i]['id'];
                    $result = order_attach($id);
                    if ($result){
                        $endtime = $order[$i]['into_time'];
                        $order[$i]['intime']==0?$intime=$order[$i]['intime']:$intime = date('Y-m-d',$order[$i]['intime']);
                        $result['into_time'] = $endtime;
                        $result['intime'] = $intime;
                        array_push($arr_res,$result);
                    }
            
                }
            }
        }
        
        $this->assign('list',$arr_res);
        return $this->fetch();
    }

//员工工资提成excel
    public function staffSalary(){
        //筛选条件
        $uname = input('uname');
        $entry = input('entry/d');
        $date = ctrim(input('date'));
        $dates = ctrim(input('dates'));
        $produce_no = ctrim(input('pdn'));
        $ordernum = ctrim(input('salenum'));
        $where = '';
        $sql_text = '';

        if (!empty($produce_no)){
            $where = " and b.unique_sn like '%$produce_no%'";
            $sql_text = " and b.unique_sn like '%$produce_no%'";
        }
        if (!empty($ordernum)){
            $where .= " and b.ordernum like '%$ordernum%'";
            $sql_text .= " and b.ordernum like '%$ordernum%'";
        }
        if (!empty($entry)){
            $where .= " and b.status=$entry";
            $sql_text .= " and b.status=$entry";
        }

        if (!empty($date)){
            $arr_date = explode('-',$date,4);
            $s_date = strtotime($arr_date[0].'-'.$arr_date[1].'-'.$arr_date[2].'00:00:00');
            $e_date = strtotime($arr_date[3].'23:59:59');
            $where .= " and a.endtime between $s_date and $e_date";
            $sql_text .= " and a.into_time between $s_date and $e_date";
        }
        if (!empty($dates)){
            $arr_dates = explode('-',$dates,4);
            $s_dates = strtotime($arr_dates[0].'-'.$arr_dates[1].'-'.$arr_dates[2].' 00:00:00');
            $e_dates = strtotime($arr_dates[3].' 23:59:59');
            $where .= " and b.intime between $s_dates and $e_dates";
            $sql_text .= " and b.intime between $s_dates and $e_dates";
        }
        //订单
        $order = Db::name('flow_check')->alias('a')->field('a.salary,a.num,d.uname,c.dname,a.endtime,b.intime,a.orstatus,b.id,e.price')
            ->join('order b','a.orderid=b.id','LEFT')
            ->join('gx_list c','a.orstatus=c.id','LEFT')
            ->join('login d','a.uid=d.id','LEFT')
            ->join('formula e','a.sid=e.id','LEFT')
            ->where("a.state=0 and a.endtime<>0 and a.status=0 and a.salary!='' $where")
            ->order('a.id asc')->select();

        $total_price = 0;
        //         $total_num = 0;
        if ($order){
            foreach ($order as $key=>$vl){
                $orderid = $vl['id'];
                $detail = order_attach($orderid);
                $order[$key] = array_merge($order[$key],$detail);
                $total_price += $vl['salary'];
                //时间戳转化
                $order[$key]['endtime'] = date('Y/m/d',$order[$key]['endtime']);
                $order[$key]['intime']==0?$order[$key]['intime'] : $order[$key]['intime']=date('Y/m/d',$order[$key]['intime']);
            }
        }
        
        //入库工序
        if (FIX_GX==0){
            $result = Db::name('into_order_gx')->alias('a')->field('a.salary,a.num,d.uname,a.into_time,b.intime,b.id,e.price')
            ->join('order b','a.orderid=b.id','LEFT')
            ->join('login d','a.uid=d.id','LEFT')
            ->join('formula e','a.sid=e.id','LEFT')
            ->where(" a.salary!='' and a.ispay>1 $sql_text")
            ->order('a.id asc')->select();
            if ($result){
                foreach ($result as $key=>$vl){
                    $orderid = $vl['id'];
                    $detail = order_attach($orderid);
                    $result[$key] = array_merge($result[$key],$detail);
                    $total_price += $vl['salary'];
                    //时间戳转化
                    $result[$key]['endtime'] = date('Y/m/d',$result[$key]['into_time']);
                    $result[$key]['intime']==0?$result[$key]['intime'] : $result[$key]['intime']=date('Y/m/d',$result[$key]['intime']);
                }
                $order = array_merge($order,$result);
            }
        }
        //列表标题
        $fields=@include APP_DATA.'qrfield.php';
        $title = array('man_name'=>'员工','dname'=>'工序','finish_time'=>'完成时间','intime'=>'订单入库时间','num'=>'计件数量','price'=>'单价','salary'=>'提成');
        foreach ($fields as $fl){
            $title[$fl['fieldname']] = $fl['explains'];
        }
        $this->salaryExcelCsv($order,array_keys($title),$title,$doc=array('title'=>'员工提成'));
    }
    //员工未审核汇总
    public function nocheckExcel(){
        $field = @include APP_DATA.'qrfield.php';
        $gx_list = @include APP_DATA.'gx_list.php';
        $new_gx_list = array();
        $pro_no = '';
        $series = '';
        foreach ($gx_list as $gl){
            $new_gx_list[$gl['id']] = $gl;
        }
        foreach ($field as $fl){
            if ($fl['fieldname']=='produce_no'){
                $pro_no = $fl['explains'];
            }
            if ($fl['fieldname']=='pname'){
                $series = $fl['explains'];
            }
        }
        //检索员工未审核工序提成
        $result = Db::name('flow_check')->alias('a')->field('a.orderid,a.orstatus,a.endtime,a.real_money,b.uname,c.price')
                        ->join('login b','a.uid=b.id')->join('formula c','a.sid=c.id')->where("a.ispay=1")->select();
        if ($result){
            foreach ($result as $k=>$res){
                $order_detail = order_attach($res['orderid']);
                $result[$k]['produce_no'] = $order_detail['produce_no'];
                $result[$k]['pname'] = $order_detail['pname'];
                $result[$k]['gxname'] = $new_gx_list[$res['orstatus']]['dname'];
                $result[$k]['endtime'] = date("Y-m-d H:i:s");
            }
        }
        //入库工序提成
        $res = Db::name('flow_check')->alias('a')->field('a.orderid,a.name,a.into_time,a.real_money,b.uname,c.price')
                        ->join('login b','a.uid=b.id')->join('formula c','a.sid=c.id')->where("a.ispay=1")->select();
        if ($res){
            foreach ($res as $k=>$rs){
                $order_detail = order_attach($rs['orderid']);
                $res[$k]['produce_no'] = $order_detail['produce_no'];
                $res[$k]['pname'] = $order_detail['pname'];
                $res[$k]['gxname'] = $rs['name'];
                $res[$k]['endtime'] = date("Y-m-d H:i:s");
            }
        }
        $title = array('man_name'=>'员工','dname'=>'工序','finish_time'=>'完成时间','price'=>'单价','salary'=>'提成','real_money'=>'实发提成','produce_no'=>$pro_no,'pname'=>$series);
        $this->salaryExcelCsv($result,array_keys($title),$title,$doc=array('title'=>'员工提成未审核'));
    }
    //员工提成报表
    public function person_salary_excel(){
        $id = input('id');
        $date = input('date');
        $type = input('type/d');
        if (empty($id)){
            $this->error('参数缺失');
            exit();
        }
        $data = array();
        $new_gx_list = array();
        $field = @include APP_DATA.'qrfield.php';
        $gx_list = @include APP_DATA.'gx_list.php';
        if(!empty($id)){
            if ($type==0){
                $result = Db::name('flow_check')->alias('a')->field("a.id,a.num,a.salary,a.orstatus,a.orderid,a.ispay,a.man,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","c.id=a.uid","LEFT")
                ->where("a.id in ($id)")->select();
                
                if ($result){
                    $new_gx_list = array();
                    $total = array();
                    foreach ($gx_list as $gl){
                        $new_gx_list[$gl['id']] = $gl;
                    }
                    foreach ($result as $kv=>$vl){
                        if (!isset($data['man_name'])){
                            $data['man_name'] = $vl['uname'];
                            $data['gx_name'] = $new_gx_list[$vl['orstatus']]['dname'];
                            $date?$data['date'] = substr($date,0,10).' 至 '.substr($date,12):$data['date']='';
                            $ispay==3?$data['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$data['true_time']='';
                            $ispay>2?$data['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$data['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $data['give_man'] = $man['uname'];
                            }
                            $data['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['real_money'] = $vl['real_money'];
                        $total['snum'] += $order_detail['snum'];
                        $total['area'] += round($order_detail['area'],2);
                        $total['doornum'] += $order_detail['doornum'];
                        $total['screenwin'] += $order_detail['screenwin'];
                        $total['price'] += round($order_detail['price'],2);
                        $total['salary'] += round($order_detail['salary'],2);
                        $total['real_money'] += round($order_detail['real_money'],2);
                        array_push($data['order_list'],$order_detail);
                    }
                }
            }else {
                $result = Db::name('into_order_gx')->alias('a')->field("a.id,a.name,a.num,a.salary,a.orderid,a.ispay,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","c.id=a.uid","LEFT")
                ->where("a.id in ($id)")->select();
                
                if ($result){
                    $total = array();
                    foreach ($result as $kv=>$vl){
                        if (!isset($data['man_name'])){
                            $data['man_name'] = $vl['uname'];
                            $data['gx_name'] = $vl['name'];
                            $date?$data['date'] = substr($date,0,10).' 至 '.substr($date,12):$data['date']='';
                            $ispay==3?$data['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$data['true_time']='';
                            $ispay>2?$data['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$data['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $data['give_man'] = $man['uname'];
                            }
                            $data['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['real_money'] = $vl['real_money'];
                        $total['snum'] += $order_detail['snum'];
                        $total['area'] += round($order_detail['area'],2);
                        $total['doornum'] += $order_detail['doornum'];
                        $total['screenwin'] += $order_detail['screenwin'];
                        $total['price'] += round($order_detail['price'],2);
                        $total['salary'] += round($order_detail['salary'],2);
                        $total['real_money'] += round($order_detail['real_money'],2);
                        array_push($data['order_list'],$order_detail);
                    }
                }
            }
            
        }
        $title = [];
        foreach ($field as $key=>$fl){
            switch ($fl['fieldname']){
                case 'produce_sn':
                    $title['produce_sn'] = $fl['explains'];
                    break;
                    case 'produce_no':
                        $title['produce_no'] = $fl['explains'];
                        break;
                        case 'pname':
                            $title['pname'] = $fl['explains'];
                            break;
                            case 'snum':
                                $title['snum'] = $fl['explains'];
                                break;
                                case 'area':
                                    $title['area'] = $fl['explains'];
                                    break;
                                    case 'doornum':
                                        $title['doornum'] = $fl['explains'];
                                        break;
                                        case 'screenwin':
                                            $title['screenwin'] = $fl['explains'];
                                            break;
            }
        }
        $title['price'] = '单价';
        $title['salary'] = '提成';
        $title['real_money'] = '实发提成';
        $this->salaryExcel($data,array_keys($title),$title,$total);
    }
    private function salaryExcelCsv($list,$fields,$title,$doc){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
    
        $fileName = date('YmdHis', time());
        ob_end_clean();
        header('Content-Encoding: UTF-8');
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Disposition: attachment;filename="' . $doc['title'].$fileName . '.csv"');
    
        $fp = fopen('php://output', 'a');
	   fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($title as $key => $item){
            $title[$key] = $item;
        }
    
        fputcsv($fp, $title);
    
        foreach($list as $key=>$value){
            $row=array();
            foreach ($fields as $k=> $name){
                $row[$k] = $value[$name];
            }
            fputcsv($fp, $row);
        }
    
        ob_flush();  //刷新缓冲区
        flush();
    }
    public function salaryExcel($order,$keys,$title,$total){
        $fields=@include APP_DATA.'qrfield.php';
        $allfield = $fields;

        //创建excel对象
        $objExcel = new \PHPExcel();
        //创建导出格式
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel,'Excel5');
        //创建工作表sheet
        $objActSheet = $objExcel->getActiveSheet();
        //字母
        //最多导出字段数，可以继续增加
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y', 'Z');
        $Excel_letter = array();
        //动态生成列名
        $length = count($keys);
        $need_num = ceil($length/26);
        if ($need_num>1){
            $Excel_letter=$letter;
            for ($i=0;$i<$need_num;$i++){
                for ($s=0;$s<count($letter);$s++){
                    $text = $letter[$i].$letter[$s];
                    array_push($Excel_letter,$text);
                }
            }
        }else {
            $Excel_letter=$letter;
        }
        //表样式设置
        //加边框
        $border = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),

            ),
        );
        $num = count($order['order_list'])+2;
        $objActSheet->getStyle('A2:'.$Excel_letter[count($title)-1].$num)->applyFromArray($border);
        for ($i=0;$i<count($title);$i++){
            for ($t=0;$t<count($order['order_list'])+1;$t++){
                $add_num = $t+2;
                $objActSheet->getStyle($Excel_letter[$i].$add_num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objActSheet->getStyle($Excel_letter[$i].$add_num)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->getStyle($Excel_letter[$i].'2')->getFont()->setBold(true);  
            } 
            $objActSheet->getColumnDimension($Excel_letter[$i])->setWidth(20);
        }
        //字段名称
        $objActSheet->setCellValue('A1','员工：'.$order['man_name']);
        $objActSheet->setCellValue('D1','工序：'.$order['gx_name']);
        $objActSheet->setCellValue('J1','报工时间：'.$order['date']);
        $s=0;
        foreach ($title as $k=>$tl){
            $objActSheet->setCellValue($letter[$s].'2',$tl);
            $s++;
        }
        //赋值
        $a=3;
        
        for($i=0;$i<count($order['order_list']);$i++){
            $index = $a+$i;
            $b=0;
            foreach ($keys as $key=>$tl){
                $objActSheet->setCellValue($letter[$b].$index,$order['order_list'][$i][$tl]);
                $b++;
            }
        }
        //汇总
        foreach ($keys as $kc=>$fl){
            if ($kc==0){
                $objActSheet->setCellValue($letter[0].(count($order['order_list'])+3),'汇总');
            }else {
                $objActSheet->setCellValue($letter[$kc].(count($order['order_list'])+3),$total[$fl]);
            }
        }

        $objActSheet->setCellValue('A'.(count($order['order_list'])+4),'员工审核：'.$order['true_time']);
        $objActSheet->setCellValue('J'.(count($order['order_list'])+4),'财务审核：'.$order['give_man'].'  '.$order['give_time']);
        
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=员工提成数据表.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit();
    }
    //计件公式
    public function formula(){
        $gx = input('gxs');
        $type = input('type');
        $where = '';
        if (!empty($gx)){
            $where = " a.gxid=$gx";
        }
        if (!empty($type)){
            $where .= " and a.is_into=$type";
        }
        //绑定字段名
        $fields=@include APP_DATA.'qrfield.php';
        $allfield = $fields;
        //工序获取
        $gxL = Db::name('gx_list')->alias('a')->field('a.*,b.title')->join('gx_line b','a.lid=b.id','LEFT')
            ->where('a.isdel=0')->order('a.orderby asc')->select();
            //动态入库
            if (FIX_GX==0){
                $auto_gx = Db::name('into_gx')->order('id asc')->select();
                if (!empty($auto_gx)){
                    foreach ($auto_gx as $k=>$ag){
                        array_push($gxL,$auto_gx[$k]);
                    }
                    
                }
            }
        if ($gxL){
            $this->assign('gxList',$gxL);
        }else {
            $this->assign('gxList',array());
        }
        //公式列表
        $result = Db::name('formula')->alias('a')->field('a.*,b.dname,b.work_unit,c.content')->join('gx_list b','a.gxid=b.id','LEFT')
            ->join('se_formula c','a.sid=c.id','LEFT')->where($where)->order('a.gxid asc,a.sort asc')->paginate(20);
        $change = $result->all();
        foreach ($change as $k=>$ck){
            if (empty($ck['dname'])){
                $gx_name = Db::name('into_gx')->where("id",$ck['gxid'])->find();
                $change[$k]['dname'] = $gx_name['name'];
            }
        }
        $this->assign('gx',$gx);
        $this->assign('type',$type);
        $this->assign('list',$change);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('total',$result->total());
        $this->assign('qrcodeList',$allfield);
        return $this->fetch();
    }
    //修改工序页面
    public function editformula(){
        $id = intval(input('id'));
        $fields=@include APP_DATA.'qrfield.php';
        $allfield = $fields;
        //工序获取
        $gxL = Db::name('gx_list')->alias('a')->field('a.*,b.title')
            ->join('gx_line b','a.lid=b.id','LEFT')
            ->where('a.isdel=0')->order('a.orderby asc')->select();
        //动态入库
        if (FIX_GX==0){
            $auto_gx = Db::name('into_gx')->order('id asc')->select();
            if (!empty($auto_gx)){
                 foreach ($auto_gx as $k=>$ag){
                        array_push($gxL,$auto_gx[$k]);
                }
            }
        }
        if ($gxL){
            $this->assign('gxList',$gxL);
        }else {
            $this->assign('gxList',array());
        }
        //该工序
        $result = Db::name('formula')->alias('a')->field('a.*,b.content,b.fields')->join('se_formula b','b.id=a.sid','LEFT')->where("a.id=$id")->find();

        if ($result){
            $this->assign('res',$result);
        }
        $this->assign('qrcodeList',$allfield);
        return $this->fetch();
    }
    //添加或修改公式
    public function addformula(){

        if (request()->isAjax()){
            $id = intval(input('param.id'));
            $sid = intval(input('param.sid'));
            $gid = intval(input('param.gid'));
            $type = input('type/d');
            $content = input('param.content');
            $fieldname = input('param.field');
            $formula = input('param.gongshi');
            $unitname = input('param.unitname');
            $price = input('param.price');
            $sort = input('param.sort');
            $field = input('param.fields');
            $contents = input('param.contents');
            $end = 0;
            if ($id){
                if (!empty($sid)){
                    $res = Db::name('se_formula')->where("id=$sid")->update(array('content'=>$contents,'fields'=>$field));
                }
                $arr = array('text'=>$content,'fieldname'=>$fieldname,'unitname'=>$unitname,'formula_text'=>$formula,'price'=>$price,'sort'=>$sort);
                $result = Db::name('formula')->where("id=$id")->update($arr);

            }else {
                if (!empty($field)){
                    $inse = Db::name('se_formula')->insertGetId(array('content'=>$contents,'fields'=>$field));
                    $arr = array('gxid'=>$gid,'is_into'=>$type,'sid'=>$inse,'text'=>$content,'fieldname'=>$fieldname,'unitname'=>$unitname,'formula_text'=>$formula,'price'=>$price,'sort'=>$sort);
                }else {
                    $arr = array('gxid'=>$gid,'is_into'=>$type,'text'=>$content,'fieldname'=>$fieldname,'unitname'=>$unitname,'formula_text'=>$formula,'price'=>$price,'sort'=>$sort);
                }

                $result = Db::name('formula')->insert($arr);
            }
            if ($end==0){
                echo json_encode(array('code'=>0,'msg'=>'操作成功'));
            }else {
                echo json_encode(array('code'=>1,'msg'=>'该工序已存在'));
            }
        }

    }
    //公式删除
    public function delformula(){
        if(request()->isAjax()){
            $id = intval(input('id'));
            $sid = intval(input('sid'));
            if($id=='' || $id==null){
                return json_encode(array('code'=>1,'msg'=>'数据缺失'));
                exit();
            }
            $del = Db::name('formula')->where("id=$id")->delete();
            if (!empty($sid)){
                $dels = Db::name('se_formula')->where("id=$sid")->delete();
            }
            if ($del){
                echo json_encode(array('code'=>0));
            }else {
                echo json_encode(array('code'=>1));
            }

        }
    }
    //字段含义数据导出
    public function fieldexcel(){
        $allfield=@include APP_DATA.'qrfield.php';
        //创建excel对象
        $objExcel = new \PHPExcel();
        //创建导出格式
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel,'Excel5');
        //创建工作表sheet
        $objActSheet = $objExcel->getActiveSheet();
        //字母
        //最多导出字段数，可以继续增加
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y', 'Z');
        $Excel_letter = array();
        array_unshift($allfield,['fieldname'=>'in_num','explains'=>'报工数量']);
        //动态生成列名
        $length = count($allfield);
        $need_num = ceil($length/26);
        if ($need_num>1){
            $Excel_letter=$letter;
            for ($i=0;$i<$need_num;$i++){
                for ($s=0;$s<count($letter);$s++){
                    $text = $letter[$i].$letter[$s];
                    array_push($Excel_letter,$text);
                }
            }
        }else {
            $Excel_letter=$letter;
        }
        //表样式设置
        for ($i=0;$i<count($Excel_letter);$i++){
            $objActSheet->getStyle($Excel_letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objActSheet->getStyle($Excel_letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objActSheet->getStyle($Excel_letter[$i])->getFont()->setBold(true);
            $objActSheet->getColumnDimension($Excel_letter[$i])->setWidth(12);
        }
        //赋值
        for ($i=0;$i<count($Excel_letter);$i++){
            $objActSheet->setCellValue($Excel_letter[$i].'1',$allfield[$i]['fieldname']);
            $objActSheet->setCellValue($Excel_letter[$i].'2',$allfield[$i]['explains']);
        }

        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=字段含义数据表.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit();

    }

    //员工提成手输
    public function handSalary(){
        //筛选条件
        $uname = input('uname');
        $date = ctrim(input('date'));
        $where = '';

        if (!empty($uname)){
            $where = " and a.uname like '%$uname%'";
        }
        if (!empty($date)){
            $arr_date = explode('-',$date,4);
            $s_date = strtotime($arr_date[0].'-'.$arr_date[1].'-'.$arr_date[2].'00:00:00');
            $e_date = strtotime($arr_date[3].'23:59:59');
            $where .= " and a.endtime between $s_date and $e_date";
        }
        //工序获取
        $gxL = Db::name('gx_list')->alias('a')->field('a.*,b.title')->join('gx_line b','a.lid=b.id','LEFT')
            ->where('a.isdel=0')->order('a.orderby asc')->select();
        if ($gxL){
            $this->assign('gxList',$gxL);
        }else {
            $this->assign('gxList',array());
        }
        $result = Db::name('hand_salary')->alias('a')->field('a.*,b.dname')
            ->join('gx_list b','a.gxid=b.id','LEFT')->where("1=1 $where")->order('a.id desc')->paginate(20,false,['query'=>request()->param()]);
        //总提成
        $total_price = 0;
        $res = $result->all();
        foreach ($res as $key=>$vl){
            $total_price += $vl['price'];
        }

        $page = $result->render();
        $this->assign('get',input(""));
        $this->assign('list',$res);
        $this->assign('totalprice',$total_price);
        $this->assign('page',$page);
        $this->assign('total',$result->total());
        return $this->fetch();
    }

    //手动输入员工提成
    public function addsalary(){
        if (request()->isAjax()){
            $id = intval(input('param.id'));
            $gid = intval(input('param.gid'));
            $uname = input('param.uname');
            $jt_time = strtotime(input('param.jt_time'));
            $unitname = input('param.unitname');
            $price = input('param.price');
            $ordernum = input('param.ordernum');
            $contents = input('param.contents');
            $end = 0;
            if ($id){
                $arr = array('uname'=>$uname,'gxid'=>$gid,'unitname'=>$unitname,'produce_no'=>$contents,'num'=>$ordernum,'price'=>$price,'endtime'=>$jt_time);
                $result = Db::name('hand_salary')->where("id=$id")->update($arr);
                $result?$end:$end=1;
            }else {
                $arr = array('uname'=>$uname,'gxid'=>$gid,'unitname'=>$unitname,'produce_no'=>$contents,'num'=>$ordernum,'price'=>$price,'endtime'=>$jt_time);
                $result = Db::name('hand_salary')->insert($arr);
            }
            if ($end==0){
                echo json_encode(array('code'=>0,'msg'=>'操作成功'));
            }else {
                echo json_encode(array('code'=>1,'msg'=>'操作失败'));
            }
        }
    }

    //员工提成（手输）删除
    public function delsalary(){
        if(request()->isAjax()){
            $id = intval(input('id'));
            if($id=='' || $id==null){
                return json_encode(array('code'=>1,'msg'=>'数据缺失'));
                exit();
            }
            $del = Db::name('hand_salary')->where("id=$id")->delete();
            if ($del){
                echo json_encode(array('code'=>0));
            }else {
                echo json_encode(array('code'=>1));
            }

        }
    }

    //编辑员工提成
    public function editsalary(){
        $id = intval(input('id'));
        //工序获取
        $gxL = Db::name('gx_list')->alias('a')->field('a.*,b.title')
            ->join('gx_line b','a.lid=b.id','LEFT')
            ->where('a.isdel=0')->order('a.orderby asc')->select();
        if ($gxL){
            $this->assign('gxList',$gxL);
        }else {
            $this->assign('gxList',array());
        }
        //该工序
        $result = Db::name('hand_salary')->where("id=$id")->find();

        if ($result){
            $result['endtime']==''?$result['endtime']:$result['endtime']=date('Y/m/d',$result['endtime']);
            $this->assign('res',$result);
        }

        return $this->fetch();
    }

    //日对账
    public function paySalary(){
        $date = input("date");
        $uid = input("uid/d");
        $gid = input("gid/d");
        $type = input('type/d','');
        $ispay = input("status/d",0);
        $gx_list = @include APP_DATA.'gx_list.php';
        $where = "";
        $sql_text = '';
        $data = array();
        if (!empty($date)){
            $starttime = strtotime(substr($date,0,10).' 00:00:00');
            $endtime = strtotime(substr($date,12).' 23:59:59');
            $where = " and a.endtime between $starttime and $endtime";
            $sql_text = " and unix_timestamp(a.into_time) between $starttime and $endtime"; 
            $this->assign('date',$date);
        }
        if (!empty($uid)){
            $where .= " and a.uid=$uid";
            $sql_text .= " and a.uid=$uid";
            $this->assign('uid',$uid);
        }
        if (!empty($gid)){
            $where .=" and a.orstatus=$gid";
            if ($type==1){
                $into_name = Db::name('into_gx')->where("id=$gid")->find();
                $gxname = $into_name['name'];
                $sql_text .= " and a.name='$gxname'";
            }

            $this->assign('gid',$gid);
        }
        $this->assign('type',$type);
        $this->assign('ispay',$ispay);
        if (!empty($date) ||!empty($uid) || !empty($gid) || !empty($ispay)){
            if (empty($type) || $type==0){
                $result = Db::name('flow_check')->alias('a')->field("a.id,a.num,a.salary,a.uid,a.orstatus,a.orderid,a.ispay,a.sid,a.real_money,a.check_id,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","a.uid=c.id")->where("a.sid>0 $where and a.ispay=$ispay")->select();//固定工序提成
                
                if ($result){
                    //简化获取工序
                    $new_gx_list = array();
                    foreach ($gx_list as $gl){
                        $new_gx_list[$gl['id']] = $gl;
                    }
                    foreach ($result as $kv=>$vl){
                        if (!isset($data[$vl['uid'].$vl['orstatus']])){
                            $data[$vl['uid'].$vl['orstatus']] = array();
                            $data[$vl['uid'].$vl['orstatus']]['type'] = 0;
                            $data[$vl['uid'].$vl['orstatus']]['man_name'] = $vl['uname'];
                            $data[$vl['uid'].$vl['orstatus']]['gx_name'] = $new_gx_list[$vl['orstatus']]['dname'];
                            $date?$data[$vl['uid'].$vl['orstatus']]['date'] = substr($date,0,10).' 至 '.substr($date,12):$data[$vl['uid'].$vl['orstatus']]['date']='';
                            $ispay==3?$data[$vl['uid'].$vl['orstatus']]['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$data[$vl['uid'].$vl['orstatus']]['true_time']='';
                            $ispay>2?$data[$vl['uid'].$vl['orstatus']]['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$data[$vl['uid'].$vl['orstatus']]['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $data[$vl['uid'].$vl['orstatus']]['give_man'] = $man['uname'];
                            }
                            $data[$vl['uid'].$vl['orstatus']]['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['orderid'] = $vl['orderid'];
                        $order_detail['sid'] = $vl['sid'];
                        $order_detail['real_money'] = $vl['real_money'];
                        array_push($data[$vl['uid'].$vl['orstatus']]['order_list'],$order_detail);
                    }
                }
            }
            if (empty($type) || $type==1){
                $auto_result = Db::name('into_order_gx')->alias('a')->field("a.id,a.num,a.salary,a.uid,a.name,a.orderid,a.sid,a.ispay,a.real_money,a.check_id,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","a.uid=c.id")
                ->join("into_gx d","a.name=d.name")->where("a.sid>0 $sql_text and a.ispay=$ispay")->select();//动态工序提成
                if ($auto_result){
                    $list = array();
                    foreach ($auto_result as $kv=>$vl){
                        if (!isset($list[$vl['uid'].$vl['name']])){
                            $list[$vl['uid'].$vl['name']] = array();
                            $list[$vl['uid'].$vl['name']]['type'] = 1;
                            $list[$vl['uid'].$vl['name']]['man_name'] = $vl['uname'];
                            $list[$vl['uid'].$vl['name']]['gx_name'] = $vl['name'];
                            $date?$list[$vl['uid'].$vl['name']]['date'] = substr($date,0,10).' 至 '.substr($date,12):$list[$vl['uid'].$vl['name']]['date']='';
                            $ispay==3?$list[$vl['uid'].$vl['name']]['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$list[$vl['uid'].$vl['name']]['true_time']='';
                            $ispay>2?$list[$vl['uid'].$vl['name']]['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$list[$vl['uid'].$vl['name']]['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $list[$vl['uid'].$vl['name']]['give_man'] = $man['uname'];
                            }
                            $list[$vl['uid'].$vl['name']]['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['orderid'] = $vl['orderid'];
                        $order_detail['sid'] = $vl['sid'];
                        $order_detail['real_money'] = $vl['real_money'];
                        array_push($list[$vl['uid'].$vl['name']]['order_list'],$order_detail);
                    }
                    $data = array_merge($data,$list);
                }
            }
            
        }
        $this->assign('list',$data);
        return $this->fetch();
    }

    //修改报工信息的提成状态
    //string id
    //int status
    public function changeStatus(){
        $id = input('id/a');
        $status = input('status/d');
        $type = input('type/d');
        $time = time();
        if ($type==0){
            if (!empty($id) && !empty($status)){
                if ($status==2){
                    $result = Db::name('flow_check')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status,'true_time'=>$time));
                }elseif ($status==3){
                    $result = Db::name('flow_check')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status,'give_time'=>$time,'check_id'=>session('uid')));
                }else{
                    $result = Db::name('flow_check')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status));
                }
                return ['code'=>0,'msg'=>'修改成功'];
            }else {
                return ['code'=>1,'msg'=>'参数缺失'];
            }
        }else {
            if (!empty($id) && !empty($status)){
                if ($status==2){
                    $result = Db::name('into_order_gx')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status,'true_time'=>$time));
                }elseif ($status==3){
                    $result = Db::name('into_order_gx')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status,'give_time'=>$time,'check_id'=>session('uid')));
                }else{
                    $result = Db::name('into_order_gx')->where("id in (".implode(',',$id).")")->update(array('ispay'=>$status));
                }
                return ['code'=>0,'msg'=>'修改成功'];
            }else {
                return ['code'=>1,'msg'=>'参数缺失'];
            }
        }
        
    }

    //保存实发提成
    public function save_data(){
        $data = input('data/a');
        $type = input('type/d');
        if (count($data)==0){
            return ['code'=>1,'msg'=>'参数缺失'];
            exit();
        }
        if ($type==0){
            foreach ($data as $k=>$vl){
                $result = Db::name('flow_check')->where('id',$vl['fid'])->update(array('real_money'=>$vl['value']));
            }
        }else {
            foreach ($data as $k=>$vl){
                $result = Db::name('into_order_gx')->where('id',$vl['fid'])->update(array('real_money'=>$vl['value']));
            }
        }
        return ['code'=>0,'msg'=>'保存成功'];
    }
    //提成信息打印页
    public function printsalary(){
        $id = input('id/s');//报工表id数组字符串
        $type = input('type/d');
        $gx_list = @include APP_DATA.'gx_list.php';
        $data = array();
        if(!empty($id)){
            if ($type==0){
                $result = Db::name('flow_check')->alias('a')->field("a.id,a.num,a.salary,a.orstatus,a.orderid,a.ispay,a.man,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","c.id=a.uid","LEFT")
                ->where("a.id in ($id)")->select();
                
                if ($result){
                    $new_gx_list = array();
                    foreach ($gx_list as $gl){
                        $new_gx_list[$gl['id']] = $gl;
                    }
                    foreach ($result as $kv=>$vl){
                        if (!isset($data[$vl['uid'].$vl['orstatus']])){
                            $data[$vl['uid'].$vl['orstatus']] = array();
                            $data[$vl['uid'].$vl['orstatus']]['man_name'] = $vl['uname'];
                            $data[$vl['uid'].$vl['orstatus']]['gx_name'] = $new_gx_list[$vl['orstatus']]['dname'];
                            $date?$data[$vl['uid'].$vl['orstatus']]['date'] = substr($date,0,10).' 至 '.substr($date,12):$data[$vl['uid'].$vl['orstatus']]['date']='';
                            $ispay==3?$data[$vl['uid'].$vl['orstatus']]['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$data[$vl['uid'].$vl['orstatus']]['true_time']='';
                            $ispay>2?$data[$vl['uid'].$vl['orstatus']]['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$data[$vl['uid'].$vl['orstatus']]['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $data[$vl['uid'].$vl['orstatus']]['give_man'] = $man['uname'];
                            }
                            $data[$vl['uid'].$vl['orstatus']]['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['real_money'] = $vl['real_money'];
                        array_push($data[$vl['uid'].$vl['orstatus']]['order_list'],$order_detail);
                    }
                }
            }else{
                $result = Db::name('into_order_gx')->alias('a')->field("a.id,a.name,a.num,a.salary,a.orderid,a.ispay,a.true_time,a.give_time,b.price,c.uname")
                ->join("formula b","a.sid=b.id")
                ->join("login c","c.id=a.uid","LEFT")
                ->where("a.id in ($id)")->select();
                
                if ($result){
                    foreach ($result as $kv=>$vl){
                        if (!isset($data[$vl['uid'].$vl['name']])){
                            $data[$vl['uid'].$vl['name']] = array();
                            $data[$vl['uid'].$vl['name']]['man_name'] = $vl['uname'];
                            $data[$vl['uid'].$vl['name']]['gx_name'] = $vl['name'];
                            $date?$data[$vl['uid'].$vl['name']]['date'] = substr($date,0,10).' 至 '.substr($date,12):$data[$vl['uid'].$vl['name']]['date']='';
                            $ispay==3?$data[$vl['uid'].$vl['name']]['true_time'] = date("Y-m-d H:i:s",$vl['true_time']):$data[$vl['uid'].$vl['name']]['true_time']='';
                            $ispay>2?$data[$vl['uid'].$vl['name']]['give_time'] = date("Y-m-d H:i:s",$vl['give_time']):$data[$vl['uid'].$vl['name']]['give_time']='';
                            if (!empty($vl['check_id'])){
                                $man = Db::name('login')->where('id',$vl['check_id'])->find();
                                $data[$vl['uid'].$vl['name']]['give_man'] = $man['uname'];
                            }
                            $data[$vl['uid'].$vl['name']]['order_list'] = array();
                        }
                        //获取订单详情
                        $order_detail = order_attach($vl['orderid']);
                        $order_detail['price'] = $vl['price'];
                        $order_detail['salary'] = $vl['salary'];
                        $order_detail['fid'] = $vl['id'];
                        $order_detail['real_money'] = $vl['real_money'];
                        array_push($data[$vl['uid'].$vl['name']]['order_list'],$order_detail);
                    }
                }
            }
            
        }
        $this->assign('list',$data);
        return $this->fetch();

    }
//     一键推送审核
    public function send_all_check(){
        $data = input('data/a');
        $type = input('type/d');
        if (empty($data)){
            return ['code'=>1,'msg'=>'参数缺失'];
        }
//         $data =json_decode($data);
        $one_group = array();
        $two_group = array();
        foreach ($data as $k=>$dk){
            if ($dk['type']==0){
                $id = explode(',',$dk['id']);
                $one_group=array_merge($one_group,$id);
            }else {
                $id = explode(',',$dk['id']);
                $two_group=array_merge($two_group,$id);
            }
        }
        if (count($one_group)>0){
            $result = Db::name('flow_check')->where("id in (".implode(',',$one_group).")")->update(array('ispay'=>1));
        }
        if (count($two_group)>0){
            $result = Db::name('into_order_gx')->where("id in (".implode(',',$two_group).")")->update(array('ispay'=>1));
        }
        return ['code'=>0,'msg'=>'推送成功'];
    }
    
    public function show_formula(){
        $orderid = input('orderid/d');
        $sid = input('fid/d');
        $field = @include APP_DATA.'qrfield.php';
        if (empty($orderid) && empty($sid)){
            return ['code'=>1,'msg'=>'参数缺失'];
            exit();
        }
        $result = Db::name('formula')->where('id',$sid)->find();
        $order_detail = order_attach($orderid);
        $data = array();
        $data['text'] = $result['formula_text'].'*'.$result['price'];
        $data['order'] = $order_detail;
        $data['field'] = $result['formula_text'];
        return ['code'=>0,'data'=>$data,'field'=>$field];
    }
     
}