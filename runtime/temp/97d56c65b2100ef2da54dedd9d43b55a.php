<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/series/index.html";i:1635496796;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-table td, .layui-table th{
        padding: 9px 8px;
    }
</style>
<link href="/static/js/treeTable/treeTable.css" rel="stylesheet" type="text/css"/>
    <body>
        <div class="x-nav">
            <span class="layui-breadcrumb">
                <a href="">系列列表</a>
<!--                <a>
                    <cite>导航元素</cite>
                </a>-->
            </span>
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
            </a>
        </div>
        <form class="layui-form layui-col-space5" method="post">
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            
                        </div>
                        <div class="layui-card-header">
                            
                                
                                <div class="layui-input-inline">
                                    
                                    <button class="layui-btn" onclick="xadmin.open('添加系列', '<?php echo url('SeriesAdd'); ?>', 500, 450)" type='button'>
                                        <i class="layui-icon"></i>添加系列
                                    </button>
                                    <button class="layui-btn layui-btn-normal" type='button' lay-filter="sort" lay-submit="">
                                        <i class="layui-icon"></i>更新排序
                                    </button>
                                    <p class="layui-word-aux" style="display: inline;font-size: 13px;">注:数字越小,则添加订单时的排列越靠前</p>
                                </div>
                            
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form treeTable" lay-even lay-skin="nob" id="tablesd">
                                <thead>
                                    <tr>

                                        <th>系列名称</th>
                                        <th>ID</th>
                                        <th>最小面积/长度</th>
                                        <th>单价</th>
                                        <th>颜色</th>
                                        <th>纱网</th>
                                        <th>花件</th>
                                        <th>结构</th>
                                        <th>把手位</th>
                                        <th>五金</th>
                                        <th>物料绑定</th>
                                        <th>扣库物料</th>
                                        <th>操作</th>
                                        <th width="7%">排序</th>
                                    </tr>
                                </thead>
                                <tbody class="x-cate">
                                    <?php echo $treeList; ?>                                    
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
            </form>
    </body>   
    
    <script>
        layui.use(['form'], function(){
            form = layui.form;
            
            form.on('submit(sort)',function(data){
                ajaxform("<?php echo url('sort'); ?>",data.field,1);
            })
        });

        /*删除*/
        function member_del(obj, id) {
            layer.confirm('将会同时删除下属系列,确定要删除吗',
            function(index) {
                var name = $(obj).parents('tr').find('td').first().text();console.log(name);
                //发异步删除数据
                ajaxform("<?php echo url('seriesDel'); ?>",{id:id,name:name},1);
            });
        }

       
       

    </script>
    <script src="/static/js/jquery.min.js" charset="utf-8"></script>
    <script src="/static/js/treeTable/treeTable.js" charset="utf-8"></script>    
    <script>
        $(function(){
           $("#tablesd").treeTable({expandable: true,initialState:'expanded'});
           
           //当table的价格不为0时，隐藏td里的链接a
           $('.is-price').each(function(){
               if($(this).text()==0){
                   $(this).nextAll('.is-hide').find('a').remove();
               }
           })
        })
        function copy(id){
            layer.confirm('确认要复制此系列吗？',function(index){
               ajaxform("<?php echo url('copy'); ?>",{id:id},1);
            });
        }
    </script>
</html>