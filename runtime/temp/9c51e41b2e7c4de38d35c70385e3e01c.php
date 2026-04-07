<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:115:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/structure/add_ruler_formula.html";i:1635496795;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .input-field{ width: 80px}
    .input-formula{ width: 300px}
</style>
<body>

    <div class="layui-fluid">
        <div class="layui-col-md6">
            <img src="/upload/<?php echo $structure['structure_pic']; ?>" style="max-width: 100%;max-height: 500px;"/>
        </div>
        <div class="layui-col-md6">
            <form class="layui-form layui-col-space5">
                <div class="layui-form-item">
                    
                    <div class="layui-input-inline">
                        <input type="text" id="code" name="name" required="" lay-verify="required" class="layui-input" placeholder="请输入公式名称" >
                    </div>
                    <div class="layui-form-mid layui-word-aux">
                        <span class="x-red">*</span>不能为空
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-inline">
                        对应字段
                    </div>
                    <div class="layui-input-inline">
                        计算公式
                    </div>
                </div>
                <div id="clone-box">
                <div class="layui-form-item" id="clone">
                    <div class="input-field layui-inline ">
                        <input type="text" id="code" name="field[]" required="" lay-verify="required" class="layui-input">
                    </div>
                    <div class="layui-inline input-formula">
                        <input type="text" id="code" name="formula[]" required="" lay-verify="required" class="layui-input">
                    </div>
                    <button type="button" class="layui-btn layui-btn-radius layui-btn-danger" onclick="del(this)">删除</button>
                </div>
                </div>
                <div style="float: right">
                    
                    <button type="button" id="add" class="layui-btn layui-btn-radius layui-btn-warm">继续添加</button>
                     
                </div>
                <div class="layui-col-md12">
                    <input name='structure_id' type='hidden' value='<?php echo $structure_id; ?>'/>
                    <button class="layui-btn" lay-filter="add" lay-submit="" style="margin-left: 40%;">立即保存</button>              
                </div>
            </form>
        </div>
    </div>
</body>
<script>layui.use(['laydate', 'form'],
            function () {
                var laydate = layui.laydate;
                var form = layui.form;
                
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('addRulerFormula'); ?>",data.field);
                    return false;
                });
            });
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
    $(function(){
        $('#add').click(function(){
            var html = $('#clone').clone();
            $('#clone-box').append(html);
        })
 
       
    })
    
    //删除
    function del(obj){
        $(obj).parent().remove();
    }
</script>
</html>