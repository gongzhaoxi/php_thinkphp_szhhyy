<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/dealerorder/priced.html";i:1751459670;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
<style type="text/css">
.layui-card-header{height:auto;}
</style>    
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
                                    <input type="text" name="keyword" placeholder="经销商名/订单号/电话/地址" autocomplete="off" class="layui-input" value="<?php echo $keyword_search; ?>">
                                </div>
                                 <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" id='start_time' name="start_time" placeholder="开始时间" autocomplete="off" class="layui-input" value='<?php echo $start_search; ?>'>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" id='end_time' name="end_time" placeholder="结束时间" autocomplete="off" class="layui-input" value='<?php echo $end_search; ?>'>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name='sales_name'>
                                        <option value=''>选择业务代表</option>
                                        <?php if(is_array($sales_name) || $sales_name instanceof \think\Collection || $sales_name instanceof \think\Paginator): $i = 0; $__LIST__ = $sales_name;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                        <option value='<?php echo $v['sales_name']; ?>' <?php if($sales_name_search == $v['sales_name']): ?>selected<?php endif; ?>><?php echo $v['sales_name']; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name='type'>
                                    	<option value=''>全部订单</option>
                                        <option value='1' <?php if($type_search == 1): ?>selected<?php endif; ?>>常规</option>
                                        <option value='2' <?php if($type_search == 2): ?>selected<?php endif; ?>>加急</option>
                                        <option value='3' <?php if($type_search == 3): ?>selected<?php endif; ?>>样板</option>
                                        <option value='4' <?php if($type_search == 4): ?>selected<?php endif; ?>>返修单</option>
                                        <option value='5' <?php if($type_search == 5): ?>selected<?php endif; ?>>单剪网</option>
                                        <option value='6' <?php if($type_search == 6): ?>selected<?php endif; ?>>单切料</option>
                                        <option value='7' <?php if($type_search == 7): ?>selected<?php endif; ?>>工程</option>
										<option value='8' <?php if($type_search == 8): ?>selected<?php endif; ?>>重做</option>
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
                                    <a class="layui-btn" href="<?php echo url('add'); ?>" style="display: none;">
                                        <i class="layui-icon"></i>添加订单
                                    </a>
                                </div>
<!--                                 <div class="layui-input-inline" style="float: right">
                                    
                                </div> -->
                            </form>
                        </div>
                        <blockquote class="layui-elem-quote" style="margin: 0 1%;padding: 8px;">
                            统计:&nbsp;&nbsp;&nbsp;&nbsp;总金额:<?php echo $statistics['price']; ?>&nbsp;&nbsp;&nbsp;&nbsp;总欠金额:<?php echo $statistics['nopay']; ?>
                        </blockquote>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form" lay-filter="demo">
                                <thead>
                                    <tr>   
                                        <th lay-data="{field:'add_type', minWidth:58}">方式</th>
                                        <th lay-data="{field:'zxc', minWidth:70}">状态</th> 
                                        <th lay-data="{field:'type', minWidth:75}">类型</th> 
                                        <th lay-data="{field:'number', minWidth:109}">订单编号</th>
                                        <!-- <th lay-data="{field:'dealer', minWidth:80}">经销商</th> -->
                                        <!-- <th lay-data="{field:'sales_name', minWidth:30}">业务代表</th> -->
                                        <th lay-data="{field:'send_address', minWidth:300}">送货地址</th>
                                        <!-- <th lay-data="{field:'addtime', minWidth:60}">下单时间</th>
                                        <th lay-data="{field:'count', minWidth:60,width:60}">数量</th>
                                        <th lay-data="{field:'area',minWidth:60}">面积</th> -->
                                        <th lay-data="{field:'total_price', minWidth:80}">总金额</th>
                                        
                                        <th lay-data="{field:'operate', minWidth:50}">操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php if($v['add_type'] == 0): ?>工厂<?php else: ?>经销商<?php endif; ?></td>
                                        <?php if($group_id == 1): ?>
                                        <td><?php if($v['dealer_status'] == 0): ?>
                                            <button class="layui-btn layui-btn-normal" onclick="check(<?php echo $v['id']; ?>)">点击审核</button>
                                            <?php else: if($v['status_text'] == '生产中'): ?>
                                                <a href='javascript:void(0)' style="color: #009688" onclick="xadmin.open('进度查询', '<?php echo url('Orderprogress/orderdetail',array('ordernum'=>$v['number'],'is_mobile'=>1)); ?>','', 170)">
                                                    <?php echo $v['status_text']; ?>
                                                </a>
                                                <?php else: ?><?php echo $v['status_text']; endif; endif; ?>
                                        </td>
                                        <?php else: ?>
                                        <td>
										<?php if(($v['status'] < 6) && ($v['status'] > 0)): if($v['status'] == 4): ?>
										    <a href='javascript:void(0)' class=" layui-btn layui-btn-sm"><?php echo $v['gxname']; ?></a>
										    <?php else: ?>
										    <?php echo $status_text[$v['status']]; endif; else: if($v['is_send'] == 0): ?>
										    待配送
										    <?php else: if($v['sign_time'] == 0): ?>
													待签收
												<?php else: ?>
													已签收
												<?php endif; endif; endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                        	<?php if($v['type'] == 1): ?>常规<?php endif; if($v['type'] == 2): ?>加急<?php endif; if($v['type'] == 3): ?>样板<?php endif; if($v['type'] == 4): ?>返修单<?php endif; if($v['type'] == 5): ?>单剪网<?php endif; if($v['type'] == 6): ?>单切料<?php endif; ?>
                                        </td>
                                        <td><?php echo $v['number']; ?></td>
                                        <!-- <td><?php echo $v['dealer']; ?></td> -->
                                        <!-- <td><?php echo $v['sales_name']; ?></td> -->
                                        <td><?php echo $v['send_address']; ?></td>
                                        <!-- <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td> -->
                                        <td><?php echo $v['total_price']; ?></td>
                                        
                                        <td class="td-manage">
                                            <?php if($group_id == 1): ?>
                                            <a title="查看" href="<?php echo url('order/edit',array('id'=>$v['id'])); ?>">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                            </a>
                                            <?php else: ?>
                                            <a title="查看" href="<?php echo url('dealerorder/edit',array('id'=>$v['id'])); ?>">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                            </a>
                                            <?php endif; if($group_id == 1 || $v['dealer_status'] == 0): ?>
                                            <a title="删除" onclick="member_del(this,<?php echo $v['id']; ?>)" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i>
                                            </a>
                                            <?php endif; ?>
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
    <script>layui.use(['laydate', 'form','table'],
        function() {
            $ = layui.jquery;
            var laydate = layui.laydate;
            var  form = layui.form;
            var table = layui.table;
            //转换静态表格
            table.init('demo', {
//                height: 689 //设置高度
                limit: 15 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
                //支持所有基础参数
            });


            $('#export_price2').click(function(){
                var start = $('#start_time').val();
                var end = $('#end_time').val();
                $('#form1').attr('action',"<?php echo url('exportPrice'); ?>")
                $('#form1').submit();
                $('#form1').attr('action',"")
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

       function check(id){
            layer.confirm('确认要审核吗？',
            function(index) {
                ajaxform("<?php echo url('dealerorder/check'); ?>",{id:id},1);
            });
       }
        /*删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('order/delOrder'); ?>",{id:id},1);
            });
        }
    
        
    </script>

</html>