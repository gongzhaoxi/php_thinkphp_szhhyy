<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\service\PrintYi;

class Printer extends Controller{
    /**
     * 扫码入库
     */
    public function scan_qrcode()
    {
        if(request()->ispost()){
            $uid = input('uid');
            $orderNumber = input('unique_sn');
            $type = input('type/a');
            $typeText = input('type_text');
            $count = input('number',1);
            $printId = input('print_id');               
            $allInto = input('all_into');
            $store = input("store_space");
            $note = input("note");
            
//            $print = new PrintYi();
//            $print->setPrint($printId,$orderNumber);
            
            $type = $typeText!=''?$typeText:implode('-', $type);
            $data = Db::name('order')->where('unique_sn',$orderNumber)->find();
            if(!$data){
                $this->_error('未找到此订单');
            }            
            //将订单附表数据合并到一起
            $orderid = $data['id'];
            $list = order_attach($data['id']);
            $list = array_merge($data,$list);
            $list['type'] = $type;            
            
//            $res = $print->executePrint($list, $count);//执行打印
////            $res = $print->picturePrint($list, $count);//执行打印
//            if(!$res){
//                $this->_error('打印失败,请重试');
//            }

            //查询入库种类表是否存在提交的种类,不存在则添加 
            $gxtype = Db::name('into_gx')->where('name',$type)->find();
            if(!$gxtype){
                $gx_id=Db::name('into_gx')->insertGetId(['name'=>$type,'addtime'=>time()]);
            }else {
                $gx_id = $gxtype['id'];
            }
            //计件所需变量
            $salary = 0;
            $nums = 0;
            $fid = - 1;
            if (PRO_SALARY == 1) {
                /* 提成计算start */
                $formula = Db::name('formula')->where("gxid=$gx_id and text!='' and is_into=1")
                ->order('sort asc')
                ->select();
                $orderdetail = Db::name('order_attach')->where("orderid=$orderid")->select();
                //                 $order_data = order_attach($orderid);
                if ($formula) {
                    for ($s = 0; $s < count($formula); $s ++) {
                        $sid = $formula[$s]['sid'];
                        strchr($formula[$s]['text'], '|') ? $pd_content = explode( '|',$formula[$s]['text']) : $pd_content = array(
                            $formula[$s]['text']
                        );
                        $pd = $formula[$s]['fieldname'];
                        // 订单详情
                        $compare = '';
                        $value = '';
                        foreach ($orderdetail as $dl) {
                            if ($dl['fieldname'] == $pd) {
                                $compare = $dl['value'];
                            }
                            $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                        }
                        $value .= 'in_num'.'='.$count;
                        parse_str($value);
                        foreach ($pd_content as $pc){
                            if (strpos($compare,$pc)!==false){
                                if (! empty($sid)) {
                                    $second_res = Db::name('se_formula')->where("id=$sid")->find();
                                    $pd = $second_res['fields'];
                                    $se_text = $list[$pd];
                                    strchr($second_res['content'], '|') ? $pd_text = explode( '|',$second_res['content']) : $pd_text = array($second_res['content']);
                                    foreach ($pd_text as $pt){
                                        if (strpos($se_text,$pt)!==false) {
                                            // 计件数量
                                            $jnum = $formula[$s]['formula_text'];
                                            $nums = eval("return $jnum;");
                                            $salary = $nums * $formula[$s]['price'];
                                            $fid = $formula[$s]['id'];
                                            break;
                                        }
                                    }
            
                                } else {
                                    // 计件数量
                                    $jnum = $formula[$s]['formula_text'];
                                    $nums = eval("return $jnum;");
                                    $salary = $nums * $formula[$s]['price'];
                                    $fid = $formula[$s]['id'];
                                    break;
                                }
                            }
                        }
                        if ($fid>-1){
                            break;
                        }
                    }
                }
                if ($salary == 0) {
                    $formula_l = Db::name('formula')->where("gxid=$gx_id and text='' and is_into=1")->find();
                    if ($formula_l) {
                        $value = '';
                        foreach ($orderdetail as $dl) {
                            $value .= $dl['fieldname'] . '=' . $dl['value'] . '&';
                        }
                        $value .= 'in_num'.'='.$count;
                        parse_str($value);
            
                        // 计件数量
                        $jnum = $formula_l['formula_text'];
                        $nums = eval("return $jnum;");
                        $salary = $nums * $formula_l['price'];
                        $fid = $formula_l['id'];
                    }
                    // echo json_encode($formula_l);
                    // exit();
                }
                /* 提成计算end */
            }
            //插入订单入库工序表
            $orderGx = ['uid'=>$uid,'orderid'=>$data['id'],'name'=>$type,'count'=>$count,'into_time'=> date('Y-m-d'),'num'=>$nums,'salary'=>$salary,'sid'=>$fid,'store_space'=>$store,'note'=>$note];           
            Db::name('into_order_gx')->insert($orderGx);
            //如果此订单状态为未入库，更新订单状态
            if($data['status'] == 0){
                 Db::name('order')->where('id',$data['id'])->update(['status'=>1]);
            }
            //如果是整单入库,则将endstatus=2
//            if($allInto == 1){
//                Db::name('order')->where('id',$data['id'])->update(['endstatus'=>2]);
//            }
           
            $this->_success('入库成功');
            
            return;
        }
        $orderid = input('orderid/d');
        $uid = session('uid');
        $ordernum = '';
        if (!empty($orderid)){
            $order_detail = Db::name('order')->where('id',$orderid)->find();
            $ordernum = $order_detail['unique_sn'];
        }
        $login = Db::name('login')->where("user_role=2 or user_role=6 and del=0 and dimission=0")->select();
        $intoGroup = Db::name('into_gx')->order('id desc')->select();       
        $intoPrinter = Db::name('print_style')->where('type',1)->select();
        $this->assign('uid',$uid);
        $this->assign('ordernum',$ordernum);
        $this->assign('into_group',$intoGroup);
        $this->assign('user',$login);
        $this->assign('into_printer',$intoPrinter);
        return $this->fetch();
    }
    
    
    /**
     * 打印半成品标签
     */
    public function ban_product()
    {
        if(request()->ispost()){
            $uid = input('uid');
            $orderNumber = input('unique_sn');
            $type = input('type/a');
            $typeText = input('type_text');
            $count = input('number',1);
            $printId = input('print_id');               
            $allInto = input('all_into');
            
            $print = new PrintYi();
            $print->setPrint($printId,$orderNumber);
            
            $type = $typeText!=''?$typeText:implode('-', $type);
            $data = Db::name('order')->where('unique_sn',$orderNumber)->find();
            if(!$data){
                $this->_error('未找到此订单');
            }            
            //将订单附表数据合并到一起
            $list = order_attach($data['id']);
            $list = array_merge($data,$list);
            $list['type'] = $type;            
            
            $res = $print->executePrint($list, $count);//执行打印
//            $res = $print->picturePrint($list, $count);//执行打印
            if(!$res){
                $this->_error('打印失败,请重试');
            }

            //查询入库种类表是否存在提交的种类,不存在则添加 
            $gxtype = Db::name('into_gx')->where('name',$type)->find();
            if(!$gxtype){
                Db::name('into_gx')->insert(['name'=>$type,'addtime'=>time()]);
            }                       
            $this->_success('打印成功');            
            return;
        }
        
        $login = Db::name('login')->where('user_role',2)->select();
        $intoGroup = Db::name('into_gx')->order('id desc')->select();       
        $intoPrinter = Db::name('print_style')->where('type',3)->select();
        
        $this->assign('into_group',$intoGroup);
        $this->assign('user',$login);
        $this->assign('into_printer',$intoPrinter);
        return $this->fetch();
    }
    
    /**
     * 异步加载用户所关联的打印机
     */
    public function selectPrint()
    {
        $uid = input('uid');
        $type = input('type',1);
        $print = new PrintYi();
        $select = $print->getPrintStyle($uid,$type);
        if($select){
            $printCache = @include_once APP_DATA.'print_style.php';
            foreach ($select as $key => $value) {
                $select[$key]['style_name'] = $printCache[$value['style']];
            }
            return $select;
        }
        return '';
    }
    
   
    
    /**
     * 导入切割方案
     */
    public function import()
    {
        $file = $this->request->file('file');
        $info = $file->move( './uploads');
        $filePath = './uploads/' . $info->getSaveName();  
       
        if(!$info){
            $this->_error('上传失败');
        }
        $field = ['sort','size','angle','ordernum','name','date'];
        $data = $this->read($filePath,$field);
        unset($data[0]);
        if(count($data) == 0){
            $this->_error('excel数据为空,请填写数据');
        }
        $batch = Db::name('print_cut')->max('batch');
        $batch = $batch+1;
        //处理phpexcel的时间格式,添加batch
        foreach ($data as $key => $value) {
            $data[$key]['date'] = strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($value['date'])));
            $data[$key]['batch'] = $batch;
            $data[$key]['addtime'] = time();
        }
        $res = Db::name('print_cut')->insertAll($data);
        if($res){
            $this->_success('导入成功');
        }
        $this->_error('导入失败,请重试');
        
    }
    
        
    /**
     * 打印切割方案界面
     */
    public function cut()
    {
        $nameSearch = input('get.name');
        $angleSearch = input('get.angle');
        $dateSearch = input('get.date');
        $importSearch = input('get.import_date');
        
        if($nameSearch){
            $where[] = ['name','=',$nameSearch];
        }
        if($angleSearch){
            $where[] = ['angle','=',$angleSearch];
        }
        if($dateSearch){
            $date = strtotime($dateSearch);
            $where[] = ['date','>=',$date];
            $where[] = ['date','<=',$date+(24*3600-1)];
        }
        if($importSearch){
            $date = strtotime($importSearch);
            $where[] = ['addtime','>=',$date];
            $where[] = ['addtime','<=',$date+(24*3600-1)];
        }
        
        $where = isset($where)&&count($where)>0?$where:"id=0";
        $list = Db::name('print_cut')->where($where)->order('sort')->select();
        
        $type = Db::name('print_cut')->group('name')->order('id desc')->select();
        $angle = Db::name('print_cut')->group('angle')->order('id desc')->select();
        $login = Db::name('login')->where('user_role',2)->select();
        $intoPrinter = Db::name('print_style')->where('type',2)->select();
        
        $this->assign('login',$login);
        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->assign('angle',$angle);
        $this->assign('name_search',$nameSearch);
        $this->assign('angle_search',$angleSearch);
        $this->assign('date_search',$dateSearch);
        $this->assign('import_search',$importSearch);
        $this->assign('printer',$intoPrinter);
        return $this->fetch();
    }
    
    /**
     * 发送打印指令 切割方案
     */
    public function print_cut()
    {
        $cutId = input('print_id/a');//勾选的切割方案id        
        $printId = input('printer_id');  //打印机id             

        $print = new PrintYi();
        $print->setPrint($printId);
        $data = Db::name('print_cut')->alias('a')->field('a.name,a.size,b.unique_sn,b.color,b.uname,b.pname')
                ->join('order b','a.ordernum=b.unique_sn','left')
                ->whereIn('a.id', $cutId)
                ->order('sort')
                ->select();   
        if(count($data)==0){
            $this->_error('导入的订单号与系统中订单号不匹配');
        }
        $res=$print->executeCut($data);
        if($res){
            $this->_success('打印成功');
        }
        $this->_error('打印异常');
    }
    
    /**
     * 读取表格里的原始数据
     * @param $filePath 表格路径
     * @param $field 数组字段名
     * @return array
     */
    public function read($filePath,$field,$sheet=0)
    {
        if (!$filePath) {
            $errmsg = "请上传文件";
            $this->_error($errmsg);
        }

        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($filePath)) {
                    $errmsg = '未知的数据格式';
                    $this->error($errmsg);
                }
            }
        }

//        $PHPReader->setReadDataOnly(true); //忽略格式，只读取文本
        $PHPExcel = $PHPReader->load($filePath); //加载文件
        $currentSheet = $PHPExcel->getSheet($sheet);  //读取文件中的第一个工作表
        $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);

        $data = [];
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            $row = [];
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $row[$field[$currentColumn]] = is_null($val) ? '' : $val;
            }

            $data[] = $row;
        }
        unset($currentSheet);
        unset($PHPReader);
        unset($PHPExcel);
        return $data;
    }
    

}
