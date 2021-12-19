<?php

namespace app\index\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'user_name' => ['require', 'alphaDash'],
        'user_pwd'  => ['require', 'alphaDash'],
        'mobile'    => ['require', 'mobile'],
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'user_name.require'  =>  '用户名不能为空',
        'user_name.alphaDash'=>  '用户名只能包含字母、数字、下划线',
        'user_pwd.require'   =>  '密码不能为空',
        'user_pwd.alphaDash' =>  '密码只能包含字母、数字和下划线',
        'mobile.require'     =>  '手机号不能为空',
        'mobile.mobile'      =>  '手机号格式不对'
    ];

    /**
     * 定义验证场景
     * 格式：'控制器' =>  ['字段名1','字段名2'...]
     */
    protected $scene = [
        'login'     => ['user_name', 'user_pwd'],
        'register'  => ['user_name', 'user_pwd', 'mobile']
    ];


}
