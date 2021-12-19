<?php

namespace app\index\controller;
use think\Controller;
use think\facade\Session;

class Base extends Controller
{
	public function initialize()
	{
		// 判断是否登录
		$token = Session::get('token');
		if($token != null){
			$isLog = 1;
		}else{
			$isLog = 0;
		}
		$this->assign('isLog', $isLog);

		$controller = request()->controller();
		// 遍历前台菜单
		$menu = model('IndexMenuModel')->field('name,c')->select();
		$this->assign('menu', $menu);
		$this->assign('controller', $controller);

	}
}

?>