<?php

namespace App\Http\Controllers\Courier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\CourierSaasSync;
use App\Models\User;
use Session;
use Yajra\DataTables\Facades\DataTables;
use Validator;
use URL;
class UserControllor extends Controller
{
	
	public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
    public function view(){
        $data['title'] = 'Manage  Users';
        $data['class'] = 'users';
        $data['table'] = 'Manage Users';
        return view("couriers.users.list",$data);
    }

    public function listView(){
        $courier_id = Session::get('courier_id');
        return DataTables::of(User::where('courier_id' , $courier_id))
        ->rawColumns(['delete' => 'delete','action' => 'action'])
         ->addColumn('action', function($data){
                  return  "<a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->user_id."'>Delete</a><a href='".url('/')."/courier/user/edit/".$data->user_id."'  class='all_button btn  btn-sm btn-info' data-value='".$data."' id='".$data->user_id."'>Edit</a>";
                   //return "<a href='".url('/')."/courier/user/edit/".$data->user_id."'  class='all_button btn  btn-sm btn-info' data-value='".$data."' id='".$data->user_id."'>Edit</a>";
                })->make(true);
    }
	public function add(Request $request){
        $data['title'] = 'Add User';
        $data['class'] = 'users';
        $data['table'] = 'Add User';
        $courier_id = session::get('courier_id');
        if ($request->isMethod('get')) {
            return view('couriers/users/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                // 'first_name' => 'required',
                // 'last_name' => 'required',
               // 'email' => 'required|unique:users|max:255',
                
                // 'password' => 'required|same:confirm_password',
                // 'confirm_password' => 'required',
                // 'address_1' => 'required',
                // 'city' => 'required',
                // 'courier_id' => 'required',
                //'postal_code' => 'required',
               // 'phone' => 'required',
                //'mobile' => 'required',
                //'trn' => 'required',
                //'g-recaptcha-response' => 'required|recaptcha'
                //'terms' => 'required|in:1',
            ]);
            if ($validator->fails()) {
                    return redirect('/courier/users')
                            ->withInput()
                            ->withErrors($validator);
            }else{

                
               
                $users = new User();
                $users->mailbox = $request->mailbox;
                $users->first_name = $request->first_name;
                $users->courier_id = $courier_id;
                $users->last_name = $request->last_name;
                $users->email = $request->email;
                
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();
                CourierSaasSync::customer($users->fresh());
               
                //$this->send_email_register($request->email, $user_name,$request->password,$mailbox,$setting->phone,$address1,$address2,$address3,$address4,$address5,$address6,$email_subject, $email_from, $link,$logo,$company_name, 'register_email');
                Session::flash('success_msg', 'New user has been added successfully');
                return redirect('/courier/users');
            }
        }
    }
    public function edit(Request $request , $id = ''){
        $data['title'] = 'Update  User';
        $data['class'] = 'users';
        $data['table'] = 'Update  User';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');;
        if ($request->isMethod('get')) {
            //dd($data['roles']);
            $data['user'] = User::where('user_id' , $id)->first();
            return view('couriers/users/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            // 'first_name' => 'required',
                            // 'last_name' => 'required',
                        ]);
            if ($validator->fails()) {
                return redirect('courier/user/edit/'.$id)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                User::where('user_id', $id)
                        ->update([
                            'first_name' => $request->first_name,
                            'last_name' => $request->last_name,
                            'mailbox' => $request->mailbox,
                            'email' => $request->email,
                            'updated_at' => date('Y-m-d h:i:s')
                        ]);
                CourierSaasSync::customer(User::where('user_id', $id)->first());
                Session::flash('success_msg', ' user has been updated successfully.'); 
                return redirect('/courier/users');
            }
        }
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
	
	public function delete(Request $request) {
		$delete = User::where('user_id' , $request->id)->delete();
		return ["status" => true,"message" =>"User has been deleted successfully."];
        
    }
}
