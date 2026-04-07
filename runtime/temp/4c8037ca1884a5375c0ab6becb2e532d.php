<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:114:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/bind_stock_material.html";i:1635496794;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-form-label{ width: 100px;}
</style>
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label" style="line-height: 15px;">整体是否考虑颜色:</label>
                        <div class="layui-inline">
                            <input type="radio" name="is_color" value="0" title="否" <?php if($list[0]['is_color'] == 0): ?>checked<?php endif; ?>>
                            <input type="radio" name="is_color" value="1" title="是" <?php if($list[0]['is_color'] == 1): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label" style="line-height: 15px;">添加物料:</label>
                        <div class="layui-inline">
                             <input type="text" id="material_number" name="material_number" class="layui-input"  placeholder="输入ERP物料编码">
                        </div>
                        <div class="layui-inline">
                            <input type="text" id="unit_content"  name="unit_content" class="layui-input"  placeholder="输入1㎡使用量(mm)">
                        </div>
                        <div class="layui-inline">
                             <a href="javascript:;" class="layui-btn" id="add"><i class='icon iconfont'>&#xe6b9;</i>添加</a>
                        </div>
                    </div>
                    <p class="x-red">注:以下添加的物料编码不可重复添加</p>
                    <table class="layui-table layui-form">
                            <thead>
                                <tr>
                                    <th>对应使用物料编码(唯一)</th>
                                    <th>1㎡使用量</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="tabled">
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td><input name="number[]" type="text" value="<?php echo $v['material_number']; ?>" class="layui-input"></td>
                                    <td><input name="unit[]" type="text" value="<?php echo $v['unit_content']; ?>" class="layui-input"></td>
                                    <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                    </table>
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                        <input name="series_id" type="hidden" value="<?php echo $id; ?>"/>
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
                
                
                $('#add').click(function(){
                    var name = $('#material_number').val();
                    var unit = $('#unit_content').val();
                    var series_id = "<?php echo $id; ?>";
                    
                    if(name == ''){
                        layer.msg('请物料编码',{icon:2});
                        return;
                    }
                    if(unit == ''){
                        layer.msg('请输入使用量',{icon:2});
                        return;
                    }
                    var html = "<tr><td><input name='number[]' type='text' class='layui-input' value='"+name+"'></td>\n\
                                <td><input name='unit[]' type='text' class='layui-input' value='"+unit+"'></td>\n\
                                <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>\n\
                                </tr>";
                    $('#tabled').append(html);
                })
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('series/bindStockMaterial'); ?>",data.field,2);
                    return false;
                });

            });
        /*删除*/
        function member_del(obj) {
            $(obj).parents("tr").remove();
        }
        </script>

    </body>

</html>