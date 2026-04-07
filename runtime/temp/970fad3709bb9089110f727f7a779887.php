<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/series/bom_edit.html";i:1722820676;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?>
            <div class="layui-form-item">
                <label class="layui-form-label"><?php echo $bomname[$v['type']]; ?></label>
                <div class="layui-inline">
                    <select name="info[<?php echo $k; ?>][one_level]" class="one_level" lay-filter='one'>
                        <option value='' <?php if($v['one_level'] == ''): ?>selected<?php endif; ?>>请选择</option>
                        <option value="bom_aluminum" <?php if($v['one_level'] == 'bom_aluminum'): ?>selected<?php endif; ?>>铝型材</option>
                        <option value="bom_flower" <?php if($v['one_level'] == 'bom_flower'): ?>selected<?php endif; ?>>花件</option>
                        <option value="bom_five" <?php if($v['one_level'] == 'bom_five'): ?>selected<?php endif; ?>>五件</option>
                        <option value="bom_hands" <?php if($v['one_level'] == 'bom_hands'): ?>selected<?php endif; ?>>把手位</option>
                        <option value="bom_yarn" <?php if($v['one_level'] == 'bom_yarn'): ?>selected<?php endif; ?>>纱网</option>
                    </select>
                    <input name="info[<?php echo $k; ?>][type]" type='hidden' value='<?php echo $k; ?>'/>
                    <input name='two_level' type='hidden' class='two_level' value='<?php echo $v['two_level']; ?>'/>
                </div>
                <div class="layui-inline" {if condition="$k == 6"}style="width:25%"{if/}>
                    <select name="info[<?php echo $k; ?>][two_level]" class="boms">
                        
                    </select>
                </div>
                <?php if($k == 1): ?>
                <div class="layui-inline">
                    <input type="text" name="take" class="layui-input" placeholder="框搭框" <?php if($v['take'] != 0): ?>value='<?php echo $v['take']; ?>'<?php endif; ?>>
                </div>
                <?php elseif($k==2): ?>
                <div class="layui-inline">
                    <input type="text" name="frame_take_fan" class="layui-input" placeholder="框搭扇" <?php if($list[0]['frame_take_fan'] != 0): ?>value='<?php echo $list[0]['frame_take_fan']; ?>'<?php endif; ?>>
                </div>
				<div class="layui-inline">
				    <input type="text" name="waikuangbian" class="layui-input" placeholder="外框边" <?php if($list[0]['waikuangbian'] != 0): ?>value='<?php echo $list[0]['waikuangbian']; ?>'<?php endif; ?>>
				</div>
				<?php elseif($k==4): ?>
				<div class="layui-inline">
				    <input type="text" name="shawangbian" class="layui-input" placeholder="纱网边" <?php if($list[0]['shawangbian'] != 0): ?>value='<?php echo $list[0]['shawangbian']; ?>'<?php endif; ?>>
				</div>
				<?php elseif($k==5): ?>
				<div class="layui-inline">
				    <input type="text" name="menshanbian" class="layui-input" placeholder="门扇边" <?php if($list[0]['menshanbian'] != 0): ?>value='<?php echo $list[0]['menshanbian']; ?>'<?php endif; ?>>
				</div>
                <?php elseif($k==6): ?>
                <div class="layui-inline" style="width: 10%">
                    <input type="text" name="small_frame" class="layui-input" placeholder="小门框搭框" <?php if($list[0]['small_frame'] != 0): ?>value='<?php echo $list[0]['small_frame']; ?>'<?php endif; ?>>
                </div>
                <div class="layui-inline" style="width: 10%">
                    <input type="text" name="small_fan" class="layui-input" placeholder="小门框搭扇" <?php if($list[0]['small_fan'] != 0): ?>value='<?php echo $list[0]['small_fan']; ?>'<?php endif; ?>>
                </div>
				<?php elseif($k==13): ?>
				<div class="layui-inline" style="width: 10%">
					<input type="text" name="ZK_B_KDK" class="layui-input" placeholder="窄边框搭框" <?php if($list[0]['ZK_B_KDK'] != 0): ?>value='<?php echo $list[0]['ZK_B_KDK']; ?>'<?php endif; ?>>
				</div>
				<div class="layui-inline" style="width: 10%">
					<input type="text" name="ZK_B_KDS" class="layui-input" placeholder="窄外框搭扇" <?php if($list[0]['ZK_B_KDS'] != 0): ?>value='<?php echo $list[0]['ZK_B_KDS']; ?>'<?php endif; ?>>
				</div>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <div class="layui-form-item">
                <label for="L_repass" class="layui-form-label"></label>
                <input name="series_id" type="hidden" value="<?php echo $id; ?>"/>
                <button class="layui-btn" lay-filter="add" lay-submit="">保存</button>
            </div>
        </form>
    </div>
</div>
<script>
    layui.use(['form', 'layer'],
            function () {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;
              
              
                $(function(){
                    $('select.one_level').each(function(){
                        var boms = $(this);
                        var two_level = $(this).nextAll('input[name=two_level]').val();
                        var table = $(this).find('option:selected').val();
                        if(table){
                            $.post("<?php echo url('series/findbom'); ?>",{table:table},function(obj){  
                                var html = '';
                                var array = obj.data;     
                                for(i=0;i<array.length;i++){
                                    if(two_level == array[i]['id']){
                                       var select = 'selected';
                                    }else{
                                       var select = '1' ;
                                    }
                                    html += "<option value="+array[i]['id']+" "+select+">"+array[i]['code']+"("+array[i]['name']+")</option>";
                                }      
                                boms.parent().next().find('select').html(html); 
                                form.render('select');  //更新渲染
                            },'json'); 
                        }
                    })
                })
                //监听select,selct二级联动
                form.on('select(one)',
                        function (data) {
                             $.post("<?php echo url('findbom'); ?>",{table:data.value},function(obj){
                                var html = '';
                                var array = obj.data;     
                                for(i=0;i<array.length;i++){
                                    html += "<option value="+array[i]['id']+">"+array[i]['code']+"("+array[i]['name']+")</option>";
                                }
                                $(data.elem).parent().next().find('select').html(html);
                                form.render('select');  //更新渲染
                             },'json');
                        });
                        
                //监听提交表单
                form.on('submit(add)',
                        function (data) {                         
                            ajaxform("<?php echo url('series/bomAdd'); ?>", data.field,2);
                            return false;
                        });
            });

</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>  
<script>
    
</script>
</body>

</html>