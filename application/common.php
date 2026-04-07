<?php
use think\Db;

/**
 * 写入系统日志
 */
function write_log($name,$uid,$uname){
    $ip = $_SERVER['REMOTE_ADDR'];
    Db::name('log')->insert(['user_id'=>$uid,'user_name'=>$uname,'name'=>$name,'addtime'=>date('Y-m-d H:i:s',time()),'ip'=>$ip]);
}



/**
 * 密码加密方法
 * @param string $pw       要加密的原始密码
 * @return string
 */
function password($pw)
{
    $key = config('password_key');
    $result = "###" . md5(md5($key . $pw));
    return $result;
}

/**
 * 生成二维码图片
 * @return string 文件名称
 */
function qrcode($url)
{
    $qrcode = new \Endroid\QrCode\QrCode();
    $url = $url; // url或内容，如果是url的话，要加http://才能跳转
    $path = config('upload') . "qrcode/" . date('Ymd') . '/'; //  图片路径
    if (!file_exists($path)) {
        mk_dir($path);
    }
    $filename = md5(time() . rand(10000, 99999)) . '.jpg';
    $qrcode->setText($url)
            ->setSize(300)//  大小
            ->setLabelFontPath(VENDOR_PATH . 'endroid'.DS.'qrcode'.DS.'assets'.DS.'noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabelFontSize(16)
            ->writeFile($path . $filename);
    return $filename;
}

/**
 * 递归获取子类id
 * @param array $result 数组
 * @param int $id 要获取子类的父id
 * @return array
 */
function getChild($result, $id, $level = 0)
{
    $child = [];
    if (is_array($result)) {
        foreach ($result as $k => $v) {
            //加上自身id数组
            if ($level == 0 && $id == $v['id']) {
                $v['level'] = $level;
                $child[] = $v['id'];
            }
            if ($v['parent_id'] == $id) {
                $v['level'] = $level + 1; //加入树层级
                $child[] = $v['id'];
                $child = array_merge($child, getChild($result, $v['id'], $level + 1));
            }
        }
    }
    return $child;
}

/**
 * 获取上传配置
 */
function get_upload_setting()
{


    $uploadSetting = [
        'file_types' => [
            'image' => [
                'upload_max_filesize' => '30240', //单位KB
                'extensions' => 'jpg,jpeg,png,gif,bmp4,pdf'
            ],
            'video' => [
                'upload_max_filesize' => '10240',
                'extensions' => 'mp4,avi,wmv,rm,rmvb,mkv'
            ],
            'audio' => [
                'upload_max_filesize' => '10240',
                'extensions' => 'mp3,wma,wav'
            ],
            'file' => [
                'upload_max_filesize' => '302400',
                'extensions' => 'txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar'
            ]
        ],
        'chunk_size' => 512, //单位KB
        'max_files' => 20 //最大同时上传文件数
    ];


    return $uploadSetting;
}

/**
 * 获取文件扩展名
 * @param string $filename 文件名
 * @return string 文件扩展名
 */
function get_file_extension($filename)
{
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}

/**
 * curl get 请求
 * @param $url
 * @return mixed
 */
function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function curl_post($url, $data = array())
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}

/**
 * 循环创建目录
 */
function mk_dir($dir, $mode = 0777)
{
    if (is_dir($dir) || @mkdir($dir, $mode))
        return true;
    if (!mk_dir(dirname($dir), $mode))
        return false;
    return @mkdir($dir, $mode);
}

function upload($file, $type)
{
    $uploadSetting = get_upload_setting();
    $arrFileTypes = [
        'image' => ['title' => 'Image files', 'extensions' => $uploadSetting['file_types']['image']['extensions']],
        'video' => ['title' => 'Video files', 'extensions' => $uploadSetting['file_types']['video']['extensions']],
        'audio' => ['title' => 'Audio files', 'extensions' => $uploadSetting['file_types']['audio']['extensions']],
        'file' => ['title' => 'Custom files', 'extensions' => $uploadSetting['file_types']['file']['extensions']]
    ];


    $originalName = $file->getInfo('name');
    $arrAllowedExtensions = explode(',', $arrFileTypes[$type]['extensions']);
    $strFileExtension = strtolower(get_file_extension($originalName));
    if (!in_array($strFileExtension, $arrAllowedExtensions) || $strFileExtension == 'php') {
        return ['code' => 1, 'msg' => "非法文件类型!"];
    }
    $info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'admin' . DS);
    if ($info) {
        $date = date('Ymd');
        $pic = "admin/$date/" . $info->getFilename();
        return ['code' => 0, 'msg' => '上传成功', 'pic' => $pic];
    } else {
        return ['code' => 1, 'msg' => $file->getError(), 'pic' => ''];
    }
}




/**
 * 将数组文件写进文件缓存
 * @param string $name 文件名
 * @param string $var 
 * @param array $values 要写入的文件的数组 
 */
function cache_write($name, $var, $values) {
	$cachefile = APP_PATH . '/extra/cache/data_' . $name . '.php';
	$cachetext = "<?php\r\n" . "if(!defined('THINK_PATH')) exit('Access Denied');\r\n" . 'return $' . $var . '=' . arrayeval ( $values ) . "\r\n?>";
	if (! swritefile ( $cachefile, $cachetext )) {
		exit ( "File: $cachefile write error." );
	}
}

//数组转换成字串
function arrayeval($array, $level = 0) {
	$space = '';
	for($i = 0; $i <= $level; $i ++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach ( $array as $key => $val ) {
		$key = is_string ( $key ) ? '\'' . addcslashes ( $key, '\'\\' ) . '\'' : $key;
		$val = ! is_array ( $val ) && (! preg_match ( "/^\-?\d+$/", $val ) || strlen ( $val ) > 12 || substr ( $val, 0, 1 ) == '0') ? '\'' . addcslashes ( $val, '\'\\' ) . '\'' : $val;
		if (is_array ( $val )) {
			$evaluate .= "$comma$key => " . arrayeval ( $val, $level + 1 );
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

//写入文件
function swritefile($filename, $writetext, $openmod = 'w') {
	if (@$fp = fopen ( $filename, $openmod )) {
		flock ( $fp, 2 );
		fwrite ( $fp, $writetext );
		fclose ( $fp );
		return true;
	}
}

//获取某段时间内的开始和结束时间戳
//$when 是指某个时间段，1是今天，2是本周，3是本月，4是三月内，5半年内，6是今年
//7昨天，8上个星期，9上个月，10去年,11是本季度
function timezone_get($when=1){
	
	$now = time();
	switch ($when){
		case 1:
			//今天
			$beginTime=mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endTime=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		break;
		case 2:
			//本周
			$time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);
			$beginTime =  $time;
			$endTime =  strtotime('Sunday', $now)+24*60*60-1;
		break;
		case 3:
			//本月
			$beginTime =  mktime(0, 0, 0, date('m', $now), '1', date('Y', $now));
			$endTime = mktime(23, 59, 59, date('m', $now), date('t', $now), date('Y', $now))-1;
		break;
		case 4:
			//三个月内
			$time = strtotime('-2 month', $now);
			$beginTime =mktime(0, 0,0, date('m', $time), 1, date('Y', $time));
			$endTime = mktime(23, 59, 59, date('m', $now), date('t', $now), date('Y', $now));
		break;	
		case 5:
			//半年内
			$time = strtotime('-6 month', $now);
			$beginTime = mktime(0, 0,0, date('m', $time), 1, date('Y', $time));
			$endTime = mktime(23, 59, 59, date('m', $now), date('t', $now), date('Y', $now));
		break;
		case 6:
			//今年
			$beginTime = mktime(0, 0,0, 1, 1, date('Y', $now));
			$endTime = mktime(23, 59, 59, 12, 31, date('Y', $now));			
		break;
		case 7:
			//昨天
			$beginTime= strtotime(date('Y-m-d',strtotime('-1 day')));
            $endTime= strtotime(date('Y-m-d'))-1;
		break;
		case 8:
			//上个星期
			$beginTime= strtotime(date('Y-m-d',strtotime('-2 week Monday')));
			$endTime=strtotime(date('Y-m-d',strtotime('-1 week Sunday +1 day')))-1;
		break;
		case 9:
			//上个月
			$beginTime= strtotime(date('Y-m-01',strtotime('-1 month')));
			$endTime= strtotime(date('Y-m-01'))-1;
		break;
		case 10:
			//去年
			$beginTime= strtotime(date('Y-01-01',strtotime('-1 year')));
			$endTime= strtotime(date('Y-12-31',strtotime('-1 year')))+24*60*60-1;
		break;
		case 11:
			//本季度
			$quarter = empty($param) ? ceil((date('n'))/3) : $param;
			$beginTime = mktime(0, 0, 0,$quarter*3-2,1,date('Y'));
			$endTime= mktime(0, 0, 0,$quarter*3+1,1,date('Y'))-1;
		break;
	}
	
	return array('begin'=>$beginTime,'end'=>$endTime);

}