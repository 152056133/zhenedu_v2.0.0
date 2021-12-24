<?php

namespace app\index\controller;
use think\Request;
use think\Db;

class Shop extends Base
{
	private $redis;
	private $time;

	public function initialize()
	{
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

	// 获取开始秒杀时间
	public function countdown(Request $request)
	{
		$id = $request->param('val') ?? '';
		// 首先获取开始抢购的时间
		$put_time = Db::table('ze_course_1')->where('id',$id)->value('put_time');
		// 获取本地的时间戳
		$local_time = time();
		$time = $put_time-$local_time;
		$this->time = $time;
		// 获取小时
		$hour = floor($time/3600);
		// 获取分钟
		$min = floor($time/60);
		// 获取秒数
		$sec = $time-$hour*3600-$min*60;

		$date = [
			'time' => $time,
			'hour' => $hour,
			'min'  => $min,
			'sec'  => $sec,
		];
		return $date;		
	}

	// 秒杀开始
	public function begin()
	{
		// 判断是否登录
		if(session('token') == ''){
			return ['status'=>0, 'msg'=>'请先登录'];
		}
		// 先判断是否到秒杀时间
		$id = request()->param('val') ?? '';
		if($this->time > 0){
			return ['status'=>0, 'msg'=>'秒杀暂未开始'];
		}

		// 对ip进行访问控制
		$ip = request()->ip();
		if(!$this->redis->get($ip)){ // 用户第一次访问，记录进redis，ip为键，次数为值
			$this->redis->set($ip, 1);
		} else { // 用户再次访问，则让次数加1
			$this->redis->incr($ip);
		}
		if($this->redis->get($ip) > 3){
			return ['status'=>0, 'msg'=>'ip访问次数过多'];
		}

		$url = md5(session('username').rand(1000,9999).session('user_id'));
		$this->redis->set('url', $url);

		return ['status'=>1, 'msg'=>'', 'url'=>$url];
	}

	// 秒杀处理逻辑
	public function order(Request $request)
	{
		$url = $request->param('url');
		if($url != $this->redis->get('url')){
			return ['status'=>0, 'msg'=>'秒杀地址错误','url'=>$url];
		}
		// 业务处理
		if(!$this->redis->get('record')){
			$this->redis->set('record', 1); // 1表示已参与 0表示未参与
			$id = $request->param('val');
			$data = $this->mysqlms($id);
			// $data = $this->redisms($id);
			return $data;
		}else{
			// $this->redis->set('record', 0);
			return ['status'=>0, 'msg'=>'您已经参与过啦'];
		}
	}

	// redis处理秒杀
	private function redisms($id)
	{
		// 数量抢购完，结束秒杀
		$num = $this->redis->get('goods_'.$id);
		if($num <= 0){
			return ['status'=>3, 'msg'=>'秒杀已经结束'];
		}
		// 让商品数减一
		$this->redis->decr('goods_'.$id);
		// 抢购成功一个，将用户信息及商品信息添加到redis的orders列表中
		$this->redis->lpush('orders', session('username').'---->'.session('user_id').'---->'.$id);
		return ['status'=>1, 'msg'=>'恭喜您，秒杀成功！'];
	}

	// mysql处理秒杀
	private function mysqlms($id)
	{
		// 启动事务
		Db::startTrans();
		try{
			// 让商品数减一
			$res = Db::table('ze_course_1')->where('id', $id)->setDec('course_num');
			$num = Db::table('ze_course_1')->where('id', $id)->value('course_num');
			if($num < 0){
				return ['status'=>0, 'msg'=>'秒杀已经结束'];
			}

			if($res){
				// 插入orders表
				Db::table('ze_order_1')->insert(['user_id'=>session('user_id'),'mobile'=>session('username'),'goods_id'=>$id]);
				// 启动事务
				Db::commit();
				return ['status'=>1, 'msg'=>'秒杀成功'];
			}
		}catch(\Exception $e){
			// 回滚事务
			Db::rollback();
			return ['status'=>0, 'msg'=>'执行错误','data'=>$e->getMessage()];
		}
	}
}

?>