<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/worker_group/index.html";i:1756306362;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="add"><i class="layui-icon layui-icon-add-1"></i>新增</button>
		<button type="button" class="layui-btn layui-btn-sm" lay-event="edit"><i class="layui-icon">&#xe642;</i>编辑</button>
		<button class="layui-btn layui-btn-danger layui-btn-sm" lay-event="batchRemove"><i class="layui-icon layui-icon-delete"></i>删除</button>
		<button type="button" onclick="location.reload()"  class="layui-btn  layui-btn-primary"><i class="layui-icon">&#xe9aa;</i>刷新</button>
	</script>
	<script type="text/html" id="options">
		<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
		<button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
	</script>	
    </body>
    <script>
	layui.use(['table', 'form', 'jquery'], function() {
		let table = layui.table;
		let form = layui.form;
		let $ = layui.jquery;
		table.render({
			elem: '#dataTable',
			url: '<?php echo url('index'); ?>',
			page: true,
			limit: 20,
			limits:[20,40,60,80,100],
			height:'full-60',			
			cols: [[
				{type: 'checkbox'},
				{field: 'name',title: '班组名称',align: 'center'},
				{field: 'department',title: '班组所属部门',align: 'center'},
				{field: 'process_name',title: '工序',align: 'center'},
				{field: 'monitor_name',title: '班长',align: 'center'},
				{title: '操作',toolbar: '#options',align: 'center',width: 130,unresize:true}
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
				xadmin.open('修改工序','<?php echo url('edit'); ?>?id='+obj.data['id'],700, 650);
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
				layer.confirm('确定要删除这些工序？',
					function(index) {
						ajaxform("<?php echo url('del'); ?>",{ids:ids},1);
					}
				);
			}else if (obj.event === 'edit') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				xadmin.open('修改工序','<?php echo url('edit'); ?>?id='+checkStatus.data[0].id,700, 650);
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