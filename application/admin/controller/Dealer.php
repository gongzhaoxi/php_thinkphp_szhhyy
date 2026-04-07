<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use excel\Excel;
use app\model\{DictData};
/**
 * 经销商控制器
 */
class Dealer extends Base
{
    /**
     * 列表
     */
    public function index()
    {
        
        $keyword = input('keyword');
        $province = input('province');
        $city = input('city');
        $area = input('area');
        $sort = input('sort');
        if($sort !='' && !in_array($sort, ['have_pay asc','have_pay desc','no_pay asc','no_pay desc'])){
            return;
        }
        
        $where = "1=1";
        if($province != ''){
            $where .= " and a.province='$province'";
        }
        if($city != ''){
            $where .= " and a.city='$city'";
        }
        if($area != ''){
            $where .= " and a.area='$area'";
        }
        if($keyword != ''){
            $where .= " and (a.name like '%$keyword%' or a.contact like '%$keyword%' or a.sales_name like '%$keyword%' or a.address like '%$keyword%')";
        }
        
        $time = time();
        $day = 24*3600;
        $list = Db::name('dealer')->alias('a')->field("a.*,sum(b.have_pay) as have_pay,sum(b.total_price) as total_price,sum(b.finance_rebate_price) as rebate,($time-a.order_time)/$day as day")
                            ->join('order b','a.id=b.dealer_id','left')
                            ->group('a.id')                            
                            ->where($where)
                            ->order('id desc')
                            ->paginate('',false,['query'=>input('get.')]);
        $array = $list->all();
        foreach ($array as $k => $v) {
            $res = Db::name('order')->field('COALESCE(sum(total_price-have_pay-finance_rebate_price),0) as no_pay')
                ->where("dealer_id={$v['id']}")->find();
            $array[$k]['no_pay'] = round($res['no_pay'],2);
        }
        $allcount =  Db::name('dealer')->alias('a')->where($where)->count();

        $this->assign('all_count',$allcount);
        $this->assign('list',$array);
        $this->assign('keyword',$keyword);
        $this->assign('sort',$sort);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
		public function indexa()
		{
		    
		    $keyword = input('keyword');
		    $province = input('province');
		    $city = input('city');
		    $area = input('area');
		    $sort = input('sort');
		    if($sort !='' && !in_array($sort, ['have_pay asc','have_pay desc','no_pay asc','no_pay desc'])){
		        return;
		    }
		    
		    $where = "1=1";
		    if($province != ''){
		        $where .= " and a.province='$province'";
		    }
		    if($city != ''){
		        $where .= " and a.city='$city'";
		    }
		    if($area != ''){
		        $where .= " and a.area='$area'";
		    }
		    if($keyword != ''){
		        $where .= " and (a.name like '%$keyword%' or a.contact like '%$keyword%' or a.sales_name like '%$keyword%' or a.address like '%$keyword%')";
		    }
		    
		    $time = time();
		    $day = 24*3600;
		    $list = Db::name('dealer')->alias('a')->field("a.*,sum(b.have_pay) as have_pay,sum(b.total_price) as total_price,sum(b.finance_rebate_price) as rebate,($time-a.order_time)/$day as day")
		                        ->join('order b','a.id=b.dealer_id','left')
		                        ->group('a.id')                            
		                        ->where($where)
		                        ->order('id desc')
		                        ->paginate('',false,['query'=>input('get.')]);
		    $array = $list->all();
		    foreach ($array as $k => $v) {
		        $res = Db::name('order')->field('COALESCE(sum(total_price-have_pay-finance_rebate_price),0) as no_pay')
		            ->where("dealer_id={$v['id']}")->find();
		        $array[$k]['no_pay'] = round($res['no_pay'],2);
		    }
		    $allcount =  Db::name('dealer')->alias('a')->where($where)->count();
		
		    $this->assign('all_count',$allcount);
		    $this->assign('list',$array);
		    $this->assign('keyword',$keyword);
		    $this->assign('sort',$sort);
		    $this->assign('page',$list->render());
		    return $this->fetch();
		}
    
    /**
     * 添加经销商
     */
    public function add()
    {
        if($this->request->isPost()){
            $data = input('post.');
            $data['add_time'] = time();
            $res = Db::name('dealer')->insert($data);
            if($res){
                $this->success('提交成功');
            }
            $this->error('提交失败,请重试');
            return;
        }
		$brand = DictData::where('type_id',2)->where('status',1)->order(['sort'=>'asc','id'=>'asc'])->select();
		$this->assign('brand',$brand);		
        return $this->fetch();
    }
    
    /**
     * 查询编码
     */
    public function findcode()
    {
        $area = input('area');
        $codeName = Db::name('dealer')->where('area',$area)->order('add_time desc')->find();
        if($codeName){
            $this->success('',$codeName);
        }
        $this->error('未找到此区域的编码');
    }
        
    
    /**
     * 编辑经销商
     */
    public function edit()
    {
        $id = input('id/d');
        if($this->request->isPost()){
            $data = input('post.');
            unset($data['id']);
            $res = Db::name('dealer')->where('id',$id)->update($data);
            if($res !== false){
                $this->success('提交成功');
            }
            $this->error('提交失败,请重试');
            return;
        }
        $res = Db::name('dealer')->where('id',$id)->find();
        
        $this->assign('res',$res);
        $this->assign('id',$id);
		$brand = DictData::where('type_id',2)->where('status',1)->order(['sort'=>'asc','id'=>'asc'])->select();
		$this->assign('brand',$brand);
        return $this->fetch();
    }
    
    /**
     * 导入经销商
     */
    public function importDealer()
    {        set_time_limit(0);
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $list = $excel->read2($filePath); 
        foreach ($list as $k=>$v){
            Db::name('dealer')->where('code',$v[8])->update(['sales_name'=>$v[9]]);
        }
        exit;
        //已经存在的经销商
        $dealer = Db::name('dealer')->select();
        $exist = [];
        foreach($dealer as $k => $v){
            $name = $v['code'].$v['name'].$v['contact'].$v['address'].$v['province'].$v['city'].$v['area'];
            $exist[$name] = $name;
        }
        
        if ($list) {
            unset($list[0]);  //去除表头
            $imagePath = config('upload') . "admin/" . date('Ymd') . '/';
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0777, true);
            }
//            $image = $excel->readImage($filePath, $imagePath); //图片数组
            $data = [];
            $i = 2;
            foreach ($list as $k => $v) {
                //如果有新增数据,数据表中不存在相同的数据才插入数据表
                $named = $v[8].$v[3].$v[4].$v[7].$v[0].$v[1].$v[2];                
                if (isset($v[0]) && $v[0] != '' && !array_key_exists($named, $exist)) {
//                    $spic = isset($image['G' . $i])?$image['G' . $i]:'';
//                    $dealerPic = 'admin/' . date('Ymd') . '/' . $spic;
//                    $dealerPic = $spic==''?'':$dealerPic;
                    $data[] = [
                        'province' => $v[0],'city' => $v[1],'area' => $v[2],
                        'name' => $v[3], 'pic' => '', 'contact' => $v[4],'back_contact' => $v[5], 'address' => $v[7],'code' => $v[8],
                        'sales_name' => $v[9]
                    ];
                    $i++;
                }          
            }
            
            $res = Db::name('dealer')->insertAll($data);
            $this->success('导入成功');
         
        }
        
    }
    
    /**
     * 经销商下单记录
     */
    public function orderList()
    {
        $dealerId = input('id/d');
        $name = input('keyword');
        $startTime = input('starttime');
        $endTime = input('endtime');
        
        $list = Db::name('order')->where('dealer_id',$dealerId)->order('id desc')->paginate();
        
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        $this->assign('name', $name);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('pay_type', config('pay_type'));
        return $this->fetch('finance/no_handle');
    }
    
}
