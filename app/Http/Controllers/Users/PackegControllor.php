<?php
namespace App\Http\Controllers\Users;

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

        return view("users/packages.list",$data);
    }

    public function listView()
    {

        $mailbox = Session::get('mailbox');
        $data = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name','outlets.outlet_trak_id')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                    //->where('is_temprary_logs' , 1)
                    ->where('packages.mailbox' , $mailbox);
                    //->get();
        return DataTables::of($data)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
    }

    public function viewStatus($type = ''){
        if($type == 'update'){
            $data['title'] = 'Manage Package Log';
            $data['class'] = 'delivery';
            $data['table'] = 'Manage Package Log';
        }else{
            $data['title'] = 'Manage Last Package Log';
            $data['class'] = 'delivery';
            $data['table'] = 'Manage Last Package Log';
        }
        $data['type'] = $type;
        $data['packeg_status'] = PackageStatus::get();
        return view("users.packages.listStatus",$data);
    }

    public function listViewStatus($type = '')
    {
        $is_temprary_logs = 0;
        $mailbox = Session::get('mailbox');
        $data = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name','outlets.outlet_name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                    ->where('is_temprary_logs' , $is_temprary_logs)
                    ->where('packages.mailbox' , $mailbox)
                    ->where('packages.status' , 1);

        return DataTables::of($data)
                 ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
                 ->editColumn('check_status', function ($data) {
                    return '<input name="check_status[]" type="checkbox" value="'.$data->packeg_id.'"><input type="hidden" name="invoice_no[]" value="'.$data->packeg_id.'">';
                })
                ->rawColumns(['check_status' => 'check_status','action' => 'action'])
                ->make(true);;
    }

    public function deliveryPackages(){
        $data['title'] = 'Manage Package Log';
        $data['class'] = 'delivery';
        $data['table'] = 'Manage Package Log';
        $mailbox = Session::get('mailbox');
        $data['packages'] = DB::table('packages')
                                ->select('packages.*','users.first_name','users.location','package_status.name','outlets.outlet_name')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->join('package_status', 'package_status.id', '=', 'packages.status')
                                ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where('packages.status' , 1)
                                ->where('packages.mailbox' , $mailbox)
                                ->get();
        return view('users/packages/listStatus' , $data);
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

    public function add(Request $request){
            $data['title'] = 'Add Pre-Alert';
            $data['class'] = 'account';
            $data['table'] = 'Add Pre-Alert';
            $role_id= Session::get('role_id');
            $user_id = Session::get('site_user_id');
            $mailbox = User::where('user_id' , $user_id)->first()->mailbox;
            $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
            if ($request->isMethod('get')) {
                $data['mailbox'] = $mailbox;
                $data['types'] = PackageType::get();
                $data['users'] = User::where('role_id' , 0)->get();
                return view('users/packages/create' , $data);
            }else{
                $validator = Validator::make($request->all(), [
                                    'mailbox' => 'required',
                                    'courier' => 'required',
                                    'weight' => 'required',
                                    'supplier_tracking_no' => 'required|unique:packages|max:255'
                                ]);
                if ($validator->fails()) {
                        return redirect('user/packege/add')
                                ->withInput()
                                ->withErrors($validator);
                }else{
                    $name = '';
                    if ($request->hasFile('input_img')) {
                        $image = $request->file('input_img');
                        $name = time().'.'.$image->getClientOriginalExtension();
                        $destinationPath = public_path('/invoices');
                        $image->move($destinationPath, $name);
                    }
                    //$package_type = explode('-' , $request->package_type);
                    $tracking_no = $this->generate_password(13);
                    $packages = DB::table('packages')->orderBy('packeg_id','DESC')->first();
                    //dd($packages);
                    // if($packages){
                    //     $manifest_no = $packages->manifest_no + 1;
                    // }else{
                    //     $manifest_no = '1000';
                    // }
                    $manifest_no = '100'.substr(str_shuffle('123456789123456789123456789321654987'),0,4);
                    $user_name = commonHelper::getUserName($request->mailbox);
                    $packages = new Package();
                    $packages->tracking_no = $tracking_no;
                    $packages->nick_name = $request->nick_name;
                    $packages->user_name = $user_name;
                    $packages->merchant = $request->merchant;
                    $packages->mailbox = $request->mailbox;
                    $packages->courier = $request->courier;
                    $packages->supplier_tracking_no = $request->supplier_tracking_no;
                    //$packages->package_type = $package_type[1];
                    //$packages->package_code = $package_type[0];
                    $packages->weight = $request->weight;
                    $packages->price = $request->price;
                    $packages->manifest_no = $manifest_no;
                    $packages->invoice = $name;
                    $packages->is_temprary_logs = 1;
                    $packages->status = 9;
                    $packages->status_name = 'PreAlert';
                    $packages->item_description = $request->item_description;
                    $packages->created_at = date('Y-m-d h:i:s');
                    $packages->updated_at = date('Y-m-d h:i:s');
                    $packages->save();
                    $usersRes = User::where('mailbox' , $request->mailbox)->first();
                    $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                    $link = URL::to('/') . '/admin/dashboard';
                    $settings = DB::table('settings')->first();
                    $email_subject = 'Brand New UPDATE from '.$settings->company_name;
                    if($settings){
                        $email_from = $settings->from_email;
                    }
                    $status = 'Arrived in Miami';
                    $logo = URL::to('/') . '/settings/'.$settings->site_logo;
                    $company_name = $settings->company_name;
                    //$this->send_email_packeg($usersRes->email, $user_name,$tracking_no,$request->supplier_tracking_no,$request->mailbox,$request->courier,$request->item_description,$request->weight,$status,$email_subject, $email_from, $logo,$company_name,'create_packege');
                    Session::flash('success_msg', 'Package has been added successfully');
                    return redirect('user/packeges/track');
                }
            }
	}

    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    public function send_email_packeg($email, $user_name,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$item_description,$weight, $status,$email_subject, $email_from,$logo,$company_name,$view_name) {
        $res['userName'] = $user_name;
        $res['tracking_no'] = $tracking_no;
        $res['mailbox'] = $mailbox;
        $res['courier'] = $courier;
        $res['item_description'] = $item_description;
        $res['weight'] = $weight;
        $res['supplier_tracking_no'] = $supplier_tracking_no;
        $res['status'] = $status;
        $res['logo_url'] = $logo;
        $res['company_name'] = $company_name;
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject, $company_name) {
            $message->from($email_from, $company_name);
            $message->to($email, $user_name)->subject($email_subject);
            $message->to('rizwan@decodershub.com', $user_name)->subject($email_subject);
            //$message->to('shopvmjbb@gmail.com', $user_name)->subject($email_subject);
        });
    }
}
