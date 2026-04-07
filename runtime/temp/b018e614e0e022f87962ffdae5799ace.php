<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:98:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/edit.html";i:1742870842;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>

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
    .layui-table-cell{ height: 50px;line-height: 50px;}
    .layui-table-edit{ margin-top: 15px}
    .layui-table td,.layui-table th{text-align: center;}
    .layui-table img{max-width: 50px;max-height: 50px;}
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
    <form class="layui-form layui-col-space5">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">

                <div class="layui-col-md12 layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            <blockquote class="layui-elem-quote">基础信息
                            <?php if($uid == 1): ?>
                            <button lay-filter="editStatus" lay-submit="" style="float:right;">手动入库</button>
                            <?php endif; ?>
                            </blockquote>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">订单编号</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="number" class="layui-input" readonly="true" placeholder="保存订单后自动生成" value="<?php echo $res['number']; ?>">
                                </div>

                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">要求交货时间</label>
                                <div class="layui-input-inline" style="width:55%">
                                     <input type="text" name="end_time" id="end_date" class="layui-input" value="<?php echo $res['end_time']; ?>" > 
                                </div>
<!--                                <button class="layui-btn" id="refresh" onclick="" type='button'>
                                    刷新时间
                                </button>-->
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label" >生产完成时间</label>
                                <div class="layui-input-inline" style="width:55%">
                                     <input type="text" name="make_time" id="make_date" class="layui-input" value="<?php echo $res['make_time']; ?>" readonly="true"> 
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">是否打印</label>
                                <div class="layui-input-inline">
                                <select name='is_printing'>
                                    <option value="0" <?php if($res['is_printing'] == 0): ?>selected<?php endif; ?>>未打印</option>
                                    <option value="1" <?php if($res['is_printing'] == 1): ?>selected<?php endif; ?>>已打印</option>
                                </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">经销商名称</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="dealer" required  lay-verify="required"  class="layui-input" value="<?php echo $res['dealer']; ?>" readonly="true" style="width: 150px;display: initial;">
									<span style="color: #f00;">总欠款：<?php echo $Tno_pay; ?></span>
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">电话</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="phone" required  lay-verify="required"  class="layui-input" value="<?php echo $res['phone']; ?>" readonly="true">
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
                                    <input type="text" name="send_address"  class="layui-input" value="<?php echo $res['send_address']; ?>">
                                </div>
                            </div>
							<div class="layui-inline" style="width: 40%">
							    <label class="layui-form-label">楼盘位置</label>
							    <div class="layui-input-inline" style="width:55%">
							        <input type="text" name="building"  class="layui-input" value="<?php echo $res['building']; ?>">
							    </div>
							</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">订单类型</label>
                                <div class="layui-input-block" style="width: 50%">
                                    <input type="radio" name="type" value="1" title="常规" <?php if($res['type'] == 1): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="2" title="加急" <?php if($res['type'] == 2): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="3" title="样板" <?php if($res['type'] == 3): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="9" title="样板2" <?php if($res['type'] == 9): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="4" title="返修单" <?php if($res['type'] == 4): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="5" title="单剪网" <?php if($res['type'] == 5): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="6" title="单切料" <?php if($res['type'] == 6): ?>checked<?php endif; ?>>
                                    <input type="radio" name="type" value="7" title="工程" <?php if($res['type'] == 7): ?>checked<?php endif; ?>>
									<input type="radio" name="type" value="8" title="重做" <?php if($res['type'] == 8): ?>checked<?php endif; ?>>
                                </div>
                            </div>
							<div class="layui-inline">
							    <label class="layui-form-label">是否打印标签</label>
							    <div class="layui-input-inline">
							    <select name='print_label'>
							        <option value="0" <?php if($res['print_label'] == 0): ?>selected<?php endif; ?>>需要打印</option>
							        <option value="1" <?php if($res['print_label'] == 1): ?>selected<?php endif; ?>>不需要打印</option>
							    </select>
							    </div>
							</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注</label>
                                <div class="layui-input-block" style="width: 63%;"> 
                                    <input type="text" name="note"  class="layui-input" value="<?php echo $res['note']; ?>">
                                </div>
                            </div>
                            <table class="layui-table">
                                <tbody class='alldisplay'>
                                    <tr>
                                        <td>总金额:<span style='color: red' id="total_price"><?php echo $res['total_price']; ?><input name="total_price" type="hidden" value="<?php echo $res['total_price']; ?>"/></span></td>

                                        <td>总面积:<span style='color: red' id='total_area'><?php echo $res['area']; ?></span></td>
                                        <td>产品面积:<span style='color: red' id='total_area'><?php echo $res['product_area']-$material_parea; ?></span></td>
                                        <td>总数量:<span style='color: red' id='total_count'><?php echo $res['count']; ?></span></td>
                                        <td style="width: 15%">
                                            <p>定金:<input name="have_pay" id="have_pay" type="text" class="layui-input" style="display: inline;width: 80%" value="<?php echo $have_pay['have_pay']; ?>"></p>
                                        </td>
                                        <td style="width: 15%">
                                            <span>收款方式:</span>
                                            <span style="width: 60%; display: inline-block">
                                            <select name="pay_type">
                                                <?php if(is_array($pay_type) || $pay_type instanceof \think\Collection || $pay_type instanceof \think\Paginator): $i = 0; $__LIST__ = $pay_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                                <option value="<?php echo $key; ?>" <?php if($have_pay['pay_type'] == $key): ?>selected<?php endif; ?>><?php echo $v; ?></option>
                                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                            </select>
                                            </span>
                                        </td>
                                        <td>总收款金额(含定金):<span style='color: red'><?php echo $res['have_pay']; ?></span></td>
                                        <td style="width: 15%">余款:
                                            <span style='color: red' id='no_pay'><?php echo round($res['total_price']-$res['have_pay']-$res['finance_rebate_price'],2); ?>
                                            </span>
                                            <?php if($res['finance_rebate_price'] > 0): ?>
                                             | 拆让：<span style='color: red'><?php echo $res['finance_rebate_price']; ?></span>
                                            <?php endif; ?>
                                            <input name="no_pay" id="no_pay_input" type="hidden" value="<?php echo $res['total_price']-$res['have_pay'] ?>"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="layui-form-item">
                                <input name="order_id" id="order_id" type="hidden" value="<?php echo $orderid; ?>"/>
                                <button class="layui-btn" lay-filter="edit" lay-submit="" style="margin:10px 0 0 15%">
                                   <i class="layui-icon"></i>保存基础信息
                                </button>
                                <?php if($res['status'] >=4 || $res['status2']>=6): ?>
                                <button class="layui-btn layui-btn-normal" type="button" id="refresh-third"  style="margin:10px 0 0 35px">
                                    刷新数据到车间
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-header" style="padding: 10px 15px;">                            
                            <button class="layui-btn" onclick="xadmin.open('添加产品', '<?php echo url('addProduct',array('orderid'=>$orderid)); ?>', 1200, 600)" type='button'>
                                <i class="layui-icon"></i>添加产品
                            </button>
                            <button class="layui-btn layui-btn-normal" type='button' onclick="xadmin.open('添加原材料', '<?php echo url('addMaterial',array('orderid'=>$orderid)); ?>',1200,600)">
                                <i class="layui-icon"></i>添加原材料
                            </button>
                            <button class="layui-btn " type='button' onclick="xadmin.open('添加手工单', '<?php echo url('addHandMade',array('orderid'=>$orderid)); ?>', 1200, 600)">
                                <i class="layui-icon"></i>添加手工单
                            </button>
                            <button class="layui-btn layui-btn-normal" type='button' onclick="open_group('添加组合单', '<?php echo url('addGroup',array('order_id'=>$orderid)); ?>')">
                                <i class="layui-icon"></i>添加组合单
                            </button>
<!--                            <button class="layui-btn layui-btn-warm" type='button' onclick="xadmin.open('打印报价单', '<?php echo url('printing',array('orderid'=>$orderid)); ?>', 1200, 600)">
                                <i class="layui-icon"></i>打印报价单
                            </button>-->
                            <button class="layui-btn layui-btn-danger" type='button' onclick="xadmin.open('添加定制类产品', '<?php echo url('addDiy',array('orderid'=>$orderid)); ?>',1300,600)">
                                <i class="layui-icon"></i>定制类产品报价
                            </button>
                            <button class="layui-btn layui-btn-warm" type='button' id='winform-print'>
                                <i class="layui-icon"></i>打印报价单（ERP版打印）
                            </button>
							<button class="layui-btn layui-btn-warm" type='button' onclick="xadmin.open('打印报价单', '<?php echo url('printing',array('orderid'=>$orderid)); ?>', 1200, 600)">
							     <i class="layui-icon"> </i>打印报价单（网页版打印）
							 </button>
                            
                            
<!--                             <button class="layui-btn layui-btn-warm" type='button' onclick="xadmin.open('打印报价单', '<?php echo url('testPrinting',array('orderid'=>$orderid)); ?>', 1200, 600)">
                                <i class="layui-icon"></i>打印测试2
                            </button>-->
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">

                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">产品信息</blockquote>                       
                            <button class="layui-btn" type='button' id="product-reload" style=" position: relative;left: 90%;top:-40px;">
                                <i class="layui-icon"></i>刷新报价
                            </button>
                        </div>
                        <style type="text/css">
                          .layui-card-body > div{ margin-top: 0px; }
                        </style>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="product-table" lay-filter="product-table" style="margin: 0px;">

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
                                        <th>操作</th>
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
                                        <td class="td-manage">
                                            <a title="查看" onclick="open_group('编辑产品', '<?php echo url('order/addGroup',array('id'=>$v['og_id'],'order_id'=>$v['order_id'])); ?>')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                             <a title='复制' onclick='gcopy(<?php echo $v['og_id']; ?>)' href='javascript:;'>
                                                <i class='icon iconfont'>&#xe6b9;</i>
                                            </a>
                                            <a title="删除" onclick="group_del(this, <?php echo $v['og_id']; ?>,<?php echo $v['total_price']; ?>)" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody> 
                                
                            </table>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">原材料信息</blockquote> 
                             <button class="layui-btn" type='button' id="material-reload" style=" position: relative;left: 90%;top:-40px;">
                                <i class="layui-icon"></i>刷新报价
                            </button>
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="material-table" lay-filter="material-table" style="margin: 0px;">

                            </table>
                        </div>


                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">定制类产品</blockquote>
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" style="margin: 0px;" lay-filter="diy">
                                <thead>
                                    <tr>
																				<th lay-data="{field:'position'}">安装位置</th>
                                        <th lay-data="{field:'material'}">材质-花类</th>
                                        <th lay-data="{field:'color_name'}">铝型/花件颜色</th>
                                        <th lay-data="{field:'all_width'}">总宽</th>
                                        <th lay-data="{field:'all_height'}">总高</th>
                                        <th lay-data="{field:'count'}">数量</th>
                                        <th lay-data="{field:'product_area'}">产品面积</th>
                                        <th lay-data="{field:'area'}">报价面积</th>
                                        <th lay-data="{field:'price'}">单价</th>
                                        <th lay-data="{field:'all_price'}">总价</th>
                                        <th lay-data="{field:'diy_pic'}">产品图片</th>
                                        <th lay-data="{field:'c'}" width="12%">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($diy) || $diy instanceof \think\Collection || $diy instanceof \think\Paginator): $i = 0; $__LIST__ = $diy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <tr>
																		<td><?php echo $v['position']; ?></td>
                                    <td><?php echo $v['material']; ?></td>
                                    <td><?php echo $v['color_name']; ?></td>
                                    <td><?php echo $v['all_width']; ?></td>
                                    <td><?php echo $v['all_height']; ?></td>
                                    <td><?php echo $v['count']; ?></td>
                                    <td><?php echo $v['product_area']; ?></td>
                                    <td><?php echo $v['area']; ?></td>
                                    <td><?php echo $v['price']; ?></td>
                                    <td><?php echo $v['all_price']; ?></td>
                                    <td><img src="/upload/<?php echo $v['diy_pic']; ?>"></td>
                                    <td class="td-manage">
                                        <a title="查看" onclick="xadmin.open('编辑', '<?php echo url('diyedit',array('id'=>$v['op_id'])); ?>',1200, 600)" href="javascript:;">
                                            <i class="layui-icon">&#xe63c;</i></a>
                                        <a title="删除" onclick="member_del(this, <?php echo $v['op_id']; ?>)" href="javascript:;">
                                            <i class="layui-icon">&#xe640;</i></a>
                                    </td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>

                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
<script type="text/html" id="tpmPic">
  <div><img src='/upload/{{d.structure}}' height='60'/></div>
</script>
<script type="text/html" id="ptoolBar">
    {{#  if(d.order_type == 0){ }}
    <a title="编辑产品" onclick="xadmin.open('编辑产品', '/admin/order/editProduct/id/{{d.op_id}}.html',1200, 600)" href="javascript:;">
        <i class="layui-icon">&#xe63c;</i>
    </a>
    {{#  } else if(d.order_type == 1) { }}
    <a title="编辑手工单" onclick="xadmin.open('编辑手工单', '/admin/order/editHandMade/id/{{d.op_id}}.html',1200, 600)" href="javascript:;">
        <i class="layui-icon">&#xe63c;</i>
    </a>
    {{#  } }}
    <a title='复制' onclick='pcopy({{d.op_id}},{{d.order_id}},this)' href='javascript:;'>
            <i class='icon iconfont'>&#xe6b9;</i>
        </a>
    <a title="删除" onclick="member_del(this, {{d.op_id}})" href="javascript:;">
        <i class="layui-icon">&#xe640;</i>
    </a>
</script>
<script type="text/html" id="mtoolBar">
  <a title="查看" onclick="xadmin.open('编辑产品', '/admin/order/editmaterial/id/{{d.om_id}}.html',1200, 600)" href="javascript:;">
        <i class="layui-icon">&#xe63c;</i></a>
        <a title='复制' onclick='mcopy({{d.om_id}})' href='javascript:;'>
            <i class='icon iconfont'>&#xe6b9;</i>
        </a>
    <a title="删除" onclick="material_del(this, {{d.om_id}},{{d.all_price}})" href="javascript:;">
        <i class="layui-icon">&#xe640;</i></a>
</script>
<script>
    layui.use(['laydate', 'form','table'],
            function () {
                var laydate = layui.laydate;
                var form = layui.form;
                var $ = layui.jquery;
                var table = layui.table;

                //转换静态表格
                table.init('diy', {
                    limit:150
                });

                //填写定金后刷新余款
                $('#have_pay').change(function () {
                    var no_pay = $('#total_price').text()-$(this).val();
                    $('#no_pay').text(no_pay);
                })

                //刷新第三方数据库
                $('#refresh-third').click(function () {
                    layer.load();
                    var id = "<?php echo $res['id']; ?>";
                    $.post("<?php echo url('refreshThird'); ?>",{id:id},function (obj) {
                        layer.closeAll('loading');
                        if(obj.code == 0){
                            layer.msg('刷新成功',{icon:1},function () {
                                layer.closeAll('loading');
                                location.reload();
                            });
                            return;
                        }
                        layer.msg(obj.msg,{icon:2});
                    },'json');
                });

                //执行一个laydate实例
                laydate.render({
                    elem: '#end_date', //指定元素
                    done:function(value, date, endDate){
                        $.ajax({
                            url:"<?php echo url('refreshTime2'); ?>",
                            data: {date:value},
                            type: 'POST',
                            dataType: 'json',
                            success:function(res){
                                $('#make_date').val(res.data);
                            }
                        })
                    }
                });
                
                //调用winform里的打印方法
                $(function(){
                    var number = "<?php echo $res['number']; ?>";
                    var is_print = "<?php echo $res['is_printing']; ?>";
                    $("#winform-print").click(function () {
                           var a = getuserName.getName(number);
//                           $.post("/api/printPrice/index", {number:number}, 
//                            function(obj){ 
//                                console.log(obj);
//                            },'json');                  
                    });
               })
                
                var table = layui.table;
                var order_id = $('#order_id').val();
                //产品表格
                table.render({
                    elem: '#product-table'
                    ,url:'<?php echo url('productTable'); ?>?order_id='+order_id
                    ,cols: [[
                      {type:'numbers',title:'编号',width:50}
                      ,{field:'position',title:'安装位置',edit:'text',width:100,minWidth:110}
                      ,{field:'name',title:'名称',width:100}
                      ,{field:'material',title:'材质',minWidth:217}
                      ,{field:'flower_type',title:'型号',maxwidth:87}
                      ,{field:'color_name',title:'铝材/花件颜色',width:110}
                      ,{field:'㎡',title:'单位',templet:"<div>㎡</div>"}
                      ,{field:'all_width',title:'宽',edit:'text',width:70,minWidth:70}
                      ,{field:'all_height',title:'高',edit:'text',width:70,minWidth:70}
                      ,{field:'count',title:'个数',edit:'text'}
                      ,{field:'area',title:'报价面积',width:79}
                      ,{field:'product_area',title:'产品面积',width:79}
                      ,{field:'price',title:'单价',edit:'text',width:70,minWidth:70}
                      ,{field:'rebate',title:'折扣',width:70,minWidth:70,edit:'text'}
                       ,{field:'rebate_price',title:'折后价',edit:'text',width:70,minWidth:70}
                        ,{field:'all_price',title:'总金额',width:70,minWidth:70}
//                         ,{field:'structure',title:'结构图',templet:"#tpmPic",minWidth:90}
                        ,{field:'om_id',title:'操作',toolbar:'#ptoolBar',minWidth:80}
                    ]]
                  });   

               
                //原材料表格
                table.render({
                    elem: '#material-table'
                    ,url:'<?php echo url('materialTable'); ?>?order_id='+order_id
                    ,cellMinWidth: 80
                    ,cols: [[
                      {type:'numbers',title:'编号',width:50}
											,{field:'name',title:'安装位置',width:100}
                      ,{field:'type',title:'材质/型号',minWidth:100}
                      ,{field:'color',title:'铝材/花件颜色',width:107}
                      ,{field:'unit',title:'单位'}
                      ,{field:'width',title:'宽',width:107}
                      ,{field:'height',title:'高',width:107}
                      ,{field:'count',title:'个数'}
                      ,{field:'area',title:'面积'}
                      ,{field:'price',title:'单价',width:107}
                      ,{field:'rebate',title:'折扣',width:107,}
                       ,{field:'rebate_price',title:'折后价',width:107}
                        ,{field:'all_price',title:'总金额',width:107}
                        ,{field:'om_id',title:'操作',toolbar:'#mtoolBar',minWidth:80}
                    ]]
                  });
                  
                //监听产品单元格编辑
                table.on('edit(product-table)', function(obj){
                  
                  //var load_index = layer.load(2);  //提交后，等待返回保存结束；2025/1/24 mark
                  
                  var value = obj.value //得到修改后的值
                  ,data = obj.data //得到所在行所有键值
                  ,field = obj.field; //得到字段
                   $.post("<?php echo url('editProductPrice'); ?>", {data:data,field:field,value:value,order_id:<?php echo $orderid; ?>,total_price:$('#total_price').text()}, 
                   function(obj){                         
                       //layer.close(load_index);   //提交后，等待返回保存结束；2025/1/24 mark
                       if(obj.code == 0){
//                           layer.msg(obj.msg, {icon: 1,time:1000});
//                           $('#total_price').text(obj.data);
//                           table.reload('product-table');  //重载数据表格
                           return;
                       }
                       layer.msg(obj.msg, {icon: 2});
                   },'json');  
                  
                });  
            
                //点击按钮重载产品表格
                $('#product-reload').click(function(){
                   table.reload('product-table');  //重载数据表格
                   //JS更新订单总价，数量等
                   $.post("<?php echo url('findTotal'); ?>", {order_id:order_id},function(obj){  
                        $('#total_price').text(obj.data.total_price);  
                        $('#total_area').text(obj.data.area); 
                        $('#total_count').text(obj.data.count); 
                   },'json'); 
                })
                
                //点击按钮重载原材料表格
                $('#material-reload').click(function(){
                    table.reload('material-table');  //重载数据表格
                    //JS更新订单总价，数量等
                    $.post("<?php echo url('findTotal'); ?>", {order_id:order_id},function(obj){  
                        $('#total_price').text(obj.data.total_price);  
                        $('#total_area').text(obj.data.area); 
                        $('#total_count').text(obj.data.count); 
                   },'json'); 
                })
                
                //监听原材料单元格价格编辑
                table.on('edit(material-table)', function(obj){
                  var value = obj.value //得到修改后的值
                  ,data = obj.data //得到所在行所有键值
                  ,field = obj.field; //得到字段
                  $(obj.tr).select();
                   $.post("<?php echo url('editMaterialPrice'); ?>", {data:data,field:field,value:value,order_id:<?php echo $orderid; ?>,total_price:$('#total_price').text()}, 
                   function(obj){                         
                       if(obj.code == 0){
//                           layer.msg(obj.msg, {icon: 1,time:1000});
//                           $('#total_price').text(obj.data);
//                           table.reload('material-table');  //重载数据表格
                           return;
                       }
                       layer.msg(obj.msg, {icon: 2});
                   },'json');   
                  
                });
                    
                //监听提交表单
                form.on('submit(edit)',
                    function (data) {                            
                        ajaxform("<?php echo url('edit'); ?>", data.field,1);
                        return false;
                });
                //手动入库
				form.on('submit(editStatus)',
					function (data) {                            
						ajaxform("<?php echo url('editStatus'); ?>", data.field,1);
						return false;
				});
                        
                $(document).on('keydown', '.layui-input',
                function(event) {
                    var td = $(this).parent('td'),    
                    tr = td.parent('tr'),
                    trs = tr.parent().parent().find('tr'),
                    tr_index = tr.index(),
                    td_index = td.index(),
                    td_last_index = tr.find('[data-edit="text"]:last').index(),
                    td_first_index = tr.find('[data-edit="text"]:first').index();

                    switch (event.keyCode) {
                    case 39:
                        td.nextAll('[data-edit="text"]:first').click();
                        if(td_index == td_last_index){
                            tr.next().find('td').eq(td_first_index).click();
                            if(tr_index == trs.length -1)
                                trs.eq(0).find('td').eq(td_first_index).click();
                        }
                        setTimeout(function(){$('.layui-table-edit').select()},0);
                        break;  
                    case 37:
                        td.prevAll('[data-edit="text"]:first').click();
                        setTimeout(function(){$('.layui-table-edit').select()},0);
                        break;
                    case 38:
                        tr.prev().find('td').eq(td_index).click();
                        setTimeout(function(){$('.layui-table-edit').select()},0);
                        break;
                    case 40:
                        tr.next().find('td').eq(td_index).click();
                        setTimeout(function(){$('.layui-table-edit').select()},0);
                        break;
                    }
                })
                $(function(){
                    
                    var height=sessionStorage.getItem('height');
                    if(height!=null){
                        setTimeout(function(){ $(document).scrollTop(height)},800)
                        
                    }
                    sessionStorage.removeItem('height');
                    //刷新时间
                    $('#refresh').click(function(){
                        var order_id = <?php echo $orderid; ?>;
                        ajaxform("<?php echo url('refreshTime'); ?>",{order_id:order_id},1);
                    })
                })
            });
        function open_group(title, url){
            var index = layer.open({
                type: 2,
                area: [ '1200px',  '600px'],
                fix: false, //不固定
                maxmin: true,
                shadeClose: false,
                shade: 0.4,
                title: title,
                content: url,
                cancel: function(index, layero){ 
                    layer.close(index);
                    window.location.reload(); //刷新父页面
                    return false; 
                }
            });
        }   
        //删除组合单
        function group_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                var order_id = <?php echo $orderid; ?>;
                ajaxform("<?php echo url('delGroup'); ?>",{id:id,order_id:order_id},1);
            });
        } 
    
        /*删除产品*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                var order_id = <?php echo $orderid; ?>;
                ajaxform("<?php echo url('delProduct'); ?>",{id:id,order_id:order_id},1);
            });
        }
        /*删除原材料*/
        function material_del(obj, id,price) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                var order_id = <?php echo $orderid; ?>;
                ajaxform("<?php echo url('delMaterial'); ?>",{id:id,price:price,order_id:order_id},1);
            });
        }
        //复制原材料
        function mcopy(id){
            layer.confirm('确认要复制吗？',{ 
                btn:['确认','取消'],
                success:function(){
                    this.enterEsc = function (event) {
                        if (event.keyCode === 13) {
                            $(".layui-layer-btn0").click();
                            return false; //阻止系统默认回车事件
                        }
                    };
                    $(document).on('keydown', this.enterEsc); //监听键盘事件，关闭层
                },
                end:function(){
                    $(document).off('keydown',this.enterEsc); //解除键盘关闭事件
                }
            },function(index){
                var order_id = $('#order_id').val();
               ajaxform("<?php echo url('mcopy'); ?>",{id:id,order_id:order_id},1);
            });
        }
        //复制组合单
        function gcopy(id){
             layer.confirm('确认要复制吗？',{ 
                btn:['确认','取消'],
                success:function(){
                    this.enterEsc = function (event) {
                        if (event.keyCode === 13) {
                            $(".layui-layer-btn0").click();
                            return false; //阻止系统默认回车事件
                        }
                    };
                    $(document).on('keydown', this.enterEsc); //监听键盘事件，关闭层
                },
                end:function(){
                    $(document).off('keydown',this.enterEsc); //解除键盘关闭事件
                }
            },function(index){
               ajaxform("<?php echo url('gcopy'); ?>",{id:id},1);
            });
        }
        //复制产品
        function pcopy(id,order_id,obj){
            var pwidth = $(obj).offset().top;
            layer.confirm('确认要复制吗？',{ 
                btn:['确认','取消'],
                success:function(){
                    this.enterEsc = function (event) {
                        if (event.keyCode === 13) {
                            $(".layui-layer-btn0").click();
                            return false; //阻止系统默认回车事件
                        }
                    };
                    $(document).on('keydown', this.enterEsc); //监听键盘事件，关闭层
                },
                end:function(){
                    $(document).off('keydown',this.enterEsc); //解除键盘关闭事件
                }
            },function(index){
                ajaxform("<?php echo url('pcopy'); ?>",{id:id,order_id:order_id},1);
                sessionStorage.setItem("height", pwidth);
                 
            });
        }
</script>

</html>