<?php

namespace app\admin\controller;

use think\Request;

class Index extends Base
{
    /**
     * 首页
     * 功能：1.返回一个主页框架模板
     * @return \think\Response
     */
    public function index()
    {
        return view();
    }

    /**
     * 控制面板
     * 1.需要获取该店铺的(当天)订单数、(当天)营业额、总用户数和总商品数
     * 2.可提供一个当天按小时分的营业额折线图
     * @return \think\Response
     */
    public function main()
    {
        /** 功能一 */
        $order = model('Order','service')->getOrderCP();
        $userCount = model('User', 'service')->getUserCount();
        $productCount = model('Product', 'service')->getProductCount();
        $result = [
            'orderCount' => $order['count'],
            'amount' => $order['amount'],
            'userCount' => $userCount,
            'productCount' => $productCount
        ];
        /** 功能二 */


        $this->view->assign('data', $result);
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
