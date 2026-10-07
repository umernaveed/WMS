<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Area;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;

class AreaControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
       $data['title'] = 'Manage Areas';
        $data['class'] = 'areas';
        $data['table'] = 'Manage Areas';
        return view("admin.areas.list",$data);
    }



    public function listView(){
        return DataTables::of(Area::query())
                ->editColumn('delete', function ($data) {
                    if($data->status == 0){
                        $btn = "<a href='".url('/')."/admin/area/1/".$data->id."'  class='btn btn-warning' style='padding: 5px 10px;' id='".$data->id."' title='Block'><i class='ti-na pdd-right-0'></i><span>Block</span></a>";
                    }else{
                        $btn = "<a href='".url('/')."/admin/area/0/".$data->id."'  class='btn btn-success' style='padding: 5px 10px;' id='".$data->id."' title='Access'><i class='ei-circle pdd-right-0'></i><span>Access</span></a>";
                    }
                    return $btn;
                })
                ->rawColumns(['delete' => 'delete','action' => 'action'])
                ->addColumn('action', function($data){
                    //<a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->id."'>Delete</a>
                    return "<a href='javascript:void(0)'  class='all_button btn  btn-sm btn-info edit_button' data-value='".$data."' id='".$data->id."'>Edit</a>";
                })->make(true);
    }
	
    public function save(){
        $insertData["name"] = request()->name;
        $insertData["cost"] = request()->cost;
        if(request()->id == 0){
            Area::insert($insertData);
            return ["status" => true,"message" =>"Area added successfully"];
        }else{
            Area::where(["id"=>request()->id])->update($insertData);
            return ["status" => true,"message" =>"Area updated successfully"];
        }
    }

    public function delete(){
        Area::where(["id"=>request()->id])->delete();
        return ["status" => true,"message" =>"Record deleted successfully"];
    }
    
    public function updateStatus($status = '' , $id = ''){
        Area::where(["id"=>$id])->update(['status' => $status]);
        if($status == 0){
            Session::flash('success_msg', 'Area has been blocked successfully.'); 
        }else{
            Session::flash('success_msg', 'Area has been Unblocked successfully.'); 
        }
        return redirect('admin/areas');
    }
    
    public function scheduleDays(Request $request) { 
        $data['title'] = 'Schedule Days';
        $data['class'] = 'areas';
        $data['table'] = 'Schedule Days';
        if ($request->isMethod('get')) {
            $data['days'] = DB::table('days')->get();
            $data['days_selected'] = DB::table('days_selected')->get();
            return view('admin/areas/edit' , $data);
        }else{
            $dayArray = [];
            $day_id = $request->day_id;
            if(count($day_id) > 0){
                DB::table('days_selected')->delete();
                foreach($day_id as $key => $val){
                    $dayArray['day_id'] = $val;
                    DB::table('days_selected')->insert($dayArray);
                }
            }
            Session::flash('success_msg', 'Selected days been updated successfully.'); 
            return redirect('admin/schedule/days');
        }
    }

	
}
