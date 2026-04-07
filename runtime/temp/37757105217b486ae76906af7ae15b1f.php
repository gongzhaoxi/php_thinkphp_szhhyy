<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/carorder/calculate.html";i:1741658482;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    *{ padding: 0px; margin: 0px; }
    ul,li{ list-style:none;}
    .single-head{ width: 100%; height: auto; overflow: hidden; }
    .center{ text-align: center;margin: 10px 0; font-size:16px; color: #000; font-weight: bold;}
    .top{width: 100%;}
    .top table tr td{ padding: 0px 10px;font-size:12px; line-height: 20px;}
    .top_left{ width: 89%; float: left;}
    .top_left ul.a1{width: 100%; float: left; height: 30px;}
    .top_left ul.a1 li{float: left; width: 33%; line-height: 20px; font-size: 14px; color: #000; text-align: left;}
    .top_left ul.a1 li.a2{ width: 66% }
    .top_right{ width: 10%; float: left; padding-right: 1%; text-align:right;}
    .top_right img{ background: #999; width: 65px; height: 65px; }
    .mwpw{ width: 100%;display: flex;flex-wrap: wrap; }
    .gqge{  width:50%;  overflow: hidden; border:1px solid #000;box-sizing:border-box; padding:0.5%;}
    .gqge_left{ float:left; width: 64%;text-align: center;}
    .gqge_left img{max-width:100%;max-height:240px;vertical-align:middle;}
    .gqge_right{ width: 35%;padding: 0.5%;float: left;}
    .gqge_right li{ width: 100%; border:1px solid #000;box-sizing:border-box;line-height:15px;font-size: 12px; color: #000; text-align: center;}
    .gqge_cent{ width: 100%; float: left; font-size: 12px; line-height:15px; color: #000;text-align: left; }
    @media print{
        INPUT {display:none}
        .PageNext{page-break-after:always}
    }
</style>
<div style="padding: 10px;">
    <div class="single-head" style="position: relative;">
		<span style="position: absolute;top: 5px; right: 5px; font-weight: bold;">
			<?php if($order['print_label'] == 0): ?>
			需要打印标签
			<?php else: ?>
			不需要打印标签
			<?php endif; ?>
		</span>
        <h3 class="center">广东恒辉窗花制品有限公司(生产作业单【
         <?php if($order['type'] == 1): ?>常规<?php endif; if($order['type'] == 2): ?>加急<?php endif; if($order['type'] == 3): ?>样板<?php endif; if($order['type'] == 4): ?>返修单<?php endif; if($order['type'] == 5): ?>单剪网<?php endif; if($order['type'] == 6): ?>单切料<?php endif; if($order['type'] == 7): ?>工程<?php endif; if($order['type'] == 8): ?>重做<?php endif; ?>
        】)</h3>
        <div class="top">
            <div class="top_left">
                <ul class="a1">
                    <li>客户名称：<?php echo $order['dealer']; ?></li>
                    <li>下单日期：<?php echo date('Y-m-d',$order['addtime']); ?></li>
                    <li>生产完成日期：<?php echo $order['make_time']; ?></li>
                </ul>
                <ul class="a1">
                    <li class="a2">送货地址：<?php echo $order['send_address']; ?></li>
                    <li>订单编号：<?php echo $order['number']; ?></li>
                </ul>
            </div>
            <div class="top_right"><img src="<?php echo $order['qrcode']; ?>"></div>
        </div>
        <div class="top">
            <table width="100%" border="1" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td width="33%">下单人 ：营运部（工号1007）</td>
                    <td width="33%">审核人 ：算料组（工号1006）</td>
                </tr>
                <tr>
                    <td>总面积：<?php echo $order['area']; ?>m²&nbsp;&nbsp;|&nbsp;&nbsp;总数量：<?php echo $order['count']; ?></td>
                    <td colspan="2">备注：</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php $z=1; if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($z == 1){  ?>
    <table class="mwpw PageNext" border="1" cellspacing="0" cellpadding="0">
    <?php } if($z == 1 || $z == 3){  ?>
         <tr>
         <?php } ?>

             <td class="gqge" style="height: 430px">
                 <div class="gqge_left">
                     <div style="min-height: 250px;">
                         <?php if($v['path']): ?>
                         <img src="<?php echo $v['path']; ?>">
                         <?php elseif($v['structure_pic'] != null): ?>
                         <img src="/upload/<?php echo $v['structure_pic']; ?>">
                         <?php endif; ?>
                     </div>
                     <div class="gqge_cent">
                         <p>型材: <?php echo $v['material']; ?> | 花件：<?php echo $v['flower_type']; ?> | 型材/花件颜色：<?php echo $v['color_name']; ?> | 锁高: <?php echo $v['lock_position']; ?>mm | 单窗面积：<?php echo $v['product_area']; ?>㎡ | 数量：<?php echo $v['count']; ?> | 横间距:<?php echo $v['spacing']; ?> | 竖间距:<?php echo $v['bottom_spacing']; ?> | 右竖间距:<?php echo $v['right_bottom_spacing']; ?>| 下固横间距:<?php echo $v['bottom_fixed_spacing']; ?> | 下固竖间距:<?php echo $v['bottom_vertical_spacing']; ?> | 上下固花件：<?php echo $v['flower_types']; ?></p>
                         <p>备注：<?php echo $v['note']; ?></p>
                         <p>结构ID：<?php echo $v['structure_id']; ?></p>
                         <div style="word-wrap:break-word;display:none;"><?php dump($v['ruler_data']);?></div>
                         <div style="word-wrap:break-word;display:none;"><?php dump($v['calculate_data']);?></div>
                     </div>
                 </div>

                 <div class="gqge_right" style="<?php if(!is_array($v['calculate'])): ?>display:none<?php endif; ?>">
                 <li><strong>开料尺寸</strong></li>
                 <?php if(is_array($v['calculate']) || $v['calculate'] instanceof \think\Collection || $v['calculate'] instanceof \think\Paginator): $i = 0; $__LIST__ = $v['calculate'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v2): $mod = ($i % 2 );++$i;?>
                 <li><?php echo $v2; ?></li>
                 <?php endforeach; endif; else: echo "" ;endif; if($v['is_hand'] == 0): ?>
                 <li><strong><?php echo $v['flower_type']; ?> (高*宽)</strong></li>
                 <li><?php echo $v['flower']; ?></li>
                 <?php endif; if($v['cal_note'] != null): ?>
                 <li style="height: 17px"><?php echo $v['cal_note']; ?></li>
                 <?php endif; ?>
                 <li style="height: 17px"><?php echo $v['flower_types']; ?></li>
                 </div>
             </td>

        <?php if($z == 2 || $z == 4){  ?>
        </tr>
        <?php } if($z == 4){ echo '</table>';} $z++; if($z>4){ $z=1;} endforeach; endif; else: echo "" ;endif; ?>

</div>
<div class="layui-col-md12" id="print" style="margin-left: 40%; display: block;">
    <button class="layui-btn" type="button">立即打印</button>
</div>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
    $(function() {
        var cloneHtml = $('.single-head').clone();
        $('#print').click(function() {
            $('#print').css('display', 'none');
            $(cloneHtml).insertBefore($('.mwpw').eq(0).siblings('.mwpw'));
            window.print();
            $('.single-head').eq(0).siblings('.single-head').remove();
            $('#print').css('display', 'block');
        })
    })
</script>

</body>
</html>
