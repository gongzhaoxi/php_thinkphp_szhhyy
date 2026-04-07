<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/production/edit.html";i:1757684876;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
<link rel="stylesheet" href="/static/css/select.css">
<div class="layui-fluid">
	<div class="layui-row">
		<form class="layui-form">
			<blockquote class="layui-elem-quote">订单信息</blockquote>
			<div class="layui-form-item">
				<label for="number" class="layui-form-label">
					订单编号
				</label>
				<div class="layui-input-inline">
					<input type="text" id="number" name="number" value="<?php echo $model['number']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<label for="dealer" class="layui-form-label">
					经销商
				</label>
				<div class="layui-input-inline">
					<input type="text" id="dealer" name="dealer" value="<?php echo $model['dealer']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<label for="area" class="layui-form-label">
					面积
				</label>
				<div class="layui-input-inline">
					<input type="text" id="area" name="area" value="<?php echo $model['area']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>	
				<label for="type" class="layui-form-label">
					订单类型
				</label>
				<div class="layui-input-inline">
					<?php 
					$arr = config('order_type');
					 ?>
					<input type="text" id="type" name="type" value="<?php echo $arr[$model['type']]??''; ?>" required="" lay-verify="required" class="layui-input" >
				</div>				
			</div>
			<div class="layui-form-item">
				<label for="end_time" class="layui-form-label">
					交货时间
				</label>
				<div class="layui-input-inline">
					<input type="text" id="end_time" name="end_time" value="<?php echo $model['end_time']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<label for="addtime" class="layui-form-label">
					创建日期
				</label>
				<div class="layui-input-inline">
					<input type="text" id="addtime" name="addtime" value="<?php echo date('Y-m-d H:i:s',$model['addtime']); ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<label for="note" class="layui-form-label">
					备注
				</label>
				<div class="layui-input-inline">
					<input type="text" id="note" name="note" value="<?php echo $model['note']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
			</div>	
		</form>
	</div>
	
	<blockquote class="layui-elem-quote">工序信息</blockquote>
	<form class="layui-form">
		<button class="layui-btn add" type="button">增加</button>
		<button class="layui-btn" lay-filter="save" lay-submit="">保存</button>
		<table class="layui-table" id="order_process">
			<colgroup>
				<col>
				<col width="250">
			</colgroup>
			<thead>
				<tr>
					<th>工序名称</th>
					<th>操作</th>
				</tr> 
			</thead>
			<tbody>
				<?php if(is_array($model['process']) || $model['process'] instanceof \think\Collection || $model['process'] instanceof \think\Paginator): $i = 0; $__LIST__ = $model['process'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
				<tr>
					<td><?php echo $vo['name']; ?><input type="hidden" value="<?php echo $vo['id']; ?>" name="process_id[]" class="process_id"  /></td>
					<td>
						<button class="layui-btn up" type="button">向上</button>
						<button class="layui-btn down" type="button">向下</button>
						<button class="layui-btn layui-btn-danger delete" type="button">删除</button>
					</td>
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</tbody>
		</table>
		<input name="order_id" type="hidden" value="<?php echo $model['id']; ?>"/>
	</form>
</div>
<script>
var process = <?php echo json_encode($process,true); ?>;
layui.config({
	base: "/static/js/",
}).extend({
	select: "select",
}).use(['form','select','table'],function() {
	$ = layui.jquery;
	form = layui.form,select = layui.select;
	form.on('submit(save)',function(data) {
		ajaxform("<?php echo url('edit'); ?>",data.field);
		return false;
	});  
	
	var table = layui.table;
	var order_id = '<?php echo $model['id']; ?>';
	
	$("body").on("click",".layui-table tbody tr .up",function(){
		var li_index=$(this).closest("tr").index();
		if(li_index>=1){
			$(this).closest("tr").insertBefore($(this).closest("tbody").find("tr").eq(Number(li_index)-1));
		}
	});
	$("body").on("click",".layui-table tbody tr .down",function(){
		var li_index=$(this).closest("tr").index();
		$(this).closest("tr").insertAfter($(this).closest("tbody").find("tr").eq(Number(li_index)+1));
	});
	$("body").on("click",".layui-table tbody tr .delete",function(){
		$(this).closest("tr").remove();
	});	
	$("body").on("click",".add",function(){
		var html = '<form style="margin-top:20px;" class="layui-form"><div class="layui-form-item"><label class="layui-form-label">工序</label><div class="layui-input-block"><select name="process_id"  required="" lay-verify="required">';
		layui.each(process, function (index, item) {
			html += '<option value="'+item.id+'">'+item.name+'</option>';
		})
		html += '</select></div></div><div class="layui-form-item"><label for="L_repass" class="layui-form-label"></label><button class="layui-btn" lay-filter="addProcess" lay-submit="">保存</button></div></form>';
		layer.open({
			title:'添加工序',
			type: 1,
			area: ['700px', '80%'],
			content: html,
			success: function(layero, index){
				form.render();
				form.on('submit(addProcess)',function(data) {
					var ids = [];
					$('.process_id').each(function(){
						ids.push($(this).val());
					})
					if(ids.indexOf(data.field.process_id) != -1){
						layer.msg('工序已存在');
					}else{
						var name = '';
						layui.each(process, function (index, item) {
							if(item.id == data.field.process_id){
								name = item.name;
							}
						})
						$('#order_process tbody').append('<tr><td>'+name+'<input type="hidden" value="'+data.field.process_id+'" name="process_id[]" class="process_id"  /></td><td><button class="layui-btn up" type="button">向上</button><button class="layui-btn down" type="button">向下</button><button class="layui-btn layui-btn-danger delete" type="button">删除</button></td></tr>');
						layer.close(index);
					}
					return false;
				}); 
			}
		});
	});
});
</script>
</body>
</html>