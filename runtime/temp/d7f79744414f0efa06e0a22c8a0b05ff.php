<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/bom/color_add.html";i:1635496792;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                        <label for="username" class="layui-form-label">
                            归属颜色
                        </label>
                        <div class="layui-input-inline">
                            <select name="parent_id" lay-verify="required">
                                <option value="0">顶级颜色</option>
                                <?php echo $color; ?>
                            </select>
                        </div>
   
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            是否自填内容
                        </label>
                        <div class="layui-input-inline">
                            <select name="is_self">
                                <option value="0" <?php if($res['is_self'] == 0): ?>selected<?php endif; ?>>否</option>
                               <option value="1" <?php if($res['is_self'] == 1): ?>selected<?php endif; ?>>是</option>
                            </select>
                        </div>
   
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            上传颜色图片
                        </label>
                        <div class="layui-input-inline">
                            <button type="button" class="layui-btn" id="color">
                                <i class="layui-icon">&#xe67c;</i>上传图片
                              </button>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>最大6m,格式jpg,png,gif,bmp,jpeg,pdf
                        </div>
                    </div>
                    <div class="layui-form-item" id="picbox" style="<?php if($res['pic'] == ''): ?>display: none<?php endif; ?>">
                        <label for="username" class="layui-form-label">                           
                        </label>
                        <div class="layui-input-inline">
                            <div class="layui-upload-list">
                                <img class="layui-upload-img" src="/upload/<?php echo $res['pic']; ?>" id="prepic" style="max-height: 60px">
                                <input name="pic" id="pic" type="hidden" value="<?php echo $res['pic']; ?>"/>
                                <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            属性
                        </label>
                        <div class="layui-input-inline">
                            <select name="attr">
                                <option value="常规色" <?php if($res['attr'] == '常规色'): ?>selected<?php endif; ?>>常规色</option>
                               <option value="特殊色" <?php if($res['attr'] == '特殊色'): ?>selected<?php endif; ?>>特殊色</option>
                            </select>
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
        <script>layui.use(['form', 'layer','upload'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;

                var upload = layui.upload;
                //执行上传实例
                var uploadInst = upload.render({
                    elem: '#color' //绑定元素
                    , url: "<?php echo url('bom/upload'); ?>" //上传接口
                    , size: 8000 //限制文件大小，单位 KB
                    , method: 'post'
                    , fileAccept: 'image/*'
                    , exts: "jpg|png|gif|bmp|jpeg|pdf"
                    , done: function (res) {
                        //上传完毕回调
                        $('#picbox').css('display','block');
                        $('#prepic').attr('src', "/upload/" + res.data);
                        $('#pic').val(res.data);
                    }
                    , error: function () {
                        //请求异常回调
                    }
                });
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('bom/colorAdd'); ?>",data.field);
                    return false;
                });

            });</script>

    </body>

</html>