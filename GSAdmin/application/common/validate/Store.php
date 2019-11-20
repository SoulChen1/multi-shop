<?php

namespace app\common\validate;

use think\Validate;

class Store extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name|店铺名称' => 'require',
        'address|店铺地址' => 'require',
        'password|店铺密码' => 'require',
        'contact|联系方式' => 'require',
        'start_time|营业开始时间' => 'require',
        'end_time|营业结束时间' => 'require',
        'takeoff|起送费' => 'require',
        'free|配送费' => 'require'
    ];

	//登录场景
	public function sceneLogin(){
	    return $this->only(['name','password']);
    }
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
