<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/manage/view/index/index.html";i:1635496804;s:93:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/manage/view/public/header.html";i:1635496806;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/manage/view/public/menu.html";i:1635496806;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>佛山市微元科技-扫码报工系统</title>

<link href="/static/theme/manage/css/bootstrap.min.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="/static/theme/manage/css/bootstrap-table.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="/static/theme/manage/css/styles.css?v=<?php echo time(); ?>" rel="stylesheet">

<!--[if lt IE 9]>
<script src="/static/theme/manage/js/html5shiv.js?v=<?php echo time(); ?>"></script>
<script src="/static/theme/manage/js/respond.min.js?v=<?php echo time(); ?>"></script>
<![endif]-->

</head>

<body>
	
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar-collapse">
					<span class="sr-only">切换导航</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#"><span>后台管理</span></a>
				<ul class="user-menu">
					<li class="dropdown pull-right">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo session('name'); ?> <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#"><span class="glyphicon glyphicon-user"></span> 个人信息</a></li>
							<li><a href="#"><span class="glyphicon glyphicon-cog"></span> 系统设置</a></li>
							<li><a href="<?php echo url('Login/logout'); ?>"><span class="glyphicon glyphicon-log-out"></span>退出登录</a></li>
						</ul>
					</li>
				</ul>
			</div>
							
		</div><!-- /.container-fluid -->
</nav>	
	<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
		<ul class="nav menu">
			<li><a href="<?php echo url('index'); ?>"><span class="glyphicon glyphicon-dashboard"></span> 站点列表</a></li>
			<li><a href="<?php echo url('commend'); ?>"><span class="glyphicon glyphicon-dashboard"></span> SQL指令</a></li>
			<li><a href="<?php echo url('copyfile'); ?>"><span class="glyphicon glyphicon-dashboard"></span> 文件复制</a></li>
			<!-- 
			<li class="parent ">
				<a href="#">
					<span class="glyphicon glyphicon-list"></span> 下拉 <span data-toggle="collapse" href="#sub-item-1" class="icon pull-right"><em class="glyphicon glyphicon-s glyphicon-plus"></em></span> 
				</a>
				<ul class="children collapse" id="sub-item-1">
					<li>
						<a class="" href="#">
							<span class="glyphicon glyphicon-share-alt"></span> 子下拉
						</a>
					</li>
				</ul>
			</li>
			 -->
			<li role="presentation" class="divider"></li>
		</ul>
		<div class="attribution"> <a href="#">佛山市微元科技有限公司</a></div>
</div>	
	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">			
		<div class="row">
			<ol class="breadcrumb">
				<li><a href="#"><span class="glyphicon glyphicon-home"></span></a></li>
				<li class="active">站点列表</li>
			</ol>
		</div>
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">所有站点</h1>
			</div>
		</div>
				
		
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<a class="btn btn-primary" href="<?php echo url('Index/add_site'); ?>">新增站点</a>
					</div>
					<div class="panel-body">
						<table data-toggle="table">
						    <thead>
						    <tr>
						        <th>序号</th>
						        <th>状态</th>
						        <th>项目标识</th>
						        <th>名称</th>
						        <th>域名</th>
						        <th>账号</th>
						        <th>添加时间</th>
						        <th>操作</th>
						    </tr>
						    </thead>
						    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						     <tr>
						     
						        <td data-field="id" data-checkbox="true" ><?php echo $vo['id']; ?></td>
						        <td data-field="status" data-sortable="true"><?php  echo $vo['status']>0?'开启':'关闭'; ?></td>
						         <td data-field="name"  data-sortable="true"><?php echo $vo['tag']; ?></td>
						        <td data-field="name"  data-sortable="true"><?php echo $vo['sitename']; ?></td>
						        <td data-field="domain" data-sortable="true"><?php echo $vo['domain']; ?></td>
						        <td data-field="price" data-sortable="true"><?php echo $vo['account']; ?></td>
						        <td data-field="" data-sortable="true"><?php echo $vo['addtime']; ?></td>
						        <td data-field="" data-sortable="true">
						        	<a href="<?php echo url('Index/add_site',array('id'=>$vo['id'])); ?>">编辑 </a>| <a href="javascript:void(0);" onclick="delete_site(<?php echo $vo['id']; ?>)">删除</a>
						        </td>
						    </tr>
						    <?php endforeach; endif; else: echo "" ;endif; ?>
						</table>
					</div>
				</div>
			</div>
		</div>

		
		
	</div>

	<script src="/static/theme/manage/js/jquery-1.11.1.min.js"></script>
	<script src="/static/theme/manage/js/bootstrap.min.js?v=<?php echo time(); ?>"></script>
	<script src="/static/theme/manage/js/bootstrap-table.js?v=<?php echo time(); ?>"></script>
	<script src="/staticindex/js/layer/layer.js"></script>
	<script>
		!function ($) {
			$(document).on("click","ul.nav li.parent > a > span.icon", function(){		  
				$(this).find('em:first').toggleClass("glyphicon-minus");	  
			}); 
			$(".sidebar span.icon").find('em:first').addClass("glyphicon-plus");
		}(window.jQuery);

		$(window).on('resize', function () {
		  if ($(window).width() > 768) $('#sidebar-collapse').collapse('show')
		})
		$(window).on('resize', function () {
		  if ($(window).width() <= 767) $('#sidebar-collapse').collapse('hide')
		})
		
		function delete_site(id){
			layer.confirm('确认要删除站点吗？', {
				  btn: ['确认','取消']
				}, function(){
					$.ajax({
						  url: "<?php echo Url('Index/delete_site'); ?>",
						  data:{"id":id},
						  dataType:"json",
						  type:'POST',
						  success:function(obj){
							  //console.log(obj);
							  if(obj.status==1){
								  layer.msg('删除成功');
								  setTimeout(function(){
									  window.location.reload();
						    		}, 2000);
							  }else{
								  layer.msg(obj.msg);
							  }
						  }
					  })
					
				}, function(){
				  
				});
		}
	</script>	
</body>

</html>
