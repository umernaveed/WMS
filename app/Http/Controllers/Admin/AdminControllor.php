<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Session;
use App\Models\RoleNew;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Validator;
use Illuminate\Support\Facades\Hash;

class AdminControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Roles';
        $data['class'] = 'users';
        $data['table'] = 'Manage Roles';
        return view("admin.admins.list",$data);
    }

    public function listView($type = ''){
        
        return DataTables::of(User::where('role_id' , '!=' , 0))
                ->addColumn('name', function ($query) {
                    $role = RoleNew::where('role_id' , $query->role_id)->first();
                    if($role){
                        return $role->title;
                    }else{
                        return '';
                    }
                })
                ->editColumn('delete', function ($query) {
                    if($query->status == 0){
                        $btn = '<i class="fa fa-times"></i>';
                    }else{
                        $btn = '<i class="fa fa-check"></i>';
                    }
                    return $btn;
                })
                ->rawColumns(['delete' => 'delete','action' => 'action'])
                ->addColumn('action', function($data){
                    //<a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->user_id."'>Delete</a>
                    return "<a href='".url('/')."/admin/edit/".$data->user_id."'  class='all_button btn  btn-sm btn-info' data-value='".$data."' id='".$data->user_id."'>Edit</a>";
                })->make(true);
    }
        
    public function create(Request $request){
        $data['title'] = 'Add Admin User';
        $data['class'] = 'users';
        $data['table'] = 'Add Admin User';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $setting = DB::table('settings')->first();
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['roles'] = RoleNew::get();
            return view('admin/admins/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'email' => 'required|unique:users|max:255',
                            'password' => 'required|same:confirm_password',
                            'confirm_password' => 'required',
                        ]);
            if ($validator->fails()) {
                    return redirect('admin/user/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                if ($request->hasFile('user_logo')) {
                    $image_name = '';
                    $imageTempName = $request->file('user_logo')->getPathname();
                    $venue_img_extension = $request->user_logo->extension();
                    $imageName = uniqid() . '.' . $venue_img_extension;
                    $path = base_path() . '/public/users/';
                    $request->file('user_logo')->move($path, $imageName);
                    $image = $imageName;
                } else {
                    $image = '';
                }
                $usersRes = User::orderby('user_id' , 'DESC')->first();
                //dd($usersRes);
                //return $usersRes->user_id;
                $user_id_mail = $usersRes->user_id + 1;
                if($usersRes){
                    if($usersRes->user_id > 1000){
                        $mailBoxNo = $setting->pre_fix.'0';
                    }elseif($usersRes->user_id > 100){
                        $mailBoxNo = $setting->pre_fix.'00';
                    }else{
                        $mailBoxNo = $setting->pre_fix.'000';
                    }
                }else{
                    $mailBoxNo = $setting->pre_fix.'0001';
                }
                if($request->role_id == 1){
                    $is_superadmin = 1;
                }else{
                    $is_superadmin = 0;
                }
                $users = new User();
                $users->mailbox = $mailBoxNo.$user_id_mail;
                $users->first_name = $request->first_name;
                $users->last_name = $request->last_name;
                $users->email = $request->email;
                $users->password = Hash::make($request->password);
                $users->role_id = $request->role_id;
                $users->is_superadmin = $is_superadmin;
                $users->status = $request->status;
                $users->image = $image;
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();
                Session::flash('success_msg', 'Admin user has been added successfully'); 
                return redirect('admin/users/list');
            }
        }
    }
    
    public function edit(Request $request , $id = ''){
        $data['title'] = 'Update Admin User';
        $data['class'] = 'users';
        $data['table'] = 'Update Admin User';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['roles'] = RoleNew::get();
            //dd($data['roles']);
            $data['user'] = User::where('user_id' , $id)->first();
            return view('admin/admins/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'first_name' => 'required',
                            'last_name' => 'required',
                        ]);
            if ($validator->fails()) {
                return redirect('admin/user/edit/'.$id)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                if ($request->hasFile('user_logo')) {
                    $image_name = '';
                    $user_img = User::where('user_id' , $user_id)->first();
                    if($user_img){
                        $logo = $user_img->image;
                        @unlink(base_path() . '/public/users/' . $logo);
                    }
                    $imageTempName = $request->file('user_logo')->getPathname();
                    $venue_img_extension = $request->user_logo->extension();
                    $imageName = uniqid() . '.' . $venue_img_extension;
                    $path = base_path() . '/public/users/';
                    $request->file('user_logo')->move($path, $imageName);
                    $image = $imageName;
                } else {
                    $image = '';
                    $user_img = User::where('user_id' , $user_id)->first();
                    if($user_img){
                        $image = $user_img->image;
                    }
                }
                if($request->role_id == 1){
                    $is_superadmin = 1;
                }else{
                    $is_superadmin = 0;
                }
                User::where('user_id', $id)
                        ->update([
                            'first_name' => $request->first_name,
                            'last_name' => $request->last_name,
                            'role_id' => $request->role_id,
                            'is_superadmin' => $is_superadmin,
                            'status' => $request->status,
                            'image' => $image,
                            'updated_at' => date('Y-m-d h:i:s')
                        ]);
                Session::flash('success_msg', 'Admin user has been updated successfully.'); 
                return redirect('admin/users/list');
            }
        }
    }
	
	public function updateStatus($status = '' , $admin_id = '') {
		
		Admin::where('admin_id', $admin_id)
          ->update([
					'status' => $status
					]
				   );
		if($status == 0){
			Session::flash('success_msg', 'Admin has been inactiv successfully.'); 
		}else{
			Session::flash('success_msg', 'Admin has been activ successfully.'); 
		}
		return redirect('admins');
        
    }
	
	public function delete($admin_id = '') {
		
            $delete = Admin::where('admin_id' , $admin_id)->delete();
            Session::flash('success_msg', 'Admin has been deleted successfully.'); 
            return redirect('admins');
        
    }
}
