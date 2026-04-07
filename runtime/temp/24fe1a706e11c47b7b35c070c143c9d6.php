<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:106:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/production/report.html";i:1757168318;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
				<label for="start_date" class="layui-form-label">
					流程开始
				</label>
				<div class="layui-input-block">
					<input type="text" id="start_date" name="start_date" value="<?php echo $model['start_date']; ?>" required="" lay-verify="required" class="layui-input" >
				</div>
			</div>
			<div class="layui-form-item">
				<label for="end_date" class="layui-form-label">
					流程结束
				</label>
				<div class="layui-input-block">
					<input type="text" id="end_date" name="end_date" value="<?php echo $model['end_date']; ?>" required="" lay-verify="required"  class="layui-input" >
				</div>
			</div>
			<div class="layui-form-item" >
				<label class="layui-form-label">报工开始人</label>
				<div class="layui-input-block" >
					<select name="start_worker" xm-select="start_worker" required  lay-verify="required" xm-select-search  xm-select-search-type="dl" xm-select-radio>
						<option value="">请选择</option>
						<?php if(is_array($worker) || $worker instanceof \think\Collection || $worker instanceof \think\Paginator): $i = 0; $__LIST__ = $worker;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option value="<?php echo $vo['name']; ?>" <?php if($vo['name'] == $model['start_worker']): ?>selected<?php endif; ?>><?php echo $vo['name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>		
			<div class="layui-form-item" >
				<label class="layui-form-label">报工结束人</label>
				<div class="layui-input-block" >
					<select name="end_worker" xm-select="end_worker" required  lay-verify="required" xm-select-search  xm-select-search-type="dl" xm-select-radio>
						<option value="">请选择</option>
						<?php if(is_array($worker) || $worker instanceof \think\Collection || $worker instanceof \think\Paginator): $i = 0; $__LIST__ = $worker;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<option value="<?php echo $vo['name']; ?>" <?php if($vo['name'] == $model['end_worker']): ?>selected<?php endif; ?>><?php echo $vo['name']; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>				
			<div class="layui-form-item">
				<input type="hidden" id="id" name="id" value="<?php echo $model['id']; ?>"  >
				<label for="L_repass" class="layui-form-label"></label>
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
}).use(['form','select','laydate'],function() {
	$ = layui.jquery;
	form = layui.form,select = layui.select,laydate = layui.laydate;
	form.on('submit(save)',function(data) {
		ajaxform("<?php echo url('report'); ?>",data.field);
		return false;
	});  
	laydate.render({
		elem: '#start_date' //指定元素
	});
	laydate.render({
		elem: '#end_date' //指定元素
	});	
});
</script>
</body>
</html>