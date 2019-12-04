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
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}