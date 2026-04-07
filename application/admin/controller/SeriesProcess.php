<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\services\{SeriesProcessServices,ProcessServices};

/**
 * 系列工序控制器
 */
class SeriesProcess extends Base
{

    /**
     * 系列工序列表
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(SeriesProcessServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			$this->assign('process', ProcessServices::all());
			$this->assign('series', SeriesProcessServices::series());
			return $this->fetch();	
		}
    }

    /**
     * 添加系列工序
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = $this->validate($data, 'SeriesProcess');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(SeriesProcessServices::add($data));
        }else{
			$this->assign('process', ProcessServices::all());
			$this->assign('series', SeriesProcessServices::series());
			$this->assign('series_id', $this->request->param('series_id/d'));
			return $this->fetch();
		}

    }

    /**
     * 编辑系列工序
     */
    public function edit()
    {
        if ($this->request->isPost()) {
			$data = $this->request->param();
            $validate = $this->validate($data, 'SeriesProcess');
            if ($validate !== true) {
                $this->error($validate);
            }
			return $this->getJson(SeriesProcessServices::edit($data));
        }
        $model = SeriesProcessServices::detail($this->request->param('id'));
        $this->assign('model', $model);
		$this->assign('process', ProcessServices::all());
		$this->assign('series', SeriesProcessServices::series());
        return $this->fetch();
    }

    /**
     * 删除系列工序
     */
    public function del()
    {
		return $this->getJson(SeriesProcessServices::del($this->request->only(['ids'])));
    }
}
