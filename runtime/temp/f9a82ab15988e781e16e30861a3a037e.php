<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/production/process.html";i:1757168266;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
	<div class="layui-card">
		<div class="layui-card-body">
			<table id="dataTable" lay-filter="dataTable"></table>
		</div>
	</div>
	<script type="text/html" id="toolbar">
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="start"><i class="layui-icon layui-icon-add-1"></i>流程开始</button>
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="end"><i class="layui-icon layui-icon-add-1"></i>流程结束</button>
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="report"><i class="layui-icon layui-icon-add-1"></i>修改</button>
		<button class="layui-btn layui-btn-danger layui-btn-sm" lay-event="remove"><i class="layui-icon layui-icon-delete"></i>撤消</button>
		<button type="button" onclick="location.reload()"  class="layui-btn  layui-btn-primary"><i class="layui-icon">&#xe9aa;</i>刷新</button>
	</script>
	<script type="text/html" id="options">
		<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
		<button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
	</script>	
    </body>
    <script>
	layui.use(['table', 'form', 'element', 'jquery'], function() {
		let table = layui.table;
		let form = layui.form;
		let $ = layui.jquery;	
		
		table.render({
			elem: '#dataTable',
			url: '<?php echo url('process'); ?>',
			page: false,
			limit: 1000,
			limits:[20,40,60,80,100],
			height:'full-50',	
			where: {'order_id':<?php echo $order_id; ?>},	
			cols: [[
				{type: 'checkbox'},
				{field: 'process_name',title: '工序名称',align: 'center'},
				{field: 'start_date',title: '开始日期',align: 'center'},
				{field: 'end_date',title: '结束日期',align: 'center'},
				{field: 'start_worker',title: '报工开始人',align: 'center'},
				{field: 'end_worker',title: '报工结束人',align: 'center'},
				//{title: '操作',toolbar: '#options',align: 'center',width: 130,unresize:true}
			]],
			cellMinWidth: 100,
			//skin: 'line',
			toolbar: '#toolbar',
			defaultToolbar: false
		});

		table.on('sort(dataTable)', function(obj){
			table.reload('dataTable', {
				initSort: obj,
				where: {
					sort: obj.field,
					order: obj.type
				}
			},true);
		});

		table.on('tool(dataTable)', function(obj) {
			if (obj.event === 'remove') {
				layer.confirm('确定要删除这些工序？',
					function(index) {
						ajaxform("<?php echo url('del'); ?>",{ids:[obj.data['id']]},1);
					}
				);				
				
			} else if (obj.event === 'edit') {
				xadmin.open('下达生产-智能推荐流程','<?php echo url('edit'); ?>?id='+obj.data['id'],$(window).width()*0.8, 650);
			}
		});

		table.on('toolbar(dataTable)', function(obj) {
			if (obj.event === 'add') {
				xadmin.open('新增工序','<?php echo url('add'); ?>',700, 650);
			}else if (obj.event === 'refresh') {
				table.reload('dataTable');
			}else if (obj.event === 'batchRemove') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				var ids = [];
				for(var i in checkStatus.data) {
					ids.push(checkStatus.data[i].id);
				}
				layer.confirm('确定要撤消排产？',
					function(index) {
						ajaxform("<?php echo url('del'); ?>",{ids:ids},1);
					}
				);
			}else if (obj.event === 'start') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				if(checkStatus.data[0].start_date){
					layer.msg('工序已上报只能修改，不能重复上报。');
					return false;
				}
				ajaxform("<?php echo url('start'); ?>",{id:checkStatus.data[0].id},1);
			}else if (obj.event === 'end') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				if(checkStatus.data[0].end_date){
					layer.msg('工序已上报只能修改，不能重复上报。');
					return false;
				}
				ajaxform("<?php echo url('end'); ?>",{id:checkStatus.data[0].id},1);
			}else if (obj.event === 'report') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				xadmin.open('修改报工','<?php echo url('report'); ?>?id='+checkStatus.data[0].id,$(window).width()*0.95, $(window).height()*0.95);
			}else if (obj.event === 'remove') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				ajaxform("<?php echo url('report'); ?>",{id:checkStatus.data[0].id,'start_date':'','end_date':'','start_worker':'','end_worker':''},1);
			}
		});

		form.on('submit(query)', function(data) {
			table.reload('dataTable', {
				where: data.field,
				page:{curr: 1}
			})
			return false;
		});
	})
</script>
</html>