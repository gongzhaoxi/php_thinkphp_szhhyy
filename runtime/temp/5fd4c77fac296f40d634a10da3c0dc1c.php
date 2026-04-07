<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/order/flower.html";i:1635496794;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .check { position: absolute;left: 20%;top:35px;}
    .del-icon{ margin-left: 30%;}
    #check-box { margin-top: 23px;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form">
                <blockquote class="layui-elem-quote">
                    <div style="width: 20%;display: inline-block;">
                        <select name="type" id="type" lay-filter='iscut'>
                            <option value="">全部类型</option>
                            <option value="0">不可切花件</option>
                            <option value="1">可切花件</option>
                        </select>
                    </div>
                    <div style="width:20%;display: inline-block;">
                        <input name="search" type="text" placeholder="搜索名称" class="layui-input" value="<?php echo $keyword; ?>"/>
                        
                    </div >
                    <div style="width:20%;display: inline-block;">
                        <button type="submit" class="layui-btn">搜索</button>
                    </div>
                </blockquote> 
                <div class="layui-col-xs12" id="all-flower">
                    <?php if(is_array($flower) || $flower instanceof \think\Collection || $flower instanceof \think\Paginator): $i = 0; $__LIST__ = $flower;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <div class="layui-col-xs3 check-box" ids='<?php echo $v['id']; ?>'pic="<?php echo $v['pic']; ?>" max_height="<?php echo $v['max_height']; ?>" min_height="<?php echo $v['min_height']; ?>">
                        <img src="/upload/<?php echo $v['pic']; ?>"  height="100"/>
                        <input name='info[<?php echo $key; ?>][name]' id="name" type='text' class='layui-input' style='width:55%' placeholder='重命名' value='<?php echo $v['name']; ?>'>
                        <input name='info[<?php echo $key; ?>][price]' type='text' lay-verify='required' class='layui-input' style='width:55%' placeholder='价格' value='<?php echo $v['price']; ?>'>
                        
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>  
                <div class="layui-col-xs12 flower-list">
                   
                </div>
                <div class="layui-form-item">
                    <input name="series_id" id="series_id" value="<?php echo $series_id; ?>" type="hidden"/>
                    <label for="L_repass" class="layui-form-label"></label>
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
                            $('.check-box').attr('check','false');
                            $('.check-box').find('i').remove();
                            $(this).attr('check','true');
                            $(this).append(html)              

                        })
                    })
                    
                    //监听是否可切 类型下拉
                    form.on('select(iscut)',function(data){
                        $.post("<?php echo url('order/findCut'); ?>",{iscut:data.value,series_id:$('#series_id').val()},function(obj){                            
                            var html = '';
                            var row = $('.flower-list').find('div').length;
                            var array = obj.data;     
                            for(i=0;i<array.length;i++){                              
                                html += "<div class='layui-col-xs3 check-box' ids='"+array[i]['id']+"' pic='"+array[i]['pic']+"' max_height='"+array[i]['max_height']+"' min_height='"+array[i]['min_height']+"'>\n\
                                         <img src='/upload/"+array[i]['pic']+"' height='100'/><input name='info["+row+"][name]' type='text' class='layui-input' value="+array[i]['name']+" style='width:55%' placeholder='重命名'>\n\
                                         <input name='info["+row+"][price]' type='text' lay-verify='required' class='layui-input' style='width:55%' placeholder='价格' value="+array[i]['price']+">\n\
                                          </div>";                                                             
                            }
                            $('#all-flower').html(html);
                            
                        },'json');
                    })
                });
        
    </script>
    <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script>
         //回调参数
        function callbackdata() {
            var data = {};
            $('.check-box').each(function(){
                if($(this).attr('check') == 'true'){
                    data = {id:$(this).attr('ids'),name:$(this).find('#name').val(),pic:$(this).attr('pic'),max_height:$(this).attr('max_height'),
                        min_height:$(this).attr('min_height')
                    };
                }
            })            
            return data;
        }
    </script>

</body>

</html>