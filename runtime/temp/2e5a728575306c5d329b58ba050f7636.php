<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:103:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/structure.html";i:1741247190;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .check { position: absolute;left: 12%;top:40px;}
    .del-icon{ margin-left: 30%;}
    .layui-col-xs3 {float:left; width:auto !important;padding-right:20px;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <div style="font-size: 14px; color: #ff0000;">提示：如无法选择或找不到您需要的窗型结构，代表此尺寸做不下此花型，建议更换其它花型下单，如有疑问请联系微信下单官号或致电：4007776388。</div>
            <form class="layui-form">
                <blockquote class="layui-elem-quote">
                    <div class="layui-inline">
                        <select name="window_type" id="window_type" lay-filter="selectd">
                            <option value="">窗型类型</option>
                            <option value="常规">常规</option>
                            <option value="飘窗">飘窗</option>
                            <option value="圆弧窗">圆弧窗</option>
                            <option value="内弧(拱)形窗">内弧(拱)形窗</option>
                            <option value="外弧(拱)形窗">外弧(拱)形窗</option>
                            <option value="内弧(拱)形护栏">内弧(拱)形护栏</option>
                              <option value="外弧(拱)形窗护栏">外弧(拱)形窗护栏</option>
                        </select>
                    </div>
                     <div class="layui-inline">
                        <select name="hands" id="hands" lay-filter="selectd">
                            <option value="">全部把手</option>
                            <option value="左把手">左把手</option>
                            <option value="右把手">右把手</option>
                            <option value="无把手">无把手</option>
                            <option value="双把手">双把手</option>                            
                        </select>
                    </div>
                    
                    <div class="layui-inline">
                        <select name="fixed" id="fixed" lay-filter="selectd">
                            <option value="">全部固定</option>
                            <option value="不带固定">不带固定</option>
                            <option value="上固定">上固定</option>
                            <option value="下固定">下固定</option>
                            <option value="上下固定">上下固定</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <select name="escape" id='escape'  lay-filter="selectd">
                            <option value="">逃生窗类型</option>
                            <option value="没有逃生窗">没有逃生窗</option>
                             <option value="有逃生窗">有逃生窗</option>
                        </select>
                    </div>
                </blockquote> 
                <div class="layui-col-xs12" id="all-flower">
                    <?php if(is_array($structure) || $structure instanceof \think\Collection || $structure instanceof \think\Paginator): $i = 0; $__LIST__ = $structure;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <div class="layui-col-xs3 check-box" spacing_count="<?php echo $v['spacing_count']; ?>" frame_count="<?php echo $v['frame_count']; ?>" sid="<?php echo $v['structure_id']; ?>" ids='<?php echo $v['ss_id']; ?>' hands="<?php echo $v['hands']; ?>" fixed="<?php echo $v['fixed']; ?>" pic="<?php echo $v['structure_pic']; ?>" window_type="<?php echo $v['window_type']; ?>">
                        <img src="/upload/<?php echo $v['structure_pic']; ?>"  height="220"/>
<!--                        <input name='info[<?php echo $key; ?>][name]' id="name" type='text' class='layui-input' style='width:55%' placeholder='重命名' value='<?php echo $v['name']; ?>'>
                        <input name='info[<?php echo $key; ?>][price]' type='text' lay-verify='required' class='layui-input' style='width:55%' placeholder='价格' value='<?php echo $v['price']; ?>'>-->
                        
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>  
                <div class="layui-col-xs12 flower-list">
                   
                </div>
                <div class="layui-form-item">
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
                    
                    //监听下拉刷新
                    form.on('select(selectd)',function(data){
                        var window = $('#window_type option:selected').val();
                        var hands = $('#hands option:selected').val();
                        var fixed = $('#fixed option:selected').val();
                        var escape = $('#escape option:selected').val();
                        var id = <?php echo $series_id; ?>;
                        var width = <?php echo $width; ?>;
                        var height = <?php echo $height; ?>;
                        $.post("<?php echo url('order/findStructure'); ?>",{window_type:window,hands:hands,fixed:fixed,id:id,width:width,height:height,escape:escape},function(obj){                            
                            var html = '';
                            var array = obj.data;     
                            for(i=0;i<array.length;i++){                              
                                html += "<div class='layui-col-xs3 check-box' sid='"+array[i]['structure_id']+"' ids='"+array[i]['ss_id']+"' hands='"+array[i]['hands']+"' fixed='"+array[i]['fixed']+"'\n\
                                           pic='"+array[i]['structure_pic']+"' window_type='"+array[i]['window_type']+"' spacing_count='"+array[i]['spacing_count']+"' frame_count='"+array[i]['frame_count']+"'>\n\
                                          <img src='/upload/"+array[i]['structure_pic']+"'  height='100'/>\n\
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
            var frame_thick = <?php echo $frame; ?>;
            $('.check-box').each(function(){
                if($(this).attr('check') == 'true'){
                    data = {
                                id:$(this).attr('ids'),name:$(this).find('#name').val(),pic:$(this).find('img').attr('src'),pic2:$(this).attr('pic'),
                                hands:$(this).attr('hands'),fixed:$(this).attr('fixed'),window_type:$(this).attr('window_type'),structure_id:$(this).attr('sid'),
                                spacing_count:$(this).attr('spacing_count'),frame_count:$(this).attr('frame_count'),frame_thick:frame_thick
                           };
                }
            })            
            return data;
        }
    </script>

</body>

</html>