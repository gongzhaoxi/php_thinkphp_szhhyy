<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:105:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/finance/no_handle.html";i:1741329605;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    
    <body>
         <div class="x-nav">
            <span class="layui-breadcrumb">
               <a href="javascript:void(0)">待处理订单</a>
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
                            <form class="layui-form layui-col-space5" >
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" placeholder="请输入订单号/经销商名称" autocomplete="off" class="layui-input" value='<?php echo $name; ?>'>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="starttime" id="starttime" placeholder="下单开始时间" autocomplete="off" class="layui-input" value="<?php echo $start_time; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="endtime" id="endtime" placeholder="下单结束时间" autocomplete="off" class="layui-input" value="<?php echo $end_time; ?>">
                                </div>    
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                                
                            </form>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form" lay-filter="demo">
                                <thead>
                                    <tr>                                       
                                        <th lay-data="{field:'number', minWidth:90}">订单编号</th>
                                        <th lay-data="{field:'type', minWidth:80}">订单类型</th>
                                        <th lay-data="{field:'dealer', minWidth:80}">经销商</th>
										<th lay-data="{field:'send_address', minWidth:300}">地址</th>
                                        <th lay-data="{field:'addtime', minWidth:60}">下单时间</th>
                                        <th lay-data="{field:'count', minWidth:60,width:60}">数量</th>
                                        <th lay-data="{field:'area',minWidth:60}">面积</th>
                                        <th lay-data="{field:'total_price', minWidth:80}">总金额</th>
                                        <th lay-data="{field:'have_pay', minWidth:80}">已收款</th>
                                        <th lay-data="{field:'no_pay', minWidth:80}">未收款</th>
                                        <th lay-data="{field:'finance_rebate_price', minWidth:80}">折让(优惠)</th>
                                        <th lay-data="{field:'sign_time', minWidth:80}">是否签收</th>
<!--                                        <th>收款方式</th>-->
                                        <th lay-data="{field:'operate', minWidth:80}">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['number']; ?></td>
                                        <td><?php echo $order_type[$v['type']]; ?></td>
                                        <td><?php echo $v['dealer']; ?></td>
										<td><?php echo $v['send_address']; ?></td>
                                        <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['total_price']; ?></td>
                                        <td><?php echo $v['have_pay']; ?>
                                            <a href="javascript:void(0)" onclick="xadmin.open('收款记录 订单编号:<?php echo $v['number']; ?>','<?php echo url('finance/paidRecord',array('id'=>$v['id'])); ?>',750,500)" style="margin-left: 15px;color: #1b96c8">收款详细</a>
                                        </td>
                                        <td><?php echo round($v['total_price']-$v['have_pay']-$v['finance_rebate_price'],2) ?></td>
                                        <td><?php echo $v['finance_rebate_price']; ?></td>
                                        <td><?php if($v['sign_time'] != 0): ?>已签收<?php else: ?>未签收<?php endif; ?></td>
<!--                                        <td><?php echo $pay_type[$v['pay_type']]; ?></td>-->
                                        <td class="td-manage">
                                            <a title="查看详细" href="<?php echo url('finance/readOrder',array('id'=>$v['id'])); ?>">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                            </a>
                                            <?php if($action == 'nohandle'): ?>
                                            <a title="返回营运" href="javascript:void(0)" onclick="back(<?php echo $v['id']; ?>)">
                                                <i class=" layui-icon">&#xe633;</i></a>
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
        </div><iframe name="imgiframe" width="0" height="0"></iframe>
    </body>
    <script>layui.use(['laydate', 'form','table'],
        function() {
            $ = layui.jquery;
            var laydate = layui.laydate;
            var  form = layui.form;
			var table = layui.table;
			
			//转换静态表格
			table.init('demo', {
    			//height: 689 //设置高度
    			limit: 15 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
    			//支持所有基础参数
			}); 
            
            //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('order/financeNoHandle'); ?>",{id:1},function(obj){                         
                    $('#finance-no-handle',parent.document).text(obj.data);                      
                 },'json');
            })
            
            //执行一个laydate实例
            laydate.render({
                elem: '#starttime' //指定元素
            });
            laydate.render({
                elem: '#endtime' //指定元素
            });
        });
     function back(id){
            layer.confirm('确认要返回营运吗？',
            function(index) {
                ajaxform("<?php echo url('back'); ?>",{id:id},1);
            });
       }    
    
        
    </script>

</html>