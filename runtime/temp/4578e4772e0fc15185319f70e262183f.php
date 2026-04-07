<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:97:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/add.html";i:1710726586;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>

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
    .search-div{
        border: 1px solid #fafafa;height:50px;background-color: #fff;position: absolute;
        width: 55%;min-height: 100px;left: 22%;top:30px; text-align: center;z-index: 9999;display: none;
    }
</style>
<body>
    <div class="x-nav">
            <span class="layui-breadcrumb">
                <a href="javascript:history.back(-1)">返回</a>
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
                            </blockquote>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">订单编号</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="price_min" class="layui-input" readonly="true" placeholder="保存订单后自动生成">
                                </div>

                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">要求交货时间</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="end_time" id="end_date"  required  lay-verify="required" autocomplete="off" class="layui-input" value="<?php echo $etime; ?>">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 100%;margin-bottom: 15px;">
                                <label class="layui-form-label" >生产完成时间</label>
                                <div class="layui-input-inline" style="width:22%">
                                     <input type="text" name="make_time" id="make_date" class="layui-input" readonly="true" value="<?php echo $product_time; ?>"> 
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">经销商名称</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="dealer" id="dealer" required  lay-verify="required"  class="layui-input" autocomplete="off">
                                </div>
                                <div class="search-div" id="search-name"></div>
                            </div>
                            <div class="layui-inline" style="width: 40%;margin-bottom: 15px;">
                                <label class="layui-form-label">电话</label>
                                <div class="layui-input-inline" style="width: 55%">
                                    <input type="text" name="phone" id="phone" required  lay-verify="required"  class="layui-input" autocomplete="off">
                                </div>
                                <div class="search-div" id="search-phone"></div>
                            </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="address" id="address" required  lay-verify="required" class="layui-input" readonly="true">
                                </div>
                            </div>
                            <div class="layui-inline" style="width: 40%">
                                <label class="layui-form-label">送货地址</label>
                                <div class="layui-input-inline" style="width:55%">
                                    <input type="text" name="send_address" id="send_address" required lay-verify="required"  class="layui-input">
                                </div>
                            </div>
							<div class="layui-inline" style="width: 40%">
							    <label class="layui-form-label">楼盘位置</label>
							    <div class="layui-input-inline" style="width:55%">
							        <input type="text" name="building" id="building" class="layui-input">
							    </div>
							</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">订单类型</label>
                                <div class="layui-input-block" style="width: 50%">
                                    <input type="radio" name="type" value="1" title="常规" checked>
                                    <input type="radio" name="type" value="2" title="加急" >
                                    <input type="radio" name="type" value="3" title="样板" >
                                    <input type="radio" name="type" value="9" title="样板2" >
                                    <input type="radio" name="type" value="4" title="返修单" >
                                    <input type="radio" name="type" value="5" title="单剪网" >
                                    <input type="radio" name="type" value="6" title="单切料" >
                                    <input type="radio" name="type" value="7" title="工程" >
									<input type="radio" name="type" value="8" title="重做" >
                                </div>
                            </div>
							<div class="layui-inline">
							    <label class="layui-form-label">是否打印标签</label>
							    <div class="layui-input-inline">
							    <select name='print_label' id="print_label">
							        <option value="0">需要打印</option>
							        <option value="1">不需要打印</option>
							    </select>
							    </div>
							</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注</label>
                                <div class="layui-input-block" style="width: 63%;"> 
                                    <input type="text" name="note"  class="layui-input">
                                </div>
                            </div>
                            <table class="layui-table">
                                <tbody class='alldisplay'>
                                    <tr>
                                        <td>总金额:<span style='color: red'>0</span></td>

                                        <td>总面积:<span style='color: red'>0</span></td>

                                        <td>总数量:<span style='color: red'>0</span></td>

                                    </tr>
                                </tbody>
                            </table>
                            <div class="layui-form-item" style="padding-bottom: 20px;">
                                <input name="dealer_id" id="dealer_id" type="hidden"/>
                                <button class="layui-btn" lay-filter="addbasic" lay-submit="" style="margin:10px 0 0 15%">
                                   <i class="layui-icon"></i>保存当前订单
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</body>
<script>
    layui.use(['laydate', 'form'],
            function () {
                $ = layui.jquery;
                var form = layui.form;
                var laydate = layui.laydate;

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
                
                //监听提交表单
                form.on('submit(addbasic)',
                        function (data) {                            
                            ajaxform("<?php echo url('add'); ?>", data.field);
                            return false;
                        });
                        
                $(function(){
                    
                    
                    $('#dealer').keyup(function(){
                        var z = $(this);
                        var search = $('#search-name');
                        search.css('display','block');
                        $.post("<?php echo url('findBasic'); ?>", {name:z.val(),field:'name'}, function(obj){ 
                            var data= obj.data;
                            if(data.lenth<=0){
                                return;
                            }
                            var html = '';
                            for(i=0;i<data.length;i++){
                                html +="<div class='select2' id="+data[i]['id']+" contact="+data[i]['contact']+" address="+data[i]['address']+" print_label="+data[i]['print_label']+" back_contact="+data[i]['back_contact']+">"+data[i]['name']+"</div>";
                            }                       
                            search.html(html);
                            //点击门店后赋予数据
                            $('.select2').click(function(){
                                z.val($(this).text());
                                var back_contact = $(this).attr('back_contact');
                                if(back_contact){
                                     $('#phone').val($(this).attr('contact')+'/'+back_contact);
                                }else{
                                    $('#phone').val($(this).attr('contact'));
                                }
                                $('#address').val($(this).attr('address'));
                                $('#send_address').val($(this).attr('address'));
								$('#print_label').val($(this).attr('print_label'));
                                $('#dealer_id').val($(this).attr('id'));
                                search.css('display','none');
								layui. form.render('select');
                            })

                         },'json');
                    });
                    $('#phone').keyup(function(){
                        var z = $(this);
                        var search = $('#search-phone');
                        search.css('display','block');
                        $.post("<?php echo url('findBasic'); ?>", {name:z.val(),field:'contact'}, function(obj){ 
                            var data= obj.data;
                            if(data.lenth<=0){
                                return;
                            }
                            var html = '';
                            for(i=0;i<data.length;i++){
                                html +="<div class='select2'  id="+data[i]['id']+" contact="+data[i]['contact']+" address="+data[i]['address']+" print_label="+data[i]['print_label']+" contact="+data[i]['contact']+" back_contact="+data[i]['back_contact']+">"+data[i]['name']+"</div>";
                            }                       
                            search.html(html);
                            //点击门店后赋予数据
                            $('.select2').click(function(){
                                var back_contact = $(this).attr('back_contact');
                                if(back_contact){
                                    $('#phone').val($(this).attr('contact')+'/'+back_contact);
                                }else{
                                    z.val($(this).attr('contact'));
                                }
                                $('#dealer').val($(this).text());
                                $('#address').val($(this).attr('address'));
                                $('#send_address').val($(this).attr('address'));
								$('#print_label').val($(this).attr('print_label'));
                                $('#dealer_id').val($(this).attr('id'));
                                search.css('display','none');
								layui. form.render('select');
                            })

                         },'json');
                    })
                    //点击空白处隐藏搜索框
                    $(document).click(function(event){
                        var _con = $('.search-div');  // 设置目标区域
                        if(!_con.is(event.target) && _con.has(event.target).length === 0){ // Mark 1
                           $('.search-div').css('display','none');
                        }
                   });
                })

            });
            
            
</script>

</html>