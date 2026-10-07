<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Purchase;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Models\Role;
use Mail;

class PurchaseControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Purchase Request';
        $data['class'] = 'purchase';
        $data['table'] = 'Manage Purchase Request';
        return view("admin.purchases.list",$data);
    }

    public function listView(){
        $data = Purchase::Join('users','users.user_id' , '=' ,'purchases.user_id')->select('purchases.*','users.mailbox');
        return DataTables::of($data)->make(true);
    }
	
    public function index(){
        $data['title'] = 'Manage Purchase Request';
        $data['class'] = 'purchase';
        $data['table'] = 'Manage Purchase Request';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if($role_id == 1){
            
        }else{
            $data['purchase'] = Purchase::where('user_id' , $user_id)->get();
        }
        
        return view('admin/purchases/index' , $data);
    }
    
    public function create(Request $request){
        $data['title'] = 'Add Purchase Request';
        $data['class'] = 'purchase';
        $data['table'] = 'Add Purchase Request';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $user_id = Session::get('user_id');
        if ($request->isMethod('get')) {
            return view('admin/purchases/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'notes' => 'required',
                                'qty' => 'required',
                                'link' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('user/purchase/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                $purchase = new Purchase();
                $purchase->user_id = $user_id;
                $purchase->name = $request->name;
                $purchase->link = $request->link;
                $purchase->qty = $request->qty;
                $purchase->notes = $request->notes;
                $purchase->created_at = date('Y-m-d h:i:s');
                $purchase->updated_at = date('Y-m-d h:i:s');
                $purchase->save();
                
                $usersRes = User::where('user_id' , $user_id)->first();
                $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                $email_subject = 'Purchase Request';
                $email_from = 'send@rsbshipping.com';
                $this->send_email($usersRes->email, $user_name,$request->name,$request->link,$request->qty,$request->notes,$email_subject, $email_from, 'purchase_request');
                    
                Session::flash('success_msg', 'Purchase request has been saved successfully.'); 
                return redirect('user/purchase/request');
                
            }
        }
    }
    
    public function edit(Request $request , $id = ''){
        $data['title'] = 'Update Purchase Request';
        $data['class'] = 'purchase';
        $data['table'] = 'Update Purchase Request';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['roles'] = Role::get();
            $data['purchase'] = Purchase::where('id' , $id)->first();
            return view('admin/purchases/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'name' => 'required',
                            'notes' => 'required',
                            'qty' => 'required',
                            'link' => 'required'
                        ]);
            if ($validator->fails()) {
                return redirect('user/purchase/edit/'.$id)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                Purchase::where('id', $id)
                        ->update([
                            'name' => $request->name,
                            'link' => $request->link,
                            'qty' => $request->qty,
                            'notes' => $request->notes
                        ]);
                Session::flash('success_msg', 'Purchase request has been updated successfully.'); 
                return redirect('user/purchase/request');
            }
        }
    }
    
    public function send_email($email, $user_name,$name,$link,$qty,$notes,$email_subject, $email_from,$view_name) {
        $res['userName'] = $user_name;
        $res['name'] = $name;
        $res['link'] = $link;
        $res['qty'] = $qty;
        $res['notes'] = $notes;
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject) {
            $message->from($email_from, $name = 'RSB Shipping');
            //$message->to('ronald@igdjamaica.com', $user_name)->subject($email_subject);
            $message->to('rizwan@decodershub.com', $user_name)->subject($email_subject);
            //$message->to('ronald@igdjamaica.com', $user_name)->subject($email_subject);
        });
    }
}
