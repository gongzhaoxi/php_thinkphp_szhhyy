<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:109:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/carorder/read_product.html";i:1635496792;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<style>

</style>
<script>
	//保存图片成功后，跳转的路径
	var jumpurl="<?php echo url('Carorder/calculate',array('series_id'=>'1','op_id'=>'1','structure_id'=>'1')); ?>";
	function submitform(){
		var index = layer.load(0, {shade: false}); 
		$("#calform").submit();
	}
	//回调
	function receiver(data){
		
		layer.closeAll();
		
		if(parseInt(data.status)==1){
//			layer.msg(String(data.msg));
                    var order_id = data.order_id;
                    xadmin.open('编辑产品', '/admin/carorder/allCalculate/.html?order_id='+order_id+'&op_id=<?php echo $info['op_id']; ?>');
		}else{
			layer.msg(String(data.msg));
		}
	}
</script>  
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form" target="imgiframe" action="<?php echo url('Carorder/iframeImg'); ?>" method="post" id="calform">
                <blockquote class="layui-elem-quote">报价信息</blockquote> 
                <table class="layui-table layui-form">
                    <thead>
                        <tr>
                            <th>名称</th>
                            <th>材质</th>
                            <th>型号</th>
                            <th>铝材/花件颜色</th>
                            <th>单位</th>
                            <th>宽</th>
                            <th>高</th>
                            <th>个数</th>
                            <th>面积</th>
                            <th>纱网</th>
                            <th>弧高/深</th>
                            <th>备注</th>
                        </tr>
                        
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $info['name']; ?></td>
                            <td><?php echo $info['material']; ?></td>
                            <td><?php echo $info['flower_type']; ?></td>
                            <td><?php echo $info['color_name']; ?></td>
                            <td>㎡</td>
                            <td><?php echo $info['all_width']; ?></td>
                            <td><?php echo $info['all_height']; ?></td>
                            <td><?php echo $info['count']; ?></td>
                            <td><?php echo $info['area']; ?></td>
                            <td>纱网</td>
                            <td><?php echo $info['arc_height']; ?></td>
                            <td><?php echo $info['note']; ?></td>
                        </tr>

                    </tbody>
                </table>
                <?php if($info['order_type'] != 2): ?>
                <blockquote class="layui-elem-quote">算料信息</blockquote>
                <div class='layui-col-md6'>
                <table class="layui-table layui-form">
                    <thead>
                        <tr>
                            <th>间距</th>
                            <?php if(is_array($fixed) || $fixed instanceof \think\Collection || $fixed instanceof \think\Paginator): $i = 0; $__LIST__ = $fixed;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <th><?php echo $v['name']; ?></th>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            <th>把手位</th>
                            <th>锁位高</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $info['spacing']; ?></td>
                            <?php if(is_array($fixed) || $fixed instanceof \think\Collection || $fixed instanceof \think\Paginator): $i = 0; $__LIST__ = $fixed;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <td><?php echo $v['fixed']; ?></td>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            <td><?php echo $hands['name']; ?></td>
                            <td><?php echo $info['lock_position']; ?></td>
                        </tr>

                    </tbody>
                </table>
                </div>
                <div class='layui-col-md12'>
                    <blockquote class="layui-elem-quote">选中结构</blockquote> 
                    <img src="/upload/<?php echo $info['structure']; ?>" style="max-height: 150px;"/>
                </div>
                <div class="layui-col-md12">
                    <input name="series_id" type="hidden" value="<?php echo $info['series_id']; ?>"/>
                    <input name="op_id" type="hidden" value="<?php echo $info['op_id']; ?>"/>
                    <input name="order_id" type="hidden" value="<?php echo $info['order_id']; ?>"/>
                    <input name="structure_id" type="hidden" value="<?php echo $info['structure_id']; ?>"/>
                    <a class="layui-btn" id="print"  style="margin-left: 40%;" href="javascript:void(0);" onclick="javascript:submitform();">立即算料</a>              
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
	<iframe name="imgiframe" width="0" height="0"></iframe>

</body>

</html>