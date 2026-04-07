<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{DictDataServices};

/**
 * 字典数据控制器
 */
class DictData extends Base
{

    /**
     * 字典数据列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(DictDataServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			return $this->fetch();	
		}
    }

    /**
     * 添加字典数据
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = $this->validate($data, 'DictData');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(DictDataServices::add($data));
        }else{
			$this->assign('type_id', $this->request->param('type_id/d'));
			return $this->fetch();
		}

    }

    /**
     * 编辑字典数据
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'DictData');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(DictDataServices::edit($data));
        }
        $model = DictDataServices::detail($this->request->param('id'));
        $this->assign('model', $model);
        return $this->fetch();
    }

    /**
     * 删除字典数据
     */
    public function del()
    {
		return $this->getJson(DictDataServices::del($this->request->only(['ids'])));
    }
}
