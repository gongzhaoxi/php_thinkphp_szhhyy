<?php

namespace app\index\controller;

use think\Db;
use Overtrue\Pinyin\Pinyin;
use excel\Excel;

/**
 * 玻璃计划控制器
 */
class Glassplan extends Super
{
    protected $field;//玻璃计划固定字段+自定义字段+状态
    protected $fieldText;//玻璃计划固定字段名称+自定义字段名称+状态
    public function initialize()
    {
        parent::initialize();
        $result = $this->check_status('glass_plan');
        if(!$result){
            $this->error('未开启此功能');
        }

        $headarr = ['批次','销售单号','生产单号','规格','订购数','回厂数','种类'];
        $field = ['batch','sales_number','product_no','specs','book_count','back_count','type'];
        $extendField = Db::name('glass_field')->select();
        foreach ($extendField as $k => $v) {
            $headarr[] = $v['value'];
            $field[] = $v['key'];
        }
        $headarr[] = "状态";
        $field[] = "status_text";
        $this->field = $field;
        $this->fieldText = $headarr;
        $this->assign('field_name',$headarr);        
        $this->assign('field',$field);
    }
    
    /**
     * 计划列表
     */
    public function index()
    {
        $search = input('get.');
        $where = "1=1";
        if($search['batch'] != ''){
            $where .= " and batch='{$search['batch']}'";
        }
        if($search['product_no'] != ''){
            $where .= " and product_no='{$search['product_no']}'";
        }
        if($search['addtime'] != ''){
            $time = strtotime($search['addtime']);
            $endtime = $time+(24*3600);
            $where .= " and addtime between $time and $endtime";
        }
        if($search['status'] != ''){
            if($search['status'] == 1){
                $where .= " and all_book > all_back";
            }else{
                $where .= " and all_book < all_back";
            }
        }
        $glassplan = model('GlassPlan');
        $list = $glassplan->index($where);
        
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('search',$search);
        return $this->fetch();
    }
    
    /**
     * 计划详细
     */
    public function detail()
    {
        $batch = input('batch');
        $search = input('get.');        
        
        $where = "batch='$batch'";
        if($search['sales_number'] != ''){
            $where .= " and sales_number='{$search['sales_number']}'";
        }
        if($search['product_no'] != ''){
            $where .= " and product_no='{$search['product_no']}'";
        }
        if($search['status'] != ''){
            if($search['status'] == 1){
                $where .= " and back_count=0";
            }elseif($search['status'] == 2){
                $where .= " and back_count!=0 and back_count<book_count";
            }elseif($search['status'] == 3){
                $where .= " and back_count=book_count";
            }
           
        }
        
        
        $glassplan = model('GlassPlan');
        //头部数据
        $title = $glassplan->field('batch,sum(book_count) as bcount,sum(back_count) as back_count,count(id) as count,addtime')
                ->where('batch',$batch)->find();
        //按生产单号汇总数据
        $button = $glassplan->field('product_no,sum(book_count) as book_count,sum(back_count) as back_count')->where('batch',$batch)
                ->group('product_no')->order('back_count desc')
                ->select();
        //筛选下拉框数据
        $filterSales = $glassplan->where('batch',$batch)->group('sales_number')->column('sales_number');        
        $filterProduct = $glassplan->where('batch',$batch)->group('product_no')->column('product_no');   
        //列表数据
        $list = $glassplan->where($where)->select()->append(['status_text','status_value']);
        $data = $glassplan->mergeField($list);
        //排序,已完成放最上面
        $sort = [];
        foreach ($data as $k => $v) {
            $sort[] = $v['status_value'];
        }
        array_multisort($sort,SORT_ASC,$data);
        
        $this->assign('data',$data);
        $this->assign('title',$title);
        $this->assign('button',$button);
        $this->assign('filter_sales',$filterSales);
        $this->assign('filter_product',$filterProduct);
        $this->assign('search',$search);
//        $this->assign('status_map',$statusMap);
        return $this->fetch();
    }

    /**
     * 计划详情，查看某条单所导入的计划
     */
    public function order_plan()
    {
        $orderNumber = input('number');
        $glassplan = model('GlassPlan');
        $list = $glassplan->where('product_no',$orderNumber)->select()->append(['status_text','status_value']);
        $data = $glassplan->mergeField($list);
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 导出批次数据
     */
    public function export()
    {
        $batch = input('batch');
        $glassplan = model('GlassPlan');
        $list = $glassplan->where('batch',$batch)->select()->append(['status_text']);             
        $data = $glassplan->mergeField($list); 
        
        $excel = new Excel();
        $excel->export("$batch", $this->fieldText, $data, $this->field);
    }
    
    /**
     * 导入计划页面
     */
    public function import_plan()
    {
        return $this->fetch();
    }
    
    /**
     * 导入计划
     * @return type
     */
    public function save_plan()
    {
        $file = $this->request->file('file');
        $originalName = $file->getInfo('name');
        $extension = strtolower(pathinfo($originalName)['extension']);
        $arrAllowedExtensions = ['xls','xlsx'];
        if (!in_array($extension, $arrAllowedExtensions) || $extension == 'php') {
            $this->_error('非法文件类型');
        }
        $info = $file->move('./uploads');
        if($info){
            $excel = new Excel();
            $data = $excel->read2('./uploads/'.$info->getSaveName(), $this->field);
            unset($data[0]);//去除表头
            if(!is_array($data) || count($data) == 0){
                $this->_error('数据不能为空');
            }
            $exist = Db::name('glass_plan')->select();
            //
            $checkExist = [];
            foreach ($exist as $k => $v) {
                $key = $v['batch'].$v['sales_number'].$v['product_no'].$v['specs'].$v['book_count'].$v['type'];
                $checkExist[$key] = $v['id'];
            }
            
            //整理数据
            $field = ['batch','sales_number','product_no','specs','book_count','back_count','type'];//固定字段
            $insert = [];//要插入的数据
            $update = [];//要更新回厂数的数据
            $updateGxline = [];//匹配订单，更新工艺线
            foreach ($data as $k => $v) {
                $temp = [];               
                foreach ($v as $k2 => $v2) {
                    if(!in_array($k2, $field)){
                        $temp[$k2] = $v2;//自定义字段单独存起来
                    }
                }
                //要更新的数据和插入的数据分开
                $keys = $v['batch'].$v['sales_number'].$v['product_no'].$v['specs'].$v['book_count'].$v['type'];
                if(array_key_exists($keys, $checkExist)){
                    $update[] = ['id'=>$checkExist[$keys],'back_count'=>$v['back_count']];
                }else{
                    $insert[] = ['batch'=>$v['batch'],'sales_number'=>$v['sales_number'],'product_no'=>$v['product_no'],'specs'=>$v['specs'],
                        'book_count'=>$v['book_count'],'back_count'=>$v['back_count'],'type'=>$v['type'],'extension'=>$temp,'addtime'=> time()
                    ];
                    if($v['type'] == '扇' || $v['type'] == '固') {
                        $lineId = $v['type'] == '扇'?2:1;
                        $updateGxline[] = ['unique_sn' => $v['product_no'], 'line_id' =>$lineId];
                    }
                }
                
                
            }
            if(count($update) > 0){
                $sql = "update bg_glass_plan set `back_count` = case `id` ";
                foreach ($update as $k => $v) {
                    $sql .= " when {$v['id']} then {$v['back_count']}";
                }
                $id = implode(',',array_column($update, 'id'));
                $sql .= " end where id in ($id)";
                $ures = Db::execute($sql);
            }
            $res = Db::name('glass_plan')->insertAll($insert);
            if($res !== false || $ures !== false){
                @unlink('../public/uploads/'.date('Ymd').'/'.$info->getFilename());
//                $this->writeLine($updateGxline);
                $this->_success('导入成功');
            }
            $this->_error('导入失败');
            return;
        }
        $this->_error('文件上传失败');
    }

    /**
     * 写入订单工艺线
     */
    public function writeLine($updateGxline)
    {
        $uniquesn = array_column($updateGxline,'unique_sn');
        $orders = Db::name('order')->whereIn('unique_sn',$uniquesn)->select();
        //如果同一个单号，有多次导入，则将工艺线合并
        $line = [];
        foreach ($updateGxline as $k => $v){
            $line[$v['unique_sn']][] = $v['line_id'];
        }

//        $orderLine = [];
        //将玻璃计划的工艺线加入订单的gxline_id里
        foreach ($orders as $k => $v) {
            $gxline = explode(',',$v['gxline_id']);
            $gxline = array_merge($gxline,$line[$v['unique_sn']]);
            $orders[$v['unique_sn']] = array_unique($gxline);
        }
        //更新
        foreach ($orders as $k => $v) {
            Db::name('order')->where('unique_sn',$k)->update(['gxline_id'=>implode(',',$v)]);
        }
    }


    /**
     * 删除计划
     */
    public function del()
    {
        $batch = input('batch');
        $plan = Db::name('glass_plan')->where('batch',$batch)->select();//包含的订单编号
        $uniquesnGxline = [];//订单编号所含有的 玻璃计划工艺线
        foreach ($plan as $k => $v) {
            if($v['type'] == '扇' || $v['type'] == '固') {
                $lineId = $v['type'] == '扇' ? 2 : 1;
                $uniquesnGxline[$v['product_no']][] = $lineId;
            }
        }

        $uniquesn = array_column($plan,'product_no');
        $order = Db::name('order')->whereIn('unique_sn',$uniquesn)->select();
        //删除对应的玻璃计划工艺线
        $orderGxline = [];
        foreach ($order as $k => $v) {
            $gxline = explode(',',$v['gxline_id']);
            foreach ($gxline as $k2 => $v2) {
                if(in_array($v2,$uniquesnGxline[$v['unique_sn']])){
                    unset($gxline[$k2]);
                }
            }
            $orderGxline[$v['id']] = implode(',',$gxline);
        }

        try{
            foreach ($orderGxline as $k => $v) {
                Db::name('order')->where('id',$k)->update(['gxline_id'=>$v]);
            }
            $res = Db::name('glass_plan')->where('batch',$batch)->delete();
            $this->_success('删除成功');
        }catch (\Exception $e){
            $this->error('删除失败');
        }

    }
    
    /**
     * 计划下载模板
     */
    public function download()
    {
        $excel = new Excel();
        $this->fieldText['6'] = "种类(填扇或固)";

        $fileText = $this->fieldText;
        array_pop($fileText);//删除最后一个状态文字
        $excel->export('玻璃计划模板', $fileText,[], $this->field);
    }
    
    /**
     * 设置计划字段
     */
    public function set_plan_field()
    {
        if(request()->ispost()){
            $data = input('post.data/a');            
            Db::name('glass_field')->delete(true);//先删除在插入
            //转成拼音，插入数据
            $pinyin = new Pinyin();
            $insert = [];
            foreach ($data as $k => $v) {
                $array = $pinyin->convert($v);
                $key = implode('', $array);
                $insert[] = ['key'=>$key,'value'=>$v];
            }
            $res = Db::name('glass_field')->insertAll($insert);
            if($res){
                $this->_success('设置成功');
            }
            $this->_error('设置失败,请重试');
            return;
        }
        $field = Db::name('glass_field')->select();
        $this->assign('field',$field);
        return $this->fetch();
    }
}
