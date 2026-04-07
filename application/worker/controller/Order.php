<?php
namespace app\worker\controller;

use think\Db;
use app\worker\services\{OrderServices};


class Order extends Base
{
	
    public function index()
    {
		return $this->getJson(OrderServices::index($this->worker));
    }	
	
    public function detail()
    {
		return $this->getJson(OrderServices::detail($this->worker,$this->request->get('number')));
    }
	
    public function start()
    {
		return $this->getJson(OrderServices::start($this->worker,$this->request->get('number')));
    }	
	
    public function end()
    {
		return $this->getJson(OrderServices::end($this->worker,$this->request->get('number')));
    }
	
	public function feedback()
    {
		return $this->getJson(OrderServices::feedback($this->worker,$this->request->post('number'),$this->request->only(['order_process_id','type','remark'])));
    }
}
