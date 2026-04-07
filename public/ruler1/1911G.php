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
.size_tb_bg{background:url(/static/images/left_1.png) left center,url(/static/images/right_1.png) right center;}
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

<!-- 砂网 -->

<!-- 标尺寸 -->
<!-- 大边坐标 -->
<!-- <div class="posi_a ov_h" style="top:-30px;left:0px;height: 30px;width:241px;"> -->
	<!-- <div class="size_tb_bg font borDW" style="height: 30px;width:241px;"> -->
		<!-- <p style="width:241px; height: 30px;"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p> -->
	<!-- </div> -->
<!-- </div> -->
<!-- <div class="posi_a ov_h" style="top:-30px;left:240px;height: 30px;width:237px;"> -->
	<!-- <div class="size_tb_bg font borDW" style="height: 30px;width:237px;"> -->
		<!-- <p style="width:237px; height: 30px;"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p> -->
	<!-- </div> -->
<!-- </div> -->
<!-- <div class="posi_a ov_h" style="top:-30px;left:476px;height: 30px;width:242px;"> -->
	<!-- <div class="size_tb_bg font borDW" style="height: 30px;width:242px;"> -->
		<!-- <p style="width:242px; height: 30px;"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p> -->
	<!-- </div> -->
<!-- </div> -->
<div class="posi_a ov_h" style="top:-30px;left:0px;height: 30px;width:755px;">
	<div class="size_tb_bg font borDW" style="height: 30px;width:755px;">
		<p style="width:755px; height: 30px;"><span><?php if(empty($W)){$W='W';}?><?php echo $W;?></span></p>
	</div>
</div>
<div class="size_lr_bg font posi_a borSH" style="height:350px;top:27px;left:0px;writing-mode:tb-rl;"><p style="height:350px"><span><?php if(empty($H1)){$H1='H1';}?><?php echo $H1;?></span></p></div>
<!-- 横向坐标 -->
<div class="font posi_a borSW" style="width:18px;top:20px;left:0px;"><p style="width:18px"><span><?php if(empty($L)){$L='L';}?><?php echo $L;?></span></p></div>
<!-- <div class="font posi_a borSW" style="width:25px;top:84px;left:229px;"><p style="width:25px"><span><?php if(empty($L2)){$L2='L2';}?><?php echo $L2;?></span></p></div> -->
<div class="font posi_a borSW" style="width:19px;top:20px;left:720px;"><p style="width:19px"><span><?php if(empty($L1)){$L1='L1';}?><?php echo $L1;?></span></p></div>
<div class="size_tb_bg font posi_a borSW" style="width:314px;top:305px;left:55px;"><p style="width:314px"><span><?php if(empty($BW)){$BW='BW';}?><?php echo $BW;?></span></p></div>
<div class="font posi_a borSW" style="width:316px;top:21px;left:388px;"><p style="width:316px"><span><?php if(empty($S)){$S='S';}?><?php echo $S;?></span></p></div>
<div class="font posi_a borSW" style="width:316px;top:342px;left:388px;"><p style="width:316px"><span><?php if(empty($S1)){$S1='S1';}?><?php echo $S1;?></span></p></div>
<!-- 竖向坐标 -->
<div class="size_lr_bg font posi_a borSH" style="height:265px;top:66px;left:54px;writing-mode:tb-rl;"><p style="height:265px"><span><?php if(empty($BH)){$BH='BH';}?><?php echo $BH;?></span></p></div>
<div class="size_lr_bg font posi_a borSH" style="height:377px;top:0px;left:755px;writing-mode:tb-rl;"><p style="height:377px"><span><?php if(empty($H)){$H='H';}?><?php echo $H;?></span></p></div>



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
<p>FH：<input type="text" name="FH" value="总高-边框*2+搭接*2-外框*8-间距*6"></p>
<p>FW：<input type="text" name="FW" value="花件最大宽"></p>
<p>A：<input type="text" name="A" value="(总高-边框*2+搭接*2-外框*3)/2"></p>
<p>D：<input type="text" name="D" value="总高-边框+搭接-外框*3-锁位高*2-执手高"></p>
<p>P：<input type="text" name="P" value="锁位高-边框+搭接-外框*2"></p>
<p>L：<input type="text" name="L" value="(总宽-边框*2+搭接*2-外框*5-花件最大宽-执手宽)/2"></p>
<?php
$frame=3; //外框厚度
?>
</div> -->
</body>
</html>
