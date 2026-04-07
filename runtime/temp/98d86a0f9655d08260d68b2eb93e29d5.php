<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/bom/aluminum_add.html";i:1635496791;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            物料编号
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="code" name="code" required="" lay-verify="required" class="layui-input" value="<?php echo $res['code']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不可重复，不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            物料名称
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="name" name="name" required="" lay-verify="required" class="layui-input" value="<?php echo $res['name']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            大面尺寸
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="big" name="big" required="" lay-verify="required" class="layui-input" value="<?php echo $res['big']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>单位mm
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            小面尺寸
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="small" name="small" required="" lay-verify="required" class="layui-input" value="<?php echo $res['small']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>单位mm
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            单位
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="unit" name="unit"  class="layui-input" value="<?php echo $res['unit']; ?>">
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            价格
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="price" name="price"  class="layui-input" value="<?php echo $res['price']; ?>">
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                        <input name='id' type='hidden' value='<?php echo $id; ?>'/>
                        <button class="layui-btn" lay-filter="add" lay-submit="">增加</button>
                    </div>
        </form>
        </div>
        </div>
        <script>layui.use(['form', 'layer'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;
                
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('bom/aluminumAdd'); ?>",data.field);
                    return false;
                });
                
            });</script>

    </body>

</html>