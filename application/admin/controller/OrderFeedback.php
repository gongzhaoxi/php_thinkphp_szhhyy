<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{OrderFeedbackServices};
use app\model\{DictData,OrderProcess};
/**
 * 车间异常控制器
 */
class OrderFeedback extends Base
{

    /**
     * 车间异常列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(OrderFeedbackServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			$feedback_type = DictData::where('type_id',1)->where('status',1)->order(['sort'=>'asc','id'=>'asc'])->select();
			$this->assign('feedback_type', $feedback_type);
			return $this->fetch();	
		}
    }

    /**
     * 编辑车间异常
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'OrderFeedback');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(OrderFeedbackServices::edit($data));
        }
        $model = OrderFeedbackServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('process',OrderProcess::where('order_id',$model['order_id'])->order(['sort'=>'asc'])->select());
		$this->assign('feedback_type', DictData::where('type_id',1)->where('status',1)->order(['sort'=>'asc','id'=>'asc'])->select());
        return $this->fetch();
    }

    /**
     * 删除车间异常
     */
    public function del()
    {
		return $this->getJson(OrderFeedbackServices::del($this->request->only(['ids'])));
    }
	
	/**
     * 处理车间异常
     */
    public function handel()
    {
		return $this->getJson(OrderFeedbackServices::handel($this->request->only(['ids'])));
    }
	
	
}
