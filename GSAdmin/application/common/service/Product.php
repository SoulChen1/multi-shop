<?php

namespace app\common\service;

use think\Model;

class Product extends Model
{
    /**
     * 获取商品数
    */
    public function getProductCount(){
        return model('Product', 'model')->getProductCount();
    }
}