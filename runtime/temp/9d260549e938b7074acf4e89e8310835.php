<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:111:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/structure/structure_add.html";i:1635496795;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                <form class="layui-form" method="post" action="<?php echo url('stuctureAdd'); ?>">
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
                            结构图
                        </label>
                        <div class="layui-input-inline">
                            <button type="button" class="layui-btn" id="btn-structure">
                                <i class="layui-icon">&#xe67c;</i>上传图片
                              </button>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>最大6m,格式jpg,png,gif,bmp,jpeg,pdf
                        </div>
                    </div>
                    <div class="layui-form-item" id="structure_picbox" style="<?php if($res['structure_pic'] == ''): ?>display: none<?php endif; ?>">
                        <label for="username" class="layui-form-label">                           
                        </label>
                        <div class="layui-input-inline">
                            <div class="layui-upload-list">
                                <img class="layui-upload-img" src="/upload/<?php echo $res['structure_pic']; ?>" id="structure_prepic" style="max-height: 60px">
                                <input name="structure_pic" id="structure_pic" type="hidden" value="<?php echo $res['structure_pic']; ?>"/>
                                <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            标尺图
                        </label>
                        <div class="layui-input-inline">
                            <button type="button" class="layui-btn" id="btn-ruler">
                                <i class="layui-icon">&#xe67c;</i>上传图片
                              </button>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>最大6m,格式jpg,png,gif,bmp,jpeg,pdf
                        </div>
                    </div>
                    <div class="layui-form-item" id="ruler_picbox" style="<?php if($res['ruler_pic'] == ''): ?>display: none<?php endif; ?>">
                        <label for="username" class="layui-form-label">                           
                        </label>
                        <div class="layui-input-inline">
                            <div class="layui-upload-list">
                                <img class="layui-upload-img" src="/upload/<?php echo $res['ruler_pic']; ?>" id="ruler_prepic" style="max-height: 60px">
                                <input name="ruler_pic" id="ruler_pic" type="hidden" value="<?php echo $res['ruler_pic']; ?>"/>
                                <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            属性
                        </label>
                        <div class="layui-inline" style="width: 15%">
                            <select name="fixed">
                             <option value="不带固定" <?php if($res['fixed'] == '不带固定'): ?>selected<?php endif; ?>>不带固定</option>
                            <option value="上固定" <?php if($res['fixed'] == '上固定'): ?>selected<?php endif; ?>>上固定</option>
                            <option value="下固定" <?php if($res['fixed'] == '下固定'): ?>selected<?php endif; ?>>下固定</option>
                            <option value="上下固定" <?php if($res['fixed'] == '上下固定'): ?>selected<?php endif; ?>>上下固定</option>
                            </select>
                        </div>
                        <div class="layui-inline" style="width: 15%">
                        <select name="window_type">
                              <option value="常规" <?php if($res['window_type'] == '常规'): ?>selected<?php endif; ?>>常规</option>
                            <option value="飘窗" <?php if($res['window_type'] == '飘窗'): ?>selected<?php endif; ?>>飘窗</option>
                            <option value="圆弧窗" <?php if($res['window_type'] == '圆弧窗'): ?>selected<?php endif; ?>>圆弧窗</option>
                            <option value="内弧(拱)形窗" <?php if($res['window_type'] == '内弧(拱)形窗'): ?>selected<?php endif; ?>>内弧(拱)形窗</option>
                            <option value="外弧(拱)形窗" <?php if($res['window_type'] == '外弧(拱)形窗'): ?>selected<?php endif; ?>>外弧(拱)形窗</option>
                            </select>
                        </div>
                         <div class="layui-inline" style="width: 15%">
                            <select name="hands">
                             <option value="左把手" <?php if($res['hands'] == '左把手'): ?>selected<?php endif; ?>>左把手</option>
                            <option value="右把手" <?php if($res['hands'] == '右把手'): ?>selected<?php endif; ?>>右把手</option>
                            <option value="无把手" <?php if($res['hands'] == '无把手'): ?>selected<?php endif; ?>>无把手</option>
                            <option value="双把手" <?php if($res['hands'] == '双把手'): ?>selected<?php endif; ?>>双把手</option>   
                            </select>
                        </div>
                         <div class="layui-inline" style="width: 15%">
                            <select name="escape">
                             <option value="没有逃生窗" <?php if($res['escape'] == '没有逃生窗'): ?>selected<?php endif; ?>>没有逃生窗</option>
                              <option value="有逃生窗" <?php if($res['escape'] == '有逃生窗'): ?>selected<?php endif; ?>>有逃生窗</option> 
                            </select>
                        </div>
                        <div class="layui-inline" style="width: 15%">
                            <select name="level">
                                <option value="A类" <?php if($res['level'] == 'A类'): ?>selected<?php endif; ?>>A类</option>
                                <option value="B类" <?php if($res['level'] == 'B类'): ?>selected<?php endif; ?>>B类</option>
                                <option value="C类" <?php if($res['level'] == 'C类'): ?>selected<?php endif; ?>>C类</option>
                                <option value="D类" <?php if($res['level'] == 'D类'): ?>selected<?php endif; ?>>D类</option>
                                <option value="E类" <?php if($res['level'] == 'E类'): ?>selected<?php endif; ?>>E类</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           面数
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="face" name="face" class="layui-input" value="<?php echo $res['face']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            
                        </div>
                    </div>                                
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           最小宽
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="min_width" name="min_width" class="layui-input" value="<?php echo $res['min_width']; ?>">
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
                            <input type="text" id="min_height" name="min_height" class="layui-input" value="<?php echo $res['min_height']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             <span class="x-red">*</span>单位mm
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           最大宽
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="max_width" name="max_width" class="layui-input" value="<?php echo $res['max_width']; ?>">
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
                            <input type="text" id="max_height" name="max_height" class="layui-input" value="<?php echo $res['max_height']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             <span class="x-red">*</span>单位mm
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           标尺脚本名称
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="path_url" name="path_url" class="layui-input" value="<?php echo $res['path_url']; ?>">
                        </div>
                        <button type="button" class="layui-btn layui-btn-normal check">检测名称是否已经存在</button>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            上传标尺脚本
                        </label>
                        <div class="layui-input-inline">
                            <button type="button" class="layui-btn" id="btn-program">
                                <i class="layui-icon">&#xe67c;</i>上传文件
                            </button>
                            <i class="layui-icon layui-icon-face-smile" id="finish" style="font-size: 30px; color:#FF5722;display:<?php if($res['path_url']): ?> block<?php else: ?>none;<?php endif; ?>" ></i>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>请先填写标尺脚本在点上传,名称必须唯一,不可乱填
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           间距数
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" name="spacing_count" class="layui-input" value="<?php echo $res['spacing_count']; ?>">
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           边框数
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" name="frame_count" class="layui-input" value="<?php echo $res['frame_count']; ?>">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           竖间距数
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" name="bottom_spacing_count" class="layui-input" value="<?php echo $res['bottom_spacing_count']; ?>">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           竖花件外框数
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" name="bottom_frame_count" class="layui-input" value="<?php echo $res['bottom_frame_count']; ?>">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                           把手数量
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" name="hold_hands_count" class="layui-input" value="<?php echo $res['hold_hands_count']; ?>">
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

                $('.check').click(function () {
                    if($('#path_url').val() == ''){
                        layer.msg('请填写名称');
                        return;
                    }
                    $.post("<?php echo url('check'); ?>",{name:$('#path_url').val()},function (obj) {
                        layer.msg(obj.msg);
                    },'json')
                });

                var upload = layui.upload;
                //执行上传实例
                var uploadInst = upload.render({
                    elem: '#btn-structure' //绑定元素
                    , url: "<?php echo url('bom/upload'); ?>" //上传接口
                    , size: 8000 //限制文件大小，单位 KB
                    , method: 'post'
                    , fileAccept: 'image/*'
                    , exts: "jpg|png|gif|bmp|jpeg|pdf"
                    , done: function (res) {
                        //上传完毕回调
                        $('#structure_picbox').css('display','block');
                        $('#structure_prepic').attr('src', "/upload/" + res.data);
                        $('#structure_pic').val(res.data);
                    }
                    , error: function () {
                        //请求异常回调
                    }
                });
                //执行上传实例
                var uploadInst2 = upload.render({
                    elem: '#btn-ruler' //绑定元素
                    , url: "<?php echo url('bom/upload'); ?>" //上传接口
                    , size: 8000 //限制文件大小，单位 KB
                    , method: 'post'
                    , fileAccept: 'image/*'
                    , exts: "jpg|png|gif|bmp|jpeg|pdf"
                    , done: function (res) {
                        //上传完毕回调
                        $('#ruler_picbox').css('display','block');
                        $('#ruler_prepic').attr('src', "/upload/" + res.data);
                        $('#ruler_pic').val(res.data);
                    }
                    , error: function () {
                        //请求异常回调
                    }
                });

                //上传标尺脚本
                var uploadInst3 = upload.render({
                    elem: '#btn-program' //绑定元素
                    , url: "<?php echo url('uploadRuler'); ?>" //上传接口
                    ,data: {
                        name: function(){
                            return $('#path_url').val();
                        }
                    }
                    , size: 8000 //限制文件大小，单位 KB
                    , method: 'post'
                    ,accept:'file'
                    , done: function (res) {
                        if(res.code == 0){
                            $('#finish').show();
                        }else{
                            layer.msg(res.msg,{icon:2});
                        }
                    }
                });


                //监听提交
                form.on('submit(add)',
                function(data) {
//                    console.log(data.field);
                    ajaxform("<?php echo url('structure/structureAdd'); ?>",data.field);
                    return false;
                });

            });</script>

    </body>

</html>