<?php

namespace app\common\model;

use think\Model;

class Store extends Model
{
    //登录
    public function login($data){
        $validate = new \app\common\validate\Store();
    }
}
