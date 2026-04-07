<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:105:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/series_add.html";i:1635496795;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                            归属系列
                        </label>
                        <div class="layui-input-inline">
                            <select name="parent_id">
                               <option value="0">顶级系列</option>
                               <?php echo $allseries; ?>
                            </select>
                        </div>
   
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            系列名称
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="name" name="name" required="" lay-verify="required" class="layui-input" value="<?php echo $res['name']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            最小面积
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="min_area" name="min_area" class="layui-input" value="<?php echo $res['min_area']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            单价
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="price" name="price" class="layui-input" value="<?php echo $res['price']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            类型
                        </label>
                        <div class="layui-input-inline">
                            <input type="radio" name="type" value="1" title="窗花" <?php if($res['type'] == 1): ?>checked<?php endif; ?>>
                            <input type="radio" name="type" value="2" title="室内护栏" <?php if($res['type'] == 2): ?>checked<?php endif; ?>>
                            <input type="radio" name="type" value="3" title="室外护栏" <?php if($res['type'] == 3): ?>checked<?php endif; ?>>
                            <input type="radio" name="type" value="4" title="纱门" <?php if($res['type'] == 4): ?>checked<?php endif; ?>>
                            <input type="radio" name="type" value="5" title="纱窗" <?php if($res['type'] == 5): ?>checked<?php endif; ?>>
                            <input type="radio" name="type" value="6" title="围栏" <?php if($res['type'] == 6): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            打印报价显示
                        </label>
                        <div class="layui-input-inline">
                            <input type="radio" name="price_show" value="0" title="显示" <?php if($res['price_show'] == 0): ?>checked<?php endif; ?>>
                            <input type="radio" name="price_show" value="1" title="不显示" <?php if($res['price_show'] == 1): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                         <input name='id' type='hidden' value='<?php echo $id; ?>'/>
                        <button class="layui-btn" lay-filter="add" lay-submit="">保存</button>
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

    </body>

</html>