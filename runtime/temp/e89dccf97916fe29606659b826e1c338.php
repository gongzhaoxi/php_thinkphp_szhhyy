<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:111:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/finance/export_payment.html";i:1635496794;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    p{height: 30px;line-height: 30px}
</style>
        <div class="layui-fluid" style="width: 70%">
            <div class="layui-row">
                <form class="layui-form" id="form1" action="<?php echo url('exportPayment'); ?>" method="post">
                    <div class="layui-form-item">
                        <label  class="layui-form-label">
                            收款日期(开始)
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="start_time" name="start_time"  class="layui-input" placeholder="请选择日期" autocomplete="off">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label  class="layui-form-label">
                            收款日期(结束)
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="end_time" name="end_time" class="layui-input" placeholder="请选择日期" autocomplete="off">
                        </div>
                    </div>
                    <div class="layui-form-item" style="margin-left: 3%">
                        <div class="layui-input-inline">
                            <input type="checkbox" id="all_dealer" name="all_dealer" lay-skin="primary" title="导出全部经销商" checked>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-inline" style="width: 62%;margin-left: 10px">
                            <select name="dealer_id" id="dealer_id" lay-search="" >
                                <option value="">请输入经销商名称</option>
                                <?php if(is_array($dealer) || $dealer instanceof \think\Collection || $dealer instanceof \think\Paginator): $i = 0; $__LIST__ = $dealer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <p class="x-red">注1:如需导出单个经销商,请选择经销商名称</p>
                    <p class="x-red">注2:此次导出,只导已有收款记录订单</p>
                    <p class="x-red">注3:导出全部经销商跟单个经销商都选择时，只导出单个经销商</p>
                    <div class="layui-form-item">
                        <button class="layui-btn" id="export-btn" type="button" style="margin: 20px 0 0 30%">立即导出excel报表</button>
                    </div>
        </form>
        </div>
        </div>
        <script src="/static/js/jquery.min.js" type="text/javascript"></script>
        <script>layui.use(['form', 'layer','laydate','upload'],
            function() {
                var laydate = layui.laydate;
                laydate.render({
                    elem:'#start_time'
                })
                laydate.render({
                    elem:'#end_time'
                })
            });
        </script>
        <script>
            $(function () {
                $('#export-btn').click(function () {
                    var stime = $('#start_time').val();
                    var end = $('#end_time').val();
                    if(stime == '' && end == ''){
                        layer.msg('时间必须选择一个',{icon:2});
                        return;
                    }
                    layer.load('',{time:20*1000});
                    $('#form1').submit();
                });
            })
        </script>

    </body>

</html>