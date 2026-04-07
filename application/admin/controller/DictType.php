<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{DictTypeServices,ProcessServices,WorkerServices};

/**
 * 班组控制器
 */
class DictType extends Base
{

    /**
     * 班组列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(DictTypeServices::list($this->request->param(),$this->request->param('limit'))) ;
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
            $validate = $this->validate($data, 'DictType');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(DictTypeServices::add($data));
        }else{
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
            $validate = $this->validate($data, 'DictType');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(DictTypeServices::edit($data));
        }
        $model = DictTypeServices::detail($this->request->param('id'));
        $this->assign('model', $model);
        return $this->fetch();
    }

    /**
     * 删除班组
     */
    public function del()
    {
		return $this->getJson(DictTypeServices::del($this->request->only(['ids'])));
    }
}
