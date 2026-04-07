<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{ProductionServices,ProcessServices,WorkerServices};

use excel\Excel;
/**
 * 订单排产控制器
 */
class Production extends Base
{

    /**
     * 订单排产列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(ProductionServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			$this->assign('production_status', $this->request->param('production_status/d',0));
			return $this->fetch();	
		}
    }

    /**
     * 创建订单排产工序
     */
    public function add()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'Production');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(ProductionServices::edit($data));
        }
        $model = ProductionServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('process', ProcessServices::all());
        return $this->fetch();
    }


    /**
     * 编辑订单排产工序
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'Production');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(ProductionServices::edit($data));
        }
        $model = ProductionServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('process', ProcessServices::all());
        return $this->fetch();
    }

    /**
     * 撤销订单排产
     */
    public function del()
    {
		return $this->getJson(ProductionServices::del($this->request->only(['ids'])));
    }
	
	
    /**
     * 订单排产工序
     */
    public function process()
    {
		if($this->request->isAjax()) {
			return $this->getJson(ProductionServices::process($this->request->param(),$this->request->param('limit'))) ;
        }else{
			$this->assign('order_id', $this->request->param('order_id/d',0));
			return $this->fetch();	
		}
    }	
	

    /**
     * 开始报工
     */
    public function start()
    {
		return $this->getJson(ProductionServices::start($this->request->param('id/d'),cookie('login_name')));
    }	
	
    /**
     * 结束报工
     */
    public function end()
    {
		return $this->getJson(ProductionServices::end($this->request->param('id/d'),cookie('login_name')));
    }	
	
	/**
     * 修改报工
     */
    public function report()
    {
        if ($this->request->isPost()) {
			$data = $this->request->only(['id','start_date','end_date','start_worker','end_worker']);
			return $this->getJson(ProductionServices::report($data));
        }
        $model = ProductionServices::processDetail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('worker', WorkerServices::all());
        return $this->fetch();
    }
	
	
	/**
     * 生产量统计
     */
    public function stat()
    {
		if($this->request->isAjax()) {
			return $this->getJson(ProductionServices::stat($this->request->param(),$this->request->param('limit'))) ;
        }else{
			$this->assign('production_status', $this->request->param('production_status/d',2));
			return $this->fetch();	
		}
    }
	
	
	/**
     * 导出生产量统计
     */
    public function exportStat()
    {
		$data = ProductionServices::stat($this->request->param(),10000);
		$excel = new Excel();
		$headArr = [
                '班组','工序','报开始人姓名','报结束人姓名','报工开始时间','报工结束时间',
				'订单总积','销售订单号','订单类型','客户名称','送货地址','排产日期','入库日期',
                '系列名称','材质','颜色','型号','产品面积','报价面积','产品数量','窗型结构','逃生窗','纱网'
        ];
		$field = [
                'group_name','process_name','start_worker','end_worker','start_date','end_date',
				'order_area','number','type','dealer','send_address','production_date','store_date',
                'name','material','color_name','flower','product_area','price_area','count','window_type_a','escape_type_a','yarn_color'
		];	

		$excel->export('生产量统计',$headArr,$data['data'],$field,"恒辉生产量统计表");
    }	
	
}
