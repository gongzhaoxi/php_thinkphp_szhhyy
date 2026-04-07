<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/carorder/no_handle.html";i:1748308624;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
               <a href="javascript:void(0)">未处理订单</a>
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
                            <form class="layui-form layui-col-space5" action="" id='form1'>
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" placeholder="请输入名称/编号" autocomplete="off" class="layui-input" value='<?php echo $keyword; ?>'>
                                </div>
								<div class="layui-input-inline layui-show-xs-block">
								    <input type="text" id='start_time' name="start_time" placeholder="下车间时间 - 开始时间" autocomplete="off" class="layui-input" value='<?php echo $start_search; ?>'>
								</div>
								<div class="layui-input-inline layui-show-xs-block">
								    <input type="text" id='end_time' name="end_time" placeholder="下车间时间 - 结束时间" autocomplete="off" class="layui-input" value='<?php echo $end_search; ?>'>
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
                                </div>
                            </form>
                        </div>
                        <blockquote class="layui-elem-quote" style="margin: 0 1%;padding: 8px;">
						    统计:&nbsp;&nbsp;&nbsp;&nbsp;订单数量:<?php echo $statistics['count']; ?> &nbsp;&nbsp;&nbsp;&nbsp;面积:<?php echo $statistics['area']; ?>
						</blockquote>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>                                       
                                        <th>订单编号</th>
                                        <th>经销商</th>
                                        <th>下单时间</th>
                                        <th>数量</th>
                                        <th>面积</th>
                                        <th>下车间时间</th>
                                        <th>要求交货时间</th>                                       
                                        <th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['number']; ?></td>
                                        <td><?php echo $v['dealer']; ?></td>
                                        <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php if($v['car_time'] == 0): ?>暂无<?php else: ?><?php echo date('Y/m/d',$v['car_time']); endif; ?></td>
                                        <td><?php echo $v['end_time']; ?></td>
                                        <td class="td-manage">
                                            <a title="查看" href="<?php echo url('readOrder',array('id'=>$v['id'])); ?>?<?php echo $getstr; ?>">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                            </a>
                                            <a title="返回营运" href="javascript:void(0)" onclick="back(<?php echo $v['id']; ?>)">
                                                <i class=" layui-icon">&#xe633;</i></a>
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
        </div><iframe name="imgiframe" width="0" height="0"></iframe>
    </body>
	<script src="/static/js/jquery.min.js" type="text/javascript"></script>
	<script src="/static/js/xcity.js" type="text/javascript"></script>
    <script>layui.use(['laydate', 'form','table','code'],
        function() {
            // var laydate = layui.laydate;
            // var  form = layui.form;
            // $ = layui.jquery;
			
			$ = layui.jquery;
			var laydate = layui.laydate;
			form = layui.form;
			var table = layui.table;
			layui.code();
             //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('order/financeNoHandle'); ?>",{id:1},function(obj){                         
                    $('#finance-no-handle',parent.document).text(obj.data);                      
                 },'json');
                 
                 $.post("<?php echo url('carorder/noHandleCount'); ?>",{id:1},function(obj){                         
                    $('#no-handle-count',parent.document).text(obj.data);                      
                 },'json');
				 
				 $('#export_price2').click(function(){
				     var start = $('#start_time').val();
				     var end = $('#end_time').val();
				     var keword = $('#keyword').val();
				 										 var keword = $('#dealer_id').val();
				     var sales_name = $('#sales_name').val();
				     var province = $('#province option:selected').val();
				     var city = $('#city option:selected').val();
				     var area = $('#city option:selected').val();
					 if(start=='' || end==''){
						 layer.msg('开始时间与结束时间不可为空',{icon:2});
						 return;
					 }
				     $('#form1').attr('action',"<?php echo url('exportPrice'); ?>")
				     $('#form1').submit();
				     $('#form1').attr('action',"")
				 })
				 
				 //执行一个laydate实例
				 laydate.render({
				     elem: '#start_time' //指定元素
				 });
				 
				 //执行一个laydate实例
				 laydate.render({
				     elem: '#end_time' //指定元素
				 });
            })

        });
        
 function back(id){
            layer.confirm('确认要返回营运吗？',
            function(index) {
                ajaxform("<?php echo url('finance/back'); ?>",{id:id},1);
            });
       }   
    
        
    </script>

</html>