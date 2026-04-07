<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:110:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/allorder/add_delivery.html";i:1745284104;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .alltable{height:232px;overflow-y: scroll;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form">

                <div class="layui-col-xs12">
                    <div class="alltable">
                    <table class="layui-table layui-form" lay-skin="line">
                        <thead>
                            <tr>                                       
                                <th>选定</th>
                                <th>订单号</th>
                                <th>经销商</th>
                                <th>送货地址</th>
                                <th>数量</th>
                        </thead>
                        <tbody id="table-list">
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><input name="check" lay-skin="primary" type="checkbox" value='<?php echo $v['id']; ?>'/></td>
                                <td><?php echo $v['number']; ?><input name="no_pay" type="hidden" value="<?php echo round($v['total_price']-$v['have_pay'],2) ?>"/></td>
                                <td><?php echo $v['dealer']; ?></td>
                                <td><?php echo $v['send_address']; ?></td>
                                <td><?php echo $v['count']; ?></td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    </div>
                    <div class="layui-form-item" style="padding-top: 10px;">
                        <blockquote class="layui-elem-quote">
                            <input type="checkbox" name="sex" value="全选" title="全选" lay-filter="checkall">
                            <a href="javascript:;" class="layui-btn" id="add" style="margin: 6px 16px 0 0;"><i class='icon iconfont'>&#xe6b9;</i>添加</a>
                            <input type="radio" name="is_send"  value="0" title="自送" checked lay-filter="send">
                            <input type="radio" name="is_send"  value="1" title="物流" lay-filter="send">
                            <input type="radio" name="is_send"  value="2" title="自提" lay-filter="send">
                            <input type="radio" name="is_send"  value="3" title="请车" lay-filter="send">
                            <input type="radio" name="is_send"  value="4" title="快递" lay-filter="send">
                            <div class="layui-inline" style="float: right">
                            <input type="text" id="send_date" name="send_date" placeholder="送货时间" class="layui-input" lay-verify="required" value="<?php echo $send_date; ?>" style="display:inline;width: 110px;">
                            <input type="text" id="search" name="search" placeholder="输入订单号/经销商名称" class="layui-input" style="display:inline;width: 220px;">
                            <button type="button" class="layui-btn" id="search-btn">搜索</button>
                            </div>
                        </blockquote>    
                    </div> 
                    <div class="select-table">
                        <table class="layui-table layui-form" lay-skin="line">
                        <thead>
                            <tr>                                       
                                <th style="width:6%">序号</th>
                                <th>订单号</th>
                                <th>经销商</th>
                                <th>送货地址</th>
                                <th>数量</th>
                                <th>预计到达时间</th>
                                <th>应收款</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="have_body">

                        </tbody>
                        </table>
                    </div>
                    <div id="driver" style="width: 30%;margin: 0 auto;">
                        <div class="layui-input-inline">
                            <input type="text" name="driver_name" id="driver_name" placeholder="请填写司机姓名" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="driver_phone" id="driver_phone" placeholder="请填写司机电话" class="layui-input">
                        </div>
                    </div>
                    <div id="logistics" style="display: none;width: 30%;margin: 0 auto;">
                        <div class="layui-input-inline">
                            <input type="text" name="logistics_name" id="logistics_name" placeholder="请填写物流公司名称" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="logistics_numbers" id="logistics_numbers" placeholder="请填写物流单号" class="layui-input">
                        </div>
                    </div>
                </div>  

                <div class="layui-form-item">
                    <label for="L_repass" class="layui-form-label"></label>
                    <button class="layui-btn" type="button" lay-filter="add" lay-submit="" >保存数据</button>
                </div>
            </form>
        </div> 
    </div>
    <script>layui.use(['laydate', 'form', 'layer'],
                function () {
                    var laydate = layui.laydate;
                    var form = layui.form;
                    var layer = layui.layer;
                    $ = layui.jquery;

                    laydate.render({
                        elem:"#send_date",
                        type:'date'
                    })
                    //异步搜索
                    $('#search-btn').click(function () {
                        $.get("<?php echo url('addSearch'); ?>",{search:$('#search').val()},function (obj) {
                            var html = "";
                            $(obj.data).each(function (k,v) {
                                html += '<tr>\n' +
                                    '<td><input name="check" lay-skin="primary" type="checkbox" value="'+v['id']+'"></td>\n' +
                                    '<td>'+v['number']+'<input name="no_pay" type="hidden" value="'+(v['total_price']-v['have_pay'])+'"/></td>\n' +
                                    '<td>'+v['dealer']+'</td>\n' +
                                    '<td>'+v['send_address']+'</td>\n' +
                                    '<td>'+v['count']+'</td>\n' +
                                    '</tr>';
                            });
                            $('#table-list').html(html);
                            form.render();
                        },'json');
                    });

                    $(function(){
                        //添加选中的html
                        $('#add').click(function(){
                            var html = '';
                            var row = Number($('#have_body').find('tr').length)+1;
                            $("input[name='check']:checked").each(function(){
                                //已经添加过的订单id
                                var ids = [];
                                $('.ids').each(function () {
                                    ids.push($(this).val());
                                });
                                //未添加的订单 才能加
                                if($.inArray($(this).val(),ids) == '-1'){
                                    var id = $(this).val();
                                    var number = $(this).parent().next().text();
                                    var dealer = $(this).parent().next().next().text();
                                    var address = $(this).parent().next().next().next().text();
                                    var count = $(this).parent().next().next().next().next().text();
                                    var no_pay = $(this).parent().next().find('input').val();
                                    html += "<tr><td><input name='sort[]' type='text' value='"+row+"' class='layui-input sort'></td>\n\
                                          <td>"+number+"</td><td>"+dealer+"</td><td>"+address+"</td><td>"+count+"</td>\n\
                                         <td><input type='text' name='arrive_time[]' autocomplete='off' class='layui-input arrive'></td>\n\
                                        <td>"+no_pay+"</td><td><button class='layui-btn-danger layui-btn layui-btn-xs' type='button' onclick='order_del(this)'>\n\
                                        <i class='layui-icon'>&#xe640;</i>删除</button></td>\n\
                                        <input name='id[]' type='hidden' class='ids' value="+id+"><input name='order_number[]' type='hidden' value="+number+">\n\
                                        <input name='count[]' type='hidden' value="+count+"></tr>";
                                    row++;
                                }


                            })
                            if(html!=''){
                                $('#have_body').append(html);
                                //多个input绑定laydate
                                lay('.arrive').each(function(){
                                    laydate.render({
                                       elem: this,
                                       type:'time'
                                      ,format:'HH:mm:ss'
                                      ,trigger: 'click',
                                    });
                                  });
                            }
                        })
                        

                    })
                    //监听单选
                    form.on('radio(send)',function(data){
                        if(data.value == 0 || data.value == 3){
                            $('#logistics').css('display','none');
                            $('#driver').css('display','block');
                        }else if(data.value == 1 || data.value == 4){
                            $('#logistics').css('display','block');
                            $('#driver').css('display','none');
                        }else{
                            $('#logistics').css('display','none');
                            $('#driver').css('display','none');
                        }
                    })
                    
                    // 监听全选
                    form.on('checkbox(checkall)', function (data) {

                        if (data.elem.checked) {
                            $('tbody input').prop('checked', true);
                        } else {
                            $('tbody input').prop('checked', false);
                        }
                        form.render('checkbox');
                    });
                    
                    //监听提交
                    form.on('submit(add)',
                    function(data) {
                        // if($('input[name=is_send]:checked').val() == 1 || $('input[name=is_send]:checked').val() == 4){
                        //     if($('#logistics_name').val() == '' || $('#logistics_number').val() == ''){
                        //         layer.msg('请填写物流公司和物流名称',{icon:2});
                        //         return;
                        //     }
                        // }
                        // if($('input[name=is_send]:checked').val() == 0 || $('input[name=is_send]:checked').val() == 3){
                        //     if($('#driver_name').val() == '' || $('#driver_phone').val() == ''){
                        //         layer.msg('请填写司机名称和电话',{icon:2});
                        //         return;
                        //     }
                        //     if(!(/^1[3456789]\d{9}$/.test($('#driver_phone').val()))){
                        //         layer.msg('手机号码不正确',{icon:2});
                        //         return;
                        //     }
                        // }
                        ajaxform("<?php echo url('addDelivery'); ?>",data.field);
                        return false;
                    });
                });
                
         /*删除*/
        function order_del(obj) {
            $(obj).parents("tr").remove();
        }

    </script>
    <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script>
        
    </script>

</body>

</html>