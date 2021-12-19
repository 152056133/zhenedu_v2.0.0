<?php

namespace app\http\middleware;

class AutoLogin
{
    public function handle($request, \Closure $next)
    {
    	$token = Session::get('token');
		if($token != ''){
			return redirect('index/index');
		}
		return $next($request);
    }
}
