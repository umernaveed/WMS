<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PackageStatus;
use App\Models\WeightNew;
use App\Models\Courier;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;

class WeightControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Weight';
        $data['class'] = 'settings';
        $data['table'] = 'Manage Weight';
        $data['couriers'] = Courier::get();
        return view("admin.weight.list",$data);
    }

    public function listView(){
        $weight = WeightNew::where('courier_id' , '!=' , 0);
        return DataTables::of($weight)
                 ->editColumn('name', function ($data) {
                    $courier = Courier::where('id',$data->courier_id)->first();
                    if($courier){
                        return $courier->name;
                    }else{
                        return '';
                    }
                })
                ->addColumn('action', function($data){
                    //<a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->id."'>Delete</a>
                    return "<a href='javascript:void(0)'  class='all_button btn  btn-sm btn-info edit_button' data-value='".$data."' id='".$data->id."'>Edit</a>";
                })->make(true);
    }
	
    public function save(){
        $insertData["courier_id"] = request()->courier_id;
        $insertData["weight"] = request()->weight;
        $insertData["price"] = request()->price;
        if(request()->id == 0){
            $checkWeight = WeightNew::where('courier_id',request()->courier_id)->where('weight' , request()->weight)->first();
            if($checkWeight){
                WeightNew::where(["id"=>$checkWeight->id])->update($insertData);
            }else{
                WeightNew::insert($insertData);
            }
            
            return ["status" => true,"message" =>"Weight added successfully"];
        }else{
            WeightNew::where(["id"=>request()->id])->update($insertData);
            return ["status" => true,"message" =>"Weight updated successfully"];
        }
    }

    public function delete(){
        Role::where(["role_id"=>request()->id])->delete();
        return ["status" => true,"message" =>"Weight deleted successfully"];
    }
}
