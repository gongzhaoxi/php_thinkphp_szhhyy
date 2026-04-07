<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:70:"D:\wwwroot\szhhyy.com\public/../application/admin\view\dealer\add.html";i:1758810148;s:63:"D:\wwwroot\szhhyy.com\application\admin\view\public\header.html";i:1775293150;}*/ ?>
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
                    <div class="layui-form-item x-city" id="end">
                        <label class="layui-form-label">选择城市</label>
                        <div class="layui-input-inline" style="width:120px">
                          <select name="province" lay-filter="province" required="" lay-verify="required">
                            <option value="">请选择省</option>
                          </select>
                        </div>
                        <div class="layui-input-inline" style="width:120px">
                          <select name="city" lay-filter="city">
                            <option value="">请选择市</option>
                          </select>
                        </div>
                        <div class="layui-input-inline" style="width:120px">
                          <select name="area" lay-filter="area" id="area">
                            <option value="">请选择县/区</option>
                          </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            名称
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="name" name="name" required="" lay-verify="required" class="layui-input" placeholder="请输入经销商名称">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            联系方式
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="contact" name="contact" required="" lay-verify="required" class="layui-input" placeholder="请输入联系方式">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            备用联系方式
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="contact" name="back_contact" class="layui-input" placeholder="请输入联系方式">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <!--<span class="x-red">*</span>不能为空-->
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            图片
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
                    <div class="layui-form-item" id="picbox" >
                        <label for="username" class="layui-form-label">                           
                        </label>
                        <div class="layui-input-inline">
                            <div class="layui-upload-list">
                                <img class="layui-upload-img" src="" id="prepic" style="max-height: 60px">
                                <input name="pic" id="pic" type="hidden" value=""/>
                                <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            业务员
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="sales_name" name="sales_name" class="layui-input" placeholder="请输入业务员名称">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red"></span>
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            地址
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="address" name="address" required="" lay-verify="required" class="layui-input" placeholder="请输入地址">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            地址2
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="other_address" name="other_address" class="layui-input" placeholder="请输入地址2">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            经销商编码
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="code" name="code" class="layui-input" placeholder="系统自动生成" readonly>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <!--<span class="x-red">*</span>不可重复，不能为空-->
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            折扣
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="dealer_rebate" name="dealer_rebate" class="layui-input" placeholder="请输入经销商折扣">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <!--<span class="x-red">*</span>不可重复，不能为空-->
                        </div>
                    </div>
					<div class="layui-inline">
					    <label class="layui-form-label">是否打印标签</label>
					    <div class="layui-input-inline">
					    <select name='print_label'>
					        <option value="0">需要打印</option>
					        <option value="1">不需要打印</option>
					    </select>
					    </div>
					</div>
					<div class="layui-form-item" >
                        <label class="layui-form-label">品牌</label>
                        <div class="layui-input-block" >
							<select name="brand_id" required  lay-verify="required">
								<?php if(is_array($brand) || $brand instanceof \think\Collection || $brand instanceof \think\Paginator): $i = 0; $__LIST__ = $brand;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
								<option  value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
								<?php endforeach; endif; else: echo "" ;endif; ?>
							</select>
                        </div>
                    </div>						
					
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                        <button class="layui-btn" lay-filter="add" lay-submit="">增加</button>
                    </div>
        </form>
        </div>
        </div>
        <script src="/static/js/jquery.min.js" type="text/javascript"></script>
        <script src="/static/js/xcity.js?v=<?php echo time()?>" type="text/javascript"></script>
        <script>layui.use(['form', 'layer','upload','code'],
            function() {
                $ = layui.jquery;
                form = layui.form,
                layer = layui.layer;
                layui.code();
                
                $('#end').xcity();

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

                form.on("select(area)",function(data){
                    $.post("<?php echo url('findcode'); ?>",{area:data.value},function(obj){
                        if(obj.code == 0){
                            $('#code').val(obj.data.code);
                        }
                    },'json')
                })
                
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('dealer/add'); ?>",data.field);
                    return false;
                });

            });</script>

    </body>

</html>