<?php
//子系统项目目录路径常量PATH_67
define('PATH_67', str_replace('\\', '/', __DIR__));
//控制器目录路径常量CONTROLLER_PATH
define('CONTROLLER_PATH', __DIR__ . '/controllers');
//define('CONTROLLER_PATH', PATH_67 . '/controllers');
//缓存目录路径CACHE_67
define('CACHE_67', PATH_67 . '/cache');
//日志数据目录路径LOG_67
define('LOG_67', CACHE_67 . '/log');
//临时文件目录路径TMP_67
define('TMP_67', CACHE_67 . '/tmp');
//定义ADMIN_67常量
define('ADMIN_67', true);
//时间戳常量
define('TIMESTAMP', time());

//判断路径是否为获取系统版本号[/index/version]
//$uri = !empty($_SERVER['argv'][1]) && '/index/version' == $_SERVER['argv'][1];
$V = !empty($_SERVER['argv'][1]) && '/index/version' == $_SERVER['argv'][1];
//如果不获取系统版本号，则需要加载所需的配置文件
if(!$V){
//加载配置文件(相应地域名配置信息)
$c = include __DIR__ . '/config/common/domain.php';
//$config = include __DIR__ . '/config/common/domain.php';

//获取发起请求(执行脚本的参数)的主机域名
$host = !empty($_REQUEST['domain']) ?
    $_REQUEST['domain'] : (!empty($_SERVER['argv'][2]) ?
        $_SERVER['argv'][2] : explode(':', $_SERVER['HTTP_HOST'][0]));
//只取顶级域名[array_slice()从数组中取出一段]
$host = implode('.', array_slice(explode('.', $host), -2));

//取对应顶级域名的配置
$D = !empty($c['domains'][$host]) ?
    $c['domains'][$host] : $c['domains'][''];
/*$domain = !empty($config['domains'][$host]) ?
    $config['domains'][$host] : $config['domains'][''];*/
//域名常量DOMAIN_67
defined('DOMAIN_67') or define('DOMAIN_67', $D['domain']);

//子系统标识常量SYSTEM_67
define('SYSTEM_67', 'user');
//文档地址常量DOC_67
define('DOC_67', $c['doc']);
//缓存头部标识常量CACHE_PREFIX
define('CACHE_PREFIX', DOMAIN_67 . ':USER|');
}

//加载系统初始化文件
require __DIR__ . '/init.php';
//require[require_once] PATH_67 . '/init.php';
//如果uri是获取系统版本号，则输出系统版本号[命令行可执行成功],并直接结束运行
if($V){
    echo VERSION_67/*该常量在init.php中*/;
    exit;
}
//将配置信息的域名(映射域名)信息赋值给公共类(容器类)C67的变量$domain
C67::$domain = $D['mapping'];
//加载公共的配置文件[config.php]，其内容为配置了各个子系统的基本信息(名字、映射的域名、icon)
C67::init_config('common');
//加载当前子系统的配置文件[config.php]，配置了相应的数据库信息、redis(缓存服务器)信息和clickhouse信息。
C67::init_config();

//初始化当前子系统的数据库，并赋值给容器类的变量$DB
C67::$DB = C67::init_db(SYSTEM_67);
//配置cookies服务器
if(DOMAIN_67 && $D){
    $CONFIG['cookie_domain'] = $D['cookie_domain'];
}else{
    $CONFIG['cookie_domain'] = _DOMAIN_/*67.com*/;
}
//设置默认时区
date_default_timezone_set('Asia/Shanghai');

//获取缓存的配置文件
$CONFIG = C67::get_cache('CONFIG', 'file');
//合并所有配置信息
$CONFIG = array_merge($CONFIG, C67::$CONF);
//同步所有配置到容器类的变量$CONFIG
C67::$CONFIG = &$CONFIG;

/**
//加载Smarty模板引擎
require_once CORE_67 . '/class/smarty/Smarty.class.php';
$tpl = new Smarty;
C67::$tpl = &$tpl;
//配置编译好文件的存放路径
$tpl->compile_dir = CACHE_67 .'/templates_c';
//配置缓存目录(临时文件)
$tpl->cache_dir = TMP_67;
//配置左定界符
$tpl->left_delimiter = '{{';
//配置右定界符
$tpl->right_delimiter = '}}';
//配置html模板目录
$tpl->template_dir = PATH_67 .'/view';
//模板中引用配置信息
$tpl->assign_by_ref('CONFIG', $CONFIG);

//设置网关
Controller_67::set_gateway('');
//动态注册模板函数插件
//注册Controller_67的smarty_R的function
$tpl->register_function('R', ['Controller_67', 'smarty_R']);
//注册Controller_67的smarty_widgets的function
$tpl->register_function('widgets', ['Controller_67', 'smarty_widgets']);
**/

Controller_67::init_template([
    'dir' => [PATH_67 . '/view']
]);

//读取会话的USER_SESS_ID
session_name('USER_SESS_ID');

//将CGI进程提前结束，并记录用户操作日志
C67::shutdown_function(['Controller_67', 'admin_log']);
//配置最高权限
//define('TMP_GOD', true);

//设置头部
header('Content-type: text/html; charset=utf-8');

//执行路由
Controller_67::route();
