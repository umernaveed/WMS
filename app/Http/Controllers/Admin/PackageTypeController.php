<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PackageType;
use Session;
use DB;
use Validator;
use image;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class PackageTypeController extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }



    public function index(){
        $data['title'] = 'Manage Package Categories';
        $data['class'] = 'Package Categories';
        $data['table'] = 'Manage Package Categories';
        $data['packagetype'] = PackageType::get();
        return view('admin/package_type/list' , $data);
    }

    public function add(Request $request){
        $data['title'] = 'Add Package Category';
        $data['class'] = 'Package Category';
        $data['table'] = 'Add Package Category';
        if ($request->isMethod('get')) {
                return view('admin/package_type/new' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                //'email' => 'required|unique:couriers|max:255',
               // 'password' => 'required|same:confirm_password',
               // 'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('admin/package/type/create')
                                ->withInput()
                                ->withErrors($validator);
            } else {
                PackageType::create([
                    'type' => $request->type,
                    'code' => $request->code,
                ]);
                Session::flash('success_msg', 'Package type has been created successfully.');
                return redirect('admin/package/type');
            }
        }
    }
    public function edit(Request $request, $id = '') {
        $data['title'] = 'Edit Package Category';
        $data['class'] = 'Package Type';
        $data['table'] = 'Edit Package Category';
        if ($request->isMethod('get')) {
                $data['package_type'] = PackageType::where('id' , $id)->first();
                //dd($data['couriers']);
                return view('admin/package_type/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                // 'first_name' => 'required',
                // 'last_name' => 'required',
                // 'email' => 'required|unique:users|max:255',
                // 'password' => 'required|same:confirm_password',
                // 'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('admin/package/type/edit/'.$id)
                                ->withInput()
                                ->withErrors($validator);
            } else {
                PackageType::where('id', $id)
                ->update([
                    'type' => $request->type,
                    'code' => $request->code,
                    //'awb_number' => 'awb.'.random_int(1000000000, 9999999999),

                ]);
                Session::flash('success_msg', 'Package Type has been updated successfully.');
                return redirect('admin/package/type');
            }
        }
    }

    public function delete(){
        PackageType::where(["id"=>request()->id])->delete();
        Session::flash('success_msg', 'Package Type has been deleted successfully.');
        return redirect('admin/package/type');
    }
}
