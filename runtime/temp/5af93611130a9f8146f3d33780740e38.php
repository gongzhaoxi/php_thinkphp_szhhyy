<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:117:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/orderprogress/orderdetailnew.html";i:1766070404;}*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>工厂内部扫码报工进度查询</title>
<style type="text/css">
<!--
body {margin: 0px; padding:0px; overflow: auto;}
*{padding:0; list-style-type:none;font-family: Arial, 'Hiragino Sans GB', '微软雅黑', '黑体-简', Helvetica, sans-serif;}
#clear{clear:both;height:0;overflow:hidden}
a:link {text-decoration: none;}
a:visited {text-decoration: none;}
a:hover {text-decoration: none;}
a:active {text-decoration: none;}
ul,li,h1,h2,h3,h4,h5,h6,h7,h8,h9{ display:inline; margin:0; padding:0;}
img { border:none;}
p{padding:0px; margin:0px;}
button{cursor:pointer}
.top{height: 102px; width: 100%; background: url(/staticindex/img/top.jpg) top left;}
.out a{float: right;margin-right: 10px;font-size:14px; color: #fff; background: #555; text-align: center; padding:0px 10px; line-height: 40px; height: 40px; margin-top: 31px;border-radius:20px}
.out a:hover{background: #d11c11;}
.search{ width: 98%; height: 30px; background: #333; padding: 10px 1%;}
.se1{ float: left;width: 140px; border: none; background: #fff; color: #333; height: 30px; line-height: 30px; font-size: 14px; padding: 0px 10px;margin-right:8px;border-radius:4px}
.se2{float: left;width: 100px;border-radius:4px; border: none; background: #d11c11; color: #fff; height: 30px; line-height: 30px; font-size: 14px; padding: 0px 10px;cursor: pointer;}
.se3{ float: right; width: 200px;border-radius:10px; border: none; background: #d11c11; color: #fff; height: 30px; line-height: 30px; font-size: 14px; padding: 0px 10px; cursor: pointer;}
.se3 option{background: #fff; color: #333;font-size: 14px; line-height: 30px;}
.title{ width: 100%; line-height: 45px; font-size: 16px; font-weight:bold; color: #333; text-align: center; }
.title ul{ background: #d9d9d9; float: left;width: 100%;border-bottom:2px solid #c9c9c9;}
.title ul li{ float: left;height: 45px; }
.title ul li.li1{ width:4%;border-right:1px solid #999;}
.title ul li.li2{ width:8%;border-right:1px solid #999;}
.title ul li.li3{ width:8%;border-right:1px solid #999;}
.title ul li.li4{ width:8%;border-right:1px solid #999;}
.title ul li.li5{ width:8%;border-right:1px solid #999;}
.title ul li.li6{ width:62%}
.list{ width: 100%;  font-size: 12px;color: #333; text-align: center; }
.list ul{float: left;width: 100%;border-bottom:1px solid #e9e9e9;}
.list ul li{ float: left; height: 60px;word-break: break-word;margin-top: 30px;}
.list ul li.li1{ width:4%;border-right:1px solid #EAEAEA;}
.list ul li.li2{ width:8%;border-right:1px solid #EAEAEA;}
.list ul li.li3{ width:8%;border-right:1px solid #EAEAEA;}
.list ul li.li4{ width:8%;border-right:1px solid #EAEAEA;}
.list ul li.li5{ width:8%;border-right:1px solid #EAEAEA;}
.list ul li.li6{ width:62%;display: flex;justify-content: center;align-items: center;}
.s2p{position: relative; width: 928px; height:54px; margin:10px auto; line-height: 54px;background-repeat: no-repeat;}
.s2p div{ float: left; overflow: hidden; line-height: 35px;color: #fff;  }
.s2p div p{ line-height: 16px; }
.s2p div.sli1{width: 83px;margin-right:4px;}
.s2p div.sli2{width:83px;}
.time{position: absolute;top: 25px; left: 0px;line-height: 16px;height:25px;}
.time div{width: 73px;margin-right:11px;text-align:right;}
.li-one{line-height:0;padding:14px 4px;background:#20B2AA;margin:0 2px;color:#fff}
.li-two{padding: 10px 4px;margin:0 2px;background:#D3D3D3;color:#ffff}
.change{background:#ADADAD}
-->

</style>
	<link rel="stylesheet" href="/static/css/font.css">
	<link rel="stylesheet" href="/static/css/xadmin.css">
	<!-- <link rel="stylesheet" href="/static/css/theme5.css"> -->
	<script src="/static/lib/layui/layui.js" charset="utf-8"></script>
	<script type="text/javascript" src="/static/js/xadmin.js"></script>
	<script type="text/javascript" src="/static/js/erp.js"></script>
</head>

<body>
<div class="search">
<form action="">
<input type="text" name="ordernum" class="se1" value="<?php echo $condition['ordernum']; ?>" placeholder="请输入订单号">
<input type="text" name="uname" class="se1" value="<?php echo $condition['uname']; ?>" placeholder="请输入客户名"></input>
<input type="date" name="ordertime" class="se1" value="<?php echo $condition['ordertime']; ?>"></input>
<input type="text" name="phone" class="se1" value="<?php echo $condition['phone']; ?>" placeholder="请输入电话号码"></input>
<select name="state" class="se1">
<option value="">全部</option>
<option value="1" <?php if($condition['state'] == 1): ?>selected<?php endif; ?>>未入库</option>
<option value="2" <?php if($condition['state'] == 2): ?>selected<?php endif; ?>>已入库</option>
</select>
<button class="se2">GO！搜索</button>
</form>
</div>

<div class="title">
	<ul>
		<li class="li1">序号</li>
		<li class="li2">订单编号</li>
		<li class="li3">下单日期</li>
		<li class="li4">客户名</li>
		<li class="li5">电话号码</li>
		<li class="li6">状态</li>
	</ul>
</div>
<div style="width: 100%;height: auto;display: inline-block;">
<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$or): $mod = ($i % 2 );++$i;?>
<div class="list">
	<ul>
		<li class="li1"><?php echo $or['id']; ?></li>
		<li class="li2"><?php echo $or['number']; ?></li>
		<li class="li3"><?php echo date('Y/m/d',$or['addtime']); ?></li>
		<li class="li4"><?php echo $or['dealer']; ?></li>
		<li class="li5"><?php echo $or['phone']; ?></li>
		<li class="li6" style="margin-top: 13px;">
		<?php if(is_array($or['process']) || $or['process'] instanceof \think\Collection || $or['process'] instanceof \think\Paginator): $i = 0; $__LIST__ = $or['process'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$gx): $mod = ($i % 2 );++$i;?>
			<div class="li-content">
				<div class="li-one <?php if($gx['start_date']==''): ?>change<?php endif; ?>"><?php echo $gx['process_name']; ?></div>
				<div class="li-two" >
					<?php if($gx['start_date']==''): ?>
						<p>未开始</p>
					<?php else: ?>
						<p><?php echo $gx['start_worker']; ?><?php echo $gx['start_date']; ?></p>
						<p><?php echo $gx['end_worker']; ?><?php echo $gx['end_date']; ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; endif; else: echo "" ;endif; ?>
		</li>
	</ul>
</div>
<?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<div style="width: 100%;height: 40px;text-align: center;" class="page"><?php echo $page; ?></div>

<script src="/static/js/jquery.min.js">
</script>
<script type="text/javascript">
   $('#se2').submit();
</script>
</body>
</html>
