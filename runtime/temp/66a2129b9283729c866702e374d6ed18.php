<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/dict_type/index.html";i:1757515100;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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

	<div class="layui-col-md4">
		<div class="layui-card">
			<div class="layui-card-body">
				<table id="dict-type-table" lay-filter="dict-type-table"></table>
			</div>
		</div>
	</div>
	<div class="layui-col-md8">
		<div class="layui-card">
			<div class="layui-card-body">
				<svg class="empty" style="margin-top: 50px;margin-left: 220px;margin-bottom: 80px;" width="184" height="152"
				 viewBox="0 0 184 152" xmlns="http://www.w3.org/2000/svg">
					<g fill="none" fillRule="evenodd">
						<g transform="translate(24 31.67)">
							<ellipse fillOpacity=".8" fill="#F5F5F7" cx="67.797" cy="106.89" rx="67.797" ry="12.668"></ellipse>
							<path d="M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z"
							 fill="#AEB8C2"></path>
							<path d="M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z"
							 fill="url(#linearGradient-1)" transform="translate(13.56)"></path>
							<path d="M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z" fill="#F5F5F7"></path>
							<path d="M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z"
							 fill="#DCE0E6"></path>
						</g>
						<path d="M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z"
						 fill="#DCE0E6"></path>
						<g transform="translate(149.65 15.383)" fill="#FFF">
							<ellipse cx="20.654" cy="3.167" rx="2.849" ry="2.815"></ellipse>
							<path d="M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z"></path>
						</g>
					</g>
				</svg>
				<table id="dataTable" lay-filter="dataTable"></table>
			</div>
		</div>
	</div>
	<script type="text/html" id="toolbar">
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="add"><i class="layui-icon layui-icon-add-1"></i>新增</button>
		<button type="button" class="layui-btn layui-btn-sm" lay-event="edit"><i class="layui-icon">&#xe642;</i>编辑</button>
		<button type="button" onclick="location.reload()"  class="layui-btn  layui-btn-primary"><i class="layui-icon">&#xe9aa;</i>刷新</button>
	</script>
	<script type="text/html" id="options">
		<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
		<button class="layui-btn layui-btn-warming layui-btn-xs" lay-event="data"><i class="layui-icon layui-icon-transfer"></i></button>
	</script>	
	<script type="text/html" id="dict-data-toolbar">
		<button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="add"><i class="layui-icon layui-icon-add-1"></i>新增</button>
		<button type="button" class="layui-btn layui-btn-sm" lay-event="edit"><i class="layui-icon">&#xe642;</i>编辑</button>
		<button class="layui-btn layui-btn-danger layui-btn-sm" lay-event="batchRemove"><i class="layui-icon layui-icon-delete"></i>删除</button>
		<button type="button" class="layui-btn  layui-btn-primary" lay-event="refresh"><i class="layui-icon">&#xe9aa;</i>刷新</button>
	</script>	
	<script type="text/html" id="dict-data-bar">
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
			elem: '#dict-type-table',
			url: '<?php echo url('index'); ?>',
			page: true,
			limit: 20,
			limits:[20,40,60,80,100],
			height:'full-60',			
			cols: [[
				{type: 'checkbox'},
				{field: 'id',title: 'id',align: 'center'},
				{field: 'name',title: '字典名称',align: 'center'},
				{title: '操作',toolbar: '#options',align: 'center',width: 130,unresize:true}
			]],
			cellMinWidth: 100,
			//skin: 'line',
			toolbar: '#toolbar',
			defaultToolbar: false
		});

		table.on('sort(dict-type-table)', function(obj){
			table.reload('dict-type-table', {
				initSort: obj,
				where: {
					sort: obj.field,
					order: obj.type
				}
			},true);
		});
		var type_id ;
		window.renderData = function(id) {
			type_id = id;
			$(".empty").hide();

			table.render({
				elem: '#dataTable',
				url: "<?php echo url('dictData/index'); ?>?type_id="+type_id,
				page: true,
				limit: 20,
				limits:[20,40,60,80,100],
				height:'full-60',
				cols: [[
					{type: 'checkbox'},
					{title: '数据名称',field: 'name',align: 'center'},
					{title: '排序',field: 'sort',align: 'center'},
					{field: 'status',title: '状态',align: 'center',templet: function(d){return d.status==1?'启用':'禁用';}},
					{title: '操作',toolbar: '#dict-data-bar',align: 'center',width: 180}
				]],
				
				toolbar: '#dict-data-toolbar',
				defaultToolbar: []
			});
		}


		table.on('tool(dict-type-table)', function(obj) {
			if (obj.event === 'remove') {
				layer.confirm('确定要删除这些工序？',
					function(index) {
						ajaxform("<?php echo url('del'); ?>",{ids:[obj.data['id']]},1);
					}
				);				
				
			} else if (obj.event === 'edit') {
				xadmin.open('修改工序','<?php echo url('edit'); ?>?id='+obj.data['id'],700, 650);
			}else if (obj.event === 'data') {
				window.renderData(obj.data['id'])
			}
		});

		table.on('toolbar(dict-type-table)', function(obj) {
			if (obj.event === 'add') {
				xadmin.open('新增工序','<?php echo url('add'); ?>',700, 650);
			}else if (obj.event === 'refresh') {
				table.reload('dict-type-table');
			}else if (obj.event === 'batchRemove') {
				var checkStatus = table.checkStatus('dict-type-table');
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
				var checkStatus = table.checkStatus('dict-type-table');
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
			table.reload('dict-type-table', {
				where: data.field,
				page:{curr: 1}
			})
			return false;
		});
		
		table.on('tool(dataTable)', function(obj) {
			if (obj.event === 'remove') {
				layer.confirm('确定要删除这些字典数据？',
					function(index) {
						ajaxform("<?php echo url('dictData/del'); ?>",{ids:[obj.data['id']]},1);
					}
				);				
				
			} else if (obj.event === 'edit') {
				xadmin.open('修改字典数据','<?php echo url('dictData/edit'); ?>?id='+obj.data['id'],700, 650);
			}else if (obj.event === 'data') {
				window.renderData(obj.data['id'])
			}
		});
		table.on('toolbar(dataTable)', function(obj) {
			if (obj.event === 'add') {
				xadmin.open('新增字典数据','<?php echo url('dictData/add'); ?>?type_id='+type_id,700, 650);
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
				layer.confirm('确定要删除这些字典数据？',
					function(index) {
						ajaxform("<?php echo url('dictData/del'); ?>",{ids:ids},1);
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
				xadmin.open('修改字典数据','<?php echo url('dictData/edit'); ?>?id='+checkStatus.data[0].id,700, 650);
			}
		});		
		
		
		
	})
</script>
</html>