<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/finance/export_bill.html";i:1635496793;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-form-item .layui-input-inline {
        float: left;
        width: 110px;
        margin-right: 10px;
    }
</style>
        <div class="layui-fluid" style="width: 80%">
            <div class="layui-row">
                <form class="layui-form" id="form1" action="<?php echo url('exportBill'); ?>" method="post">
                    <div class="layui-form-item" style="margin-left: 3%">
                        <div class="layui-form-item">
                            <label  class="layui-form-label">
                                选择时间(开始)
                            </label>
                            <div class="layui-input-inline">
                                <input type="text" id="start_time" name="start_time"  class="layui-input" placeholder="请选择日期" autocomplete="off">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label  class="layui-form-label">
                                选择时间(结束)
                            </label>
                            <div class="layui-input-inline">
                                <input type="text" id="end_time" name="end_time" class="layui-input" placeholder="请选择日期" autocomplete="off">
                            </div>
                        </div>
<!--                        <div class="layui-input-inline">-->
<!--                            <input type="checkbox" class="layui-inline" name="send_order" lay-skin="primary" title="已配送订单"   value="1">-->
<!--                        </div>-->
<!--                        <div class="layui-input-inline">-->
<!--                            <input type="checkbox" class="layui-inline" name="sign_order" lay-skin="primary" title="已签收订单"   value="2">-->
<!--                        </div>-->
<!--                        <div class="layui-input-inline">-->
<!--                            <input type="checkbox"  name="into_order" lay-skin="primary" title="已入库订单" value="3">-->
<!--                        </div>-->
<!--                        <div class="layui-input-inline">-->
<!--                            <input type="checkbox" id="all_dealer" name="all_dealer" lay-skin="primary" title="导出全部经销商" >-->
<!--                        </div>-->
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-inline" style="width: 62%;margin-left: 10px">
                            <div id="demo1" class="layui-input-inline" style="width: 100%"></div>
                        </div>
                    </div>

                    <p class="x-red">注1:请选择经销商名称</p>
                    <p class="x-red">注2:至少选择一个时间</p>
                    <input name="dealer_id" id="dealer_id" type="hidden">
                    <div class="layui-form-item">
                        <button class="layui-btn" id="export-btn" type="button" style="margin: 20px 0 0 30%">立即导出excel报表</button>
                    </div>
        </form>
        </div>
        </div>
<!--        <script src="/static/js/jquery.min.js" type="text/javascript"></script>-->
<script src="/static/js/xm-select.js" type="text/javascript"></script>
<script>layui.use(['form', 'layer','laydate','upload'],
            function() {
                var laydate = layui.laydate;
                var laydate = layui.laydate;
                $ = layui.jquery;
                laydate.render({
                    elem:'#start_time'
                })
                laydate.render({
                    elem:'#end_time'
                })

                //经销商多选
                var demo1 = xmSelect.render({
                    el: '#demo1',
                    filterable: true,
                    autoRow: true,
                    paging: true,
                    pageSize: 10,
                    data: [
                        <?php if(is_array($dealer) || $dealer instanceof \think\Collection || $dealer instanceof \think\Paginator): $i = 0; $__LIST__ = $dealer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        {name: "<?php echo $v['name']; ?>", value: <?php echo $v['id']; ?>},
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    ]
                })

                $(function () {
                    $('#export-btn').click(function () {
                        var selectArr = demo1.getValue('value');//所选的经销id数组
                        if(selectArr.length == 0){
                            layer.msg("请选择经销商",{icon:2});
                            return;
                        }
                        $('#dealer_id').val(selectArr.toString());
                        var stime = $('#start_time').val();
                        var end = $('#end_time').val();
                        if(stime == '' && end == ''){
                            layer.msg('时间必须选择一个',{icon:2});
                            return;
                        }
                        layer.load('',{time:15*1000});
                        $('#form1').submit();

                    });
                })
            });
        </script>
        <script>

        </script>

    </body>

</html>