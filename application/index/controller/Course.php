<?php
namespace app\index\controller;
use think\Request;
use app\index\model\CourseModel;
use app\index\model\GradeModel;

class Course extends Base
{
	public function index(Request $request)
	{
		if(input('get.')){

			$data = $request->param();
		// $keyword = $data['keyword'];
			// halt($data);
			$s = input('get.s') ?? '';	// 学段
			$g = input('get.g') ?? '';	// 年级
			$t = input('get.t') ?? '';  // 类型
			$keyword = input('get.keyword') ?? '';  // 搜索关键字

			// 数据库搜索
			$map[] = ['title', 'like', '%'.$keyword.'%'];
			if($s!=''){
				$map[] = ['grade_id1', '=', $s];
			}
			if($g!=''){
				$map[] = ['grade_id2', '=', $g];
			}
			if($t!=''){
				$map[] = ['type_id', '=', $t];
			}
			
			$course = CourseModel::where($map)
						->field('id,title,grade_id1,grade_id2,img,type_id,teacher_id,subject_id')
						->order('create_at desc')
						->paginate(9);
			dump(CourseModel::getLastSql());
			foreach ($course as $key => $value) {
				if($value['type_id'] == 1){
					$course[$key]['type_id'] = '视频';
				}
				if($value['type_id'] == 2){
					$course[$key]['type_id'] = '直播';
				}
				if($value['type_id'] == 3){
					$course[$key]['type_id'] = '图文';
				}
			}
			// dump(CourseModel::getLastSql());	
			// 自定义，用来防止用户随意输入不存在的s值而报错
			$grade = [
				['id' => '6', 'pid' => '1', 'title' => '一年级']
			];
			// 如果用户点击了学段，即s不为空
			if(!empty($s)){
				$grade = GradeModel::field('id, pid, title')->where('pid', $s)->select();
			}
			$section = GradeModel::all(function ($e) {
                $e->field('id, pid, title')->where('pid', 0);
        	});
        	$type = db('type')->select();

			$this->assign('type', $type);
			$this->assign('section', $section);
			$this->assign('grade', $grade);
			$this->assign('course', $course);
			return $this->fetch();
		}

		$section = GradeModel::all(function ($e) {
            $e->field('id, pid, title')->where('pid', 0);
        });
        $grade = GradeModel::all(function ($e) {
            $e->field('id, pid, title')->where('pid', 1);
        });
        $type = db('type')->select();
        $course = CourseModel::paginate(9);
        foreach ($course as $key => $value) {
			if($value['type_id'] == 1){
				$course[$key]['type_id'] = '视频';
			}
			if($value['type_id'] == 2){
				$course[$key]['type_id'] = '直播';
			}
			if($value['type_id'] == 3){
				$course[$key]['type_id'] = '图文';
			}
		}
		$this->assign('type', $type);
		$this->assign('section', $section);
		$this->assign('grade', $grade);
		$this->assign('course', $course);
		return $this->fetch();
	}

	public function search(Request $request)
	{
		$data = $request->param();
		$keyword = $data['keyword'];

		$map[] = ['title', 'like', '%'.$keyword.'%'];
    	// if()
    	// if($s!=''){
    	// 	$map[] = ['grade_id1', '=', $s];
    	// }
    	// if($g!=''){
    	// 	$map[] = ['grade_id2', '=', $g];
    	// }
    	// if($t!=''){
    	// 	$map[] = ['type_id', '=', $t];
    	// }
    	$course = CourseModel::where($map)
    				->field('id,title,grade_id1,grade_id2,img,type_id')
    				->order('create_at desc')
    				->paginate(9);
    	// halt($course->toArray());
		$this->assign('course', $course);

	}

	public function index_(Request $request)
	{
		if(input('post.')){
			$param = $request->param();
			
			$id = input('post.id') ?? '';	// 学段
			$pid = input('post.pid') ?? '0';	// 年级

			// 自定义，用来防止用户随意输入不存在的s值而报错
			$grade = [
				['id' => '6', 'pid' => '1', 'title' => '一年级']
			];
			// 如果用户点击了学段，即s不为空
			if(!empty($id)){
				$grade = GradeModel::field('id, pid, title')->where('pid', $id)->order('id desc')->select();
				// dump($grade);
			}
			return $grade;
		}
		$section = GradeModel::all(function ($e) {
            $e->field('id, pid, title')->where('pid', 0);
        });
        $grade = GradeModel::all(function ($e) {
            $e->field('id, pid, title')->where('pid', 1);
        });

		$this->assign('section', $section);
		$this->assign('grade', $grade);
		return $this->fetch();
	}

	// 仅供测试
	function test()
	{
		// $res = $this->M('ze_type');
		// dump($res);
	}

}

?>