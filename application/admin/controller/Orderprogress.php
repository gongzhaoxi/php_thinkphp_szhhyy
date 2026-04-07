<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use app\model\Order;

class Orderprogress extends Controller
{
    public function orderdetail()
    {
        
        $ordernum = input("ordernum/s");
        $ordertime = input("ordertime/s");
        $uname = input("uname/s");
        $phone = input('phone/s');
        $isMobile = input('is_mobile');
		$state = input('status/s');
        $where = '1=1 ';
        if (!empty($ordertime)) {
            $start = strtotime($ordertime);
            $end = strtotime($ordertime . ' 23:59:59');
            $where .= " and time between $start and $end";
        }
        !empty($ordernum) ? $where .= " and number like '%$ordernum%'" : $where;
        !empty($uname) ? $where .= " and Cname like '%$uname%'" : $where;
        !empty($phone) ? $where .= " and tel like '%$phone%'" : $where;
		if(!empty($state)){
			if($state=='未开始'){
				$where .= " and state like '%$state%'";
			}else{
				$where .= " and state not like '%$state%'";
			}
		}
        $condition = array();
        $condition['ordernum'] = $ordernum;
        $condition['ordertime'] = $ordertime;
        $condition['uname'] = $uname;
        $condition['phone'] = $phone;
		$condition['state'] = $state;
				
        $this->assign('condition', $condition);

        $result = Db::connect('database.db2')->table('henghuiorder')->where($where)
            ->order('id desc')->paginate(20, false, ['query' => request()->param()]);
		
		// $sql = Db::connect('database.db2')->table('henghuiorder')->where($where)->getLastSql();
		//  halt($sql);
		
        if ($result) {
            $list = $result->all();
            foreach ($list as $key => $val) {
                $state_arr = array();
                $wp = explode('|', $val['wp']);
                $state = explode('|', $val['state']);
                foreach ($wp as $k2 => $v2) {
                    if($state[$k2] != '未开始'){
                        $temp = explode('-',$state[$k2]);
                        if(is_array($temp) && count($temp) > 0) {
                            $temp1 = isset($temp[1])?$temp[1]:'';
                            $temp0 = isset($temp[0])?$temp[0]:'';
                            $time = $temp0 . '<br/>' . $temp1;
                        }else{
                            $time = $state[$k2];
                        }
                    }else{
                        $time = $state[$k2];
                    }
                    $in_arr = array('name' => $v2, 'time' => $time);
                    $state_arr[] = $in_arr;
                }
                $list[$key]['status'] = $state_arr;
            }

            $page = $result->render();
            $total = $result->total();
            $this->assign("page", $page);
            $this->assign("total", $total);
            $this->assign("list", $list);
        }
        Db::connect('database.db2')->close();
        $template = $isMobile==1?'mobile_orderdetail':'orderdetail';
        return $this->fetch($template);
		
    }
	
	public function orderdetailnew()
    {
        
        $ordernum = input("ordernum/s");
        $ordertime = input("ordertime/s");
        $uname = input("uname/s");
        $phone = input('phone/s');
        $isMobile = input('is_mobile');
		$state = input('state/s');
        $where = '1=1 ';
        if (!empty($ordertime)) {
            $start = strtotime($ordertime);
            $end = strtotime($ordertime . ' 23:59:59');
            $where .= " and addtime between $start and $end";
        }
        !empty($ordernum) ? $where .= " and number like '%$ordernum%'" : $where;
        !empty($uname) ? $where .= " and dealer like '%$uname%'" : $where;
        !empty($phone) ? $where .= " and phone like '%$phone%'" : $where;
		if(!empty($state)){
			if($state=='1'){
				$where .= " and status <> 7 ";
			}else{
				$where .= " and status = 7 ";
			}
		}
        $condition = array();
        $condition['ordernum'] = $ordernum;
        $condition['ordertime'] = $ordertime;
        $condition['uname'] = $uname;
        $condition['phone'] = $phone;
		$condition['state'] = $state;
				
        $this->assign('condition', $condition);

        //$result = Db::connect('database.db2')->table('henghuiorder')->where($where)->order('id desc')->paginate(20, false, ['query' => request()->param()]);
			
		$result = Order::alias('a')->with(['process'])->field('a.*')
			->where($where)
			->order('a.id desc')
			->paginate();	
		
		$page = $result->render();
		$total = $result->total();
		$list = $result->all();
		$this->assign("page", $page);
		$this->assign("total", $total);
		$this->assign("list", $list);
		$this->assign('status_text', config('order_status'));	
		
		$template = $isMobile==1?'mobile_orderdetailnew':'orderdetailnew';
        return $this->fetch($template);

		
    }

}
