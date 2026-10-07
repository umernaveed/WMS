<?php

namespace App\Http\Controllers\Outlet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Session;
use Yajra\DataTables\Facades\DataTables;

class UserControllor extends Controller
{
	
	public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
    public function view(){
        $data['title'] = 'Manage Outlet Users';
        $data['class'] = 'users';
        $data['table'] = 'Manage Outlet Users';
        return view("outlet.users.list",$data);
    }

    public function listView(){
        $outlet_id = Session::get('outlet_id');
        return DataTables::of(User::where('outlet_id' , $outlet_id))->make(true);
    }
	
	public function updateStatus($status = '' , $user_id = '') {
		
		User::where('user_id', $user_id)
          ->update([
					'status' => $status
					]
				   );
		if($status == 0){
			Session::flash('success_msg', 'User has been inactiv successfully.'); 
		}else{
			Session::flash('success_msg', 'User has been activ successfully.'); 
		}
		return redirect('admin/users');
        
    }
	
	public function delete($user_id = '') {
		
		$delete = User::where('user_id' , $user_id)->delete();
		Session::flash('del_msg', 'User has been deleted successfully.'); 
		return redirect('admin/users');
        
    }
}
