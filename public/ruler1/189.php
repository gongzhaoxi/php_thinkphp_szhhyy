<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>无标题文档</title>
<style type="text/css">
/*框*/

/*body {transform: scale(1) translate(0px,0px); }/*scale是缩放比例，translate是距离：左px，上px border:5px solid #2a53a8;*/
#container{position:relative;}
.text {position:absolute;top:0px;left:0px;mix-blend-mode:lighten;}
/*标尺寸*/
.posi_a{ position:absolute;}
.font p {text-align:center;display:table-cell;vertical-align:middle;}
.font p span{font-size:16px;font-weight:bold;text-shadow:1px 1px 0px #FFFFFF, -1px -1px 0px #FFFFFF, 2px 2px 0px #FFFFFF, -2px -2px 0px #FFFFFF, 3px 3px 0px #FFFFFF, -3px -3px 0px #FFFFFF;}
/*上下标尺寸*/
.size_tb_bg{background:url(/static/images/left.png) left center,url(/static/images/right.png) right center;}
/*左右标尺寸*/
.size_lr_bg{background:url(/static/images/top.png) top center,url(/static/images/bottom.png) bottom center;}
/*左右大尺*/
.borDH,.borDH p{ width:100px;writing-mode:tb-rl;}
/*左右小尺*/
.borSH,.borSH p{ width:30px;}
.borSH,.borSH p span{ font-size: 12px; }
/*上下大尺*/
.borDW,.borDW p{ height:70px;}
/*上下小尺*/
.borSW,.borSW p{ height:20px;}
.borSW,.borSW p span{ font-size: 12px; }
.ov_h{overflow: hidden;}
</style>
<script src="/static/js/jquery.min.js"></script>
</head>

<body>

<div style="padding:20px 60px 45px 30px; float: left;">
<div id="container" class="img<?php echo $diff;?>">
<!-- 框 -->
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $alumPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $rulerPic;?>" id="imageE<?php echo $diff;?>" height="377"/></div>
<!-- 花件 -->
<div style="overflow: hidden;position:absolute; top:138px;left:58px;width:85px; height:176px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="85" height="176"/></div>
</div>
<!-- 标尺寸 -->
<!-- 上边 -->
<!-- 下边 -->
<div class="posi_a ov_h" style="top:377px;left:0px;height: 45px;width:201px;">
	<div class="size_tb_bg font borDW">
		<p style="width:201px"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<!-- 左边 -->
<div class="size_lr_bg font posi_a borSH" style="height:96px;top:79px;left:-30px;"><p style="height:96px"><span><?php if(empty($A)){$A='A';}?><?php echo $A;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:116px;top:228px;left:-30px;"><p style="height:116px"><span><?php if(empty($B)){$B='B';}?><?php echo $B;?></span></p></div>
<!-- 右边 -->
<div class="posi_a ov_h" style="top:0px;left:201px;width: 60px;height:377px;">
	<div class="size_lr_bg font borDH">
		<p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p>
	</div>
</div>
<div class="size_lr_bg font posi_a borSH" style="height:72px;top:4px;left:201px;"><p style="height:72px"><span><?php if(empty($GTH)){$GTH='GTH';}?><?php echo $GTH;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:26px;top:79px;left:201px;"><p style="height:26px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:178px;top:137px;left:201px;"><p style="height:178px"><span><?php if(empty($FH)){$FH='FH';}?><?php echo $FH;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:87px;top:319px;left:57px;"><p style="width:87px"><span><?php if(empty($FW)){$FW='FW';}?><?php echo $FW;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:27px;top:-20px;left:4px;"><p style="width:27px"><span><?php if(empty($T)){$T='T';}?><?php echo $T;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:23px;top:377px;left:174px;"><p style="width:23px"><span><?php if(empty($L)){$L='L';}?><?php echo $L;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:140px;top:377px;left:31px;"><p style="width:140px"><span><?php if(empty($I)){$I='I';}?><?php echo $I;?></span></p></div>
</div>
</div>
<script>
var imgw = $('#imageE<?php echo $diff;?>').width();
var imgh = $('#imageE<?php echo $diff;?>').height();
$(".img<?php echo $diff;?>").width(imgw);$(".img<?php echo $diff;?>").height(imgh);
</script>

</body>
</html>
