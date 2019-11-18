<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
   public function initialize(Type $var = null)
   {
       if (!session('?admin.id')) {
           $this->redirect('admin/login/index');
       }
   }

   public function loginOut(){
       session(null);
       $this->redirect('admin/login/index');
   }
}
