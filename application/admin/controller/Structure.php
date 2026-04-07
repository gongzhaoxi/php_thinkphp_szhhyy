<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use excel\Excel;

/**
 * 结构控制器
 */
class Structure extends Base
{

    /**
     * 结构列表
     */
    public function index()
    {
        $keyword = input('keyword');
        $where = "1=1";
        if ($keyword) {
            $where .= " and (code like '%" . $keyword . "%')";
        }
        $list = Db::name('structure')->where($where)->paginate();
        $list->appends(input('get.'));
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 检测脚本名称存在
     */
    public function check()
    {
        $name = input('name');
        $rulername = scandir('./ruler/');
        if(in_array($name,$rulername)){
            $this->error('此脚本名称已存在');
        }
        $this->success('可以使用');
    }

    /**
     * 上传标尺脚本
     */
    public function uploadRuler()
    {
        $name = input('name');
        if(!$name){
            $this->error('请填写标尺脚本名称');
        }
        $rulername = scandir('./ruler/');
        if(in_array($name,$rulername)){
            $this->error('此脚本名称已存在');
        }
        $file = $this->request->file('file');

        $info = $file->move(ROOT_PATH . 'public' . DS . 'ruler' . DS ,$name);
        if($info){
            $this->success('上传成功');
        }
        $this->_error('上传失败');
    }

    /**
     * 添加结构
     */
    public function structureAdd()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $id = input('post.id/d');

            $result = $this->validate($data, "Structure");
            if ($result !== true) {
                $this->error($result);
            }

            if ($id) {
                $res = Db::name('structure')->where('id', $id)->update($data);
            } else {
                $res = Db::name('structure')->insert($data);
            }
            if (!$data) {
                $this->error('保存失败，请重试');
            }
            $this->success('保存成功');
            return;
        }

        $res = Db::name('structure')->where('id', input('id/d'))->find();
        $this->assign('res', $res);
        $this->assign('id', input('id'));
        return $this->fetch();
    }

   

    /**
     * 导入结构
     */
    public function importStructure()
    {
        set_time_limit(0);
        $file = $this->request->file('file');

        $upload = upload($file, 'file'); 
        if($upload['code'] == 0){
            $filePath = config('upload').$upload['pic'];  
            
//        $filePath = "./upload/admin/20190627/structure5.xlsx";
//        $list = $this->use_phpexcel($filePath);       
            
        $excel = new Excel();
        $list = $excel->read2($filePath); 
//         unset($list[0]);
//        foreach ($list as $k => $v) {
//            $res =Db::name('structure')->where('id',$v[0])->update(['bottom_spacing_count'=>$v[14],'bottom_frame_count'=>$v[15],'hold_hands_count'=>$v[16]]);
//
//        }
//        if($res){
//            $this->success(1);
//        } else {
//            $this->error(0);
//        }
//       exit;
        if ($list) {
            unset($list[0]);  //去除表头
            $imagePath = config('upload') . "admin/" . date('Ymd') . '/';
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0777, true);
            }
            $image = $excel->readImage($filePath, $imagePath); //图片数组
            $data = [];
            $i = 2;
            foreach ($list as $k => $v) {
                if (isset($v[0]) && $v[0] != '' && isset($v[3]) && $v[3] != '') {
                    $spic = isset($image['B' . $i])?$image['B' . $i]:'';
                    $rpic = isset($image['C' . $i])?$image['C' . $i]:'';
                    $structurePic = 'admin/' . date('Ymd') . '/' . $spic;
                    $rulerPic = 'admin/' . date('Ymd') . '/' . $rpic;
                    $data[] = [
                        'code' => $v[0], 'path_url' => $v[11], 'structure_pic' => $structurePic, 'ruler_pic' => $rulerPic, 'window_type' => $v[4],
                        'hands' => $v[5], 'fixed' => $v[3], 'face' => $v[6], 'min_height' => $v[8], 'min_width' => $v[7],
                        'max_width' => $v[9], 'max_height' => $v[10],'spacing_count'=>$v[12],'frame_count'=>$v[13],
                        'bottom_spacing_count' => $v[14],'bottom_frame_count'=>$v[15],'hold_hands_count'=>$v[16]
                    ];
                    $i++;
                }
               
            }
//            dump($data);exit;
            $res = Db::name('structure')->insertAll($data);
            if ($res) {
                $this->success('导入成功');
            }
            $this->error('导入失败,请重试');
        }
        }
    }

    /**
     * 导入公式
     */
    public function importFormula()
    {        set_time_limit(0);
        $file = $this->request->file('file');
        $upload = upload($file, 'file');
        $excel = new Excel();
        $filePath = config('upload') . $upload['pic'];
        $data = $excel->read2($filePath); //标尺公式
        $data2 = $excel->read2($filePath, 1); //算料公式
        //插入标尺公式
        if ($data) {
            unset($data[0]);
            //先将结构id抽离
            $structureId = [];
            foreach ($data as $k => $v) {
                $structureId[$v[0]] = $v[0];
            }
            //循环结构id和excel数据，判断是否属于当前结构
            $formula = [];
            $i = 0;
//            $sss = 717;
            foreach ($structureId as $k => $v) {
                foreach ($data as $k2 => $v2) {
                    //判断是否同一个结构
                    if ($v2[0] == $v) {
                        //再判断是否同一套公式
                        $temp = isset($temp) ? $temp : $v2[1];  //公式名称
                        $structure = isset($structure) ? $structure : $v2[0];
                        if (($v2[1] != $temp && $v2[1] != '') || $v2[0] != $structure) {
                            $i++;
                            $temp = $v2[1];
                            $structure = $v2[0];
                        }
                        $formula[$i]['formula'][] = ['field' => $v2[2], 'formula' => $v2[3]];
                        $formula[$i]['name'] = $temp;
                        $formula[$i]['structure_id'] = $v2[0];
//                        $formula[$i]['structure_id'] = $sss;
                    }
                }
//                $sss++;
            }
            //整理成能插入数据表的数据
            $insert = [];
            foreach ($formula as $k => $v) {
                $insert[] = ['name' => $v['name'], 'formula' => serialize($v['formula']), 'structure_id' => $v['structure_id']];
            }
            $res = Db::name('structure_ruler_formula')->insertAll($insert);
        }
        //插入算料公式
        if ($data2) {
            unset($data2[0]);
            //先将结构id抽离
            $structureId = [];
            foreach ($data2 as $k => $v) {
                $structureId[$v[0]] = $v[0];
            }
            //循环结构id和excel数据，判断是否属于当前结构
            $formula = [];
            $i = 0;
//            $sss = 717;
            foreach ($structureId as $k => $v) {
                foreach ($data2 as $k2 => $v2) {
                    //判断是否同一个结构
                    if ($v2[0] == $v) {
                        //再判断是否同一套公式
                        $temp = isset($temp) ? $temp : $v2[1];  //公式名称
                        $structure = isset($structure) ? $structure : $v2[0];
                        if (($v2[1] != $temp && $v2[1] != '') || $v2[0] != $structure) {
                            $i++;
                            $temp = $v2[1];
                            $structure = $v2[0];
                        }

                        $formula[$i]['formula'][] = ['bom' => $v2[2], 'formula' => $v2[3], 'count' => $v2[4], 'export_name' => $v2[5]];
                        $formula[$i]['name'] = $temp;
                        $formula[$i]['structure_id'] = $v2[0];
//                        $formula[$i]['structure_id'] = $sss;
                    }
                }
//                $sss++;
            }
            //整理成能插入数据表的数据
            $insert = [];
            foreach ($formula as $k => $v) {
                $insert[] = ['name' => $v['name'], 'formula' => serialize($v['formula']), 'structure_id' => $v['structure_id']];
//                $res = Db::name('structure_calculate_formula')->where("structure_id=$v[structure_id] and name='{$v['name']}'")->update(['formula' => serialize($v['formula'])]);
            }
            $res2 = Db::name('structure_calculate_formula')->insertAll($insert);
        }
        if (isset($res) && isset($res2)) {
            $this->success('导入成功');
        }
        $this->error('导入失败,请重试');
    }

    /**
     * 删除结构
     */
    public function structureDel()
    {
        
        //删除选中的
        if (input('ids/a') && is_array(input('ids/a'))) {
            $ids = implode(',', input('ids/a'));
            $res = Db::name('structure')->whereIn('id', $ids)->delete();
            write_log("批量删除结构id为:{$ids}的结构", cookie('uid'), cookie('login_name'));
        } else {
            //单个删除
            $id = input('id/d');
            $res = Db::name('structure')->where('id', $id)->delete();
            write_log("删除结构id为:{$id}的结构", cookie('uid'), cookie('login_name'));
        }
        
        
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败，请重试');
    }

    /**
     * 标尺公式
     */
    public function rulerFormula()
    {
        $structureId = input('id');
        $list = Db::name('structure_ruler_formula')->where('structure_id', $structureId)->select();

        $this->assign('list', $list);
        $this->assign('structure_id', input('id'));
        return $this->fetch();
    }

    /**
     * 添加标尺公式
     */
    public function addRulerFormula()
    {
        if ($this->request->isPost()) {
            $name = input('name');
            $structureId = input('structure_id');
            $field = input('field/a');
            $formula = input('formula/a');

            //整理数据
            $array = [];
            foreach ($field as $k => $v) {
                $array[] = ['field' => $v, 'formula' => $formula[$k]];
            }
            $formulaArray = serialize($array);
            $res = Db::name('structure_ruler_formula')->insert(['name' => $name, 'formula' => $formulaArray, 'structure_id' => $structureId]);

            if ($res) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }

        $structureId = input('structure_id');
        $structure = Db::name('structure')->where('id', $structureId)->find();
        $this->assign('structure_id', input('structure_id'));
        $this->assign('structure', $structure);
        return $this->fetch();
    }

    /**
     * 编辑标尺公式
     */
    public function editRulerFormula()
    {
        if ($this->request->isPost()) {
            $name = input('name');
            $field = input('field/a');
            $formula = input('formula/a');
            $srfId = input('srf_id');   //标尺公式Id
            //整理数据           
            $array = [];
            foreach ($field as $k => $v) {
                $array[] = ['field' => $v, 'formula' => $formula[$k]];
            }
            $formulaArray = serialize($array);
            $res = Db::name('structure_ruler_formula')->where('srf_id', $srfId)->update(['name' => $name, 'formula' => $formulaArray,]);

            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }
        $structureId = input('structure_id');
        $structure = Db::name('structure')->where('id', $structureId)->find();

        $srfId = input('srf_id'); //公式id
        $formula = Db::name('structure_ruler_formula')->where('srf_id', $srfId)->find();
        $formulaArray = $formula ? unserialize($formula['formula']) : '';

        $this->assign('list', $formulaArray);
        $this->assign('name', $formula);
        $this->assign('srf_id', $srfId);
        $this->assign('structure', $structure);
        return $this->fetch();
    }

    /**
     * 结构公式
     */
    public function calculateFormula()
    {
        $structureId = input('id');
        $list = Db::name('structure_calculate_formula')->where('structure_id', $structureId)->select();

        $this->assign('list', $list);
        $this->assign('structure_id', input('id'));
        return $this->fetch();
    }

    /**
     * 添加算料公式
     */
    public function addCalculateFormula()
    {
        if ($this->request->isPost()) {

            $name = input('name');
            $structureId = input('structure_id');
            $bom = input('bom/a'); //对应物料
            $formula = input('formula/a'); //计算公式
            $count = input('count/a'); //数量
            $exportName = input('export_name/a'); //输出名称
            //整理数据
            $array = [];
            foreach ($bom as $k => $v) {
                $array[] = ['bom' => $v, 'formula' => $formula[$k], 'count' => $count[$k], 'export_name' => $exportName[$k]];
            }
            $formulaArray = serialize($array);
            $res = Db::name('structure_calculate_formula')->insert(['name' => $name, 'formula' => $formulaArray, 'structure_id' => $structureId]);

            if ($res) {
                $this->success('添加成功');
            }
            $this->error('添加失败,请重试');
            return;
        }

        $structureId = input('structure_id');
        $structure = Db::name('structure')->where('id', $structureId)->find();
        $this->assign('structure_id', input('structure_id'));
        $this->assign('structure', $structure);
        return $this->fetch();
    }

    /**
     * 编辑算料公式
     */
    public function editCalculateFormula()
    {
        if ($this->request->isPost()) {

            $name = input('name');
            $bom = input('bom/a'); //对应物料
            $formula = input('formula/a'); //计算公式
            $count = input('count/a'); //数量
            $exportName = input('export_name/a'); //输出名称
            $scfId = input('scf_id');

            //整理数据
            $array = [];
            foreach ($bom as $k => $v) {
                $array[] = ['bom' => $v, 'formula' => $formula[$k], 'count' => $count[$k], 'export_name' => $exportName[$k]];
            }
            $formulaArray = serialize($array);
            $res = Db::name('structure_calculate_formula')->where('scf_id', $scfId)->update(['name' => $name, 'formula' => $formulaArray]);

            if ($res) {
                $this->success('保存成功');
            }
            $this->error('保存失败,请重试');
            return;
        }

        $structureId = input('structure_id');
        $scfId = input('scf_id'); //公式id

        $structure = Db::name('structure')->where('id', $structureId)->find();
        $formula = Db::name('structure_calculate_formula')->where('scf_id', $scfId)->find();
        $formulaArray = $formula ? unserialize($formula['formula']) : '';

        $this->assign('list', $formulaArray);
        $this->assign('name', $formula);
        $this->assign('scf_id', $scfId);
        $this->assign('structure', $structure);
        return $this->fetch();
    }
    
    //复制算料公式
    public function colorAdd()
    {
        $id = input('id/d');
        $sql = "insert into erp_structure_calculate_formula(name,formula,structure_id) select name,formula,structure_id from erp_structure_calculate_formula where scf_id=$id";
        $res = Db::name('structure_calculate_formula')->execute($sql);
				if($res){
				    $this->success('复制成功');
				}
				$this->error('复制失败,请重试');
		}
		
		//复制标尺公式
		public function colorAdda()
		{
		    $id = input('id/d');
		    $sql = "insert into erp_structure_ruler_formula(name,formula,structure_id) select name,formula,structure_id from erp_structure_ruler_formula where srf_id=$id";
		    $res = Db::name('structure_ruler_formula')->execute($sql);
				if($res){
				    $this->success('复制成功');
				}
				$this->error('复制失败,请重试');
		}
		
		//删除算料公式
		public function memberDela()
		{
				$id = input('id/d');
				$res = Db::name('structure_calculate_formula')->where('scf_id', $id)->delete();
				if($res){
				    $this->success('删除成功');
				}
				$this->error('删除失败,请重试');
		}
		
		//删除标尺公式
		public function memberDel()
		{
				$id = input('id/d');
				$res = Db::name('structure_ruler_formula')->where('srf_id', $id)->delete();
				if($res){
				    $this->success('删除成功');
				}
				$this->error('删除失败,请重试');
		}

}
