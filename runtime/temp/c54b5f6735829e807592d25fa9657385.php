<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:97:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/bom/color.html";i:1635496791;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
<link href="/static/js/treeTable/treeTable.css" rel="stylesheet" type="text/css"/>    
    <body>
        <div class="x-nav">
            <span class="layui-breadcrumb">
                <a href="#">颜色</a>
            </span>
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
            </a>
        </div>
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            
                        </div>
                        <div class="layui-card-header">
                            <form class="layui-form layui-col-space5">
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" placeholder="请输入名称/编号" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                                <div class="layui-input-inline" style="float: right">
<!--                                    <button class="layui-btn layui-btn-danger" onclick="delAll()" type="button">
                                        <i class="layui-icon"></i>批量删除
                                    </button>-->
                                    <button class="layui-btn" onclick="xadmin.open('添加物料', '<?php echo url('colorAdd'); ?>', 600, 550)" type='button'>
                                        <i class="layui-icon"></i>添加颜色
                                    </button>
                                  
                                </div>
                            </form>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form treeTable" lay-even lay-skin="nob" id="tables2">
                                <thead>
                                    <tr>
<!--                                        <th style="width: 1%;">
                                            <input type="checkbox" name="" lay-filter="checkall" lay-skin="primary">
                                        </th>-->
                                        <th>物料名称</th>
                                        <th>物料编号</th>
                                        <th>属性</th>
                                        
                                        <!--<th>归属颜色</th>-->
                                        <th>图片</th>
                                         <th>单位</th>
                                        <th>单价</th>
                                        <th>操作</th></tr>
                                </thead>
                                <tbody>
                                   <?php echo $treeList; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </body>
     <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            
            
            // 监听全选
            form.on('checkbox(checkall)', function(data){

              if(data.elem.checked){
                $('tbody input').prop('checked',true);
              }else{
                $('tbody input').prop('checked',false);
              }
              form.render('checkbox');
            });
        });

       

        
        /*删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('bom/colorDel'); ?>",{id:id},1);
            });
        }
        
        //删除选中的数据
        function delAll (argument) {
            var ids = [];

            // 获取选中的id 
            $('tbody input').each(function(index, el) {
                if($(this).prop('checked')){
                   ids.push($(this).val())
                }
            });
            
            layer.confirm('确认要删除吗？',function(index){
               ajaxform("<?php echo url('bom/colorDel'); ?>",{ids:ids},1);
            });
           
      }
    </script>
    <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script src="/static/js/treeTable/treeTable.js" type="text/javascript"></script>
    <!--<script src="/static/js/treeTable/jquery.treetable.js" charset="utf-8"></script>-->  
    <script>
        $(function(){
           $("#tables2").treeTable({
               expandLevel: 2,
               column: 1
           });
        })
 
    </script>

</html>