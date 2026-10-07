<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Status;
use App\Models\UserLoginData;
use App\Models\User;
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
	
    public function index(Request $request){
       $uoe_no = Session::get('uoe_no');
        $data['title'] = 'Manage Package Pick Up Request';
        $data['class'] = 'delivery';
        $data['table'] = 'Manage Package Pick Up Request';
        $mailbox = Session::get('mailbox');
        $data['mailbox'] = $mailbox;
        
        if ($request->isMethod('get')) {
            
            $data['packages'] = DB::table('invoice_packages')
                                ->select('packages.*','users.first_name','users.location','users.address_1','package_status.name','outlets.outlet_name')
                                ->join('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->join('package_status', 'package_status.id', '=', 'packages.status')
                                ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where('packages.status' , 1)
                                ->where('packages.is_request' , 0)
                                ->where('packages.mailbox' , $mailbox)
                                ->get();
                                //dd($data['packages']);
        return view('users/packages/listStatus' , $data);
        }else{
            if($request->schdule_method == 'get'){
                $data['package_total'] = $request->package_total;
                $data['no_of_packages'] = $request->no_of_packages;
                $data['package_ids'] = $request->package_ids;
                $data['user'] = User::where('mailbox' , $mailbox)->first();
                $data['areas'] = Area::get();
                $data['days'] = DB::table('days_selected')->join('days' , 'days.id','=','days_selected.day_id')->get();
                $data['days_selected'] = DB::table('days_selected')->get();
                return view('users/packages/create_request' , $data);
            }else{
                $days = explode('-' , $request->day_id);
                $area_id = explode('-' , $request->area_id);
                $packageRequest = new PackageRequest();
                $packageRequest->mailbox = $mailbox;
                $packageRequest->name = $request->name;
                $packageRequest->phone = $request->phone;
                $packageRequest->delivery_date = $request->req_date;
                $packageRequest->delivery_time = $request->req_time;
                $packageRequest->no_of_packages = $request->no_of_packages;
                $packageRequest->package_total = $request->package_total;
                $packageRequest->packages = $request->package_ids;
                $packageRequest->notes = $request->notes;
                $packageRequest->address = $request->address;
                $packageRequest->day = $days[0];
                $packageRequest->area_id = $area_id[0];
                $packageRequest->cost = $request->cost;
                $packageRequest->save();
                
                $package_ids = explode(',' , $request->package_ids);
                if(count($package_ids) > 0){
                    foreach($package_ids as $key => $val){
                        Package::where('tracking_no',$val)->update(['is_request' => 1]);
                    }
                }
                Session::flash('success_msg', 'Pick Up Request scheduled successfully.');
                return redirect('user/delivery/packages');
            }
        }
    }
    public function viewHistory(){
        $mailbox = Session::get('mailbox');
        $package =  PackageRequest::where('status' , 1)->where('is_pay' , 1)->where('mailbox' , $mailbox)->get();
        //dd($package);
       $data['title'] = 'Manage Delivery Packages History';
        $data['class'] = 'delivery';
        $data['table'] = 'Manage Delivery Packages History'; 
        return view("users.packages.listDeliveryHistory",$data);
    }



    public function listHistoryView(){
        $mailbox = Session::get('mailbox');
            return DataTables::of(PackageRequest::where('mailbox' , $mailbox))
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