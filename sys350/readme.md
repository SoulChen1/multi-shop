## 350系统结构：
core系统核心库——core核心库中包含了很多公共方法、公共类、模型类、开发所需插件、配置文件和广告商上报接口，该核心库中同时包含了权限管理系统。
- core > index.php——系统的入口文件，定义了系统运行所需的常量、加载系统初始化文件init.php、加载系统所需配置文件和配置Smarty模板引擎。
- core > init.php——检验系统运行所需的常量、定义系统运行所需的公共方法和定义系统运行的容器类(公共类)、控制器父类、Redis类和Module类。

#### 公共方法：

- 获取IP地址方法：get_ip()
- 将目录的路径有效化：valid_path()
- 获取绝对路径：real_path()
- 删除文件：rm()
- 格式化输出数据：pr()
- 深度反转义字符串：stripslashes_deep()
- 是否配置自动反转义：magic_stripslashes()
- 16位的字符串MD5加密：md5_16()
- 生成唯一随机数：unique_id()
- 密码评分：password_score()
- 替换字符方法：mb_substr_replace()
- 验证码：captcha()
- 转换widget组件的数据格式：convert_widget_data()
- 分页功能：list_page()
- 分析身份证：parse_id_card()

#### 公共类(容器类)——C67：

- 常量(final或const)：缓存的头部信息常量CACHE_PREFIX;

- 公共静态变量(public static)：
    - 缓存静态实例$cache;
    - 当前系统的数据库实例对象$DB;
    - Smarty模板引擎的静态实例$tpl;
    - 加载config配置文件中的系统的基本配置信息$CONF;
    - 系统运行所需配置信息(包含系统的基本配置信息$CONF);
    - 存储已经初始化过的数据库$dbs;
    - 当前系统的顶级域名$domain;

- 保护的静态变量(protected static)：
    - 存储加载过的模型类$_modules;
    - 存储加载过的控制器类$_controllers;
    - 存储打开过的文件$_log_fp;
    - 当前系统是否为redis缓存;

```
补充
CSRF概念：CSRF(或XSRF)跨站点请求伪造，是一种挟制用户在当前已登录的Web应用程序上执行非本意操作的攻击方法。
防御CSRF攻击：验证HTTP Referer字段；在请求地址中添加token并验证；在HTTP头中自定义属性并验证。

```