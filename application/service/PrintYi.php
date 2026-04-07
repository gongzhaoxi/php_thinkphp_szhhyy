<?php

namespace app\service;

use App\Config\YlyConfig;
use App\Oauth\YlyOauthClient;
use App\Api\PrinterService;
use App\Api\PrintService;
use App\Api\PicturePrintService;
use think\Db;

/**
 * 打印及根据用户id获取打印机
 */
class PrintYi 
{

    protected $config;
    protected $machine_code = ''; //机器码
    protected $secret = '';//机器密钥
    protected $origin_id = '';   //内部订单号(32位以内)
    protected $style = 1;//样式,默认为1

    public function __construct()
    {
       
    }

    /**
     * 初始化设置打印机配置
     * @param int $printId 打印机print_style表id
     * @param int $origin_id 内部订单号
     */
    public function setPrint($printId,$origin_id=111)
    {
        include_once "../extend/yilianyun/Autoloader.php";
        $print = Db::name('print_style')->where('id',$printId)->find();
        if(!$print){
            $msg = ['code'=>1,'msg'=>'未找到此打印机'];
            exit(json_encode($msg));
        }
        $this->config = new YlyConfig('1097688740', '4c4421a2abd6a01e1eedc2509c532aaf');
        $this->machine_code = $print['machine_code'];
        $this->secret = $print['secret'];
        $this->origin_id = $origin_id;
        $this->style = $print['style'];
    }
    
    
    /**
     * 获取打印机 uid->班组->工序->打印机
     * @param type $uid
     */
    public function getPrintStyle($uid,$type=1)
    {
        //先获取班组，然后获取班组绑定的工序
        $team = Db::name('login')->alias('a')->field('a.*,b.ngx_id')
                ->join('team_gx b','a.tid=b.tid')
                ->where('a.id',$uid)->find();
        if(!$team){
            return false;
        }
        $teamGx = $this->getTeamGx($team['tid'], $team);//用户对应班组 所绑定的工序
        $print = Db::name('print_style')->alias('a')->field('a.*,b.dname')
                ->join('gx_list b','a.gxid=b.id')
                ->whereIn('a.gxid',$teamGx)->where('a.type',$type)->select();        
        return $print;
    }
       
    /**
     * 获取班组绑定的工序id
     * @param int $teamid 班组id
     * @param array $team 班组数据
     * @return array
     */
    public function getTeamGx($teamid,$team)
    {
        $gxid = unserialize($team['ngx_id']);
        $data = [];
        foreach ($gxid as $k2 => $v2) {
            $data[] = $v2[0];
        }
        
        return $data;
    }    
    
    /**
     * 获取入库组的工序
     */
    public function getIntoGroup()
    {
        //入库组的工序
        $into = Db::name('gx_list')->field('a.id,a.dname')->alias('a')->join('gx_group b','a.gid=b.id')
                ->where("a.lid",'>','0')->where('b.cache_id',5)
                ->select();
        return $into;
    }
    
    
    /**
     * 获取token
     * @param bool $constraint 是否强制从接口获取access_token
     */
    public function getAccessToken($constraint=false)
    {

        $client = new YlyOauthClient($this->config);
        $token = cache('print_token');          
        if (!$token || $constraint == true) {
            $token = $client->getToken();                     
            cache('print_token', $token);
            //授权打印机(自有型应用使用)
            $printer = new PrinterService($token->access_token, $this->config);
            $data = $printer->addPrinter("$this->machine_code", "$this->secret", '', '');
 
        }
        return $token;
    }

    /**
     * 执行打印
     */
    public function executePrint($data = [],$number=1)
    {
        $printCache = @include_once APP_DATA.'print_style.php';
        $styleMap = ['1'=>'template','2'=>'template2'];//样式值对应的方法名
        $method = $styleMap[$this->style];
        $token = $this->getAccessToken();
        $access_token = $token->access_token;        
        $print = new PrintService($access_token, $this->config);
        try {
            
            for($i=1;$i<=$number;$i++){
                if ($url=='https://hongnuoxuan2.ecloudm.com' || $uri=='hongnuoxuan2.ecloudm.com'){
                    $content = $this->template3($data, $number, $i);
                    $print->index($this->machine_code, $content, $this->origin_id);
                }elseif ($url=='https://shengbodun2.ecloudm.com' && $uri=='shengbodun2.ecloudm.com'){
                    $content = $this->template($data, $number, $i);
                    $print->index($this->machine_code, $content, $this->origin_id);
                }else {
                    $content = $this->template2($data, $number, $i);
                    $print->index($this->machine_code, $content, $this->origin_id);
                }
                
            }
            return true;
        } catch (Exception $e) {
//            $this->getAccessToken($constraint); //若打印失败，则更新acess_token缓存
            return false;
        }
    }
    
    /**
     * 执行图片打印
     */
    public function picturePrint($data = [],$number=1)
    {
        $printCache = @include_once APP_DATA.'print_style.php';
        $styleMap = ['1'=>'template','2'=>'template2'];//样式值对应的方法名
        $method = $styleMap[$this->style];
        $token = $this->getAccessToken();
        $access_token = $token->access_token;        
        $print = new PicturePrintService($access_token, $this->config);
        try {          
            for($i=1;$i<=$number;$i++){ 
                $content = create_table($data, $number, $i);                        
                $print->index($this->machine_code, 'http://'.$_SERVER['HTTP_HOST'].$content, $this->origin_id);
            }
            return true;
        } catch (Exception $e) {
//            $this->getAccessToken($constraint); //若打印失败，则更新acess_token缓存
            return false;
        }
    }
    
    /**
     * 设置打印模板
     * @param array $data 
     * @param int $number 打印总数量
     * @param int $page 第几页
     */
    public function template($data,$number,$page)
    {
        //58mm排版 排版指令详情请看 http://doc2.10ss.net/332006
        $content = "<PW>100</PW>";
		$content .= "\n";
        $content .= "<FB><FS2>          圣博盾门窗</FS2></FB>\n\n";
        $content .= "<FS2><FB>包装名称:</FB>{$data['type']}</FS2>\n";
        $content .= "<FS2>产品窗号：{$data['name1']}</FS2>\n";
        $content .= "<FS2><FB>客户:</FB> {$data['uname']}</FS2>\n";
        $content .= "<FS2><FB>单号:</FB> {$data['unique_sn']}</FS2>\n\n";
        $content .= "<FS>品名: {$data['pname']}</FS>\n";
        $content .= "<FS>颜色: <FB>{$data['color']}</FB></FS>\n";
        $content .= "<FS>产品规格: {$data['width']}*{$data['height']} </FS>";
        $content .= "<FS>网型号: {$data['signnet']}</FS>\n";
        $content .= "<FS>配件数量: {$data['accnum']}  </FS>";
        $content .= "<FS>件数: {$data['snum']}</FS>\n";
        $content .= "<FS><FB>地址: {$data['address']}</FB></FS>\n";
        $content .= "<BR2>{$data['unique_sn']}</BR2>";
        
        return $content;
    }

     /**
     * 设置打印模板
     * @param array $data 
     * @param int $number 打印总数量
     * @param int $page 第几页
     */
    public function template2($data,$number,$page)
    {
        //58mm排版 排版指令详情请看 http://doc2.10ss.net/332006
        $content = "<FS><center>兴泰兄弟2</center></FS>";
        $content .= str_repeat('.', 32)."\n";
        $content .= "<FS>客户    {$data['uname']} \n";
        $content .= "单号    {$data['unique_sn']} \n";
        $content .= "系列    {$data['pname']} \n";
        $content .= "颜色    {$data['color']} \n";
        $content .= "尺寸    {$data['guige']} \n";
        $content .= "锁具    {$data['lock_color']}\n";
        $content .= "件数    {$data['type']}$number-$page \n";
        $content .= "备注    {$data['tip']} \n</FS>";
        $content .= "<QR>{$data['unique_sn']}</QR>";
        
        return $content;
    }
    /**
     * 设置打印模板
     * @param array $data
     * @param int $number 打印总数量
     * @param int $page 第几页
     */
    public function template3($data,$number,$page)
    {
        //58mm排版 排版指令详情请看 http://doc2.10ss.net/332006
        $content = "  \n\n";
        $content = "<FS2><center>泓诺轩门窗</center></FS2>";
        $content .= "\n";
        //        $content .= str_repeat('.', 32)."\n";
        $content .= "<FS>客户   {$data['uname']} \n";
        $content .= "单号    {$data['unique_sn']} \n";
        $content .= "系列   {$data['pname']} \n";
        $content .= "颜色   {$data['color']} \n";
        $content .= "尺寸    {$data['guige']} \n";
        $content .= "锁具    {$data['lock_color']}\n";
        $content .= "件数    {$data['type']}$number-$page \n";
        $content .= "备注    {$data['tips']} \n</FS>";
        $content .= "<BR2>{$data['unique_sn']}</BR2>";
    
        return $content;
    }
    /**
     * 切割方案打印
     */
    public function executeCut($data = [])
    {
        $token = $this->getAccessToken();
        $access_token = $token->access_token;        
        $print = new PicturePrintService($access_token, $this->config);
        try {          
            foreach ($data as $k => $v) {
                $content = '/uploads/print/'.date('Ymd',time()).'/'.cut_table($v);                      
                $print->index($this->machine_code, 'http://'.$_SERVER['HTTP_HOST'].$content, $this->origin_id);
            }
            return true;
        } catch (Exception $e) {
//            $this->getAccessToken($constraint); //若打印失败，则更新acess_token缓存
            return false;
        }
    }
    
    /**
     * 切割方案打印模板
     * @param array $data 
     */
//    public function cutTemplate($data)
//    {
//        $content .= "<PW>080</PW>";
//        $content .= "<FS><LR>单号:{$data['ordernum']},  {$data['uname']}</LR></FS>\n";
//        $content .= "<FS><LR>[{$data['pname']}]{$data['name']},  {$data['color']}{$data['size']}</LR></FS>\n";
//        $content .= "<QR>{$data['ordernum']}</QR>";     
//        return $content;
//    }
          
    
}
