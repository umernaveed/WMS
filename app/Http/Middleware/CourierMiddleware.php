<?php

namespace App\Http\Middleware;

use Closure;
use Session;
class CourierMiddleware
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
        $courier_id  =  Session::get('courier_id');
        if (isset($courier_id) && $courier_id != ''){
        }else{
            redirect()->to('/admin')->send();
        }
        return $next($request);
    }
    public function editor(){
        echo "work";
    }
}
