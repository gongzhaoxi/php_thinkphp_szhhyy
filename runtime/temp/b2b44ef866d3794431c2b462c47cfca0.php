<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/add_diy.html";i:1675733277;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-table img{max-width: 50px;max-height: 50px;}
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
                            <th>材质-花类</th>
                            <th>铝型/花件颜色</th>
                            <th>总宽</th>
                            <th>总高</th>
                            <th>数量</th>
                            <th>产品面积</th>
                            <th>报价面积</th>
                            <th>单价</th>
                            <th>总价</th>
                            <th>产品图片</th>
                            <th width="12%">操作</th>
                        </tr>
                    </thead>
                    <tbody id="table">
                        <tr class="clone">
														<td class="position-td"><input name="position[]" type="text"  class="layui-input" placeholder="安装位置"/></td>
                            <td class="name-td"><input name="name[]" type="text"  class="layui-input" placeholder="材质-花类"/></td>
                            <td class="color-td"><input name="color_name[]" type="text"  class="layui-input" placeholder="铝型/花件颜色"/></td>
                            <td class="width-td"><input name="width[]" type="text" lay-verify="required" class="layui-input width all" placeholder="单位:mm"/></td>
                            <td class="height-td"><input name="height[]" type="text" lay-verify="required" class="layui-input height all" placeholder="单位:mm"/></td>
                            <td class="count-td"><input name="count[]" type="number" lay-verify="required" class="layui-input count all" placeholder="数值"/></td>
                            <td class="product-area-td"><input name="product_area[]" type="text" lay-verify="required" class="layui-input product-area" placeholder="自动计算" readonly="true"/></td>
                            <td class="area-td"><input name="area[]" type="text" lay-verify="required" class="layui-input area all"/></td>
                            <td class="price-td"><input name="price[]" type="text" lay-verify="required" class="layui-input one-price"/></td>
                            <td class="all-price-td"><input name="all_price[]" lay-verify="required" type="text" class="layui-input all_price"/></td>
                            <td>
                                <button type="button" class="layui-btn layui-btn-radius layui-btn-normal upload-pic">点击上传</button>

                            </td>
                            <td>
                                <button type="button" class="layui-btn layui-btn-radius" onclick="copy(this)">复制</button>
                                <button type="button" class="layui-btn layui-btn-radius layui-btn-danger" onclick="del(this)">删除</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="float: right">
                    <button type="button" id="add" class="layui-btn layui-btn-radius layui-btn-warm">继续添加</button>
                     
                </div>
            </div>
            <div class="layui-col-md12">
                <input name="order_id" type="hidden" value="<?php echo $order_id; ?>"/>
                <button class="layui-btn" lay-filter="add" lay-submit="" style="margin-left: 40%;">立即保存</button>              
            </div>
        </div>
    </div>
     </form>
</body>
<script>
    layui.use(['laydate', 'form','upload'],
            function () {
                var form = layui.form;
                var upload = layui.upload;

                $('.upload-pic').each(function(){
                    var type = $(this).attr('type');
                    var code = $(this).attr('code');
                    var pic_obj = $(this);
                    var uploadInst = upload.render({
                        elem: this //绑定元素
                        , url: "<?php echo url('bom/upload'); ?>" //上传接口
                        , data: {type:type,code:code}
                        , size: 800000 //限制文件大小，单位 KB
                        , method: 'post'
                        , accept: 'file'
                        , exts: "jpg|jpeg|png"
                        , before:function(){ layer.load()}
                        , done: function (res) {
                            layer.closeAll('loading');
                            if(res.code==0){
                                var html = '<img src=/upload/'+res.data+'>';
                                var input = "<input name='diy_pic[]' type='hidden' value='"+res.data+"'>";
                                pic_obj.css('display','none');
                                pic_obj.after(html);
                                pic_obj.after(input);
                                return false;
                            }
                            layer.msg(res.msg,{icon:2})
                        }
                        , error: function () {
                            //请求异常回调
                        }
                    });
                })

                //监听提交表单
                form.on('submit(add)',
                        function (data) {                            
                            ajaxform("<?php echo url('addDiy'); ?>", data.field);
                            return false;
                        });

            });
</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script>
    $(function(){
        $('#add').click(function(){
            var html = " <tr class=\"clone\">\n" +
                "<td><input name=\"name[]\" type=\"text\"  class=\"layui-input\" placeholder=\"材质-花类\"/></td>\n" +
                "<td class=\"color-td\"><input name=\"color_name[]\" type=\"text\"  class=\"layui-input\" placeholder=\"铝型/花件颜色\"/></td>\n" +
                "<td class=\"width-td\"><input name=\"width[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input width all\" /></td>\n" +
                "<td class=\"height-td\"><input name=\"height[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input height all\" placeholder=\"单位:mm\"/></td>\n" +
                "<td class=\"count-td\"><input name=\"count[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input count all\" placeholder=\"单位:mm\"/></td>\n" +
                "<td class=\"product-area-td\"><input name=\"product_area[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input product-area\" placeholder=\"自动计算\" readonly='true'/></td>\n" +
                "<td class=\"area-td\"><input name=\"area[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input area all\"/></td>\n" +
                "<td class=\"price-td\"><input name=\"price[]\" type=\"text\" lay-verify=\"required\" class=\"layui-input one-price\"/></td>\n" +
                "<td class=\"all-price-td\"><input name=\"all_price[]\" lay-verify=\"required\" type=\"text\" class=\"layui-input all_price\"/></td>\n" +
                "<td>\n" +
                "<button type=\"button\" class=\"layui-btn layui-btn-radius layui-btn-normal upload-pic\">点击上传</button>\n" +
                "\n" +
                "</td>\n" +
                "<td>\n" +
                "<button type=\"button\" class=\"layui-btn layui-btn-radius\" onclick=\"copy(this)\">复制</button>\n" +
                "<button type=\"button\" class=\"layui-btn layui-btn-radius layui-btn-danger\" onclick=\"del(this)\">删除</button>\n" +
                "</td>\n" +
                "</tr>";
            $('#table').append(html);
        })

         //计算面积-宽
        $('#table').on('change','.width',function(){
            var width = $(this).val();
            var height = $(this).parent().nextAll('.height-td').find('input').val();
            var count = $(this).parent().nextAll('.count-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number(count);
            var area_d = $(this).parent().nextAll('.product-area-td').find('input'); //产品面积dom
            area_d.val(area.toFixed(2));
        })
        
        //计算面积-高
        $('#table').on('change','.height',function(){
            var width = $(this).parent().prevAll('.width-td').find('input').val();
            var height = $(this).val();
            var count = $(this).parent().nextAll('.count-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number(count);
            var area_d = $(this).parent().nextAll('.product-area-td').find('input'); //产品面积dom
            area_d.val(area.toFixed(2));

        })

        //计算面积-数量
        $('#table').on('change','.count',function(){
            var width = $(this).parent().prevAll('.width-td').find('input').val();
            var height = $(this).parent().prevAll('.height-td').find('input').val();
            var area = (Number(width)*Number(height))/1000000*Number($(this).val());
            var area_d = $(this).parent().nextAll('.product-area-td').find('input'); //产品面积dom
            area_d.val(area.toFixed(2));
        })

        //计算价格-报价面积
        $('#table').on('change','.area',function(){
            var area = $(this).val();
            var price = $(this).parent().nextAll('.price-td').find('input').val();
            var _all_price = $(this).parent().nextAll('.all-price-td').find('input');
            var all = (Number(area)*Number(price)).toFixed(2);
            _all_price.val(all);console.log(all);
        })

        //计算价格-单价
        $('#table').on('change','.one-price',function(){
            var area = $(this).parent().prevAll('.area-td').find('input').val();
            var price = $(this).val();
            var _all_price = $(this).parent().nextAll('.all-price-td').find('input');
            var all = (Number(area)*Number(price)).toFixed(2);
            _all_price.val(all);
        })

    })
    //复制
    function copy(obj){
        var html = $(obj).parents('.clone').clone();
        $(obj).parents('.clone').after(html);
    }
    //删除
    function del(obj){
        $(obj).parents('tr').remove();
    }
</script>
</html>
