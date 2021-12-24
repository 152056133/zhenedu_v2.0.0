<?php

namespace app\index\controller;
use think\Request;
use think\Loader;
use think\facade\Session;
use think\facade\Cache;

use app\index\model\LoginModel;

class Login extends Base
{
	// 注册
	public function register(Request $request)
	{
		$param = $request->param();
		$user_name = check_sql($param['user_name']) ?? '';
		$user_pwd  = check_sql($param['user_pwd']) ?? '';
		$mobile    = check_sql($param['mobile']) ?? '';
		$code	   = check_sql($param['code']) ?? '';

		$user_name = check_null($user_name);
		$user_pwd  = check_null($user_pwd);
		$mobile    = check_null($mobile);
		$code      = check_null($code);

		if($user_name == ''){
			return ['status' => 500, 'msg' => '用户名不能为空'];
		}
		if($user_pwd == ''){
			return ['status' => 500, 'msg' => '密码不能为空'];
		}
		if($mobile == ''){
			return ['status' => 500, 'msg' => '手机号不能为空'];
		}
		if($code == ''){
			return ['status' => 500, 'msg' => '验证码不能为空'];
		}

		// 用户唯一键值，生成于函数getRegCode
		$ip = $request->ip();
		$ip = str_replace('.', '', $ip);
		$key = 'code'.$ip;
		if(Cache::get($key) == ''){
			return ['status' => 500, 'msg' => '请发送验证码'];
		}
		if(Cache::get($key) != $code){
			return ['status' => 500, 'msg' => '验证码不正确'];
		}

		$avatar='../../static/images/default.png';
        $avatar_thumb='../../static/images/default_thumb.png';

		$param['register_ip']  = $request->ip();
		$param['user_pwd'] 	   = md5($user_pwd);
		$param['avatar'] 	   = $avatar;
		$param['avatar_thumb'] = $avatar_thumb;

		if(LoginModel::where('user_name', $user_name)->find()){
			return ['status' => 500, 'msg' => '用户名已存在'];
		}
		$user = new LoginModel;
		$res = $user->save($param);
		return $res ? ['status' => 200, 'msg' => '注册成功'] : ['status' => 500, 'msg' => '注册失败'];
	}

	public function login()
	{
		return $this->fetch();
	}

	// 弹窗 密码登录
	public function loginByPass(Request $request)
	{
		$param = $request->param();
		$user_name = check_sql($param['user_name']) ?? '';
		$user_pwd = check_sql($param['user_pwd']) ?? '';

		$user_name = check_null($param['user_name']);
		$user_pwd = check_null($param['user_pwd']);

		if($user_name == ''){
			return ['status' => 500, 'msg' => '用户名不能为空'];
		}
		if($user_pwd == ''){
			return ['status' => 500, 'msg' => '密码不能为空'];
		}

		$user = LoginModel::where('user_name', $user_name)->find();
		if (!$user) {
			return ['status' => 500, 'msg' => '用户名不存在'];
		}

		$md5_password = $user->user_pwd;
		$user_id = $user->id;
		if(md5($user_pwd) != $md5_password){
			return ['status' => 500, 'msg' => '用户名或密码不正确'];
		} else {
			$token = $request->token();
			Session::set('username', $user_name);
			Session::set('user_id', $user_id);
			Session::set('token', $token);
			
			$user->token = $token;
			$res = $user->save();
			if($res){
				$this->assign('userInfo', $user);
				return ['status' => 200, 'msg' => '登录成功'];
			}else{
				$this->assign('isLog', 0);
				return ['status' => 500, 'msg' => '系统繁忙'];
			}
		}
	}

	public function logout()
	{
		$username = Session::get('username');
		$user = LoginModel::where('user_name', $username)->find();
		$user->save(['token'=>'']);
		Session::set('username', null);
		Session::set('token', null);
		return ['status' => 200, 'msg' => '退出成功'];
	}

	// 生成短信验证码，存储到redis，并发送
	public function getRegCode(Request $request)
	{
		// 生成验证码
		$yun_code = rand(1000,9999);
		$redis = new \Redis();
		$redis->connect('127.0.0.1', 6379);
		// 每个用户生成唯一键值
		$ip = $request->ip();
		$ip = str_replace('.', '', $ip);
		$key = 'code'.$ip;
		// 存储到redis，有效期5分钟
		$redis->set($key, $yun_code, 300);
		// 发送短信
		$mobile = $request->param('mobile');
		$type   = $request->param('type');

		$res = sendTemplateSMS($mobile, array($yun_code,'5'), $type);
		if($res['status'] == 0){
			$res['msg']='发送成功';
		}
		return $res;
		// $arList = $redis->keys("*");
	}

	// 仅供测试
	public function test()
	{
		$res = sendTemplateSMS('15735655742', array('1234','5'), 1);
		if($res['status'] == 0){
			$res['msg']='发送成功';
		}
		dump($res);

		$ip = request()->ip();
		$ip = str_replace('.', '', $ip);
		$key = 'code'.$ip;
		echo $key;
	}

}

?>