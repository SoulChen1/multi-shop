<?php

namespace app\common\model;

use think\Model;

class Product extends Model
{

    //获取商品数
    public function getProductCount(){
        $result = $this->count();
        return $result;
    }

}
