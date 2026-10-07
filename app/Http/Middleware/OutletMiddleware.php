<?php

namespace App\Http\Middleware;

use Closure;
use Session;
class OutletMiddleware
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
        $outlet_id  =  Session::get('outlet_id');
        if (isset($outlet_id) && $outlet_id != ''){
        }else{
            redirect()->to('/admin')->send();
        }
        return $next($request);
    }
    public function editor(){
        echo "work";
    }
}
