<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:105:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/allorder/delivery.html";i:1635496791;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    
    <body>
         <div class="x-nav">
            <span class="layui-breadcrumb">
               <a href="javascript:void(0)">配送批次订单</a>
            </span> 
            
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
            </a>
        </div>
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            
                        </div>
                        <div class="layui-card-header">
                            <form class="layui-form layui-col-space5" id="form1">
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="number" placeholder="订单号" autocomplete="off" class="layui-input" value="<?php echo $number; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="starttime" id="starttime" placeholder="送货开始时间" autocomplete="off" class="layui-input" value="<?php echo $start_time; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="endtime" id="endtime" placeholder="送货结束时间" autocomplete="off" class="layui-input" value="<?php echo $end_time; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="status">
                                        <option value=""  <?php if($status == ''): ?>selected<?php endif; ?>>全部状态</option>
                                        <option value="0" <?php if($status == '0'): ?>selected<?php endif; ?>>待配送</option>                                       
                                        <option value="1" <?php if($status == '1'): ?>selected<?php endif; ?>>配送中</option>
                                        <option value="2" <?php if($status == '2'): ?>selected<?php endif; ?>>已完成</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <a class="layui-btn layui-btn-normal" href="javascript:void(0)" id="export-btn">
                                        导出数据
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>                                       
                                        <th>配送单号</th>
                                        <th width="30%">包含订单号</th>
                                        <th>送货时间</th>
                                        <th>产品数量</th>
                                        <th>进度/修改</th>
                                        <th>配送方式</th>
                                        <th>状态</th>
                                        <th>操作</th>
    
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['snumber']; ?></td>
                                        <td><?php echo $v['all_number']; ?></td>
                                        <td><?php echo $v['send_date']; ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td>
                                            <a href="javascript:void(0)" onclick="xadmin.open('查看详细', '<?php echo url('readDelivery',array('id'=>$v['id'])); ?>', 1200, 700)">查看</a>&nbsp;&nbsp;
                                            <a href="javascript:void(0)" onclick="xadmin.open('查看详细', '<?php echo url('editDelivery',array('id'=>$v['id'])); ?>', 1200, 700)">修改</a>
                                            </td>
                                        <td><?php echo $send_type[$v['is_send']]; ?></td>
                                        <td><?php echo $send_status[$v['status']]; ?></td>
                                        <td>
                                            <?php if($v['is_check'] == 1): ?>
                                            <a title="打印" onclick="xadmin.open('打印配送单', '<?php echo url('printDelivery',array('id'=>$v['id'])); ?>',1200, 600)" href="javascript:;">
                                                <i class="icon iconfont">&#xe6c9;</i>
                                            </a>
                                            <?php endif; if($v['is_check'] == 0): ?>
                                            <a title="删除" onclick="delivery_del(this,<?php echo $v['id']; ?>)" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body ">
                            <div class="page">
                                   <?php echo $page; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            $ = layui.jquery;
            
            //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('allorder/waitDeliveryCount'); ?>",{id:1},function(obj){                         
                    $('#wait-delivery-count',parent.document).text(obj.data);                      
                 },'json');
                 
                 $.post("<?php echo url('allorder/deliveryCount'); ?>",{id:1},function(obj){                         
                    $('#finance-delivery',parent.document).text(obj.data);                      
                 },'json');


            })
            $('#export-btn').click(function () {
                var start = $('#starttime').val();
                var end = $('#endtime').val();
                var status = $('select[name=status]').find('option:selected').val();console.log(status);
                if(start == '' && end == '' && status == ''){
                    layer.msg('至少选择一个筛选条件',{icon:2});
                    return;
                }
                $('#form1').attr('action',"<?php echo url('exportSend'); ?>");
                $('#form1').submit();
                $('#form1').attr('action','');
            })
            //执行一个laydate实例
            laydate.render({
                elem: '#starttime' //指定元素
            });
            laydate.render({
                elem: '#endtime' //指定元素
            });


        });

        /*删除*/
        function delivery_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('delDelivery'); ?>",{id:id},1);
            });
        }

    </script>

</html>