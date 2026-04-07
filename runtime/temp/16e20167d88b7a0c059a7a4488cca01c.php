<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/series/flower.html";i:1635496794;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-col-xs12 .iconfont{ font-size: 20px;}
    .check-box{ margin-top: 10px;}
    .check { position: relative;left: 8%}
    .del-icon{ margin-left: 30%;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form">
                <blockquote class="layui-elem-quote">
                    <div style="width: 20%">
                        <select name="type" id="type" lay-filter='iscut'>
                            <option value="">全部类型</option>
                            <option value="1">可切花件</option>
                            <option value="0">不可切花件</option>
                        </select>
                    </div>
                </blockquote> 
                <div class="layui-col-xs12" id="all-flower">
                    <?php if(is_array($bom) || $bom instanceof \think\Collection || $bom instanceof \think\Paginator): $i = 0; $__LIST__ = $bom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <div class="layui-col-xs3 check-box" ids='<?php echo $v['id']; ?>'>
                        <img src="/upload/<?php echo $v['pic']; ?>"  height="100"/>
                        <?php if($v['select'] == 1): ?><i class="icon iconfont check">&#xe6af</i><?php endif; ?>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <div class="layui-form-item" style="padding-top: 10px;">
                    <blockquote class="layui-elem-quote">
                        <input type="checkbox" name="sex" value="全选" title="全选" lay-filter="checkall">
                        <a href="javascript:;" class="layui-btn" id="add" style="margin-top: 6px;"><i class='icon iconfont'>&#xe6b9;</i>添加</a>
                    </blockquote>    
                </div>  
                <div class="layui-col-xs12 flower-list">
                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <div class='layui-col-xs3'>
                        <img src="/upload/<?php echo $v['pic']; ?>"  height='100' style="margin-left:20%;"/>
                        <input name='info[<?php echo $key; ?>][name]' type='text' class='layui-input' style='width:71%' placeholder='重命名' value='<?php echo $v['name']; ?>'>
                        <input name='info[<?php echo $key; ?>][price]' type='text' lay-verify='required' class='layui-input' style='width:71%' placeholder='价格' value='<?php echo $v['price']; ?>'>
                        <input name='info[<?php echo $key; ?>][flower_id]' type='hidden' value='<?php echo $v['flower_id']; ?>'/>
                        <a title='删除' class='del-icon' onclick='flower_del(this)' href='javascript:;'><i class='layui-icon'>&#xe640;</i></a>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <div class="layui-form-item">
                    <label for="L_repass" class="layui-form-label"></label>
                    <input name="series_id" type="hidden" value="<?php echo $id; ?>"/>
                    <button class="layui-btn" lay-filter="add" lay-submit="">保存</button>
                </div>
            </form>
        </div> 
    </div>
    <script>layui.use(['form', 'layer'],
                function () {
                    $ = layui.jquery;
                    var form = layui.form,
                    layer = layui.layer;
            
                    var html = '<i class="icon iconfont check">&#xe6b1</i>';//选中icon图标
                    $(function(){
                        //点击图片添加选中icon
                        $('#all-flower').on('click','.check-box',function(){
                            $(this).find('i').remove();
                            if($(this).attr('attr') == 'false'){
                                $(this).attr('attr','true');
                                $(this).find('i').remove();
                            }else{
                                $(this).attr('attr','false');
                                 $(this).append(html)
                            }                      

                        })
                        
                        //添加花件图片
                        $('#add').click(function(){ 
                                                    
                            $('.check-box').each(function(){
                                if($(this).attr('attr') == 'false'){
                                    var row = $('.flower-list').find('div').length;
                                    var id = $(this).attr('ids');  //花件id
                                    var pic = $(this).find('img').attr('src'); //花件图片
                                    var check_html = "<div class='layui-col-xs3'><img src='"+pic+"'  height='100' style='margin-left:20%'/>\n\
                                                       <input name='info["+row+"][name]' type='text' class='layui-input' style='width:71%' placeholder='重命名'>\n\
                                                       <input name='info["+row+"][price]' type='text' lay-verify='required' class='layui-input' style='width:71%' placeholder='价格'>\n\
                                                        <input name='info["+row+"][flower_id]' type='hidden' value='"+id+"'/>\n\
                                                      <a title='删除' class='del-icon' onclick='flower_del(this)' href='javascript:;'><i class='layui-icon'>&#xe640;</i></a></div>";                                                        
                                    $('.flower-list').append(check_html);
                                }
                            })
                        })
                    })
                    //监听全选
                    form.on('checkbox(checkall)',function(data){
                          $('.check-box').find('i').remove();
                          if(data.elem.checked){
                            $('.check-box').attr('attr',false);
                            $('.check-box').append(html);
                          }else{       
                              $(this).attr('attr',true);
                             $('.check-box').find('i').remove();
                          }
                          form.render('checkbox');
                    })
                    
                    //监听是否可切 类型下拉
                    form.on('select(iscut)',function(data){
                        $.post("<?php echo url('findCut'); ?>",{iscut:data.value},function(obj){                            
                            var html = '';
                            var array = obj.data;     
                            for(i=0;i<array.length;i++){                              
                                html += "<div class='layui-col-xs3 check-box' ids='"+array[i]['id']+"'>\n\
                                          <img src='/upload/"+array[i]['pic']+"' height='100'/>\n\</div>";                                                             
                            }
                            $('#all-flower').html(html);
                            
                        },'json');
                    })
                    
                    //监听提交表单
                    form.on('submit(add)',
                            function (data) {
                                ajaxform("<?php echo url('series/flowerAdd'); ?>", data.field, 2);
                                return false;
                            });

                });
         /*删除*/
        function flower_del(obj) {
            $(obj).parent().remove();
        }
    </script>

</body>

</html>