<?php

namespace app\common\model;

use think\Model;

class User extends Model
{
    //获取用户数
    public function getUserCount(){
        $result = $this->count();
        return $result;
    }
    
}
