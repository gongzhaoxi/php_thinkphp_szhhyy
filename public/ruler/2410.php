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
<div style="overflow: hidden;position:absolute; top:35px;left:82px;width:131px; height:174px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flower;?>" id="imageE<?php echo $diff;?>" width="131" height="174"/></div>
</div>

<!-- 上下固花件 -->

<div style="overflow: hidden;position:absolute; top:276px;left:44px;width:131px; height:66px;">
<div class="img<?php echo $diff;?>" style="background:url(/upload/<?php echo $flowerPic;?>);"></div>
<div class="img<?php echo $diff;?> text"><img src="/upload/<?php echo $flowers;?>" id="imageE<?php echo $diff;?>" width="131" height="66"/></div>
</div>

<!-- 砂网 -->
<?php
if(!isset($SW)){
$SW=0;
}
if($SW=='1'){
echo "<div style='background: url(/static/images/shawang.png); position:absolute; top: 0px;left: 0px;width: 220px;height: 244px;'></div>";
}
?>
<!-- <div style="background: url(/static/images/shawang.png); position:absolute; top: 0px;left: 0;width: 220px;height: 244px; ">
</div> -->
<!-- 标尺寸 -->
<!-- 上边 -->
<!-- 下边 -->
<div class="posi_a ov_h" style="top:357px;left:0px;height: 45px;width:220px;">
	<div class="size_tb_bg font borDW">
		<p style="width:220px"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:297px;left:44px;height: 45px;width:133px;">
	<div class="size_tb_bg font borDW">
		<p style="width:133px"><span><?php if(empty($Z)){$Z='Z';}?><?php echo $Z;?></span></p>
	</div>
</div>
<!-- <div class="posi_a ov_h" style="top:92px;left:0px;height: 45px;width:45px;">
	<div class="size_tb_bg font borDW">
		<p style="width:45px"><span><?php if(empty($L)){$L='L';}?><?php echo $L;?></span></p>
	</div>
</div> -->
<div class="posi_a ov_h" style="top:260px;left:0px;height: 45px;width:44px;">
	<div class="size_tb_bg font borDW">
		<p style="width:44px"><span><?php if(empty($L1)){$L1='L1';}?><?php echo $L1;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:100px;left:6px;height: 45px;width:72px;">
	<div class="size_tb_bg font borDW">
		<p style="width:72px"><span><?php if(empty($LW)){$LW='LW';}?><?php echo $LW;?></span></p>
	</div>
</div>
<!-- 左边 -->
<!-- 右边 -->
<div class="posi_a ov_h" style="top:0px;left:200px;width: 60px;height:377px;">
	<div class="size_lr_bg font borDH">
		<p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:210px;left:70px;width: 60px;height:35px;">
	<div class="size_lr_bg font borDH">
		<p style="height:35px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:343px;left:70px;width: 60px;height:36px;">
	<div class="size_lr_bg font borDH">
		<p style="height:36px"><span><?php if(empty($S1)){$S1='S1';}?><?php echo $S1;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:146px;left:-60px;width: 60px;height:231px;">
	<div class="size_lr_bg font borDH">
		<p style="height:231px"><span><?php if(empty($LPH)){$LPH='LPH';}?><?php echo $LPH;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:90px;left:17px;width: 60px;height:56px;">
	<div class="size_lr_bg font borDH">
		<p style="height:56px"><span><?php if(empty($LH)){$LH='LH';}?><?php echo $LH;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:245px;left:180px;width: 60px;height:129px;">
	<div class="size_lr_bg font borDH">
		<p style="height:129px"><span><?php if(empty($GBH)){$GBH='GBH';}?><?php echo $GBH;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:276px;left:5px;width: 60px;height:65px;">
	<div class="size_lr_bg font borDH">
		<p style="height:65px"><span><?php if(empty($GBH1)){$GBH1='GBH1';}?><?php echo $GBH1;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:150px;left:5px;width: 60px;height:60px;">
	<div class="size_lr_bg font borDH">
		<p style="height:60px"><span><?php if(empty($P)){$P='P';}?><?php echo $P;?></span></p>
	</div>
</div>
<div class="posi_a ov_h" style="top:35px;left:5px;width: 60px;height:51px;">
	<div class="size_lr_bg font borDH">
		<p style="height:51px"><span><?php if(empty($D)){$D='D';}?><?php echo $D;?></span></p>
	</div>
</div>
<!-- 锁位 -->
</div>
</div>
<script>
var imgw = $('#imageE<?php echo $diff;?>').width();
var imgh = $('#imageE<?php echo $diff;?>').height();
$(".img<?php echo $diff;?>").width(imgw);$(".img<?php echo $diff;?>").height(imgh);
</script>
<!-- <div style="margin-left: 20px; float: left;">
<p>W：<input type="text" name="W" value="总宽"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>H：<input type="text" name="H" value="总高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>S：<input type="text" name="S" value="间距"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>F：<input type="text" name="F" value="边框"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LA：<input type="text" name="LA" value="搭接"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LPH：<input type="text" name="LPH" value="锁位高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LH：<input type="text" name="LH" value="执手高"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>LW：<input type="text" name="LW" value="执手宽"><font color="#ff000" size="0.5">*下单时带出</font></p>
<p>FH：<input type="text" name="FH" value="总高-边框*2+搭接*2-外框*6-间距*4"></p>
<p>FW：<input type="text" name="FW" value="花件最大宽"></p>
<p>A：<input type="text" name="A" value="(总高-边框*2+搭接*2-外框*3)/2"></p>
<p>D：<input type="text" name="D" value="总高-边框+搭接-外框*3-锁位高-执手高*2"></p>
<p>P：<input type="text" name="P" value="锁位高-边框+搭接-外框*2"></p>
<p>L：<input type="text" name="L" value="(总宽-边框*2+搭接*2-外框*5-花件最大宽-执手宽)/2"></p>
<?php
$frame=3; //外框厚度
?>
</div> -->
</body>
</html>
