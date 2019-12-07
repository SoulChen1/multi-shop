<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Order extends Model
{

    //获取订单数
    public function getOrderCount($date){
        $result = Db::query("SELECT COUNT(DISTINCT order_id), SUM(price) FROM order WHERE DATE_FORMAT(add_time, '%Y-%m-%d') = '{$date}'");
        return $result;
    }

}
