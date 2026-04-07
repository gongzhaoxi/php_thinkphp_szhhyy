<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/production/stat.html";i:1758034904;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
			<div class="layui-tab">
				<ul class="layui-tab-title">
					<a href="<?php echo url('stat'); ?>?production_status=2"><li class="<?php if($production_status == 2): ?>layui-this<?php endif; ?>">入库</li></a>
					<a href="<?php echo url('stat'); ?>?production_status=1"><li class="<?php if($production_status == 1): ?>layui-this<?php endif; ?>">即时</li></a>
				</ul>
			</div>
		</div>
		<div class="layui-card-body">
			<form class="layui-form" action="">
				<div class=" layui-inline">
					<input type="text" name="number" value="" placeholder="订单号" class="layui-input" >
				</div>	
				<div class=" layui-inline">
					<input type="text" id="store_date" name="store_date" class="layui-input" placeholder="入库日期" autocomplete="off">
				</div>	
				<div class="layui-inline">
					<button class="layui-btn  layui-btn-primary" lay-submit lay-filter="query"><i class="layui-icon layui-icon-search"></i>查询</button>
				</div>
				<div class="layui-inline">
					<button class="layui-btn  layui-btn-primary" type="button" id="export" lay-filter="export"><i class="layui-icon layui-icon-export"></i>导出</button>
				</div>				
			</form>
		</div>
		<div class="layui-card-body">
			<table id="dataTable" lay-filter="dataTable"></table>
		</div>
	</div>	
    </body>
    <script>
	layui.use(['table', 'form', 'element', 'jquery','laydate'], function() {
		let table = layui.table;
		let form = layui.form;
		let $ = layui.jquery;	
		let laydate = layui.laydate;	
		laydate.render({
			elem:'#store_date',
			range:'至'
		})			
		table.render({
			elem: '#dataTable',
			url: '<?php echo url('stat'); ?>?production_status=<?php echo $production_status; ?>}',
			page: true,
			limit: 20,
			limits:[20,40,60,80,100],
			height:'full-160',	
			cols: [[
				{field: 'group_name',title: '班组',align: 'center'},
				{field: 'process_name',title: '工序',align: 'center'},
				{field: 'start_worker',title: '报开始人姓名',align: 'center'},
				{field: 'end_worker',title: '报结束人姓名',align: 'center'},
				{field: 'start_date',title: '报工开始时间',align: 'center'},
				{field: 'end_date',title: '报工结束时间',align: 'center'},
				{field: 'order_area',title: '订单总积',align: 'center'},
				{field: 'number',title: '销售订单号',align: 'center'},
				{field: 'type',title: '订单类型',align: 'center'},
				{field: 'dealer',title: '客户名称',align: 'center'},
				{field: 'send_address',title: '送货地址',align: 'center'},
				{field: 'production_date',title: '排产日期',align: 'center'},
				{field: 'store_date',title: '入库日期',align: 'center'},
				{field: 'name',title: '系列名称',align: 'center'},
				{field: 'material',title: '材质',align: 'center'},
				{field: 'color_name',title: '颜色',align: 'center'},
				{field: 'flower',title: '型号',align: 'center'},
				{field: 'product_area',title: '产品面积',align: 'center'},
				{field: 'price_area',title: '报价面积',align: 'center'},
				{field: 'count',title: '产品数量',align: 'center'},
				{field: 'window_type_a',title: '窗型结构',align: 'center'},
				{field: 'escape_type_a',title: '逃生窗',align: 'center'},
				{field: 'yarn_color',title: '纱网',align: 'center'},
			]],
			cellMinWidth: 100,
			//skin: 'line',
			toolbar: false,
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
			if (obj.event === 'refresh') {
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
			}else if (obj.event === 'add') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				xadmin.open('下达生产-智能推荐流程','<?php echo url('add'); ?>?id='+checkStatus.data[0].id,$(window).width()*0.95, $(window).height()*0.95);
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
				xadmin.open('修改工序','<?php echo url('edit'); ?>?id='+checkStatus.data[0].id,$(window).width()*0.95, $(window).height()*0.95);
			}else if (obj.event === 'process') {
				var checkStatus = table.checkStatus('dataTable');
				if(checkStatus.data.length == 0){
					layer.msg('请选择要操作的内容');
					return false;
				}
				if(checkStatus.data.length > 1){
					layer.msg('只能选择单个数据编辑');
					return false;
				}
				xadmin.open('快速报工','<?php echo url('process'); ?>?order_id='+checkStatus.data[0].id,$(window).width()*0.95, $(window).height()*0.95);
			}
		});

		form.on('submit(query)', function(data) {
			table.reload('dataTable', {
				where: data.field,
				page:{curr: 1}
			})
			return false;
		});
		
		$('#export').click(function () {
			var store_date = $('#store_date').val();
			if(!store_date){
				//layer.msg('请选择入库时间',{icon:2});
				//return;
			}
			location = '<?php echo url('exportStat'); ?>?production_status=<?php echo $production_status; ?>}&store_date='+store_date
			return false;
		});		
		
	})
</script>
</html>