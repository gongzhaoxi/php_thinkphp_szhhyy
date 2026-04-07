<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Validate;
use think\Loader;
use tree\Tree;
use excel\Excel;

/**
 * 物料清单控制器
 */
class Bom extends Base
{

    /**
     * 铝型材
     */
    public function aluminum()
    {

        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        }
        $list = Db::name('bom_aluminum')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加,编辑铝型材
     */
    public function aluminumAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomaluminum");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_aluminum')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_aluminum')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('bom_aluminum')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    /**
     * 删除铝型材
     */
    public function aluminumDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_aluminum')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_aluminum')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 花型
     */
    public function flower()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        }
        $list = Db::name('bom_flower')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加,编辑花型
     */
    public function flowerAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomflower");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_flower')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_flower')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('bom_flower')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    /**
     * 删除花件
     */
    public function flowerDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_flower')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_flower')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 图片上传
     */
    public function upload()
    {
        $uploadSetting = get_upload_setting();
        $arrFileTypes = [
            'image' => ['title' => 'Image files', 'extensions' => $uploadSetting['file_types']['image']['extensions']],
            'video' => ['title' => 'Video files', 'extensions' => $uploadSetting['file_types']['video']['extensions']],
            'audio' => ['title' => 'Audio files', 'extensions' => $uploadSetting['file_types']['audio']['extensions']],
            'file' => ['title' => 'Custom files', 'extensions' => $uploadSetting['file_types']['file']['extensions']]
        ];

        $file = $this->request->file('file');

        $originalName = $file->getInfo('name');
        $arrAllowedExtensions = explode(',', $arrFileTypes['image']['extensions']);
        $strFileExtension = strtolower(get_file_extension($originalName));
        if (!in_array($strFileExtension, $arrAllowedExtensions) || $strFileExtension == 'php') {
            $this->error("非法文件类型！");
        }
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'admin' . DS);
        if ($info) {
            $date = date('Ymd');
            $pic = "admin/$date/" . $info->getFilename();
            $this->success('上传成功', $pic);
        } else {
            $this->error($file->getError());
        }
    }

    /**
     * 五件
     */
    public function five()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        }
        $list = Db::name('bom_five')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加,编辑五件
     */
    public function fiveAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomfive");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_five')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_five')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('bom_five')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    /**
     * 删除五件
     */
    public function fiveDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_five')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_five')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 把手位
     */
    public function hands()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        }
        $list = Db::name('bom_hands')->where($where)->order('sort asc')->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 更新把手位排序
     */
    public function handsSort()
    {
        $id = input('id/a');
        $sort = input('sort/a');

        foreach ($id as $k => $v) {
            $res = Db::name('bom_hands')->where('id',$v)->update(['sort'=>$sort[$k]]);
        }
        if($res !== false){
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * 添加,编辑把手位
     */
    public function handsAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomhands");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_hands')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_hands')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('bom_hands')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    /**
     * 删除把手位
     */
    public function handsDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_hands')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_hands')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 纱网
     */
    public function yarn()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        }
        $list = Db::name('bom_yarn')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加纱网
     */
    public function yarnAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomyarn");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_yarn')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_yarn')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('bom_yarn')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    /**
     * 删除把手位
     */
    public function yarnDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_yarn')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_yarn')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 颜色
     */
    public function color()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (name like '%" . $keyword . "%' or code like '%" . $keyword . "%')";
        } 
        $result = Db::name('bom_color')->where($where)->order('id asc')->select();
        $tree = new Tree();
        $tree->icon = ['', '', ''];  //icon
        $tree->nbsp = '';  //空格偏移量
        foreach ($result as $key => $value) {
            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style'] = $value['parent_id'] == 0 ? '' : 'display:none;';
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                    <td>\$name</td>
                    <td>\$code</td>
                    <td>\$attr</td>
                    
                    <td><img src='/upload/\$pic' style='width:50px;height:60px;'/></td>     
                    <td>\$unit</td>
                    <td>\$price</td>
                    <td class='td-manage'>
                        <a title='编辑' onclick=xadmin.open('纱网','" . url('colorAdd') . "?id=\$id','600','550') href='javascript:;'>
                            <i class='layui-icon'>&#xe642;</i>
                        </a>
                        <a title='删除' onclick='member_del(this,\$id)' href='javascript:;'>
                            <i class='layui-icon'>&#xe640;</i>
                        </a>
                    </td>
                </tr>";
        $treeList = $tree->getTree(0, $str);
        $this->assign('treeList', $treeList);
        return $this->fetch();
    }

    /**
     * 添加,编辑颜色
     */
    public function colorAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Bomcolor");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('bom_color')->where('id', $id)->update($data);
            } else {
                $res = Db::name('bom_color')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        
        //全部系列
        $tree = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result = Db::name('bom_color')->select();
        $array = [];
        //编辑时上级选中
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign('color', $selectCategory);
        
        $res = Db::name('bom_color')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));

        //所有颜色
//        $color = Db::name('bom_color')->select();
//        $this->assign('color', $color);
        return $this->fetch();
    }

    /**
     * 删除颜色
     */
    public function colorDel()
    {
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('bom_color')->whereIn('id', $ids)->delete();
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('bom_color')->where('id', $id)->delete();
        }
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    
    /**
     * 铝型材导出
     */
    public function exportAluminum()
    {
        $excel = new Excel();
        $list = Db::name('bom_aluminum')->field('code,name,big,small,unit,price')->select();
        $header = ['物料编号','物料名称','大面尺寸','小面尺寸','单位','单价'];
        $res = $excel->export('aluminum', $header,$list);
        
    }
    
    /**
     * 铝型材导入
     */
    public function importAluminum()
    {
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $read = $excel->read2($filePath); 
        
        $list = Db::name('bom_aluminum')->field('code,name,big,small,unit,price')->select();
        $checkExist = [];
        foreach($list as $k => $v){
            $name = $v['code'].$v['name'].$v['big'].$v['small'].$v['unit'].floatval($v['price']);
            $checkExist[$name] = $name;
        }
     
        if($read){
            unset($read[0]);
            $insert = [];
            foreach ($read as $k => $v) {
                $name = $v[0].$v[1].$v[2].$v[3].$v[4].floatval($v[5]);                
                if(!array_key_exists($name, $checkExist)){
                    $insert[] = ['code'=>$v[0],'name'=>$v[1],'big'=>$v[2],'small'=>$v[3],'unit'=>$v[4],'price'=>$v[5]];
                }
            }
            
            $res = Db::name('bom_aluminum')->insertAll($insert);
            if($res){
                $this->success('导入成功');
            }
            $this->error('导入失败或数据已经存在');
        }
        
    }
    
    /**
     * 纱网导出
     */
    public function exportYarn()
    {
        $excel = new Excel();
        $list = Db::name('bom_yarn')->field('code,name,thickness,unit,price')->select();
        $header = ['物料编号','物料名称','厚度','单位','单价'];
        $res = $excel->export('yarn', $header,$list);
        
    }
    
    /**
     * 纱网导入
     */
    public function importYarn()
    {
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $read = $excel->read2($filePath); 
        
        $list = Db::name('bom_yarn')->field('code,name,thickness,unit,price')->select();
        $checkExist = [];
        foreach($list as $k => $v){
            $name = $v['code'].$v['name'].$v['thickness'].$v['unit'].floatval($v['price']);
            $checkExist[$name] = $name;
        }
     
        if($read){
            unset($read[0]);
            $insert = [];
            foreach ($read as $k => $v) {
                $name = $v[0].$v[1].$v[2].$v[3].floatval($v[4]);                
                if(!array_key_exists($name, $checkExist)){
                    $insert[] = ['code'=>$v[0],'name'=>$v[1],'thickness'=>$v[2],'unit'=>$v[3],'price'=>$v[4]];
                }
            }
            
            $res = Db::name('bom_yarn')->insertAll($insert);
            if($res){
                $this->success('导入成功');
            }
            $this->error('导入失败或数据已经存在');
        }
        
    }
    
    /**
     * 五件导出
     */
    public function exportFive()
    {
        $excel = new Excel();
        $list = Db::name('bom_five')->field('code,name,unit,price')->select();
        $header = ['物料编号','物料名称','单位','单价'];
        $res = $excel->export('five', $header,$list);
        
    }
    
    
    /**
     * 五件导入
     */
    public function importFive()
    {
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $read = $excel->read2($filePath); 
        
        $list = Db::name('bom_five')->field('code,name,unit,price')->select();
        $checkExist = [];
        foreach($list as $k => $v){
            $name = $v['code'].$v['name'].$v['unit'].floatval($v['price']);
            $checkExist[$name] = $name;
        }
     
        if($read){
            unset($read[0]);
            $insert = [];
            foreach ($read as $k => $v) {
                $name = $v[0].$v[1].$v[2].floatval($v[3]);                
                if(!array_key_exists($name, $checkExist)){
                    $insert[] = ['code'=>$v[0],'name'=>$v[1],'unit'=>$v[2],'price'=>$v[3]];
                }
            }
            
            $res = Db::name('bom_five')->insertAll($insert);
            if($res){
                $this->success('导入成功');
            }
            $this->error('导入失败或数据已经存在');
        }
        
    }
    
    /**
     * 把手位导出
     */
    public function exportHands()
    {
        $excel = new Excel();
        $list = Db::name('bom_hands')->field('code,name,width,height,unit,price')->select();
        $header = ['物料编号','物料名称','宽','高','单位','单价'];
        $res = $excel->export('hands', $header,$list);
        
    }
    
    /**
     * 把手位导入
     */
    public function importHands()
    {
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $read = $excel->read2($filePath); 
        
        $list = Db::name('bom_hands')->field('code,name,width,height,unit,price')->select();
        $checkExist = [];
        foreach($list as $k => $v){
            $name = $v['code'].$v['name'].$v['width'].$v['height'].$v['unit'].floatval($v['price']);
            $checkExist[$name] = $name;
        }
     
        if($read){
            unset($read[0]);
            $insert = [];
            foreach ($read as $k => $v) {
                $name = $v[0].$v[1].$v[2].$v[3].$v[4].floatval($v[5]);                
                if(!array_key_exists($name, $checkExist)){
                    $insert[] = ['code'=>$v[0],'name'=>$v[1],'width'=>$v[2],'height'=>$v[3],'unit'=>$v[4],'price'=>$v[5]];
                }
            }
            
            $res = Db::name('bom_hands')->insertAll($insert);
            if($res){
                $this->success('导入成功');
            }
            $this->error('导入失败或数据已经存在');
        }
        
    }
    
    /**
     * 花件导出
     */
    public function exportFlower()
    {
        $excel = new Excel();
        $list = Db::name('bom_flower')->field('code,name,min_height,min_width,max_height,max_width,'
                . 'is_cut,cut_min_height,cut_min_width,cut_max_height,cut_max_width,unit,price')
                ->select();
        $header = ['物料编号','物料名称','最小高','最小宽','最大高','最大宽','是否可切:0否,1是','最小高','最小宽','最大高','最大宽','单位','单价'];
        $res = $excel->export('flower', $header,$list);
        
    }
    
    /**
     * 导入花件
     */
    public function importFlower()
    {

       $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];       
        }else{
            $this->error('上传excel失败');
        }    
        $excel = new Excel();
        $read = $excel->read2($filePath); 
        
        $list = Db::name('bom_flower')->field('code,name,min_height,min_width,max_height,max_width,'
                . 'is_cut,cut_min_height,cut_min_width,cut_max_height,cut_max_width,unit,price')
                ->select();
        $checkExist = [];
        foreach($list as $k => $v){
            $name = $v['code'].$v['name'].$v['min_height'].$v['min_width'].$v['max_height'].$v['max_width'].
                    $v['is_cut'].$v['cut_min_height'].$v['cut_min_width'].$v['cut_max_height'].$v['cut_max_width'].$v['unit'].floatval($v['price']);
            $checkExist[$name] = $name;
        }
     
        if($read){
            unset($read[0]);
            $insert = [];
            foreach ($read as $k => $v) {
                $name = $v[0].$v[1].$v[2].$v[3].$v[4].$v[5].$v[6].$v[7].$v[8].$v[9].$v[10].$v[11].floatval($v[12]);                
                if(!array_key_exists($name, $checkExist)){
                    $insert[] = [
                        'code'=>$v[0],'name'=>$v[1],'min_height'=>$v[2],'min_width'=>$v[3],'max_height'=>$v[4],'max_width'=>$v[5],
                        'is_cut'=>$v[6],'cut_min_height'=>$v[7],'cut_min_width'=>$v[8],'cut_max_height'=>$v[9],'cut_max_width'=>$v[10],
                        'unit'=>$v[11],'price'=>$v[12]
                        ];
                }
            }
            
            $res = Db::name('bom_flower')->insertAll($insert);
            if($res){
                $this->success('导入成功');
            }
            $this->error('导入失败或数据已经存在');
        }
        
    }
    
    /**
     * 花件结构
     */
    public function flowerStructure()
    {
        $id = input('id/d');

        //当前花件绑定的结构
        $struture = Db::name('bom_flower')->where('id',$id)->find();
        if(isset($struture['structure_id']) && $struture['structure_id'] != ''){
            $strutureId = $struture['structure_id'];
        }else{
            $strutureId = 0;
        }
        
        $bom = Db::name('structure')->order('id asc')->select(); //物料清单结构
        $listId = Db::name('structure')->whereIn('id', $strutureId)->column('id');
        //比较当前系列结构是否在结构表中
        foreach ($bom as $k => $v) {
            if (in_array($v['id'], $listId)) {
                $bom[$k]['select'] = 1;
            } else {
                $bom[$k]['select'] = 2;
            }
        }

        $list = Db::name('structure')->whereIn('id', $strutureId)->select();
        $this->assign('id', input('id'));
        $this->assign('bom', $bom);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    /**
     * 保存花件结构
     */
    public function saveFlowerStructure()
    {
        $flowerId = input('id/d');
        $structureId = input('structure_id/a');
        
        $res = Db::name('bom_flower')->where('id',$flowerId)->update([
            'structure_id' => implode(',', $structureId)
        ]);
        if($res!==false){
            $this->success('保存成功');
        }
        $this->error('保存失败,请重试');
    }
    
}
