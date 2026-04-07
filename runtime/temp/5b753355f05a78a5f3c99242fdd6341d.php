<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:100:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/color.html";i:1635496794;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .color-box{width: 49%;float: left}
</style>
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    <div class="color-box">
                        <div class="layui-form-item">
                            <label class="layui-form-label" style="line-height: 15px;">铝型颜色</label>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-inline" style="width: 18%">
                                <select name="name" id="bone" class="one" lay-filter="one">
                                  <option value="">请选择颜色</option>
                                  <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                  <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                  <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="layui-inline two-box" style="width: 17%;display: none;">
                                <select name="name" id="btwo" class="two" lay-filter="two">
                                 
                                </select>
                            </div>
                            <div class="layui-inline three-box" style="width: 17%;display: none;">
                                <select name="name" id="bthree" class="three">
                                  
                                  
                                </select>
                            </div>
                            <div class="layui-inline" style="width:15%">
                                 <input type="text" id="price" name="price" class="layui-input" placeholder="请填写价格">
                            </div>
                            <div class="layui-inline" style="width:15%">
                                 <a href="javascript:;" class="layui-btn" id="add"><i class='icon iconfont'>&#xe6b9;</i>添加颜色</a>
                            </div>
                        </div>
                        <table class="layui-table layui-form">
                                <thead>
                                    <tr>
                                        <th>名称</th>
                                        <th>价格</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="table1">
                                    <?php if(is_array($frame) || $frame instanceof \think\Collection || $frame instanceof \think\Paginator): $i = 0; $__LIST__ = $frame;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['name']; ?><input name='info[<?php echo $key; ?>][color_id]' type='hidden' value='<?php echo $v['color_id']; ?>'/></td>
                                        <td><?php echo $v['price']; ?><input name='info[<?php echo $key; ?>][price]' type='hidden' value='<?php echo $v['price']; ?>'/></td>
                                        <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>
                                        <input name='info[<?php echo $key; ?>][level]' type='hidden' value='<?php echo $v['level']; ?>'/>
                                        <input name='info[<?php echo $key; ?>][relation]' type='hidden' value='<?php echo $v['relation']; ?>'/>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                        </table>
                    </div>
                    <div class="color-box" style="margin-left: 1%;">
                        <div class="layui-form-item">
                            <label class="layui-form-label" style="line-height: 15px;">花件颜色</label>
                        </div>
                        <div class="layui-form-item">                            
                             <div class="layui-inline" style="width: 18%">
                                <select name="name" id="hone" class="one" lay-filter="one">
                                  <option value="">请选择颜色</option>
                                  <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                  <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                  <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="layui-inline two-box" style="width: 17%;display: none;">
                                <select name="name" id="htwo" class="two" lay-filter="two">
                                 
                                </select>
                            </div>
                            <div class="layui-inline three-box" style="width: 17%;display: none;">
                                <select name="name" id="hthree" class="three">
                                  
                                  
                                </select>
                            </div>
                            <div class="layui-inline" style="width:15%">
                                 <input type="text" id="hprice" name="hprice" class="layui-input" placeholder="请填写价格">
                            </div>
                            <div class="layui-inline" style="width:15%">
                                 <a href="javascript:;" class="layui-btn" id="hadd"><i class='icon iconfont'>&#xe6b9;</i>添加颜色</a>
                            </div>
                        </div>
                        <table class="layui-table layui-form">
                                <thead>
                                    <tr>
                                        <th>名称</th>
                                        <th>价格</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="table2">
                                    <?php if(is_array($flower) || $flower instanceof \think\Collection || $flower instanceof \think\Paginator): $i = 0; $__LIST__ = $flower;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo $v['name']; ?><input name='hinfo[<?php echo $key; ?>][color_id]' type='hidden' value='<?php echo $v['color_id']; ?>'/></td>
                                        <td><?php echo $v['price']; ?><input name='hinfo[<?php echo $key; ?>][price]' type='hidden' value='<?php echo $v['price']; ?>'/></td>
                                        <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>
                                        <input name='hinfo[<?php echo $key; ?>][level]' type='hidden' value='<?php echo $v['level']; ?>'/>
                                        <input name='hinfo[<?php echo $key; ?>][relation]' type='hidden' value='<?php echo $v['relation']; ?>'/>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                        </table>
                    </div>
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                        <input name="series_id" type="hidden" value="<?php echo $id; ?>"/>
                        <button class="layui-btn layui-btn-lg" lay-filter="add" lay-submit="" style="margin: 20px 0 0 38%">保存</button>
                    </div>
        </form>
        </div>
        </div>
        <script>layui.use(['form', 'layer'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;
                
                
                //监听select,二级联动
                form.on('select(one)',function(data){
                    $.post("<?php echo url('findcolor'); ?>",{pid:data.value},function(obj){                            
                            var html = ' <option value="">请选择颜色</option>';  
                            //二级，三级先隐藏
                            $(data.elem).parent().next().css('display','none'); 
                            $(data.elem).parent().next().next().css('display','none');
                            var array = obj.data;     
                            for(i=0;i<array.length;i++){
                                html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";
                            }
                            if(array.length>0){
                                $(data.elem).parent().next().css('display','inline-block');                               
                            }
                            $(data.elem).parent().next().find('select').html(html);
                            $(data.elem).parent().next().next().find('select').html(html);
                            form.render('select');  //更新渲染
                    },'json');
                });
                
                //监听select,三级联动
                form.on('select(two)',function(data){
                    $.post("<?php echo url('findcolor'); ?>",{pid:data.value,two:2},function(obj){
                            var html = ' <option value="">请选择颜色</option>';  
                            //三级先隐藏
                            $(data.elem).parent().next().css('display','none'); 
                            var array = obj.data;     
                            for(i=0;i<array.length;i++){
                                html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";
                            }
                            if(array.length>0){
                                $(data.elem).parent().next().css('display','inline-block');                               
                            }
                            $(data.elem).parent().next().find('select').html(html);
                            form.render('select');  //更新渲染
                    },'json');
                });
                
                //边框颜色，js添加
                $('#add').click(function(){
                    var price = $('#price').val();
                    var one = $('#bone option:selected');
                    var two = $('#btwo option:selected');
                    var three = $('#bthree option:selected');
                    var row = $('#table1').find('tr').length; //tr的行数
                    if(one == ''){
                        layer.msg('请选择名称',{icon:2});
                        return;
                    }
                    if(price == ''){
                        layer.msg('请输入价格',{icon:2});
                        return;
                    }
                    var current = '';//当前选中的option
                    var level = 0; //层级
                    var relation = []; //父子关系
                    var textd = ''; //显示文字
                    if(one.val() != ''){
                        current = one;
                        level = 1;
                        textd = one.text();
                    }
                    if(two.val() != ''){
                        current = two;
                        level = 2;
                        relation = [one.val()];
                        textd = one.text()+'--'+two.text();
                    }
                    if(three.val() != ''){
                        current = three;
                        level = 3;
                        relation = [one.val(),two.val()];
                        textd = one.text()+'--'+two.text()+'--'+three.text();
                    }
                   
                    var html = "<tr><td>"+textd+"<input name='info["+row+"][color_id]' type='hidden' value="+current.val()+"></td>\n\
                                <td>"+price+"<input name='info["+row+"][price]' type='hidden' value="+price+"></td>\n\
                               <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>\n\
                                <input name='info["+row+"][level]' type='hidden' value="+level+"><input name='info["+row+"][relation]' type='hidden' value="+relation+"></tr>";
                    
                    $('#table1').append(html);
                })
                
                //花件颜色,js添加
                $('#hadd').click(function(){
                    var price = $('#hprice').val();
                    var one = $('#hone option:selected');
                    var two = $('#htwo option:selected');
                    var three = $('#hthree option:selected');
                    var row = $('#table2').find('tr').length; //tr的行数
                    if(one == ''){
                        layer.msg('请选择名称',{icon:2});
                        return;
                    }
                    if(price == ''){
                        layer.msg('请输入价格',{icon:2});
                        return;
                    }
                    var current = '';//当前选中的option
                    var level = 0; //层级
                    var relation = []; //父子关系
                    var textd = ''; //显示文字
                    if(one.val() != ''){
                        current = one;
                        level = 1;
                        textd = one.text();
                    }
                    if(two.val() != ''){
                        current = two;
                        level = 2;
                        relation = [one.val()];
                        textd = one.text()+'--'+two.text();
                    }
                    if(three.val() != ''){
                        current = three;
                        level = 3;
                        relation = [one.val(),two.val()];
                        textd = one.text()+'--'+two.text()+'--'+three.text();
                    }
                    var html = "<tr><td>"+textd+"<input name='hinfo["+row+"][color_id]' type='hidden' value="+current.val()+"></td>\n\
                                <td>"+price+"<input name='hinfo["+row+"][price]' type='hidden' value="+price+"></td>\n\
                               <td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='member_del(this)' ><i class='layui-icon'>&#xe640;</i>删除</button></td>\n\
                                <input name='hinfo["+row+"][level]' type='hidden' value="+level+"><input name='hinfo["+row+"][relation]' type='hidden' value="+relation+"></tr>";
                    
                    $('#table2').append(html);
                })
                //监听提交
                form.on('submit(add)',
                function(data) {
                   
                    ajaxform("<?php echo url('series/colorAdd'); ?>",data.field,2);
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