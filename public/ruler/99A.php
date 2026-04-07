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
.font p span{font-size: 20px;font-weight:bold;text-shadow:1px 1px 0px #FFFFFF, -1px -1px 0px #FFFFFF, 2px 2px 0px #FFFFFF, -2px -2px 0px #FFFFFF, 3px 3px 0px #FFFFFF, -3px -3px 0px #FFFFFF;}
/*上下标尺寸*/
.size_tb_bg{background:url(/static/images/left.png) left center,url(/static/images/right.png) right center;}
/*左右标尺寸*/
.size_lr_bg{background:url(/static/images/top.png) top center,url(/static/images/bottom.png) bottom center;}
/*左右大尺*/
.borDH,.borDH p{ width:100px;writing-mode:tb-rl;}
/*左右小尺*/
.borSH,.borSH p{ width:30px;}
.borSH,.borSH p span{ font-size: 20px; }
/*上下大尺*/
.borDW,.borDW p{ height:70px;}
/*上下小尺*/
.borSW,.borSW p{ height:20px;}
.borSW,.borSW p span{ font-size: 20px; }
.ov_h{overflow: hidden;}
.p_input p input{ width: 200px 	 }
.p_input p input.ts{ width: 30px 	 }
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
<div style="overflow: hidden;position:absolute; top:34px;left:54px;width:67px; height:183px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="67" height="183"/></div>
</div>
<!-- 标尺寸 -->
<!-- 上边 -->
<div class="size_tb_bg font posi_a borSW" style="width:148px;top:-20px;left:4px;"><p style="width:148px"><span><?php if(empty($I)){$I='I';}?><?php echo $I;?></span></p></div>
<!-- 下边 -->
<div class="size_tb_bg font posi_a borSW" style="width:27px;top:377px;left:125px;"><p style="width:27px"><span><?php if(empty($L)){$L='L';}?><?php echo $L;?></span></p></div>
<div class="posi_a ov_h" style="top:377px;left:0px;height: 45px;width:156px;">
	<div class="size_tb_bg font borDW">
		<p style="width:156px"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<!-- 左边 -->
<div class="size_lr_bg font posi_a borSH" style="height:186px;top:33px;left:156px;"><p style="height:186px"><span><?php if(empty($FH)){$FH='FH';}?><?php echo $FH;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:122px;top:251px;left:156px;"><p style="height:122px"><span><?php if(empty($GBH)){$GBH='GBH';}?><?php echo $GBH;?></span></p></div>
<!-- 右边 -->
<div class="posi_a ov_h" style="top:0px;left:156px;width: 60px;height:377px;">
	<div class="size_lr_bg font borDH">
		<p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p>
	</div>
</div>
<div class="size_lr_bg font posi_a borSH" style="height:26px;top:4px;left:-30px;"><p style="height:26px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:74px;top:33px;left:25px;writing-mode:tb-rl;"><p style="height:74px"><span><?php if(empty($D)){$D='D';}?><?php echo $D;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:59px;top:160px;left:25px;writing-mode:tb-rl;"><p style="height:59px"><span><?php if(empty($P)){$P='P';}?><?php echo $P;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:221px;top:156px;left:-30px;"><p style="height:221px"><span><?php if(empty($LPH)){$LPH='LPH';}?><?php echo $LPH;?></span></p></div>
<!-- 锁位 -->
<div class="size_tb_bg font posi_a borSW" style="width:46px;top:122px;left:4px;"><p style="width:46px"><span><?php if(empty($LW)){$LW='LW';}?><?php echo $LW;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:46px;top:111px;left:-30px;"><p style="height:46px"><span><?php if(empty($LH)){$LH='LH';}?><?php echo $LH;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:69px;top:222px;left:53px;"><p style="width:69px"><span><?php if(empty($FW)){$FW='FW';}?><?php echo $FW;?></span></p></div>
</div>
</div>
<script>
var imgw = $('#imageE<?php echo $diff;?>').width();
var imgh = $('#imageE<?php echo $diff;?>').height();
$(".img<?php echo $diff;?>").width(imgw);$(".img<?php echo $diff;?>").height(imgh);
</script>

</body>
</html>
