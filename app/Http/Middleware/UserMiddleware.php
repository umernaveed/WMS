<?php

namespace App\Http\Middleware;

use Closure;
use Session;
class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		//echo 'dddddd';exit;
        $site_user_id  =  Session::get('site_user_id');
        if (isset($site_user_id) && $site_user_id != ''){
        }else{
            redirect()->to('/admin')->send();
        }
        return $next($request);
    }
    public function editor(){
        echo "work";
    }
}
