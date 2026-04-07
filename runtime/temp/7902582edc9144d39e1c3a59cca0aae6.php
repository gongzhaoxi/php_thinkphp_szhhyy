<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:110:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/allorder/read_delivery.html";i:1731654538;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .alltable{height:232px;overflow-y: scroll;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form">

                <div class="layui-col-xs12">
                    
                    <div class="layui-form-item" style="padding-top: 10px;">
                        <blockquote class="layui-elem-quote">
                            <div class="layui-inline">
                            <input type="text" name="search" placeholder="输入订单号/经销商名称" class="layui-input" style="display:inline;width: 220px;">
                            <button class="layui-btn" id="add">搜索</button>
                            </div>
                            <div class="layui-inline">
                                <?php if($send['is_send'] == 0): ?>
                                <p style='font-size:13px;'>自送   &nbsp;&nbsp;司机姓名：<?php echo $send['driver_name']; ?>    司机电话：<?php echo $send['driver_phone']; ?></p>
                                <?php elseif($send['is_send'] == 3): ?>
                                <p style='font-size:13px;'>请车   &nbsp;&nbsp;司机姓名：<?php echo $send['driver_name']; ?>    司机电话：<?php echo $send['driver_phone']; ?></p>
                                <?php elseif($send['is_send'] == 1): ?>
                                <p style='font-size:13px;'>物流    &nbsp;&nbsp;物流公司：<?php echo $send['logistics_name']; ?>   物流电话：<?php echo $send['logistics_numbers']; ?></p>
                                <?php elseif($send['is_send'] == 4): ?>
                                <p style='font-size:13px;'>快递    &nbsp;&nbsp;物流公司：<?php echo $send['logistics_name']; ?>   物流电话：<?php echo $send['logistics_numbers']; ?></p>
                                <?php else: ?>
                                <p style='font-size:13px;'>自提    &nbsp;&nbsp;</p>
                                <?php endif; ?>
                            </div>
                        </blockquote>    
                    </div> 
                    <div class="select-table">
                        <table class="layui-table layui-form" lay-skin="line">
                        <thead>
                            <tr>                                       
                                <th>序号</th>
                                <th>订单号</th>
                                <th>经销商</th>
                                <th>送货地址</th>
                                <th>数量</th>
                                <th>预计到达时间</th>
                                <th>签收时间</th>
                                <th>收款情况</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $v['sort']; ?></td>
                                <td><?php echo $v['number']; ?></td>
                                <td><?php echo $v['dealer']; ?></td>
                                <td><?php echo $v['send_address']; ?></td>
                                <td><?php echo $v['count']; ?></td>
                                <td><?php echo $v['arrive_time']; ?></td>
                                <td><?php if($v['sign_time'] != 0): ?><?php echo date('Y-m-d H:i:s',$v['sign_time']); endif; ?></td>
                                <td>¥：<?php echo $v['total_price']; ?></br><?php if($v['sign_time'] != ''): ?><?php echo $pay_type[$v['pay_type']]; endif; ?></td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                        </table>
                    </div>
                </div>  

            </form>
        </div> 
    </div>

</body>

</html>