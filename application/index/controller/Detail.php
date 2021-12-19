<?php

namespace app\index\controller;
use think\Request;

use app\index\model\CourseModel;

class Detail extends Base
{
	public function class($id)
	{
		$course = CourseModel::get($id);
		// halt($course);
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

	public function substancestudy($courseid)
	{
		return $this->fetch();
	}
}

?>