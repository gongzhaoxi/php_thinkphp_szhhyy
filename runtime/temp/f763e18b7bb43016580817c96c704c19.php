<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:102:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/printing.html";i:1741166091;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
    <head>
        <meta charset="UTF-8">
        <title>深圳恒辉</title>
        <meta name="renderer" content="webkit|ie-comp|ie-stand">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!--        <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />-->
        <meta http-equiv="Cache-Control" content="no-siteapp" />
        <link rel="stylesheet" href="/static/css/font.css">
        <link rel="stylesheet" href="/static/css/xadmin.css">
        <!-- <link rel="stylesheet" href="/static/css/theme5.css"> -->
        <script src="/static/lib/layui/layui.js" charset="utf-8"></script>
        <script type="text/javascript" src="/static/js/xadmin.js"></script> 
        <script type="text/javascript" src="/static/js/erp.js"></script>       
        <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
        <!--[if lt IE 9]>
          <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
          <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script>
            // 是否开启刷新记忆tab功能
             var is_remember = false;
        </script>
    </head>
<style>
    .center{ text-align: center;margin: 10px 0; font-size: 28px; color: #000; font-weight: bold;}
    .title{ display: inline-block;width: 32%; margin: 5px 0;}
    .all{ display: inline-block;width: 10%;}
    td{text-align: center;font-size:12px !important;}
	.layui-table{margin: 0px;}
    .layui-table td{ color: #000;border-color:#000; padding: 10px 0;}
    .layui-table thead tr{ background: none;}
    .layui-table thead td{ border: none;text-align: left;}
    .layui-table tfoot td{ border: none;text-align: left;}
    .layui-col-md12{ padding-top: 0px; padding-bottom: 0px; }
    @media print {
            INPUT {
                display: none;
            }
            #print{display:none;}
        }
    .layui-table td{ padding: 0px; line-height: 13px;}
    .title{ line-height: 20px; margin:0px;}
    .layui-table{font-size: 12px;}
    body{
		font-size: 12px; width:100%;margin: 0 auto;overflow: hidden;background-color: #fff;font-family: "SimSun", "宋体", sans-serif;
		/* width: 869.2913385826772px; height: 529.1338582677166px;overflow: hidden; */
		}
    .layui-fluid{ padding:0px; }
    .layui-col-space15{margin:0px;}
    .layui-col-space15>*{padding: 0px;}
    .center{margin: 0px;}

</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
               
            </div>
            <div class="layui-col-md12">
                <table class="layui-table layui-form" >
                    <thead style="display: table-header-group;">
                        <tr>
                            <td colspan="12" style="position: relative;padding-top: 5px;">
                                <h3 style="text-align: left; padding-left:20px ;"><img src='/static/images/logo2023.png?20230703' style="height: 28px; margin-right: 5px; float: left;"/><span style="font-size: 28px; line-height: 30px;">广东恒辉窗花制品有限公司订购清单(标准)</span></h3>
                                <div>
                                    <div class="title" style="width: 47%;text-align: left;">客户名称:<?php echo $order['dealer']; ?></div>
                                    <div class="title" style="width: 28%;text-align: left;">客户电话:<?php echo $order['phone']; ?></div>
                                    <div class="title" style="width: 23%;text-align: left;">订单日期:<?php echo date('Y-m-d',$order['addtime']); ?></div>
                                    <div class="title" style="width: 47%;text-align: left;">送货地址:<?php echo $order['send_address']; ?></div>
                                    <div class="title" style="width: 28%;text-align: left;">订单编号:<?php echo $order['number']; ?></div>
                                    <div class="title" style="width: 23%;text-align: left;">出货日期:<?php echo $order['end_time']; ?></div>
									<div style='position: absolute;top:5px;right: 10px;'>
										微信收款码<br/>
										<img src='/static/images/fahuo.jpg' width="50" /></div>
                                </div>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>                                       
                            <!--<td>栏号</td>-->
                            <td width="4%">编号</td>
                            <td width="24%">材质 - 花类</td>
                            <td width="8%">铝型/花件颜色</td>
                            <td width="4%">单位</td>
                            <td width="4%">宽</td>
                            <td width="4%">高</td>
                            <td width="4%">个数</td>
                            <td width="5%">面积</td>
                            <td width="23%">单价清单</td>
                            <td width="5%">折扣</td>
                            <td width="8%">折后价</td>
                            <td width="10%">总金额</td>
                            
                        </tr>
                        <?php if(is_array($product) || $product instanceof \think\Collection || $product instanceof \think\Paginator): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td style="text-align: left;"><?php echo trim($v['material'],'/'); if($v['flower_type']){ echo '/'.$v['flower_type'];} ?>/<?php echo $v['small']; ?>*<?php echo $v['big'];  $name = unserialize($v['yarn_color']);if($name['name'] != ''){ echo '/网'.$name['name'];} ?></td>
                            <td><?php echo $v['color_name']; ?></td>
                            <td>㎡</td>
                            <td><?php echo $v['all_width']; ?></td>
                            <td><?php echo $v['all_height']; ?></td>
                            <td><?php echo $v['count']; ?></td>
                            <td><?php echo $v['area']; ?></td>
                            <td>单价:<?php echo $v['price']; 
                                $priceList = unserialize($v['other_add_price']);
                                if(is_array($priceList) && count($priceList)>0){
                                echo "<table class='layui-table layui-form table2' style='margin:10px 0 0 0'>";
                                foreach($priceList as $k => $v2){

                                echo "<tr><td style='border: none;padding-bottom: 0px;'>$v2[descript]</td></tr>";
                                }
                                echo '</table>';
                                }
                                 ?>
                            </td>
                            <td><?php echo $v['rebate']; ?></td>
                            <td><?php echo $v['rebate_price']; 
                                $priceList = unserialize($v['other_add_price']);
                                if(is_array($priceList) && count($priceList)>0){
                                echo "<table class='layui-table layui-form table2' style='margin:10px 0 0 0'>";
                                foreach($priceList as $k => $v2){

                                echo "<tr><td style='border: none;padding-bottom: 0px;'>$v2[value]</td></tr>";
                                }
                                echo '</table>';
                                }
                                
                                 ?>
                            </td>
                            <td><?php echo $v['all_price']; ?></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; if(is_array($material) || $material instanceof \think\Collection || $material instanceof \think\Paginator): $i = 0; $__LIST__ = $material;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $v['type']; ?></td>
                            <td><?php echo $v['color']; ?></td>
                            <td>㎡</td>
                            <td><?php echo $v['width']; ?></td>
                            <td><?php echo $v['height']; ?></td>
                            <td><?php echo $v['count']; ?></td>
                            <td><?php echo $v['area']; ?></td>
                            <td><?php echo $v['price']; ?></td>
                            <td><?php echo $v['rebate']; ?></td>
                            <td><?php echo $v['rebate_price']; ?></td>
                            <td><?php echo $v['all_price']; ?></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <tr>
                            <td>备注</td>
                            <td colspan="11" style=" text-align: left;padding-left: 1%;">
                                <?php if($order['note'] != ''): ?>订单备注<?php echo $order['note']; ?>&nbsp;&nbsp;<?php endif; if(is_array($product) || $product instanceof \think\Collection || $product instanceof \think\Paginator): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($v['note'] != ''): ?>
                                编号<?php echo $i; ?>:<?php echo $v['note']; endif; endforeach; endif; else: echo "" ;endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">汇总</td>
                            <td colspan="2">个数：<?php echo $order['count']; ?></td>
                            <td colspan="4">面积：<?php echo $order['area']; ?></td>
                            <td></td>
                            <td colspan="3"><?php echo $order['total_price']; ?></td>
                        </tr>
						<tr>
						    <td colspan="2"><strong>订金</strong></td>
						    <td colspan="6"><strong><?php echo $order['have_pay']; ?></strong></td>
						    <td><strong>余额</strong></td>
						    <td colspan="3"><strong><?php echo $order['total_price'] - $order['have_pay']; ?></strong></td>
						</tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="12" style="position: relative;">
                                <div style="width: 80%;float: left; line-height: 15px;">
									<p>&nbsp;</p>
									<p>特别说明</p>
									<p>一、敬请核对价格表中型号及结算方式，此单一旦签字认可及作为欠条凭证；</p>
									<p>二、以上报价不含税（如需发票，单价另计税费）、不含运输、不含量尺安装，深圳地区订单包送货。</p>
									<p>三、客户核对订单后，给予50%订单，订货生效，订单核对并确认3小时后不可修改，如需修改，费用另计。</p>
									<p>咨询电话：4007776388 25735065 传真：0755-25736017 业务代表：<?php echo $order['sales_name']; ?>。</p>
								</div>
								<div style="position: absolute;top: 0px; right: 85px; width: 80px; height: 70px; text-align: center;border: 1px solid #000;border-top: none;">
									<strong>客户已收货<br/>未付款签字</strong>
								</div>
								<div style="position: absolute;top: 0px; right: 5px; width: 80px; height: 80px; text-align: center;">
									<img src="<?php echo $order['qrcode']; ?>" width="80" height="80"/>
								</div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                
            </div>
            
            
        </div>
    </div>
	<script>
		function print_page(){
			try{
				cefAsyncJS.PrintPage("<?php echo url('printing',array('orderid'=>$order['id']),'',true); ?>");
			}catch(e){
				window.print();
			}
		}
	</script>
    <div class="layui-col-md12">
    <button class="layui-btn" id="print" type='button' onClick="print_page()" style="margin-left: 40%;">立即打印</button>              
    </div>
</body>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
</html>
