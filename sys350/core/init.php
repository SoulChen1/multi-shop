<?php
/*!encrypt*/
//查询是否定义了PATH_67、CACHE_67、TMP_67和TIMESTAMP的常量
//如果没有定义，则退出程序并返回错误信息
defined('PATH_67') or die('define PATH_67');
defined('CACHE_67') or die('define CACHE_67');
defined('TMP_67') or die('define TMP_67');
defined('TIMESTAMP') or die('define TIMESTAMP');

//设置系统的版本号
define('VERSION_67', '1.0.0');
//定义核心库目录路径的常量CORE_67
define('CORE_67', str_replace('\\', '/', __DIR__));

//定义空字符常量ASCII0
define('ASCII0', chr(0));
//获取系统配置的GPC操作(GET/POST/Cookie)状态[PHP5.4.0起始终返回false]
//当magic_quotes为on，所有的'(单引号)、"(双引号)、\(反斜杠)和NUL's被一个反斜杠自动转义
define('MAGIC_QUOTES', get_magic_quotes_gpc());
//定义DEV_SHM常量
define('DEV_SHM', '/dev/shm/67_serv');
//获取接收到的请求方法(GET/POST/PUT/DELETE)
define('REQUEST_METHOD', strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''));
//获取用户移动设备的UA值
define('USER_AGENT', isset($_COOKIE['UA']) ? $_COOKIE['UA'] :
    (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL));
//用户是否为移动设备
define('MOBILE_67', stripos(USER_AGENT, 'mobile') !== false);
//获取服务器中的用户代理
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
//判断当前请求是否为AJAX请求
define('AJAX_67', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_REQUEST['_ajax']));
//定义_DOMAIN_常量，默认的域名常量[用于默认的cookie服务器]
define('_DOMAIN_', '67.com');
//判断当前请求是否有HTTPS
define('HTTPS', !empty($_SERVER['HTTPS']) ||
    (
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        !empty($_SERVER['HTTP_X_FORWARDED_PORT']) &&
        'https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        443 == $_SERVER['HTTP_X_FORWARDED_PORT']
    )
);

//当有请求时，获取请求的URL并定义为常量URL_67
if(REQUEST_METHOD){
    define(
        'URL_67',
        'http' . (HTTPS ? 's' : '') . '://'.
            $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    );
}
/**
 * 以下都是公共的方法
 */
//获取IP地址
function get_ip($proxy_first = false/*优先获取代理服务器IP*/){
    $ip = '';
    $envs = ['HTTP_ALI_REAL_IP'/*获取ALI真实IP*/, 'HTTP_CDN_SRC_IP'/*获取CDN服务器IP*/, 'REMOTE_ADDR'/*发起请求的IP地址*/, 'HTTP_CLIENT_IP'/*客户端IP*/, 'HTTP_X_FORWARDER_FOR'/*请求IP*/];
    if($proxy_first){
        //将第一个元素移出数组后，插入数组的最后一位
        $tmp = array_shift($envs);
        $envs[] = $tmp;
    }
    foreach($envs as $env){
        //获取环境变量值
        $environment = getenv($env);

        if('REMOTE_ADDR' == $env && !$environment && !empty($_SERVER[$env])){
            $environment = $_SERVER[$env];
        }
        if($environment && strcasecmp($environment, 'unknown')/*比较字符串是否为unknown*/){
            $ip = $environment;
            break;
        }
    }

    preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip, $m);/*匹配正则表达式*/
    $ip = isset($m[0]) ? $m[0]/*包含完整的匹配结果*/ : 'unknown';
    return $ip;
}
//获取IP地址，并设为常量IP_67
define('IP_67', get_ip());

//将目录的路径有效化(规范化)
function valid_path($path){
    return str_replace(['\\', ASCII0], ['/', ''], $path);
}
//规范化的绝对路径
function real_path($path){
    $path = valid_path(realpath($path));
    return is_dir($path) && substr($path, -1) != '/' ? $path.'/' : $path;
}
//删除文件
function rm($file, $flag = false, $ignores = []){
    $file = real_path($file);
    if(!strlen($file)) return false;//空文件不执行

    //防止删除项目全部文件
    if($file == PATH_67 . '/') return false;

    //删除文件或文件夹
    if(is_file($file)){
        return @unlink($file);
    }else{
        $handle = opendir($file);
        while(($item = readdir($handle)) !== false){
            if($item == '.' || $item == '..' || isset($ignoresp[$item])) continue;

            rm($file . $item, false, $ignores);
        }
        closerdir($handle);

        return $flag ? true : rmdir($file);
    }
}
//格式化输出数据
function pr($data){
    echo '<pre>';
    print_r($data);
}
//深度反转义字符方法，可反转义数组内的数据
function stripslashes_deep($value){
    //array_map为数组的每个元素应用回调函数
    $value = is_array($value) ?
        array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}
//php是否配置自动反转义，如果无，则手动反转义
function magic_stripslashes($value){
    if(! MAGIC_QUOTES) return $value;
    return stripslashes_deep($value);
}
//将字符串MD5加密后取16位
function md5_16($str){
    return substr(md5($str), 8, 16);
}
//生成唯一随机数
//默认32位的UUID(随机的唯一数)
function unique_id($bit = 32){
    $id = uniqid(mt_rand(), true);
    return $bit == 32 ? md5($id) : md5_16($id);
}
//密码评分
function password_score($string, $len = 6){
    $h = 0;
    $size = strlen($string);

    //密码需要大于6位
    if($size < $len){
        return 0;
    }
    //筛选并计算字符数，最后统计得分
    foreach(count_chars($string, 1) as $v){
        $p = $v / $size;
        $h -= $p * log($p) / log(2);
    }
    //得出最后分数
    $score = ($h / 4) * 100;
    if($score > 100){
        $score = 100;
    }

    return $score;
}
//自定义替换字符方法，包括指定字符编码'utf-8'
function mb_substr_replace($string, $replace, $start, $length = 0){
    $charset = 'utf-8';
    $strLength = mb_strlen($string, $charset);//根据字符编码来计算字符串的长度
    $result = [];
    $tag = 0;
    $replaced = false;
    for($i = 0; $i < $strLength; $i++){
        if(!$replaced && $i >= $start){
            if(++$tag == $length){
                $sub = $replace;
                $replaced = true;
            }else{
                continue;
            }
        }else{
            $sub = mb_substr($string, $i, 1, $charset);
        }
        $result[] = $sub;
    }
    return implode('', $result);
}
//检查验证码
function captcha($str, $test = false/*是否为测试*/){
    if(empty($str) || empty($_SESSION['CAPTCHA'])) return false;

    $code = $_SESSION['CAPTCHA']['value'];
    if(!$test || ++$_SESSION['CAPTCHA']['time'] > 3){
        unset($_SESSION['CAPTCHA']);
    }
    return $code == $str;
}
//转换widget组件的数据格式
function convert_widget_data($data, /*!source{*/ $id = 'k', $name = 'v',/*!source}*/ $sub_key = '') {
    $result = [];
    foreach($data as /*!source{*/ $k => $v /*!source}*/){
        $text = /*!source{*/ $$name; /*!source}*/;
        $result[] = [
            'id' => /*!source{*/ $$id, /*!source}*/
            'name' => $sub_key ? $text[$sub_key] : $text,
        ];
    }
    return $result;
}
//分页功能
function list_page($param){
    $item_count = $param['count'];
    $current_page = $param['page'];
    $page_size = $param['page_size'] ? $param['page_size'] : 1;
    $page_url = $param['url'];

    $layout_size = isset($param['layout_size']) ? $param['layout_size'] : 10;
    //默认模板
    if(empty($param['template'])){
        $template = '{item_count}条{page}{/pages}页
        {first_page:<a href="$link">首页</a>|default:<span>首页</span>>}
        {prev_layout:<a href="$link">&lt;&lt;</a>|default:<span>&lt;&lt;</span>}
        {prev_page:<a href="$link">上一页</a>|default:<span>上一页</span>}
        {pages: <a href="$link">$page</a> |default: <a href="###">$page</a> }
        {next_page:<a href="$link">下一页</a>|default:<span>下一页</span>}
        {next_layout:<a href="$link">&gt;&gt;</a>|default:<span>&gt;&gt;</span>}
        {last_page:<a href="$link">尾页</a>|default:<span>尾页</span>}
        {goto}';
    }else{
        $template = $param['template'];
    }
    static $page_id = [0];
    if($item_count == 0){
        return '';
    }

    $tmp = $template;
    //包围之间是页码可选部分，第1页不显示
    $pageable = preg_replace('/#([^#]+)#/', '\1', $page_url);
    $nopage = preg_replace('/#([^#]+)#/', '', $page_url);

    $pages = ceil($item_count / $page_size);
    $layouts = @ceil($pages / $layout_size);
    //当前页码版面
    $layout = @floor(($current_page - 1) / $layout_size) * $layout_size;
    if($current_page > $layout_size / 2){
        $page_offset = $current_page - $layout;
        $layout += ceil($page_offset - $layout_size / 2);
    }

    $tmp = str_replace('{page}', $current_page, $tmp);      //当前页
    $tmp = str_replace('{pages}', $pages, $tmp);            //总页数
    $tmp = str_replace('{item_count}', $item_count, $tmp);  //记录数
    $s = '';

    //首页
    if(preg_match('/\{first_page:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', 1, $nopage);
        isset($param['return_link']) && $param['return_link']['first_page'] = $link;

        if($current_page != 1){
            $s = str_replace('$link', $link, $m[1]);
        }else{
            $s = $m[2];
        }
        $tmp = preg_replace('/\{first_page:[^\}]+\}/', $s, $tmp);
    }

    //上一版面
    if(preg_match('/\{prev_layout:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', $layout - $layout_size + 1, $layout - $layout_size + 1 == 1 ? $nopage : $pageable);
        isset($param['return_link']) && $param['return_link']['prev_layout'] = $link;

        if($layout - $layout_size + 1 < 1){
            $s = $m[2];
        }else{
            $s = str_replace('$link', $link, $m[1]);
        }
        $tmp = preg_replace('/\{prev_layout:[^\}]+\}/', $s, $tmp);
    }

    //上一页
    if(preg_match('/\{prev_page:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', $current_page - 1, $current_page - 1 == 1 ? $nopage : $pageable);
        isset($param['return_link']) && $param['return_link']['prev_page'] = $link;

        if($current_page -1 < 1){
            $s = $m[2];
        }else{
            $s = str_replace('$link', $link, $m[1]);
        }
        $tmp = preg_replace('/\{prev_page:[^\}]+\}/', $s, $tmp);
    }

    //下一页
    if(preg_match('/\{next_page:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', $current_page+1, $pageable);
        isset($param['return_link']) && $param['return_link']['next_page'] = $link;

        if($current_page + 1 > $pages){
            $s = $m[2];
        }else{
            $s = str_replace('$link', $link, $m[1]);
        }
        $tmp = preg_replace('/\{next_page:[^\}]+\}/', $s, $tmp);
    }

    //下一版面
    if(preg_match('/\{next_layout:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', $layout + $layout_size + 1, $pageable);
        isset($param['return_link']) && $param['return_link']['next_layout'] = $link;

        if($layout + $layout_size + 1 > $pages){
            $s = $m[2];
        }else{
            $s = str_replace('$link', $link, $m[1]);
        }
        $tmp = preg_replace('/\{next_layout:[^\}]+\}/', $s, $tmp);
    }

    //末页
    if(preg_match('/\{last_page:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $link = str_replace('?page?', $pages, $pageable);
        isset($param['return_link']) && $param['return_link']['last_page'] = $link;

        if($current_page != $pages){
            $s = str_replace('$link', $link, $m[1]);
        }else{
            $s = $m[2];
        }
        $tmp = preg_replace('/\{last_page:[^\}]+\}/', $s, $tmp);
    }

    //各页
    if(preg_match('/\{pages:([^\}]+)(?:\|default:)([^\}]+)\}/', $template, $m)){
        $s = '';
        $count = $layout_size ? $layout_size : $pages;
        for($i = 1; $i <= $count; $i++){
            $page = $layout + $i;
            if($page > $pages) break;

            $link = str_replace('?page?', $page, $page == 1 ? $nopage : $pageable);
            isset($param['return_link']) && $param['return_link']['pages'][$page] = $link;

            if($current_page != $page){
                $s .= str_replace(
                    ['$link', '$page'],
                    [$link, $page],
                    $m[1]
                );
            }else{
                $s .= str_replace(
                    ['$link', '$page'],
                    [$link, $page],
                    $m[2]
                );

                isset($param['return_link']) && $param['return_link']['pages'][$page] = $link;
            }
        }
        $tmp = preg_replace('/\{pages:[^\}]+\}/', $s, $tmp);
    }

    //生成随机ID
    $id = 0;
    while(!$id){
        $id = mt_rand(1, 10000);
        $id = isset($page_id[$id]) ? 0 : $id;
    }
    $page_id[$id] = 1;
    $id = '__page'. $id;

    //跳转JS
    $goto = '<script type="text/javascript">var '. $id .' = {url:\''. $page_url .'\',pages:'. $pages .'};</script>'.
        '<input type="text" value="'. $current_page .'" id="p'. $id .'" size="3" '.
        'onkeyup="var p=this.value.replace(/[^0-9]/g,\'\');if(p!=\'\'){'.
        'p=Math.max(p,1);p=Math.min(p,'. $id .'.pages);}this.value=p;" '.
        'onkeydown="if(event.keyCode==13){'.
        'var p=this.value;p=Math.min(p,'. $id .'.pages);p=Math.max(1,p);if(p == '. $current_page .')return;'.
        'if(p==1){'.
        $id .'.url='. $id .'.url.replace(/#([^#]+)#/, \'\');'.
        '}else{'.
        $id .'.url='. $id .'.url.replace(/#([^#]+)#/, \'$1\');'.
        '}'.
        'if(pos='. $id .'.url.indexOf(\'javascript:\')!=-1){'.
        'eval('. $id .'.url.substr(pos).replace(\'?page?\',p));'.
        '}else{'.
        'window.location.href='. $id .'.url.replace(\'?page?\',p);'.
        '}'.
        'return false;}" />';

    $tmp = str_replace('{goto}', $goto, $tmp);
    unset($template, $s, $id, $m);
    return $tmp;
}
//对身份证号码进行分析，提取出生年月日和性别
function parse_id_card($id){
    if(empty($id) || 18 != strlen($id)){
        return [];
    }

    $result = [
        'Y' => substr($id, 6, 4),
        'm' => substr($id, 10, 2),
        'd' => substr($id, 12, 2),
        'sex' => substr($id, 16, 1) % 2 != 0 ? 1 : 0
    ];
    $result['age'] = C67::date('Y') - $result['Y'];
    return $result;
}
/**
 *  系统统一的异常处理
 */
//错误提示或错误日志记录
function _log_error($type, $errno, $errstr, $file, $line, $trace){
    $debug = '127.0.0.1' == IP_67;
    if(
        !$debug && !in_array($errno, [403, 404])
    ){
        $uri = REQUEST_METHOD ?
            explode('?', $_SERVER['REQUEST_URI'][0]) : $_SERVER['argv'][1];
        $str = "$uri\n$file: $line\n$errno: $errstr\n";
        foreach($trace as $v){
            $v['file'] = isset($v['file']) ? $v['file'] : '';
            $v['line'] = isset($v['line']) ? $v['line'] : '';
            $str .= "$v[file]: $v[line]\n";
        }
        C67::unity_logger('error', $str);
    }

    $clean = !REQUEST_METHOD || AJAX_67 || !empty($_REQUEST['json']);

    if(!defined('ADMIN_67') && REQUEST_METHOD){
        if(!$debug){
            return;
        }
    }
    if(!($fileOpen = fopen($file, 'r'))){
        return;
    }

    $code = '';
    $l = 0;
    while($fline = fgets($fileOpen)){
        if($l++ > $l + 10){
            break;
        }

        if(($minus = abs($l - $line)) < 10){
            $hcode = $fline;
            $hcode = str_replace(
                '<span style="color: #0000BB">&lt;?php<br />',
                '<span style="display: inline-block; width: 50px;">'. $l .'</span>',
                highlight_string("<?php\n". $hcode, true)
            );
            if(0 == $minus){
                $hcode = '<span style="background-color: yellow;">'. $hcode .'</span>';
            }
            !$clean && $code .= $hcode;
        }
    }

    if(!$clean){
        echo '<div style="border: 1px solid #000; padding: 5px;">';
        echo "<h4>$type $errno: $errstr</h4><ul>";
    }else{
        echo "$type $errno: $errstr\n";
    }
    $ide = defined('IDE_67') ? IDE_67 : '';
    foreach($trace as $v){
        $v['file'] = isset($v['file']) ? $v['file'] : '';
        $v['line'] = isset($v['line']) ? $v['line'] : '';
        $real = real_path($v['file']);
        $v['file'] = str_replace(
            [CORE_67, PATH_67],
            ['CORE', ''],
            $real
        );
        if(!$clean){
            $str = "$v[file]: $v[line]";
            if('127.0.0.1' == IP_67){
                $real = str_replace(
                    ['{1}', '{2}'],
                    [$real, $v['line']],
                    $ide
                );
                $str = '<a href="'. $real .'">'. $str .'</a>';
            }
            echo "<li>$str</li>";
        }else{
            echo "# $v[file]: $v[line]\n";
        }
    }

    $real = real_path($file);
    $file = str_replace(
        [CORE_67, PATH_67],
        ['CORE', ''],
        $real
    );
    if(!$clean){
        $str = "$file: $line";
        if('127.0.0.1' == IP_67){
            $real = str_replace(
                ['{1}', '{2}'],
                [$real, $line],
                $ide
            );
            $str = '<a href="'. $real .'">'. $str .'</a>';
        }
        echo "<li><b>$str</b></li>";
    }else{
        echo "# $file: $line\n";
    }

    if(!$clean){
        echo "</ul>$code</div>";
    }
}

//系统错误处理器
function _error_handler($errno, $errstr, $file, $line, $context = []){
    if(in_array($errno, [2, 8, 2048])){
        return;
    }

    _log_error('error', $errno, $errstr, $file, $line, debug_backtrace(true));
}

//程序异常处理器
function _exception_handle($e){
    $errno = $e->getCode();

    _log_error('exception', $errno, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
}
//设置用户自定义的错误处理函数
set_error_handler('_error_handler');
//设置用户自定义的异常处理函数
set_exception_handler('_exception_handle');


/**
 * 以下包含公共类、控制器父类、Redis类、module类
*/
//公共方法类(容器类)
class C67{
    const CACHE_PREFIX = CACHE_PREFIX;/*缓存的首部信息*/

    public static $cache/*配置缓存redis或memcache*/,
        $db/*未使用变量*/,
        $DB/*当前系统的数据库实例对象*/,
        $adb/*未使用变量*/,
        $tpl/*Smarty模板引擎的实例,在index.php中已经实例*/,
        $CONF = []/*加载系统的基本配置，一般为config配置文件中的配置信息*/,
        $CONFIG = []/*系统运行所需配置信息(包含系统的基本配置信息$CONF)*/,
        $VAR = []/*未使用变量*/,
        $LANG = [],
        $dbs = []/*存储初始化过的数据库*/,
        $domain = ''/*访问系统的域名*/;

    protected static $_modules = []/*已经加载过的模型类*/,
        $_controllers = []/*已经加载过的控制器类*/,
        $_log_fp = []/*打开过的文件*/,
        $_redis_cache/*是否为redis缓存*/;

    //初始化缓存机制
    public static function init_cache(){
        //避免重复初始化
        if(static::$cache !== null){
            return;
        }

        if('redis' == C67::$CONF['cache']){
            self::_redis();
        }else{
            self::_memcache();
        }
    }

    //初始化memcache缓存服务器
    protected static function _memcache(){
        //将memcache实例对象赋值给$cache
        static::$cache = new memcache();
        $fail_callback = ['C67', '_memcache_fail'];
        foreach(self::$CONF['memcache_servers'] as $server){
            //向连接池中添加一个memcache服务器addServer($host, $port, $persistent, $weight, $timeout, $retry_interval, $status, $failure_callback)
            static::$cache->addServer($server['host'], $server['port'], true/*持久化连接*/, 1/*服务器桶数*/, 2, 15, true, $fail_callback);
        }
        //开启大值自动压缩
        static::$cache->setCompressThreshold(10240/*自动压缩的阈值*/, 0.2/*压缩率*/);
    }

    //memcache错误回调函数
    public static function _memcache_fail($host, $port){
        //将错误信息写入日志
        //可以改为统一日志，不过线上不使用memcache缓存服务器
        self::logger(
            'sql_error/memcache-' . C67::date('Y-m-d'),
            "$host:$port"
        );
    }

    //初始化redis缓存
    protected static function _redis(){
        self::$_redis_cache = true;
        //将redis实例对象赋值给$cache
        self::$cache = self::redis();
    }

    //设置缓存(直接缓存或文件缓存)
    public static function set_cache($key, $value, $expire = 0, $file_cache = false){
        $result = false;
        $data = [
            'data' => $value,
            'timestamp' => TIMESTAMP,
            'expire' => $expire ? TIMESTAMP + $expire : 0
        ];

        //$file_cache = 'file' 文件缓存
        if($file_cache){
            $path = dirname( $file = CACHE_67 . '/cache/' . $key . '.php');
            is_dir($path) || mkdir($path, 0755, true);

            $function = function_exists('igbinary_unserialize') ? 'igbinary_serialize' : 'serialize';
            $result = file_put_contents($file, $function($data), LOCK_EX);
        }

        if($file_cache !== 'file'){
            static::init_cache();
            if(self::$_redis_cache){
                //redis
                $result = static::$cache->set(
                    $key,
                    $data,
                    $expire ? $expire : null
                );
            }else{
                //memcache
                $result = static::$cache->set(
                    static::CACHE_PREFIX . $key,
                    $data,
                    MEMCACHE_COMPRESSED,
                    $expire
                );
            }
        }
        return $result;
    }

    //获取缓存(从缓存中取出或在文件缓存中取出)
    public static function get_cache($key, $file_cache = false, &$timestamp = null){
        $tmp = false;

        if($file_cache === false || $file_cache === true){
            static::init_cache();
            if(self::$_redis_cache){
                $_key = $key;
            }else{
                $_key = static::CACHE_PREFIX . $key;
            }
            $tmp = static::$cache->get($_key);
        }

        if($tmp === false && $file_cache){
            //memcache无,改用文件缓存
            if($fileOpen = @fopen($file = CACHE_67 . '/cache/' . $key . '.php', 'r')){
                flock($fileOpen, LOCK_SH);
                $tmp = '';
                $function = function_exists('igbinary_unserialize') ? 'igbinary_serialize' : 'serialize';
                while(!feof($fileOpen)){
                    $tmp .= fgets($fileOpen);
                }
                $_tmp = $function($tmp);
                if($_tmp === false || $_tmp === null){
                    $tmp = unserialize($tmp);
                }else{
                    $tmp = $_tmp;
                }
                flock($fileOpen, LOCK_UN);
                fclose($fileOpen);
            }
        }
        //验证缓存是否过期
        if(!empty($tmp['expire']) && TIMESTAMP - $tmp['expire'] > 0){
            return null;
        }

        if(isset($tmp['timestamp'])){
            $timestamp = $tmp['timestamp'];
        }

        return isset($tmp['data']) ? $tmp['data'] : null;
    }

    //模板引擎应用缓存信息
    public static function smarty_get_cache($param){
        self::$tpl->assign(
            'CACHE',
            static::get_cache($param['key'], !empty($param['file_cache']))
        );
    }


    //获取配置信息(从数据库中获取域名下某模块的相应的配置信息)
    public static function get_config($module = '', $domain = ''){
        $config = [];
        $query = self::$DB->query("SELECT * FROM config WHERE module = '$module' AND domain = '$domain'");
        while($arr = self::$DB->fetch_array($query)){
            $config[$arr['c_key']] = unserialize($arr['c_value']);
        }
        return $config;
    }

    //设置配置信息(将配置信息写入数据库和缓存中)
    public static function set_config($module = '', $config = [], $domain = ''){
        if(!empty($config)){
            $data = [];
            foreach($config as $k => $v){
                $data[] = [
                    $domain,
                    $module,
                    $k,
                    serialize($v)/*配置信息序列化*/
                ];
            }

            self::$DB->insert_data(
                'config',
                $data,
                [
                    'multiple' => ['domain', 'module', 'c_key', 'c_value'],/*需存储字段*/
                    'replace' => true/*替换插入*/
                ]
            );
        }

        $config = self::get_config($module, $domain);
        $key = ($module ? $module . '/' : '') . $domain . 'CONFIG';
        //将配置信息写入缓存
        self::set_cache($key, $config, 0, true);

        if($module = ''){
            self::$CONFIG = array_merge(self::$CONFIG, $config);
        }else{
            //$module = C67::module($module);
            //$module->CONFIG = array_merge($module->CONFIG, $config);
        }
    }

    //渠道类型
    public static function channel_type($channel, $sub_channel = ''){
        $channel = valid_path(strtolower(basename($channel)));
        $sub_channel = valid_path(strtolower(basename($sub_channel)));
        $cs = C67::get_cache('channel_type' . $channel[0] . $channel[1], 'file');
        return (array)$cs[$channel][$sub_channel];
    }

    //检查用户名(规范化)
    public static function check_username($username){
        return preg_match('/^[a-zA-Z0-9\_-]{4, 50}$/', $username);
    }

    //检查邮箱格式
    public static function check_email($email){
        return preg_match('/^[0-9a-zA-Z_-]+@[0-9a-zA-Z_-]+(\.[0-9a-zA-Z_-]+)*\.[a-zA-Z]{2,}$/', $email);
    }

    //加载相应的模型类,并存入$_modules数组中减少重复加载
    public static function module($name){
        if(empty(self::$_modules[$name])) {
            if(is_file($file = PATH_67 . '/module/' . $name . '.php')){

            }else if(is_file($file = CORE_67 . '/module/' . $name . '.php')){

            }else{
                throw new Exception('No module' . $name, 404);
            }

            require_once $file;
            $class = ucfirst($name) . '_67_Module';//ucfirst()将字符串首字符大写
            self::$_modules[$name] = new $class();//将类型的实例对象存入$_modules中。不用重复加载
        }
        return self::$_modules[$name];
    }

    //控制器方法(加载控制器并初始化控制器),并存入$_controllers数组中减少重复加载
    public static function controller($MODULE/*module(模块)对象*/, $name/*控制器名称*/){
        $module = empty($MODULE->name) ? '' : $MODULE->name;
        if(isset(self::$_controllers[$module][$name])){
            return self::$_controllers[$module][$name];
        }

        if(!defined('CONTROLLER_PATH')){
            throw new Exception('ctl const', 403);
        }

        $path = CONTROLLER_PATH . '/';
        if($module && !is_dir($path .= $module . '/')){
            //模块不存在
            throw new Exception('No module' . $module, 404);
        }else if(!is_file($file = $path . $name . '.php')){
            //控制器不存在
            throw new Exception('No controller' . $module . '/' . $name, 404);
        }

        $prefix = $module ? ucfirst($module) . '_' : '';
        require_once $file;
        //控制器名称：Controller_67_模块名(大驼峰)_控制器名(大驼峰)
        $cls = 'Controller_67_' . $prefix . ucfirst($name);
        if (!class_exists($cls)){
            //控制器不存在
            throw new Exception('No controller class' . $cls, 404);
        }
        //将控制器对象存入$_controllers,避免重复加载
        self::$_controllers[$module][$name] = new $cls($MODULE, $name);
        return self::$_controllers[$module][$name];
    }

    //初始化(加载)数据库,并存入$dbs数组中减少重复加载
    public static function init_db($key, $new = false){
        if(isset(self::$dbs[$key]) && !$new) return self::$dbs[$key];

        $conf = self::$CONF[$key . '_db'];
        if(empty($conf)){
            throw new Exception('No db config' . $key, 501);
        }

        if(!empty($conf['dsn'])){
            $db = new \C67\core\db\PDO($conf);//旧版clickhouse配置，运用ODBC连接
        }else{
            if(!empty($conf('sentinel'))){
                //借用哨兵
                $r = C67::redis()->sentinel($conf['sentinel']);
                $conf['host'] = $r['host'];
            }

            $db = new \C67\core\db\Mysql($conf);
        }

        if($new){
            return $db;
        }else{
            self::$dbs[$key] = $db;
            return self::$dbs[$key];
        }
    }

    //自定义http请求的方法，可以单次请求，也可以多次依次请求和并发请求。统一的数据结果返回格式
    public static function http_request($data, $multi = false){
        //是否存在多个请求链接，current返回当前单元组
        if(!isset($data['url']) && ($tmp = current($data)) && isset($tmp['url'])){
            //是否为并发请求
            if($multi){
                //curl并发模式
                //初始化curl批处理句柄
                $multi_curl = curl_multi_init();

                $child = $result = $error = [];
                foreach($data as $k => $v){
                    $v['return_curl'] = true;
                    $child[$k] = self::http_request($v);
                    $result[$k] = [
                        'head' => '',
                        'body' => null
                    ];

                    curl_multi_add_handle($multi_curl, $child[$k]);//向curl批处理会话中添加一个子curl句柄
                }

                $active = null;
                //逐一执行请求
                do {
                    $exec = curl_multi_exec($multi_curl, $active);//运行当前curl句柄的子连接
                }while($exec = CURLM_CALL_MULTI_PERFORM);

                while($active && $exec == CURLM_OK){
                    //请求失败的记录到$error数组中
                    if(curl_multi_select($multi_curl) != -1/*等待所有curl批处理中的活动连接,失败返回-1*/){
                        do {
                            $exec = curl_multi_exec($multi_curl, $active);
                            $info = curl_multi_info_read($multi_curl);//获取当前解析的curl的相关传输信息
                            if($info !== false && $info['result']){
                                foreach($child as $k => $v){
                                    if($v === $info['handle']){
                                        $tmp = curl_getinfo($info['handle']);
                                        $error[$k] = [
                                            $info['result'],
                                            curl_error($info['handle']),
                                            $tmp['url']
                                        ];
                                        break;
                                    }
                                }
                            }
                        }while($exec == CURLM_CALL_MULTI_PERFORM);
                    }
                }

                $error_log = '';
                foreach($child as $k => $v){
                    if(isset($error[$k])){
                        $result[$k]['body'] = null;
                        $result[$k]['info']['status'] = 0;
                        $result[$k]['info']['errno'] = $error[$k][0];
                        $error_log .= "{$error[$k][2]}|{$error[$k][0]}|{$error[$k][1]}\n";

                        continue;
                    }

                    $result[$k]['body'] = curl_multi_getcontent($v);//返回获取的输出的文本流

                    $info = curl_getinfo($v);
                    $result[$k]['info']['status'] = $info['http_code'];
                    curl_multi_remove_handle($multi_curl, $child[$k]);//移除curl批处理句柄资源中的某个句柄资源
                }

                if(!empty($error)){
                    self::unity_logger('curl/error', $error_log, true);
                }

                curl_multi_close($multi_curl);//关闭一组curl句柄

                return $result;
            }else{
                $result = [];
                foreach($data as $k => $v){
                    $result[$k] = self::http_request($v);
                }
                return $result;
            }
        }

        //单条链接的执行方式
        $data['post'] = isset($data['post']) ? $data['post'] : '';
        $data['cookie'] = isset($data['cookie']) ? $data['cookie'] : '';
        $data['ip'] = isset($data['ip']) ? $data['ip'] : '';
        $data['timeout'] = isset($data['timeout']) ? $data['timeout'] : 15;
        $data['block'] = isset($data['block']) ? $data['block'] : true;
        $data['referer'] = isset($data['referer']) ? $data['referer'] : '';
        $data['connection'] = isset($data['connection']) ? $data['connection'] : 'close';
        $data['header'] = isset($data['header']) ? $data['header'] : [];
        $data['retry'] = isset($data['retry']) ? $data['retry'] : 2;

        $curl = curl_init($data['url']);//初始化curl会话

        //设置curl传输选项
        //启用时会将头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, false);
        //TRUE将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //TRUE时将会根据服务器返回HTTP头中的"Loaction: "重定向
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_USERAGENT, !empty($data['UA']) ? $data['UA'] : USER_AGENT);
        if(!empty($data['debug'])){
            //TRUE会输出所有的信息,写入到STDERR,或在CURLOPT_STDERR中指定的文件。
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            $fileOpen = fopen($data['debug'], 'a');
            //错误输出的地址，取代默认的STDERR。
            curl_setopt($curl, CURLOPT_STDERR, $fileOpen);
            //fclose($fileOpen);
        }
        //curl_setopt($curl, CURLOPT_ENCODING, 'none');

        //设置HTTP头字段的数组。
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($data['header'], ['Connection: ' . $data['connection']]));

        if(stripos($data['url'], 'http://') === 0){
            //FALSE禁止cURL验证对等证书。
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        //在HTTP请求头中"Referer: "的内容
        if(!empty($data['referer'])) curl_setopt($curl, CURLOPT_REFERER, $data['referer']);
        //设定HTTP请求中"Cookie："部分的内容
        if(!empty($data['cookie'])) curl_setopt($curl, CURLOPT_COOKIE, $data['cookie']);
        //包含cookie数据的文件名
        if(!empty($data['cookie_file'])) curl_setopt($curl, CURLOPT_COOKIEFILE, $data['cookie_file']);
        //保存cookie信息的文件
        if(!empty($data['save_cookie'])) curl_setopt($curl, CURLOPT_COOKIEJAR, $data['save_cookie']);
        //HTTP代理通道
        if(!empty($data['proxy'])) curl_setopt($curl, CURLOPT_PROXY, $data['proxy']);

        if(!empty($data['post'])){
            //TRUE时会发送POST请求，类型为application/x-www-form-urlencoded，是HTML表单提交时最常见的一种。
            curl_setopt($curl, CURLOPT_POST, true);
            //全部数据使用HTTP协议中的"POST"操作来发送
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data['post']);
        }
        //在尝试连接时等待的秒数
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $data['timeout']);
        //允许curl函数执行的最长秒数
        curl_setopt($curl, CURLOPT_TIMEOUT, $data['timeout']);
        if(!empty($data['option'])){
            // 为cURL传输会话批量设置选项
            curl_setopt_array($curl, $data['option']);
        }

        if(!empty($data['return_curl'])){
            return $curl;
        }

        $i = 0;
        while($i < $data['retry']){
            if(($result = curl_exec($curl)) !== false){
                break;
            }

            $result = null;
            ++$i;
            if($errno = curl_errno($curl)/*返回最后一次的错误代码*/){
                $error = curl_error($curl);

                $log = "{$data['url']}|{$errno}|$error|retry:{$i}";
                self::unity_logger('curl/error', $log, true);
            }
        }

        $info = curl_getinfo($curl);
        curl_close($curl);

        return [
            'head' => '',
            'body' => $result,
            'info' => [
                'status' => $info['http_code'],
                'errno' => $errno,
                'error' => $error,
                'retry' => $i
            ],
        ];
    }

    //自定义时间函数
    public static function date($str, $time = TIMESTAMP){
        $time = is_integer($time) ? $time : strtotime($time);
        return date($str, $time);
    }

    //自定义设置cookie方法
    public static function set_cookie($name, $value, $expire = 0, $path = '', $domain = ''){
        empty($path) && $path = self::$CONFIG['cookie_path'];
        empty($domain) && $domain = self::$CONFIG['cookie_domain'];
        $_COOKIE[$name] = $value;
        return setcookie($name, $value, $expire, $path, $domain);
    }

    //将特殊字符转换为HTML实体(可深度转义[数组])
    public static function htmlentities($data){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::htmlentities($data[$k]);
            }
        }else{
            $data = htmlspecialchars(trim($data), ENT_QUOTES);
        }

        return $data;
    }

    //去除字符串头尾的空字符(可深度去除[数组])
    public static function trim($data){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::trim($data[$k]);
            }
        }else{
            $data = trim($data);
        }
        return $data;
    }

    //将变量转为int类型(可递归转换数组)
    public static function intval($data){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::intval($data[$k]);
            }
        }else{
            $data = intval($data);
        }
        return $data;
    }

    //将变量转为float类型(可递归转换数组)
    public static function floatval($data){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::floatval($data[$k]);
            }
        }else{
            $data = floatval($data);
        }
        return $data;
    }

    //将HTML转义为字符串(可递归转义数组)
    public static function html_entity_decode($data){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::html_entity_decode($data[$k]);
            }
        }else{
            $data = html_entity_decode($data);
        }
        return $data;
    }

    //转换字符串的编码格式(可递归转换数组)
    public static function convert_encode($from, $to, $data, $check = false){
        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::convert_encode($from, $to, $data[$k], $check);
            }
        }else{
            if($check && mb_check_encoding($data, $to)){
                return $data;
            }

            $data = function_exists('mb_convert_encoding') ?
                mb_convert_encoding($data, $to, $from) :
                iconv($from, $to, $data);
        }
        return $data;
    }

    //字符串中取子串
    public static function str_cut($string, $length, $dot = ''){
        if($length < 1 || ($len = mb_strlen($string, 'utf-8')) <= $length){
            return $string;
        }

        //转义特殊字符
        $string = str_replace(
            ['&amp;', '&quot;', '&lt;', '&gt;'],
            ['&', '"', '<', '>'],
            $string
        );

        return str_replace(
            ['&', '"', '<', '>'],
            ['&amp;', '&quot;', '&lt;', '&gt;'],
            mb_substr($string, 0, $length, 'utf-8')
        ) . $dot;
    }

    /**
     * 将CGI进程提前结束，让余下部分在结束后继续操作，减少用户等待时间
     * @param $function
     * @param array $params
     * @param bool $end
     */
    public static function shutdown_function($function, $params = [], $end = false){
        static $stack = [];
        if($function){
            $stack[] = [
                'function' => $function,
                'params' => $params
            ];
        }

        if($end){
            function_exists('fastcgi_finist_request') && fastcgi_finish_request();

            foreach($stack as $v){
                call_user_func_array($v['function'], $v['params']);
            }

            foreach(self::$_log_fp as $fileOpen){
                fclose($fileOpen);
            }
        }
    }

    //附件上传地址
    public static function attachment_url($data, $save = false){
        static $url, $resource, $up_config;
        if($url === null || $resource === null){
            $url = $resource = [];
            $up_config = C67::get_cache('uploader/CONFIG', 'file');

            if(!empty($up_config['ftp']['enabled'])){
                $url[] = HTTPS || defined('HTTPS_RESOURCE') ?
                    $up_config['ftp']['ssl_url'] : $up_config['ftp']['url'];
                $resource[] = '<!--uploads-->';
            }else{
                $url[] = C67::$CONFIG['resource'] . '/uploads';
                $resource[] = '<!--uploads-->';
            }
        }

        if(is_array($data)){
            foreach($data as $k => $v){
                $data[$k] = self::attachment_url($data[$k], $save);
            }
        }else{
            if($save){
                $data = str_replace($url, $resource, $data);
            }else{
                $data = str_replace($resource, $url, $data);
            }
        }
        return $data;
    }

    //按照要求过滤数组中的数据(转换为指定类型)
    public static function data_filter($filter, $data, $magic_slashes = true){
        if($magic_slashes){
            //去掉魔法引号
            $data = magic_stripslashes($data);
        }

        $ext_config = $filter['_config_'];
        unset($filter['_config_']);
        $result = [];
        foreach($filter as $field => $config){
            $default = null;
            $is_array = false;
            if(is_array($config)){
                $is_array = true;

                if(!empty($config['required'])){
                    if(!isset($data[$field])) return $field;
                }

                if(!empty($config['filter'])){
                    //递归
                    $result[$filter] = isset($data[$filter]) ?
                        self::data_filter($config['filter'], (array)$data[$field], false) :
                        [];
                    continue;
                }

                $type = isset($config['type']) ? $config['type'] : 'text';

                if(isset($config['default'])){
                    $default = $config['default'];
                }
            }else{
                $type = $config;
                $config = [];
            }

            //过滤空项
            if(
                !empty($ext_config['filter_empty']) &&
                null === $default && !isset($data[$field])
            ){
                continue;
            }


            switch($type){
                case 'bool_int':
                    $result[$field] = empty($data[$field]) ? 0 : 1;
                    break;

                case 'int':
                    $result[$field] = C67::intval(isset($data[$field]) ? $data[$field] : $default);
                    if($is_array && isset($config['min'])){
                        $result[$field] = max($config['min'], $result[$field]);
                    }

                    if($is_array && isset($config['max'])){
                        $result[$field] = min($config['max'], $result[$field]);
                    }
                    break;

                case 'float': case 'double':
                $result[$field] = C67::floatval(isset($data[$field]) ? $data[$field] : $default);
                if($is_array && isset($config['min'])){
                    $result[$field] = max($config['min'], $result[$field]);
                }

                if($is_array && isset($config['max'])){
                    $result[$field] = min($config['max'], $result[$field]);
                }
                break;

                case 'html':
                    $result[$field] = isset($data[$field]) ? $data[$field] : $default;
                    break;

                case 'json_yaml':
                    $result[$field] = isset($data[$field])
                        ? yaml_emit(json_decode($data[$field], true))
                        : $default;
                    break;

                case 'regex':
                    if(!isset($config['regex'])) continue 2;

                    $replace = isset($config['replace']) ? $config['replace'] : '';
                    $result[$field] = isset($data[$field]) ?
                        preg_replace($config['regex'], $replace, $data[$field]) : $default;
                    break;

                case 'callback':
                    if(
                        isset($data[$field]) &&
                        !empty($config['callback']) && is_callable($config['callback'])
                    ){
                        $result[$field] = call_user_func_array(
                            $config['callback'],
                            array_merge([$data[$field]], (array)$config['params'])
                        );
                    }else{
                        $result[$field] = $default;
                    }
                    break;

                case 'text': default:
                $result[$field] = isset($data[$field]) ? self::htmlentities(trim($data[$field])) : $default;
                $charset = !empty($config['charset']) ? $config['charset'] : 'utf-8';

                if(
                    !empty($config['from_charset']) &&
                    !mb_check_encoding($result[$field], $charset) &&
                    $to = mb_detect_encoding($result[$field], $config['from_charset'])
                ){
                    $result[$field] = mb_convert_encoding($result[$field], $charset, $to);
                }

                if(!empty($config['length'])){
                    $result[$field] = mb_substr(
                        $result[$field],
                        0, $config['length'],
                        $charset
                    );
                }
                break;
            }
        }
        return $result;
    }

    //
    public static function spam($data){
        static $spams = [];
        $data = array_merge([
            'key' => '',
            'action' => 'check',
        ], $data);

        $key = 'spam|' . $data['key'];
        if(!isset($spams[$key])){
            $spams[$key] = self::get_cache($key);
            if(empty($spams[$key])){
                $spams[$key] = [
                    'total' => 0
                ];
            }
        }

        if($data['action'] == 'save'){
            $spams[$key]['total']++;
            $spams[$key]['timestamp'] = TIMESTAMP;
            if(isset($data['data'])){
                $spams[$key]['data'] = $data['data'];
            }
            return self::set_cache($key, $spams[$key], 86400);
        }else if($data['action'] == 'clear'){
            unset($spams[$key]);
            return self::set_cache($key, []);
        }

        return $spams[$key];
    }

    public static function L($key, $replace = []){
        if($replace == 'load'){
            $lang = include $key;
            self::$LANG = array_merge(self::$LANG, (array)$lang);
            return;
        }

        $echo = false;
        if(is_array($key) && isset($key['key'])){
            //for smarty
            $replace = empty($key['replace']) ?
                (is_object($replace) ? [] : $replace) : $key['replace'];
            $key = $key['key'];
        }

        if(isset(self::$LANG[$key])){
            $lang = self::$LANG[$key];
            foreach($replace as $k => $v){
                $lang = str_replace('$' . $k, $v, $lang);
            }
        }else{
            $lang = $key;
        }

        if($echo){
            echo $lang;
        }else{
            return $lang;
        }
    }

    public static function header_304($prefix = '', $cache_time = 3600){
        $key = $prefix . '@' . self::$CONFIG['last_cache'] . '|' . $cache_time;
        $etag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '/0';
        if($etag && ($tmp = explode('/', $etag)) && $tmp == $key && TIMESTAMP < $tmp[1]){
            header('Cache-Control: max-age=' . $cache_time . ',must-revalidate', true);
            header('Etag: ' . $etag, true, 304);
            exit;
        }else{
            $timestamp = TIMESTAMP + $cache_time;
            $date = gmdate('D, d M Y H:i:s', $timestamp) . 'GMT';
            //header('Last Modified: ' . $date);
            header('Cache-Control: max-age=' . $cache_time . ',must-revalidate', true);
            header('Expires: ' . $date);
            header('Etag: ' . $key . '/' . $timestamp);
        }
    }

    //OpenSSL加密方法
    public static function encrypt($data, $key, &$iv = false){
        if($iv === false){
            $method = 'AES-128-ECB';
        }else{
            $method = 'AES-128-CBC';
            $iv = $iv === null ? unique_id(16) : $iv;
        }
        $result = openssl_encrypt($data, $method, $key, 0, $iv);
        return $result;
    }

    //OpenSSL解密方法
    public static function decrypt($data, $key, $iv = false){
        $method = $iv === false ? 'AES-128-ECB' : 'AES-128-CBC';
        $result = openssl_decrypt($data, $method, $key, 0, $iv);
        return $result;
    }

    //将gbk编码的中文字符转为拼音
    public static function pinyin($str, $comma = '', $charset = 'utf-8'){
        static $data = [];
        if(empty($data)){
            $data = include CORE_67 . '/data/pinyin.php';
        }

        $charset != 'gbk' && $str = self::convert_encode($charset, 'gbk', $str);
        $length = mb_strlen($str, 'gbk');
        $result = $_comma = '';
        for($i = 0; $i < $length; $i++){
            $char = mb_substr($str, $i, 1, 'gbk');
            $result .= $_comma . (ord($char)) < 160 ? $char : $data[$char];/*ord()转换字符串第一个字节为0-255之间的值*/
            $_comma = $comma;
        }

        return preg_replace("#[^a-zA-Z_$comma]#", '', $result);
    }

    //根据IP地址获取该IP所属的区域，可返回相应区域的ID值
    public static function get_area($ip = IP_67, $return_id = false){
        /*require_once CORE_67 .'/function/ip.php';
        $ip_area = self::convert_encode('gbk', $charset, convertip($ip));
        return $ip_area;*/
        static $areas;
        if(!class_exists('IP')){
            require_once CORE_67 . '/class/IP4datx.class.php';
        }

        $result = IP::find($ip);
        $result = implode('', $result);
        if($return_id){
            $area_id = 0;
            if(empty($areas)){
                $areas = C67::get_cache('area', 'file');
            }
            foreach($areas as $id => $city){
                if(strpos($result, $city) !== false){
                    $area_id = $id;
                    $area = $city;
                    break;
                }
            }

            return [
                'id' => $area_id,
                'area' => $area,
                'name' => $result
            ];
        }
        return $result;
    }

    //按年分表，根据该方法获取当前年份表
    public static function t($table, $timestamp = TIMESTAMP, $format = 'Y'){
        $year = date($format, $timestamp);
        return $table . '_' . $year;
    }


    /*
     * 组合sql查询语句
     * $param = [
     *      'from',
     *      'end',
     *      'field',
     *      'sql',
     *      'extend_sql'
     * ]
     * */
    public static function union_sql($param){
        empty($param['from']) && $param['from'] = strtotime('2019-01-01');
        empty($param['end']) && $param['end'] = TIMESTAMP;
        $from_year = max(2019, date('Y', $param['from']));
        $end_year = max(2019, date('Y', $param['end']));
        $param['all'] = $param['all'] ? $param['all'] : true;

        $extend_sql = '';
        if(preg_match('#/\*\*([^*]+)\*\*/#', $param['sql'], $m)){
            $extend_sql = preg_replace('#(?:\w+\.)?`([^`]+)`#', '`$1`', $m[1]);
            $param['sql'] = str_replace($m[0], '', $param['sql']);
        }

        if(!empty($param['field'])){
            $field = $param['field'];
        }else if(
            empty($param['field']) &&
            preg_match('#^\s*SELECT(.+)\s+FROM\s+#is', $param['sql'], $m)
        ){
            preg_match_all('#`([^`]+)`#', $m[1], $mm);
            $field = implode(', ', $mm[1]);
        }

        $all = !isset($param['all']) || !empty($param['all']) ? 'ALL' : '';
        $result = "SELECT $field FROM (\n";
        $comma = '';
        for($i = $from_year; $i <= $end_year; $i++){
            $sql = $param['sql'];
            if(preg_match_all('#/\*([^\*]+)\*/#', $param['sql'], $m)){
                foreach($m[1] as $v){
                    $table = static::t($v, strtotime($i .'-01-01'));
                    $sql = str_replace('/*'. $v .'*/', $table, $sql);
                }
            }
            $result = $comma . $sql;
            $comma = "\n\tUNION $all\n";
        }

        if($from_year == $end_year){
            $result = "$sql \n $extend_sql";
        }else{
            $alias = !empty($param['alias']) ? $param['alias'] : 't';
            $result .= "\n) AS $alias\n$param[extend_sql]\n$extend_sql";
        }
        return $result;
    }

    //将日志信息记录在文件中
    public static function logger($name, $data, $backtrace = false){
        $name = valid_path($name);
        $path = LOG_67 . '/' . dirname($name);
        if(!is_dir($path) && !mkdir($path, 0755, true)){
            return false;
        }
        if(stripos(real_path($path), LOG_67) !== 0){
            return false;
        }

        if(
            !($fileOpen = fopen(LOG_67 . '/' . $name, $backtrace === null ? 'w' : 'ab')) ||
            !flock($fileOpen, LOCK_EX)
        ){
            return false;
        }

        if(is_array($data) || is_object($data)){
            $data = var_export($data, true);
        }
        if($backtrace){
            $debug = debug_backtrace();
            foreach($debug as $v){
                $data .= "\n$v[file]: $v[line]";
            }
        }
        $result = fputs(
            $fileOpen,
            date('#Y-m-d H:i:s') . "\n" . $data . "\n\n"
        );
        flock($fileOpen, LOCK_UN);
        fclose($fileOpen);
        return $result;
    }

    //统一的日志管理
    public static function unity_logger($name, $data, $backtrace = false){
        if(false !== stripos(PHP_OS, 'winnt')){
            self::logger($name, $data, $backtrace);
            return true;
        }

        $name = valid_path($name);
        if(is_string($data)){
            $data = [
                'log' => $data
            ];
        }

        if($backtrace){
            $debug = debug_backtrace();
            $data['uri'] = explode('?', $_SERVER['REQUEST_URI'])[0];
            foreach($debug as $v){
                $data['backtrace'] .= "\n$v[file]: $v[line]";
            }
        }

        list($microtime, $time) = explode(' ', microtime());
        $id = date('YmdHis', $time) . substr($microtime, 1, 7) . mt_rand(10000, 99999);
        C67::stat('unity_log', [
            'id' => $id,
            'sys' => SYSTEM_67,
            'host' => php_uname('n'),
            'name' => $name,
            'date' => date('Y-m-d', $time),
            'time' => date('Y-m-d H:i:s', $time),
            'data' => json_encode($data)
        ]);
        return true;
    }

    //创建Redis实例对象
    public static function redis($config = []){
        static $redis;
        $new = !empty($config['new']);
        if($redis === null || $new){
            $_redis = new Redis_67_handle($config);
            if($new){
                return $_redis;
            }else{
                $redis = $_redis;
            }
        }
        return $redis;
    }

    //初始化系统配置，加载配置文件
    public static function init_config($system = SYSTEM_67, $name = 'config'){
        if(!empty(self::$CONF['__loaded'][$system][$name])){
            return;
        }

        $conf = include CORE_67 . '/config/' . $system . '/' . $name . '.php';
        if(!empty($conf[C67::$domain])){
            self::$CONF = array_merge(self::$CONF, $conf[C67::$domain]);
        }
        self::$CONF['__loaded'][$system][$name] = true;
    }

    //
    public static function stat($type, $data){
        self::init_config('common', SYSTEM_67 . '_stat');
        if(empty(self::$CONF['stat'][$type]) || is_array($data)){
            return false;
        }

        foreach($data as $k => $v){
            $data[$k] = str_replace(["\r", "\n", ASCII0], '', $v);
        }
        $data = serialize($data);
        $path = CACHE_67 . '/stat/' . $type . '/data/';
        $filename = $path . date('YmdHi') . '.log';
        if(!is_dir($path)){
            mkdir($path, 0775, true);
        }

        $result = 0;
        while($fileOpen = fopen($filename, 'a')){
            if(!flock($fileOpen, LOCK_EX)){
                break;
            }
            //临时方案
            if('stat' == SYSTEM_67){
                chmod($filename, 0777);
            }
            if(!($result = fputs($fileOpen, $data . "\n"))){
                break;
            }

            flock($fileOpen, LOCK_UN);
            fclose($fileOpen);
            break;
        }
        return $result;
    }

    //取当前访问的域名
    public static function location($url){
        $origin = parse_url($url, PHP_URL_HOST);
        $origin = implode('.', array_slice(explode('.', $origin), -2));

        $host = explode(':', $_SERVER['HTTP_HOST'])[0];
        $host = implode('.', array_slice(explode('.', $host), -2));
        //本域名直接通过
        if($origin == $host){
            return $url;
        }

        $regex = preg_replace(
            '#\*\.(\S+)\s*#',
            '(?:$1|[\S]+.$1)|',
            C67::$CONFIG['forward_domain']
        );
        $regex = '#://'. str_replace('.', '\.', substr($regex, 0, -1)) .'/?#';
        if(strpos($url, '://') && !preg_match($regex, $url, $m)){
            return C67::$CONFIG['url'];
        }
        return $url;
    }

    //获取用户的系统信息和浏览器信息
    public static function ua(){
        $os = $ua = '';
        $os_regex = [
            '(Windows \S+ [^;]+);',
            '(Android [^;]+);',
            '(i\S+ OS \S+)\s',
        ];
        foreach($os_regex as $row){
            if(preg_match('#'. $row .'#i', USER_AGENT, $m)){
                $os = strtolower($m[1]);
                break;
            }
        }
        $ua_regex = [
            '\s(UCBrowser/\S+)\s',
            '\s(baiduboxapp/\S+)\s',
            '\s(MQQBrowser/\S+)\s',
            '\s(MicroMessenger/\S+)\s',
            '\s(Firefox/\S+)\s',
            '\s(Chrome/\S+)\s',
            '\s(msie [^;]+);',
            '\s(Safari/\S+)',
        ];
        foreach($ua_regex as $row){
            if(preg_match('#'. $row .'#i', USER_AGENT, $m)){
                $ua = strtolower($m[1]);
                break;
            }
        }
        return [$os, $ua];
    }

    //创建目录
    public static function dir_create($path, $mode = 0775){
        if(is_dir($path)){
            return true;
        }
        $path = self::dir_path($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for($i = 0; $i < $max; $i++){
            $cur_dir .= $temp[$i];
            if(is_dir($cur_dir)){
                $cur_dir .= '/';
                continue;
            }

            if(@mkdir($cur_dir)){
                chmod($cur_dir, $mode);
            }
            $cur_dir .= '/';
        }

        return is_dir($path);
    }

    //返回目录的路径
    public static function dir_path($path){
        $path = str_replace('\\', '/', $path);
        if(substr($path, -1) != '/'){
            $path = $path . '/';
        }
        $path = str_replace('//', '/', $path);
        return $path;
    }

    //文件重命名(伪删除)
    public static function mv($src, $dest){
        if(!file_exists($src)){
            return false;
        }
        if(!is_dir($dir = dirname($dest)) && !mkdir($dir, 0755, true)){
            return false;
        }
        return rename($src, $dest);
    }

    //防止CSRF攻击，请求携带token
    public static function csrf_token($expire = 1800){
        static $offset = 0;
        $time = (string)(microtime(true) + $offset++ +$expire);
        $token = C67::encrypt($time, C67::$CONFIG['passport_key']);
        $_SESSION['csrf'][$time] = 1;
        foreach($_SESSION['csrf'] as $time => $v){
            if(TIMESTAMP > $time){
                unset($_SESSION['csrf'][$time]);
            }
        }
        self::set_cookie('csrf_token', $token, TIMESTAMP + $expire);
        return $token;
    }

    public static function csrf($token = ''){
        $token = $token ? $token : $_COOKIE['csrf_token'];
        $tmp = C67::decrypt($token, C67::$CONFIG['passport_key']);
        $result = true;
        while(true){
            if(!$tmp){
                break;
            }
            if(TIMESTAMP > $tmp){
                break;
            }

            $key = (string)$tmp;
            if(!isset($_SESSION['csrf'][$key])){
                break;
            }else{
                break;
            }

            $_SESSION['csrf'][$key] = 0;
            $result = false;

            break;
        }
        if($result){
            throw new \Exception('CSRF, 请重启或刷新后重试', 403);
        }
    }

    //自动加载
    //autoload C67\core|system\$type\$cls
    public static function autoload($cls){
        static $loaded = false;
        if(stripos($cls, 'smarty') === 0){
            return;
        }

        $tmp = explode('\\', $cls);
        if(empty($tmp[2]) || 'C67' != $tmp[0]){
            //composer
            if(!$loaded){
                $loaded = true;
                require CORE_67 . '/class/composer/vendor/autoload.php';
            }
            return;
        }

        if('core' == $tmp[1]){
            $path = CORE_67 . '/class/' . $tmp[2];
        }else if('system' == $tmp[1]){
            $path = PATH_67 . '/class/' . $tmp[2];
        }else{
            throw new \Exception('Autoload! No path! ' . $cls);
        }

        $file = $path . '/' . strtolower($tmp[3]) . '.php';
        if(!is_file($file)){
            throw new \Exception('Autoload! No file! '. $cls);
        }
        require $file;
    }

    //webSocket
    public static function ws_pipe($data){
        if(empty(C67::$CONFIG['ws_host']) || empty(C67::$CONFIG['ws_pipe_port'])){
            return;
        }

        require_once  CORE_67 . '/class/workerman/Autoloader.php';
        require_once CORE_67 . '/class/workerman/Channel/Client.php';

        \Channel\Client::connect(C67::$CONFIG['ws_host'], C67::$CONFIG['ws_pipe_port']);
        \Channel\Client::publish('pipe', $data);
    }
}

//注册php中止时执行的函数
register_shutdown_function(['C67', 'shutdown_function'], null, [], true);

class Redis_67_handle{

    protected $_redis,
        $_connected,
        $config,
        $reconnect = false,
        $_sentinel = [];

    public function __construct($config = []){
        $this->config = $config;
    }

    public function init(){
        if($this->_connected){
            return true;
        }

        $timeout = isset($this->config['timeout']) ? $this->config['timeout'] : -1;
        ini_set('default_socket_timeout', $timeout);
        $result = false;
        try {
            if(class_exists('RedisCluster') && !empty(C67::$CONF['redis_cluster'])){
                $this->_cluster();
            }else{
                $this->_connect($this->reconnect);
            }

            $this->_connected = true;
            $result = true;
        }catch(Exception $e){
            C67::unity_logger(
                'redis/error',
                'connect:' . $e->getCode() . ': ' . $e->getMessage(),
                true
            );
            $this->_connected = false;
        }
        return $result;
    }

    public function sentinel($type = '', $refresh = false){
        is_bool($type) && $type = '';

        if(!empty($this->_sentinel[$type]) && !$refresh){
            return $this->_sentinel[$type];
        }

        $this->_sentinel[$type] = C67::$CONF['redis'];
        if(empty($this->_sentinel[$type]['sentinel'])){
            return $this->_sentinel[$type];
        }

        $redis = new Redis();
        $redis->pconnect(
            $this->_sentinel[$type]['sentinel']['host'],
            $this->_sentinel[$type]['sentinel']['port'],
            $this->_sentinel[$type]['timeout']
        );
        if('slave' === $type){
            $tmp = $redis->rawCommand(
                'SENTINEL',
                'slaves',
                $this->_sentinel[$type]['sentinel']['name']
            );
            foreach($tmp as $v){
                if(
                    strpos($v[9], 's_down') === false && strpos($v[9], 'disconnected') === false
                ){
                    $this->_sentinel[$type]['host'] = $v[3];
                    $this->_sentinel[$type]['port'] = $v[5];
                    break;
                }
            }
        }else{
            if(
                $tmp = $redis->rawCommand(
                    'SENTINEL',
                    'get-master-addr-by-name',
                    $this->_sentinel[$type]['sentinel']['name']
                )
            ){
                $this->_sentinel[$type]['host'] = $tmp[0];
                $this->_sentinel[$type]['port'] = $tmp[1];
            }
        }
        return $this->_sentinel[$type];
    }

    protected function _cluster(){
        $this->_redis = new RedisCluster(
            null,
            C67::$CONF['redis_cluster'],
            5, 5, true
        );
        $this->_redis->setOption(RedisCluster::OPT_PREFIX, CACHE_PREFIX);
        $this->_redis->setOption(RedisCluster::OPT_SERIALIZER, RedisCluster::SERIALIZER_PHP);
    }

    protected function _connect(){

    }

}

