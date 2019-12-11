<?php

namespace app\common\model;

use think\Model;

class Order extends Model
{

    /**
     * 获取当天的订单数和营业额
    */
    public function getOrderCP(){
        $amount = $this->whereBetweenTime('add_date', date('Y-m-d'))->sum('price');
        $count = $this->whereBetweenTime('add_date', date('Y-m-d'))->count();
        $result = [
            'count' => $count,
            'amount' => $amount
        ];
        return $result;
    }

}
