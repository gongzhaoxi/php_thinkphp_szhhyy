<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:111:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/finance/finance_handle.html";i:1635496794;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
               <a href="javascript:void(0)">已处理订单</a>
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
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>                                       
                                        <th>订单编号</th>
                                        <th>经销商</th>
                                        <th>下单时间</th>
                                        <th>数量</th>
                                        <th>面积</th>
                                        <th>总金额</th>
                                        <th>已收款</th>
                                        <th>未收款</th>
                                        <th>折让(优惠)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['number']; ?></td>
                                        <td><?php echo $v['dealer']; ?></td>
                                        <td><?php echo date('Y/m/d',$v['addtime']); ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['total_price']; ?></td>
                                        <td><?php echo $v['have_pay']; ?></td>
                                        <td><td><?php echo round($v['total_price']-$v['have_pay']-$v['finance_rebate_price'],2) ?></td></td>
                                        <td><?php echo $v['finance_rebate_price']; ?></td>

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
    <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            
            //执行一个laydate实例
            laydate.render({
                elem: '#starttime' //指定元素
            });
            laydate.render({
                elem: '#endtime' //指定元素
            });
        });

    
        
    </script>

</html>