<?php

namespace app\index\model;
use think\Model;

class GradeModel extends Model
{
	protected $name = 'grade';

	public function getChildId( $pid, $father=0)
	{
        $cat = $this->all();
		$arr = [];
		if ($father == 1) {
            $arr = intval($pid);
        }
        foreach ($cat as $v) {
            if ($v['pid'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, $this->getChildId($v['id']));
            }
        }
        return $arr;
	}

}

?>