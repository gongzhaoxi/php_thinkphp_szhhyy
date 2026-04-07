<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/process/edit.html";i:1756218896;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-form-label{ width: 90px;}
</style>
<div class="layui-fluid">
	<div class="layui-row">
		<form class="layui-form">
			<div class="layui-form-item">
				<label for="username" class="layui-form-label">
					工序名称
				</label>
				<div class="layui-input-inline">
					<input type="text" id="name" name="name" value="<?php echo $model['name']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<div class="layui-form-mid layui-word-aux">
					<span class="x-red">*</span>
				</div>
			</div>
			<div class="layui-form-item">
				<label for="sort" class="layui-form-label">
					排序
				</label>
				<div class="layui-input-inline">
					<input type="text" id="sort" name="sort" value="<?php echo $model['sort']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
				<div class="layui-form-mid layui-word-aux">
					<span class="x-red">*</span>小的排前面
				</div>
			</div>
			<div class="layui-form-item">
				<label for="status" class="layui-form-label">
					状态
				</label>
				<div class="layui-input-inline">
					<select name='status'>
						<option value='0' <?php if($model['status'] == 0): ?>selected<?php endif; ?>>禁用</option>
						<option value='1' <?php if($model['status'] == 1): ?>selected<?php endif; ?>>启用</option>
					</select>
				</div>
				<div class="layui-form-mid layui-word-aux">
					
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
layui.use(['laydate','form'],function() {
	$ = layui.jquery;
	var form = layui.form;
	//监听提交
	form.on('submit(save)',function(data) {
		ajaxform("<?php echo url('edit'); ?>",data.field);
		return false;
	});
});
</script>
</body>
</html>