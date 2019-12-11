<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Login extends Controller
{
    /**
     * 店铺登录
     * @return \think\Response
     */
    public function storeLogin()
    {
        if(request()->isAjax()){
            $data = [
                'name' => input("post.username"),
                'password' => input("post.password")
            ];
            $result = model('Store')->login($data);
            if ($result == true) {
                $this->success('登录成功！', 'admin/index/index');
            }else{
                $this->error($result);
            }
        }
        return view();
    }

    /**
     * 管理员登录
     * @return \think\Response
     */
    public function adminLogin()
    {
        if(request()->isAjax()){

        }
        return view();
    }

}
