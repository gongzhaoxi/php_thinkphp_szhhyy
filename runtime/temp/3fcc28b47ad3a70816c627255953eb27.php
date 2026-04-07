<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:106:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/worker_group/edit.html";i:1758896601;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
			<div class="layui-form-item">
				<label for="name" class="layui-form-label">
					班组名称
				</label>
				<div class="layui-input-block">
					<input type="text" id="name" name="name" value="<?php echo $model['name']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
			</div>
			<div class="layui-form-item">
				<label for="department" class="layui-form-label">
					班组所属部门
				</label>
				<div class="layui-input-block">
					<input type="text" id="department" name="department" value="<?php echo $model['department']; ?>"  class="layui-input" >
				</div>
			</div>
			<div class="layui-form-item" >
				<label class="layui-form-label">工序</label>
				<div class="layui-input-block" >
					<select name="process" xm-select="process" required  lay-verify="required" xm-select-search  xm-select-search-type="dl" xm-select-checkbox>
						<option value="">请选择</option>
						<?php if(is_array($process) || $process instanceof \think\Collection || $process instanceof \think\Paginator): $i = 0; $__LIST__ = $process;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option <?php if(in_array($vo['id'],$model['process'])): ?>selected<?php endif; ?> value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>	
			<div class="layui-form-item" >
				<label class="layui-form-label">班长</label>
				<div class="layui-input-block" >
					<select name="monitor" xm-select="monitor"  xm-select-search  xm-select-search-type="dl" xm-select-checkbox>
						<option value="">请选择</option>
						<?php if(is_array($worker) || $worker instanceof \think\Collection || $worker instanceof \think\Paginator): $i = 0; $__LIST__ = $worker;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option <?php if(in_array($vo['id'],$model['monitor'])): ?>selected<?php endif; ?> value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
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