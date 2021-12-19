<?php
namespace app\index\controller;
use think\Ruquest;

class App extends Base
{
	public function index()
	{
		return $this->fetch();
	}
}

?>