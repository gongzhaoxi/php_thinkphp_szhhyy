<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:108:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/order_feedback/edit.html";i:1757776244;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
			<div class="layui-form-item" >
				<label class="layui-form-label">异常工序</label>
				<div class="layui-input-block" >
					<select name="order_process_id" required  lay-verify="required" >
						<option value="">请选择</option>
						<?php if(is_array($process) || $process instanceof \think\Collection || $process instanceof \think\Paginator): $i = 0; $__LIST__ = $process;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option <?php if($vo['id'] == $model['order_process_id']): ?>selected<?php endif; ?> value="<?php echo $vo['id']; ?>"><?php echo $vo['process_name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>	
			<div class="layui-form-item" >
				<label class="layui-form-label">异常分类</label>
				<div class="layui-input-block" >
					<select name="type" required  lay-verify="required">
						<option value="">请选择</option>
						<?php if(is_array($feedback_type) || $feedback_type instanceof \think\Collection || $feedback_type instanceof \think\Paginator): $i = 0; $__LIST__ = $feedback_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option <?php if($vo['name'] == $model['type']): ?>selected<?php endif; ?> value="<?php echo $vo['name']; ?>"><?php echo $vo['name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>	
			<div class="layui-form-item">
				<label for="name" class="layui-form-label">
					备注
				</label>
				<div class="layui-input-block">
					<input type="text" id="remark" name="remark" value="<?php echo $model['remark']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
			</div>
			
			<div class="layui-form-item">
				<label for="L_repass" class="layui-form-label"></label>
				<input name="id" type="hidden" value="<?php echo $model['id']; ?>"/>
				<button class="layui-btn" lay-filter="save" lay-submit="">保存</button>
			</div>
		</form>
	</div>
</div>
<script>
layui.config({
	base: "/static/js/",
}).extend({
	select: "select",
}).use(['form','select'],function() {
	$ = layui.jquery;
	form = layui.form,select = layui.select;
	form.on('submit(save)',function(data) {
		ajaxform("<?php echo url('edit'); ?>",data.field);
		return false;
	});  
});
</script>
</body>
</html>