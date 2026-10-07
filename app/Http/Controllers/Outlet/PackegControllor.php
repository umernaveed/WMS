<?php
namespace App\Http\Controllers\Outlet;

use Session;
use Validator;
use DB;
use PDF;
use URL;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;
use App\Classes\CommonLibrary;
use App\Models\PackageType;
use App\Models\User;
use App\Models\Package;
use App\Models\PackageStatus;
use App\Models\Invoice;
use App\Models\ManifestInfo;
use App\Models\Manifest;
use App\Models\ManifestPackage;
use App\Models\HSCode;
use Yajra\DataTables\Facades\DataTables;

class PackegControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Package Log';
        $data['class'] = 'package';
        $data['table'] = 'Manage Package Log';
        
        return view("outlet/packages.list",$data);
    }

    public function listView()
    {
        $outlet_id= Session::get('outlet_id');
        $data = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name','outlets.outlet_trak_id')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                    ->where('is_temprary_logs' , 0)
                    ->where('users.outlet_id' , $outlet_id);
                    //->get();
        return DataTables::of($data)
                ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
                ->addColumn('action', function($data){
                    $btn = '';
                    if($data->package_status == 0){
                        $btn = "<a href='".url('/')."/outlet/package/status/update/1/".$data->tracking_no."' class='btn btn-default' title='Accept'><i class='fa fa-circle'></i> Accept</a>";
                    }elseif($data->package_status == 1){
                        $btn = "<a href='".url('/')."/outlet/package/status/update/2/".$data->tracking_no."' class='btn btn-info' title='Ready To Deliver'><i class='fa fa-edit'></i> Ready To Deliver</a>";
                    }else{
                        $btn = "<a href='javascript:void(0)' class='btn btn-danger' title='Delivered'><i class=fa fa-edit></i> Delivered</a>";
                    }
                    return $btn;
                })->make(true);
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
	
    public function updateStatus($package_status = '' , $tracking_no = ''){ 
        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';
        Package::where('tracking_no' , $tracking_no)->update(['package_status' => $package_status]);
        if($package_status == 1){
            Session::flash('success_msg', 'Package has been accepted successfully.'); 
        }else{
            Session::flash('success_msg', 'Package has been delivered successfully.');
        }
        return redirect('outlet/packeges');	
    }
        
    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}
