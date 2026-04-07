<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:111:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/allorder/print_delivery.html";i:1635496791;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .center{ text-align: center;margin: 10px 0;}
    .title{ display: inline-block;width: 19.7%;text-align: left;height: 30px }
    .layui-table td, .layui-table th{padding:5px 2px;}
    .layui-table td,.layui-table th{ border-color: #000000;}
    .layui-table{ color: #000000;}
</style>   
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <div class="layui-col-md12">
                <h3 class="center">广东恒辉窗花制品有限公司（配送清单）</h3>
                <div class="title">配送日期:<?php echo $send['send_date']; ?></div>
                <div class="title" style="width: 35%">配送订单号:<?php echo $send['snumber']; ?></div>
                <div class="title"><?php echo $field1; ?>:<?php echo $type_name; ?></div>
                <div class="title"><?php echo $field2; ?>:<?php echo $type_value; ?></div>
                <div class="title">配送订单数量:<?php echo count($list) ?></div>
                <div class="title" style="width: 35%">本次配送订单总面积:<?php echo $total_area; ?></div>
                <div class="title" >总金额:<?php echo $total_price; ?></div>
            </div>
            <div class="layui-col-md12">
                <table class="layui-table layui-form">
                    <thead>
                        <tr>                                       
                            <th>顺序</th>
                            <th>订单号</th>
                            <th>客户名称</th>
                            <!--<th>地址</th>-->
                            <th>总面积</th>
                            <th>总额</th>
                            <th>已收款</th>
                            <th>余款</th>
                            <th>收款情况</th>
                         
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $v['sort']; ?></td>
                            <td><?php echo $v['number']; ?></td>
                            <td><?php echo $v['dealer']; ?></td>
                            <!--<td><?php echo mb_substr($v['address'],0,8); if(mb_strlen($v['address']) > 15): ?>...<?php endif; ?></td>-->
                            <td><?php echo $v['area']; ?></td>
                            <td><?php echo $v['total_price']; ?></td>
                            <td><?php echo $v['have_pay']; ?></td>
                            <td><?php echo round($v['total_price']-$v['have_pay']-$v['finance_rebate_price'],2); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                
            </div>
           
            
            
        </div>
    </div>
    <div class="layui-col-md12">
        <button class="layui-btn" id="print" type='button' style="margin-left: 40%;">立即打印</button>
    </div>
</body>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
    $('#print').click(function () {
        $(this).hide();
        window.print();
        $(this).show();
    });
</script>
</html>
