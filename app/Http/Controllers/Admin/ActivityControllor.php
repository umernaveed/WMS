<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Status;
use App\Models\UserLoginData;
use App\Classes\CommonLibrary;
use commonHelper;
use App\Models\Activity;
use App\Models\Message;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\PackageRequest;
use App\Models\Setting;
use App\Models\PreAlert;
use App\Models\Review;
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

class ActivityControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(Request $request){
            $data['title'] = 'Manage Activity Log';
            $data['class'] = 'activity';
            $data['table'] = 'Manage Activity Log';
            return view('admin/activities/list' , $data);
        
    }

    public function listView(Request $request){
        
        $query = DB::table('activities')
                    ->select('activities.*','users.first_name','users.last_name','couriers.name')
                    ->join('users', 'users.user_id', '=', 'activities.added_by','left')
                    ->join('couriers', 'couriers.id', '=', 'activities.courier_id','left')
                    ->orderby('activities.created_at','DESC');
                    //->get();          
        return DataTables::of($query)
                ->addColumn('user_name', function ($query) {
                    if($query->first_name == ''){
                        return 'user';
                    }else{
                        return $query->first_name . ' ' . $query->last_name;
                    }
                    
                })
                 ->addColumn('name', function ($query) {
                    if($query->name == ''){
                        return 'Unknown';
                    }else{
                        return $query->name;
                    }
                    
                })
                ->make(true);
    }
	
    public function index(){
            $data['title'] = 'Manage Activity Log';
            $data['class'] = 'activity';
            $data['table'] = 'Manage Activity Log';
            $data['activities'] = DB::table('activities')
                                ->select('activities.*','admin.admin_name','admin.last_name')
                                ->join('admin', 'admin.admin_id', '=', 'activities.added_by')
                                ->orderby('activities.created_at','DESC')
                                ->get();
            //dd($data['activities']);
            return view('admin/activities/index' , $data);
    }
    
    public function saveLog($type = '',$title = ''){
        if($type == 'pdf'){
            $message = 'Report  '.$title.' pdf exported';
        }elseif($type == 'print'){
            $message = 'Report  '.$title.' printed';
        }elseif($type == 'excel'){
            $message = 'Report  '.$title.' excel exported';
        }elseif($type == 'csv'){
            $message = 'Report  '.$title.' csv exported';
        }
        //Add Activity
        $type = 'Report';
        $dateTime = date('Y-m-d H:i:s');
        $admin = Session::get('admin_id');
        $activity = commonHelper::addActivity($type,$dateTime,$message,$admin);
        //Save activity
        return 'succes';
    }
    
    public function detail($request_id = ''){
        $data['title'] = 'Pick Up Request';
        $data['class'] = 'pickup-requests';
        $data['table'] = 'Pick Up Request';
        $packageRequest = PackageRequest::where('request_id',$request_id)->first();
        $currency = DB::table('users')->select('currencies.symbol as currency')->join('territories', 'territories.id', '=', 'users.territory_id' , 'left')->join('currencies', 'territories.id', '=', 'currencies.territory_id' , 'left')->where('users.uoe_no' , $packageRequest->uoe_no)->first();
        if($currency){
            $data['currency'] = $currency->currency;
        }else{
            $data['currency'] = '';
        }
        $packages = explode(',',$packageRequest->packages);
        $data['packages'] = DB::table('packages')
                            ->select('packages.*','users.first_name','users.last_name','vendors.name as v_name','package_categories.title','package_invoices.package_invoice_full','package_invoices.invoice_no','package_invoices.total','currencies.symbol as currency')
                            ->join('users', 'users.uoe_no', '=', 'packages.uoe_no')
                            ->join('vendors', 'vendors.id', '=', 'packages.vendor_id')
                            ->join('package_categories', 'package_categories.id', '=', 'packages.category_id')
                            ->join('package_invoices', 'package_invoices.package_id', '=', 'packages.package_id' , 'left')
                            ->join('territories', 'territories.id', '=', 'users.territory_id' , 'left')
                            ->join('currencies', 'territories.id', '=', 'currencies.territory_id' , 'left')
                            ->whereIn('packages.package_id' , $packages)
                            ->groupBy('packages.package_id')
                            ->orderby('packages.created_at','DESC')
                            ->get();
        //dd($data['packages']);
        return view('admin/pickup_requests/detail' , $data);
    }

    public function delete($id = '') { 
        $delete = PackageRequest::where('id',$id)->delete();
        Session::flash('success_msg', 'Pick Up Request has been deleted successfully.'); 
        return redirect('admin/package/pickup/requests'); 
    }
    
    public function feedbackDelete($id = '') { 
        $delete = Review::where('id',$id)->delete();
        Session::flash('success_msg', 'Feedback has been deleted successfully.'); 
        return redirect('admin/feedbacks'); 
    }
}