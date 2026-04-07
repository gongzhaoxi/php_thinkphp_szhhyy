<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:111:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/allorder/wait_delivery.html";i:1758808926;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
               <a href="javascript:void(0)">待发货订单</a>
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
                            <form class="layui-form layui-col-space5" >
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" id="keyword" placeholder="请输入名称/编号" autocomplete="off" class="layui-input" value="<?php echo $search['keyword']; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="starttime" id="starttime" placeholder="下单开始时间" autocomplete="off" class="layui-input" value="<?php echo $search['starttime']; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="endtime" id="endtime" placeholder="下单结束时间" autocomplete="off" class="layui-input" value="<?php echo $search['endtime']; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                                 <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn layui-btn-normal" type='button' id='export_price2'>
                                        导出数据
                                    </button>
                                </div> 								
                                <div class="layui-input-inline" style="float: right">
                                    <button class="layui-btn" onclick="xadmin.open('添加发货单', '<?php echo url('addDelivery'); ?>', 1200, 700)" type='button'>
                                        <i class="layui-icon"></i>添加配送批次单
                                    </button>
									<a class="layui-btn"  href="<?php echo url('printTag'); ?>" target="_blank">
                                        <i class="layui-icon"></i>打印标签
                                    </a>
<!--                                    <button class="layui-btn layui-btn-normal" type='button' onclick="xadmin.open('打印配送批次单', '<?php echo url('printDelivery'); ?>',1200,600)">
                                        <i class="layui-icon"></i>打印配送批次单
                                    </button>-->
                                </div>
                            </form>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>                                       
                                        <th>订单编号</th>
                                        <th>经销商</th>
                                        <th>送货地址</th>
                                        <th>下单时间</th>
                                        <th>数量</th>
                                        <th>面积</th>
                                        <th>要求交货时间</th>
                                        <th>入库时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['number']; ?></td>
                                        <td><?php echo $v['dealer']; ?></td>
                                        <td><?php echo $v['send_address']; ?></td>
                                        <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['end_time']; ?></td>
                                        <td><?php if($v['intime'] != ''): ?><?php echo date('Y-m-d',$v['intime']); else: ?>暂无<?php endif; ?></td>
                                        <td>
                                        <a title="打印订购清单" onclick="xadmin.open('打印订购清单', '<?php echo url('printBuy',array('id'=>$v['id'])); ?>',1200, 600)" href="javascript:;">
                                                <i class="icon iconfont">&#xe6c9;</i>
                                            </a>
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
            
            //执行一个laydate实例
            laydate.render({
                elem: '#starttime' //指定元素
            });
            laydate.render({
                elem: '#endtime' //指定元素
            });
			$('#export_price2').click(function(){
				var starttime = $('#starttime').val();
				var endtime = $('#endtime').val();
				var keyword = $('#keyword').val();

				window.open('<?php echo url('exportWaitDelivery'); ?>?starttime='+starttime+'&endtime='+endtime+'&keyword='+keyword);
			 })
        });

    
        
    </script>

</html>