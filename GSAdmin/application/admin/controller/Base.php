<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    //控制器初始化方法
   public function initialize()
   {
       if (!session('?store.id')) {
           $this->redirect('admin/login/storeLogin');
       }
   }

   public function loginOut(){
       session('store', null);
       $this->redirect('admin/login/storeLogin');
   }
}
