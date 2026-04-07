<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use tree\Tree;
use app\admin\logic\copy;

/**
 * 系列控制器
 */
class Series extends Base
{

    /**
     * 系列列表
     */
    public function index()
    {
        $result = Db::name('series')->orderRaw('convert(name using gbk)')->select();
        $tree = new Tree();
        $tree->icon = ['', '', ''];  //icon
        $tree->nbsp = '';  //空格偏移量
        foreach ($result as $key => $value) {
            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style'] = $value['parent_id'] == 0 ? '' : 'display:none;';
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                  <td style='padding-left:20px;'>\$spacer\$name</td>
                  <td>\$id</td>
                  <td>\$min_area</td>
                  <td class='is-price'>\$price</td>                  
                  <td class='is-hide'><a title='颜色'  onclick=xadmin.open('颜色','" . url('series/color') . "?id=\$id','1200','600') href='javascript:;'>颜色</a></td>
                  <td class='is-hide'><a title='纱网' onclick=xadmin.open('纱网','" . url('series/yarn') . "?id=\$id','1000','600') href='javascript:;'>纱网</a></td>
                  <td class='is-hide'><a title='花件' onclick=xadmin.open('花件','" . url('series/flower') . "?id=\$id','1000','600') href='javascript:;'>花件</a></td>
                  <td class='is-hide'><a title='结构' onclick=xadmin.open('结构','" . url('series/structure') . "?id=\$id','1200','600') href='javascript:;'>结构</a></td>
                  <td class='is-hide'><a title='把手位' onclick=xadmin.open('把手位','" . url('series/hands') . "?id=\$id','1000','600') href='javascript:;'>把手位</a></td>
                  <td class='is-hide'><a title='五金' onclick=xadmin.open('五金','" . url('series/five') . "?id=\$id','1000','600') href='javascript:;'>五金</a></td>
                  <td class='is-hide'><a title='物料绑定' onclick=xadmin.open('物料绑定','" . url('series/bom') . "?id=\$id','1000','650') href='javascript:;'>物料绑定</a></td>
                  <td class='is-hide'><a title='物料绑定' onclick=xadmin.open('绑定扣库物料','" . url('series/bindStockMaterial') . "?id=\$id','1000','650') href='javascript:;'>绑定扣库物料</a></td>
                  <td class='td-manage'>
                        <a title='编辑' onclick=xadmin.open('颜色','" . url('series/seriesAdd') . "?id=\$id&parent_id=\$parent_id','500','450') href='javascript:;'>
                            <i class='layui-icon'>&#xe642;</i>
                        </a>
                        <a title='复制' onclick='copy(\$id)' href='javascript:;'>
                            <i class='icon iconfont'>&#xe6b9;</i>
                        </a>";
        if($this->uid == 1){
            $str .= "<a title='删除' onclick=member_del(this,'\$id') href='javascript:;'>
                           <i class='layui-icon'>&#xe640;</i>
                        </a>";
        }
        $str .= "</td>
                  <td>
                    <input type='text' name='sort[]' value='\$sort' style='width:50%;text-align:center;'>
                    <input type='hidden' name='id[]' value='\$id' >
                  </td>                
               </tr>";

        $treeList = $tree->getTree(0, $str);
        $this->assign('treeList', $treeList);
        return $this->fetch();
    }

    /**
     * 系列绑定扣库物料
     */
    public function bindStockMaterial()
    {

        if(request()->isPost()){
            $seriesId = input('series_id');
            $number = input('number/a');
            $unit = input('unit/a');
            $iscolor = input('is_color');

            if(!is_array($number) || count($number) == 0){
                $this->error('请添加编码');
            }
            $insert = [];
            foreach ($number as $k => $v) {
                $insert[] = ['material_number'=>$v,'unit_content'=>$unit[$k],'is_color'=>$iscolor,'series_id'=>$seriesId];
            }
            //先删除,在添加
            Db::name('series_stock_material')->where('series_id',$seriesId)->delete();
            $res = Db::name('series_stock_material')->insertAll($insert);
            if($res){
                $this->success('操作成功');
            }
            $this->error('操作失败');
            return;
        }
        $id = input('id/d');
        $list = Db::name('series_stock_material')->where('series_id',$id)->select();
        $this->assign('list',$list);
        $this->assign('id',$id);
        return $this->fetch();
    }

    /**
     * 系列排序
     */
    public function sort()
    {
        $id = input('id/a');
        $sort = input('sort/a');

        $sql = "update erp_series set sort=case id";
        foreach($id as $k=>$v){
            $sortValue = $sort[$k]; 
            $sql .= " when $v then '{$sortValue}' \n";
        }
        $sql .= "end,";             
        $sql = rtrim($sql, ',');
        
        $ids = implode(',', $id);
        $sql .= " where id in ($ids)";        
        $res = Db::name('series')->execute($sql);
        if($res !== false){
            $this->success('更新排序成功');
        }
        $this->error('排序失败');
    }
    
    
    /**
     * 添加,编辑系列
     */
    public function seriesAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $db2 = Db::connect('database.db2');//中间数据库
            if ($id) {
                $res = Db::name('series')->where('id', $id)->update($data);
                $db2->table('erp_series')->where('id', $id)->update($data);
            } else {
                $res = Db::name('series')->insertGetId($data);
                $data['id'] = $res;
                $db2->table('erp_series')->insert($data);
//                Db::name('series_connect')->insert(['series_id' => $res]);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }
        $res = Db::name('series')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));

        //全部系列
        $tree = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result = Db::name('series')->order('name')->select();
        $array = [];
        //编辑时上级选中
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign('allseries', $selectCategory);
        return $this->fetch();
    }

    /**
     * 系列删除
     */
    public function seriesDel()
    {
        $id = input('id/d');
        $name = input('name');        

        if($this->uid != 1){
            $this->error('你没有权限删除');
        }
        $series = Db::name('series')->select();
        $delId = getChild($series, $id);
        if (!$delId) {
            $this->error('参数错误');
        }
        $ids = implode(',', $delId);
        $res = Db::name('series')->whereIn('id', $ids)->delete();

        $db2 = Db::connect('database.db2')->table('erp_series')->whereIn('id', $ids)->delete();//中间数据库
        write_log("删除系列名为:{$name}的系列", cookie('uid'), cookie('login_name'));
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 系列颜色
     */
    public function color()
    {
        $id = input('id/d');
        $list = Db::name('bom_color')->where('parent_id', 0)->orderRaw('convert(name using gbk)')->select();
        $this->assign('list', $list);
        $this->assign('id', input('id'));

        //当前系列全部颜色
        $color = Db::name('series_color')->alias('a')->field('a.*,b.name')
                ->join('bom_color b', 'a.color_id=b.id')
                ->where('series_id', $id)
                ->select();
        $frameColor = []; //铝型颜色
        $flowerColor = []; //花件颜色
        foreach ($color as $k => $v) {
            //铝型颜色
            if ($v['type'] == 1) {
                if ($v['level'] >= 2 && $v['relation'] != '') {
                    $levleName = Db::name('bom_color')->whereIn('id', $v['relation'])->column('name'); //多级栏目的上级名称
                    if ($levleName) {
                        $name = implode('--', $levleName);
                        $color[$k]['name'] = $name . '--' . $v['name'];
                    }
                }
                $frameColor[] = $color[$k];
            } else {
                //花件颜色
                if ($v['level'] >= 2 && $v['relation'] != '') {
                    $levleName = Db::name('bom_color')->whereIn('id', $v['relation'])->column('name'); //多级栏目的上级名称
                    if ($levleName) {
                        $name = implode('--', $levleName);
                        $color[$k]['name'] = $name . '--' . $v['name'];
                    }
                }
                $flowerColor[] = $color[$k];
            }
        }

        $this->assign('frame', $frameColor);
        $this->assign('flower', $flowerColor);
        return $this->fetch();
    }

    /**
     * 系列颜色添加，保存
     */
    public function colorAdd()
    {
        $data = input('post.info/a'); //铝型颜色
        $hdata = input('post.hinfo/a'); //花件颜色
        $id = input('series_id');
        
        

        //加入type和series_id
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]['type'] = 1;
                $data[$k]['series_id'] = $id;
                $data[$k]['all_relation'] = $v['relation'] != '' ? $v['relation'] . ',' . $v['color_id'] : $v['relation'] . $v['color_id'];
            }
            Db::name('series_color')->where(['series_id' => $id, 'type' => 1])->delete();
            $res = Db::name('series_color')->insertAll($data);
        }else{
            $res= Db::name('series_color')->where(['series_id' => $id, 'type' => 1])->delete();
        }
        
        if ($hdata) {
            foreach ($hdata as $k => $v) {
                $hdata[$k]['type'] = 2;
                $hdata[$k]['series_id'] = $id;
                $hdata[$k]['all_relation'] = $v['relation'] != '' ? $v['relation'] . ',' . $v['color_id'] : $v['relation'] . $v['color_id'];
            }
            Db::name('series_color')->where(['series_id' => $id, 'type' => 2])->delete();
            $res2 = Db::name('series_color')->insertAll($hdata);
        }else{
            $res2 = Db::name('series_color')->where(['series_id' => $id, 'type' => 2])->delete();
        }

        if ((isset($res) && $res !== false) || (isset($res2) && $res2 !== false)) {
            $this->success('保存成功');
        }
        $this->error('保存失败，请重试');
    }

    /**
     * 异步-颜色多级联动
     */
    public function findcolor()
    {
        $pid = input('pid/d');
        $two = input('two');  //判断是否是二级select

        $where = "parent_id=$pid";
        if ($two == 2) {
            $where .= " and parent_id !=0";
        }

        $list = Db::name('bom_color')->where($where)->select();
        $this->success('', $list);
    }

    /**
     * 纱网
     */
    public function yarn()
    {
        //下拉slelect数据
        $bom = Db::name('bom_yarn')->orderRaw('convert(name using gbk)')->select();
        //列表
        $list = Db::name('series_yarn')->alias('a')->field('a.*,b.name,b.thickness')
                ->join('bom_yarn b', 'a.yarn_id=b.id')
                ->where('series_id', input('id/d'))
                ->orderRaw('convert(name using gbk)')
                ->select();
        $this->assign('id', input('id'));
        $this->assign('bom', $bom);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 纱网添加,保存
     */
    public function yarnAdd()
    {
        $data = input('post.info/a');
        $id = input('series_id');
        if(!is_array($data)){
            Db::name('series_yarn')->where('series_id', $id)->delete();
            $this->success('保存成功');
        }else{
            //先删除在添加
            Db::name('series_yarn')->where('series_id', $id)->delete();
            $res = Db::name('series_yarn')->insertAll($data);
            if ($res !== FALSE) {
                $this->success('保存成功');
            }
            $this->error('保存失败，请重试');
        }
    }

    /**
     * 把手位
     */
    public function hands()
    {
        //下拉slelect数据
        $hands = Db::name('bom_hands')->orderRaw('convert(name using gbk)')->select();
        //列表数据
        $list = Db::name('series_hands')->alias('a')->field('a.*,b.name,b.width,b.height')
                ->join('bom_hands b', 'a.hands_id=b.id')
                ->where('series_id', input('id/d'))
                ->orderRaw('convert(name using gbk)')
                ->select();
        $this->assign('id', input('id'));
        $this->assign('hands', $hands);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 把手位添加,保存
     */
    public function handsAdd()
    {
        $data = input('post.info/a');
        $id = input('series_id');
        
        if(!is_array($data)){
            Db::name('series_hands')->where('series_id', $id)->delete();
            $this->success('保存成功');
        }else{
            //先删除在添加
            Db::name('series_hands')->where('series_id', $id)->delete();
            $res = Db::name('series_hands')->insertAll($data);
            if ($res !== FALSE) {
                $this->success('保存成功');
            }
            $this->error('保存失败，请重试');
        }
    }

    /**
     * 五金
     */
    public function five()
    {
        //下拉slelect数据
        $five = Db::name('bom_five')->orderRaw('convert(name using gbk)')->select();
        //列表数据
        $list = Db::name('series_five')->alias('a')->field('a.*,b.name,b.code')
                ->join('bom_five b', 'a.five_id=b.id')
                ->where('series_id', input('id/d'))
                ->orderRaw('convert(name using gbk)')
                ->select();
        $this->assign('id', input('id'));
        $this->assign('five', $five);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    /**
     * 把手位添加,保存
     */
    public function fiveAdd()
    {
        $data = input('post.info/a');
        $id = input('series_id');
        
        if(!is_array($data)){
            Db::name('series_five')->where('series_id', $id)->delete();
            $this->success('保存成功');
        }else{
            //先删除在添加
            Db::name('series_five')->where('series_id', $id)->delete();
            $res = Db::name('series_five')->insertAll($data);
            if ($res !== FALSE) {
                $this->success('保存成功');
            }
            $this->error('保存失败，请重试');
        }
    }
    
    /**
     * 物料绑定添加,保存
     */
    public function bomAdd()
    {

        $data = input('post.info/a');
        $id = input('series_id');
        foreach ($data as $k => $v) {
            $data[$k]['series_id'] = $id;
            //物料用户填写的数据 都存在第一行
            if ($k == 1) {
                $data[1]['take'] = input('take');
                $data[1]['frame_take_fan'] = input('frame_take_fan');
                $data[1]['small_frame'] = input('small_frame');
                $data[1]['small_fan'] = input('small_fan');
				$data[1]['ZK_B_KDK'] = input('ZK_B_KDK');
				$data[1]['ZK_B_KDS'] = input('ZK_B_KDS');
				$data[1]['waikuangbian'] = input('waikuangbian');
				$data[1]['shawangbian'] = input('shawangbian');
				$data[1]['menshanbian'] = input('menshanbian');
            } else {
                $data[$k]['take'] = 0;
                $data[$k]['frame_take_fan'] = 0;
                $data[$k]['small_frame'] = 0;
                $data[$k]['small_fan'] = 0;
				$data[$k]['ZK_B_KDK'] = 0;
				$data[$k]['ZK_B_KDS'] = 0;
				$data[$k]['waikuangbian'] = 0;
				$data[$k]['shawangbian'] = 0;
				$data[$k]['menshanbian'] = 0;
            }
        }
        
        //先删除在添加
        Db::name('series_bom')->where('series_id', $id)->delete();
        $res = Db::name('series_bom')->insertAll($data);
        if ($res !== FALSE) {
            $this->success('保存成功');
        }
        $this->error('保存失败，请重试');
    }

    /**
     * 物料绑定
     */
    public function bom()
    {
        $id = input('id/d');
        $list = Db::name('series_bom')->where('series_id', $id)->select();

        $this->assign('id', $id);
        $this->assign('bomname', config('series_bom'));
        if ($list) {
            $offset = count($list);
						for($i=$offset;$i<count(config('series_bom'));$i++){
							$list[] =  ['type'=>$i+1,'one_level'=>'','two_level'=>0];
						}
            //兼容旧逻辑，如果只有6，则加入新加的绑定关系
//             if(count($list) <= 6){
//                 $temp[7] = ['type'=>7,'one_level'=>'','two_level'=>0];
//                 $temp[8] = ['type'=>8,'one_level'=>'','two_level'=>0];
//                 $temp[9] = ['type'=>9,'one_level'=>'','two_level'=>0];
//                 $temp[10] = ['type'=>10,'one_level'=>'','two_level'=>0];
//                 $list = array_merge($list,$temp);
//             }
//             if(count($list) <= 10){
// 			$temp[11] = ['type'=>11,'one_level'=>'','two_level'=>0];
// 			$temp[12] = ['type'=>12,'one_level'=>'','two_level'=>0];
// 			$list = array_merge($list,$temp);
// 			}
//             if(count($list) <= 12){
// 			$temp[13] = ['type'=>13,'one_level'=>'','two_level'=>0];
// 			$temp[14] = ['type'=>14,'one_level'=>'','two_level'=>0];
// 			$temp[15] = ['type'=>15,'one_level'=>'','two_level'=>0];
// 			$list = array_merge($list,$temp);
//			}
            $this->assign('list', $list);
            return $this->fetch('bom_edit');
        }
        $this->assign('list', $list);
        return $this->fetch('bom_add');
    }

    /**
     * 物料绑定--异步查询物料信息
     */
    public function findbom()
    {
        $table = input('post.table');
        $list = Db::name($table)->order('id asc')->select();

        $this->success('', $list);
    }

    /**
     * 系列花件
     */
    public function flower()
    {
        $id = input('id/d');

        //当前系列花件
        $list = Db::name('series_flower')->alias('a')->field('a.*,b.pic')
                ->join('bom_flower b', 'a.flower_id=b.id')
                ->where('series_id', $id)
                ->select();
        $bom = Db::name('bom_flower')->select(); //物料清单花件
        $listId = Db::name('series_flower')->where('series_id', $id)->column('flower_id'); //花件flower_id
        //比较当前系列花件是否在物料清单中
        foreach ($bom as $k => $v) {
            if (in_array($v['id'], $listId)) {
                $bom[$k]['select'] = 1;
            } else {
                $bom[$k]['select'] = 2;
            }
        }
        $this->assign('id', input('id'));
        $this->assign('bom', $bom);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加，保存系列花件
     */
    public function flowerAdd()
    {
        $data = input('post.info/a');
        $id = input('series_id');

        if (!is_array($data)) {
            Db::name('series_flower')->where('series_id', $id)->delete();
            $this->success('保存成功');
        } else {

            $ids = [];
            //整理数据
            foreach ($data as $k => $v) {
                $data[$k]['series_id'] = $id;
                $ids[] = $v['flower_id'];
            }
            //获取物料清单中对应id的花件名称
            $names = Db::name('bom_flower')->whereIn('id', implode(',', $ids))->select();
            //若有填重命名则使用此名称，否则使用物料清单名称
            foreach ($data as $k => $v) {
                if ($v['name'] == '') {
                    $data[$k]['name'] = $names[$k]['name'];
                }
            }

            //先删除在添加
            Db::name('series_flower')->where('series_id', $id)->delete();
            $res = Db::name('series_flower')->insertAll($data);
            if ($res !== FALSE) {
                $this->success('保存成功');
            }
            $this->error('保存失败，请重试');
        }
    }

    /**
     * select下拉查询 花件是否可切
     */
    public function findCut()
    {
        $cut = input('iscut');
        $where = "is_cut='$cut'";
        if ($cut == '') {
            $where = '1=1';
        }
        $list = Db::name('bom_flower')->where($where)->select();
        $this->success('', $list);
    }

    /**
     * 结构
     */
    public function structure()
    {
        $id = input('id/d');

        //当前系列结构
        $list = Db::name('series_structure')->alias('a')->field('a.*,b.structure_pic')
                ->join('structure b', 'a.structure_id=b.id')
                ->where('series_id', $id)
                ->select();
        $bom = Db::name('structure')->order('id asc')->select(); //物料清单花件
        $listId = Db::name('series_structure')->where('series_id', $id)->column('structure_id');
        //比较当前系列结构是否在结构表中
        foreach ($bom as $k => $v) {
            if (in_array($v['id'], $listId)) {
                $bom[$k]['select'] = 1;
            } else {
                $bom[$k]['select'] = 2;
            }
        }

        //标尺公式套数
        $rulerFormula = Db::name('structure_ruler_formula')->distinct('name')->column('name');
        //算料公式套数
        $calculateFormula = Db::name('structure_calculate_formula')->distinct('name')->column('name');

        $this->assign('id', input('id'));
        $this->assign('bom', $bom);
        $this->assign('list', $list);
        $this->assign('ruler', $rulerFormula);
        $this->assign('calculate', $calculateFormula);
        return $this->fetch();
    }

    /**
     * 批量写入公式
     */
    public function writeFormula()
    {

        $structure = input('post.structure_id/a');
        $rulerName = input('post.sruler_name'); //所选的标尺公式名称
        $calculateName = input('post.scalculate_name'); //所选的算料公式名称
        $ssid = input('post.ss_id/a'); //系列结构id

        if (!is_array($structure) && count($structure) <= 0) {
            $this->error('请先添加结构');
        }
        $ruler = Db::name('structure_ruler_formula')->select();
        $calculate = Db::name('structure_calculate_formula')->select();
        $formulaId = []; //公式数组
        foreach ($structure as $k => $v) {
            foreach ($ruler as $k2 => $v2) {
                if ($v == $v2['structure_id'] && $v2['name'] == $rulerName) {
                    $formulaId[$ssid[$k]] = ['srf_id' => $v2['srf_id'], 'ruler_name' => $rulerName];
                }
            }
            foreach ($calculate as $k3 => $v3) {
                if ($v == $v3['structure_id'] && $v3['name'] == $calculateName) {
                    $formulaId[$ssid[$k]]['scf_id'] = $v3['scf_id'];
                    $formulaId[$ssid[$k]]['calculate_name'] = $calculateName;
                }
            }
        }
        
        $fieldArray = ['srf_id', 'scf_id', 'ruler_name', 'calculate_name'];
        $sql = "update erp_series_structure set";
        foreach ($fieldArray as $k => $v) {
            $sql .= " $v=case ss_id";
            foreach($formulaId as $k2=>$v2){
                if(isset($v2[$v]) && trim($v2[$v]) != '' && isset($k2) && $k2!=''){
                    $sql .= " when $k2 then '{$v2[$v]}' \n";
                }
            }
            $sql .= "end,";
        }
        $sql = rtrim($sql, ',');
        $id = implode(',', $ssid);
        $sql .= " where ss_id in ($id)";
//dump($sql);exit;
        
        $res = Db::name('series_structure')->execute($sql);
        if($res!==false){
            $this->success('写入成功');
        }
        $this->error('写入失败,请重试');
    }

    /**
     * 添加，保存系列结构
     */
    public function structureAdd()
    {
    	
//  	echo 123;
// 	print_r($_POST['structure_id']);die;
    	
    	
        $ruler = input('ruler/a');        
        $calculate = input('calculate/a');
        $structure = input('structure_id/a');        
        $rulerName = input('ruler_name/a');
        $calculateName = input('calculate_name/a');
       
        if (count($ruler) == 0) {
            $this->error('请添加结构');
        }
        $id = input('series_id');        
        //整理数据
        $data = [];
        foreach ($ruler as $k => $v) {
            $data[$k]['series_id'] = $id;
            $data[$k]['srf_id'] = $v;
            $data[$k]['scf_id'] = $calculate[$k];
            $data[$k]['structure_id'] = $structure[$k];
            $data[$k]['ruler_name'] = $rulerName[$k];
            $data[$k]['calculate_name'] = $calculateName[$k];
        }
//dump($data);dump($structure);exit;
        //先删除在添加
        Db::name('series_structure')->where('series_id', $id)->delete();
        $res = Db::name('series_structure')->insertAll($data);
        if ($res !== FALSE) {
            $this->success('保存成功');
        }
        $this->error('保存失败，请重试');
    }

    /**
     * 订单结构异步下拉
     */
    public function findStructure()
    {
        $windowType = input('window_type');
        $hands = input('hands');
        $fixed = input('fixed');
        $level = input('level');
        $sql = "1=1";
        if ($windowType != '') {
            $sql .= " and window_type='$windowType'";
        }
        if ($hands != '') {
            $sql .= " and hands='$hands'";
        }
        if ($fixed != '') {
            $sql .= " and fixed='$fixed'";
        }
        if($level){
            $sql .= " and level='$level'";
        }
        $list = Db::name('structure')->where($sql)->order('id asc')->select();

        $this->success('', $list);
    }

    /**
     * 选择标尺,算料公式
     */
    public function selectFormula()
    {
        $structureId = input('structure_id');
        $ruler = Db::name('structure_ruler_formula')->where('structure_id', $structureId)->select();
        $calculate = Db::name('structure_calculate_formula')->where('structure_id', $structureId)->select();

        $this->assign('calculate', $calculate);
        $this->assign('ruler', $ruler);
        return $this->fetch();
    }

    /**
     * 复制功能
     */
    public function copy()
    {
        $seriesId = input('id/d');
        $copy = new copy();
        //获取包含自身的所有子集数组
        $result = Db::name('series')->select();
        if ($result) {
            $res = $copy->series($result, $seriesId);
            if ($res) {
                $this->success('复制成功');
            }
            $this->error('复制失败,请重试');
        }
    }

}
