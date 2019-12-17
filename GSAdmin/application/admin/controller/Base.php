<?php

namespace app\admin\controller;

use think\Controller;

class Base extends Controller
{
    /**
     * 问题：
     * 1.超级管理员登录问题(目前为店铺登录)
     * */
    //控制器初始化方法
   public function initialize()
   {
       //判断session中是否有store.id
       if (!session('?store.id')) {
           $this->redirect('admin/login/storeLogin');
       }
   }

   /**
    * 登出后清空session并重定向到登录页面
    **/
   public function loginOut(){
       session('store', null);
       $this->redirect('admin/login/storeLogin');
   }
}
