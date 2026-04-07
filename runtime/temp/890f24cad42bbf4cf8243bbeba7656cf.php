<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:101:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/dealer/index.html";i:1688961496;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .layui-fluid{ background-color: #FFFFFF;margin: 15px;}
    .list {color:#666;margin-top: 18px;}
    .list p{line-height: 20px;}
    .red{color:red;}
</style>   
    <body>
         <div class="x-nav">
            <span class="layui-breadcrumb">
               <a href="javascript:void(0)">经销商列表</a>
            </span> 
            
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
            </a>
        </div>
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    
                        
                            <form class="layui-form layui-col-space5" id="form1">
                                <div class="layui-input-inline x-city" id="end">
                                    <div class="layui-input-inline" style="width:120px">
                                      <select name="province" lay-filter="province">
                                        <option value="">请选择省</option>
                                      </select>
                                    </div>
                                    <div class="layui-input-inline" style="width:120px">
                                      <select name="city" lay-filter="city">
                                        <option value="">请选择市</option>
                                      </select>
                                    </div>
                                    <div class="layui-input-inline" style="width:120px">
                                      <select name="area" lay-filter="area">
                                        <option value="">请选择县/区</option>
                                      </select>
                                    </div>
                                </div>
                                
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="keyword" placeholder="名称/号码/业务员/地址" autocomplete="off" class="layui-input" value="<?php echo $keyword; ?>">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="sort" lay-filter="sort">
                                        <option value="">默认排序</option>
                                        <option value="have_pay asc" <?php if($sort == 'have_pay asc'): ?>selected<?php endif; ?>>销售金额升序</option>
                                        <option value="have_pay desc" <?php if($sort == 'have_pay desc'): ?>selected<?php endif; ?>>销售金额降序</option>
                                        <option value="no_pay asc" <?php if($sort == 'no_pay asc'): ?>selected<?php endif; ?>>欠款金额升序</option>
                                        <option value="no_pay desc" <?php if($sort == 'no_pay desc'): ?>selected<?php endif; ?>>欠款金额降序</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                               
                                <div class="layui-input-inline" style="float: right;">
                                    <button class="layui-btn layui-btn-normal" type='button' id="import">
                                        <i class="layui-icon"></i>导入经销商
                                    </button>
                                    <a class="layui-btn layui-btn-warm" href="/template/dealer.xls">
                                        <i class="layui-icon"></i>下载模板
                                    </a>
                                    <a class="layui-btn" onclick="xadmin.open('添加经销商', '<?php echo url('add'); ?>', 550, 500)">
                                        <i class="layui-icon"></i>添加经销商
                                    </a>
                                    
                                </div>
                                <blockquote class="layui-elem-quote" style="padding: 8px;margin-top: 5px;">
                                    目前经销商数量:<?php echo $all_count; ?>家
                                </blockquote>
                            </form>


                        <div class="layui-form list">
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <div class="layui-col-md6" style="margin-top: 15px;min-height: 126px;">
                                <div class="layui-col-md2">
                                    <?php if($v['pic'] != ''): ?>
                                    <img src="/upload/<?php echo $v['pic']; ?>" height="90" width='102'/>
                                    <?php else: ?>
                                    <img src="/static/images/dealer.png" height="90" width="102"/>
                                    <?php endif; ?>
                                    <p>ID:<?php echo $v['id']; ?></p>
                                </div>
                                <div class="layui-col-md4">
                                    <h3><?php echo $v['name']; ?>  <?php echo $v['contact']; ?></h3>
                                    <p>地址:<?php echo mb_substr($v['address'],0,30); ?></p>
                                    <p>业务员:<?php echo $v['sales_name']; ?></p>
                                    <p>销售金额:<span class="red"><?php if($v['total_price'] != null): ?><?php echo $v['total_price']; else: ?>0<?php endif; ?></span></p>
                                     <p>欠款金额:<span class="red"><?php echo $v['no_pay']; ?></span></p>
                                </div>
                                <div class="layui-col-md3">
                                    <p><?php if($v['order_time'] != 0){ echo round($v['day'],2).'天无下单';}else{ echo '暂未下单';} ?></p>
                                    <p>最后下单:<?php if($v['order_time'] != 0): ?><?php echo date('Y-m-d',$v['order_time']); else: ?>暂无<?php endif; ?></p>
                                    <p>创建时间：<?php if($v['add_time'] != 0): ?><?php echo date('Y-m-d',$v['add_time']); else: endif; ?></p>
                                    <p>
                                        <a href='javascript:;' class='layui-btn layui-btn-normal' onclick="xadmin.open('编辑经销商', '<?php echo url('edit',array('id'=>$v['id'])); ?>', 550, 500)">编辑</a>
                                        <a class="layui-btn" href="<?php echo url('orderList',array('id'=>$v['id'])); ?>">查看订单</a>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>

                    
                </div>
<!--                <div class="layui-card-body ">-->
                    <div class="page">
                        <?php echo $page; ?>
                    </div>
<!--                </div>-->
            </div>
        </div>
    </body>
    <script src="/static/js/jquery.min.js" type="text/javascript"></script>
    <script src="/static/js/xcity.js?v=<?php echo time()?>" type="text/javascript"></script>
    <script>layui.use(['laydate', 'form','upload','code'],
        function() {
            var laydate = layui.laydate;
            form = layui.form;
            var upload = layui.upload;
            $ = layui.jquery;
            layui.code();
            
            $('#end').xcity();
            laydate.render({
                elem: '#starttime' //指定元素
            });
            laydate.render({
                elem: '#endtime' //指定元素
            });
            
            //执行上传实例,导入结构
            var uploadInst = upload.render({
                elem: '#import' //绑定元素
                , url: "<?php echo url('importDealer'); ?>" //上传接口
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
            
            form.on('select(sort)',function(data){
                $('#form1').submit();
            })
        });

        /*删除*/
        function delivery_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                ajaxform("<?php echo url('delDelivery'); ?>",{id:id},1);
            });
        }
        
    </script>

</html>