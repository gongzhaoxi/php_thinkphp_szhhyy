<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:120:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/structure/edit_calculate_formula.html";i:1715563592;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .input-formula{ width: 400px}
    .input-count{ width: 10%}
    .input-name { width: 20%}
</style>
<body>

    <div class="layui-fluid">
        <div class="layui-col-md3">
            <img src="/upload/<?php echo $structure['ruler_pic']; ?>" style="max-width: 100%;max-height: 500px;"/>
        </div>
        <div class="layui-col-md9" style="padding:15px;">
            <form class="layui-form layui-col-space5">
                <div class="layui-form-item">
                    
                    <div class="layui-input-inline">
                        <input type="text" id="code" name="name" required="" lay-verify="required" class="layui-input" placeholder="请输入公式名称" value="<?php echo $name['name']; ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">
                        <span class="x-red">*</span>不能为空
                    </div>
                </div>
                <div class="layui-form-item">
                     <div class="layui-input-inline">
                        对应物料
                    </div>
                    <div class="layui-input-inline" style="width: 250px">
                        计算公式
                    </div>
                    <div class="layui-input-inline" style="width: 30px;margin-left: 80px;">
                        数量
                    </div>
                    <div class="layui-input-inline" style="width: 50px;margin-left: 50px;">
                        输出名称
                    </div>
                </div>
                <div id="box">
                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                <div class="layui-form-item" id="clone">
                    <div class="input-field layui-inline ">
                        <select name="bom[]">
                            <option value="边框" <?php if($v['bom'] == '边框'): ?>selected<?php endif; ?>>边框</option>
                            <option value="外框" <?php if($v['bom'] == '外框'): ?>selected<?php endif; ?>>外框</option>
                            <option value="内框" <?php if($v['bom'] == '内框'): ?>selected<?php endif; ?>>内框</option>
                            <option value="中挺" <?php if($v['bom'] == '中挺'): ?>selected<?php endif; ?>>中挺</option>
                            <option value="扇" <?php if($v['bom'] == '扇'): ?>selected<?php endif; ?>>扇</option>
                            <option value="小门框" <?php if($v['bom'] == '小门框'): ?>selected<?php endif; ?>>小门框</option>
                            <option value="纱网" <?php if($v['bom'] == '纱网'): ?>selected<?php endif; ?>>纱网</option>
                            <option value="花件" <?php if($v['bom'] == '花件'): ?>selected<?php endif; ?>>花件</option>
                            <option value="配件" <?php if($v['bom'] == '配件'): ?>selected<?php endif; ?>>配件</option>
                            <option value="中横" <?php if($v['bom'] == '中横'): ?>selected<?php endif; ?>>中横</option>
                            <option value="小门框" <?php if($v['bom'] == '小门框'): ?>selected<?php endif; ?>>小门框</option>
                            <option value="上中横" <?php if($v['bom'] == '上中横'): ?>selected<?php endif; ?>>上中横</option>
							<option value="下中横" <?php if($v['bom'] == '下中横'): ?>selected<?php endif; ?>>下中横</option>
							<option value="窄边框" <?php if($v['bom'] == '窄边框'): ?>selected<?php endif; ?>>窄边框</option>
							<option value="窄内框" <?php if($v['bom'] == '窄内框'): ?>selected<?php endif; ?>>窄内框</option>
							<option value="窄外框" <?php if($v['bom'] == '窄外框'): ?>selected<?php endif; ?>>窄外框</option>
							<option value="纱压线" <?php if($v['bom'] == '纱压线'): ?>selected<?php endif; ?>>纱压线</option>
                        </select>
                    </div>
                    <div class="layui-inline input-formula">
                        <input type="text" id="code" name="formula[]" required="" lay-verify="required" class="layui-input" value="<?php echo $v['formula']; ?>">
                    </div>
                    <div class="layui-inline input-count">
                        <input type="text" id="code" name="count[]" required="" lay-verify="required" class="layui-input" value="<?php echo $v['count']; ?>">
                    </div>
                    <div class="layui-inline input-name">
                        <input type="text" id="code" name="export_name[]" class="layui-input" value="<?php echo $v['export_name']; ?>">
                    </div>
                    <button type="button" class="layui-btn layui-btn-radius layui-btn-danger" onclick="del(this)">删除</button>
                </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <div style="float: right">
                    
                    <button type="button" id="add" class="layui-btn layui-btn-radius layui-btn-warm">继续添加</button>
                     
                </div>
                <div class="layui-col-md12">
                    <input name='scf_id' type='hidden' value='<?php echo $scf_id; ?>'/>
                    <button class="layui-btn" lay-filter="add" lay-submit="" style="margin-left: 40%;">立即保存</button>              
                </div>
            </form>
        </div>
    </div>
</body>
<script>layui.use(['laydate', 'form'],
            function () {
                $ = layui.jquery;
                var laydate = layui.laydate;
                var form = layui.form;
                
                 $(function(){
                    $('#add').click(function(){
                        var html = $('#clone').clone();
                        $('#box').append(html);
                        form.render('select');  
                    })     
                })
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('editCalculateFormula'); ?>",data.field);
                    return false;
                });
            });
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
   
    
    //删除
    function del(obj){
        $(obj).parent().remove();
    }
</script>
</html>