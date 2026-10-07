<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PackageStatus;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;

class StatusControllor extends Controller
{
	
	public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
	public function index(){
		$data['title'] = 'Manage Tracking Status';
		$data['class'] = 'status';
		$data['table'] = 'Manage Tracking Status';
                $role_id= Session::get('role_id');
                $user_id= Session::get('user_id');
                //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
                $data['getModues'] = '';
		$data['status'] = PackageStatus::get();
		return view('admin/status/index' , $data);
	}
	
    public function create(Request $request){
        $data['title'] = 'Add Tracking Status';
        $data['class'] = 'status';
        $data['table'] = 'Add Tracking Status';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
                return view('admin/status/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'name' => 'required',
                            'note' => 'required',
                        ]);
            if ($validator->fails()) {
                    return redirect('admin/status/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{

                $package_status = new PackageStatus();
                $package_status->name = $request->name;
                $package_status->note = $request->note;
                $package_status->created_at = date('Y-m-d h:i:s');
                $package_status->updated_at = date('Y-m-d h:i:s');
                $package_status->save();
                Session::flash('success_msg', 'Tracking Status has been added successfully'); 
                return redirect('admin/package/status');
            }
        }
    }
	
    public function edit(Request $request , $id = ''){
        $data['title'] = 'Edit Tracking Status';
        $data['class'] = 'status';
        $data['table'] = 'Edit Tracking Status';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
                $data['status_pak'] = PackageStatus::where('id' , $id)->first();
                return view('admin/status/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'name' => 'required',
                            'note' => 'required',
                        ]);
            if ($validator->fails()) {
                return redirect('admin/status/edit/'.$id)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                PackageStatus::where('id', $id)
                        ->update([
                            'name' => $request->name,
                            'note' => $request->note,
                        ]);
                Session::flash('success_msg', 'Tracking Status has been updated successfully.'); 
                return redirect('admin/package/status');
            }
        }
    }
	
    public function delete($id = '') {
        $delete = PackageStatus::where('id' , $id)->delete();
        Session::flash('success_msg', 'Tracking Status has been deleted successfully.'); 
        return redirect('admin/package/status');
    }
	
}
