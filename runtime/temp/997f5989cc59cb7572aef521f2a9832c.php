<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:103:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/user/auth_rule.html";i:1635496796;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
<link href="/static/js/treeTable/treeTable.css" rel="stylesheet" type="text/css"/>
    <body>
        <form class="layui-form">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form treeTable" lay-even lay-skin="nob" id="tablesd">
                                
                                <tbody class="x-cate">
                                    <?php echo $treeList; ?>                              
                                </tbody>
                            </table>
                        </div>
                        <div class="layui-form-item" style="padding-bottom: 20px;">
                            <input name="id" type="hidden" value="<?php echo $id; ?>"/>
                                <button class="layui-btn" lay-filter="add" lay-submit="" style="margin:10px 0 0 15%">
                                   保存
                                </button>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </body>   
    
    <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            
             //监听提交
            form.on('submit(add)',
            function(data) {
                ajaxform("<?php echo url('user/saveRule'); ?>",data.field);
                return false;
            });
        });

       

    </script>
    <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script src="/static/js/treeTable/treeTable.js" charset="utf-8"></script>    
    <script>
        $(function(){
           $("#tablesd").treeTable({expandable: true,initialState:'expanded'});
        })
    </script>
</html>