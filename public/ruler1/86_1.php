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
<div style="overflow: hidden;position:absolute; top:8px;left:8px;width:147px; height:361px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="147" height="361"/></div>
</div>
<!-- 标尺寸 -->
<!-- 上边 -->
<!-- 下边 -->
<div class="posi_a ov_h" style="top:377px;left:0px;height: 45px;width:253px;">
	<div class="size_tb_bg font borDW">
		<p style="width:253px"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<!-- 左边 -->
<div class="size_lr_bg font posi_a borSH" style="height:363px;top:7px;left:-30px;"><p style="height:363px"><span><?php if(empty($FH)){$FH='FH';}?><?php echo $FH;?></span></p></div>
<!-- 右边 -->
<div class="posi_a ov_h" style="top:0px;left:253px;width: 60px;height:377px;">
	<div class="size_lr_bg font borDH">
		<p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p>
	</div>
</div>
<div class="size_lr_bg font posi_a borSH" style="height:133px;top:7px;left:213px;writing-mode:tb-rl;"><p style="height:133px"><span><?php if(empty($D)){$D='D';}?><?php echo $D;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:133px;top:237px;left:213px;writing-mode:tb-rl;"><p style="height:133px"><span><?php if(empty($P)){$P='P';}?><?php echo $P;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:148px;top:229px;left:253px;"><p style="height:148px"><span><?php if(empty($LPH)){$LPH='LPH';}?><?php echo $LPH;?></span></p></div>
<!-- 锁位 -->
<div class="size_tb_bg font posi_a borSW" style="width:83px;top:178px;left:163px;"><p style="width:83px"><span><?php if(empty($LW)){$LW='LW';}?><?php echo $LW;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:83px;top:147px;left:253px;"><p style="height:83px"><span><?php if(empty($LH)){$LH='LH';}?><?php echo $LH;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:149px;top:377px;left:7px;"><p style="width:149px"><span><?php if(empty($FW)){$FW='FW';}?><?php echo $FW;?></span></p></div>
</div>
</div>
<script>
var imgw = $('#imageE<?php echo $diff;?>').width();
var imgh = $('#imageE<?php echo $diff;?>').height();
$(".img<?php echo $diff;?>").width(imgw);$(".img<?php echo $diff;?>").height(imgh);
</script>

</body>
</html>
