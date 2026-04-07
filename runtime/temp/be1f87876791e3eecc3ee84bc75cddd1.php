<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:105:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/public/../application/admin/view/order/add_product.html";i:1635510062;s:91:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/public/header.html";i:1635496794;s:100:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/order/common_add_price.html";i:1743130885;s:104:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/order/common_add_calculate.html";i:1635496794;s:93:"/mnt/datadisk0/www/wwwroot/wchuanghua.ecloudm.com/application/admin/view/order/common_js.html";i:1732236299;}*/ ?>
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
    .tips{ position: absolute;left: 40%;bottom: 150px;display: none;font-size: 19px;}
    .tips span { line-height: 30px;}
	.bottom-fixed{display:none;}
</style>   
    <body>
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    <blockquote class="layui-elem-quote">报价信息</blockquote> 
                    <div class="layui-form-item technology">
                        <label class="layui-form-label"><span class="x-red">*</span>工艺</label>
                        <div class="layui-inline">
                            <select name="tech[]" lay-filter="one">
                              <option value="">请选择</option>
                              <?php if(is_array($one) || $one instanceof \think\Collection || $one instanceof \think\Paginator): $i = 0; $__LIST__ = $one;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                              <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                              <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="tech[]" lay-filter="two" id="two">
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" id="tthree" style="display: none;">
                            <select name="tech[]" lay-filter="three" id="three" >
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" id="tfour" style="display: none;">
                             <select name="tech[]" lay-filter="four" id="four">
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" id="tfive" style="display: none;">
                             <select name="tech[]" lay-filter="five" id="five">
                              <option value="">请选择</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item" id="flower-boxd">
                        <label for="username" class="layui-form-label">
                            <span class="x-red"></span>花型
                        </label>
                        <div class="layui-input-inline">
                           <input type="text" id="flower" name="flower" class="layui-input">                          
                        </div>
                        <input name="flower_pic" id="flower_pic" type="hidden" />
                        <input name="flower_id" id="flower_id" type="hidden" />
                        <input name="flower_max_height" id="flower_max_height" type="hidden" value="0" />
                        <input name="flower_min_height" id="flower_min_height" type="hidden" value="0" />
                        <input name="flower_max_width" id="flower_max_width" type="hidden" value="0" />
                        <input name="flower_min_width" id="flower_min_width" type="hidden" value="0" />
                        <button class="layui-btn" type="button" onclick="show_flower()">点击选择</button>
                    </div>
                    <input name="series_id" id="series_id" type="hidden"/>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><span class="x-red">*</span>铝型颜色</label>
                        <div class="layui-inline">
                            <select name="alum_color[]" lay-filter="color" required="" lay-verify="required" id="aluminum_color" type="1">
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" style="display: none">
                            <select name="alum_color[]" lay-filter="color_two" id="aluminum_two" type="1">
                              <option value="">请选择</option>
                            </select>
                        </div>
                         <div class="layui-inline" style="display: none">
                            <select name="alum_color[]" id="aluminum_three" type="1">
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline diy-name" style="display:none;">
                            <input type="text" id="username" name="alum_name" class="layui-input" placeholder="填写颜色名称">
                        </div>
                        <div class="layui-inline diy-price" style="display:none;">
                            <input type="text" id="username" name="alum_name_price" class="layui-input" placeholder="填写颜色价格">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">花件颜色</label>
                        <div class="layui-inline">
                            <select name="flower_color[]" lay-filter="color" id="flower_color" type="2" class='flower-select'>
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" style="display: none">
                            <select name="flower_color[]" lay-filter="color_two" type="2" id="flower_two" class="flower-select">
                              <option value="">请选择</option>
                            </select>
                        </div>
                         <div class="layui-inline" style="display: none">
                            <select name="flower_color[]" id="flower_three" type="2" id="flower_three" class="flower-select">
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline diy-name" style="display:none;">
                            <input type="text" id="username" name="flower_name" class="layui-input" placeholder="填写颜色名称">
                        </div>
                        <div class="layui-inline diy-price" style="display:none;">
                            <input type="text" id="username" name="flower_name_price" class="layui-input" placeholder="填写颜色价格">
                        </div>
                    </div>
                    <div class="layui-form-item yarn-box">
                        
                        <label class="layui-form-label">纱网</label>
                        <div class="layui-inline">
                            <select name="yarn_color" lay-filter='yarn' id='yarn'>
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" style='width: 3%;margin-right: 0;display: none;'>
                            <label class="layui-form-label" style='width:100%;float: left;padding: 0;'>厚度</label>
                        </div>
                        <div class="layui-inline" style='margin-left: 9px;display: none;'>                            
                            <select name="yarn_thickness" id='yarn_two'>
                            
                            </select>
                        </div>
                        <input name="yarn_price" id="yarn_price" type="hidden"/>
                    </div>
                    <div class="layui-form-item five-box" style="display: none;">
                        
                        <label class="layui-form-label">五金</label>
                        <div class="layui-inline">
                            <select name="five_id" lay-filter='fived' id='fived'>
                              <option value="">请选择</option>
                            </select>
                        </div>
                        <div class="layui-inline" style='width: 8%;margin-right: 0;'>
                            <label class="layui-form-label" style='width:100%;float: left;padding: 0;'>五金数量</label>
                        </div>
                        <div class="layui-inline" style='margin-left: 9px;'>                            
                           <input name="five_count" id="five_count" type="text" class="layui-input"/>
                        </div>
                        <input name="five_price" id="five_price" type="hidden"/>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">窗型结构</label>
                        <div class="layui-inline">
                            <select name="window_type_a" lay-filter='window_type_a' id='window_type_a'>
                              <option value="常规">常规</option>
                              <option value="飘窗">飘窗</option>
                              <option value="圆弧">圆弧</option>
                              <option value="内弧(拱)形窗">内弧(拱)形窗</option>
                              <option value="外弧(拱)形窗">外弧(拱)形窗</option>
                              <option value="内弧(拱)形护栏">内弧(拱)形护栏</option>
                              <option value="外弧(拱)形窗护栏">外弧(拱)形窗护栏</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item" id="window-box" style="display:none">
                        <div class="layui-input-inline" style="width: 40%">
                            <label for="username" class="layui-form-label">
                                <span class="x-red"></span>飘窗面数
                            </label>
                            <div class="layui-input-inline">
                                <input type="text" id="flywindow" name="window" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位面
                            </div>
                        </div>
                        <div class="layui-input-inline" style="width: 40%">
                            <label for="username" class="layui-form-label">
                            <span class="x-red"></span>上飘
                            </label>
                            <div class="layui-input-inline">
                                <input type="text"  name="top_fly" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位mm
                            </div>
                        </div>
                        <div class="layui-input-inline" style="width: 40%">
                            <label for="username" class="layui-form-label">
                            <span class="x-red"></span>下飘
                            </label>
                            <div class="layui-input-inline">
                                <input type="text"  name="bottom_fly" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位mm
                            </div>
                        </div>
                        <div class="layui-input-inline" style="width: 40%">
                            <label for="username" class="layui-form-label">
                            <span class="x-red"></span>左飘
                            </label>
                            <div class="layui-input-inline">
                                <input type="text" name="left_fly" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位mm
                            </div>
                        </div>
                        <div class="layui-input-inline" style="width: 40%">
                            <label for="username" class="layui-form-label">
                            <span class="x-red"></span>右飘
                            </label>
                            <div class="layui-input-inline">
                                <input type="text"  name="right_fly" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位mm
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item" id="arc-box">
                        <div id="arc-length-box" style="display:none">
                            <label for="username" class="layui-form-label ">
                                <span class="x-red"></span>弧长
                            </label>
                            <div class="layui-input-inline arc-length-box">
                                <input type="text" id="arc" name="arc_height" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位mm
                            </div>
                        </div>
                        <div id="arc-length-count-box" style="display:none">
                            <label for="username" class="layui-form-label ">
                                <span class="x-red"></span>计算弧长数量
                            </label>
                            <div class="layui-input-inline">
                                <input type="text" id="username" name="arc_length_count" class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                 单位:条
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">逃生窗</label>
                        <div class="layui-inline">
                            <select name="escape_type_a">
                              <option value="没有逃生窗">没有逃生窗</option>
                              <option value="有逃生窗">有逃生窗</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>产品尺寸:总宽
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="all_width" name="all_width" required="" lay-verify="required" autocomplete="off" class="layui-input">
                        </div>
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>总高
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="all_height" name="all_height" required="" lay-verify="required" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             单位:mm
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>数量
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="username" name="count" required="" lay-verify="required" autocomplete="off" class="layui-input" value='1'>
                        </div>
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>折扣率
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" id="rebate" name="rebate" required="" lay-verify="rebate" autocomplete="off" class="layui-input" value='<?php echo $res['dealer_rebate']; ?>'>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             区间：0-1之间
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red"></span>备注
                        </label>
                        <div class="layui-input-inline" style="width: 40%">
                            <input type="text" id="username" name="note" class="layui-input">
                        </div>
                    </div>
										<div class="layui-form-item">
										    <label for="username" class="layui-form-label">
										        <span class="x-red"></span>安装位置
										    </label>
										    <div class="layui-input-inline" style="width: 40%">
										        <input type="text" id="position" name="position" class="layui-input">
										    </div>
										</div>

                    <!--    经销商下的单才有原单图片            -->
                    <?php if($res['add_type'] == 1): ?>
                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red"></span>原单图片
                        </label>
                        <div class="layui-input-inline">
                            <button type="button" class="layui-btn" id="btn-upload">
                                <i class="layui-icon">&#xe67c;</i>上传图片
                            </button>
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                            <span class="x-red">*</span>最大6m,格式jpg,png,gif,bmp,jpeg,pdf
                        </div>

                    </div>
                    <div class="layui-form-item" id="diy_pic_box" style="display: none">
                        <label for="username" class="layui-form-label">
                        </label>
                        <div class="layui-input-inline">
                            <div class="layui-upload-list">
                                <img class="layui-upload-img" src="/upload/" id="diy_prepic" style="max-height: 60px">
                                <input name="diy_pic" id="diy_pic" type="hidden"/>
                                <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <blockquote class="layui-elem-quote">算料信息</blockquote> 
                    <div class="layui-form-item">
    <label for="username" class="layui-form-label">
        <span class="x-red">*</span>横间距</label>
    <div class="layui-input-inline">
        <input type="text" id="spacing" name="spacing" required="" lay-verify="required" class="layui-input" value="120">
    </div>
    <div class="layui-form-mid layui-word-aux">
         默认120mm，如需修改请填写
    </div>
</div>
<div class="layui-form-item">
    <label for="username" class="layui-form-label">
        <span class="x-red"></span>竖间距</label>
    <div class="layui-input-inline">
        <input type="text" id="bottom_spacing" name="bottom_spacing"  class="layui-input">
    </div>
   
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>右竖间距</label>
    <div class="layui-input-inline">
        <input type="text" id="right_bottom_spacing" name="right_bottom_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>中竖间距</label>
    <div class="layui-input-inline">
        <input type="text" id="center_row_spacing" name="center_row_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>中左竖间距</label>
    <div class="layui-input-inline">
        <input type="text" id="center_left_spacing" name="center_left_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>中右竖间距</label>
    <div class="layui-input-inline">
        <input type="text" id="center_right_spacing" name="center_right_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>左到中</label>
    <div class="layui-input-inline">
        <input type="text"  name="left_to_middle"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>右到中</label>
    <div class="layui-input-inline">
        <input type="text"  name="right_to_middle"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>中到左</label>
    <div class="layui-input-inline">
        <input type="text"  name="middle_to_left"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>中到右</label>
    <div class="layui-input-inline">
        <input type="text"  name="middle_to_right"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>下固横间距</label>
    <div class="layui-input-inline">
        <input type="text" name="bottom_fixed_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">
        <span class="x-red"></span>下固竖间距</label>
    <div class="layui-input-inline">
        <input type="text"  name="bottom_vertical_spacing"  class="layui-input">
    </div>
</div>
<div class="layui-form-item" id="hands-box">
    <label class="layui-form-label">把手位</label>
    <div class="layui-inline">
        <select name="hands" id="hands">
          <option value="">请选择</option>
        </select>
    </div>                      
</div>
<div class="layui-form-item" id="lock_position_box">
    <label for="username" class="layui-form-label">
        <span class="x-red">*</span>锁位高</label>
    <div class="layui-input-inline">
        <input type="text" id="lock_position" name="lock_position" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-form-mid layui-word-aux">
        单位mm
    </div>
</div>
<div class="layui-form-item" >
    <label for="username" class="layui-form-label">
    生产备注
    </label>
    <div class="layui-input-inline">
        <input type="text" id="cal_note" name="cal_note" autocomplete="off" class="layui-input">
    </div>
</div>
<div class="layui-form-item" id="flower-boxd">
    <label for="username" class="layui-form-label">
        <span class="x-red"></span>花型
    </label>
    <div class="layui-input-inline">
        <input type="text" id="flowers" name="flowers" class="layui-input">
    </div>
    <input name="flower_pics" id="flower_pics" type="hidden" />
    <input name="flower_ids" id="flower_ids" type="hidden" />
    <input name="flower_max_heights" id="flower_max_heights" type="hidden" value="0" />
    <input name="flower_min_heights" id="flower_min_heights" type="hidden" value="0" />
    <input name="flower_max_widths" id="flower_max_widths" type="hidden" value="0" />
    <input name="flower_min_widths" id="flower_min_widths" type="hidden" value="0" />
    <button class="layui-btn" type="button" onclick="show_flowers()">点击选择</button>
</div>
                   
                    <div class="layui-form-item" id="structure-box" fixed="">
                        <label for="username" class="layui-form-label">
                            <span class="x-red"></span>选择结构</label>
                        <div class="layui-input-inline">
                            <button class="layui-btn" type="button" onclick="show_structure()">选择</button>
                        </div>
                        <img src="" height="100" id="structure_pic"/>
                        <input name="structure" id="structure" type="hidden"/>
                        <input name="structure_id" id="structure_id" type="hidden"/>
                        <span id="structure_id_box" style="<?php if($info['structure_id'] == ''): ?>display: none<?php endif; ?>">结构id:<?php echo $info['structure_id']; ?></span>
                    </div>
                    <div id="fixed-box">
                    </div>
<!--                    <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>上固定高</label>
                        <div class="layui-input-inline">
                            <input type="text" id="username" name="username" required="" lay-verify="required" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             单位mm
                        </div>
                    </div>
                     <div class="layui-form-item">
                        <label for="username" class="layui-form-label">
                            <span class="x-red">*</span>下固定高</label>
                        <div class="layui-input-inline">
                            <input type="text" id="username" name="username" required="" lay-verify="required" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">
                             单位mm
                        </div>
                    </div>-->
                   
                          
                    <div class="tips">
<!--                        <span class="x-red"><span id="c-min">0</span>≤<span class="fixed-name"></span>≤<span id="c-max">0</span></span></br>-->
<!--                        <div class="bottom-fixed">
                            <span class="x-red"><span>下固定高</span><=<span id="bottom-fixed-height">200</span></span>
                        </div>-->
                    </div>
                    <div class="layui-form-item">
                        <input name="window_type" id="window_type" type="hidden"/>
                        <input name="order_id" type="hidden" value="<?php echo $orderid; ?>"/>
                        <label for="L_repass" class="layui-form-label"></label>
                        <button class="layui-btn" lay-filter="add" lay-submit="">保存</button>
                        <button class="layui-btn layui-btn-normal" type="button" id="read-pic" onclick="show_pic()">
                            查看渲染图
                        </button>
                    </div>
            </form>
            </div>
            </div>
        <script>layui.use(['form', 'layer','upload'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;              
                var upload = layui.upload;

                form.verify({
                    fixed:function(value){
                        if(Number(value)>1000){
                            return '固定值最大为1000';
                        }
                    }
                })
               
                //自定义折扣区间 验证
                form.verify({
                    rebate: function(value, item){ //value：表单的值、item：表单的DOM对象
                    //   if(!new RegExp("^[0|1](\.[0-9]{1,2}){0,1}$").test(value)){
                    //     return '折扣区间：0-1';
                    //   }                      
                    }

                  });

                //上传原单图
                var uploadInst = upload.render({
                    elem: '#btn-upload' //绑定元素
                    , url: "<?php echo url('bom/upload'); ?>" //上传接口
                    , size: 8000 //限制文件大小，单位 KB
                    , method: 'post'
                    , fileAccept: 'image/*'
                    , exts: "jpg|png|gif|bmp|jpeg|pdf"
                    , done: function (res) {
                            //上传完毕回调
                            $('#diy_pic_box').css('display','block');
                            $('#diy_prepic').attr('src', "/upload/" + res.data);
                            $('#diy_pic').val(res.data);
                    }
                    , error: function () {
                            //请求异常回调
                    }
                });
               
                //监听工艺一级下拉
                form.on('select(one)',function(data){
                    var html = "<option value=''>请选择</option>";
                    //一级下拉时，清楚其他级别的select内容
                    $('#three').html(html);
                    $('#four').html(html);
                    $('#five').html(html);
                    //三级，四级，五级默认隐藏
                    $('#tthree').css('display','none');
                    $('#tfour').css('display','none');
                    $('#tfive').css('display','none');
                    html += tech(data.value);
                    $('#two').html(html);  
                    form.render('select');  //更新渲染
                });
                //监听工艺二级下拉
                form.on('select(two)',function(data){
                    if(data.value!=''){
                        var html = "<option value=''>请选择</option>";
                        var res = tech(data.value);
                        //显示下一级
                        if(res.length!=''){
                            $('#tthree').css('display','inline-block');
                        }
                        html += tech(data.value);
                        $('#three').html(html);
                          //写入隐藏域的系列id
                        $('#series_id').val(data.value);
                        //添加颜色下拉数据
                        $('#aluminum_color').html(bcolor(1,data.value));
                        $('#flower_color').html(bcolor(2,data.value));
                        //添加纱网下拉数据
                        $('#yarn').html(yarn(data.value));
                        //添加五金下拉数据
                        $('#fived').html(five(data.value));
                        //添加把手位下拉数据
                        $('#hands').html(hands(data.value));
                        $('#five_price').val('');$('#five_count').val('');
                        form.render('select');  //更新渲染
                    }
                });
                 //监听工艺三级下拉
                form.on('select(three)',function(data){
                    $('#tfour').css('display','none');
                    if(data.value!=''){
                        var html = "<option value=''>请选择</option>";
                        var res = tech(data.value);
                        //显示下一级
                        if(res.length!=''){
                            $('#tfour').css('display','inline-block');
                        }
                        html += res;
                        $('#four').html(html);
                          //写入隐藏域的系列id
                        $('#series_id').val(data.value);
                        //添加颜色下拉数据
                        $('#aluminum_color').html(bcolor(1,data.value));
                        $('#flower_color').html(bcolor(2,data.value));
                        //添加纱网下拉数据
                        $('#yarn').html(yarn(data.value));
                        //添加五金下拉数据
                        $('#fived').html(five(data.value));
                        //添加把手位下拉数据
                        $('#hands').html(hands(data.value));
                        $('#five_price').val('');$('#five_count').val('');
                        form.render('select');  //更新渲染
                    }
                });
                 //监听工艺四级下拉
                form.on('select(four)',function(data){
                    $('#tfive').css('display','none');
                    if(data.value!=''){
                        var html = "<option value=''>请选择</option>";
                        var res = tech(data.value);
                        //显示下一级
                        if(res != ''){
                            $('#tfive').css('display','inline-block');
                        }
                        html += res;
                        
                        //花件显示或隐藏
                        var flower = findFlower(data.value);
                        if(flower){
                            $('#flower-boxd').css('display','block');
                        }else{
                            $('#flower-boxd').css('display','none');
                        }
                        $('#five').html(html);  
                        //写入隐藏域的系列id
                        $('#series_id').val(data.value);
                        //添加颜色下拉数据
                        $('#aluminum_color').html(bcolor(1,data.value));
                        $('#flower_color').html(bcolor(2,data.value));
                        //添加纱网下拉数据
                        $('#yarn').html(yarn(data.value));
                        //添加五金下拉数据
                        $('#fived').html(five(data.value));
                        //添加把手位下拉数据
                        $('#hands').html(hands(data.value));
                        $('#five_price').val('');$('#five_count').val('');
                        
                        form.render('select');  //更新渲染
                    }
                });
                //监听工艺五级下拉
                form.on('select(five)',function(data){
                    //花件显示或隐藏
                    var flower = findFlower(data.value);
                    if(flower){
                        $('#flower-boxd').css('display','block');
                    }else{
                        $('#flower-boxd').css('display','none');
                    }
                    
                   //写入隐藏域的系列id
                   $('#series_id').val(data.value);
                   //添加颜色下拉数据
                    $('#aluminum_color').html(bcolor(1,data.value));
                    $('#flower_color').html(bcolor(2,data.value));
                    //添加纱网下拉数据
                    $('#yarn').html(yarn(data.value));
                    //添加五金下拉数据
                        $('#fived').html(five(data.value));
                    //添加把手位下拉数据
                    $('#hands').html(hands(data.value));
                    form.render('select');  //更新渲染
                });
                
                //监听颜色联动
                form.on('select(color)',function(data){
                    var type = $(data.elem).attr('type'); //铝材颜色还是花件颜色
                    var select_text = $(data.elem).find('option:selected').text()
                    var two =  $(data.elem).parent().next();
                    var three = $(data.elem).parent().next().next();
                    var diy = $(data.elem).parent().nextAll('.diy-name');
                    var diy_price = $(data.elem).parent().nextAll('.diy-price');
                    
                    //花件颜色的自定义名称和价格
                    var _f_name = $(data.elem).parents('.layui-form-item').next().find('.diy-name');
                    var _f_price = $(data.elem).parents('.layui-form-item').next().find('.diy-price');
                    
                    two.css('display','none');
                    three.css('display','none');
                    diy.css('display','none');
                    diy_price.css('display','none');
                    _f_name.css('display','none');
                    _f_price.css('display','none');
                    
                    diy.find('input').val('');
                    diy_price.find('input').val('');
                    _f_name.find('input').val('');
                    _f_price.find('input').val('');
                    $.post("<?php echo url('order/colorTwo'); ?>",{id:data.value,series_id:$('#series_id').val(),type:type},function(obj){                         
                        var array = obj.data;   
                        var html = "<option value=''>请选择</option>";
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";                                                             
                        }      
                        $(data.elem).parent().next().find('select').html(html);
                        //如果是特殊烤漆,则显示自定义名称和自定义价格
                        if(data.value=='-1'){
                            diy.css('display','inline-block');
                            diy_price.css('display','inline-block');
                        }else if(array.is_self == 1){
                            diy.css('display','inline-block')
                        }else if(array!=''){                       
                            two.css('display','inline-block');
                        }  
                        
                        //同时更新花件颜色选中
                        if(type == 1){
                            $('.flower-select option').each(function(){
                                if($(this).text() == select_text){
                                    $(this).prop('selected',true);
                                }
                            })
                            
                            if(data.value=='-1'){
                                _f_name.css('display','inline-block');
                                _f_price.css('display','inline-block');
                            }else if(array.is_self == 1){
                                _f_name.css('display','inline-block')
                            }
                        }
                                               
                        form.render('select'); 
                    },'json'); 
                   
                })
                
                //监听颜色二级联动
                form.on('select(color_two)',function(data){
                    var type = $(data.elem).attr('type'); //铝材颜色还是花件颜色
                    var three = $(data.elem).parent().next();
                    if(type == 1){
                        var name = 'alum_color[]';
                    }else{
                        var name = 'flower_color[]';
                    }
                    if(data.value!=''){
                        $.post("<?php echo url('order/colorThree'); ?>",{id:data.value,series_id:$('#series_id').val(),type:type},function(obj){                         
                            var array = obj.data;     
                            var html = "<option value=''>请选择</option>";
                            for(i=0;i<array.length;i++){                              
                                html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";                                                             
                            }   
                            $(data.elem).parent().next().find('select').html(html);
                            if(array!=''){
                                three.css('display','inline-block');
                            }
                            form.render('select'); 
                        },'json'); 
                    }
                })
                
                $(function(){
                    $("input[name='alum_name']").change(function(){
                        $("input[name='flower_name']").val($(this).val());
                    })
                   
                })
                
                //监听纱网联动
                form.on('select(yarn)',function(data){
                    if(data.value!=''){
                        $.post("<?php echo url('order/yarnTwo'); ?>",{id:data.value,series_id:$('#series_id').val()},function(obj){                            
                           var array = obj.data;                           
                           var html = "<option value="+array['thickness']+">"+array['thickness']+"</option>";     
                           $('#yarn_two').html(html);
                           $('#yarn_price').val(array.price);
                           form.render('select');  
                       },'json'); 
                    }else{
                            $('#yarn_price').val(0);
                            var html = "<option value=''>0</option>";
                            $('#yarn_two').html(html);
                    }
                })
                
                //监听五金
                form.on('select(fived)',function(data){
                    if(data.value!=''){
                         var price = $(data.elem).find('option:selected').attr('price');
                         $('#five_price').val(price);
                    }
                })
                
                //根据新加窗型--隐藏显示飘窗面数，弧长等
                form.on('select(window_type_a)',function(data){
                    if(data.value == '常规'){
                        $('#window-box').css('display','none');$('#window-box').find('input').val('');
                        $('#arc-length-box').css('display','none');$('#arc-length-box').find('input').val('');
                        $('#arc-length-count-box').css('display','none');$('#arc-length-count-box').find('input').val('');
                    }else if(data.value == '飘窗'){
                        $('#window-box').css('display','inline-block');
                        $('#arc-length-box').css('display','none');$('#arc-length-box').find('input').val('');
                        $('#arc-length-count-box').css('display','none');$('#arc-length-count-box').find('input').val('');
                    }else{
                        $('#window-box').css('display','none');$('#window-box').find('input').val('');
                        $('#arc-length-box').css('display','inline-block');
                        $('#arc-length-count-box').css('display','inline-block');
                    }
                })
                
            });
            
            //颜色一级
            function bcolor(type,series_id){
                $.ajaxSetup({async : false});  
                var html = "<option value=''>请选择</option>";
                $.post("<?php echo url('order/color'); ?>",{type:type,series_id:series_id},function(obj){                         
                        var array = obj.data;     
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";                                                             
                        }      
                        html += "<option value='-1'>特殊烤漆</option>";
                    },'json'); 
                return html;
            }
            
            //纱网一级
            function yarn(series_id){
                $.ajaxSetup({async : false});  
                var html = "<option value=''>请选择</option>";
                $.post("<?php echo url('yarn'); ?>",{series_id:series_id},function(obj){                         
                        var array = obj.data;  
                        if(array.length<=0){
                            $('.yarn-box').css('display','none');
                        }else{
                            $('.yarn-box').css('display','block');
                        }
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['yarn_id']+">"+array[i]['name']+"</option>";                                                             
                        }      
                       
                    },'json'); 
                return html;
            }
            
            //把手位
            function hands(series_id){
                $.ajaxSetup({async : false});  
                var html = "<option value=''>请选择</option>";
                $.post("<?php echo url('hands'); ?>",{series_id:series_id},function(obj){                         
                        var array = obj.data;     
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['hands_id']+" width="+array[i]['width']+">"+array[i]['name']+"</option>";                                                             
                        }      
                       
                    },'json'); 
                return html;
            }
            //五金
            function five(series_id){
                $.ajaxSetup({async : false});  
                var html = "<option value=''>请选择</option>";
                $.post("<?php echo url('five'); ?>",{series_id:series_id},function(obj){  
                        var array = obj.data;
                        if(array.length<=0){
                            $('.five-box').css('display','none');
                        }else{
                            $('.five-box').css('display','block');
                        }    
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['five_id']+" price="+array[i]['price']+">"+array[i]['name']+"</option>";                                                             
                        }      
                       
                    },'json'); 
                return html;
            }
            //工艺多级联动，返回html
            function tech(pid){
                 $.ajaxSetup({async : false}); 
                 var html = '';
                 $.post("<?php echo url('order/technology'); ?>",{pid:pid},function(obj){                                                   
                        var array = obj.data;     
                        for(i=0;i<array.length;i++){                              
                            html += "<option value="+array[i]['id']+">"+array[i]['name']+"</option>";                                                             
                        }                           
                },'json'); 
                return html;
            }
            //查询当前工艺是否是带花的
            function findFlower(id){
                $.ajaxSetup({async : false}); 
                var find = true
                $.post("<?php echo url('findFlower'); ?>",{series_id:id},function(obj){                         
                      if(obj.code == 0){
                          find = true;
                      }else{
                          find =false;
                      }    
                       
                },'json'); 
                return find;
            }
            
            
            //弹出选择花件
            function show_flower(){
                layer.open({type: 2, content: '/admin/order/flower/id/'+$('#series_id').val()+'.html',area:['1000px','500px'],btn:['确定'],
                    shadeClose:true,
                    yes: function (index, layero) {
                        //引用弹出层的回调函数
                        var res = window["layui-layer-iframe" + index].callbackdata();
                        
                        $('#flower').val(res.name);
                        $('#flower_pic').val(res.pic);
                        $('#flower_id').val(res.id);
                        $('#flower_max_height').val(res.max_height);
                        $('#flower_min_height').val(res.min_height);
                        layer.close(index);
                    }});
            }

        //弹出选择花件
        function show_flowers(){
                layer.open({type: 2, content: '/admin/order/flower/id/'+$('#series_id').val()+'.html',area:['1000px','500px'],btn:['确定'],
                        shadeClose:true,
                        yes: function (index, layero) {
                                //引用弹出层的回调函数
                                var res = window["layui-layer-iframe" + index].callbackdata();
                                $('#flowers').val(res.name);
                                $('#flower_pics').val(res.pic);
                                $('#flower_ids').val(res.id);
                                $('#flower_max_heights').val(res.max_height);
                                $('#flower_min_heights').val(res.min_height);
                                layer.close(index);
                        }});
        }

               //弹出选择结构
            function show_structure(){
                layer.open({type: 2, 
                    content: '/admin/order/structure.html?id='+$('#series_id').val()+'&width='+$('#all_width').val()+'&height='+$('#all_height').val()+'&flywindow=\n\
        '+$('#flywindow').val()+'&arc='+$('#arc').val() +'&flower_max='+Number($('#flower_max_height').val())+'&flower_min='+Number($('#flower_min_height').val())+'&\n\
        spacing='+Number($('#spacing').val())+'&flower_id='+Number($('#flower_id').val())+'&flower_max_width='+$('#flower_max_width').val()+'&flower_min_width=\n\
        '+$('#flower_min_width').val()+'&hold_hands_width='+$('#hands option:selected').attr('width'),                                                          
                    area:['1000px','500px'],btn:['确定'],
                    shadeClose:true,
                    yes: function (index, layero) {
                        //引用弹出层的回调函数
                        var res = window["layui-layer-iframe" + index].callbackdata();
//                        console.log(res);
                        $('#structure_pic').attr('src',res.pic);
                        $('#structure').val(res.pic2);
                        $('#structure_id').val(res.structure_id);
                        $('#structure_id_box').text(res.structure_id);$('#structure_id_box').show();
                        //窗型
                        $('#window_type').val(res.window_type);
                        
                        //结构有固定则显示提示
                        if(res.fixed != '不带固定'){
                            $('.tips').css('display','block');
                            $('.fixed-name').text(res.fixed); //固定的类型
                            //固定高的最大值和最小值
                            var spacing_count = Number(res.spacing_count); //间距数
                            var frame_count = Number(res.frame_count); //边框数
                            var frame_thick = Number(res.frame_thick); //边框厚
                            var flower_max = Number($('#flower_max_height').val()); //花件最大高
                            var flower_min = Number($('#flower_min_height').val()); //花件最小高
                            var cmax = Number($('#all_height').val())-spacing_count*Number($('#spacing').val())-flower_min-frame_count*frame_thick;
                            var cmin = Number($('#all_height').val())-spacing_count*Number($('#spacing').val())-flower_max-frame_count*frame_thick;
//                            console.log(cmin+'--'+cmax);
                            if(res.fixed=='上固定' || res.fixed=='下固定'){
                                if(cmin>=130 & cmax>=130){
                                    
                                    $('#c-min').text(cmin);$('#c-max').text(cmax);
                                    $('#fixed-name').text(res.fixed);
                                }
                            }else if(res.fixed=='上下固定'){
                                if(cmin+cmax>=260){
                                    
                                    $('#c-min').text(cmin);$('#c-max').text(cmax);
                                    $('#fixed-name').text(res.fixed);
                                }
                            }                            
                        }else{
                            $('.tips').css('display','none');
                            $('#c-min').text(0);$('#c-max').text(0);
                        }
                        
                        //固定位动态显示
                        var html = '';
                        if(res.fixed == '上下固定'){
                            html = "<div class='layui-form-item'><label class='layui-form-label'><span class='x-red'>*</span>上固定</label>\n\
                        <div class='layui-input-inline'><input type='text' id='fixed' name='fixed[]' autocomplete='off' class='layui-input sfixed' lay-verify='fixed'></div>\n\
                        <div class='layui-form-mid layui-word-aux'>单位mm</div><input name='fixed_name[]' type='hidden' value='上固定' /></div>";
                            html += "<div class='layui-form-item'><label class='layui-form-label'><span class='x-red'>*</span>下固定</label>\n\
                        <div class='layui-input-inline'><input type='text' id='fixed' name='fixed[]' autocomplete='off' class='layui-input sfixed' lay-verify='fixed'></div>\n\
                        <div class='layui-form-mid layui-word-aux'>单位mm</div><input name='fixed_name[]' type='hidden' value='下固定'/></div>";
                        }else if(res.fixed != '不带固定'){
                            html = "<div class='layui-form-item'><label class='layui-form-label'><span class='x-red'>*</span>"+res.fixed+"</label>\n\
                        <div class='layui-input-inline'><input type='text' id='fixed' name='fixed[]' autocomplete='off' class='layui-input sfixed'></div>\n\
                        <div class='layui-form-mid layui-word-aux'>单位mm</div><input name='fixed_name[]' type='hidden' value='"+res.fixed+"'/></div>";
                        }
                        $('#fixed-box').html(html);
                        //把手位和锁位高动态显示
                        if(res.hands == '无把手'){
//                            $('#hands-box').css('display','none');
                            $('#lock_position_box').css('display','none');
                        }else{
//                             $('#hands-box').css('display','block');
                             $('#lock_position_box').css('display','block');
                        }
                        layer.close(index);
                    }});
            }
            //弹出查看图片
            function show_pic(){
                var series_id = $('#series_id').val();
                var flower = $('#flower_pic').val();
                var structure_id = $('#structure_id').val();
                var alum_one = $("#aluminum_color option:selected").val();
                var alum_two = $("#aluminum_two option:selected").val();
                var alum_three = $("#aluminum_three option:selected").val();
                var flower_one = $("#flower_color option:selected").val();
                var flower_two = $('#flower_two option:selected').val();
                var flower_three = $('#flower_three option:selected').val();
                var url = "series_id="+series_id+"&flower="+flower+"&alum_color[]="+alum_one+"&alum_color[]="+alum_two+"&alum_color[]="+alum_three+"&flower_color[]=\n\
                           "+flower_one+"&flower_color[]="+flower_two+"&flower_color[]="+flower_three+"&structure_id="+structure_id;
                xadmin.open('查看图片', '/admin/order/pic/?'+url, 800, 550);
            }
        </script>
        <script>layui.use(['form', 'layer'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;

                //监听表单提交
                form.on('submit(add)',
                function(data) {
                    var field = data.field;
                    var yarn_text = $('#yarn').find('option:selected').text(); //纱网text
                    var hands_text = $('#hands-box').find('option:selected').text(); //把手位text
                    field.hands_text = hands_text;
                    field.yarn_text = yarn_text;
                    field.name = $('#two option:selected').text();
                    field.tips_min = $('#c-min').text();
                    field.tips_max = $('#c-max').text();
                    field.tips_fixed = $('.fixed-name').text();
                    
                    var temp = 0;
                    var cmin = $('#c-min').text(); //上下固定最小值
                    var cmax = $('#c-max').text(); //上下固定最大值
                    var fixed = $('.fixed-name').text();//固定名称
                    $('.sfixed').each(function(){
                        temp += Number($(this).val());
                    })
//                  if(temp>cmax || temp<cmin){
//                      var tips = '固定高值范围:'+cmin+'≤'+fixed+'≤'+cmax+';';
//                      layer.msg(tips,{icon:2})
////                        layer.confirm(tips,function(){
////                            ajaxform("<?php echo url('addProduct'); ?>",field);
////                            return false;
////                        })
//                      return false;
//                  }

                    ajaxform("<?php echo url('addProduct'); ?>",field);
                    return false;
                });
                
            });
          
         
        </script>

    </body>

</html>