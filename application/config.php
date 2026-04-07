<?php

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'admin',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否开启路由解析缓存
    'route_check_cache'      => false,
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],
	//默认上传文件路径
	'upload'=>'./upload/',
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
        'auto_rule'    => 1,
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => true,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'test',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 5,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],
    //密码加密key
    'password_key' => 'r3L1DH0qcajONQC5GV',
    
    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
    //物料绑定关系
    'series_bom' => [
        '1' => '边框',
        '2' => '外框',
        '3' => '内框',
        '4' => '中横',
        '5' => '扇',
        '6' => '小门框',
        '7' => '面管',
        '8' => '立柱',
        '9' => '横杆',
        '10' => '内竖杆',
        '11' => '上中横',
		'12' => '下中横',
		'13' => '窄边框',
		'14' => '窄内框',
		'15' => '窄外框',
		'16' => '纱压线',
    ],
    //订单类型
    'order_type' => [
        '1' => '常规',
        '2' => '加急',
        '3' => '样板',
        '4' => '返修单',
        '5' => '单剪网',
        '6' => '单切料',
        '7' => '工程',
        '8' => '重做',
        '9' => '样板2',
		'10' => '样版间'
    ],
    //公式-文字对应关系 (可查询下单时用户添加的数据)
    'calculate_setting' => [
        '总宽' => '$W',
        '总高' => '$H',
        '横间距' => '$S',
        '竖间距' => '$BS',
        '大横间距' => '$S',
        '大竖间距' => '$BS',
        '右竖间距' => '$RBS',
        '中竖间距' => '$CRS',
        '中左竖间距' => '$MLRS',
        '中右竖间距' => '$MRRS',
        '下固定高' => '$GBH',
        '上固定高' => '$GTH',
        '锁位高' => '$LPH',
        '执手高' => '$LH',
        '执手宽' => '$LW',
        '弧高' => '$RH',
        '左到中' => '$LEFTM',
        '右到中' => '$RIGHTM',
        '中到左' => '$MTL',
        '中到右' => '$MTR',
        '下固横间距' => '$BFS',
        '下固竖间距' => '$BVS'
    ],
    //公式字母对应的 数据表字段
    'calculate_field' => [
        '$W' => 'all_width',
        '$H' => 'all_height',
        '$S' => 'spacing',
        '$BS' => 'bottom_spacing',
        '$LA'=> 'take',
        '$LPH'=> 'lock_position',
        '$RH' => 'arc_height',
        '$GTH'=>'top_fixed',
        '$GBH'=>'bottom_fixed',
        '$LH'=>'hands_height',
        '$LW'=>'hands_width',
        '$RBS' => 'right_bottom_spacing',
        '$LEFTM' => 'left_to_middle',
        '$RIGHTM' => 'right_to_middle',
        '$BFS' => 'bottom_fixed_spacing',
        '$BVS' => 'bottom_vertical_spacing',
        '$CRS' => 'center_row',
        '$MLRS' => 'center_left',
        '$MRRS' => 'center_right',
        '$MTL' => 'center_to_left',
        '$MTR' => 'center_to_right',
		'$waikuangbian' => 'waikuangbian',
		'$shawangbian' => 'shawangbian',
		'$menshanbian' => 'menshanbian',
//       '$GBH'=> ''
    ],
    //系列中的物料绑定  公式-文字对应关系 (查询物料池中数据)
    'calculate_bom' => [
        '边框' => '$F',         
        '外框' => '$FRAME',
        '内框' => '$W_FRAME',
        '中横' => '$ZH',
        '扇宽'   => '$SAN',
        '小门框' => '$X_FRAME',
        '面管' => '$FACE_GUANG',
        '立柱' => '$LIZHU',
        '横杆' => '$HENGGAN',
        '内竖杆' => '$NEISHU',
        '框搭框' => '$LA',
        '框搭扇' => '$FEAME_TAKE_FAN',
        '小门框搭框' => '$SMALL_FRAME',
        '小门框搭扇' => '$SMALL_FAN',
        '上中横' => '$ZH_T',
		'下中横' => '$ZH_B',
		'窄边框' => '$ZK_B',
		'窄内框' => '$ZK_N',
		'窄外框' => '$ZK_W',
		'窄边框搭框' => '$ZK_B_KDK',
		'窄外框搭扇' => '$ZK_B_KDS',
		'纱压线' => '$sha_ya_xian',
		'外框边' => '$waikuangbian',
		'纱网边' => '$shawangbian',
		'门扇边' => '$menshanbian',
    ],
    //系列物料绑定 名称对应的type
    'bom_type' => [
        '$F' => 1, 
//        '$LA' => 2,
        '$FRAME' => 2,
        '$W_FRAME' => 3,
        '$ZH' => 4,
        '$SAN'   => 5,
        '$X_FRAME' => 6,
        '$FACE_GUANG' => 7,
        '$LIZHU' => 8,
        '$HENGGAN' => 9,
        '$NEISHU' => 10,
        '$ZH_T' => 11,
        '$ZH_B' => 12,
		'$ZK_B' => 13,
		'$ZK_N' => 14,
		'$ZK_W' => 15,
		'$sha_ya_xian' => 16,
    ],
    //系列物料绑定 用户填写的数据
    'bom_write' => [
        '$LA' => 'take',
        '$FEAME_TAKE_FAN' => 'frame_take_fan',
        '$SMALL_FRAME' => 'small_frame',
        '$SMALL_FAN' => 'small_fan',
        '$ZK_B_KDK' => 'ZK_B_KDK',
		'$ZK_B_KDS' => 'ZK_B_KDS',
		'$waikuangbian' => 'waikuangbian',
		'$shawangbian' => 'shawangbian',
		'$menshanbian' => 'menshanbian',
    ],
    //花件-文字对应关系
    'flower_bom' => [
        '花件最大宽' => '$FW',
        '花件最大高' => '$FH'
    ],
    'flower_field' => [
        '$FW' => 'max_width',
        '$FH' => 'max_height',
    ],
    //订单收款方式
    'pay_type' => [
        '0' => '未收款',
        '1' => '现金',
        '2' => '公微',
        '3' => '支付宝',
        '4' => '喜仕多',
        '5' => '刷卡',
        '6' => '银行转账',
        '7' => '佛山收款',
        '8' => '农商行'
    ],
    //配送单状态
    'send_status' => [
        '0' => '待配送',
        '1' => '配送中',
        '2' => '已配送'
    ],
    //订单状态
    'order_status' => [
        '1' => '已报价',
        '2' => '财务处理',
        '3' => '待生产',
        '4' => '生产中',
        '7' => '已入库'
        
    ],
    //订单状态
    'order_status2' => [
        '1' => '已报价',
        '2' => '车间填写数据',
        '3' => '营运填写数据',
        '4' => '财务处理',
        '5' => '待生产',
        '6' => '生产中',
        '7' => '已入库'
    ]
];
