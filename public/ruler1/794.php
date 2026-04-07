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
<!-- <div style="overflow: hidden;position:absolute; top:56px;left:56px;width:68px; height:168px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="72" height="168"/></div>
</div>
<div style="overflow: hidden;position:absolute; top:56px;left:213px;width:68px; height:168px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="72" height="168"/></div>
</div> -->
<!-- 砂网 -->
<!-- <div style="background: url(/static/images/shawang.png); position:absolute; top: 0px;left: 0px;width: 337px;height: 279px; ">
</div> -->
<!-- 标尺寸 -->
<!-- 上边 -->
<!-- <div class="size_tb_bg font posi_a borSW" style="width:163px;top:-20px;left:4px;"><p style="width:163px"><span><?php if(empty($I)){$I='I';}?><?php echo $I;?></span></p></div> -->
<!-- 下边 -->
<div class="size_tb_bg font posi_a borSW" style="width:54px;top:377px;left:356px;"><p style="width:54px"><span><?php if(empty($L)){$L='L';}?><?php echo $L;?></span></p></div>
<div class="posi_a ov_h" style="top:377px;left:0px;height:45px;width:420px;">
	<div class="size_tb_bg font borDW">
		<p style="width:420px"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<!-- 中间竖 -->
<div class="size_lr_bg font posi_a borSH" style="height:128px;top:10px;left:58px;"><p style="height:128px"><span><?php if(empty($D)){$D='D';}?><?php echo $D;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:128px;top:239px;left:58px;"><p style="height:128px"><span><?php if(empty($P)){$P='P';}?><?php echo $P;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:174px;top:10px;left:368px;"><p style="height:174px"><span><?php if(empty($A)){$A='A';}?><?php echo $A;?></span></p></div>
<!-- <div class="size_lr_bg font posi_a borSH" style="height:120px;top:4px;left:270px;"><p style="height:120px"><span><?php if(empty($B)){$B='B';}?><?php echo $B;?></span></p></div> -->
<!-- 中间横 -->
<div class="size_tb_bg font posi_a borSW" style="width:83px;top:179px;left:9px;"><p style="width:83px"><span><?php if(empty($LW)){$LW='LW';}?><?php echo $LW;?></span></p></div>
<!-- 左边 -->
<div class="size_lr_bg font posi_a borSH" style="height:83px;top:147px;left:-30px;"><p style="height:83px"><span><?php if(empty($LH)){$LH='LH';}?><?php echo $LH;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:147px;top:230px;left:-30px;"><p style="height:147px"><span><?php if(empty($LPH)){$LPH='LPH';}?><?php echo $LPH;?></span></p></div>
<!-- 右边 -->
<!-- <div class="size_lr_bg font posi_a borSH" style="height:28px;top:4px;left:420px;"><p style="height:28px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p></div> -->
<div class="size_lr_bg font posi_a borSH" style="height:357px;top:10px;left:420px;"><p style="height:357px"><span><?php if(empty($C)){$C='C';}?><?php echo $C;?></span></p></div>
<div class="posi_a ov_h" style="top:0px;left:420px;width:60px;height:377px;">
	<div class="size_lr_bg font borDH">
		<p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p>
	</div>
</div>
<!-- <div class="size_lr_bg font posi_a borSH" style="height:22px;top:4px;left:337px;"><p style="height:22px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:109px;top:30px;left:337px;"><p style="height:109px"><span><?php if(empty($A)){$A='A';}?><?php echo $A;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:67px;top:29px;left:185px;writing-mode:tb-rl;"><p style="height:67px"><span><?php if(empty($D)){$D='D';}?><?php echo $D;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:67px;top:184px;left:185px;writing-mode:tb-rl;"><p style="height:67px"><span><?php if(empty($P)){$P='P';}?><?php echo $P;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:197px;top:180px;left:337px;"><p style="height:197px"><span><?php if(empty($LPH)){$LPH='LPH';}?><?php echo $LPH;?></span></p></div> -->
<!-- 锁位 -->
<!-- <div class="size_tb_bg font posi_a borSW" style="width:39px;top:105px;left:128px;"><p style="width:39px"><span><?php if(empty($LW)){$LW='LW';}?><?php echo $LW;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:39px;top:99px;left:174px;"><p style="height:39px"><span><?php if(empty($LH)){$LH='LH';}?><?php echo $LH;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:70px;top:230px;left:55px;"><p style="width:70px"><span><?php if(empty($FW)){$FW='FW';}?><?php echo $FW;?></span></p></div> -->
</div>
</div>
<script>
var imgw = $('#imageE<?php echo $diff;?>').width();
var imgh = $('#imageE<?php echo $diff;?>').height();
$(".img<?php echo $diff;?>").width(imgw);$(".img<?php echo $diff;?>").height(imgh);
</script>
<!-- <div class="p_input" style="margin-left: 20px; float: left;">
<p>标尺寸</p>
<p>W：<input type="text" name="W" value="总宽"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>H：<input type="text" name="H" value="总高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>S：<input type="text" name="S" value="间距"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>F：<input type="text" name="F" value="边框"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LA：<input type="text" name="LA" value="搭接"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>GBH：<input type="text" name="GBH" value="下固定高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LPH：<input type="text" name="LPH" value="锁位高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LH：<input type="text" name="LH" value="执手高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LW：<input type="text" name="LW" value="执手宽"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>FH：<input type="text" name="FH" value="总高-边框*2+搭接*2-外框*7-间距*4-下固定高"></p>
<p>FW：<input type="text" name="FW" value="(总宽-边框*2+搭接*2-外框*9-执手宽*2-(总宽-边框*2+搭接*2-外框*13)/12*4)/2"></p>
<p>A：<input type="text" name="A" value="(总高-边框*2+搭接*2-外框*6-下固定高-间距*2)/2"></p>
<p>D：<input type="text" name="D" value="总高-边框+搭接-外框*4-间距-执手高*2-锁位高"></p>
<p>P：<input type="text" name="P" value="锁位高-边框+搭接-外框*4-下固定高-间距"></p>
<p>I：<input type="text" name="I" value="(总宽-边框*2+搭接*2-外框*3)/2"></p>
<p>L：<input type="text" name="L" value="(总宽-边框*2+搭接*2-外框*13)/12"></p>
</div> -->
</body>
</html>
