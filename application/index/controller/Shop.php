<?php

namespace app\index\controller;
use think\Ruquest;

class Shop extends Base
{
	public function index()
	{
		return $this->fetch();
	}
}

?>