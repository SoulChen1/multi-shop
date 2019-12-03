<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//后台管理系统路由
Route::group('admin',function (){
    Route::rule('/','admin/index/index','get');
    Route::rule('main','admin/index/main','get');
    Route::rule('storeLogin','admin/login/storeLogin','get|post');
    Route::rule('loginOut', 'admin/base/loginOut', 'get');
});
