<?php
namespace App\Http\Controllers\Courier;

use Session;
use Validator;
use DB;
use PDF;
use URL;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use App\Classes\CommonLibrary;
use App\Classes\CourierSaasSync;
use App\Models\PackageType;
use App\Models\User;
use App\Models\Package;
use App\Models\PackageRequest;
use App\Models\PackageStatus;
use App\Models\Courier;
use App\Models\Invoice;
use App\Models\ManifestInfo;
use App\Models\Manifest;
use App\Models\ManifestPackage;
use App\Models\HSCode;
use Yajra\DataTables\Facades\DataTables;
use commonHelper;

class PackegControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function view(){
        $courier_id = Session::get('courier_id');
        $data['title'] = 'Manage Package Log';
        $data['class'] = 'package';
        $data['table'] = 'Manage Package Log';
        $data['courier_id'] = $courier_id;
        // if($courier_id == 17){
            $data['package_count'] = DB::table('packages')->where('packages.status' , 3)->where('packages.courier_id' , $courier_id)->count();
            $package_weight_lb = DB::table('packages')->where('packages.status' , 3)->where('packages.courier_id' , $courier_id)->sum('weight');
            
            $data['package_weight_lb'] = $package_weight_lb;
            $data['package_weight_kg'] = $package_weight_lb / 2.205;
        // }else{
        //     $data['package_count'] = 0;
        //     $package_weight_lb = 0;
            
        //     $data['package_weight_lb'] = 0;
        //     $data['package_weight_kg'] = 0;
        // }
        
        return view("couriers/packages.list",$data);
    }

    public function listView(Request $request)
    {

        $courier_id = Session::get('courier_id');
        $data = DB::table('packages')
                    ->select('packages.*')
                    //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    //->join('package_status', 'package_status.id', '=', 'packages.status')
                    //->where('is_temprary_logs' , 1)
                    ->where('packages.courier_id' , $courier_id);
        $this->applyCourierPackageFilters($data, $request);
                    //->get();
        return DataTables::of($data)
        ->editColumn('weight', function($data){
            return $this->packageMeasureDisplay($data);
        })
        ->addColumn('package_images', function($data){
            return $this->packageImagePreview($data);
        })
        ->addColumn('action', function($data){
            $actions = '';
            if($data->mailbox == '0'){
                $actions .= " <a href='".url('/')."/courier/package/edit/".$data->tracking_no."'  class='all_button btn  btn-sm btn-info'  id='".$data->tracking_no."'>Assigned User</a>";
            }

            return $actions;
        })
        ->rawColumns(['package_images', 'action'])
        ->make(true);
    }

    private function packageMeasureDisplay($package): string
    {
        if(stripos((string) ($package->freight_type ?? ''), 'sea') !== false){
            $cuft = (float) ($package->volume_cuft ?? $package->chargeable_weight ?? 0);
            $cbm = (float) ($package->volume_cbm ?? ($cuft > 0 ? $cuft / 35.3147 : 0));

            return number_format($cuft, 2).' cu.ft / '.number_format($cbm, 3).' CBM';
        }

        $unit = strtolower((string) ($package->weight_unit ?? 'lb')) === 'kg' ? 'kg' : 'lb';

        return number_format((float) ($package->weight ?? 0), 2).' '.$unit;
    }

    private function packageImageButton($package): string
    {
        $images = $this->decodedPackageImages($package);
        if(! count($images)){
            return '';
        }

        $urls = array_map(fn ($path) => asset($path), $images);
        $json = htmlspecialchars(json_encode($urls), ENT_QUOTES, 'UTF-8');

        return "<button type='button' class='btn btn-sm btn-warning view-package-images' data-images='".$json."'><i class='fa fa-image'></i> Images (".count($urls).")</button>";
    }

    private function packageImagePreview($package): string
    {
        $images = $this->decodedPackageImages($package);
        if(! count($images)){
            return '<span class="image-empty">No images</span>';
        }

        $urls = array_map(fn ($path) => asset($path), $images);
        $json = htmlspecialchars(json_encode($urls), ENT_QUOTES, 'UTF-8');
        $firstImage = htmlspecialchars($urls[0], ENT_QUOTES, 'UTF-8');
        $count = count($urls);

        return "<button type='button' class='package-image-preview view-package-images' data-images='".$json."' title='View package images'>"
            ."<img src='".$firstImage."' alt='Package image preview'>"
            ."<span>".$count."</span>"
            ."</button>";
    }

    private function decodedPackageImages($package): array
    {
        $decoded = json_decode((string) ($package->package_image_paths ?? ''), true);

        return is_array($decoded) ? array_slice(array_values(array_filter($decoded)), 0, 5) : [];
    }
    public function miscellaneousView(){
        $data['title'] = 'Manage Miscellaneous Log';
        $data['class'] = 'package';
        $data['table'] = 'Manage Miscellaneous Log';

        return view("couriers/packages.mallicious",$data);
    }

    public function miscellaneousListView(Request $request)
    {

        $courier_id = Session::get('courier_id');
        $data = DB::table('packages')
                    ->select('packages.*')
                    // ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    // ->join('package_status', 'package_status.id', '=', 'packages.status')
                    //->where('is_temprary_logs' , 1)
                    ->where('packages.courier_id' , 0);
        $this->applyCourierPackageFilters($data, $request);
                    //->get();
        return DataTables::of($data)
        ->editColumn('weight', function($data){
            return $this->packageMeasureDisplay($data);
        })
        ->addColumn('package_images', function($data){
            return $this->packageImagePreview($data);
        })
        ->editColumn('supplier_tracking_no', function($data){
            return $this->maskedTrackingNumber($data->supplier_tracking_no);
        })
        ->addColumn('action', function($data){
            if($data->is_request==1){
                return "<a href='javscript:void(0)'  class='all_button btn  btn-sm btn-success'  id='".$data->tracking_no."'>Request Pending</a>";
            }else{
                return "<a href='".url('/')."/courier/package/request/".$data->tracking_no."'  class='all_button btn  btn-sm btn-info'  id='".$data->tracking_no."'>Request Package</a>";
            }
            
        })
        ->rawColumns(['package_images', 'action'])
        ->make(true);
    }
    
    public function viewtransit(){
        $data['title'] = 'Manage Transit';
        $data['class'] = 'package';
        $data['table'] = 'Manage Transit';

        return view("couriers/packages.listtransit",$data);
    }

    public function listtransitListView(Request $request)
    {

        $courier_id = Session::get('courier_id');
        $data = DB::table('packages')
                    ->select('packages.*')
                    //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    //->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('status' , 2)
                    ->where('packages.courier_id' , $courier_id);
        $this->applyCourierPackageFilters($data, $request);
                    //->get();
        return DataTables::of($data)
            ->editColumn('weight', function($data){
                return $this->packageMeasureDisplay($data);
            })
            ->addColumn('package_images', function($data){
                return $this->packageImagePreview($data);
            })
            ->rawColumns(['package_images'])
            ->make(true);
    }

    private function applyCourierPackageFilters($query, Request $request): void
    {
        $tracking = trim((string) $request->input('filter_tracking'));
        $hawb = trim((string) $request->input('filter_hawb'));
        $carrier = trim((string) $request->input('filter_carrier'));
        $customer = trim((string) $request->input('filter_customer'));
        $description = trim((string) $request->input('filter_description'));
        $status = trim((string) $request->input('filter_status'));
        $freight = trim((string) $request->input('filter_freight'));
        $dateFrom = trim((string) $request->input('filter_date_from'));
        $dateTo = trim((string) $request->input('filter_date_to'));

        if($tracking !== ''){
            $query->where(function ($trackingQuery) use ($tracking) {
                $trackingQuery->where('packages.supplier_tracking_no', 'LIKE', "%{$tracking}%")
                    ->orWhere('packages.tracking_no', 'LIKE', "%{$tracking}%");
            });
        }
        if($hawb !== ''){
            $query->where('packages.manifest_no', 'LIKE', "%{$hawb}%");
        }
        if($carrier !== ''){
            $query->where('packages.courier', 'LIKE', "%{$carrier}%");
        }
        if($customer !== ''){
            $query->where('packages.user_name', 'LIKE', "%{$customer}%");
        }
        if($description !== ''){
            $query->where('packages.item_description', 'LIKE', "%{$description}%");
        }
        if($status !== ''){
            $query->where('packages.status_name', 'LIKE', "%{$status}%");
        }
        if($freight !== ''){
            $query->where('packages.freight_type', 'LIKE', "%{$freight}%");
        }
        if($dateFrom !== ''){
            $query->whereDate('packages.created_at', '>=', $dateFrom);
        }
        if($dateTo !== ''){
            $query->whereDate('packages.created_at', '<=', $dateTo);
        }
    }

    public function exportPackages(Request $request, $scope = 'packages')
    {
        $courierId = (int) Session::get('courier_id');
        $query = DB::table('packages')->select('packages.*');

        if($scope === 'unknown'){
            $query->where('packages.courier_id', 0);
        }elseif($scope === 'transit'){
            $query->where('packages.courier_id', $courierId)->where('packages.status', 2);
        }else{
            $query->where('packages.courier_id', $courierId);
        }

        $this->applyCourierPackageFilters($query, $request);
        $packages = $query->orderBy('packages.created_at', 'DESC')->get();
        $filename = 'courier-'.$scope.'-packages-'.date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($packages) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['HAWB', 'Tracking No', 'Carrier', 'Weight / Volume', 'Description', 'User Name', 'Shipment Status', 'Freight Type', 'Date Created']);
            foreach($packages as $package){
                fputcsv($out, [
                    $package->manifest_no,
                    $package->supplier_tracking_no ?: $package->tracking_no,
                    $package->courier,
                    $this->packageMeasureDisplay($package),
                    strip_tags((string) $package->item_description),
                    $package->user_name,
                    $package->status_name,
                    $package->freight_type,
                    $package->created_at,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
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
        //return 'ffffff';
            $data['title'] = 'Add Pre-Alert';
            $data['class'] = 'account';
            $data['table'] = 'Add Pre-Alert';
            $role_id= Session::get('role_id');
            $courier_id = Session::get('courier_id');
            //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
            if ($request->isMethod('get')) {
                $data['courier_id'] = $courier_id;
                $data['types'] = PackageType::get();
                $data['users'] = User::where('courier_id' , $courier_id)->get();
                return view('couriers/packages/create' , $data);
            }else{
                $validator = Validator::make($request->all(), [
                                    'mailbox' => 'required',
                                    'courier' => 'required',
                                    'weight' => 'required',
                                    'supplier_tracking_no' => 'required|unique:packages|max:255'
                                ]);
                if ($validator->fails()) {
                        return redirect('courier/packege/add')
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
                    $tracking_no = $this->nextWmsPackageTrackingNumber((int) $request->courier_id);
                    $packages = DB::table('packages')->orderBy('packeg_id','DESC')->first();
                    //dd($packages);
                    // if($packages){
                    //     $manifest_no = $packages->manifest_no + 1;
                    // }else{
                    //     $manifest_no = '1000';
                    // }
                    $manifest_no = $tracking_no;
                    $user_name = commonHelper::getUserName($request->mailbox);
                    $packages = new Package();
                    $packages->tracking_no = $tracking_no;
                    $packages->nick_name = $request->nick_name;
                    $packages->user_name = $user_name;
                    $packages->merchant = $request->merchant;
                    $packages->mailbox = $request->mailbox;
                    $packages->courier_id = $request->courier_id;
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
                    CourierSaasSync::package($packages->fresh());
                    $usersRes = User::where('mailbox' , $request->mailbox)->first();
                    $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                    $link = URL::to('/') . '/courier/dashboard';
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
                    return redirect('courier/packeges/track');
                }
            }
	}
	
	public function updatePackage(Request $request , $tracking_no = ''){
        $data['title'] = 'Assigned User';
        $data['class'] = 'package';
        $data['table'] = 'Assigned User';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $courier_id = Session::get('courier_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['types'] = PackageType::get();
            $data['couriers'] = Courier::get();
            
            $couriers = Courier::where('id' , $courier_id)->first();
            $package = Package::where('tracking_no' , $tracking_no)->first();
            if(! $package || ((int) $package->courier_id > 0 && (int) $package->courier_id !== (int) $courier_id)){
                Session::flash('success_msg', 'Package not found or unavailable for this courier.');
                return redirect('courier/package/miscellaneous');
            }
            //dd($package);
            $data['package'] = $package;
            $data['tracking_hint'] = $this->maskedTrackingNumber($package->supplier_tracking_no);
            if($package->mailbox == 0){
                $data['users'] = User::where('courier_id' , $courier_id)->distinct()->get();
                
            }else{
                $data['added'] = 0;
                $data['users'] = [];
            }
            //return $data['added'];
            //dd($data['users']);
            return view('couriers/packages/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                // 'courier' => 'required',
                                // 'weight' => 'required',
                                // 'item_description' => 'required'
                            ]);
            if ($validator->fails()) {
                return redirect('courier/package/request/'.$tracking_no)
                                ->withInput()
                                ->withErrors($validator);
            }else{
                $couriers = Courier::where('id' , $courier_id)->first();
                $package = Package::where('tracking_no' , $tracking_no)->first();
                if(! $package || ((int) $package->courier_id > 0 && (int) $package->courier_id !== (int) $courier_id)){
                    return redirect('courier/package/miscellaneous')
                        ->withErrors(['package' => 'Package not found or unavailable for this courier.'])
                        ->withInput();
                }

                $enteredTrackingNo = $this->normalizedTrackingNumber($request->supplier_tracking_no_confirm ?? '');
                $expectedTrackingNo = $this->normalizedTrackingNumber($package->supplier_tracking_no ?? '');
                if($enteredTrackingNo === '' || $expectedTrackingNo === '' || $enteredTrackingNo !== $expectedTrackingNo){
                    return redirect()->back()
                        ->withErrors(['supplier_tracking_no_confirm' => 'Tracking number does not match this package.'])
                        ->withInput();
                }

                //return $request->mailbox;
                if($request->mailbox != ''){
                    $resMailbox = explode('+' , $request->mailbox);
                    //dd($resMailbox);
                    $mailbox = $resMailbox[0];
                    if(isset($resMailbox[1])){
                        $user_name = $resMailbox[1];
                    }else{
                        $user_name = '';
                    }
                    if(isset($resMailbox[2])){
                        $email = $resMailbox[2];
                    }else{
                        $email = '';
                    }
                    
                }else{
                    $mailbox = '0';
                    $user_name = '';
                    $email = '';
                }
                if($mailbox === '' || $mailbox === '0'){
                    return redirect()->back()
                        ->withErrors(['mailbox' => 'Please select a customer before requesting this package.'])
                        ->withInput();
                }
                //return $user_name;
                $nextTrackingNo = $this->trackingNumberForCourierClaim($package, (int) $courier_id);
                $courierName = $couriers->name ?? $package->courier;

                $claimUpdate = [
                    'tracking_no' => $nextTrackingNo,
                    'manifest_no' => $nextTrackingNo,
                    'is_request' => 0,
                    'user_name' => $user_name,
                    'mailbox' => $mailbox,
                    'courier_id' => $courier_id,
                    'courier' => $courierName,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (Schema::hasColumn('packages', 'routing_status')) {
                    $claimUpdate['routing_status'] = 'VERIFIED';
                }
                if (Schema::hasColumn('packages', 'entry_mode')) {
                    $claimUpdate['entry_mode'] = 'STANDARD';
                }
                if (Schema::hasColumn('packages', 'unknown_reason')) {
                    $claimUpdate['unknown_reason'] = null;
                }

                Package::where('packeg_id', $package->packeg_id)->update($claimUpdate);

                PackageRequest::where('tracking_no', $tracking_no)->delete();
                PackageRequest::create([
                    'courier_id' => $courier_id,
                    'tracking_no' => $nextTrackingNo,
                    'mailbox' => $mailbox,
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $package = Package::where('packeg_id', $package->packeg_id)->first();
                CourierSaasSync::package($package);
                $checkPkg = '';
                $type = 'Package Claimed';
                $dateTime = date('Y-m-d H:i:s');
                $message = 'Package claimed by courier for Tracking Number:<b>'.$package->supplier_tracking_no.'</b> and assigned to <b>'.$user_name.'</b>';
                $admin = 0;
                commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$courier_id);
                Session::flash('success_msg', 'Package verified, assigned, and synced successfully.');
                
        
                
        
                
                return redirect('courier/packeges/track');
               
            }
        }

    }
    
    public function getApiUsers($courier_id = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            if(!$couriers || empty($couriers->base_url)){
                return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
            }

            $headers = [];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }

            $curl = curl_init();
            $url = rtrim($couriers->base_url, '/').'/api/GetUsers';
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => $headers,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
        //return view('admin/users/ajaxCourierUsers' , $data);
    }
	
	public function edit(Request $request , $tracking_no = ''){
        $courier_id = Session::get('courier_id');
        PackageRequest::where('tracking_no' , $tracking_no)
                ->create([
                        'courier_id' => $courier_id,
                        'tracking_no' => $tracking_no
                    ]);
        
        Package::where('tracking_no' , $tracking_no)
                ->update([
                        'is_request' => 1,
                        
                    ]);
        
            Session::flash('success_msg', 'Package request sent to admin successfully.');
            return redirect('courier/package/miscellaneous');
        
            

    }

    public function viewMenifest(){
        $data['title'] = 'Create Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Create Manifest';
        $data['couriers'] = Courier::get();
        return view("couriers.packages.createMani",$data);
    }

    public function listViewMenifest()
    {
        $coerier_id = Session::get('courier_id');
        $data = DB::table('packages')
                ->select('packages.*','users.first_name','users.last_name','users.location','package_status.name')
                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                ->join('package_status', 'package_status.id', '=', 'packages.status')
                ->where('packages.is_added_manifest' ,  0)
                ->where('packages.is_temprary_logs' ,  0)
                ->where('packages.courier_id' ,  $coerier_id);
        return DataTables::of($data)
                ->editColumn('check_status', function ($data) {
                    return '<input name="check_status[]" type="checkbox" value="'.$data->packeg_id.'"><input type="hidden" name="invoice_no[]" value="'.$data->packeg_id.'">';
                })
                ->addColumn('package_images', function($data){
                    return $this->packageImagePreview($data);
                })
                ->rawColumns(['check_status', 'package_images'])
                ->make(true);
    }

    public function createMani(Request $request){
        $data['title'] = 'Create Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $coerier_id = Session::get('courier_id');
            $packeg_id = $request->packeg_id;
            //echo $request->status;exit;
            //dd($packeg_id);
            $check_status = $request->check_status;
            //dd($check_status);exit;
            for($i = 0; $i<count($check_status); $i++){
                $pack_id = $check_status[$i];
                $packages = Package::where('packeg_id', $pack_id)->first();
                //dd($packages);
                $usersRes = User::where('mailbox' , $packages->mailbox)->first();
                $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                $link = URL::to('/') . '/courier/dashboard';
                $settings = DB::table('settings')->first();
                $email_subject = 'Brand New UPDATE from '.$settings->company_name;
                $settings = DB::table('settings')->first();
                if($settings){
                    $email_from = $settings->from_email;
                }else{
                    $email_from = 'info@rsbshipping.com';
                }
                $name = PackageStatus::where('id' , $packages->status)->first()->name;
                $status = $name;
                //$this->send_email_packeg($usersRes->email, $user_name,$packages->tracking_no,$packages->mailbox,$packages->courier,$packages->item_description,$packages->weight,$status,$email_subject, $email_from, 'update_packege');
            }

            $package_id_Arr = [];
            for($i = 0; $i<count($check_status); $i++){
                $pack_id = $check_status[$i];
                $package_id_Arr[] = $pack_id;
            }

            $data['packages'] = DB::table('packages')
                                    ->select('packages.*','users.first_name','users.last_name')
                                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                    ->whereIn('packages.packeg_id', $package_id_Arr)
                                    ->orderby('packages.created_at' , 'desc')
                                    ->get();
            $data['manifest_info'] = ManifestInfo::first();

            //dd($package_id_Arr);
            $data['packeg_id'] = implode(',' , $package_id_Arr);
            return view('couriers/packages/minifist' , $data);

    }

    public function exportExcel($id = ''){
        //return 'ddddd';
        $data['title'] = 'Manage Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Manage Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['manifest'] = Manifest::get();
        $data['manifest'] = DB::table('manifest_packeges')
                                ->select('packages.*')
                                ->join('packages', 'packages.packeg_id', '=', 'manifest_packeges.packeges_id')
                                ->where('manifest_packeges.manifest_id' , $id)
                                ->get();
        return view('couriers/packages/export_manifest' , $data);
    }

    public function createManifest(Request $request){

        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $coerier_id = Session::get('courier_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $validator = Validator::make($request->all(), [
                        'cargo_company' => 'required',
                        'custom_office_code' => 'required',
                        //'voyage_number' => 'required',
                        'date_of_departure' => 'required',
                        'shipping_agent_code' => 'required',
                        'exporter_name' => 'required',
                        'loading_port' => 'required',
                        'exporter_address' => 'required',
                        'master_air_billing_no' => 'required',
                        //'carrier_code' => 'required'
                    ]);
        if ($validator->fails()) {
                return redirect('carier/manifest/create')
                        ->withInput()
                        ->withErrors($validator);
        }else{
            $manifestInfo = ManifestInfo::first();
            if($manifestInfo){
                ManifestInfo::where('id' , 1)
                        ->update([
                            'cargo_company' => $request->cargo_company,
                            'custom_office_code' => $request->custom_office_code,
                            'voyage_number' => $request->voyage_number,
                            'date_of_departure' => $request->date_of_departure,
                            'shipping_agent_code' => $request->shipping_agent_code,
                            'exporter_name' => $request->exporter_name,
                            'loading_port' => $request->loading_port,
                            'exporter_address' => $request->exporter_address,
                            'master_air_billing_no' => $request->master_air_billing_no,
                            'carrier_code' => $request->carrier_code
                        ]);
                $data['outlet_name'] = 'Test Pdf';
                view('pdfinvoice' , $data);
                $fileName = time().'_manifest.pdf';
                $pathPDF = base_path() . '/public/pdf/'.$fileName;
                $baseURL = URL::to('/');
                //$expUrl = explode('/index.php' , $baseURL);
                $url = $baseURL . '/public/pdf/'.$fileName;
                $pdf = PDF::loadView('pdfinvoice' , $data)->save($pathPDF);
                $manifest = new Manifest();
                $manifest->courier_id = $coerier_id;
                $manifest->file_pdf = $fileName;
                $manifest->master_air_billing_no = $request->master_air_billing_no;
                $manifest->created_at = date('Y-m-d h:i:s');
                $manifest->updated_at = date('Y-m-d h:i:s');
                $manifest->save();
                $manifest_id = $manifest->id;
                $packege_ids = explode(',' ,$request->packege_ids);
                for($i = 0; $i<count($packege_ids); $i++){
                    $manifArray = [];
                    $manifArray['manifest_id'] = $manifest_id;
                    $manifArray['packeges_id'] = $packege_ids[$i];
                    Package::where('packeg_id', $packege_ids[$i])->update(['is_added_manifest' => 1]);
                    if($packege_ids[$i]){
                        DB::table('manifest_packeges')->insert($manifArray);
                    }
                }
                CourierSaasSync::manifest($manifest->fresh());
                return redirect('courier/package/manifest');
            }else{
                $manInfo = new ManifestInfo();
                $manInfo->cargo_company = $request->cargo_company;
                $manInfo->custom_office_code = $request->custom_office_code;
                $manInfo->date_of_departure = $request->date_of_departure;
                $manInfo->voyage_number = $request->voyage_number;
                $manInfo->shipping_agent_code = $request->shipping_agent_code;
                $manInfo->exporter_name = $request->exporter_name;
                $manInfo->loading_port = $request->loading_port;
                $manInfo->exporter_address = $request->exporter_address;
                $manInfo->master_air_billing_no = $request->master_air_billing_no;
                $manInfo->carrier_code = $request->carrier_code;
                $manInfo->created_at = date('Y-m-d h:i:s');
                $manInfo->updated_at = date('Y-m-d h:i:s');
                $manInfo->save();
                $data['outlet_name'] = 'Test Pdf';
                view('pdfinvoice' , $data);
                $fileName = time().'_manifest.pdf';
                $pathPDF = base_path() . '/public/pdf/'.$fileName;
                $baseURL = URL::to('/');
                //$expUrl = explode('/index.php' , $baseURL);
                $url = $baseURL . '/public/pdf/'.$fileName;
                $pdf = PDF::loadView('pdfinvoice' , $data)->save($pathPDF);
                $manifest = new Manifest();
                $manifest->courier_id = $coerier_id;
                $manifest->file_pdf = $fileName;
                $manifest->master_air_billing_no = $request->master_air_billing_no;
                $manifest->created_at = date('Y-m-d h:i:s');
                $manifest->updated_at = date('Y-m-d h:i:s');
                $manifest->save();
                $manifest_id = $manifest->id;
                $packege_ids = explode(',' ,$request->packege_ids);
                for($i = 0; $i<count($packege_ids); $i++){
                    $manifArray = [];
                    $manifArray['manifest_id'] = $manifest_id;
                    $manifArray['packeges_id'] = $packege_ids[$i];
                    Package::where('packeg_id', $packege_ids[$i])->update(['is_added_manifest' => 1]);
                    if($packege_ids[$i]){
                        DB::table('manifest_packeges')->insert($manifArray);
                    }
                }
                CourierSaasSync::manifest($manifest->fresh());

                Session::flash('success_msg', 'Package has been added successfully');
                return redirect('courier/package/manifest');
            }

        }
    }

    public function editManifest(Request $request , $id = ''){
        $data['title'] = 'Update Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Update Package Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            $data['manifest_info'] = ManifestInfo::first();
            $data['packages'] = DB::table('manifest_packeges')
                                ->select('packages.*','users.first_name','users.last_name')
                                ->join('packages', 'packages.packeg_id', '=', 'manifest_packeges.packeges_id')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->where('manifest_packeges.manifest_id' , $id)
                                ->get();
            $data['manifest_id'] = $id;
            return view('couriers/packages/minifist_edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                'cargo_company' => 'required',
                                //'custom_office_code' => 'required',
                                //'voyage_number' => 'required',
                                'date_of_departure' => 'required',
                                'shipping_agent_code' => 'required',
                                'exporter_name' => 'required',
                                'loading_port' => 'required',
                                'exporter_address' => 'required',
                                'master_air_billing_no' => 'required',
                                //'carrier_code' => 'required'
                            ]);
            if ($validator->fails()) {
                return redirect('courier/manifest/edit/'.$id)
                                ->withInput()
                                ->withErrors($validator);
            }else{
                //echo 'dddddd';exit;
                ManifestInfo::where('id' , 1)
                        ->update([
                            'cargo_company' => $request->cargo_company,
                            'custom_office_code' => $request->custom_office_code,
                            'voyage_number' => $request->voyage_number,
                            'date_of_departure' => $request->date_of_departure,
                            'shipping_agent_code' => $request->shipping_agent_code,
                            'exporter_name' => $request->exporter_name,
                            'loading_port' => $request->loading_port,
                            'exporter_address' => $request->exporter_address,
                            'master_air_billing_no' => $request->master_air_billing_no,
                            'carrier_code' => $request->carrier_code
                        ]);
//                $data['outlet_name'] = 'Test Pdf';
//                view('pdfinvoice' , $data);
//                $fileName = time().'_manifest.pdf';
//                $pathPDF = base_path() . '/public/pdf/'.$fileName;
//                $baseURL = URL::to('/');
//                //$expUrl = explode('/index.php' , $baseURL);
//                $url = $baseURL . '/public/pdf/'.$fileName;
//                $pdf = PDF::loadView('pdfinvoice' , $data)->save($pathPDF);
//                $manifest = new Manifest();
//                $manifest->file_pdf = $fileName;
//                $manifest->created_at = date('Y-m-d h:i:s');
//                $manifest->updated_at = date('Y-m-d h:i:s');
//                $manifest->save();
                $manifest_id = $id;
                $packege_ids = $request->packege_id;
                if($packege_ids){
                    $delete = DB::table('manifest_packeges')->where('manifest_id' , $id)->delete();
                    for($i = 0; $i<count($packege_ids); $i++){
                        $manifArray = [];
                        $manifArray['manifest_id'] = $manifest_id;
                        $manifArray['packeges_id'] = $packege_ids[$i];
                        if($packege_ids[$i]){
                            DB::table('manifest_packeges')->insert($manifArray);
                        }
                    }
                }

                Session::flash('success_msg', 'Manifest has been updated successfully.');
                return redirect('courier/package/manifest');
            }
        }

    }

    public function manifest(){
        $data['title'] = 'Manage Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Manage Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $coerier_id = Session::get('courier_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['manifest'] = Manifest::select('manifests.*','couriers.name')->join('couriers', 'couriers.id', '=', 'manifests.courier_id')->orderby('manifests.created_at' , 'DESC')->where('manifests.courier_id' , $coerier_id)->get();
        return view('couriers/packages/list_manifest' , $data);
    }

    public function exportManifest( Request $request , $id = '' ){

        $data[ 'title' ] = 'Export Package Manifest';
        $data[ 'class' ] = 'package';
        $data[ 'table' ] = 'Export Package Manifest';
        $role_id = Session::get( 'role_id' );
        $user_id = Session::get( 'user_id' );
        $data[ 'getModues' ] = DB::table( 'role_modules' )->where( 'user_id', '=', $user_id )->where( 'role_id', '=', $role_id )->first();

        $data[ 'manifest_info' ] = ManifestInfo::first();
        $data[ 'packages' ] = ManifestPackage::where( 'manifest_id', $id )
                                ->with( 'info', 'package', 'package.client' )
                                ->get();
        //dd( $data[ 'packages' ] ); exit();
        $content = response()->view( 'couriers/packages/minifist_export',$data );
        return response($content)
            ->header('Content-type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="manifest.txt"');
        //return response()->view( 'couriers/packages/minifist_export', $data )->header( 'Content-Type', 'text/xml' );
    }

    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    private function nextWmsPackageTrackingNumber(int $courierId): string
    {
        $tracking = CourierSaasSync::nextPackageTrackingNumber($courierId);

        return $tracking ?: $this->generate_password(13);
    }

    private function trackingNumberForCourierClaim(?Package $package, int $courierId): string
    {
        $current = strtoupper(trim((string) optional($package)->tracking_no));

        if($courierId > 0 && str_starts_with($current, 'UNK100')){
            return $this->nextWmsPackageTrackingNumber($courierId);
        }

        return $current ?: $this->nextWmsPackageTrackingNumber($courierId);
    }

    private function maskedTrackingNumber($trackingNo): string
    {
        $trackingNo = trim((string) $trackingNo);
        if($trackingNo === ''){
            return '';
        }

        $lastFour = substr($trackingNo, -4);
        $hiddenLength = max(strlen($trackingNo) - 4, 0);

        return str_repeat('*', $hiddenLength).$lastFour;
    }

    private function normalizedTrackingNumber($trackingNo): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $trackingNo)));
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
