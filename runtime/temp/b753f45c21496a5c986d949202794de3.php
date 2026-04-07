<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/finance/read_order.html";i:1758810436;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>

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
    .alldisplay td{ text-align: center;}
    .cporgress{width: 30%;position: relative;bottom: 30px;left: 30%;display: none;}
</style>

<body>
    <div class="x-nav">
        <span class="layui-breadcrumb">
            <!--<a href="">订单信息</a>-->
            <a href="javascript:history.back(-1)"><i class="icon iconfont">&#xe697</i>返回</a>
        </span> 

        <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
            <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
        </a>
    </div>
    <form class="layui-form layui-col-space5" target="imgiframe" action="<?php echo url('Carorder/allIframe'); ?>" method="post" id="form1">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">

                <div class="layui-col-md12 layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            <blockquote class="layui-elem-quote">基础信息
                            </blockquote>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">订单编号</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="number" class="layui-input" readonly="true" value="<?php echo $res['number']; ?>">
                                </div>

                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">要求交货时间</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="end_time"  readonly="true" class="layui-input" value="<?php echo $res['end_time']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">经销商名称</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="dealer" required  lay-verify="required" readonly="true"  class="layui-input" value="<?php echo $res['dealer']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">电话</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="phone" required   readonly="true"  class="layui-input" value="<?php echo $res['phone']; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="address" class="layui-input" value="<?php echo $res['address']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">送货地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="send_address"  class="layui-input" value="<?php echo $res['send_address']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">订单类型</label>
                                <div class="layui-input-block" style="width: 30%">
                                    <label class="layui-form-label" style='text-align: left'><?php echo $order_type[$res['type']]; ?></label>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label" =>备注</label>
                                <div class="layui-input-block" style="width: 63%;"> 
                                    <input type="text" name="note"  class="layui-input" value="<?php echo $res['note']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="layui-inline" >
                                <label class="layui-form-label" style="color: red">总金额</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="total_price" id="total_price" required  lay-verify="required" class="layui-input" value="<?php echo $res['total_price']; ?>" readonly>
                                </div>
                            </div>
                            <div class="layui-inline" >
                                <label class="layui-form-label" style="color: red">本次收款</label>
                                <div class="layui-input-inline" style="width: 30%">
                                    <input type="text" name="have_pay" id="have_pay" required  lay-verify="required" class="layui-input">
                                </div>
                                已收款：<?php echo $res['have_pay']; ?>
                            </div>
                            <div class="layui-inline" >
                                <label class="layui-form-label" style="color: red">本次折让(优惠)</label>
                                <div class="layui-input-inline" style="width: 30%">
                                    <input type="text" name="finance_rebate" id="finance_rebate" required  lay-verify="required" class="layui-input">
                                </div>
                                已折让：<?php echo $res['finance_rebate_price']; ?>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="color: red">未收款</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="no_pay" id="no_pay" required  lay-verify="required" class="layui-input" value="<?php echo round($res['total_price']-$res['have_pay']-$res['finance_rebate_price'],2) ?>" readonly>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="color: red">其它</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="other_pay" id="other_pay" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="color: red">收款方式</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <select name='pay_type'>
                                        <?php if(is_array($pay_type) || $pay_type instanceof \think\Collection || $pay_type instanceof \think\Paginator): $i = 0; $__LIST__ = $pay_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                        <option value="<?php echo $key; ?>"><?php echo $v; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="color: red">收款时间</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input name="paid_time" id="paid_time" type="text" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="color: red">收款备注</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input name="finance_remark"  type="text" class="layui-input">
                                </div>
                            </div>								
                            <div class="layui-form-item">
                                <input name="order_id" id="order_id" type="hidden" value="<?php echo $orderid; ?>"/>
                                <button class="layui-btn" lay-filter="edit" lay-submit="" style="margin:10px 0 0 15%">
                                   <i class="layui-icon"></i>保存收款信息
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-col-md12">
                    <div class="layui-card">
                        <style type="text/css">
                          .layui-card-body > div{ margin-top: 0px; }
                          .layui-form th,.layui-form td{text-align: center;}
                        </style>
                        <div class="layui-card-header" style="padding: 10px 15px;"> 
                            <?php if(($res['status'] == 2) or ($res['status2'] == 4)): ?>
                            <button class="layui-btn" type="button" onclick="check(<?php echo $orderid; ?>)" id="check-btn">
                                <i class="layui-icon"></i>审核通过并下发到车间
                            </button>
                            <?php elseif($res['sign_time'] != ''): ?>
                            <button class="layui-btn" type="button" onclick="confirm(<?php echo $orderid; ?>)" id="check-btn">
                                <i class="layui-icon"></i>确认已完工
                            </button>
                            <?php endif; ?>
                            <button class="layui-btn layui-btn-warm" type='button' id='winform-print'>
                                <i class="layui-icon"></i>打印报价单
                            </button>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">产品信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;">
                                <thead>
                                    <tr>

                                        <th width="30">编号</th>
                                        <th>名称</th>
                                        <th>材质</th>
                                        <th>型号</th>
                                        <th>铝材/花件颜色</th>
                                        <th>单位</th>
                                        <th>宽</th>
                                        <th>高</th>
                                        <th>个数</th>
                                        <th>面积</th>
                                        <th>单价</th>
                                        <th>折后价</th>
                                        <th>总金额</th>
                                        <th>窗型结构</th>
                                        <!--<th>操作</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($product) || $product instanceof \think\Collection || $product instanceof \think\Paginator): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>

                                        <td width="30"><?php echo $i; ?></td>
                                        <td><?php echo $v['name']; ?></td>
                                        <td><?php echo $v['material']; ?></td>
                                        <td><?php echo $v['flower_type']; ?></td>
                                        <td><?php echo $v['color_name']; ?></td>
                                        <td>㎡</td>
                                        <td><?php echo $v['all_width']; ?></td>
                                        <td><?php echo $v['all_height']; ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><img src='/upload/<?php echo $v['structure']; ?>' height="60"/></td>
<!--                                        <td class="td-manage">
                                            <input name="op_id[]" type="hidden" value="<?php echo $v['op_id']; ?>"/>
                                            <input name="series_id[]" type="hidden" value="<?php echo $v['series_id']; ?>"/>
                                            <input name="structure_id[]" type="hidden" value="<?php echo $v['structure_id']; ?>"/>
                                            <a title="查看" onclick="xadmin.open('编辑产品', '<?php echo url('Carorder/readProduct',array('id'=>$v['op_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>-->
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">组合单信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="group-table" lay-filter="group-table" style="margin: 0px;">
                                  <thead>
                                    <tr>
                                        
                                        <th>编号</th>
                                        <th>总宽</th>
                                        <th>总高</th>
                                        <th>面积</th>
                                        <th>报价总个数</th>                                        
                                        <th>生成总个数</th>
                                        <th>总金额</th>
                                        <!--<th>操作</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($group) || $group instanceof \think\Collection || $group instanceof \think\Paginator): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $v['width']; ?></td>
                                        <td><?php echo $v['height']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['price_count']; ?></td>
                                        <td><?php echo $v['calculate_count']; ?></td>
                                        <td><?php echo $v['total_price']; ?></td>
<!--                                        <td class="td-manage">
                                            <a title="查看" onclick="open_group('编辑产品', '<?php echo url('order/addGroup',array('id'=>$v['og_id'],'order_id'=>$v['order_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>-->
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody> 
                                
                            </table>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">原材料信息</blockquote>                       
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;">
                                <thead>
                                    <tr>

                                        <th width="30">编号</th>
                                        <th>名称</th>
                                        <th>材质/型号</th>
                                        <th>铝材/花件颜色</th>
                                        <th>单位</th>
                                        <th>宽</th>
                                        <th>高</th>
                                        <th>个数</th>
                                        <th>面积</th>
                                        <th>单价</th>
                                        <th>折后价</th>
                                        <th>总金额</th>
                                        <!--<th>操作</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($material) || $material instanceof \think\Collection || $material instanceof \think\Paginator): $i = 0; $__LIST__ = $material;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>

                                        <td width="30"><?php echo $i; ?></td>
                                        <td><?php echo $v['name']; ?></td>
                                        <td><?php echo $v['type']; ?></td>
                                        <td><?php echo $v['color']; ?></td>
                                        <td>㎡</td>
                                        <td><?php echo $v['width']; ?></td>
                                        <td><?php echo $v['height']; ?></td>
                                        <td><?php echo $v['count']; ?></td>
                                        <td><?php echo $v['area']; ?></td>
                                        <td><?php echo $v['price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
                                        <td><?php echo $v['all_price']; ?></td>
<!--                                        <td class="td-manage">
                                            <a title="查看" onclick="xadmin.open('编辑产品', '<?php echo url('Carorder/readMaterial',array('id'=>$v['om_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>-->
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <input name="order_id" type="hidden" value="<?php echo $orderid; ?>"/>
        </div>
    </form>
</body>
<script>
    layui.use(['laydate', 'form','element'],
            function () {
            var laydate = layui.laydate;
            var form = layui.form;
            var element = layui.element;
            $ = layui.jquery;
            laydate.render({
                elem:'#paid_time',
                type:'datetime'
            })

            //设置左侧菜单栏的提示数量
            $(function(){
                 $.post("<?php echo url('order/financeNoHandle'); ?>",{id:1},function(obj){                         
                    $('#finance-no-handle',parent.document).text(obj.data);                      
                 },'json');
                
                 $.post("<?php echo url('carorder/noHandleCount'); ?>",{id:1},function(obj){                         
                    $('#no-handle-count',parent.document).text(obj.data);                      
                 },'json');
            })
            
            //调用winform里的打印方法
                $(function(){
                    var number = "<?php echo $res['number']; ?>";
                    $("#winform-print").click(function () {
                           var a = getuserName.getName(number);                 
                    });
               })
            
            //执行一个laydate实例
            laydate.render({
            elem: '#end_date' //指定元素
            });
            //监听提交表单
            form.on('submit(edit)',
                    function (data) {
                    ajaxform("<?php echo url('readOrder'); ?>", data.field, 1);
                    return false;
                    });
                    
            $(function(){
                $('#have_pay').change(function(){
                    var total = $('#total_price').val();
                    var have_pay = Number($(this).val());
                    var rebate = Number($('#finance_rebate').val());
                    var no_pay = (total-have_pay-rebate).toFixed(2);
                    $('#no_pay').val(no_pay);
                })
                $('#finance_rebate').change(function(){
                    var total = $('#total_price').val();
                    var have_pay = Number($('#have_pay').val());
                    var rebate = Number($(this).val());
                    var no_pay = (total-have_pay-rebate).toFixed(2);
                    $('#no_pay').val(no_pay);
                })
            })
            
            });
            
    function check(id){
         var status = <?php echo $res['status']; ?>;
         var status2 = <?php echo $res['status2']; ?>;
         var field = status2>status?'status2':'status';
         var status_value = status2>status?status2:status
         layer.confirm('确定通过审核',function(){
             ajaxform("<?php echo url('check'); ?>", {id:id,field:field,status:status_value}, 1);
         });
         
     }      
     function confirm(id){
         layer.confirm('确定已完工?',function(){
             ajaxform("<?php echo url('confirm'); ?>", {id:id}, 1);
         });
         
     } 
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>

</script>
</html>