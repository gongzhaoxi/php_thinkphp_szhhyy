<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{WorkerGroupServices,ProcessServices,WorkerServices};

/**
 * 班组控制器
 */
class WorkerGroup extends Base
{

    /**
     * 班组列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(WorkerGroupServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			return $this->fetch();	
		}
    }

    /**
     * 添加班组
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = $this->validate($data, 'WorkerGroup');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(WorkerGroupServices::add($data));
        }else{
			$this->assign('process', ProcessServices::all());
			$this->assign('worker', WorkerServices::all());
			return $this->fetch();
		}

    }

    /**
     * 编辑班组
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'WorkerGroup');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(WorkerGroupServices::edit($data));
        }
        $model = WorkerGroupServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('process', ProcessServices::all());
		$this->assign('worker', WorkerServices::all());
        return $this->fetch();
    }

    /**
     * 删除班组
     */
    public function del()
    {
		return $this->getJson(WorkerGroupServices::del($this->request->only(['ids'])));
    }
}
