<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:99:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/five.html";i:1635496794;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                        <label class="layui-form-label" style="line-height: 15px;">五金</label>
                        <div class="layui-inline">
                            <select name="name" id="name">
                              <option value="">请选择五金</option>
                              <?php if(is_array($five) || $five instanceof \think\Collection || $five instanceof \think\Paginator): $i = 0; $__LIST__ = $five;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                              <option value="<?php echo $v['id']; ?>" code="<?php echo $v['code']; ?>" ><?php echo $v['name']; ?></option>
                              <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="layui-inline">
                             <input type="text" id="price" name="price" class="layui-input"  placeholder="请填写价格">
                        </div>
                        <div class="layui-inline">
                             <a href="javascript:;" class="layui-btn" id="add"><i class='icon iconfont'>&#xe6b9;</i>添加五金</a>
                        </div>
                    </div>
                    <table class="layui-table layui-form">
                            <thead>
                                <tr>
                                    <th>名称</th>
                                    <th>编号</th>
                                    <th>价格</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="tabled">
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td><?php echo $v['name']; ?></td>
                                    <td><?php echo $v['code']; ?></td>
                                    <td><?php echo $v['price']; ?><input name="info[<?php echo $key; ?>][five_id]" type="hidden" value="<?php echo $v['five_id']; ?>"/>
                                        
                                        <input name="info[<?php echo $key; ?>][price]" type='hidden' value='<?php echo $v['price']; ?>'/>
                                        <input name="info[<?php echo $key; ?>][series_id]" type='hidden' value='<?php echo $v['series_id']; ?>'/>
                                    </td>
                                    <td><button class="layui-btn-danger layui-btn layui-btn-xs"  onclick="member_del(this)" ><i class="layui-icon">&#xe640;</i>删除</button></td>
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
                    var five_id = $('#name option:selected').val();
                    var name = $('#name option:selected').text();
                    var code = $('#name option:selected').attr('code');
                    var price = $('#price').val();
                    var row = $('#tabled').find('tr').length; //tr的行数
                    var series_id = <?php echo $id; ?>;
                    
                    if(name == ''){
                        layer.msg('请选择名称',{icon:2});
                        return;
                    }
                    if(price == ''){
                        layer.msg('请输入价格',{icon:2});
                        return;
                    }
                    var html = "<tr><td>"+name+"</td>\n\
                                <td>"+code+"<input name='info["+row+"][five_id]' type='hidden' value="+five_id+"></td><td>"+price+"<input name='info["+row+"][price]' type='hidden' value="+price+"></td>\n\
                                <td><input name='info["+row+"][series_id]' type='hidden' value="+series_id+"><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>\n\
                                </tr>";
                    $('#tabled').append(html);
                })
                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('series/fiveAdd'); ?>",data.field,2);
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