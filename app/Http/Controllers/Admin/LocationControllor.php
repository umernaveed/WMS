<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Location;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;

class LocationControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Pickup Location';
        $data['class'] = 'locations';
        $data['table'] = 'Manage Pickup Location';
        return view("admin.locations.list",$data);
    }

    public function listView($type = ''){
        
        return DataTables::of(Location::query())
                ->addColumn('action', function($data){
                    return "<a href='javascript:void(0)'  class='all_button btn btn-sm btn-info edit_button' data-value='".$data."' id='".$data->id."'>Edit</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->id."'>Delete</a>";
                })->make(true);
    }
	
    public function index(){
        $data['title'] = 'Manage Pickup Location';
        $data['class'] = 'locations';
        $data['table'] = 'Manage Pickup Location';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['locations'] = Location::get();
        return view('admin/locations/index' , $data);
    }
	
    public function save(){
        $insertData["title"] = request()->title;
        if(request()->id == 0){
            Location::insert($insertData);
            return ["status" => true,"message" =>"Location added successfully"];
        }else{
            Location::where(["id"=>request()->id])->update($insertData);
            return ["status" => true,"message" =>"Location updated successfully"];
        }
    }
	
    public function delete(){
        Location::where(["id"=>request()->id])->delete();
        return ["status" => true,"message" =>"Record deleted successfully"];
    }
	
}
