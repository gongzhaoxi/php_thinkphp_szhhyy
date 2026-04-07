<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\ProcessServices;

/**
 * 工序控制器
 */
class Process extends Base
{

    /**
     * 工序列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(ProcessServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			return $this->fetch();	
		}
    }

    /**
     * 添加工序
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = $this->validate($data, 'process');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(ProcessServices::add($data));
        }else{
			return $this->fetch();
		}

    }

    /**
     * 编辑工序
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'process');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(ProcessServices::edit($data));
        }
        $model = ProcessServices::detail($this->request->param('id'));
        $this->assign('model', $model);
        return $this->fetch();
    }

    /**
     * 删除工序
     */
    public function del()
    {
		return $this->getJson(ProcessServices::del($this->request->only(['ids'])));
    }
}
