<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Status;
use App\Models\UserLoginData;
use App\Models\User;
use App\Models\Invoice;
use App\Models\PackageRequest;
use App\Models\Setting;
use App\Models\Area;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Image;
use File;
use DB;
use URL;
use QrCode;
use Milon\Barcode\DNS1D;
use DNS2D;

class PickupRequestControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
    public function view($type = ''){
       $data['title'] = 'Manage Delivery Schedule';
        $data['class'] = 'areas';
        $data['table'] = 'Manage Delivery Schedule';
        $data['type'] = $type;
        $data['areas'] = Area::get(); 
        return view("admin.areas.listDelivery",$data);
    }



    public function listView($type){
        if($type == 'all'){
            return DataTables::of(PackageRequest::where('status' , 0))
                    
                    ->addColumn('day', function ($query) {
                        $days = DB::table('days')->where('id' , $query->day)->first();
                        return $days->day;
                    })
                    ->addColumn('package_total', function ($query) {
                        //return $query->package_total + $query->cost;
                        return $query->package_total;
                    })
                    ->addColumn('action', function($query){
                        return "<a href=".url('/')."/admin/request/0/".$query->id." class='all_button btn  btn-sm btn-info edit_button' id='".$query->id."'>Delivered</a><a href=".url('/')."/admin/request/1/".$query->id." class='all_button btn btn-sm btn-danger'  id='".$query->id."'>Delivery and paid</a><a href='javascript:void(0)' class='all_button package_modal btn btn-sm btn-warning' data='".$query->packages."'  id='".$query->id."'>Packages</a>";
                    })->make(true);
        }else{
            return DataTables::of(PackageRequest::where('status' , 0)->where('area_id' , $type))
                    
                    ->addColumn('day', function ($query) {
                        $days = DB::table('days')->where('id' , $query->day)->first();
                        return $days->day;
                    })
                    ->addColumn('package_total', function ($query) {
                        //return $query->package_total + $query->cost;
                        return $query->package_total;
                    })
                    ->addColumn('action', function($query){
                        return "<a href=".url('/')."/admin/request/0/".$query->id." class='all_button btn  btn-sm btn-info edit_button' id='".$query->id."'>Delivered</a><a href=".url('/')."/admin/request/1/".$query->id." class='all_button btn btn-sm btn-danger'  id='".$query->id."'>Delivery and paid</a><a href='javascript:void(0)' class='all_button package_modal btn btn-sm btn-warning'  id='".$query->id."'>Packages</a>";
                    })->make(true);
        }
    }
    
    public function indexDelivery(Request $request){
        $package = PackageRequest::where('id' , $request->id)->first();
        $packages = explode(',',$package->packages);
        //dd($packages);
        $data['packages'] = DB::table('invoice_packages')
                                ->select('packages.*','package_status.name','users.first_name','users.location','users.address_1')
                                ->join('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                                ->join('package_status', 'package_status.id', '=', 'packages.status','left')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->whereIn('invoice_packages.tracking_no' , $packages)
                                ->get();
        return view("admin.areas.ajaxlistDeliveryPackages",$data);
    }
    
    public function updateStatus($status = '' , $id = ''){ 
        $data['title'] = 'Update Status';
        $data['class'] = 'areas';
        $data['table'] = 'Update Status';
        $requests = PackageRequest::where('id' , $id)->first();
        if($status == 1){
            $packages = explode(',',$requests->packages);
            //$invoice_packages = DB::table('invoice_packages')->whereIn('tracking_no' , $packages)->get();
            //dd($invoice_packages);
            if(count($packages) > 0){
                foreach($packages as $key => $row){
                    Package::where('tracking_no', $row)
                      ->update([
                        'status' => 12
                    ]);
                    $invoice_packages = DB::table('invoice_packages')->where('tracking_no' , $row)->update(['status' => 1]);
                    // if($invoice_packages){
                        
                    //     Invoice::where('invoice_id' , $invoice_packages->invoice_id)->update(['status' => 1,'updated_at'=>date('Y-m-d H:i:s')]);
                    // }
                }
            }
            
            $invoice_packages = DB::table('invoice_packages')->whereIn('tracking_no' , $packages)->get();
            //dd($invoice_packages);
            if(count($invoice_packages) > 0){
                foreach($invoice_packages as $row1){
                    $invoice_packages_count = DB::table('invoice_packages')->where('invoice_id' , $row1->invoice_id)->where('status' , 0)->get();
                    if(count($invoice_packages_count) == 0){
                        Invoice::where('invoice_id' , $row1->invoice_id)->update(['status' => 1,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }
            }
            
            $requests = PackageRequest::where('id' , $id)->update(['status' => 1,'is_pay' =>1]);
            Session::flash('success_msg', 'Package has been delivered and paid successfully.'); 
        }else{
            $packages = explode(',',$requests->packages);
            if(count($packages) > 0){
                foreach($packages as $key => $row){
                    Package::where('tracking_no', $row)
                      ->update([
                        'status' => 12
                    ]);
                }
            }
            $requests=PackageRequest::where('id',$id)->update(['status' => 1]);
            Session::flash('success_msg', 'Package has been delivered successfully.');
        }
        
        return redirect('admin/delivery/schedule/all');	
    }
    
    public function viewHistory(){
        
       $data['title'] = 'Manage Delivery Packages History';
        $data['class'] = 'areas';
        $data['table'] = 'Manage Delivery Packages History'; 
        return view("admin.areas.listDeliveryHistory",$data);
    }



    public function listHistoryView(){
        //return 'rrrrrr';
            return DataTables::of(PackageRequest::where('status' , 1)->where('is_pay' , 1))
                    ->addColumn('user_name', function ($query) {
                        $user = User::where('mailbox' , $query->mailbox)->first();
                        return $user->first_name.' '.$user->first_name;
                    })
                    ->addColumn('package_total', function ($query) {
                        //return $query->package_total + $query->cost;
                        return $query->package_total;
                    })
                    ->addColumn('action', function($query){
                        return "<a href='javascript:void(0)' class='all_button package_modal btn btn-sm btn-warning' data='".$query->packages."'  id='".$query->id."'>Packages</a>";
                    })->make(true);
    }
}