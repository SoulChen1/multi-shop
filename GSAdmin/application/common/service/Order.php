<?php

namespace app\common\service;

use think\Model;

class Order extends Model
{
    /**
     * 获取当天的订单数和营业额
    **/
    public function getOrderCP(){
        return model('Order', 'model')->getOrderCP();
    }
}