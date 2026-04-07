<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:102:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/public/../application/admin/view/index/welcome.html";i:1677560636;s:92:"/mnt/datadisk0/www/wwwroot/wchuanghua2.ecloudm.com/application/admin/view/public/header.html";i:1635496794;}*/ ?>
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
    .center{text-align: center;padding: 15px 0;}
    .header-span span{float: right;margin: 0 1%}
    .salesname-title span,.series-title span{margin: 0 1%;}
    .time{ width: 11%;display: inline;float: right;margin: 5px;}
    .time2{ width: 35%;display: inline;float: right;margin: 5px;}
    .right{float: right;}
    .top{font-size: 13px;height: 32px;line-height: 30px;}
    .active{ color:red;}
    .tactive{ color:#1E9FFF}
    .price-title{background-color:#2F4056;text-align: center;padding: 5px 0;color: #fff}
    .rank li{line-height: 35px;height: 35px;}
</style>
<body>
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header header-span all-count">
                        <a href="javascript:void(0)" class="layui-btn" id="total_btn" style="float: right;margin-top: 5px;">查询</a>
                        <input type="text" name="total_end" id="total-time" placeholder="选择时间区间" autocomplete="off" class="layui-inline layui-input time">
                        <span attr="year">今年</span>
                        <span attr="month">本月</span>
                         <span attr="week">本周</span>
                        <span attr="today" class="active">今日</span>

                    </div>
                    <div class="layui-card-body ">
                        <ul class="layui-row layui-col-space10 layui-this x-admin-carousel x-admin-backlog">
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>总销售金额</h3>
                                    <p class="center"><cite >¥:<span id="all_money">0</span></cite></p>
                                    <hr>
                                    <p>日均销售金额 ¥:<span id="amoney">0</span></p>
                                </a>
                            </li>
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>已收款总金额</h3>
                                    <p class="center"><cite >¥:<span id="all_have">0</span></cite></p>
                                    <hr>
                                    <p>日均收款金额 ¥:<span id="ahave">0</span></p>
                                </a>
                            </li>
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>未收款总金额</h3>
                                    <p class="center"><cite >¥:<span id="all_no">0</span></cite></p>
                                    <hr>
                                    <p>折让金额：<span id="financePay">0</span></p>
                                </a>
                            </li>
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>总订单数</h3>
                                    <p class="center"><cite><span id="all_order">0</span></cite></p>
                                    <hr>
                                    <p>日均订单数 <span id="aorder">0</span></p>
                                </a>
                            </li>
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>总销售产品数</h3>
                                    <p class="center"><cite><span id="all_product">0</span></cite></p>
                                    <hr>
                                    <p>日均销售产品数 <span id="aproduct">0</span></p>
                                </a>
                            </li>
                            <li class="layui-col-md2">
                                <a href="javascript:;" class="x-admin-backlog-body">
                                    <h3>总产品面积数</h3>
                                    <p class="center"><cite><span id="all_area">0</span></cite></p>
                                    <hr>
                                    <p>日均产品面积数 <span id="aarea">0</span></p>
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>

            <div class="layui-col-md4">
                <div class="layui-card" style="height: 500px;">
                    <div class="layui-card-header total-span">
                        <div class="series-title" style="display: inline;">
                            <span attr="today">今日</span>
                            <span attr="week" class="tactive">本周</span>
                            <span attr="month">本月</span>
                            <a href="javascript:void(0)" class="layui-btn" id="series-btn" style="float: right;margin-top: 5px;">查询</a>
                            <input type="text" name="salesname_time" id="series-time" autocomplete="off" class="layui-inline layui-input time2" placeholder="选择时间区间">
                        </div>

                    </div>
                    <div class="layui-card-body">
                        <div id="series" style="width: 100%;height:450px;display: inline-block;float: left;"></div>
                    </div>
                </div>
            </div>

            <div class="layui-col-md4">
                <div class="layui-card" style="height: 500px;">
                    <div class="layui-card-header total-span">
                        <div class="salesname-title" style="display: inline;">
                            <span attr="today">今日</span>
                            <span attr="week" class="tactive">本周</span>
                            <span attr="month">本月</span>
                            <a href="javascript:void(0)" class="layui-btn" id="salesname-btn" style="float: right;margin-top: 5px;">查询</a>
                            <input type="text" name="salesname_time" id="salesname-time" autocomplete="off" class="layui-inline layui-input time2" placeholder="选择时间区间">
                        </div>

                    </div>
                    <div class="layui-card-body">
                        <div id="main" style="width: 100%;height:450px;display: inline-block;float: left;"></div>
                    </div>
                </div>
            </div>

            <div class="layui-col-md4">
                <div class="layui-card" style="height: 500px;">
                    <div class="layui-card-header total-span">
                        <div class="area-price-title" style="display: inline;">
                            <span attr="today">今日</span>
                            <span attr="week" class="tactive">本周</span>
                            <span attr="month">本月</span>
                            <a href="javascript:void(0)" class="layui-btn" id="price-area-btn" style="float: right;margin-top: 5px;">查询</a>
                            <input type="text" name="price-area-time" id="price-area-time" autocomplete="off" class="layui-inline layui-input time2" placeholder="选择时间区间">
                        </div>

                    </div>
                    <div class="layui-card-body">
                        <div id="area-box" style="width: 100%;height:241px;"></div>
                        <div id="price-box" style="width: 100%;height:244px;"></div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md12">
                <div class="layui-col-md3">
                    <div class="layui-card" style="height: 450px;">
                        <div class="layui-card-body">
                            <div class="price-title">本月总额&nbsp;&nbsp;<?php echo $today_month['sum']; ?></div>
                            <p>客户销售额排名</p>
                            <ul class="rank">
                                <?php if(is_array($today_month['list']) || $today_month['list'] instanceof \think\Collection || $today_month['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $today_month['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <li><?php echo $i; ?>&nbsp;&nbsp;<?php echo $v['name']; ?> <span style="float: right"><?php echo $v['all_price']; ?></span></li>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                <li class="x-red">&nbsp;&nbsp;&nbsp;&nbsp;前十汇总<span style="float: right"><?php echo $today_month['ten']; ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="layui-card" style="height: 450px;">
                        <div class="layui-card-body">
                            <div class="price-title">上月总额&nbsp;&nbsp;<?php echo $prev_month['sum']; ?></div>
                            <p>客户销售额排名</p>
                            <ul class="rank">
                                <?php if(is_array($prev_month['list']) || $prev_month['list'] instanceof \think\Collection || $prev_month['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $prev_month['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <li><?php echo $i; ?>&nbsp;&nbsp;<?php echo $v['name']; ?> <span style="float: right"><?php echo $v['all_price']; ?></span></li>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                <li class="x-red">&nbsp;&nbsp;&nbsp;&nbsp;前十汇总<span style="float: right"><?php echo $prev_month['ten']; ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="layui-card" style="height: 450px;">
                        <div class="layui-card-body">
                            <div class="price-title">上年本月总额&nbsp;&nbsp;<?php echo $month_year['sum']; ?></div>
                            <p>客户销售额排名</p>
                            <ul class="rank" style="height: 354px">
                                <?php if(is_array($month_year['list']) || $month_year['list'] instanceof \think\Collection || $month_year['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $month_year['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <li><?php echo $i; ?>&nbsp;&nbsp;<?php echo $v['name']; ?> <span style="float: right"><?php echo $v['all_price']; ?></span></li>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                            <div class="x-red">&nbsp;&nbsp;&nbsp;&nbsp;前十汇总<span style="float: right"><?php echo $month_year['ten']; ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="layui-card" style="height: 450px;">
                        <div class="layui-card-body">
                            <div id="sales-back" style="width: 100%;height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>layui.use(['laydate', 'form', 'layer'],
            function () {
                var laydate = layui.laydate;
                var form = layui.form;
                var layer = layui.layer;
                $ = layui.jquery;

                lay('.time').each(function () {
                    laydate.render({
                        elem: this
                        , trigger: 'click',
                        range:'~'
                    });
                });
                lay('.time2').each(function () {
                    laydate.render({
                        elem: this
                        , trigger: 'click',
                        range:'~'
                    });
                });


            });



</script>
<script src="/static/js/jquery.min.js" charset="utf-8"></script>
<script src="/static/js/echarts.min.js" charset="utf-8"></script>
<script>
    $(function(){
       //初次加载
        total({type:'today'});
        series('week','');
        salesname('week','');
        area('week','');

        //概况统计
        $('.all-count span').click(function(){
            $('.all-count span').removeClass('active');
            $(this).addClass('active');
            var type = $(this).attr('attr');
            total({type:type});

        })
        //概况统计--区间筛选
        $('#total_btn').click(function(){
            var time = $('#total-time').val();
            if(time == '' ){
                layer.msg("请填写开始时间和结束时间",{icon:2});
                return;
            }
            total({type:'btn',time:time})
        })
        
        function total(data){
             $.post("<?php echo url('index/count'); ?>",data,function(obj){  
                 var obj = obj.data;
                  $('#all_money').text(obj.money);
                  $('#amoney').text(obj.averageMoney);
                  $('#all_have').text(obj.havePay);
                  $('#ahave').text(obj.averageHave);
                  $('#financePay').text(obj.financePay);
                  $('#all_no').text(obj.noPay);
                  $('#ano').text(obj.averageNo);
                  $('#all_product').text(obj.product);
                  $('#aproduct').text(obj.averageProduct);
                  $('#all_order').text(obj.order);
                  $('#aorder').text(obj.averageOrder);
                  $('#all_area').text(obj.area);
                  $('#aarea').text(obj.averageArea);
             },'json'); 
        }
    })
</script>
<script>
var seriesChart = echarts.init(document.getElementById('series'));
//系列柱状图
var series_option = {
    title: {
        subtext: '总面积(平方米)',
        top:5,
        left:'10%',
    },
    color: ['#3398DB'],
    tooltip: {
        trigger: 'axis',
        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        top:'3%',
        containLabel: true
    },
    xAxis: [
        {
            type: 'category',
            data: [],
            axisTick: {
                alignWithLabel: true
            }
        }
    ],
    yAxis: [
        {
            type: 'value'
        }
    ],
    series : [
        {
            type:'bar',
            barWidth: '60%',
            label: {
                normal: {
                    show: true,
                    position: 'inside'
                }
            },
            data:[]
        }
    ]
};

function series(field,time) {
    seriesChart.showLoading();
    $.post("<?php echo url('index/series'); ?>", {field: field,time:time}, function (obj) {
        seriesChart.hideLoading()
        series_option['xAxis'][0]['data'] = obj.name;
        series_option['series'][0]['data'] = obj.value;
        seriesChart.setOption(series_option);
    }, 'json');
}
$('.series-title span').click(function () {
    var field = $(this).attr('attr');
    $('.series-title span').removeClass('tactive');
    $(this).addClass('tactive');
    series(field,'');
})
//时间筛选 系列图标
$('#series-btn').click(function () {
    var time = $(this).parent().find('input').val();
    if(time == ''){
        layer.msg('请选择时间',{icon:2});
        return;
    }
    series('btn',time);
});


//初始化 业务员柱状图
var myChart = echarts.init(document.getElementById('main'));
var sales_option = {
    title: {
        subtext: '销售额(元)',
        top:5,
        left:'13%',
    },
    color: ['#d11c11'],
    tooltip: {
        trigger: 'axis',
        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        top:'3%',
        containLabel: true
    },
    xAxis: [
        {
            type: 'category',
            data: [],
            axisTick: {
                alignWithLabel: true
            }
        }
    ],
    dataZoom: [
        {
            show: true,
            start: 0,
            end: 100
        },
        {
            type: 'inside',
            start: 0,
            end: 100
        },
    ],
    yAxis: [
        {
            type: 'value'
        }
    ],
    series : [
        {
            type:'bar',
            barWidth: '60%',
            label: {
                normal: {
                    show: true,
                    position: 'inside'
                }
            },
            data:[]

        }
    ]
};
function salesname(type,time)
{
    myChart.showLoading();
    $.post("<?php echo url('index/salesName'); ?>", {field: type,time:time}, function (obj) {
        myChart.hideLoading()
        sales_option['xAxis'][0]['data'] = obj.name;
        sales_option['series'][0]['data'] = obj.value;
        myChart.setOption(sales_option);
    }, 'json');
}

//异步更改 业务员柱状图数据
$('.salesname-title span').click(function () {
    $('.salesname-title span').removeClass('tactive');
    $(this).addClass('tactive');
    var field = $(this).attr('attr');
    salesname(field,'')
})
//时间筛选 更改 业务员柱状图数据
$('#salesname-btn').click(function () {
    var time = $(this).parent().find('input').val();
    if(time == ''){
        layer.msg('请选择时间',{icon:2});
        return;
    }
    salesname('btn',time);
});




//系列面积 饼状图
var areaChart = echarts.init(document.getElementById('area-box'));
var priceChart = echarts.init(document.getElementById('price-box'));
var areaOption = {
    title: {
        text: '面积',
        left: 'center'
    },
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
        orient: 'vertical',
        type: 'scroll',
        top: '0',
        right: '0',
        itemWidth: 10,
        itemHeight: 10,
        formatter: function(name){
            return name.length > 10 ? name.substr(0,10) + "..." : name;
        }
    },
    series: [
        {
            name: '面积',
            type: 'pie',
            radius: ['55%', '80%'], // 圆心切割大小, 饼图大小
            center: ["30%", "40%"], // 显示位置
            label: {
                show: false,
                position: 'center'
            },
        },
    ]
};
function area(field,time){
    areaChart.showLoading();
    //面积图标
    $.post("<?php echo url('areaCount'); ?>",{field: field,time:time},function(obj){
        areaOption['title']['text'] = '面积';
        areaOption['series'][0]['name'] = '面积';
       areaOption['legend']['data'] = obj.data.title;
       areaOption['series'][0]['data'] = obj.data.value;
       areaChart.setOption(areaOption);
    },'json');
    //价格图标
    $.post("<?php echo url('priceCount'); ?>",{field: field,time:time},function(obj){
        areaChart.hideLoading();
        areaOption['legend']['data'] = obj.data.title;
        areaOption['title']['text'] = '价格';
        areaOption['series'][0]['name'] = '价格';
        areaOption['series'][0]['data'] = obj.data.value;
        priceChart.setOption(areaOption);
    },'json');
}
$('.area-price-title span').click(function () {
    var field = $(this).attr('attr');
    $('.area-price-title span').removeClass('tactive');
    $(this).addClass('tactive');
    area(field,'');
})
//时间筛选 价格面积 图表
$('#price-area-btn').click(function () {
    var time = $('#price-area-time').val();
    if(time == ''){
        layer.msg('请选择时间',{icon:2});
        return;
    }
    area('btn',time);
});


//销售额 回款额 图表
var salesbackChart = echarts.init(document.getElementById('sales-back'),'light');
var sales_back_option = {
    title: {
        text: '金额(元)',
    },
    color: ['#3398DB'],
    tooltip: {
        trigger: 'axis',
        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis: [
        {
            type: 'category',
            data: [],
            axisTick: {
                alignWithLabel: true
            }
        }
    ],
    yAxis: [
        {
            type: 'value'
        }
    ],
    series: [
        {
            name: '金额',
            type: 'bar',
            barWidth: '60%',
            showBackground: true,
            data: []
        }
    ]
};
$(function(){
    salesbackChart.showLoading();
    $.post("<?php echo url('index/salesBack'); ?>",{},function (obj) {
        salesbackChart.hideLoading();
        sales_back_option['xAxis'][0]['data'] = obj.name;
        sales_back_option['series'][0]['data'] = obj.value;
        salesbackChart.setOption(sales_back_option);
    },'json');
})
</script>

</html>