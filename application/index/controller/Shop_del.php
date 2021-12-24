<?php

namespace app\index\controller;
use think\Request;
use think\Db;
use think\facade\Session;

class Shop extends Base
{
	private $redis;

	public function initialize(){
		parent::initialize();

		$this->redis = new \Redis();
		$this->redis->connect('127.0.0.1', 6379);
	}

	public function index()
	{
		$course = Db::table('ze_course_1')->where('id', 1)->select();
		$course = $course[0];
		if($course['type_id'] == 1){
			$course['type'] = '视频';
		}
		if($course['type_id'] == 2){
			$course['type'] = '直播';
		}
		if($course['type_id'] == 3){
			$course['type'] = '图文';
		}
		$this->assign('course', $course);
		return $this->fetch();
	}

	public function order(Request $request)
	{
		// 对ip进行访问控制
		$ip = $request->ip();
		// 用户第一次访问，记录redis，ip为键，次数为值
		if(!$this->redis->get($ip)){
			$this->redis->set($ip, 1);
		} else {  // 用户再次访问，则让次数加1
			$this->redis->incr($ip);	
		}	
		$count = $this->redis->get($ip);	
		// 如果用户访问次数大于3次，则就限制访问
		if($count > 3){
			return ['status'=>'0', 'msg'=>'该IP访问次数过多'];
		}else{
			// 业务处理
			$id = $request->param('val'); // 秒杀商品id
			$data = $this->redisms($id);
			return $data;
		}	
	}


	// redis处理秒杀
	private function redisms($id)
	{
		// 数量抢购完，结束秒杀
		$num = $this->redis->get('goods_'.$id);
		if($num <= 0){
			return ['status'=>'0', 'msg'=>'秒杀已经结束'];
		}
		// 让商品数减一
		$this->redis->decr('goods_'.$id);
		$username = Session::get('username');
		$user_id = Session::get('user_id');
		// 抢购成功一个，将用户信息及商品信息添加到redis的orders列表中
		$this->redis->lpush('orders', $username.'---->'.$user_id.'---->'.$id);
		return ['status'=>'1', 'msg'=>'秒杀成功'];
	}

	private function mysqlms($id)
	{
		// 启动事务
		Db::startTrans();
		try{
			// 让商品数减一
			$res = Db::table('ze_course_1')->where('id', $id)->setDec('course_num');
			$num = Db::table('ze_course_1')->where('id', $id)->value('course_num');
			if($num < 0){
				return ['status'=>'0', 'msg'=>'秒杀已经结束'];
			}
			// 插入orders表
			if($res){
				$username = Session::get('username');
				$user_id = Session::get('user_id');
				Db::table('ze_order_1')->insert(['user_id'=>$user_id,'mobile'=>$username,'goods_id'=>$id]);
				// 启动事务
				Db::commit();
				return ['status'=>'1', 'msg'=>'秒杀成功'];
			}
		}catch(\Exception $e){
			// 回滚事务
			Db::rollback();
			return ['status'=>'0', 'msg'=>'执行错误'];
		}
	}

	public function getTime()
	{
		// 首先获取开始抢购的时间
		$put_time = Db::table('ze_course_1')->where('id',1)->value('put_time');
		// 获取本地的时间戳
		$local_time = time();
		$time = $put_time-$local_time;
		// 获取小时
		$hour = floor($time/3600);
		// 获取分钟
		$min = floor($time/60);
		// 获取秒数
		$sec = $time-$hour*3600-$min*60;
		$url = md5(Session::get('token').'1');
		$data = ['status'=>'1','url'=>$url,'time'=>$time,'hour'=>$hour,'min'=>$min,'sec'=>$sec];

		return $data;
		
	}

	public function test()
	{
		$put_time = 1640313759;
		// 获取本地的时间戳
		$local_time = time();
		$time = $put_time-$local_time;
		// return $time;
		return json(['time'=>$time]);	
	}

	public function isLogin()
	{
		$token = Session::get('token');
		return $token;
	}

}

?>