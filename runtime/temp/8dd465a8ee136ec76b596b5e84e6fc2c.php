<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/allorder/print_buy.html";i:1635496792;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    body{background-color: #fff;}
    .center{ text-align: center;margin: 10px 0; font-size: 28px; color: #000; font-weight: bold;}
    .title{ display: inline-block;width: 32%; margin: 5px 0;}
    .all{ display: inline-block;width: 10%;}
    td{text-align: center;}
    .layui-table td{ color: #000;border-color:#000; padding:10px 0px;}
    .layui-col-md12{ padding-top: 0px; padding-bottom: 0px; }


</style>   
<body>
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <h3 class="center">深圳市恒辉窗花饰制品有限公式(订购清单)</h3>
                <div class="title">客户名称:<?php echo $order['dealer']; ?></div>
                <div class="title">客户电话:<?php echo $order['phone']; ?></div>
                <div class="title">订单日期:<?php echo date('Y-m-d',$order['addtime']); ?></div>
                <div class="title">送货地址:<?php echo $order['send_address']; ?></div>
                <div class="title">订单编号:<?php echo $order['number']; ?></div>
            </div>
            <div class="layui-col-md12">
                <table class="layui-table layui-form" style='margin-bottom:0px'>
                        <tr>                                       
                            <!--<td>栏号</td>-->
                            <td width="3%">编号</td>
                            <td width="5%">名称</td>
                            <td width="18%">材质 - 花类</td>
                            <td width="12%">铝型/花件颜色</td>
                            <td width="3%">单位</td>
                            <td width="5%">宽</td>
                            <td width="5%">高</td>
                            <td width="3%">个数</td>
                            <td width="5%">面积</td>
                            <td width="7%">单价</td>
                            <td width="7%">折后价</td>
                            <td width="10%">总金额</td>
                            <td width="9%">备注</td>
                        </tr>
                        <?php if(is_array($product) || $product instanceof \think\Collection || $product instanceof \think\Paginator): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $v['name']; ?></td>
                            <td><?php echo $v['material']; ?> - <?php echo $v['flower_type']; ?></td>
                            <td><?php echo $v['color_name']; ?></td>
                            <td>㎡</td>
                            <td><?php echo $v['all_width']; ?></td>
                            <td><?php echo $v['all_height']; ?></td>
                            <td><?php echo $v['count']; ?></td>
                            <td><?php echo $v['area']; ?></td>
                            <td><?php echo $v['price']; ?></td>
                            <td><?php echo $v['rebate_price']; ?></td>
                            <td><?php echo $v['all_price']; ?></td>
                            <td><?php echo $v['note']; ?></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; if(is_array($material) || $material instanceof \think\Collection || $material instanceof \think\Paginator): $i = 0; $__LIST__ = $material;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $v['name']; ?></td>
                            <td><?php echo $v['type']; ?></td>
                            <td><?php echo $v['color']; ?></td>
                            <td><?php echo $v['unit']; ?></td>
                            <td><?php echo $v['width']; ?></td>
                            <td><?php echo $v['height']; ?></td>
                            <td><?php echo $v['count']; ?></td>
                            <td><?php echo $v['area']; ?></td>
                            <td><?php echo $v['price']; ?></td>
                            <td><?php echo $v['rebate_price']; ?></td>
                            <td><?php echo $v['all_price']; ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <tr>
                            <td>备注</td>
                            <td colspan="12"><?php echo $order['note']; ?></td>
                        </tr>
                </table>
               <table class="layui-table layui-form" style='margin:0'>
                        
                        <tr>
                            <td>总金额</td>
                            <td><?php echo $order['total_price']; ?></td>
                            <td>总面积</td>
                            <td><?php echo $order['area']; ?></td>
                            <td>总数量</td>
                            <td><?php echo $order['count']; ?></td>
                            <td>已付</td>
                            <td><?php echo $order['have_pay']; ?></td>
                            <td>未付</td>
                            <td><?php echo $order['no_pay']; ?></td>
                            <td></td>
                        </tr>
                </table>
            </div>
            <div style="width: 80%;float: left">
                <h4>特别说明</h4>
                <p>1.敬请核对价格表中各种型号及结算方式，此单一旦签字认可及作为欠条凭证，汇款账户如下：户名：唐萍，中国农业银行深圳罗岗支行银行卡号：6228 4301 2001
                4745 811；中国建设银行深圳罗岗支行卡号：6227007200240598021
                </p>
                <p>2.客户核对顶订单后，给予50%订金，订货生效，订单核对并确认3小时后不可修改，如需修改，费用另计。</p>
                <p>咨询电话：4007776388 25735065 传真：0755-25736017 业务代表：人员</p>
            </div>    
            <div style="width: 15%;float: left">
                <h3>客户已收货未付款签字</h3>
            </div>
            
        </div>
    </div>

    <div class="layui-col-md12">
    <button class="layui-btn" id="print" type='button' onClick="window.print()" style="margin-left: 40%;">立即打印</button>              
    </div>
</body>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
  
</script>
</html>
