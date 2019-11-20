<?php

namespace app\common\model;

use think\Model;

class Store extends Model
{
    //登录
    public function login($data){
        //验证数据
        $validate = new \app\common\validate\Store();
        if(!$validate->scene('login')->check($data)){
            return $validate->getError();
        }
        //查询数据
        $result = $this->where($data)->find();
        //返回结果
        if($result){
            if($result['lock'] == 1){
                return '此店铺不可用！';
            }
            /*再添加调整*/
            $sessionData = [
                'id' => $result['id'],
                'name' => $result['name'],
                'address' => $result['address']
            ];
            session('store', $sessionData);
            return true;
        }else{
            return '请检查用户名和密码！';
        }
    }


}
