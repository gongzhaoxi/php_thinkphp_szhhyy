<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/finance/paid_record.html";i:1635496793;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <div class="layui-form-item" style="margin-bottom: 0px;">
                        <label class="layui-form-label" style="text-align: left;width: 200px">
                            收款日期:&nbsp;&nbsp;&nbsp;&nbsp;<?php echo date('Y-m-d H:i:s',$v['addtime']); ?>
                        </label>
                    </div>
                    <div class="layui-form-item box">
                        <label class="layui-form-label" style="width: 50px;">
                            收款金额
                        </label>
                        <div class="layui-input-inline" style="width: 70px">
                            <input type="text" id="have_pay" name="have_pay" required="" lay-verify="required" class="layui-input" value="<?php echo $v['have_pay']; ?>">
                        </div>
                        <label class="layui-form-label" style="width: 50px;">
                            收款方式
                        </label>
                        <div class="layui-input-inline" style="width: 80px">
                            <select name="pay_type">
                                <?php if(is_array($pay_type) || $pay_type instanceof \think\Collection || $pay_type instanceof \think\Paginator): $i = 0; $__LIST__ = $pay_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v2): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo $key; ?>" <?php if($v['pay_type'] == $key): ?>selected<?php endif; ?>><?php echo $v2; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <label class="layui-form-label" style="width: 30px;">
                            折让
                        </label>
                        <div class="layui-input-inline" style="width: 70px">
                            <input type="text" id="finance_rebate" name="finance_rebate"  class="layui-input" value="<?php echo $v['finance_rebate']; ?>">
                        </div>
                        <label class="layui-form-label" style="width: 50px;">
                            其它收款
                        </label>
                        <div class="layui-input-inline" style="width: 70px">
                            <input type="text" id="other_pay" name="other_pay"  class="layui-input" value="<?php echo $v['other_pay']; ?>">
                        </div>
                        <input type="hidden"  name="id" value="<?php echo $v['id']; ?>">
                        <button type="button" class="layui-btn layui-btn-sm save-btn">保存</button>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <input type="hidden" id="order_id"  name="order_id" value="<?php echo $order_id; ?>">
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

                $('.save-btn').click(function () {
                    var _box = $(this).parents('.box');
                    var have_pay = _box.find('input[name=have_pay]').val();
                    var pay_type = _box.find('select option:selected').val();
                    var other_pay = _box.find('input[name=other_pay]').val();
                    var rebate = _box.find('input[name=finance_rebate]').val();
                    var id = _box.find('input[name=id]').val();
                    var order_id = $('#order_id').val();
                    ajaxform("<?php echo url('paidRecord'); ?>",{have_pay:have_pay,pay_type:pay_type,other_pay:other_pay,id:id,order_id:order_id,rebate:rebate})
                });

                
            });</script>

    </body>

</html>