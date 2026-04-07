<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:110:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/carorder/read_material.html";i:1683506981;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .material-div{
        border: 1px solid #fafafa;height:50px;background-color: #fff;position: absolute;
        width: 100%;min-height: 100px;left: 0; text-align: center;z-index: 9999;display: none;
    }
</style>   
<body>
     <form class="layui-form layui-col-space5">
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <table class="layui-table layui-form">
                    <thead>
                        <tr>                                        
                            <th>安装位置</th>
                            <th>材质/型号</th>
                            <th>铝材/花件颜色</th>
                            <th>单位</th>
                            <th>宽</th>
                            <th>高</th>
                            <th>个数</th>
                            <th>面积</th>
                            <th>单价</th>
                            <th>折后价</th>
                            <th>总金额</th>
                        </tr>
                    </thead>
                    <tbody id="table">
                        <tr id="clone">
                            <td><?php echo $info['name']; ?></td>
                            <td><?php echo $info['type']; ?></td>
                            <td><?php echo $info['color']; ?></td>                           
                            <td>㎡</td>
                            <td><?php echo $info['width']; ?></td>
                            <td><?php echo $info['height']; ?></td>
                            <td><?php echo $info['count']; ?></td>
                            <td><?php echo $info['area']; ?></td>
                            <td><?php echo $info['price']; ?></td>
                            <td><?php echo $info['rebate_price']; ?></td>
                            <td><?php echo $info['all_price']; ?></td>
                           
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
     </form>
</body>
<script>
    layui.use(['laydate', 'form'],
            function () {
                var form = layui.form;
                
                //监听提交表单
                form.on('submit(add)',
                        function (data) {                            
                            ajaxform("<?php echo url('editMaterial'); ?>", data.field);
                            return false;
                        });

            });
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
</html>
