<?php

namespace App\Http\Controllers\Outlet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\Outlet;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;

class DashboardControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
    public function dashboard(Request $request) { 
        $data['title'] = 'Dashboard';
        $data['class'] = 'dashboard';
        $data['setting'] = DB::table('settings')->first();
        return view('outlet/dashboard/dashboard' , $data);
    }
    
    public function listViewReady(){
        $outlet_id= Session::get('outlet_id');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' , 1)
                    ->where('users.outlet_id' , $outlet_id);
        return DataTables::of($query)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
    }
    
    public function listViewRecent(){
        $outlet_id= Session::get('outlet_id');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' ,'!=' , 1)
                    ->where('users.outlet_id' , $outlet_id);
        return DataTables::of($query)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
    }

    private function packageMeasureDisplay($package): string
    {
        if(stripos((string) ($package->freight_type ?? ''), 'sea') !== false){
            $cuft = (float) (($package->volume_cuft ?? 0) ?: ($package->chargeable_weight ?? 0));
            $cbm = (float) (($package->volume_cbm ?? 0) ?: ($cuft > 0 ? $cuft / 35.3147 : 0));

            return number_format($cuft, 2).' cu.ft / '.number_format($cbm, 3).' CBM';
        }

        $unit = strtolower((string) ($package->weight_unit ?? 'lb')) === 'kg' ? 'kg' : 'lb';

        return number_format((float) ($package->weight ?? 0), 2).' '.$unit;
    }
	
    public function profile(Request $request) { 
		$data['title'] = 'Manages Profile';
		$data['class'] = 'dashboard';
		$data['table'] = 'Update Profile';
		$user_id= Session::get('user_id');
		if ($request->isMethod('get')) {
			$data['users'] = User::where('user_id' , $user_id)->first();
			return view('front/dashboard/create' , $data);
		}else{
			$validator = Validator::make($request->all(), [
				'name' => 'required',
				'password' => 'same:confirm_pass',
				'phone' => 'required|numeric',
				'address' => 'required',
			]);
			if ($validator->fails()) {
				return redirect('user/profile')
					->withInput()
					->withErrors($validator);
				
			}else{
				$password = $request->password;
				if($password == ''){
					User::where('user_id', $user_id)
							  ->update([
										'name' => $request->name,
										'phone' => $request->phone,
										'address' => $request->address
								]);
					Session::put('name', $request->name);
					Session::flash('success_msg', 'Profile has been updated successfully.'); 
					return redirect('user/profile');
				}else{
					User::where('user_id', $user_id)
							  ->update([
										'name' => $request->name,
										'phone' => $request->phone,
										'address' => $request->address,
										'password' => Hash::make($password)
										
								]);
					Session::put('name', $request->name);
					Session::put('phone', $request->phone);
					Session::flash('success_msg', 'Profile has been updated successfully.'); 
					return redirect('user/profile');
				}
			}
		}
		
    }
    
    public function changePassword(Request $request) {
        $data['title'] = 'Change Password';
        $data['class'] = 'users';
        $data['table'] = 'Change Password';
        $outlet_id = Session::get('outlet_id');
        if ($request->isMethod('get')) {
            //$data['email'] = $id;
            return view('outlet/users/change_password', $data);
        }else{
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('outlet/change/password')
                        ->withInput()
                        ->withErrors($validator);
            }else{
                $users = Outlet::where('outlet_id', $outlet_id)->first();
                if($users){
                    $user_password = $users->password;
                    if(Hash::check($request->old_password, $user_password)){
                        $password = $request->input("new_password");
                        Outlet::where('outlet_id', $outlet_id)->update(['password' => Hash::make($password),'password_hint' => $password]);
                        Session::flash('error_msg', 'Your password was reset successfully. You may now log in with new password.'); 
                        $request->session()->flush();
                        return redirect('outlet/change/password');
                    }else{
                        Session::flash('error_msg', 'Your current password does not matched'); 
                        return redirect('outlet/change/password');
                    }
                }else{
                    Session::flash('error_msg', 'User does not exist in our database.'); 
                    return redirect('outlet/change/password');
                }  
            }
        }
    }
}
