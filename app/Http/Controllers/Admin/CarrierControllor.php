<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use Validator;
use image;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Carrier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarrierControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }



    public function index(){
        $data['title'] = 'Manage Couriers';
        $data['class'] = 'Couriers';
        $data['table'] = 'Manage Couriers';
        $couriers = Carrier::get();
        $data['couriers'] = $couriers;
        
        return view('admin/carrier/index' , $data);
    }
    
  


    public function add(Request $request){
        $data['title'] = 'Add Courier';
        $data['class'] = 'Couriers';
        $data['table'] = 'Add Courier';
        if ($request->isMethod('get')) {
                return view('admin/carrier/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                // 'email' => 'required|unique:couriers|max:255',
                // 'password' => 'required|same:confirm_password',
                // 'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('admin/carrier/add')
                                ->withInput()
                                ->withErrors($validator);
            } else {
               
                Carrier::create([
                    'name' => $request->name
                ]);
                Session::flash('success_msg', 'Courier has been created successfully.');
                return redirect('admin/carriers');
            }
        }
    }
    public function edit(Request $request, $id = '') {
        $data['title'] = 'Edit Courier';
        $data['class'] = 'Couriers';
        $data['table'] = 'Edit Courier';
        if ($request->isMethod('get')) {
                $data['couriers'] = Carrier::where('id' , $id)->first();
                //dd($data['couriers']);
                return view('admin/carrier/edit' , $data);
        }else{
            if($request->email != $request->old_email){
                $validator = Validator::make($request->all(), [
                              //  'email' => 'required|unique:couriers|max:255',
                            ]);
            }else{
                                $validator = Validator::make($request->all(), [
                               // 'email' => 'required|unique:couriers|max:255',
                            ]);
            }
            
            if ($validator->fails()) {
                return redirect('admin/carrier/edit/'.$id)
                                ->withInput()
                                ->withErrors($validator);
            } else {
               

                Carrier::where('id', $id)
                ->update([
                    'name' => $request->name,

                ]);
                Session::flash('success_msg', 'Courier has been updated successfully.');
                return redirect('admin/carriers');
            }
        }
    }

    public function delete(){
        Carrier::where(["id"=>request()->id])->delete();
        Session::flash('success_msg', 'Courier has been deleted successfully.');
        return redirect('admin/carriers');
    }
    
    
}
