<?php

use app\service\MatchingGxline;
use think\Exception;
//字段绑定工艺线缓存
function field_rule_cache(){
    $matchine = new MatchingGxline();
    $list = $matchine->convertField();
    cache_write("filed_rule","field_rule",$list);
}
//班组缓存
function team_cache(){
	$father=M("team")->where("pid='0'")->order("id asc")->select();//一级分组
	$data=array();//按级别数组编组
	$list=array();//所有班组同级
	if($father!==false&&count($father)>0){
		foreach($father as $value){
			//保存一级
			$data[$value['id']]=$value;
			$list[$value['id']]=$value;
			//查找二级
			$child=M("team")->where("pid='{$value['id']}'")->order("id asc")->select();
			if($child!==false&&count($child)>0){
				$t=array();
				foreach ($child as $cval){
					$t[$cval['id']]=$cval;
					$list[$cval['id']]=$cval;
				}
				$data[$value['id']]['childs']=$t;
			}
		}	
		
		cache_write("team","team",$data);
		cache_write("team_list","team_list",$list);
	}

}
//字段设置缓存
function qrfield_cache(){
	$list=Db::name("qrcode_fields")->where("status='0'")->order("isqrcode desc,orderby asc")->select();
	$data=array();
	$field_type=array();//区分开二维码还是列表
	$field_type['qrcode']=array();
	$field_type['onlist']=array();
	$field_type['scheduallist']=array();
	foreach($list as $key=>$value){
		$data[$value['id']]=$value;
		if($value['isqrcode']==1){
			$field_type['qrcode'][]=$value;
		}
		if($value['onlist']==1){
			$field_type['onlist'][$value['listorder']]=$value;
		}
		if($value['scheduallist']==1){
			$field_type['scheduallist'][$value['scheduleorder']]=$value;
		}
	}
	if(isset($field_type['onlist'])){
		ksort($field_type['onlist']);
	}
	if(isset($field_type['scheduallist'])){
		ksort($field_type['scheduallist']);
	}
	cache_write("qrfield_type","qrfield_type",$field_type);
	cache_write("qrfield","qrfield",$data);
}
//固定工作流缓存
function fix_gx_cache(){
	
	$gx_list=M("gx_list")->order("orderby asc")->select();
	$gxlist=array();
	if($gx_list){
		foreach($gx_list as $value){
			$gxlist[$value['id']]=$value;
		}
	}
	
	$list=M("fixed_gx")->order("gx_orderby asc")->select();
	$data=array();//按分组显示所有工序的名称和id
	$id_arr=array();//按分组所有工序id  
	foreach($list as $key=>$value){
		
		$gob=$value['gx_orderby'];
		$gx_id=$value['gx_id'];
		$gx_name=$gxlist[$gx_id]['dname'];;
		
		$t=array();
		$t['gx_id']=$gx_id;
		$t['gx_name']=$gx_name;
		
		if($value['pid']=='0'){
			//父
			$id_arr[$gob]['parent'][]=$value['gx_id'];
			$data[$gob]['parent'][]=$t;
		}else{
			//子
			$id_arr[$gob]['child'][]=$value['gx_id'];
			$data[$gob]['child'][]=$t;
		}
	
	}
	cache_write("fix_gx","fix_gx",$data);
	cache_write("fix_gx_id","fix_gx_id",$id_arr);
}
//生产二维码base64
function qrcode($text,$_size=1){
	$qrcode = new \QRcode();
	$level = 'L'; //容错级别
	$size = $_size;
	ob_start();
	$qrcode->png($text,false,$level,$size,2);
	$imageString = base64_encode(ob_get_contents());
	ob_end_clean();
	return $imageString;
}
//查找某个字段名是否存在
function find_field($name){
	$qrfield=@include APP_DATA.'qrfield.php';
	if($qrfield){
		foreach($qrfield as $value){
			if($value['fieldname']==$name){
				return true;
			}
		}
	}
	return false;
}

//导出csv
//$list  是数据
//$field 是英文字段名称-用来对应$list的数据
//$title 是列的标题数组
function export_csv($list, $field,$title,$doc){
	set_time_limit(0);
	ini_set('memory_limit', '1024M');
	
	$fileName = date('YmdHis', time());
	ob_end_clean();
	header('Content-Encoding: UTF-8');
	header("Content-type:application/vnd.ms-excel;charset=UTF-8");
	header('Content-Disposition: attachment;filename="' . $doc['title'].$fileName . '.csv"');
	
	$fp = fopen('php://output', 'a');
	fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
	foreach ($title as $key => $item){
		$title[$key] = $item;
	}
	
	fputcsv($fp, $title);
	
	foreach($list as $key=>$value){
		$row=array();
		foreach ($field as $k=> $name){
			$row[$k] = $value[$name];
		}
		fputcsv($fp, $row);
	}
	
	ob_flush();  //刷新缓冲区
	flush();
}
//导出多个csv，并用zip打包
//$title 是列的标题数组 array('表格名1'=>array('id'=>'ID','name'=>'姓名'..))
//$tabList 是array('表格名1'=>$list数据1,'表格名2'=>$list数据2...)
//$doc文档标识
function export_csv_zip($tabList,$title,$doc){
	set_time_limit(0);
	ini_set('memory_limit', '1024M');
	
	$dir=UPLOAD_DIR."csv/";
	if(!file_exists($dir)){
		mkdir($dir);
	}
	
	if ($tabList === false||count($tabList)<=0) {
		exit("数据为空");
	}
	
	$files=[];
	foreach($tabList as $fileName=>$list){
		
		$name=$fileName.".csv";
		$path =$dir.$name;
		$files[$name]=$path;
		
		ob_end_clean();
		header('Content-Encoding: UTF-8');
		header("Content-type:application/vnd.ms-excel;charset=UTF-8");
		header('Content-Disposition: attachment;filename="' . $doc['headTitle'].$name.'"');
		
		$fp = fopen($path, 'w');
		fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));
		
		//写入标题行
		$sheetTitle=[];
		foreach ($title[$fileName] as $field => $item){
			$sheetTitle[$field] = $item;
		}
		
		if(isset($doc['headTitle'])){
			//居中
			$head=[];
			$length=count($sheetTitle);
			$middle=ceil($length/2);
			for($i=0;$i<$length;$i++){
				if($i==$middle){
					$head[$i]=$doc['headTitle'];
				}else{
					$head[$i]='';
				}
			}
			fputcsv($fp,$head);
		}
		
		fputcsv($fp, $sheetTitle);
		
		//写入每一行值
		foreach($tabList[$fileName] as $value){
			$row=array();
			foreach($title[$fileName] as $field=>$name){
				$row[$field] = $value[$field];
			}
			fputcsv($fp, $row);
		}
		
		fclose($fp);
		
	}
	
	$zip = new \ZipArchive();
	$docName=$doc['title'].time().'.zip';
	$zipName = $dir. $docName;
	$result=$zip->open($zipName, \ZipArchive::CREATE);
	foreach ($files as $name=>$file) {
		$zip->addFile($file, $name);
	}
	$zip->close();

	foreach ($files as $file) {
		@unlink($file);
	}
	
	header('Content-disposition: attachment; filename=' . $docName);
	header("Content-Type: application/zip");
	header("Content-Transfer-Encoding: binary");
	header('Content-Length: ' . filesize($zipName));
	readfile($zipName);
	@unlink($zipName);
	
	ob_flush();  //刷新缓冲区
	flush();
}
//导出单个工作表数据
//$doc 要都出的文件基本信息数组
//$field是要导出的字段名称的数组
//$line_title是导出的excel的第一行标题数组，不是数据
//$ex 要导出的excel 文件的版本默认是2007,可以使用2003版本的
//$jumpurl 是默认跳转的页面
//$un_need 不需要导出的字段名
//$field 字段数目应该要与$line_title的长度是一样的
function export_excel($list, $field, $line_title,$doc,$un_need = array(), $ex = '2007') {

	if ($list === false||count($list)<=0) {
		return false;
	}

	//最多导出60个字段，可以继续增加
	$Excel_letter = array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 
							'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
							'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF',
							'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP',
							'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
							'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ',
							'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT',
							'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD' );
	//spl_autoload_register ( array ('Think', 'autoload' ) ); //必须的，不然ThinkPHP和PHPExcel会冲突
	$objExcel = new \PHPExcel ();

	//设置导出文档的文件基本属性
	$objExcel->getProperties ()->setCreator ( $doc ['creator'] );
	$objExcel->getProperties ()->setLastModifiedBy ( $doc ['creator'] );
	$objExcel->getProperties ()->setTitle ( $doc ['title'] );
	$objExcel->getProperties ()->setSubject ( $doc ['subject'] );
	$objExcel->getProperties ()->setDescription ( $doc ['description'] );
	$objExcel->getProperties ()->setKeywords ( $doc ['keywords'] );
	$objExcel->getProperties ()->setCategory ( $doc ['category'] );
	$objExcel->setActiveSheetIndex ( 0 ); //第一个工作表


	//设置表头--即Excel的第一行数据
	foreach ( $line_title as $key => $value ) {
		$objExcel->getActiveSheet ()->setCellValue ( $Excel_letter [$key] . "1", $value ); //$key 格式是:A1 $value是字段的中文名称
	}

	
	$start_line = 0; //从第几行开始写入数据，一般是从第二行开始
		 

	/*----------写入内容-------------*/
	foreach ( $list as $key => $value ) {
		$line = $start_line + 2;
		for($k = 0; $k < count ( $field ); $k ++) {
			if (! in_array ( $field [$k], $un_need )) {
				//不输出指定的字符串
				$objExcel->getActiveSheet ()->getStyle ( $Excel_letter [$k] )->getNumberFormat ()->setFormatCode ( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );
				$objExcel->getActiveSheet ()->setCellValueExplicit ( $Excel_letter [$k] . $line, $value [$field [$k]], \PHPExcel_Cell_DataType::TYPE_STRING );
			}
		}
		$start_line ++; //移动到下一行
	}

	// 高置列的宽度  $Excel_letter[$i] 代表的该列的名称 例如 A B C ...
	for($i = 0; $i < count ( $line_title ); $i ++) {
		 
		$objExcel->getActiveSheet ()->getColumnDimension ( $Excel_letter [$i] )->setWidth ( 20 ); //默认宽度是15
	}

	$objExcel->getActiveSheet ()->getHeaderFooter ()->setOddHeader ( '&L&BPersonal cash register&RPrinted on &D' );
	$objExcel->getActiveSheet ()->getHeaderFooter ()->setOddFooter ( '&L&B' . $objExcel->getProperties ()->getTitle () . '&RPage &P of &N' );

	// 设置页方向和规模
	$objExcel->getActiveSheet ()->getPageSetup ()->setOrientation ( \PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT );
	$objExcel->getActiveSheet ()->getPageSetup ()->setPaperSize ( \PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
	$objExcel->setActiveSheetIndex ( 0 );
	$timestamp = "_" . date ( "YmdHis", time() );
	if ($ex == '2007') { //导出excel2007文档
		header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header ( 'Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xlsx"' );
		header ( 'Cache-Control: max-age=0' );
		$objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
		$objWriter->save ( 'php://output' );
		exit ();
	} else { //导出excel2003文档
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xls"' );
		header ( 'Cache-Control: max-age=0' );
		$objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel5' );
		$objWriter->save ( 'php://output' );
		exit ();
	}

}


//导出多个工作表数据
//$tabList 是array(工作表名=>二维数组) 的三维数组
//$doc 要都出的文件基本信息数组
//$field是要导出的字段名称的数组 array(工作表名=>二维数组)
//$line_title是导出的excel的第一行标题数组，不是数据 array(工作表名=>二维数组)
//$ex 要导出的excel 文件的版本默认是2007,可以使用2003版本的
//$jumpurl 是默认跳转的页面
//$un_need 不需要导出的字段名
//$field 字段数目应该要与$line_title的长度是一样的
function export_excel_multiple($tabList, $field, $line_title,$doc,$un_need = array(), $ex = '2007') {

	if ($tabList === false||count($tabList)<=0) {
		return false;
	}
	
	//最多导出60个字段，可以继续增加
	$Excel_letter = array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF',
			'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP',
			'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ',
			'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT',
			'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD' );
	//spl_autoload_register ( array ('Think', 'autoload' ) ); //必须的，不然ThinkPHP和PHPExcel会冲突
	$objExcel = new \PHPExcel ();

	//设置导出文档的文件基本属性
	$objExcel->getProperties ()->setCreator ( $doc ['creator'] );
	$objExcel->getProperties ()->setLastModifiedBy ( $doc ['creator'] );
	$objExcel->getProperties ()->setTitle ( $doc ['title'] );
	$objExcel->getProperties ()->setSubject ( $doc ['subject'] );
	$objExcel->getProperties ()->setDescription ( $doc ['description'] );
	$objExcel->getProperties ()->setKeywords ( $doc ['keywords'] );
	$objExcel->getProperties ()->setCategory ( $doc ['category'] );
	
	$tabIndex=0;
	foreach($tabList as $title=>$list){
		
		if($tabIndex>0){
			$objExcel->createSheet();
		}
		
		$objExcel->setActiveSheetIndex ($tabIndex); //第一个工作表
			
		//设置表头--即Excel的第一行数据
		foreach ( $line_title[$title] as $key => $value ) {
			$objExcel->getActiveSheet ()->setCellValue ( $Excel_letter [$key] . "1", $value ); //$key 格式是:A1 $value是字段的中文名称
		}
	
	
		$start_line = 0; //从第几行开始写入数据，一般是从第二行开始
			
	
		/*----------写入内容-------------*/
		foreach ( $list as $key => $value ) {
			$line = $start_line + 2;
			for($k = 0; $k < count ( $field[$title] ); $k ++) {
				if (! in_array ( $field[$title] [$k], $un_need )) {
					//不输出指定的字符串
					$objExcel->getActiveSheet ()->getStyle ( $Excel_letter [$k] )->getNumberFormat ()->setFormatCode ( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );
					$objExcel->getActiveSheet ()->setCellValueExplicit ( $Excel_letter [$k] . $line, $value [$field[$title] [$k]], \PHPExcel_Cell_DataType::TYPE_STRING );
				}
			}
			$start_line ++; //移动到下一行
		}
	
		// 高置列的宽度  $Excel_letter[$i] 代表的该列的名称 例如 A B C ...
		for($i = 0; $i < count ( $line_title[$title] ); $i ++) {
				
			$objExcel->getActiveSheet ()->getColumnDimension ( $Excel_letter [$i] )->setWidth ( 20 ); //默认宽度是15
		}
	
		$objExcel->getActiveSheet ()->getHeaderFooter ()->setOddHeader ( '&L&BPersonal cash register&RPrinted on &D' );
		$objExcel->getActiveSheet ()->getHeaderFooter ()->setOddFooter ( '&L&B' . $objExcel->getProperties ()->getTitle () . '&RPage &P of &N' );
	
		// 设置页方向和规模
		$objExcel->getActiveSheet ()->getPageSetup ()->setOrientation ( \PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT );
		$objExcel->getActiveSheet ()->getPageSetup ()->setPaperSize ( \PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
		$objExcel->setActiveSheetIndex ( $tabIndex );
		$objExcel->getActiveSheet()->setTitle($title);
			
		$tabIndex++;
	}
	
	$timestamp = "_" . date ( "YmdHis", time() );
	if ($ex == '2007') { //导出excel2007文档
		header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header ( 'Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xlsx"' );
		header ( 'Cache-Control: max-age=0' );
		$objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
		$objWriter->save ( 'php://output' );
		exit ();
	} else { //导出excel2003文档
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $doc ['title'] . $timestamp . '.xls"' );
		header ( 'Cache-Control: max-age=0' );
		$objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel5' );
		$objWriter->save ( 'php://output' );
		exit ();
	}

}

function schedule_plan_excel($genre_data,$glass_data,$data,$title,$glass_title,$date,$ex = '2007'){
    $genre = Db::name('series_genre')->order('id asc')->select();//物料类别
    if (empty($genre)){
        $genre = array(['name'=>'默认类别']);
    }
    //创建excel对象
    $objExcel = new \PHPExcel();
    $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y', 'Z');
    $genre_title = ['bad_cause'=>'异常原因','over_cause'=>'超时原因','ordernum'=>'销售单号','product_no'=>'生产单号','area'=>'面积','width'=>'宽','height'=>'高','pname'=>'物料名称','uname'=>'客户名称','color'=>'颜色','snum'=>'套数','doornum'=>'玻扇数','screenwin'=>'纱扇数','fixedglassnum'=>'固玻','addtime'=>'提交时间','schedule_time'=>'排产时间','plan_time'=>'计划完成时间','fact_time'=>'实际完成时间','send_time'=>'实际发货时间'];
    foreach ($genre_data as $kc=>$gc){
        if($kc>0){
            $objExcel->createSheet();
        }
        /* 第n个sheet */
        $objExcel->setActiveSheetIndex($kc);
        //给Sheet设置名字
        $objExcel->getActiveSheet()->setTitle($genre[$kc]['name']);
        $Excel_title = array();
        //动态生成列名
        $length = intval(count($title[$kc])*4)+19;
        $need_num = ceil($length/26);
        if ($need_num>1){
            $Excel_title=$letter;
            for ($e=0;$e<$need_num;$e++){
                for ($s=0;$s<count($letter);$s++){
                    $text = $letter[$e].$letter[$s];
                    array_push($Excel_title,$text);
                }
            }
        }else {
            $Excel_title=$letter;
        }
        //         echo json_encode($genre_title);
        //         exit();
        //合并单元格
        $t = 0;
        foreach ($genre_title as $kj=>$tl){
            $objExcel->getActiveSheet()->mergeCells($Excel_title[$t].'1:'.$Excel_title[$t].'3');
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$t].'1',$tl);//赋标题值
            $t++;
        }
        $inx = 19;
        foreach ($title[$kc] as $key=>$tk){
            $objExcel->getActiveSheet()->mergeCells($Excel_title[$inx].'1:'.$Excel_title[$inx+3].'1');
            $objExcel->getActiveSheet()->mergeCells($Excel_title[$inx].'2:'.$Excel_title[$inx+3].'2');
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx].'1',$tk['work_value'].'天');//赋标题值
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx].'2',$tk['dname']);//赋标题值
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx].'3','开始日期');//赋标题值
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx+1].'3','预计完成日期');//赋标题值
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx+2].'3','实际完成日期');//赋标题值
            $objExcel->getActiveSheet()->setCellValue($Excel_title[$inx+3].'3','是否超时');//赋标题值
            $inx+=4;
        }
        foreach ($gc as $kz=>$val){
            $ix = 0;
            foreach ($genre_title as $ko=>$go){
                $objExcel->getActiveSheet()->setCellValue($Excel_title[$ix].($kz+4),$val[$ko]);//赋值
                $ix++;
            }
        foreach ($title[$kc] as $tc){
                foreach ($val['gx_list'] as $ki=>$vl){
                    if ($tc['id']==$ki){
                        $objExcel->getActiveSheet()->setCellValue($Excel_title[$ix].($kz+4),'');//赋值
                        $objExcel->getActiveSheet()->setCellValue($Excel_title[$ix+1].($kz+4),$vl['plan_finish_time']);//赋值
                        $objExcel->getActiveSheet()->setCellValue($Excel_title[$ix+2].($kz+4),$vl['finish_time']);//赋值
                        $objExcel->getActiveSheet()->setCellValue($Excel_title[$ix+3].($kz+4),$vl['isover']);//赋值
                    }
                }
                $ix+=4;
            }
        }
    }

    $objExcel->createSheet();
    /* 第二个sheet */
    $objExcel->setActiveSheetIndex(count($genre_data));
    //给Sheet设置名字
    $objExcel->getActiveSheet()->setTitle("玻璃");
    $Excel_letter = array();
    //动态生成列名
    $length = count($glass_title);
    $need_num = ceil($length/26);
    if ($need_num>1){
        $Excel_letter=$letter;
        for ($i=0;$i<$need_num;$i++){
            for ($s=0;$s<count($letter);$s++){
                $text = $letter[$i].$letter[$s];
                array_push($Excel_letter,$text);
            }
        }
    }else {
        $Excel_letter=$letter;
    }
    //设置表标题
    $index = 0;
    foreach ($glass_title as $k=>$gd){
        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$index].'1',$gd);
        $index++;
    }
    foreach ($glass_data as $kt=>$gk){
        $num = 0;
        $line = $kt+2;
        foreach ($glass_title as $key=>$gt){
            $objExcel->getActiveSheet()->setCellValue($Excel_letter[$num].$line,$gk[$key]);
            $num++;
        }
    }
    /* 第二个sheetEND */

    /* 最后sheet */
    $objExcel->createSheet();
    $objExcel->setActiveSheetIndex(count($genre_data)+1);
    //给Sheet设置名字
    $objExcel->getActiveSheet()->setTitle("汇总");
    $first_title = ['kname','gxname','final_value','unit','num','area','b_num','s_num','finish_num','finish_area','finish_b_num','finish_s_num','finish_man','day_value','hour_value','unit','value','people'];//表1的字段
    $i = 1;
    //加边框
    $border = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),
        ),
    );
    foreach ($data as $key=>$dk){
        $objExcel->getActiveSheet()->getStyle('A'.($i+1).':R'.(count($dk['list'])+$i+2))->applyFromArray($border);
        $objExcel->getActiveSheet()->mergeCells('A'.$i.':R'.$i);
        $objExcel->getActiveSheet()->mergeCells('A'.($i+1).':A'.($i+2));
        $objExcel->getActiveSheet()->mergeCells('B'.($i+1).':B'.($i+2));
        $objExcel->getActiveSheet()->mergeCells('C'.($i+1).':C'.($i+2));
        $objExcel->getActiveSheet()->mergeCells('D'.($i+1).':D'.($i+2));
        $objExcel->getActiveSheet()->mergeCells('E'.($i+1).':H'.($i+1));
        $objExcel->getActiveSheet()->mergeCells('I'.($i+1).':L'.($i+1));
        $objExcel->getActiveSheet()->mergeCells('M'.($i+1).':M'.($i+2));
        $objExcel->getActiveSheet()->mergeCells('N'.($i+1).':P'.($i+1));
        $objExcel->getActiveSheet()->mergeCells('Q'.($i+1).':R'.($i+1));
        $objExcel->getActiveSheet()->setCellValue('A'.$i, '排产时间：'.$date);
        $objExcel->getActiveSheet()->setCellValue('A'.($i+1), '种类');
        $objExcel->getActiveSheet()->setCellValue('B'.($i+1), '工序');
        $objExcel->getActiveSheet()->setCellValue('C'.($i+1), '固定日产值');
        $objExcel->getActiveSheet()->setCellValue('D'.($i+1), '单位');
        $objExcel->getActiveSheet()->setCellValue('E'.($i+1), '排产值');
        $objExcel->getActiveSheet()->setCellValue('I'.($i+1), '完成值');
        $objExcel->getActiveSheet()->setCellValue('M'.($i+1), '报工人数');
        $objExcel->getActiveSheet()->setCellValue('N'.($i+1), '产值');
        $objExcel->getActiveSheet()->setCellValue('Q'.($i+1), '缺口');
        $objExcel->getActiveSheet()->setCellValue('E'.($i+2), '套数');
        $objExcel->getActiveSheet()->setCellValue('F'.($i+2), '面积');
        $objExcel->getActiveSheet()->setCellValue('G'.($i+2), '玻扇');
        $objExcel->getActiveSheet()->setCellValue('H'.($i+2), '纱扇');
        $objExcel->getActiveSheet()->setCellValue('I'.($i+2), '套数');
        $objExcel->getActiveSheet()->setCellValue('J'.($i+2), '面积');
        $objExcel->getActiveSheet()->setCellValue('K'.($i+2), '玻扇');
        $objExcel->getActiveSheet()->setCellValue('L'.($i+2), '纱扇');
        $objExcel->getActiveSheet()->setCellValue('N'.($i+2), '日(1D)');
        $objExcel->getActiveSheet()->setCellValue('O'.($i+2), '时(8H/D)');
        $objExcel->getActiveSheet()->setCellValue('P'.($i+2), '单位');
        $objExcel->getActiveSheet()->setCellValue('Q'.($i+2), '产值');
        $objExcel->getActiveSheet()->setCellValue('R'.($i+2), '人数');
        $i+=3;
        foreach ($dk['list'] as $ke=>$dl){
            foreach ($first_title as $key=>$name){
                $objExcel->getActiveSheet()->setCellValue($letter[$key].$i, $dl[$name]);
            }
            $i++;
        }

        //汇总行
        $objExcel->getActiveSheet()->setCellValue('B'.$i, '合计');
        $objExcel->getActiveSheet()->setCellValue('E'.$i, $dk['total_num']);
        $objExcel->getActiveSheet()->setCellValue('F'.$i, $dk['total_area']);
        $objExcel->getActiveSheet()->setCellValue('G'.$i, $dk['total_b_num']);
        $objExcel->getActiveSheet()->setCellValue('H'.$i, $dk['total_s_num']);
        $objExcel->getActiveSheet()->setCellValue('I'.$i, $dk['total_finish_num']);
        $objExcel->getActiveSheet()->setCellValue('J'.$i, $dk['total_finish_area']);
        $objExcel->getActiveSheet()->setCellValue('K'.$i, $dk['total_finish_bnum']);
        $objExcel->getActiveSheet()->setCellValue('L'.$i, $dk['total_finish_snum']);
        $objExcel->getActiveSheet()->setCellValue('M'.$i, $dk['total_man']);
        $objExcel->getActiveSheet()->setCellValue('N'.$i, $dk['total_day_value']);
        $objExcel->getActiveSheet()->setCellValue('O'.$i,$dk['total_hour_value']);
        $objExcel->getActiveSheet()->setCellValue('Q'.$i, $dk['total_value']);
        $objExcel->getActiveSheet()->setCellValue('R'.$i, $dk['total_people']);
        $i+=2;
    }
    /* 最后sheetEND */
     


     
    if ($ex == '2007') { //导出excel2007文档
        header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        header ( 'Content-Disposition: attachment;filename="预生产计划汇总报表'.date('Y-m-d').'.xlsx"' );
        header ( 'Cache-Control: max-age=0' );
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
        $objWriter->save ( 'php://output' );
        exit ();
    } else { //导出excel2003文档
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename="预生产计划汇总报表'.date('Y-m-d').'.xls"' );
        header ( 'Cache-Control: max-age=0' );
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel5' );
        $objWriter->save ( 'php://output' );
        exit ();
    }
}

//入库&接单数量
function into_order_excel($data,$total,$title,$ex = '2007'){
    $genre = Db::name('series_genre')->order('id asc')->select();//物料类别
    if (empty($genre)){
        $genre = array(['name'=>'默认类别']);
    }
    //创建excel对象
    $objExcel = new \PHPExcel();
    $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y', 'Z');
    $Excel_letter = array();
    //动态生成列字母
    $length = count($title)+5;
    $need_num = ceil($length/26);
    if ($need_num>1){
        $Excel_letter=$letter;
        for ($i=0;$i<$need_num;$i++){
            if ($i<26){
                for ($s=0;$s<count($letter);$s++){
                    $text = $letter[$i].$letter[$s];
                    array_push($Excel_letter,$text);
                }
            }
        }
    }else {
        $Excel_letter=$letter;
    }
    //样式设置
    $objExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);//所有单元格（行）默认高度
    $objExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);//所有单元格（列）默认宽度
    $objExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    //合并单元格
    $objExcel->getActiveSheet()->mergeCells('A1:C1');
    //列标题
    $objExcel->getActiveSheet()->setCellValue('A1','日期 / 系列');
    $objExcel->getActiveSheet()->setCellValue('D1','合计数量');
    $objExcel->getActiveSheet()->setCellValue('E1','平均数量');
    for ($i=0;$i<count($title);$i++){
        $objExcel->getActiveSheet()->setCellValue($Excel_letter[$i+5].'1',$title[$i]);
    }
    //行标题并赋值
    $start = 2;
    foreach ($genre as $k=>$gk){
        //合并单元格
        $objExcel->getActiveSheet()->mergeCells('A'.$start.':A'.($start+6));
        $objExcel->getActiveSheet()->mergeCells('B'.$start.':B'.($start+1));
        $objExcel->getActiveSheet()->mergeCells('B'.($start+2).':B'.($start+3));
        $objExcel->getActiveSheet()->mergeCells('B'.($start+4).':B'.($start+5));
        //标题
        $objExcel->getActiveSheet()->setCellValue('A'.($start),$gk['name']);
        $objExcel->getActiveSheet()->setCellValue('B'.$start,'接单');
        $objExcel->getActiveSheet()->setCellValue('B'.($start+2),'入库');
        $objExcel->getActiveSheet()->setCellValue('B'.($start+4),'未完成订单');
        $objExcel->getActiveSheet()->setCellValue('B'.($start+6),'实际周期');
        $t = 0;
        for ($s=0;$s<3;$s++){
            $objExcel->getActiveSheet()->setCellValue('C'.($start+$t),'套数');
            $objExcel->getActiveSheet()->setCellValue('C'.($start+$t+1),'平方');
            $t += 2;
        }
        $objExcel->getActiveSheet()->setCellValue('C'.($start+6),'天数');
        foreach ($data[$k] as $kn=>$val){
            foreach ($val as $kc=>$vl){
                $objExcel->getActiveSheet()->setCellValue($Excel_letter[$kc+3].''.($kn+$start),$vl);//赋值
            }
        }
        $start += 7;
    }
    //汇总标题并赋值
    //合并单元格
    $objExcel->getActiveSheet()->mergeCells('A'.($start).':A'.($start+2));
    $objExcel->getActiveSheet()->mergeCells('B'.($start).':E'.($start));
    $objExcel->getActiveSheet()->mergeCells('B'.($start+1).':E'.($start+1));
    $objExcel->getActiveSheet()->mergeCells('B'.($start+2).':E'.($start+2));
    $objExcel->getActiveSheet()->setCellValue('A'.($start),'汇总');
    $objExcel->getActiveSheet()->setCellValue('B'.($start),'接单平方');
    $objExcel->getActiveSheet()->setCellValue('B'.($start+1),'成品入库');
    $objExcel->getActiveSheet()->setCellValue('B'.($start+2),'未完成订单');
    //赋值
    foreach ($total as $kl=>$val){
        foreach ($val as $key=>$vl){
            $objExcel->getActiveSheet()->setCellValue($Excel_letter[$key+5].''.($start+$kl),$vl);
        }
    }
    $start += count($total);
    for ($j=0;$j<$start;$j++){

    }
    if ($ex == '2007') { //导出excel2007文档
        ob_end_clean();
        header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        header ( 'Content-Disposition: attachment;filename="预生产计划汇总报表'.date('Y-m-d').'.xlsx"' );
        header ( 'Cache-Control: max-age=0' );
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
        $objWriter->save ( 'php://output' );
        //         return ['code'=>0];
        exit ();
    } else { //导出excel2003文档
        ob_end_clean();
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename="预生产计划汇总报表'.date('Y-m-d').'.xls"' );
        header ( 'Cache-Control: max-age=0' );
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel5' );
        $objWriter->save ( 'php://output' );
        //         return ['code'=>0];
        exit ();
    }
}