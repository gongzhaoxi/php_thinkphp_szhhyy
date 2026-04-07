<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:102:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/bom/flower_add.html";i:1635496792;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                    缩略图
                </label>
                <div class="layui-input-inline">
                    <button type="button" class="layui-btn" id="test1">
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
                    物料名称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" required="" lay-verify="required" class="layui-input" value="<?php echo $res['name']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    最小高
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="min_height" name="min_height" required="" lay-verify="required" class="layui-input" value="<?php echo $res['min_height']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="phone" class="layui-form-label">
                    最小宽
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="min_width" name="min_width" required="" lay-verify="required" class="layui-input" value="<?php echo $res['min_width']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    最大高
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="max_height" name="max_height" required="" lay-verify="required" class="layui-input" value="<?php echo $res['max_height']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="phone" class="layui-form-label">
                    最大宽
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="max_width" name="max_width" required="" lay-verify="required" class="layui-input" value="<?php echo $res['max_width']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>                      
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    是否可切
                </label>
                <div class="layui-inline">
                    <select name="is_cut">
                        <option value="0" <?php if($res['is_cut'] == 0): ?>selected<?php endif; ?>>否</option>
                        <option value="1" <?php if($res['is_cut'] == 1): ?>selected<?php endif; ?>>是</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    最小高
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="cut_min_height" name="cut_min_height" class="layui-input" value="<?php echo $res['cut_min_height']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="phone" class="layui-form-label">
                    最小宽
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="cut_min_width" name="cut_min_width" class="layui-input" value="<?php echo $res['cut_min_width']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    最大高
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="cut_max_height" name="cut_max_height" class="layui-input" value="<?php echo $res['cut_max_height']; ?>">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    <span class="x-red">*</span>单位mm
                </div>
            </div>
            <div class="layui-form-item">
                <label for="phone" class="layui-form-label">
                    最大宽
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="cut_max_width" name="cut_max_width" class="layui-input" value="<?php echo $res['cut_max_width']; ?>">
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
                <label for="username" class="layui-form-label">
                    绑定结构
                </label>
                <div class="layui-input-inline">
                    <button class="layui-btn layui-btn-normal" type="button" onclick="show_formula(this,<?php echo $res['id']; ?>)">选择</button>
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
<script>layui.use(['form', 'layer', 'upload'],
            function () {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;

                var upload = layui.upload;
                //执行上传实例
                var uploadInst = upload.render({
                    elem: '#test1' //绑定元素
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
                        function (data) {
                            ajaxform("<?php echo url('bom/flowerAdd'); ?>",data.field);
                            return false;
                        });

            });
              //弹出选择公式
        function show_formula(obj,id){
            layer.open({type: 2, content: '/admin/bom/flowerStructure/id/'+id+'.html',area:['800px','500px'],btn:['离开'],
                shadeClose:true,
                yes: function (index, layero) {
                    //引用弹出层的回调函数
                    var res = window["layui-layer-iframe" + index].callbackdata();
                    $(obj).nextAll('#ruler_name').val(res.ruler_name);
                    $(obj).nextAll('#calculate_name').val(res.calculate_name);
                    $(obj).nextAll('#ruler').val(res.ruler);
                    $(obj).nextAll('#calculate').val(res.calculate);
                    layer.close(index);
                }});
        }
</script>

</body>

</html>