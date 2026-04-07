<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:103:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/order/allorder.html";i:1769441940;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
	<link rel="stylesheet" href="/static/css/select.css">
    <body>
        <div class="x-nav">
            <span class="layui-breadcrumb">
               <a href="javascript:void(0)">已报价订单</a>
            </span> 
            
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
            </a>
        </div>
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            
                        </div>
                        <div class="layui-card-header">
                            <form class="layui-form layui-col-space5" action="" id='form1'>
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" id="keyword" placeholder="经销商名/订单号/电话/地址" autocomplete="off" class="layui-input" value="<?php echo $keyword_search; ?>">
                                </div>
																<div class="layui-input-inline layui-show-xs-block">
																    <input type="text" name="dealer_id" id="dealer_id" placeholder="经销商ID" autocomplete="off" class="layui-input" value="<?php echo $dealer_id; ?>">
																</div>
                                 <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" id='start_time' name="start_time" placeholder="开始时间" autocomplete="off" class="layui-input" value='<?php echo $start_search; ?>'>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" id='end_time' name="end_time" placeholder="结束时间" autocomplete="off" class="layui-input" value='<?php echo $end_search; ?>'>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name='sales_name' id="sales_name">
                                        <option value=''>选择业务代表</option>
                                        <?php if(is_array($sales_name) || $sales_name instanceof \think\Collection || $sales_name instanceof \think\Paginator): $i = 0; $__LIST__ = $sales_name;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                        <option value='<?php echo $v['sales_name']; ?>' <?php if($sales_name_search == $v['sales_name']): ?>selected<?php endif; ?>><?php echo $v['sales_name']; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline x-city" id="end">
                                    <div class="layui-input-inline" style="width:120px">
                                        <select name="province" lay-filter="province" id="province">
                                            <option value="">请选择省</option>
                                        </select>
                                    </div>
                                    <div class="layui-input-inline" style="width:120px">
                                        <select name="city" lay-filter="city" id="city">
                                            <option value="">请选择市</option>
                                        </select>
                                    </div>
                                    <div class="layui-input-inline" style="width:120px">
                                        <select name="area" lay-filter="area" id="area">
                                            <option value="">请选择县/区</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name='type' xm-select="type" xm-select-show-count="1">
                                        <option value=''>订单类型</option>
                                        <?php foreach($order_type as $k=>$v): ?>
                                        <option value='<?php echo $k; ?>' <?php if(in_array($k,$type)): ?>selected<?php endif; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>									
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn layui-btn-normal" type='button' id='export_price2'>
                                        导出数据
                                    </button>
                                </div>
                                <div class="layui-input-inline" style="float: right">
                                    <a class="layui-btn" href="<?php echo url('add'); ?>">
                                        <i class="layui-icon"></i>添加订单
                                    </a>
                                </div>
                            </form>
                        </div>
                        <blockquote class="layui-elem-quote" style="margin: 0 1%;padding: 8px;">
                            统计:&nbsp;&nbsp;&nbsp;&nbsp;订单数量:<?php echo $statistics['count']; ?> &nbsp;&nbsp;&nbsp;&nbsp;面积:<?php echo $statistics['area']; ?>&nbsp;&nbsp;&nbsp;&nbsp;总金额:<?php echo $statistics['price']; ?>&nbsp;&nbsp;&nbsp;&nbsp;总欠金额:<?php echo $statistics['nopay']; ?>
                            &nbsp;&nbsp;待生产:<?php echo $statistics['wait_product']; ?>㎡ &nbsp;&nbsp;&nbsp;&nbsp;生产中:<?php echo $statistics['producting']; ?>㎡&nbsp;&nbsp;&nbsp;&nbsp;已入库:<?php echo $statistics['into']; ?>㎡
                        </blockquote>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form" lay-filter="demo">
                                <thead>
                                    <tr>                                       
                                        <th lay-data="{field:'number', minWidth:90}">订单编号</th>
                                        <th lay-data="{field:'dealer', minWidth:80}">经销商</th>
                                        <th lay-data="{field:'type', minWidth:30}">订单类型</th>
                                        <th lay-data="{field:'sales_name', minWidth:30}">业务代表</th>
                                        <th lay-data="{field:'send_address', minWidth:200}">送货地址</th>
                                        <th lay-data="{field:'addtime', minWidth:60}">下单时间</th>
                                        <th lay-data="{field:'count', minWidth:60,width:60}">数量</th>
                                        <th lay-data="{field:'area',minWidth:60}">面积</th>
                                        <th lay-data="{field:'total_price', minWidth:80}">总金额</th>
                                        <th lay-data="{field:'zxc', minWidth:60}">状态</th>
                                        <th lay-data="{field:'operate', minWidth:50}">操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['number']; ?></td>
                                        <td><?php echo $v['dealer']; ?> ID：<?php echo $v['dealer_id']; ?></td>
                                        <td><?php echo $order_type[$v['type']]; ?></td>
                                        <td><?php echo $v['sales_name']; ?></td>
                                        <td><?php echo $v['send_address']; ?></td>
                                        <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['total_price']; ?></td>
                                        <!-- 2024-06-4 注释掉，<td><?php if(($v['status'] == 1) && ($v['status2'] == 1)): ?>已报价
                                            <?php else: if($v['status'] > 1): if($v['status'] == 4): ?>
                                                    <a href='javascript:void(0)' class=" layui-btn layui-btn-sm" onclick="xadmin.open('进度查询', '<?php echo url('Orderprogress/orderdetail',array('ordernum'=>$v['number'])); ?>',1200, 600)"><?php echo $v['gxname']; ?></a>
                                                    <?php else: ?>
                                                    <?php echo $status_text[$v['status']]; endif; else: if($v['status'] == 6): ?>
                                                    <a href='javascript:void(0)' class=" layui-btn layui-btn-sm" onclick="xadmin.open('进度查询', '<?php echo url('Orderprogress/orderdetail',array('ordernum'=>$v['number'])); ?>',1200, 600)"><?php echo $v['gxname']; ?></a>
                                                    <?php else: ?>
                                                    <?php echo $status_text2[$v['status2']]; endif; endif; endif; ?>
                                        </td> -->
										<td><?php if($v['status'] == 1): ?>已报价
										    <?php else: if(($v['status'] < 6) && ($v['status'] > 1)): if($v['status'] == 4): ?>
										            <a href='javascript:void(0)' class=" layui-btn layui-btn-sm" onclick="xadmin.open('进度查询', '<?php echo url('orderprogress/orderdetailnew',array('ordernum'=>$v['number'])); ?>',1200, 600)"><?php echo $v['gxname']; ?></a>
										            <?php else: ?>
										            <?php echo $status_text[$v['status']]; endif; else: if($v['is_send'] == 0): ?>
										            待配送
										            <?php else: if($v['sign_time'] == 0): ?>
															待签收
														<?php else: ?>
															已签收
														<?php endif; endif; endif; endif; ?>
										</td>
                                        
                                        <td class="td-manage">
                                            <a title="查看" href="<?php echo url('edit',array('id'=>$v['id'])); ?>">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                            </a>
<!--                                            <?php if($uid == 1): ?>
                                            <a title="删除" onclick="member_del(this,<?php echo $v['id']; ?>)" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i>
                                            </a>
                                            <?php endif; ?> -->
                                        </td>
                                    </tr>
                                     <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body ">
                            <div class="page">
                                    <?php echo $page; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="/static/js/jquery.min.js" type="text/javascript"></script>
    <script src="/static/js/xcity.js" type="text/javascript"></script>
    <script>
	layui.config({
		base: "/static/js/",
	}).extend({
		select: "select",
	}).use(['laydate', 'form','table','code','select'],
        function() {
            $ = layui.jquery;
            var laydate = layui.laydate;
            form = layui.form;
            var table = layui.table,select= layui.select;
            layui.code();

            $('#end').xcity('<?php echo $search['province']; ?>','<?php echo $search['city']; ?>','<?php echo $search['area']; ?>');
            //转换静态表格
            table.init('demo', {
//                height: 689 //设置高度
                limit: 15 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
                //支持所有基础参数
            }); 
            //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('financeNoHandle'); ?>",{id:1},function(obj){                         
                    $('#finance-no-handle',parent.document).text(obj.data);                      
                 },'json');
                 
                 $.post("<?php echo url('carorder/noHandleCount'); ?>",{id:1},function(obj){                         
                    $('#no-handle-count',parent.document).text(obj.data);                      
                 },'json');
                 
                 $('#export_price2').click(function(){
                     var start = $('#start_time').val();
                     var end = $('#end_time').val();
                     var keword = $('#keyword').val();
										 var keword = $('#dealer_id').val();
                     var sales_name = $('#sales_name').val();
                     var province = $('#province option:selected').val();
                     var city = $('#city option:selected').val();
                     var area = $('#city option:selected').val();
                     if(start=='' && end=='' && keword=='' && province=='' && city=='' && area==''){
                         layer.msg('至少选择一个筛选条件',{icon:2});
                         return;
                     }
                     $('#form1').attr('action',"<?php echo url('exportPrice'); ?>")
                     $('#form1').submit();
                     $('#form1').attr('action',"")
                 })
            })
            
            
            //执行一个laydate实例
            laydate.render({
                elem: '#start_time' //指定元素
            });

            //执行一个laydate实例
            laydate.render({
                elem: '#end_time' //指定元素
            });
            
            // 监听全选
            form.on('checkbox(checkall)', function(data){

              if(data.elem.checked){
                $('tbody input').prop('checked',true);
              }else{
                $('tbody input').prop('checked',false);
              }
              form.render('checkbox');
            });
        });

       function sendCar(id){
            layer.confirm('确认要发送吗？',
            function(index) {
                ajaxform("<?php echo url('sendCar'); ?>",{id:id},1);
            });
       }
       function sendCar2(id,status){
            layer.confirm('确认要发送吗？',
            function(index) {
                ajaxform("<?php echo url('sendCar2'); ?>",{id:id,status:status},1);
            });
       }
        /*删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('delOrder'); ?>",{id:id},1);
            });
        }
    
        
    </script>
</html>