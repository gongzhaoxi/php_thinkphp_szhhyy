<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:102:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/user/edit_user.html";i:1635496795;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-form-label{ width: 90px;}
</style>
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            用户登录名
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="login_name" name="login_name" required="" lay-verify="required" class="layui-input" value="<?php echo $res['login_name']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不可重复，不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            密码
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="login_password" name="login_password" class="layui-input" placeholder="不修改则留空">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            姓名
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="uname" name="uname" required="" lay-verify="required" class="layui-input" value="<?php echo $res['uname']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            电话
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="phone" name="phone" class="layui-input" required="" lay-verify="phone" value="<?php echo $res['phone']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            所属角色
                        </label>
                        <div class="layui-input-inline">
                            <select name='depart' required="" lay-verify="required" lay-filter="departd">
                                <option value=''>请选择所属角色</option>
                               <?php if(is_array($role) || $role instanceof \think\Collection || $role instanceof \think\Paginator): $i = 0; $__LIST__ = $role;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <option value='<?php echo $v['id']; ?>' <?php if($v['id'] == $res['depart']): ?>selected<?php endif; ?>><?php echo $v['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>不能为空
                        </div>
                    </div>
                    <div class="layui-form-item" id="bind-dealer-box" style="<?php if($res['depart'] != 2): ?>display: none;<?php else: ?>display:block<?php endif; ?>" id="bind-dealer-box">
                        <label class="layui-form-label">
                            绑定经销商
                        </label>
                        <div class="layui-input-inline">
                            <select name='bind_dealer' lay-filter="select_dealer" lay-search="">
                                <option value=''>请选择绑定的经销商</option>
                                <?php if(is_array($dealer) || $dealer instanceof \think\Collection || $dealer instanceof \think\Paginator): $i = 0; $__LIST__ = $dealer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <option value='<?php echo $v['id']; ?>' <?php if($res['bind_dealer'] == $v['id']): ?>selected<?php endif; ?>><?php echo $v['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            职位
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="job" name="job" class="layui-input" value="<?php echo $res['job']; ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
<!--                            <span class="x-red">*</span>不能为空-->
                        </div>
                    </div>
                     
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            是否禁用
                        </label>
                        <div class="layui-input-inline">
                            <select name='is_disable'>
                                <option value='0' <?php if($res['is_disable'] == '0'): ?>selected<?php endif; ?>>否</option>
                                <option value='1' <?php if($res['is_disable'] == '1'): ?>selected<?php endif; ?>>是</option>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            
                        </div>
                    </div>
                   
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            学历
                        </label>
                        <div class="layui-input-inline">
                            <select name='teach'>
                                <option value=''>请选择学历</option>
                                <option value='本科'>本科</option>
                                <option value='大专'>大专</option>
                            </select>
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            入职时间
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="test" name="join_time" class="layui-input" value="<?php echo $res['join_time']; ?>">
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            紧急联系人姓名
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="warm_name" name="warm_name" class="layui-input" value="<?php echo $res['warm_name']; ?>">
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="phone" class="layui-form-label">
                            紧急联系人电话
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="warm_phone" name="warm_phone" class="layui-input" value="<?php echo $res['warm_phone']; ?>">
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label for="L_repass" class="layui-form-label"></label>
                        <input name="id" type="hidden" value="<?php echo $res['id']; ?>"/>
                        <button class="layui-btn" lay-filter="add" lay-submit="">保存</button>
                    </div>
        </form>
        </div>
        </div>
        <script>layui.use(['laydate','form'],
            function() {
                $ = layui.jquery;
                var form = layui.form;
                var laydate = layui.laydate;
                
                //执行一个laydate实例
                laydate.render({
                    elem: '#test' //指定元素
                });

                form.on('select(departd)',function(data){
                    if(data.value == 2){
                        $('#bind-dealer-box').css('display','block');
                    }else{
                        $('#bind-dealer-box').css('display','none');
                    }
                });

                form.on('select(select_dealer)',function(data){
                    $.post("<?php echo url('findDealer'); ?>",{id:data.value},function (data) {
                        var obj = data.data;
                        $('#uname').val(obj.name);
                        $('#phone').val(obj.contact);
                    },'json');
                });

                //监听提交
                form.on('submit(add)',
                function(data) {
                    ajaxform("<?php echo url('user/editUser'); ?>",data.field);
                    return false;
                });
                
            });</script>

    </body>

</html>