<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Setting;
use Session;
use App\Models\Role;
use DB;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class OutletControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Outlets';
        $data['class'] = 'outlets';
        $data['table'] = 'Manage Outlets';
        return view("admin.outlets.list",$data);
    }

    public function listView()
    {
        return DataTables::of(Outlet::query())
                 ->addColumn('action', function($data){
                    return "<a href='".url('/')."/admin/outlet/packages/".$data->outlet_id."'  class='all_button btn  btn-sm btn-success'  data-value='".$data."' id='".$data->outlet_id."'>Outlet Packages</a><a href='".url('/')."/admin/outlet/edit/".$data->id."'  class='all_button btn  btn-sm btn-info'  data-value='".$data."' id='".$data->id."'>Edit</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->id."'>Delete</a>";
                })->make(true);;
    }
    
    public function viewPackages($outlet_id = ''){
        $data['title'] = 'Manage Outlet Packages';
        $data['class'] = 'outlets';
        $data['table'] = 'Manage Outlet Packages';
        $data['id'] = $outlet_id;
        return view("admin.outlets.package_list",$data);
    }

    public function listViewPackages($outlet_id = '')
    {
        $data = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    //->where('is_temprary_logs' , 0)
                    ->where('users.outlet_id' , $outlet_id);
        return DataTables::of($data)
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
	
    public function index(){
        $data['title'] = 'Manage Outlets';
        $data['class'] = 'outlets';
        $data['table'] = 'Manage Outlets';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        //$data['users'] = User::where('role_id' , '!=' , 0)->get();
        $data['outlets'] = Outlet::get();
        return view('admin/outlets/index' , $data);
    }
    
    
    public function packages($outlet_id = ''){
        $data['title'] = 'Manage Outlet Packages';
        $data['class'] = 'outlets';
        $data['table'] = 'Manage Outlet Packages';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['packages'] = DB::table('packages')
                                ->select('packages.*','users.first_name','users.last_name','package_status.name')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->join('package_status', 'package_status.id', '=', 'packages.status')
                                //->where('is_temprary_logs' , 0)
                                ->where('users.outlet_id' , $outlet_id)
                                ->get();
        return view('admin/outlets/package_index' , $data);
    }
        
    public function create(Request $request){
        $data['title'] = 'Add Oulet';
        $data['class'] = 'outlets';
        $data['table'] = 'Add Outlet';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $Setting = Setting::first();
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            return view('admin/outlets/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'outlet_name' => 'required',
                            'password' => 'required',
                            'email' => 'required|unique:outlets|max:255',
                            'outlet_trak_id' => 'required|unique:outlets|max:255',
                            'phone' => 'required',
                        ]);
            if ($validator->fails()) {
                    return redirect('admin/outlet/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
               
                $outlets = new outlet();
                $outlets->outlet_id = $Setting->pre_fix.$request->short_name.'-'.substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789123456789123456789321654987'),0,6);
                $outlets->outlet_name = $request->outlet_name;
                $outlets->short_name = $request->short_name;
                $outlets->outlet_trak_id = $request->outlet_trak_id;
                $outlets->email = $request->email;
                $outlets->password = Hash::make($request->password);
                $outlets->password_hint = $request->password;
                $outlets->phone = $request->phone;
                $outlets->created_at = date('Y-m-d h:i:s');
                $outlets->updated_at = date('Y-m-d h:i:s');
                $outlets->save();
                
                Session::flash('success_msg', 'Outlet user has been added successfully'); 
                return redirect('admin/outlets');
            }
        }
    }
    
    public function edit(Request $request , $id = ''){
        $data['title'] = 'Update Outlet';
        $data['class'] = 'outlets';
        $data['table'] = 'Update Outlet';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['roles'] = Role::get();
            //dd($data['roles']);
            $data['outlet'] = Outlet::where('id' , $id)->first();
            return view('admin/outlets/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                             'outlet_name' => 'required',
                            'phone' => 'required',
                        ]);
            if ($validator->fails()) {
                return redirect('admin/outlet/edit/'.$id)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                $outlet_id = Outlet::where('id' , $id)->first()->outlet_id;
                $outlet_id = explode('-',$outlet_id);
                Outlet::where('id', $id)
                        ->update([
                            'outlet_id' => $outlet_id[0].'-'.$request->short_name.'-'.$outlet_id[2],
                            'outlet_name' => $request->outlet_name,
                            'phone' => $request->phone,
                            'short_name' => $request->short_name,
                            'password' => Hash::make($request->password),
                            'password_hint' => $request->password,
                            'updated_at' => date('Y-m-d h:i:s')
                        ]);
                Session::flash('success_msg', 'outlet user has been updated successfully.'); 
                return redirect('admin/outlets');
            }
        }
    }
	
    public function delete(){
        Outlet::where(["id"=>request()->id])->delete();
        return ["status" => true,"message" =>"Record deleted successfully"];
    }
}
