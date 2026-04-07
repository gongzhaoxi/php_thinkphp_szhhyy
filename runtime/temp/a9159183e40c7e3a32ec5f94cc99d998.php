<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:109:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/select_formula.html";i:1635496795;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                            标尺公式
                        </label>
                        <div class="layui-input-inline">
                            <select name="ruler" id="ruler" required="" lay-verify="required">
                                <option value="">请选择公式</option>
                                <?php if(is_array($ruler) || $ruler instanceof \think\Collection || $ruler instanceof \think\Paginator): $i = 0; $__LIST__ = $ruler;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>                                
                                <option value="<?php echo $v['srf_id']; ?>"><?php echo $v['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            算料公式
                        </label>
                        <div class="layui-input-inline">
                             <select name="calculate" id="calculate" required="" lay-verify="required">
                                <option value="">请选择公式</option>
                                <?php if(is_array($calculate) || $calculate instanceof \think\Collection || $calculate instanceof \think\Paginator): $i = 0; $__LIST__ = $calculate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo $v['scf_id']; ?>"><?php echo $v['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        
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
                    ajaxform("<?php echo url('series/seriesAdd'); ?>",data.field);
                    return false;
                });

            });</script>
            <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script>
         //回调参数
        function callbackdata() {
            if($('#calculate option:selected').val() == '' || $('#calculate option:selected').val() == ''){
                layer.msg('请选择公式',{icon:2});
                return;
            }
            var data = {ruler_name:$('#ruler option:selected').text(),ruler:$('#ruler option:selected').val(),
                        calculate_name:$('#calculate option:selected').text(),calculate:$('#calculate option:selected').val()};                       
            return data;
        }
    </script>
    </body>

</html>