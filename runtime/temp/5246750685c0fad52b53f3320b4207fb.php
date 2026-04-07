<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:108:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/finance/dealer_price.html";i:1635496793;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .red{color:red;}
    .layui-inline{height: 30px;line-height: 30px}
    .set_max_width{
        max-width: 10vw;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .layui-table td, .layui-table th {
        position: relative;
        padding: 9px 5px;
        min-height: 20px;
        line-height: 20px;
        font-size: 14px;
    }
</style>
    <body>
    <form class="layui-form" >
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-header" style="height: 180px">
                            <h3>经销商账套汇总</h3>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="text-align: left;padding-left: 0">
                                    选择经销商
                                </label>
                                <div class="layui-input-inline">
                                    <select name="dealer_id" lay-search="" lay-filter="dealer_id" lay-verify="required">
                                        <option value="">选择或输入经销商</option>
                                        <?php if(is_array($dealer) || $dealer instanceof \think\Collection || $dealer instanceof \think\Paginator): $i = 0; $__LIST__ = $dealer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                        <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-inline" style="margin-left: 30px">
                                    <p>总下单金额:<span class="red" id="dealer-price">0</span>&nbsp;&nbsp;
                                        总已收款金额(含定金):<span class="red" id="have-pay">0</span>&nbsp;&nbsp;
                                        总欠款金额:<span class="red" id="no-pay">0</span>
                                    </p>
                                </div>
                            </div>
                            <h3>添加本次收款信息</h3>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="text-align: left;padding-left: 0">
                                    收款日期
                                </label>
                                <div class="layui-input-inline" style="width: 150px">
                                    <input name="pay_time" id="pay_time" type="text" class="layui-input" lay-verify="required" autocomplete="off">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">
                                    收款方式
                                </label>
                                <div class="layui-input-inline" style="width: 150px">
                                    <select name="pay_type" lay-verify="required">
                                        <option></option>
                                        <?php if(is_array($pay_type) || $pay_type instanceof \think\Collection || $pay_type instanceof \think\Paginator): $i = 0; $__LIST__ = $pay_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($key != '0'): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $v; ?></option>
                                        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">
                                    收款金额
                                </label>
                                <div class="layui-input-inline" style="width: 150px">
                                    <input name="all_price" id="all_price" type="text" class="layui-input" lay-verify="number">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">
                                    折让金额
                                </label>
                                <div class="layui-input-inline" style="width: 150px;width: 70px">
                                    <input name="all_rebate_price" id="all_rebate_price" type="text" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <button class="layui-btn layui-btn-normal" lay-filter="cal" lay-submit="">核销运算</button>
                            </div>

                            <div class="layui-inline" style="float: right;margin-top: 11px">
                                <button class="layui-btn layui-btn-warm" lay-filter="save" lay-submit="">确定保存</button>
                            </div>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>
                                        <th>下单日期</th>
                                        <th>送货单号</th>
                                        <th>发货日期</th>
                                        <th>业务员</th>
                                        <th>客户名称</th>
                                        <th>送货地址</th>
                                        <th>订单号</th>
                                        <th>数量</th>
                                        <th>总金额</th>
                                        <th>已收款(含定金)</th>
                                        <th width="10%">本次核销</th>
                                        <th width="10%">折让(优惠)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-box">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </body>
    <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            
            //执行一个laydate实例
            laydate.render({
                elem: '#pay_time' //指定元素
            });
            form.on("select(dealer_id)",function (data) {
                var id = data.value;
                $.post("<?php echo url('getDealerPrice'); ?>",{id:id},function (res) {
                    if(res.code == 0){
                        var data = res.data;
                        $('#dealer-price').text(data.total_price);
                        $('#have-pay').text(data.have_pay);
                        $('#no-pay').text(data.no_pay);
                    }
                },'json')
            })

            //核销运算
            form.on('submit(cal)',
                function(data) {
                    layer.load();
                    $('#tbody-box').html('');
                    $.post("<?php echo url('getDealerOrder'); ?>",data.field,function (res) {
                        layer.closeAll('loading');
                        if(res.length == 0){
                            layer.msg('此经销商没有未核销的订单',{icon:2});
                            return;
                        }
                        var html = "";
                        $(res).each(function (k,v) {
                            html += "<tr>";
                            html += "<td>"+v['addtime']+"</td>";
                            html += "<td>"+v['snumber']+"</td>";
                            html += "<td>"+v['send_date']+"</td>";
                            html += "<td>"+v['sales_name']+"</td>";
                            html += "<td>"+v['dealer']+"</td>";
                            html += "<td class='set_max_width'>"+v['send_address']+"</td>";
                            html += "<td>"+v['number']+"</td>";
                            html += "<td>"+v['count']+"</td>";
                            html += "<td>"+v['total_price']+"</td>";
                            html += "<td>"+v['have_pay']+"</td>";
                            html += "<td><input name='cal[]' type='text' class='layui-input' value='"+v['cal']+"' lay-verify='number'></td>";
                            html += "<td><input name='rebate[]' type='text' class='layui-input' value=''><input name='id[]' type='hidden' value='"+v['id']+"'></td>";
                            html += "</tr>";
                        });
                        $('#tbody-box').html(html);
                        form.render();
                    });
                    return false;
                });

            //保存数据
            form.on('submit(save)',
                function(data) {
                    layer.confirm('确定要保存吗',function () {
                        ajaxform("<?php echo url('savePayments'); ?>",data.field,1);
                    })
                    return false;
                });
        });

    
        
    </script>

</html>