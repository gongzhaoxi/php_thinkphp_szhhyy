<?php

namespace app\worker\controller;

use think\Db;
use app\worker\services\{OrderFeedbackServices};

class Board extends Base
{

	protected $noLogin = ['board/index'];

    /**
     * 异常上报汇总屏
     */
    public function index()
    {
		if($this->request->isAjax()) {
			return $this->getJson(OrderFeedbackServices::list($this->request->param(),$this->request->param('limit'))) ;
        }else{
			return $this->fetch();	
		}
    }

}
