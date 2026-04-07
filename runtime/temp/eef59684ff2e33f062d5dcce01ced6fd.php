<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:108:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/carorder/read_order.html";i:1710724290;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>

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
<style>
    .alldisplay td{ text-align: center;}
    .cporgress{width: 30%;position: relative;bottom: 30px;left: 30%;display: none;}
</style>
<script>
        var z=0;
        var total_good=<?php echo $total_good; ?>;
        function calculate_btn(){
            
            
            //验证需要算料的产品是否有填写算料信息
            var opid = "<?php echo $opids; ?>";
            $.post("<?php echo url('Carorder/calculateCheck'); ?>",{opid:opid},function(obj){                                                     
                 if(obj.code == 0){
                     layer.load(0, {shade: false});
                     $('.cporgress').css('display','block');
                     $('#form1').submit();
                 }else{
                     layer.msg(obj.msg,{icon:2});
                 }
             },'json'); 
            
            
        }

        
	//回调
	function receiver(data){
            z++;
            progress(z);
            if(total_good==z){
                layer.closeAll();
                clear();
                z = 0;
                xadmin.open('编辑产品', '/admin/carorder/allCalculate/.html?order_id='+data);
            }

	}
	//直接打开算料结果
    function open_result(data) {
        layer.closeAll();
        clear();
        z = 0;
        xadmin.open('算料结果', '/admin/carorder/allCalculate/.html?order_id='+data);
    }
</script>  
<body>
    <div class="x-nav">
        <span class="layui-breadcrumb">
            <!--<a href="">订单信息</a>-->
            <a href="<?php echo url('noHandle'); ?>?<?php echo $getstr; ?>"><i class="icon iconfont">&#xe697</i>返回</a>
        </span> 

        <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
            <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
        </a>
    </div>
    <form class="layui-form layui-col-space5" target="imgiframe" action="<?php echo url('Carorder/allIframe'); ?>" method="post" id="form1">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">

                <div class="layui-col-md12 layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            <blockquote class="layui-elem-quote">基础信息
                            </blockquote>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">订单编号</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="number" class="layui-input" readonly="true" value="<?php echo $res['number']; ?>">
                                </div>

                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">要求交货时间</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="end_time"  readonly="true" class="layui-input" value="<?php echo $res['end_time']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">经销商名称</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="dealer" required  lay-verify="required" readonly="true"  class="layui-input" value="<?php echo $res['dealer']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">电话</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="phone" required  lay-verify="phone" readonly="true"  class="layui-input" value="<?php echo $res['phone']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="address" class="layui-input" value="<?php echo $res['address']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">送货地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="send_address"  class="layui-input" value="<?php echo $res['send_address']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">订单类型</label>
                                <div class="layui-input-block" style="width: 30%">
                                    <label class="layui-form-label" style='text-align: left'><?php echo $order_type[$res['type']]; ?></label>
                                </div>
                            </div>
							<div class="layui-form-item">
							    <label class="layui-form-label">是否打印标签</label>
							    <div class="layui-input-block" style="width: 63%; color: #ff0000;" > 
							        <label class="layui-form-label" style='text-align: left'>
										<?php if(($res['print_label'] == 0)): ?>
										需要
										<?php else: ?>
										不需要
										<?php endif; ?>
									</label>
							    </div>
							</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注</label>
                                <div class="layui-input-block" style="width: 63%;"> 
                                    <input type="text" name="note"  class="layui-input" value="<?php echo $res['note']; ?>" readonly="true">
                                </div>
                            </div>
                            <table class="layui-table">
                                <tbody class='alldisplay'>
                                    <tr>
                                        <td>总面积:<span style='color: red'><?php echo $res['product_area']; ?></span></td>

                                        <td>总数量:<span style='color: red'><?php echo $res['count']; ?></span></td>

                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <div class="layui-col-md12">
                    <div class="layui-card">


                        <div class="layui-card-header">
                            <?php if(($res['status2'] == 5) or ($res['status'] == 3)): ?>
                            <button class="layui-btn" type="button" onclick="check(<?php echo $orderid; ?>)" id="check-btn">
                                <i class="layui-icon"></i>确认生产
                            </button>
                            <?php endif; if($res['status2'] == 2): ?>
                            <button class="layui-btn" type="button" onclick="order_check(<?php echo $orderid; ?>)" id="check-btn">
                                <i class="layui-icon"></i>发给营运
                            </button>
                            <?php endif; ?>
                            <button class="layui-btn layui-btn-normal" type='button' onclick='calculate_btn()' >
                                <i class="layui-icon"></i>一键所有算料
                            </button>
                            
                            <button class="layui-btn layui-btn-warm" type='button' onclick="xadmin.open('算料结果', '<?php echo url('allCalculate',array('order_id'=>$orderid)); ?>', 1200, 600)">
                                <i class="layui-icon"></i>查看算料结果
                            </button>
                            <?php if(($res['status2'] == 5) or ($res['status'] == 3)): ?>
                            <button class="layui-btn" type="button" onclick="zcheck(<?php echo $orderid; ?>)" id="check-btn">
                                <i class="layui-icon"></i>不选结构下达车间
                            </button>
                            <?php endif; ?>
                            <div class="layui-progress layui-progress-big cporgress" lay-filter="demo" lay-showPercent="true">
                                <div class="layui-progress-bar" lay-percent="0%"></div>
                            </div>
                            
                        </div>
                        <style type="text/css">
                          .layui-card-body > div{ margin-top: 0px; }
                          .layui-form th,.layui-form td{text-align: center;}
                        </style>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">产品信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;">
                                <thead>
                                    <tr>

                                        <th width="30">编号</th>
																				<th>安装位置</th>
                                        <th>名称</th>
                                        <th>材质</th>
                                        <th>型号</th>
                                        <th>铝材/花件颜色</th>
                                        <th>单位</th>
                                        <th>宽</th>
                                        <th>高</th>
                                        <th>个数</th>
                                        <th>产品面积</th>
                                        <th>单价</th>
                                        <th>折后价</th>
                                        <th>总金额</th>
                                        <th>窗型结构</th>
                                        <th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($product) || $product instanceof \think\Collection || $product instanceof \think\Paginator): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>

                                        <td width="30"><?php echo $i; ?></td>
																				<td><?php echo $v['position']; ?></td>
                                        <td><?php echo $v['name']; ?></td>
                                        <td><?php echo $v['material']; ?></td>
                                        <td><?php echo $v['flower_type']; ?></td>
                                        <td><?php echo $v['color_name']; ?></td>
                                        <td>㎡</td>
                                        <td><?php echo $v['all_width']; ?></td>
                                        <td><?php echo $v['all_height']; ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['product_area']; ?></td>
                                        <td><?php echo $v['price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><img src='/upload/<?php echo $v['structure']; ?>' height="60"/></td>
                                        
                                        <?php if($v['order_type'] == 1): ?>
                                        <td class="td-manage">
                                            <a title="查看手工单" onclick="xadmin.open('查看手工单', '<?php echo url('order/editHandMade',array('id'=>$v['op_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>
                                        <?php else: ?>
                                        <td class="td-manage">
                                            <input name="op_id[]" type="hidden" value="<?php echo $v['op_id']; ?>"/>
                                            <input name="series_id[]" type="hidden" value="<?php echo $v['series_id']; ?>"/>
                                            <input name="structure_id[]" type="hidden" value="<?php echo $v['structure_id']; ?>"/>
                                            <a title="查看产品" onclick="xadmin.open('查看产品', '<?php echo url('Carorder/carEditInfo',array('id'=>$v['op_id'])); ?>',1200,600)" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">组合单信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="group-table" lay-filter="group-table" style="margin: 0px;">
                                  <thead>
                                    <tr>
                                        
                                        <th>编号</th>
                                        <th>总宽</th>
                                        <th>总高</th>
                                        <th>产品面积</th>
                                        <th>报价总个数</th>                                        
                                        <th>生成总个数</th>
                                        <th>总金额</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($group) || $group instanceof \think\Collection || $group instanceof \think\Paginator): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $v['width']; ?></td>
                                        <td><?php echo $v['height']; ?></td>
                                        <td><?php echo $v['product_area']; ?></td>
                                        <td><?php echo $v['price_count']; ?></td>
                                        <td><?php echo $v['calculate_count']; ?></td>
                                        <td><?php echo $v['total_price']; ?></td>
                                        <td class="td-manage">
                                            <a title="查看" onclick="open_group('编辑产品', '<?php echo url('Carorder/addGroup',array('id'=>$v['og_id'],'order_id'=>$v['order_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody> 
                                
                            </table>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">原材料信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;">
                                <thead>
                                    <tr>

                                        <th width="30">编号</th>
                                        <th>安装位置</th>
                                        <th>材质/型号</th>
                                        <th>铝材/花件颜色</th>
                                        <th>单位</th>
                                        <th>宽</th>
                                        <th>高</th>
                                        <th>个数</th>
                                        <th>产品面积</th>
                                        <th>单价</th>
                                        <th>折后价</th>
                                        <th>总金额</th>
                                        <th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($material) || $material instanceof \think\Collection || $material instanceof \think\Paginator): $i = 0; $__LIST__ = $material;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>

                                        <td width="30"><?php echo $i; ?></td>
                                        <td><?php echo $v['name']; ?></td>
                                        <td><?php echo $v['type']; ?></td>
                                        <td><?php echo $v['color']; ?></td>
                                        <td>㎡</td>
                                        <td><?php echo $v['width']; ?></td>
                                        <td><?php echo $v['height']; ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['product_area']; ?></td>
                                        <td><?php echo $v['price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td class="td-manage">
                                            <a title="查看" onclick="xadmin.open('编辑产品', '<?php echo url('Carorder/readMaterial',array('id'=>$v['om_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>
                            </table>
                        </div>

                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">定制类产品</blockquote>
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;" lay-filter="diy">
                                <thead>
                                <tr>
																		<th lay-data="{field:'position'}">安装位置</th>
                                    <th lay-data="{field:'material'}">名称</th>
                                    <th lay-data="{field:'color_name'}">铝型/花件颜色</th>
                                    <th lay-data="{field:'all_width'}">总宽</th>
                                    <th lay-data="{field:'all_height'}">总高</th>
                                    <th lay-data="{field:'count'}">数量</th>
                                    <th lay-data="{field:'product_area'}">产品面积</th>
                                    <th lay-data="{field:'area'}">报价面积</th>
                                    <th lay-data="{field:'price'}">单价</th>
                                    <th lay-data="{field:'all_price'}">总价</th>
                                    <th lay-data="{field:'diy_pic'}">产品图片</th>
                                    <th lay-data="{field:'c'}" width="12%">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($diy) || $diy instanceof \think\Collection || $diy instanceof \think\Paginator): $i = 0; $__LIST__ = $diy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <tr>
																		<td><?php echo $v['position']; ?></td>
                                    <td><?php echo $v['material']; ?></td>
                                    <td><?php echo $v['color_name']; ?></td>
                                    <td><?php echo $v['all_width']; ?></td>
                                    <td><?php echo $v['all_height']; ?></td>
                                    <td><?php echo $v['count']; ?></td>
                                    <td><?php echo $v['product_area']; ?></td>
                                    <td><?php echo $v['area']; ?></td>
                                    <td><?php echo $v['price']; ?></td>
                                    <td><?php echo $v['all_price']; ?></td>
                                    <td><img src="/upload/<?php echo $v['diy_pic']; ?>"></td>
                                    <td class="td-manage">
                                        <a title="查看" onclick="xadmin.open('编辑', '<?php echo url('diyedit',array('id'=>$v['op_id'])); ?>',1200, 600)" href="javascript:;">
                                            <i class="layui-icon">&#xe63c;</i></a>
                                        <!-- <a title="删除" onclick="member_del(this, <?php echo $v['op_id']; ?>)" href="javascript:;">
                                            <i class="layui-icon">&#xe640;</i></a> -->
                                    </td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <input name="order_id" type="hidden" value="<?php echo $orderid; ?>"/>
            <?php if(is_array($group_product) || $group_product instanceof \think\Collection || $group_product instanceof \think\Paginator): $i = 0; $__LIST__ = $group_product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
            <div>
            <input name="op_id[]" type="hidden" value="<?php echo $v['op_id']; ?>"/>
            <input name="series_id[]" type="hidden" value="<?php echo $v['series_id']; ?>"/>
            <input name="structure_id[]" type="hidden" value="<?php echo $v['structure_id']; ?>"/>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <iframe name="imgiframe" width="0" height="0"></iframe>
        </div>
    </form>
</body>
<script>
    layui.use(['laydate', 'form','element'],
            function () {
            var laydate = layui.laydate;
            var form = layui.form;
            var element = layui.element;



            //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('noHandleCount'); ?>",{id:1},function(obj){                         
                    $('#no-handle-count',parent.document).text(obj.data);                      
                 },'json');
               
            })
            
            //执行一个laydate实例
            laydate.render({
            elem: '#end_date' //指定元素
            });
            //监听提交表单
            form.on('submit(edit)',
                    function (data) {
                    ajaxform("<?php echo url('edit'); ?>", data.field, 1);
                    return false;
                    });
            });
        
            
        function open_group(title, url){
            var index = layer.open({
                type: 2,
                area: [ '1200px',  '600px'],
                fix: false, //不固定
                maxmin: true,
                shadeClose: false,
                shade: 0.4,
                title: title,
                content: url,
                cancel: function(index, layero){ 
                    layer.close(index);
                    window.location.reload(); //刷新父页面
                    return false; 
                }
            });
            layer.full(index);
        }  
            
      //进度条   
      function progress(number){
          var all_count =<?php echo $total_good; ?>;         
          var one = Math.round(100/all_count);
          var progress = number*one;
          element.progress('demo', progress+'%');
      }      
     //清空进度条
     function clear(){
            clearInterval();
            $('.cporgress').css('display','none');
            element.progress('demo', '0%');
            element.render();
        }
     //车间最终审核       
     function check(id){
         var status = <?php echo $res['status']; ?>;
         var status2 = <?php echo $res['status2']; ?>;
         var field = status2>status?'status2':'status';
         var status_value = status2>status?status2:status
         layer.confirm('确定通过审核',function(){
             ajaxform("<?php echo url('check'); ?>", {id:id,field:field,status:status_value}, 1);
         });
         
     }   
     function zcheck(id){
         var status = <?php echo $res['status']; ?>;
         var status2 = <?php echo $res['status2']; ?>;
         var field = status2>status?'status2':'status';
         var status_value = status2>status?status2:status
         layer.confirm('确定通过审核',function(){
             ajaxform("<?php echo url('check'); ?>", {id:id,field:field,status:status_value,select:1}, 1);
         });
         
     }  
     //发给营运
     function order_check(id){
         layer.confirm('确定通过审核',function(){
             ajaxform("<?php echo url('orderCheck'); ?>", {id:id}, 1);
         });
         
     }   
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>

</script>
</html>