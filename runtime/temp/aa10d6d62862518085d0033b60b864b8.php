<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/order/add_group.html";i:1675733278;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>

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
      .layui-card-body > div{ margin-top: 0px; }
    .alldisplay td{ text-align: center;}
    .layui-table-cell{ height: 60px;line-height: 60px;}
    .layui-table-edit{ margin-top: 15px}
    .layui-table td,.layui-table th{text-align: center;}
</style>
<body>

    <form class="layui-form layui-col-space5">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">

                <div class="layui-col-md12">
                    <div class="layui-card">
                        
                        <div class="layui-card-body " style="padding-bottom: 0px;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">报价信息</blockquote>                       
                            <button class="layui-btn" type='button' id="product-reload" style=" position: relative;left: 90%;top:-40px;">
                                <i class="layui-icon"></i>刷新报价
                            </button>
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="product-table" lay-filter="product-table" style="margin: 0px;">

                                </tbody> 
                            </table>
                            <div style="float: right;margin:10px 0;">
                                <button class="layui-btn" onclick="xadmin.open('添加产品', '<?php echo url('addGroupProduct',array('order_id'=>$orderid,'order_type'=>2,'og_id'=>$og_id)); ?>', 1100, 550)" type='button'>
                                    添加产品
                                </button>
                            </div>
                        </div>
                        <div class="layui-card-body " style="padding-bottom: 0px;clear: both;">
                            <blockquote class="layui-elem-quote" style="margin: 0px;background: #c3c3c3;font-size: 14px; color: #000;">算料信息</blockquote>                       
                            <button class="layui-btn" type='button' id="group-reload" style=" position: relative;left: 90%;top:-40px;">
                                <i class="layui-icon"></i>刷新报价
                            </button>
                        </div>
                        <div class="layui-card-body " style="padding-top: 0px;">
                            <table class="layui-table layui-form" id="group-table" lay-filter="group-table" style="margin: 0px;">

                            </table>
                            <div style="float: right;margin:10px 0;">
                                <button class="layui-btn" onclick="xadmin.open('添加产品', '<?php echo url('addGroupProduct',array('order_id'=>$orderid,'order_type'=>3,'og_id'=>$og_id)); ?>', 1100, 550)" type='button'>
                                    添加产品
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
<script type="text/html" id="ptoolBar">
    <a title="查看1" onclick="xadmin.open('编辑产品', '/admin/order/editGroupProduct.html?id={{d.op_id}}&order_id={{d.order_id}}')" href="javascript:;">
        <i class="layui-icon">&#xe63c;</i>
    </a>
    <a title='复制' onclick='gcopy({{d.op_id}},{{d.order_id}})' href='javascript:;'>
        <i class='icon iconfont'>&#xe6b9;</i>
    </a>
    <a title="删除" onclick="member_del(this, {{d.op_id}})" href="javascript:;">
        <i class="layui-icon">&#xe640;</i>
    </a>
</script>
<script type="text/html" id="tpmPic">
    <div><img src='/upload/{{d.structure}}' height='60'/></div>
</script>
<script>
    layui.use(['laydate', 'form','table'],
            function () {
                var laydate = layui.laydate;
                var form = layui.form;
                
                //执行一个laydate实例
                laydate.render({
                    elem: '#end_date' //指定元素
                });
                var table = layui.table;
                var order_id = "<?php echo $orderid; ?>";
                var og_id = "<?php echo $og_id; ?>";
                table.render({
                    elem: '#product-table'
                    ,url:"<?php echo url('groupPrice'); ?>?order_id="+order_id+"&og_id="+og_id
                    ,cellMinWidth: 80
                     ,cols: [[
                      {field:'numbers',title:'编号',width:50}
											,{field:'position',title:'安装位置',edit:'text',width:100,minWidth:110}
                      ,{field:'name',title:'名称',width:100}
                      ,{field:'material',title:'材质',minWidth:217}
                      ,{field:'flower_type',title:'型号',maxwidth:87}
                      ,{field:'color_name',title:'铝材/花件颜色',width:107}
                      ,{field:'㎡',title:'单位',templet:"<div>㎡</div>"}
                      ,{field:'all_width',title:'宽',edit:'text',width:107}
                      ,{field:'all_height',title:'高',edit:'text',width:107}
                      ,{field:'count',title:'个数',edit:'text'}
                      ,{field:'area',title:'报价面积',width:79}
                      ,{field:'product_area',title:'产品面积',width:79}
                      ,{field:'price',title:'单价',edit:'text',width:107}
                      ,{field:'rebate',title:'折扣',width:107}
                       ,{field:'rebate_price',title:'折后价',edit:'text',width:107}
                        ,{field:'all_price',title:'总金额',width:107}
                        ,{field:'om_id',title:'操作',toolbar:'#ptoolBar',minWidth:80}
                    ]]
                  });
                  
                  table.render({
                    elem: '#group-table'
                    ,url:"<?php echo url('groupCalculate'); ?>?order_id="+order_id+"&og_id="+og_id
                    ,cellMinWidth: 80
                     ,cols: [[
                      {type:'numbers',title:'编号',width:50}
											,{field:'position',title:'安装位置',edit:'text',width:100,minWidth:110}
                      ,{field:'name',title:'名称',width:100}
                      ,{field:'material',title:'材质',minWidth:217}
                      ,{field:'flower_type',title:'型号',maxwidth:87}
                      ,{field:'color_name',title:'铝材/花件颜色',width:107}
                      ,{field:'㎡',title:'单位',templet:"<div>㎡</div>"}
                      ,{field:'all_width',title:'宽',edit:'text',width:80}
                      ,{field:'all_height',title:'高',edit:'text',width:80}
                      ,{field:'count',title:'个数',edit:'text'}
                      ,{field:'area',title:'报价面积',width:79}
                      ,{field:'product_area',title:'产品面积',width:79}
                      ,{field:'price',title:'单价',edit:'text',width:107}
                      ,{field:'rebate',title:'折扣',width:80}
                       ,{field:'rebate_price',title:'折后价',edit:'text',width:107}
                        ,{field:'all_price',title:'总金额',width:107}
                        ,{field:'structure',title:'结构图',templet:"#tpmPic",minWidth:90}
                        ,{field:'om_id',title:'操作',toolbar:'#ptoolBar',minWidth:80}
                    ]]
                  });
                  
                //监听产品单元格编辑
                table.on('edit(product-table)', function(obj){
                  var value = obj.value //得到修改后的值
                  ,data = obj.data //得到所在行所有键值
                  ,field = obj.field; //得到字段
                   $.post("<?php echo url('editProductPrice'); ?>", {data:data,field:field,value:value,order_id:<?php echo $orderid; ?>,is_group:1}, 
                   function(obj){                         
                       if(obj.code == 0){

                           return;
                       }
                       layer.msg(obj.msg, {icon: 2});
                   },'json');  
                  
                });  
                //监听产品单元格编辑
                table.on('edit(group-table)', function(obj){
                  var value = obj.value //得到修改后的值
                  ,data = obj.data //得到所在行所有键值
                  ,field = obj.field; //得到字段
                   $.post("<?php echo url('editProductPrice'); ?>", {data:data,field:field,value:value,order_id:<?php echo $orderid; ?>,is_group:3},
                   function(obj){                         
                       if(obj.code == 0){

                           return;
                       }
                       layer.msg(obj.msg, {icon: 2});
                   },'json');  
                  
                }); 
                //点击按钮重载产品表格
                $('#product-reload').click(function(){
                    table.reload('product-table');  //重载数据表格
                })
                
                //点击按钮重载原材料表格
                $('#group-reload').click(function(){
                    table.reload('group-table');  //重载数据表格
                })
        
                //监听提交表单
                form.on('submit(edit)',
                        function (data) {                            
                            ajaxform("<?php echo url('edit'); ?>", data.field);
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

            });
        /*删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                var order_id = <?php echo $orderid; ?>;
                ajaxform("<?php echo url('delGroupProduct'); ?>",{id:id,order_id:order_id},1);
            });
        }   
         //复制产品
        function gcopy(id,order_id){
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
            });
        }
        
</script>

</html>