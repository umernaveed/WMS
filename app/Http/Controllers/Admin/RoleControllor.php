<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PackageStatus;
use App\Models\Role;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;

class RoleControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Roles';
        $data['class'] = 'users';
        $data['table'] = 'Manage Roles';
        return view("admin.roles.list",$data);
    }

    public function listView($type = ''){
        return DataTables::of(Role::query())
                ->addColumn('action', function($data){
                    //<a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->id."'>Delete</a>
                    return "<a href='javascript:void(0)'  class='all_button btn  btn-sm btn-info edit_button' data-value='".$data."' id='".$data->role_id."'>Edit</a>";
                })->make(true);
    }
	
    public function save(){
        $insertData["name"] = request()->name;
        $insertData["created_at"] = date('Y-m-d h:i:s');
        $insertData["updated_at"] = date('Y-m-d h:i:s');
        if(request()->id == 0){
            Role::insert($insertData);
            return ["status" => true,"message" =>"Role added successfully"];
        }else{
            Role::where(["role_id"=>request()->id])->update($insertData);
            return ["status" => true,"message" =>"Role updated successfully"];
        }
    }

    public function delete(){
        Role::where(["role_id"=>request()->id])->delete();
        return ["status" => true,"message" =>"Role deleted successfully"];
    }
}
