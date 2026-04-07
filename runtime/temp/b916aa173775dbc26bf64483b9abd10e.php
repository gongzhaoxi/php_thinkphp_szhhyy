<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:96:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/bom/five.html";i:1635496792;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    
    <body>
        <div class="x-nav">
            <span class="layui-breadcrumb">
                <a href="#">五件</a>
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
                                    <button class="layui-btn layui-btn-danger" onclick="delAll()" type='button'>
                                        <i class="layui-icon"></i>批量删除
                                    </button>
                                    <button class="layui-btn" onclick="xadmin.open('添加物料', '<?php echo url('fiveAdd'); ?>', 600, 400)" type='button'>
                                        <i class="layui-icon"></i>添加物料
                                    </button>
                                    <button class="layui-btn layui-btn-normal" type='button' id="import">
                                        <i class="layui-icon"></i>导入数据
                                    </button>
                                    <a class="layui-btn layui-btn-warm" href="<?php echo url('bom/exportFive'); ?>">
                                        <i class="layui-icon"></i>导出数据
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" name="" lay-filter="checkall" lay-skin="primary">
                                        </th>
                                        <th>物料编号</th>
                                        <th>物料名称</th>
                                         <th>单位</th>
                                        <th>单价</th>
                                        <th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="id" lay-skin="primary" value="<?php echo $v['id']; ?>">
                                        </td>
                                        <td><?php echo $v['code']; ?></td>
                                        <td><?php echo $v['name']; ?></td>
                                        <td><?php echo $v['unit']; ?></td>
                                        <td><?php echo $v['price']; ?></td>
                                        <td class="td-manage">
                                            <a title="编辑" onclick="xadmin.open('编辑','<?php echo url('fiveAdd',array('id'=>$v['id'])); ?>', 600, 400)" href="javascript:;">
                                                <i class="layui-icon">&#xe642;</i>
                                            </a>
                                            <a title="删除" onclick="member_del(this,<?php echo $v['id']; ?>)" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body ">
                            <div class="page">
                                <?php echo $page; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>layui.use(['laydate', 'form','upload'],
        function() {
            var laydate = layui.laydate;
            var  form = layui.form;
            var upload = layui.upload;
            
            //执行一个laydate实例
            laydate.render({
                elem: '#start' //指定元素
            });

            //执行一个laydate实例
            laydate.render({
                elem: '#end' //指定元素
            });
            
            // 监听全选
            form.on('checkbox(checkall)', function(data){

              if(data.elem.checked){
                $('tbody input').prop('checked',true);
              }else{
                $('tbody input').prop('checked',false);
              }
              form.render('checkbox');
            });
            
               //执行上传实例
            var uploadInst = upload.render({
                elem: '#import' //绑定元素
                , url: "<?php echo url('bom/importFive'); ?>" //上传接口
                , size: 800000 //限制文件大小，单位 KB
                , method: 'post'
                , fileAccept: 'file'
                , exts: "xls|xlsx"
                , before:function(){ layer.load(0,{time: 10*1000,shade:false});}
                , done: function (res) {
                    layer.close();
                    if(res.code==0){
                        layer.msg(res.msg,{icon:1,time:2000},function(){ location.reload();});                        
                        return;
                    }
                    layer.msg(res.msg,{icon:2})
                }
                , error: function () {
                    //请求异常回调
                }
            });
            
        });

       

        /*删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('bom/fiveDel'); ?>",{id:id},1);
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
               ajaxform("<?php echo url('bom/fiveDel'); ?>",{ids:ids},1);
            });
           
      }
    </script>

</html>