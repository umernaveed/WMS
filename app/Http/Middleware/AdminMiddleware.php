<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use commonHelper;
class AdminMiddleware
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
        $admin_id  =  Session::get('user_id');
        if (!isset($admin_id) || $admin_id == ''){
            redirect()->to('/admin')->send();
        }

        if(!commonHelper::canAccessAdminPath($request->path())){
            if($request->ajax() || $request->expectsJson()){
                return response()->json(['status' => false, 'message' => 'Permission denied.'], 403);
            }

            return response()->view('admin.denied', [
                'title' => 'Permission Denied',
                'table' => 'Permission Denied',
                'class' => 'denied',
            ], 403);
        }

        return $next($request);
    }
    public function editor(){
        echo "work";
    }
}
