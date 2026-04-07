<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{WorkerServices,WorkerGroupServices};

/**
 * 员工控制器
 */
class Worker extends Base
{

    /**
     * 员工列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(WorkerServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			return $this->fetch();	
		}
    }

    /**
     * 添加员工
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = $this->validate($data, 'Worker');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(WorkerServices::add($data));
        }else{
			$this->assign('group', WorkerGroupServices::all());
			return $this->fetch();
		}

    }

    /**
     * 编辑员工
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'Worker');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(WorkerServices::edit($data));
        }
        $model = WorkerServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('group', WorkerGroupServices::all());
        return $this->fetch();
    }

    /**
     * 删除员工
     */
    public function del()
    {
		return $this->getJson(WorkerServices::del($this->request->only(['ids'])));
    }
}
