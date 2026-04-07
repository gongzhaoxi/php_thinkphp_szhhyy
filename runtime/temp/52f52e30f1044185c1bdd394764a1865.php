<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:107:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/edit_material.html";i:1684396573;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .material-div{
        border: 1px solid #fafafa;height:50px;background-color: #fff;position: absolute;
        width: 100%;min-height: 100px;left: 0; text-align: center;z-index: 9999;display: none;
    }
</style>   
<body>
     <form class="layui-form layui-col-space5">
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <table class="layui-table layui-form">
                    <thead>
                        <tr>                                        
                            <th>安装位置</th>
                            <th>材质/型号</th>
                            <th>铝材/花件颜色</th>
                            <th>单位</th>
                            <th>宽</th>
                            <th>高</th>
                            <th>个数</th>
                            <th>报价面积</th>
                            <th>产品面积</th>
                            <th>单价</th>
                            <th>折扣</th>
                            <th>折后价</th>
                            <th>总金额</th>
                            <!--<th>操作</th>-->
                        </tr>
                    </thead>
                    <tbody id="table">
                        <tr id="clone">
                            <td><input name="name" type="text" class="layui-input" value="<?php echo $info['name']; ?>"/></td>
                            <td>
                                <input name="type" type="text" lay-verify="required" class="layui-input material" autocomplete="off" value="<?php echo $info['type']; ?>"/>
                                <div class="material-div" id="material-div"></div>
                            </td>
                            <td><input name="color" type="text" class="layui-input type" value="<?php echo $info['color']; ?>"/>
                                <div class="material-div" id="material-div"></div>
                            </td>
                            <td class="unit"><input name="unit" type="text" lay-verify="required" class="layui-input" value="<?php echo $info['unit']; ?>"/></td>
                            <td class="width-td"><input name="width" type="text" lay-verify="required" class="layui-input width all" value="<?php echo $info['width']; ?>"/></td>
                            <td class="height-td"><input name="height" type="text" lay-verify="required" class="layui-input height all" value="<?php echo $info['height']; ?>"/></td>
                            <td class="count-td"><input name="count" type="text" lay-verify="required" class="layui-input count all" value="<?php echo $info['count']; ?>"/></td>
                            <td class="area-td"><input name="area" type="text" lay-verify="required" class="layui-input area all" value="<?php echo $info['area']; ?>"/></td>
                            <td class="product-area-td"><input name="product_area" type="text" lay-verify="required" class="layui-input product-area" value="<?php echo $info['product_area']; ?>"/></td>
                            <td class="price"><input name="price" type="text" lay-verify="required" class="layui-input price all" value="<?php echo $info['price']; ?>"/></td>
                            <td class="rebate-td"><input name="rebate" lay-verify="rebate" type="text" class="layui-input rebate all" value="<?php echo $info['rebate']; ?>"/></td>
                            <td class="rebate-price-td"><input name="rebate_price" lay-verify="required" type="text" class="layui-input rebate_price all" value="<?php echo $info['rebate_price']; ?>"/></td>
                            <td class="all-price-td"><input name="all_price" lay-verify="required" type="text" class="layui-input all_price" value="<?php echo $info['all_price']; ?>"/></td>
                            <!--<td><button type="button" class="layui-btn layui-btn-radius layui-btn-danger" onclick="del(this)">删除</button></td>-->
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="layui-col-md12">
                <input name="om_id" type="hidden" value="<?php echo $om_id; ?>"/>
                <input name="order_id" type="hidden" value="<?php echo $info['order_id']; ?>"/>
                <button class="layui-btn" lay-filter="add" lay-submit="" style="margin-left: 40%;">立即保存</button>              
            </div>
        </div>
    </div>
     </form>
</body>
<script>
    layui.use(['laydate', 'form'],
            function () {
                var form = layui.form;
                
                //监听提交表单
                form.on('submit(add)',
                        function (data) {      
                            
                            ajaxform("<?php echo url('editMaterial'); ?>", data.field);
                            return false;
                        });

            });
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
    $(function(){
        $('#add').click(function(){
            var html = $('#clone').clone();
            $('#clone').after(html);
        })
        
                //计算面积-数量
        $('#table').on('change','.count',function(){
            var width = $(this).parent().prevAll('.width-td').find('input').val();
            var height = $(this).parent().prevAll('.height-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number($(this).val());
            var rebate = $(this).parent().nextAll('.rebate-td').find('input').val();
            var price = $(this).parent().nextAll('.price').find('input').val();
            var area_d = $(this).parent().nextAll('.area-td').find('input'); //产品面积dom
            var area_p = $(this).parent().nextAll('.product-area-td').find('input'); //报价面积dom
            var _rebate_price = $(this).parent().nextAll('.rebate-price-td').find('input'); //折后价dom
            
            area_d.val(area.toFixed(2));
            area_p.val(area.toFixed(2));
            var rebate_price = (Number(price)*Number(rebate)).toFixed(2);
            _rebate_price.val(rebate_price);
        })
        
         //计算面积-宽
        $('#table').on('change','.width',function(){
            var width = $(this).val();
            var height = $(this).parent().nextAll('.height-td').find('input').val();
            var count = $(this).parent().nextAll('.count-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number(count);
            var rebate = $(this).parent().nextAll('.rebate-td').find('input').val();
            var price = $(this).parent().nextAll('.price').find('input').val();
            var area_d = $(this).parent().nextAll('.area-td').find('input'); //产品面积dom
            var area_p = $(this).parent().nextAll('.product-area-td').find('input'); //报价面积dom
            var _rebate_price = $(this).parent().nextAll('.rebate-price-td').find('input'); //折后价dom
            area_d.val(area.toFixed(2));
            area_p.val(area.toFixed(2));
            var rebate_price = (Number(price)*Number(rebate)).toFixed(2);
            _rebate_price.val(rebate_price);
        })
        
        //计算面积-高
        $('#table').on('change','.height',function(){
            var width = $(this).parent().prevAll('.width-td').find('input').val();
            var height = $(this).val();
            var count = $(this).parent().nextAll('.count-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number(count);
            
            var rebate = $(this).parent().nextAll('.rebate-td').find('input').val();
            var price = $(this).parent().nextAll('.price').find('input').val();
            var area_d = $(this).parent().nextAll('.area-td').find('input'); //产品面积dom
            var area_p = $(this).parent().nextAll('.product-area-td').find('input'); //报价面积dom
            var _rebate_price = $(this).parent().nextAll('.rebate-price-td').find('input'); //折后价dom
            area_d.val(area.toFixed(2));
            area_p.val(area.toFixed(2));
            
             var rebate_price = (Number(price)*Number(rebate)).toFixed(2);
            _rebate_price.val(rebate_price);
        })
				
				//计算折后价
				$('#table').on('change','.price',function(){
						var tr  = $(this).closest('tr');
						var price = Number(tr.find('.price input').val()); //单价					
						var rebate = Number(tr.find('.rebate').val()); //折扣
						var width = Number(tr.find('.width').val());
						var height = Number(tr.find('.height').val());
						var area = Number(tr.find('.area').val());
						var count = Number(tr.find('.count').val());
						var rebate_price =  Number(rebate)*price; //折后价	
						
						var total = 0;
						if(width ==0 || height == 0){
						    //如果宽和高都为空
						    if(width == 0 && height == 0){
						        total = rebate_price*count; 
						    }else{
						        //宽和高只有一个为空
						        var woh = width==0?height:width;
						        total = woh/1000*rebate_price*count;
						    }
						}else{
						    total = area*rebate_price
						}
				
						tr.find('.rebate_price').val((rebate_price).toFixed(2));
						tr.find('.all_price').val((total).toFixed(2));
				})	
        
        //计算折后价
        $('#table').on('change','.rebate',function(){
            var price = $(this).parent().prev().find('input').val(); //单价
            var rebate_price = $(this).parent().nextAll('.rebate-price-td').find('input'); //折后价dom
            var rprice = Number($(this).val())*price; //折后价
             rebate_price.val((rprice).toFixed(2));
            
        })
        
        //计算总价
        $('#table').on('change','.all',function(){
            var width = Number($(this).parents('tr').find('.width').val());
            var height = Number($(this).parents('tr').find('.height').val());
            var rebate_price = Number($(this).parents('tr').find('.rebate_price').val());
            var count = Number($(this).parents('tr').find('.count').val());
            var area = Number($(this).parents('tr').find('.area').val());
            var _total = $(this).parents('tr').find('.all_price');
            var total = 0;
            if(width ==0 || height == 0){
                //如果宽和高都为空
                if(width == 0 && height == 0){
                    total = rebate_price*count; 
                }else{
                    //宽和高只有一个为空
                    var woh = width==0?height:width;
                    total = woh/1000*rebate_price*count;
                }
            }else{
                total = area*rebate_price
            }
            _total.val(total.toFixed(2));
        })
        
        //材料异步下拉
        $('#table').on('keyup','.material',function(){            
            var z = $(this);
            var material = $(this).next();
            material.css('display','block');
            $.post("<?php echo url('findBom'); ?>", {name:z.val()}, function(obj){ 
                var data= obj.data;
                if(data.lenth<=0){
                    return;
                }
                var html = '';
                for(i=0;i<data.length;i++){
                    html +="<div class='select2' unit='"+data[i]['unit']+"' price="+data[i]['price']+">"+data[i]['name']+"</div>";
                }                       
                material.html(html);
                var _input = $(z).parents('tr').find('input');
//                $(_input).each(function(){
//                    $(this).val('');
//                })
                //点击门店后赋予数据
                $('.select2').click(function(){
                    z.val($(this).text());
                    z.parent().nextAll('.unit').find('input').val($(this).attr('unit'));
                    z.parent().nextAll('.price').find('input').val($(this).attr('price'));
                    material.css('display','none');
                })

             },'json');
        })
        
          //颜色异步下拉
        $('#table').on('keyup','.type',function(){            
            var z = $(this);
            var material = $(this).next();
            material.css('display','block');
            $.post("<?php echo url('findColor'); ?>", {name:z.val()}, function(obj){ 
                var data= obj.data;
                if(data.lenth<=0){
                    return;
                }
                var html = '';
                for(i=0;i<data.length;i++){
                    html +="<div class='select2'>"+data[i]['name']+"</div>";
                }                       
                material.html(html);
                //点击门店后赋予数据
                $('.select2').click(function(){
                    z.val($(this).text());
                    material.css('display','none');
                })

             },'json');
        })
        
           //点击空白处隐藏搜索框
            $(document).click(function(event){
                var _con = $('.material-div');  // 设置目标区域
                if(!_con.is(event.target) && _con.has(event.target).length === 0){ 
                   $('.material-div').css('display','none');
                }
           });
    })
    
    //删除
    function del(obj){
        $(obj).parents('tr').remove();
    }
</script>
</html>
