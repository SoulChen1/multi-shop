<?php

namespace app\common\service;

use think\Model;

class User extends Model
{
    /**
     * 获取用户数
     **/
    public function getUserCount(){
        return model('User', 'model')->getUserCount();
    }
}