<?php
namespace App\Http\Controllers\Admin;

use Session;
use Validator;
use DB;
use PDF;
use URL;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use App\Models\WeightNew;
use App\Http\Controllers\Controller;
use App\Classes\CommonLibrary;
use App\Classes\CourierSaasSync;
use App\Models\PackageType;
use App\Models\User;
use App\Models\Package;
use App\Models\PackageStatus;
use App\Models\PackageRequest;
use App\Models\Invoice;

use App\Models\Carrier;
use App\Models\ManifestInfo;
use App\Models\Manifest;
use App\Models\Courier;
use App\Models\ManifestPackage;
use App\Models\HSCode;
use Yajra\DataTables\Facades\DataTables;
use QrCode;
use Milon\Barcode\DNS1D;
use DNS2D;
use commonHelper;
use commonHelperApi;
use Str;
use Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PackegControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function importPackages(){
        $packages = DB::table('packages')->where('mailbox','SX-02456')->get();
        //dd($packages);
        $count = 1;
        if(count($packages) > 0){
            foreach($packages as $package){
                $couriers = Courier::where('id' , $package->courier_id)->first();
                $weight = $package->weight;
                $price = $this->priceForWeight($weight, $package->price ?? 0);
                $checkPkg = $this->resendPackageApiNew($couriers->base_url,$couriers->courier_id,$package->tracking_no,$package->supplier_tracking_no,$package->mailbox,$package->courier,$weight,$price,$package->package_type,$package->package_code,$package->item_description,$package->manifest_no,$package->created_at);
                //dd($checkPkg);
                $count++;
                
            }
        }
        return $count.' Package imported';
    }
    
    public function resendPackageApiNew($base_url, $courier_id, $tracking_no, $supplier_tracking_no, $mailbox, $courier, $weight, $price, $package_type, $category_id, $item_description, $manifest_no, $created_at)
{
    try {
        \Log::info('Resending package to New API', [
            'courier_id' => $courier_id,
            'tracking_no' => $tracking_no
        ]);
        
        $curl = curl_init();
        $couriers = Courier::where('id', $courier_id)->first();
        
        if (!$couriers) {
            \Log::error('Courier not found: ' . $courier_id);
            return json_encode(['status' => false, 'message' => 'Courier not found']);
        }
        
        $url = $base_url . '/api/resendPackageNew';
        
        $postData = [
            'tracking_no' => $tracking_no,
            'mailbox' => $mailbox,
            'courier' => $courier,
            'supplier_tracking_no' => $supplier_tracking_no,
            'package_type' => $package_type,
            'package_code' => $category_id,
            'weight' => $weight,
            'price' => $price,
            'manifest_no' => $manifest_no,
            'item_description' => $item_description,
            'created_at' => $created_at
        ];
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        
        curl_close($curl);
        
        if ($curlError) {
            \Log::error('CURL Error: ' . $curlError);
            return json_encode(['status' => false, 'message' => 'CURL Error: ' . $curlError]);
        }
        
        \Log::info('API Response:', ['response' => $response, 'http_code' => $httpCode]);
        
        if (empty($response)) {
            return json_encode(['status' => true, 'message' => 'Package processed']);
        }
        
        return $response;
        
    } catch (\Exception $e) {
        \Log::error('Exception in resendPackageApiNew: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_encode(['status' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    }
}

    public function view($type = '',$courier_id = ''){
        $data['couriers'] = Courier::get();
        $data['package_statuses'] = PackageStatus::orderBy('name')->get();
        $data['package_carriers'] = Package::query()
            ->select('courier')
            ->whereNotNull('courier')
            ->where('courier', '!=', '')
            ->distinct()
            ->orderBy('courier')
            ->limit(300)
            ->pluck('courier');
        $data['package_loggers'] = Package::query()
            ->select('addedby_name')
            ->whereNotNull('addedby_name')
            ->where('addedby_name', '!=', '')
            ->distinct()
            ->orderBy('addedby_name')
            ->limit(300)
            ->pluck('addedby_name');
        $data['coerier_id'] = $courier_id;
            $data['package_count'] = DB::table('packages')->where('packages.status' , 3)->count();
         $package_weight_lb = DB::table('packages')->where('packages.status' , 3)->sum('weight');
            
            $data['package_weight_lb'] = $package_weight_lb;
            $data['package_weight_kg'] = $package_weight_lb / 2.205;
        if($type == 'logs'){
            $data['title'] = 'Manage Package Log';
            $data['class'] = 'package';
            $data['table'] = 'Manage Package Log';
            $data['type'] = $type;
            return view("admin.packages.list",$data);
        }else{
            $unknownMode = $this->unknownQueueMode($type);
            $data['title'] = $unknownMode === 'assigned' ? 'Assigned Claims' : 'Manage Unknown Package Log';
            $data['class'] = 'package';
            $data['table'] = $unknownMode === 'assigned' ? 'Assigned Claims' : 'Manage Unknown Package Log';
            $data['type'] = $type;
            $data['unknown_mode'] = $unknownMode;
            $unknownPackages = $this->applyUnknownPackageFilter(DB::table('packages'), $unknownMode);
            $data['unknown_count'] = (clone $unknownPackages)->count();
            $data['unknown_weight_lb'] = (clone $unknownPackages)->sum('weight');
            $data['unknown_oldest_days'] = 0;
            $oldestUnknown = (clone $unknownPackages)->orderBy('created_at', 'asc')->value('created_at');
            if ($oldestUnknown) {
                $data['unknown_oldest_days'] = max(0, round((time() - strtotime($oldestUnknown)) / (60 * 60 * 24)));
            }
            return view("admin.packages.listUnknown",$data);
        }
        
    }

    public function viewStatus($type = '')
    {
        return redirect('admin/packeges/logs/0?status=' . urlencode($type));
    }

    public function listView(Request $request, $type = '', $courier_id = '')
    {
        if($type == 'logs'){
            return $this->packageManagementListView($request, $courier_id);

            $is_temprary_logs = 0;
            if($courier_id == 0){
                $key = 'packages.courier_id';
                $opt = '!=';
                $val = $courier_id;
                $where = $key .',!=,'. $val;
            }else{
                $key = 'packages.courier_id';
                $opt = '==';
                $val = $courier_id;
                $where = $key .','. $val;
            }
            $columns = array(
                            0 =>'manifest_no',
                            1 =>'supplier_tracking_no',
                            2=> 'courier',
                            3=> 'weight',
                            4=> 'item_description',
                            5=> 'first_name',
                            6=> 'name',
                            7=> 'outlet_name',
                            8=> 'created_at',
                            9=> 'action'
                        );
            if($courier_id == 0){
                $totalData = DB::table('packages')
                                //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                //->join('package_status', 'package_status.id', '=', 'packages.status')
                                //->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where(function ($query) use ($is_temprary_logs) {
                                    $query->where('is_temprary_logs', $is_temprary_logs)->orWhereNull('is_temprary_logs');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 0)
                                ->count();
            }else{
                $totalData = DB::table('packages')
                                //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                //->join('package_status', 'package_status.id', '=', 'packages.status')
                                //->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where(function ($query) use ($is_temprary_logs) {
                                    $query->where('is_temprary_logs', $is_temprary_logs)->orWhereNull('is_temprary_logs');
                                })
                                ->where('packages.courier_id'  , $courier_id)
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 0)
                                ->count();
            }

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if(empty($request->input('search.value')))
            {
                if($courier_id == 0){
                    $posts = DB::table('packages')->select('packages.packeg_id','packages.label','packages.courier_id','packages.mailbox','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                            ->where(function ($query) use ($is_temprary_logs) {
                                $query->where('is_temprary_logs', $is_temprary_logs)->orWhereNull('is_temprary_logs');
                            })
                            ->where('packages.status' , '!=' , 9)
                            //->where('packages.is_request' , 0)
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy('packages.created_at','DESC')
                            ->get();
                }else{
                    $posts = DB::table('packages')->select('packages.packeg_id','packages.label','packages.courier_id','packages.mailbox','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                            ->where(function ($query) use ($is_temprary_logs) {
                                $query->where('is_temprary_logs', $is_temprary_logs)->orWhereNull('is_temprary_logs');
                            })
                            ->where('packages.courier_id'  , $courier_id)
                            ->where('packages.status' , '!=' , 9)
                            //->where('packages.is_request' , 0)
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy('packages.created_at','DESC')
                            ->get();
                }
                
            }else {
                $paramsArr = [];
                $search = $request->input('search.value');
                if($search != ''){
                    $paramsArr['packages.user_name'] = $search;
                    //$paramsArr['users.last_name'] = $search;
                    $paramsArr['packages.manifest_no'] = $search;
                    $paramsArr['packages.tracking_no'] = $search;
                    $paramsArr['packages.supplier_tracking_no'] = $search;
                    $paramsArr['packages.courier'] = $search;
                    $paramsArr['packages.status_name'] = $search;
                    //$paramsArr['outlets.outlet_name'] = $search;
                }
                if($courier_id == 0){
                    $posts =  DB::table('packages')->select('packages.packeg_id','packages.label','packages.courier_id','packages.mailbox','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                            //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                            //->join('package_status', 'package_status.id', '=', 'packages.status')
                            //->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                            ->where(function ($query) use ($is_temprary_logs) {
                                $query->where('packages.is_temprary_logs', $is_temprary_logs)->orWhereNull('packages.is_temprary_logs');
                            })
                            ->where('packages.status' , '!=' , 9)
                            //->where('packages.is_request' , 0)
                            //->where('packages.status' , '!=' , 12)
                            ->where(function($q) use ($paramsArr)
                            {
                                foreach($paramsArr as $key => $value)
                                {
                                    if($key == 'packages.user_name'){
                                        $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                    }elseif($key == 'packages.manifest_no'){
                                        $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                    }elseif($key == 'packages.tracking_no'){
                                        $q->orWhere('packages.tracking_no', 'LIKE',"%{$value}%");
                                    }elseif($key == 'packages.supplier_tracking_no'){
                                        $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                    }elseif($key == 'packages.courier'){
                                        $q->orWhere('packages.courier', 'LIKE',"%{$value}%");
                                    }elseif($key == 'packages.status_name'){
                                        $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                    }
                                }
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy('packages.created_at','DESC')
                            ->get();
                $totalFiltered = DB::table('packages')
                                    ->where(function ($query) use ($is_temprary_logs) {
                                        $query->where('packages.is_temprary_logs', $is_temprary_logs)->orWhereNull('packages.is_temprary_logs');
                                    })
                                    ->where('packages.status' , '!=' , 9)
                                    //->where('packages.is_request' , 0)
                                    ->where(function($q) use ($paramsArr)
                                    {
                                        foreach($paramsArr as $key => $value)
                                        {
                                            if($key == 'packages.user_name'){
                                                $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.manifest_no'){
                                                $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.tracking_no'){
                                                $q->orWhere('packages.tracking_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.supplier_tracking_no'){
                                                $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.courier'){
                                                $q->orWhere('packages.courier', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.status_name'){
                                                $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                            }
                                        }
                                    })
                                    ->count();
                }else{
                    $posts =  DB::table('packages')->select('packages.packeg_id','packages.label','packages.courier_id','packages.mailbox','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                //->join('package_status', 'package_status.id', '=', 'packages.status')
                                //->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where(function ($query) use ($is_temprary_logs) {
                                    $query->where('packages.is_temprary_logs', $is_temprary_logs)->orWhereNull('packages.is_temprary_logs');
                                })
                                ->where('packages.courier_id'  , $courier_id)
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 0)
                                //->where('packages.status' , '!=' , 12)
                                ->where(function($q) use ($paramsArr)
                                {
                                    foreach($paramsArr as $key => $value)
                                    {
                                        if($key == 'packages.user_name'){
                                            $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.manifest_no'){
                                            $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.tracking_no'){
                                            $q->orWhere('packages.tracking_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.supplier_tracking_no'){
                                            $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.courier'){
                                            $q->orWhere('packages.courier', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.status_name'){
                                            $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                        }
                                    }
                                })
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy('packages.created_at','DESC')
                                ->get();
                $totalFiltered = DB::table('packages')
                                    ->where(function ($query) use ($is_temprary_logs) {
                                        $query->where('packages.is_temprary_logs', $is_temprary_logs)->orWhereNull('packages.is_temprary_logs');
                                    })
                                    ->where('packages.courier_id'  , $courier_id)
                                    ->where('packages.status' , '!=' , 9)
                                    //->where('packages.is_request' , 0)
                                    ->where(function($q) use ($paramsArr)
                                    {
                                        foreach($paramsArr as $key => $value)
                                        {
                                            if($key == 'packages.user_name'){
                                                $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.manifest_no'){
                                                $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.tracking_no'){
                                                $q->orWhere('packages.tracking_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.supplier_tracking_no'){
                                                $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.courier'){
                                                $q->orWhere('packages.courier', 'LIKE',"%{$value}%");
                                            }elseif($key == 'packages.status_name'){
                                                $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                            }
                                        }
                                    })
                                    ->count();
                }
                
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $post)
                {
                    $btn = $this->packageLogActionsDropdown($post);
                    $outlet_name = commonHelper::getCourierName($post->courier_id);
                    $nestedData['manifest_no'] = "<span class='pkg-code'>".e($post->manifest_no)."</span>";
                    $nestedData['supplier_tracking_no'] = "<span class='pkg-tracking'>".e($post->supplier_tracking_no)."</span>";
                    $nestedData['courier'] = "<span class='pkg-carrier'>".e($post->courier)."</span>";
                    $nestedData['weight'] = "<span class='pkg-weight'>".e($this->packageMeasureDisplay($post))."</span>";
                    $nestedData['item_description'] = "<span class='pkg-description' title='".e(strip_tags($post->item_description))."'>".e(\Illuminate\Support\Str::limit(strip_tags($post->item_description), 44))."</span>";
                    $nestedData['first_name'] = "<span class='pkg-user'>".e($post->user_name)."</span>";
                    $nestedData['name'] = "<span class='pkg-status'>".e($this->packageStatusDisplayName($post))."</span>";
                    $nestedData['outlet_name'] = "<span class='pkg-courier'>".e($outlet_name)."</span>";
                    $nestedData['addedby_name'] = $post->addedby_name;
                    $nestedData['created_at'] = date('j M Y',strtotime($post->created_at));
                    $nestedData['action'] = $btn;
                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                            "draw"            => intval($request->input('draw')),
                            "recordsTotal"    => intval($totalData),
                            "recordsFiltered" => intval($totalFiltered),
                            "data"            => $data
                        );
            echo json_encode($json_data);
        }else{
            return $this->unknownPackagesListView($request, $type, $courier_id);

            $filterArr = [];
            //$is_temprary_logs = 1;
            if($courier_id == 0){
                $key = 'packages.courier_id';
                $opt = '==';
                $val = $courier_id;
            }
            if($courier_id == 2){
                $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                $end_date = date('Y-m-d');
            }elseif($courier_id == 30){
                $start_date = date('Y-m-d',strtotime("-32 days", time()));
                $end_date = date('Y-m-d',strtotime("-2 days", time()));
            }elseif($courier_id == 60){
                $start_date = date('Y-m-d',strtotime("-60 days", time()));
                $end_date = date('Y-m-d',strtotime("-30 days", time()));
            }elseif($courier_id == 90){
                $start_date = date('Y-m-d',strtotime("-90 days", time()));
                $end_date = date('Y-m-d',strtotime("-60 days", time()));
            }elseif($courier_id == 120){
                $start_date = date('Y-m-d',strtotime("-120 days", time()));
                $end_date = date('Y-m-d',strtotime("-90 days", time()));
            }elseif($courier_id == 121){
                $end_date = date('Y-m-d',strtotime("-121 days", time()));
            }
            $columns = array(
                            0 =>'manifest_no',
                            1 =>'supplier_tracking_no',
                            2=> 'courier',
                            3=> 'weight',
                            4=> 'item_description',
                            5=> 'first_name',
                            6=> 'name',
                            7=> 'outlet_name',
                            8=> 'created_at',
                            9=> 'action'
                        );
            if($courier_id == 0){
                $totalData = DB::table('packages')
                                    ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                    //->where('packages.is_request' , 1)
                                    ->where('packages.status' , '!=' , 9)
                                    ->count();
            }elseif($courier_id == 121){
                // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                // $end_date = date('Y-m-d');
                $totalData = DB::table('packages')
                                    ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                    ->where('packages.status' , '!=' , 9)
                                    //->where('packages.is_request' , 1)
                                    //->whereDate('packages.created_at' , '>=' , $start_date)
                                    ->whereDate('packages.created_at' , '<=' , $end_date)
                                    //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                    ->count();
            }else{
                // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                // $end_date = date('Y-m-d');
                $totalData = DB::table('packages')
                                    ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                    ->where('packages.status' , '!=' , 9)
                                    //->where('packages.is_request' , 1)
                                    ->whereDate('packages.created_at' , '>=' , $start_date)
                                    ->whereDate('packages.created_at' , '<=' , $end_date)
                                    //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                    ->count();
            }

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if(empty($request->input('search.value')))
            {
                if($courier_id == 0){
                    $posts = DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy('packages.created_at','DESC')
                                ->get();
                }elseif($courier_id == 121){       
                    // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                    // $end_date = date('Y-m-d');
                    $posts = DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                //->whereDate('packages.created_at' , '>=' , $start_date)
                                ->whereDate('packages.created_at' , '<=' , $end_date)
                                //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                ->offset($start)
                                ->limit($limit)
                               ->orderBy('packages.created_at','DESC')
                                ->get();
                }else{
                    // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                    // $end_date = date('Y-m-d');
                    $posts = DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                ->whereDate('packages.created_at' , '>=' , $start_date)
                                ->whereDate('packages.created_at' , '<=' , $end_date)
                                //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                ->offset($start)
                                ->limit($limit)
                               ->orderBy('packages.created_at','DESC')
                                ->get();
                }
            }else {
                $paramsArr = [];
                $search = $request->input('search.value');
                if($search != ''){
                    $paramsArr['packages.user_name'] = $search;
                    //$paramsArr['users.last_name'] = $search;
                    $paramsArr['packages.manifest_no'] = $search;
                    $paramsArr['packages.supplier_tracking_no'] = $search;
                    $paramsArr['packages.status_name'] = $search;
                    //$paramsArr['outlets.outlet_name'] = $search;
                }
                if($courier_id == 0){
                    $posts =  DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                //->where('packages.is_temprary_logs' , $is_temprary_logs)
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                ->where(function($q) use ($paramsArr)
                                {
                                    foreach($paramsArr as $key => $value)
                                    {
                                        if($key == 'packages.user_name'){
                                            $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.manifest_no'){
                                            $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.supplier_tracking_no'){
                                            $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.status_name'){
                                            $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                        }
                                    }
                                })
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy('packages.created_at','DESC')
                                ->get();
                    $totalFiltered = DB::table('packages')
                                      //  ->where('packages.is_temprary_logs' , $is_temprary_logs)
                                        ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                        ->where('packages.status' , '!=' , 9)
                                        //->where('packages.is_request' , 1)
                                        ->where(function($q) use ($paramsArr)
                                        {
                                            foreach($paramsArr as $key => $value)
                                            {
                                                if($key == 'packages.user_name'){
                                                    $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.manifest_no'){
                                                    $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.supplier_tracking_no'){
                                                    $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.status_name'){
                                                    $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                                }
                                            }
                                        })
                                        ->count();
                }elseif($courier_id == 121){   
                    // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                    // $end_date = date('Y-m-d');
                    $posts =  DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                // ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                // ->join('package_status', 'package_status.id', '=', 'packages.status')
                                // ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                //->where('packages.is_temprary_logs' , $is_temprary_logs)
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                //->whereDate('packages.created_at' , '>=' , $start_date)
                                ->whereDate('packages.created_at' , '<=' , $end_date)
                                //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                ->where(function($q) use ($paramsArr)
                                {
                                    foreach($paramsArr as $key => $value)
                                    {
                                        if($key == 'packages.user_name'){
                                            $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.manifest_no'){
                                            $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.supplier_tracking_no'){
                                            $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.status_name'){
                                            $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                        }
                                        // elseif($key == 'outlets.outlet_name'){
                                        //     $q->orWhere('outlets.outlet_name', 'LIKE',"%{$value}%");
                                        // }
                                    }
                                })
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy('packages.created_at','DESC')
                                ->get();
                    $totalFiltered = DB::table('packages')
                                        // ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                        // ->join('package_status', 'package_status.id', '=', 'packages.status')
                                        // ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                        //->where('packages.is_temprary_logs' , $is_temprary_logs)
                                        ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                        ->where('packages.status' , '!=' , 9)
                                        //->where('packages.is_request' , 1)
                                        //->whereDate('packages.created_at' , '>=' , $start_date)
                                        ->whereDate('packages.created_at' , '<=' , $end_date)
                                        //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                        ->where(function($q) use ($paramsArr)
                                        {
                                            foreach($paramsArr as $key => $value)
                                            {
                                                if($key == 'packages.user_name'){
                                                    $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.manifest_no'){
                                                    $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.supplier_tracking_no'){
                                                    $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.status_name'){
                                                    $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                                }
                                            }
                                        })
                                        ->count();
                }else{
                    // $start_date = date('Y-m-d',strtotime("-".$courier_id." days", time()));
                    // $end_date = date('Y-m-d');
                    $posts =  DB::table('packages')->select('packages.packeg_id','packages.courier_id','packages.mailbox','packages.is_request','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.weight_unit','packages.freight_type','packages.warehouse_code','packages.label_type','packages.volume_cuft','packages.volume_cbm','packages.chargeable_weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at','packages.addedby_name')
                                // ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                // ->join('package_status', 'package_status.id', '=', 'packages.status')
                                // ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                //->where('packages.is_temprary_logs' , $is_temprary_logs)
                                ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                ->where('packages.status' , '!=' , 9)
                                //->where('packages.is_request' , 1)
                                ->whereDate('packages.created_at' , '>=' , $start_date)
                                ->whereDate('packages.created_at' , '<=' , $end_date)
                                //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                ->where(function($q) use ($paramsArr)
                                {
                                    foreach($paramsArr as $key => $value)
                                    {
                                        if($key == 'packages.user_name'){
                                            $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.manifest_no'){
                                            $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.supplier_tracking_no'){
                                            $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                        }elseif($key == 'packages.status_name'){
                                            $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                        }
                                        // elseif($key == 'outlets.outlet_name'){
                                        //     $q->orWhere('outlets.outlet_name', 'LIKE',"%{$value}%");
                                        // }
                                    }
                                })
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy('packages.created_at','DESC')
                                ->get();
                    $totalFiltered = DB::table('packages')
                                        // ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                        // ->join('package_status', 'package_status.id', '=', 'packages.status')
                                        // ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                        //->where('packages.is_temprary_logs' , $is_temprary_logs)
                                        ->where(function ($unknownQuery) {
                                    $unknownQuery->where('packages.courier_id', 0)
                                        ->orWhereNull('packages.mailbox')
                                        ->orWhere('packages.mailbox', '')
                                        ->orWhere('packages.mailbox', '0');
                                })
                                        ->where('packages.status' , '!=' , 9)
                                        //->where('packages.is_request' , 1)
                                        ->whereDate('packages.created_at' , '>=' , $start_date)
                                        ->whereDate('packages.created_at' , '<=' , $end_date)
                                        //->whereBetween('packages.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                                        ->where(function($q) use ($paramsArr)
                                        {
                                            foreach($paramsArr as $key => $value)
                                            {
                                                if($key == 'packages.user_name'){
                                                    $q->orWhere('packages.user_name', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.manifest_no'){
                                                    $q->orWhere('packages.manifest_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.supplier_tracking_no'){
                                                    $q->orWhere('packages.supplier_tracking_no', 'LIKE',"%{$value}%");
                                                }elseif($key == 'packages.status_name'){
                                                    $q->orWhere('packages.status_name', 'LIKE',"%{$value}%");
                                                }
                                            }
                                        })
                                        ->count();
                }
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $post)
                {
                    $btn = '';
                    $now = time(); // or your date as well
                    $your_date = strtotime($post->created_at);
                    $datediff = $now - $your_date;
                    // $date1 = strtotime(date('Y-m-d'));
                    // $date2 = strtotime(date('Y-m-d' , strtotime($post->created_at)));
                    // $diff = abs($date2 - $date1);
                    // $years = floor($diff / (365*60*60*24));
                    // $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
                    // $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                    //echo round($datediff / (60 * 60 * 24));
                    if($post->invoice != ''){
                        $btn = "<a href='".url('/')."/invoices/".$post->invoice."' class='btn btn-warning' title='Customer invoice' download><i class='fa fa-download'></i> Customer invoice </a>";
                    }
                    $btn .= "<a href='".url('/')."/admin/package/edit/".$post->tracking_no."'  class='all_button btn  btn-sm btn-info'  id='".$post->tracking_no."'>Edit</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$post->packeg_id."'>Delete</a>";
                    if($post->is_request == 1){
                        $outlet_name = commonHelper::getCourierNameUnknown($post->tracking_no);
                    }else{
                        $outlet_name = '';
                    }
                    
                    $nestedData['packeg_id'] = $post->packeg_id;
                    $nestedData['manifest_no'] = $post->manifest_no;
                    $nestedData['supplier_tracking_no'] = $post->supplier_tracking_no;
                    $nestedData['courier'] = $post->courier;
                    $nestedData['weight'] = $this->packageMeasureDisplay($post);
                    $nestedData['item_description'] = substr(strip_tags($post->item_description),0,50)."...";
                    $nestedData['first_name'] = $post->user_name;
                    $nestedData['name'] = $this->packageStatusDisplayName($post);
                    $nestedData['outlet_name'] = $outlet_name;
                    $nestedData['ageing'] = round($datediff / (60 * 60 * 24)).' Days';
                    $nestedData['addedby_name'] = $post->addedby_name;
                    $nestedData['created_at'] = date('j M Y',strtotime($post->created_at));
                    $nestedData['action'] = $btn;
                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                            "draw"            => intval($request->input('draw')),
                            "recordsTotal"    => intval($totalData),
                            "recordsFiltered" => intval($totalFiltered),
                            "data"            => $data
                        );
            echo json_encode($json_data);
        }

    }

    private function packageLogActionsDropdown($post): string
    {
        $packageId = (int) $post->packeg_id;
        $trackingNo = e((string) $post->tracking_no);
        $actions = '';

        if(! empty($post->invoice)){
            $actions .= "<a href='".url('/')."/invoices/".e((string) $post->invoice)."' class='dropdown-item pkg-action-item' title='Customer invoice' target='_blank'><i class='fa fa-file-text-o'></i><span>Invoice</span></a>";
        }

        $actions .= "<a href='".url('/')."/admin/download/label/".$packageId."' class='dropdown-item pkg-action-item pkg-action-item-label' title='Download label' target='_blank'><i class='fa fa-download'></i><span>Label</span></a>";
        $actions .= "<button type='button' class='dropdown-item pkg-action-item pkg-action-item-sync resend_package' title='Re-send package' id='".$packageId."'><i class='ti-reload'></i><span>Re-send</span></button>";
        $actions .= "<a href='".url('/')."/admin/package/edit/".$trackingNo."' class='dropdown-item pkg-action-item pkg-action-item-edit' title='Edit package' id='".$trackingNo."'><i class='fa fa-pencil'></i><span>Edit</span></a>";
        $actions .= "<button type='button' class='dropdown-item pkg-action-item pkg-action-item-delete delete_btn' title='Delete package' id='".$packageId."'><i class='fa fa-trash-o'></i><span>Delete</span></button>";

        return "<div class='dropdown pkg-actions-dropdown'><button type='button' class='btn pkg-actions-toggle dropdown-toggle' data-toggle='dropdown' data-boundary='window' aria-haspopup='true' aria-expanded='false'><i class='fa fa-ellipsis-h'></i><span>Actions</span></button><div class='dropdown-menu dropdown-menu-right pkg-actions-menu'>".$actions."</div></div>";
    }

    private function packageManagementListView(Request $request, $courier_id = '')
    {
        $columns = [
            0 => 'packages.created_at',
            1 => 'packages.manifest_no',
            2 => 'packages.supplier_tracking_no',
            3 => 'packages.courier',
            4 => 'packages.weight',
            5 => 'packages.item_description',
            6 => 'packages.user_name',
            7 => 'packages.status_name',
            8 => 'couriers.name',
            9 => 'packages.addedby_name',
            10 => 'packages.created_at',
            11 => 'packages.packeg_id',
        ];

        $baseQuery = DB::table('packages')
            ->leftJoin('couriers', 'couriers.id', '=', 'packages.courier_id')
            ->where(function ($query) {
                $query->where('packages.is_temprary_logs', 0)->orWhereNull('packages.is_temprary_logs');
            })
            ->where('packages.status', '!=', 9);

        $this->applyPackageManagementFilter($baseQuery, $courier_id);

        $totalData = (clone $baseQuery)->count();
        $this->applyPackageAdvancedFilters($baseQuery, $request, false);
        $search = trim((string) $request->input('search.value'));

        if($search !== ''){
            $baseQuery->where(function ($query) use ($search) {
                $query->orWhere('packages.user_name', 'LIKE', "%{$search}%")
                    ->orWhere('packages.manifest_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.tracking_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.supplier_tracking_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.courier', 'LIKE', "%{$search}%")
                    ->orWhere('packages.status_name', 'LIKE', "%{$search}%")
                    ->orWhere('couriers.name', 'LIKE', "%{$search}%");
            });
        }

        $totalFiltered = (clone $baseQuery)->count();
        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $orderColumn = $columns[(int) $request->input('order.0.column', 0)] ?? 'packages.created_at';
        $direction = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $posts = $baseQuery
            ->select(
                'packages.packeg_id',
                'packages.label',
                'packages.courier_id',
                'packages.mailbox',
                'packages.supplier_tracking_no',
                'packages.tracking_no',
                'packages.invoice',
                'packages.manifest_no',
                'packages.courier',
                'packages.weight',
                'packages.weight_unit',
                'packages.freight_type',
                'packages.warehouse_code',
                'packages.label_type',
                'packages.volume_cuft',
                'packages.volume_cbm',
                'packages.chargeable_weight',
                'packages.item_description',
                'packages.user_name',
                'packages.status_name',
                'packages.created_at',
                'packages.addedby_name',
                'couriers.name as assigned_courier_name'
            )
            ->offset($start)
            ->limit($limit)
            ->orderBy($orderColumn, $direction)
            ->get();

        $data = [];
        foreach($posts as $post){
            $outletName = $post->assigned_courier_name ?: $this->courierNameForId((int) $post->courier_id, '');
            $data[] = [
                'manifest_no' => "<span class='pkg-code'>".e($post->manifest_no)."</span>",
                'supplier_tracking_no' => "<span class='pkg-tracking'>".e($post->supplier_tracking_no)."</span>",
                'courier' => "<span class='pkg-carrier'>".e($post->courier)."</span>",
                'weight' => "<span class='pkg-weight'>".e($this->packageMeasureDisplay($post))."</span>",
                'item_description' => "<span class='pkg-description' title='".e(strip_tags((string) $post->item_description))."'>".e(\Illuminate\Support\Str::limit(strip_tags((string) $post->item_description), 44))."</span>",
                'first_name' => "<span class='pkg-user'>".e($post->user_name)."</span>",
                'name' => "<span class='pkg-status'>".e($this->packageStatusDisplayName($post))."</span>",
                'outlet_name' => "<span class='pkg-courier'>".e($outletName)."</span>",
                'addedby_name' => $post->addedby_name,
                'created_at' => $post->created_at ? date('j M Y', strtotime($post->created_at)) : '',
                'action' => $this->packageLogActionsDropdown($post),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data,
        ]);
    }

    private function applyPackageManagementFilter($query, $courier_id)
    {
        if((int) $courier_id > 0){
            $query->where('packages.courier_id', (int) $courier_id);
        }else{
            $query->where('packages.courier_id', '>', 0);
        }

        return $query->where(function ($normalPackage) {
            $normalPackage->where(function ($mailbox) {
                    $mailbox->whereNotNull('packages.mailbox')
                        ->where('packages.mailbox', '!=', '')
                        ->where('packages.mailbox', '!=', '0');
                })
                ->where(function ($routing) {
                    $routing->whereNull('packages.routing_status')
                        ->orWhere('packages.routing_status', '!=', 'ASSIGNED_UNKNOWN');
                });
        });
    }

    private function unknownPackagesListView(Request $request, $type = '', $courier_id = '')
    {
        $unknownMode = $this->unknownQueueMode($type);
        $columns = [
            0 => 'packages.packeg_id',
            1 => 'packages.manifest_no',
            2 => 'packages.supplier_tracking_no',
            3 => 'packages.courier',
            4 => 'packages.weight',
            5 => 'packages.item_description',
            6 => 'packages.user_name',
            7 => 'packages.status_name',
            8 => 'couriers.name',
            9 => 'packages.created_at',
            10 => 'packages.addedby_name',
            11 => 'packages.created_at',
            12 => 'packages.packeg_id',
        ];

        $baseQuery = $this->applyUnknownPackageFilter(
            DB::table('packages')->leftJoin('couriers', 'couriers.id', '=', 'packages.courier_id'),
            $unknownMode
        )->where('packages.status', '!=', 9);

        if($unknownMode === 'general' && trim((string) $request->input('filter_age')) === ''){
            $this->applyUnknownAgeFilter($baseQuery, $courier_id);
        }

        $totalData = (clone $baseQuery)->count();
        $this->applyPackageAdvancedFilters($baseQuery, $request, true);
        $search = trim((string) $request->input('search.value'));

        if($search !== ''){
            $baseQuery->where(function ($query) use ($search) {
                $query->orWhere('packages.user_name', 'LIKE', "%{$search}%")
                    ->orWhere('packages.manifest_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.supplier_tracking_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.tracking_no', 'LIKE', "%{$search}%")
                    ->orWhere('packages.courier', 'LIKE', "%{$search}%")
                    ->orWhere('packages.status_name', 'LIKE', "%{$search}%")
                    ->orWhere('couriers.name', 'LIKE', "%{$search}%");
            });
        }

        $totalFiltered = (clone $baseQuery)->count();
        $limit = (int) $request->input('length', 25);
        $start = (int) $request->input('start', 0);
        $orderColumn = $columns[(int) $request->input('order.0.column', 0)] ?? 'packages.packeg_id';
        $direction = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $posts = $baseQuery
            ->select(
                'packages.packeg_id',
                'packages.courier_id',
                'packages.mailbox',
                'packages.is_request',
                'packages.supplier_tracking_no',
                'packages.tracking_no',
                'packages.invoice',
                'packages.manifest_no',
                'packages.courier',
                'packages.weight',
                'packages.weight_unit',
                'packages.freight_type',
                'packages.warehouse_code',
                'packages.label_type',
                'packages.volume_cuft',
                'packages.volume_cbm',
                'packages.chargeable_weight',
                'packages.item_description',
                'packages.user_name',
                'packages.status_name',
                'packages.created_at',
                'packages.addedby_name',
                'couriers.name as assigned_courier_name'
            )
            ->offset($start)
            ->limit($limit)
            ->orderBy($orderColumn, $direction)
            ->get();

        $data = [];
        foreach($posts as $post){
            $createdAt = strtotime($post->created_at);
            $ageDays = $createdAt ? round((time() - $createdAt) / (60 * 60 * 24)) : 0;
            $btn = '';

            if($post->invoice != ''){
                $btn = "<a href='".url('/')."/invoices/".$post->invoice."' class='btn btn-warning' title='Customer invoice' download><i class='fa fa-download'></i> Customer invoice </a>";
            }

            $btn .= "<a href='".url('/')."/admin/package/edit/".$post->tracking_no."' class='all_button btn btn-sm btn-info' id='".$post->tracking_no."'>Edit</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn' id='".$post->packeg_id."'>Delete</a>";

            $data[] = [
                'packeg_id' => $post->packeg_id,
                'manifest_no' => $post->manifest_no,
                'supplier_tracking_no' => $post->supplier_tracking_no,
                'courier' => $post->courier,
                'weight' => $this->packageMeasureDisplay($post),
                'item_description' => substr(strip_tags((string) $post->item_description), 0, 50)."...",
                'first_name' => $post->user_name,
                'name' => $this->packageStatusDisplayName($post),
                'outlet_name' => $unknownMode === 'assigned' ? ($post->assigned_courier_name ?: $this->courierNameForId((int) $post->courier_id, '')) : '',
                'ageing' => $ageDays.' Days',
                'addedby_name' => $post->addedby_name,
                'created_at' => $createdAt ? date('j M Y', $createdAt) : '',
                'action' => $btn,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data,
        ]);
    }

    private function applyPackageAdvancedFilters($query, Request $request, bool $allowAgeFilter = false)
    {
        $dateFrom = trim((string) $request->input('filter_date_from'));
        $dateTo = trim((string) $request->input('filter_date_to'));
        $freight = strtolower(trim((string) $request->input('filter_freight')));
        $status = trim((string) $request->input('filter_status'));
        $carrier = trim((string) $request->input('filter_carrier'));
        $assignedCourierId = (int) $request->input('filter_assigned_courier_id', 0);
        $hawb = trim((string) $request->input('filter_hawb'));
        $tracking = trim((string) $request->input('filter_tracking'));
        $mailbox = trim((string) $request->input('filter_mailbox'));
        $customer = trim((string) $request->input('filter_customer'));
        $loggedBy = trim((string) $request->input('filter_logged_by'));
        $invoice = trim((string) $request->input('filter_invoice'));
        $measureMin = trim((string) $request->input('filter_measure_min'));
        $measureMax = trim((string) $request->input('filter_measure_max'));

        if($dateFrom !== ''){
            $query->whereDate('packages.created_at', '>=', $dateFrom);
        }

        if($dateTo !== ''){
            $query->whereDate('packages.created_at', '<=', $dateTo);
        }

        if(in_array($freight, ['air', 'sea'], true)){
            $query->where('packages.freight_type', 'LIKE', "%{$freight}%");
        }

        if($status !== ''){
            $query->where('packages.status_name', $status);
        }

        if($carrier !== ''){
            $query->where('packages.courier', 'LIKE', "%{$carrier}%");
        }

        if($assignedCourierId > 0){
            $query->where('packages.courier_id', $assignedCourierId);
        }

        if($hawb !== ''){
            $query->where('packages.manifest_no', 'LIKE', "%{$hawb}%");
        }

        if($tracking !== ''){
            $query->where(function ($trackingQuery) use ($tracking) {
                $trackingQuery->where('packages.supplier_tracking_no', 'LIKE', "%{$tracking}%")
                    ->orWhere('packages.tracking_no', 'LIKE', "%{$tracking}%");
            });
        }

        if($mailbox !== ''){
            $query->where('packages.mailbox', 'LIKE', "%{$mailbox}%");
        }

        if($customer !== ''){
            $query->where('packages.user_name', 'LIKE', "%{$customer}%");
        }

        if($loggedBy !== ''){
            $query->where('packages.addedby_name', 'LIKE', "%{$loggedBy}%");
        }

        if($invoice === 'yes'){
            $query->whereNotNull('packages.invoice')->where('packages.invoice', '!=', '');
        }elseif($invoice === 'no'){
            $query->where(function ($invoiceQuery) {
                $invoiceQuery->whereNull('packages.invoice')->orWhere('packages.invoice', '');
            });
        }

        if(is_numeric($measureMin)){
            $query->whereRaw(
                "CASE WHEN LOWER(COALESCE(packages.freight_type, '')) LIKE ? THEN COALESCE(NULLIF(packages.volume_cuft, 0), packages.chargeable_weight, 0) ELSE packages.weight END >= ?",
                ['%sea%', (float) $measureMin]
            );
        }

        if(is_numeric($measureMax)){
            $query->whereRaw(
                "CASE WHEN LOWER(COALESCE(packages.freight_type, '')) LIKE ? THEN COALESCE(NULLIF(packages.volume_cuft, 0), packages.chargeable_weight, 0) ELSE packages.weight END <= ?",
                ['%sea%', (float) $measureMax]
            );
        }

        if($allowAgeFilter){
            $this->applyUnknownAgeFilter($query, $request->input('filter_age'));
        }

        return $query;
    }

    public function create(Request $request){
        $data['title'] = 'Add Package Log';
        $data['class'] = 'package';
        $data['table'] = 'Add Package Log';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['types'] = PackageType::orderBy('id', 'DESC')->get();
            $data['couriers'] = Courier::get();
            $data['carriers'] = Carrier::get();
            $data['users'] = [];
            //dd($data['users']);
            return view('admin/packages/create' , $data);
        }else{
            $entryMode = $this->smartEntryMode($request->entry_mode);
            $freightType = $this->selectedFreightType($request->freight_type);
            $isSea = stripos($freightType, 'sea') !== false;
            $request->merge([
                'weight' => $isSea && trim((string) $request->weight) === '' ? 0 : $this->normalizedPackageWeight($request->weight, $request->weight_unit ?: $request->warehouse_code),
                'pieces' => max(1, (int) $request->pieces),
            ]);

            $validator = Validator::make($request->all(), [
                                'courier' => 'required|string|max:100',
                                'weight' => ($isSea ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01'),
                                'package_type' => 'required',
                                'pieces' => 'nullable|integer|min:1',
                                'dimension_length' => 'nullable|numeric|min:0',
                                'dimension_width' => 'nullable|numeric|min:0',
                                'dimension_height' => 'nullable|numeric|min:0',
                                'dimension_unit' => 'nullable|in:in,cm,ft',
                                'volume_cuft' => 'nullable|numeric|min:0',
                                'volume_cbm' => 'nullable|numeric|min:0',
                                'unknown_fee' => 'nullable|numeric|min:0',
                                'unknown_fee_reason' => 'nullable|string|max:255',
                                'package_images' => 'nullable|array|max:5',
                                'package_images.*' => 'nullable|image|max:5120',
                                'entry_mode' => 'nullable|in:STANDARD,DROPOFF,FORCED_UNKNOWN',
                                'routing_status' => 'nullable|in:VERIFIED,ASSIGNED_UNKNOWN,GENERAL_UNKNOWN',
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/packege/add')
                            ->withInput()
                            ->withErrors($validator);
            }else{
                $dimensionData = $this->packageDimensionData($request, $isSea);

                if ($isSea && ! $this->hasSeaMeasureData($dimensionData)) {
                    Session::flash('error_msg', 'Sea freight requires dimensions or a direct cubic value.');
                    return redirect('admin/packege/add')->withInput();
                }

                $routing = $this->resolvePackageRouting($request, $entryMode);
                if ($routing['routing_status'] !== 'VERIFIED' && trim((string) $request->consignee_name_on_package) === '' && trim((string) $request->unknown_reason) === '') {
                    Session::flash('error_msg', 'Unknown packages require the consignee name or an unreadable/no-name reason.');
                    return redirect('admin/packege/add')->withInput();
                }

                if ($routing['routing_status'] === 'ASSIGNED_UNKNOWN' && (int) $routing['courier_id'] <= 0) {
                    Session::flash('error_msg', 'Assigned Unknown packages require a courier system.');
                    return redirect('admin/packege/add')->withInput();
                }

                $idempotencyKey = trim((string) $request->idempotency_key);
                if ($idempotencyKey !== '' && Schema::hasColumn('packages', 'idempotency_key')) {
                    $existingPackage = Package::where('idempotency_key', $idempotencyKey)->first();
                    if ($existingPackage) {
                        Session::flash('success_msg', 'Package has already been added successfully');
                        return redirect($this->packageQueueRedirect($existingPackage));
                    }
                }

                $courier = $routing['courier'];
                $category_id = 0;
                $courier_id = 0;
                $supplier_tracking_no = $this->officialSupplierTrackingNumber($request->supplier_tracking_no, $entryMode);
                if ($entryMode === 'STANDARD' && $supplier_tracking_no === '') {
                    Session::flash('error_msg', 'Standard Entry requires a tracking number.');
                    return redirect('admin/packege/add')->withInput();
                }
                
                if($routing['courier_id'] == 0){
                    $is_request = 0;
                }else{
                    $is_request = 0;
                }
                
                $tracking_no = $this->nextWmsPackageTrackingNumber((int) $routing['courier_id']);
                if ($this->duplicatePackageExists($supplier_tracking_no, (int) $routing['courier_id'])) {
                    Session::flash('error_msg', 'Package already exists with this supplier tracking number.');
                    return redirect('admin/packege/add')->withInput();
                }
                $package_type = array_pad(explode('-' , (string) $request->package_type), 3, '');
                
                $packages = DB::table('packages')->orderBy('manifest_no','DESC')->first();
                //$user_name = commonHelper::getUserName($request->mailbox);
                // //dd($packages);
                // if($packages){
                //     $manifest_no = $packages->manifest_no + 1;
                // }else{
                //     $manifest_no = '10000';
                // }
                //return $manifest_no;
                //$manifest_no = '100'.substr(str_shuffle('123456789123456789123456789321654987'),0,4);
                $packages = new Package();
                $packages->tracking_no = $tracking_no;
                $packages->courier_id = $routing['courier_id'];
                $packages->mailbox = $routing['mailbox'];
                $packages->user_name = $routing['user_name'];
                $packages->courier = $courier;
                $packages->supplier_tracking_no = $supplier_tracking_no;
                //$packages->supplier_tracking_no = $request->supplier_tracking_no;
               // $packages->package_type = $package_type[1];
                $packages->package_code = $package_type[0];
                $packages->category_id = $category_id;
                $packages->vendor_id = $courier_id;
                $packages->weight = $request->weight;
                $packages->is_request = $is_request;
                $packages->manifest_no = $tracking_no;
                $packages->price = $request->price;
                $packages->freight_type = $freightType;
                $packages->item_description = $request->item_description;
                $packages->status_name = $this->warehouseStatusName($request);
                if (Schema::hasColumn('packages', 'is_temprary_logs')) {
                    $packages->is_temprary_logs = 0;
                }
                $this->assignPackageWorkflowColumns($packages, array_merge($dimensionData, [
                    'entry_mode' => $entryMode,
                    'routing_status' => $routing['routing_status'],
                    'warehouse_code' => $this->warehouseCode($request->warehouse_code),
                    'label_type' => $this->packageLabelType($request->warehouse_code, $freightType),
                    'weight_unit' => $this->weightUnit($request->weight_unit ?: $request->warehouse_code),
                    'pieces' => $request->pieces ?: 1,
                    'consignee_name_on_package' => $request->consignee_name_on_package,
                    'unknown_reason' => $request->unknown_reason,
                    'internal_notes' => $request->internal_notes,
                    'unknown_fee' => max(0, (float) $request->unknown_fee),
                    'unknown_fee_reason' => (float) $request->unknown_fee > 0 ? $request->unknown_fee_reason : null,
                    'package_image_paths' => $this->storePackageImages($request),
                    'chargeable_weight' => $request->chargeable_weight ?: ($dimensionData['volume_cuft'] ?: $request->weight),
                    'capture_method' => $request->capture_method ?: 'manual',
                    'idempotency_key' => $idempotencyKey ?: null,
                ]));
                $packages->addedby_id = session('user_id');
                $packages->addedby_name = session('name').' '.session('last_name');
                $packages->created_at = date('Y-m-d H:i:s');
                $packages->updated_at = date('Y-m-d H:i:s');
                $packages->save();
                $manifest_no = $tracking_no;
                $checkPkg = 'Unknown Package';
                if($routing['courier_id'] > 0){
                    $couriers = Courier::where('id' , $routing['courier_id'])->first();
                    if($couriers && $couriers->is_courier == 'Yes'){
                        //$tracking_no = 'tyru64ur8di5';
                        //$supplier_tracking_no = 'tycrttru64ddur8i5';
                        
                        $weight = round($request->weight);
                        $price = $this->priceForWeight($weight, $request->price);
                        
                        $checkPkg = $this->createPackage($couriers->base_url,$routing['courier_id'],$tracking_no,$supplier_tracking_no,$routing['mailbox'],$courier,$weight,$price,$package_type[0],$request->item_description,$manifest_no);
                        $responce = json_decode($checkPkg,true);
                        if(isset($responce['Package_no'])){
                            Package::where('tracking_no',$tracking_no)->update(['package_no' => $responce['Package_no']]);
                        }
                        Log::info("Courier ID - ".$request->courier_id.':'.$checkPkg.'. Supplier Tracking Number:'.$supplier_tracking_no);
                    }else{
                        $usersRes = User::where('mailbox' , $routing['mailbox'])->first();
                        $user_name = $usersRes ? trim($usersRes->first_name.' '.$usersRes->last_name) : $routing['user_name'];
                        $link = URL::to('/') . '/admin/dashboard';
                        $settings = DB::table('settings')->first();
                        $email_subject = 'Brand New UPDATE from '.($couriers->name ?? 'WMS');
                        $settings = DB::table('settings')->first();
                        if($settings){
                            $email_from = $settings->from_email;
                            
                        }
                        //$email_from = $couriers->email;
                        $status = 'Arrived in Miami';
                        //$this->send_email_packeg($usersRes->email,$couriers->name, $user_name,$tracking_no,$request->supplier_tracking_no,$mailbox,$courier,$request->item_description,$request->weight,$status,$email_subject, $email_from, 'create_packege');
                    }
                    //dd($checkPkg);
                }
                
                //Save Label
                //$this->createLabel($tracking_no,$request->courier_id);
                CourierSaasSync::package($packages->fresh());

                if((string) $request->print_after_save === '1'){
                    Session::flash('success_msg', 'Package has been added successfully');
                    return redirect('admin/download/label/'.$packages->packeg_id.'?print=1');
                }

                 if($routing['courier_id'] == 0 || $routing['routing_status'] === 'GENERAL_UNKNOWN'){
                     //Add Activity Unknown Package
                    $type = 'Unknown Package';
                    $dateTime = date('Y-m-d H:i:s');
                    $message = 'Unknown Package created with Supplier Tracking Number:<b>'.$supplier_tracking_no.'</b>';
                    $admin = session('user_id');
                    $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$routing['courier_id']);
                    Session::flash('success_msg', 'Package has been added successfully');
                    return redirect('admin/packeges/unknown/0');
                }else{
                    $type = 'Package';
                    $dateTime = date('Y-m-d H:i:s');
                    $message = 'Package created with Supplier Tracking Number:<b>'.$supplier_tracking_no.'</b>';
                    $admin = session('user_id');
                    $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$routing['courier_id']);
                    Session::flash('success_msg', 'Package has been added successfully');
                    return redirect($this->packageQueueRedirect($packages->fresh()));
                }
                
            }
        }
    }
    
    public function edit(Request $request , $tracking_no = ''){
        $data['title'] = 'Manage Package Log';
        $data['class'] = 'package';
        $data['table'] = 'Update Package Log';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['tracking_no'] = $tracking_no;
        $packageReq = PackageRequest::where('tracking_no' , $tracking_no)->first();
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['couriers'] = Courier::get();
            $data['carriers'] = Carrier::get();
            //$data['users'] = User::where('role_id' , 0)->get();
            $package = Package::where('tracking_no' , $tracking_no)->first();
            if(! $package){
                Session::flash('error_msg', 'Package not found.');
                return redirect('admin/packeges/logs/0');
            }
            $data['package'] = $package;
            $data['isEdit'] = true;
            $courier_id = $package->courier_id;
            $data['users'] = User::where('courier_id' , $courier_id)->get();
            $data['categories'] = [];
            $data['vendors'] = [];
            $data['types'] = PackageType::orderBy('id', 'DESC')->get();
            
            
            return view('admin/packages/create' , $data);
        }else{
            if($request->has('entry_mode')){
                return $this->updateSmartPackage($request, $tracking_no);
            }

            $freightType = $this->selectedFreightType($request->freight_type);
            $isSea = stripos($freightType, 'sea') !== false;
            if($isSea && trim((string) $request->weight) === ''){
                $request->merge(['weight' => 0]);
            }

            $validator = Validator::make($request->all(), [
                                'courier' => 'required',
                                'weight' => ($isSea ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01'),
                                //'item_description' => 'required'
                            ]);
            if ($validator->fails()) {
                return redirect('admin/package/edit/'.$tracking_no)
                                ->withInput()
                                ->withErrors($validator);
            }else{
                $mailboxValue = $request->mailbox ?: $request->mailbox_search;
                if($mailboxValue != ''){
                    $resMailbox = explode('+' , $mailboxValue);
                    $mailbox = $resMailbox[0] ?? '0';
                    $user_name = $resMailbox[1] ?? '';
                    $email = $resMailbox[2] ?? '';
                }else{
                    $mailbox = '0';
                    $user_name = '';
                    $email = '';
                }
                $courier = $request->courier;
                $category_id = 0;
                $courier_id = 0;
                $checkpack = Package::where('tracking_no' , $tracking_no)->first();
                //dd($checkpack);
                //If Conditions
                if($request->courier_id != $checkpack->courier_id){
                    $nextTrackingNo = $this->trackingNumberForReassignment($checkpack, (int) $request->courier_id);
                    //return '333333';
                    //return $checkpack->courier_id;
                    if($checkpack->courier_id != 0){
                        CourierSaasSync::deletePackage($checkpack);
                        $deletePkg = $this->deletePackageApi($checkpack->courier_id,$tracking_no);
                    }
                    
                    //return $request->courier_id;
                    $package_type = array_pad(explode('-' , (string) $request->package_type), 3, '');
                    if($request->courier_id != ''){
                        Package::where('tracking_no' , $tracking_no)
                            ->update([
                                    'tracking_no' => $nextTrackingNo,
                                    'manifest_no' => $nextTrackingNo,
                                    'mailbox' => $mailbox,
                                    'courier_id' => $request->courier_id,
                                    'courier' => $courier,
                                    'user_name' => $user_name,
                                    'package_type' => $package_type[1],
                                    'package_code' => $package_type[0],
                                    'weight' => $request->weight,
                                    'category_id' => $category_id,
                                    'vendor_id' => $courier_id,
                                    'price' => $request->price,
                                    'freight_type' => $this->selectedFreightType($request->freight_type),
                                    'is_request' => 0,
                                    'item_description' => $request->item_description
                                ]);
                    }else{
                        Package::where('tracking_no' , $tracking_no)
                            ->update([
                                    'tracking_no' => $nextTrackingNo,
                                    'manifest_no' => $nextTrackingNo,
                                    //'courier_id' => $request->courier_id,
                                    'courier' => $courier,
                                    //'user_name' => $user_name,
                                    'package_type' => $package_type[1],
                                    'package_code' => $package_type[0],
                                    'weight' => $request->weight,
                                    'price' => $request->price,
                                    'freight_type' => $this->selectedFreightType($request->freight_type),
                                    'category_id' => $category_id,
                                    'vendor_id' => $courier_id,
                                    'item_description' => $request->item_description,
                                    'addedby_id' => session('user_id'),
                                    'addedby_name' => session('name').' '.session('last_name')
                                ]);
                    }
                    $tracking_no = $nextTrackingNo;
                    $checkPkg = '';
                    if($request->courier_id != ''){
                        //return '2222222';
                        $package = Package::where('tracking_no' , $tracking_no)->first();
                        $couriers = Courier::where('id' , $request->courier_id)->first();
                            //dd($couriers);
                            //$tracking_no = 'tyru64ur8di5';
                            //$supplier_tracking_no = 'tycrttru64ddur8i5';
                            $supplier_tracking_no = $package->supplier_tracking_no;
                            $manifest_no = $package->manifest_no;
                            $weight = $request->weight;
                            $price = $this->priceForWeight($weight, $request->price ?? ($package->price ?? 0));
                            //return $request->courier_id;
                            if($request->courier_id != 0){
                                if($couriers->is_courier == 'Yes'){
                                    PackageRequest::where('tracking_no' , $tracking_no)->update(['status' => 1]);
                                    $checkPkg = $this->resendPackageApi($couriers->base_url,$couriers->id,$package->tracking_no,$package->supplier_tracking_no,$package->mailbox,$package->courier,$weight,$price,$package->package_type,$package->package_code,$package->item_description,$package->manifest_no);
                                    $responce = json_decode($checkPkg,true);
                                    if(isset($responce['Package_no'])){
                                        Package::where('tracking_no',$tracking_no)->update(['package_no' => $responce['Package_no']]);
                                    }
                                    if(isset($responce['status']) && $responce['status'] == false){
                                        Session::flash('error_msg', $responce['message'] ?? 'Courier API rejected the package.');
                                        return redirect('admin/package/edit/'.$tracking_no);
                                    }
                                }
                                
                            }else{
                                //return '2223333333';
                                $package = Package::where('tracking_no' , $tracking_no)->first();
                                $couriers = Courier::where('id' , $request->courier_id)->first();
                                $weight = round($package->weight);
                                $price = $this->priceForWeight($weight, $package->price ?? 0);
                                $checkPkg = $this->resendPackageApi($couriers->base_url,$couriers->courier_id,$package->tracking_no,$package->supplier_tracking_no,$package->mailbox,$package->courier,$weight,$price,$package->package_type,$package->package_code,$package->item_description,$package->manifest_no);
                                //Save Label
                                $this->createLabel($package->tracking_no,$couriers->courier_id);
                            }
                        
                        
                        //dd($checkPkg);
                    }
                    $package = Package::where('tracking_no' , $tracking_no)->first();
                    //Add Activity
                    $type = 'Package';
                    $dateTime = date('Y-m-d H:i:s');
                    $message = 'Package edited with Supplier Tracking Number:<b>'.$package->supplier_tracking_no.'</b>';
                    $admin = session('user_id');
                    $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$package->courier_id);
                    $this->syncEditedPackageToSaas($package);
                    if($package->courier_id == 0){
                        Session::flash('success_msg', 'Package has been updated successfully.');
                        return redirect('admin/packeges/unknown/0');
                    }else{
                        //Save Label
                        //$this->createLabel($tracking_no,$request->courier_id);
                        Session::flash('success_msg', 'Package has been updated successfully.');
                        return redirect('admin/packeges/logs/0');
                    }           
                }else{
                    //return '44444';
                    //return $tracking_no;
                    $package_type = array_pad(explode('-' , (string) $request->package_type), 3, '');
                    if($request->courier_id != ''){
                        
                        Package::where('tracking_no' , $tracking_no)
                            ->update([
                                    'mailbox' => $mailbox,
                                    'courier_id' => $request->courier_id,
                                    'courier' => $courier,
                                    'user_name' => $user_name,
                                    'package_type' => $package_type[1],
                                    'package_code' => $package_type[0],
                                    'weight' => $request->weight,
                                    'category_id' => $category_id,
                                    'vendor_id' => $courier_id,
                                    'price' => $request->price,
                                    'freight_type' => $this->selectedFreightType($request->freight_type),
                                    'is_request' => 0,
                                    'item_description' => $request->item_description
                                ]);
                    }else{
                        Package::where('tracking_no' , $tracking_no)
                            ->update([
                                    //'courier_id' => $request->courier_id,
                                    'courier' => $courier,
                                    //'user_name' => $user_name,
                                    'package_type' => $package_type[1],
                                    'package_code' => $package_type[0],
                                    'weight' => $request->weight,
                                    'price' => $request->price,
                                    'freight_type' => $this->selectedFreightType($request->freight_type),
                                    'category_id' => $category_id,
                                    'vendor_id' => $courier_id,
                                    'item_description' => $request->item_description,
                                    'addedby_id' => session('user_id'),
                                    'addedby_name' => session('name').' '.session('last_name')
                                ]);
                    }
                    
                    if($request->courier_id != ''){
                        //return 'errewrew'.$tracking_no;
                        $checkPkg = '';
                        $package = Package::where('tracking_no' , $tracking_no)->first();
                        //dd($package);
                        $couriers = Courier::where('id' , $request->courier_id)->first();
                            
                            //$tracking_no = 'tyru64ur8di5';
                            //$supplier_tracking_no = 'tycrttru64ddur8i5';
                            $supplier_tracking_no = $package->supplier_tracking_no;
                            $manifest_no = $package->manifest_no;
                            $weight = $request->weight;
                            $price = $this->priceForWeight($weight, $request->price ?? ($package->price ?? 0));
                           // return $request->courier_id;
                            if($request->courier_id != 0){
                                if($couriers->is_courier == 'Yes'){
                                //return 'eerrrrrr'.$supplier_tracking_no;
                                    PackageRequest::where('tracking_no' , $tracking_no)->update(['status' => 1]);
                                    $checkPkg = $this->resendPackageApi($couriers->base_url,$couriers->id,$package->tracking_no,$package->supplier_tracking_no,$package->mailbox,$package->courier,$weight,$price,$package->package_type,$package->package_code,$package->item_description,$package->manifest_no);
                                    $responce = json_decode($checkPkg,true);
                                    if(isset($responce['Package_no'])){
                                        Package::where('tracking_no',$tracking_no)->update(['package_no' => $responce['Package_no']]);
                                    }
                                    if(isset($responce['status']) && $responce['status'] == false){
                                        Session::flash('error_msg', $responce['message'] ?? 'Courier API rejected the package.');
                                        return redirect('admin/package/edit/'.$tracking_no);
                                    }
                                }
                                
                            }
                        //dd($checkPkg);
                    }
                    $package = Package::where('tracking_no' , $tracking_no)->first();
                    //Add Activity
                    $type = 'Package';
                    $dateTime = date('Y-m-d H:i:s');
                    $message = 'Package edited with Supplier Tracking Number:<b>'.$package->supplier_tracking_no.'</b>';
                    $admin = session('user_id');
                    $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$package->courier_id);
                    $this->syncEditedPackageToSaas($package);
                    if($package->courier_id == 0){
                        Session::flash('success_msg', 'Package has been updated successfully.');
                        return redirect('admin/packeges/unknown/0');
                    }else{
                        //Save Label
                        //$this->createLabel($tracking_no,$request->courier_id);
                        Session::flash('success_msg', 'Package has been updated successfully.');
                        return redirect('admin/packeges/logs/0');
                    }
                }
                
            }
        }

    }

    private function updateSmartPackage(Request $request, string $trackingNo)
    {
        $package = Package::where('tracking_no', $trackingNo)->first();
        if(! $package){
            Session::flash('error_msg', 'Package not found.');
            return redirect('admin/packeges/logs/0');
        }

        $entryMode = $this->smartEntryMode($request->entry_mode);
        $freightType = $this->selectedFreightType($request->freight_type);
        $isSea = stripos($freightType, 'sea') !== false;
        $request->merge([
            'weight' => $isSea && trim((string) $request->weight) === '' ? 0 : $this->normalizedPackageWeight($request->weight, $request->weight_unit ?: $request->warehouse_code),
            'pieces' => max(1, (int) $request->pieces),
        ]);

        $validator = Validator::make($request->all(), [
            'supplier_tracking_no' => 'required|string|max:150',
            'courier' => 'required|string|max:100',
            'weight' => ($isSea ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01'),
            'package_type' => 'required',
            'pieces' => 'nullable|integer|min:1',
            'dimension_length' => 'nullable|numeric|min:0',
            'dimension_width' => 'nullable|numeric|min:0',
            'dimension_height' => 'nullable|numeric|min:0',
            'dimension_unit' => 'nullable|in:in,cm,ft',
            'volume_cuft' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'unknown_fee' => 'nullable|numeric|min:0',
            'unknown_fee_reason' => 'nullable|string|max:255',
            'package_images' => 'nullable|array|max:5',
            'package_images.*' => 'nullable|image|max:5120',
            'remove_package_images' => 'nullable|array|max:5',
            'remove_package_images.*' => 'nullable|string|max:255',
            'entry_mode' => 'nullable|in:STANDARD,DROPOFF,FORCED_UNKNOWN',
            'routing_status' => 'nullable|in:VERIFIED,ASSIGNED_UNKNOWN,GENERAL_UNKNOWN',
        ]);

        if($validator->fails()){
            return redirect('admin/package/edit/'.$trackingNo)->withInput()->withErrors($validator);
        }

        $dimensionData = $this->packageDimensionData($request, $isSea);
        if($isSea && ! $this->hasSeaMeasureData($dimensionData)){
            Session::flash('error_msg', 'Sea freight requires dimensions or a direct cubic value.');
            return redirect('admin/package/edit/'.$trackingNo)->withInput();
        }

        $routing = $this->resolvePackageRouting($request, $entryMode);
        if($routing['routing_status'] !== 'VERIFIED' && trim((string) $request->consignee_name_on_package) === '' && trim((string) $request->unknown_reason) === ''){
            Session::flash('error_msg', 'Unknown packages require the consignee name or an unreadable/no-name reason.');
            return redirect('admin/package/edit/'.$trackingNo)->withInput();
        }
        if($routing['routing_status'] === 'ASSIGNED_UNKNOWN' && (int) $routing['courier_id'] <= 0){
            Session::flash('error_msg', 'Assigned Unknown packages require a courier system identified from the mailbox code.');
            return redirect('admin/package/edit/'.$trackingNo)->withInput();
        }

        $supplierTrackingNo = $this->normalizeTrackingNumber($request->supplier_tracking_no);
        if($this->duplicatePackageExists($supplierTrackingNo, (int) $routing['courier_id'], (int) $package->packeg_id)){
            Session::flash('error_msg', 'Package already exists with this supplier tracking number.');
            return redirect('admin/package/edit/'.$trackingNo)->withInput();
        }

        $oldCourierId = (int) $package->courier_id;
        $nextTrackingNo = $oldCourierId !== (int) $routing['courier_id']
            ? $this->trackingNumberForReassignment($package, (int) $routing['courier_id'])
            : $package->tracking_no;
        if($oldCourierId !== (int) $routing['courier_id'] && $oldCourierId > 0){
            CourierSaasSync::deletePackage($package);
            $this->deletePackageApi($oldCourierId, $package->tracking_no);
        }

        $packageType = array_pad(explode('-', (string) $request->package_type), 3, '');
        $storedImages = $this->storePackageImages($request);
        $existingImages = $this->filteredExistingPackageImages($package, (array) $request->remove_package_images);

        $package->tracking_no = $nextTrackingNo;
        $package->manifest_no = $nextTrackingNo;
        $package->courier_id = $routing['courier_id'];
        $package->mailbox = $routing['mailbox'];
        $package->user_name = $routing['user_name'];
        $package->courier = $routing['courier'];
        $package->supplier_tracking_no = $supplierTrackingNo;
        $package->package_type = $packageType[1];
        $package->package_code = $packageType[0];
        $package->category_id = 0;
        $package->vendor_id = 0;
        $package->weight = $request->weight;
        $package->price = $this->priceForWeight($request->weight, $request->price);
        $package->freight_type = $freightType;
        $package->is_request = 0;
        $package->item_description = $request->item_description;
        $package->status_name = $this->warehouseStatusName($request);
        if (Schema::hasColumn('packages', 'is_temprary_logs')) {
            $package->is_temprary_logs = 0;
        }

        $this->assignPackageWorkflowColumns($package, array_merge($dimensionData, [
            'entry_mode' => $entryMode,
            'routing_status' => $routing['routing_status'],
            'warehouse_code' => $this->warehouseCode($request->warehouse_code),
            'label_type' => $this->packageLabelType($request->warehouse_code, $freightType),
            'weight_unit' => $this->weightUnit($request->weight_unit ?: $request->warehouse_code),
            'pieces' => $request->pieces ?: 1,
            'consignee_name_on_package' => $request->consignee_name_on_package,
            'unknown_reason' => $request->unknown_reason,
            'internal_notes' => $request->internal_notes,
            'unknown_fee' => max(0, (float) $request->unknown_fee),
            'unknown_fee_reason' => (float) $request->unknown_fee > 0 ? $request->unknown_fee_reason : null,
            'package_image_paths' => $storedImages ?: ($existingImages ? json_encode($existingImages) : null),
            'chargeable_weight' => $request->chargeable_weight ?: ($dimensionData['volume_cuft'] ?: $request->weight),
            'capture_method' => $request->capture_method ?: 'manual',
        ]));
        $package->addedby_id = session('user_id');
        $package->addedby_name = session('name').' '.session('last_name');
        $package->updated_at = date('Y-m-d H:i:s');
        $package->save();

        $checkPkg = '';
        if((int) $routing['courier_id'] > 0){
            $couriers = Courier::where('id', $routing['courier_id'])->first();
            if($couriers && $couriers->is_courier == 'Yes'){
                $weight = round($request->weight);
                $price = $this->priceForWeight($weight, $package->price ?? 0);
                $checkPkg = $this->resendPackageApi($couriers->base_url, $couriers->id, $package->tracking_no, $package->supplier_tracking_no, $package->mailbox, $package->courier, $weight, $price, $package->package_type, $package->package_code, $package->item_description, $package->manifest_no);
                $response = json_decode($checkPkg, true);
                if(isset($response['Package_no'])){
                    $package->package_no = $response['Package_no'];
                    $package->save();
                }
                if(isset($response['status']) && $response['status'] == false){
                    Session::flash('error_msg', $response['message'] ?? 'Courier API rejected the package.');
                    return redirect('admin/package/edit/'.$package->tracking_no);
                }
            }
        }

        commonHelper::addActivity('Package', date('Y-m-d H:i:s'), 'Package edited with Supplier Tracking Number:<b>'.$package->supplier_tracking_no.'</b>', session('user_id'), $checkPkg, $package->courier_id);
        $saasSynced = CourierSaasSync::package($package->fresh());
        $message = 'Package has been updated successfully. WMS ID: '.$package->packeg_id.'. Tracking: '.$package->tracking_no.'.';
        if(! $saasSynced){
            $message .= ' CMS sync failed: '.(CourierSaasSync::lastError() ?: 'not synced');
        }
        Session::flash($saasSynced ? 'success_msg' : 'error_msg', $message);

        if((string) $request->print_after_save === '1'){
            return redirect('admin/download/label/'.$package->packeg_id.'?print=1');
        }

        return redirect($this->packageQueueRedirect($package));
    }

    private function syncEditedPackageToSaas(?Package $package): void
    {
        if(! $package){
            return;
        }

        CourierSaasSync::package($package->fresh());
    }

    private function smartEntryMode($value): string
    {
        $value = strtoupper(trim((string) $value));

        return in_array($value, ['STANDARD', 'DROPOFF', 'FORCED_UNKNOWN'], true) ? $value : 'STANDARD';
    }

    private function warehouseCode($value): string
    {
        return stripos((string) $value, 'china') !== false || strtoupper((string) $value) === 'CHINA' ? 'CHINA' : 'USA';
    }

    private function packageLabelType($warehouseCode, string $freightType): string
    {
        $region = $this->warehouseCode($warehouseCode) === 'CHINA' ? 'CN' : 'USA';
        $method = stripos($freightType, 'sea') !== false ? 'Sea' : 'Air';

        return $region.' '.$method;
    }

    private function weightUnit($warehouseCode): string
    {
        $value = strtolower(trim((string) $warehouseCode));
        if (in_array($value, ['kg', 'kilogram', 'kilograms'], true)) {
            return 'kg';
        }
        if (in_array($value, ['lb', 'lbs', 'pound', 'pounds'], true)) {
            return 'lb';
        }

        return $this->warehouseCode($warehouseCode) === 'CHINA' ? 'kg' : 'lb';
    }

    private function normalizedPackageWeight($value, $warehouseCode): float
    {
        $value = trim((string) $value);
        if ($this->weightUnit($warehouseCode) === 'lb' && str_contains($value, ':')) {
            [$pounds, $ounces] = array_pad(explode(':', $value, 2), 2, 0);

            return round(max(0, (float) $pounds) + (max(0, (float) $ounces) / 16), 2);
        }

        return round(max(0, (float) $value), 2);
    }

    private function officialSupplierTrackingNumber($value, string $entryMode): string
    {
        $value = $this->normalizeTrackingNumber($value);
        if ($entryMode === 'DROPOFF') {
            return $this->nextSmartTemporaryTrackingNumber('DROP');
        }
        if ($entryMode === 'FORCED_UNKNOWN') {
            return $this->nextSmartTemporaryTrackingNumber('GEN-UNK');
        }

        return $value;
    }

    private function normalizeTrackingNumber($value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $value))) ?: '';
    }

    private function nextSmartTemporaryTrackingNumber(string $prefix): string
    {
        do {
            $tracking = $prefix.'-'.date('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Package::where('supplier_tracking_no', $tracking)->exists());

        return $tracking;
    }

    private function resolvePackageRouting(Request $request, string $entryMode): array
    {
        $mailboxValue = trim((string) ($request->mailbox ?: $request->mailbox_search));
        $consignee = trim((string) $request->consignee_name_on_package);
        $parts = $mailboxValue !== '' ? array_pad(explode('+', $mailboxValue), 3, '') : ['', '', ''];
        $mailbox = strtoupper(trim((string) $parts[0]));
        $courierName = trim((string) $request->courier);

        if ($entryMode === 'FORCED_UNKNOWN') {
            return [
                'routing_status' => 'GENERAL_UNKNOWN',
                'courier_id' => 0,
                'mailbox' => '0',
                'user_name' => $consignee,
                'courier' => 'Unknown Carrier',
            ];
        }

        $matchedUser = $this->matchedMailboxUser($mailbox, 0);
        if ($matchedUser && (int) $matchedUser->courier_id > 0) {
            $matchedCourierName = $this->courierNameForId((int) $matchedUser->courier_id, $courierName);

            return [
                'routing_status' => 'VERIFIED',
                'courier_id' => (int) $matchedUser->courier_id,
                'mailbox' => strtoupper((string) $matchedUser->mailbox),
                'user_name' => trim(($matchedUser->first_name ?? '').' '.($matchedUser->last_name ?? '')) ?: ($matchedUser->user_name ?? ''),
                'courier' => $matchedCourierName,
            ];
        }

        $prefixCourier = $this->matchedCourierForMailboxPrefix($mailbox);
        if ($mailbox !== '' && $prefixCourier) {
            return [
                'routing_status' => 'ASSIGNED_UNKNOWN',
                'courier_id' => (int) $prefixCourier->id,
                'mailbox' => $mailbox,
                'user_name' => $consignee,
                'courier' => $prefixCourier->name ?: $courierName,
            ];
        }

        return [
            'routing_status' => 'GENERAL_UNKNOWN',
            'courier_id' => 0,
            'mailbox' => '0',
            'user_name' => $consignee,
            'courier' => $courierName ?: 'Unknown Carrier',
        ];
    }

    private function matchedMailboxUser(string $mailbox, int $courierId): ?User
    {
        if ($mailbox === '') {
            return null;
        }

        return User::where('mailbox', $mailbox)
            ->when($courierId > 0, fn ($query) => $query->where('courier_id', $courierId))
            ->first();
    }

    private function matchedCourierForMailboxPrefix(string $code): ?Courier
    {
        foreach ($this->mailboxPrefixCandidates($code) as $prefix) {
            $courierId = $this->courierIdForMailboxPrefix($prefix);

            if ($courierId) {
                return Courier::where('id', $courierId)->first();
            }
        }

        return null;
    }

    private function mailboxPrefixCandidates(string $code): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return [];
        }

        if (str_contains($code, '-')) {
            $rawParts = explode('-', $code);
            if (count($rawParts) === 2 && trim($rawParts[1]) === '') {
                return [];
            }

            $parts = array_values(array_filter($rawParts, fn ($part) => trim($part) !== ''));
            $last = end($parts) ?: '';
            $courierIndex = count($parts) - 1;
            if (preg_match('/^\d+$/', $last) && count($parts) >= 2) {
                $courierIndex = count($parts) - 2;
                $last = $parts[$courierIndex];
            }

            $courierCode = $this->leadingLetters($last);
            if (strlen($courierCode) < 2) {
                return [];
            }

            $candidates = [$courierCode];
            $prefixParts = array_slice($parts, 0, $courierIndex);
            if ($prefixParts) {
                array_unshift($candidates, implode('-', array_merge($prefixParts, [$courierCode])));
            }

            return array_values(array_unique(array_filter($candidates)));
        }

        $courierCode = $this->leadingLetters($code);

        return strlen($courierCode) >= 2 ? [$courierCode] : [];
    }

    private function leadingLetters(string $value): string
    {
        preg_match('/^[A-Z]+/', strtoupper(trim($value)), $matches);

        return $matches[0] ?? '';
    }

    private function courierIdForMailboxPrefix(string $prefix): ?int
    {
        $query = User::whereNotNull('courier_id')->where('courier_id', '>', 0);

        if (str_contains($prefix, '-')) {
            $query->where('mailbox', 'like', $prefix.'%');
        } else {
            $query->where(function ($inner) use ($prefix) {
                $inner->where(function ($plain) use ($prefix) {
                    $plain->where('mailbox', 'like', $prefix.'%')
                        ->where('mailbox', 'not like', $prefix.'-%');
                })->orWhere('mailbox', 'like', '%-'.$prefix.'%');
            });
        }

        $courierId = $query->value('courier_id');

        return $courierId ? (int) $courierId : null;
    }

    private function courierNameForId(int $courierId, string $fallback = ''): string
    {
        if ($courierId <= 0) {
            return $fallback;
        }

        return (string) (Courier::where('id', $courierId)->value('name') ?: $fallback);
    }

    private function packageDimensionData(Request $request, bool $isSea): array
    {
        $length = $isSea ? max(0, (float) $request->dimension_length) : null;
        $width = $isSea ? max(0, (float) $request->dimension_width) : null;
        $height = $isSea ? max(0, (float) $request->dimension_height) : null;
        $unit = in_array($request->dimension_unit, ['in', 'cm', 'ft'], true) ? $request->dimension_unit : 'in';
        $cuft = null;
        $cbm = null;

        if ($isSea && $length > 0 && $width > 0 && $height > 0) {
            $lengthIn = $unit === 'cm' ? $length / 2.54 : ($unit === 'ft' ? $length * 12 : $length);
            $widthIn = $unit === 'cm' ? $width / 2.54 : ($unit === 'ft' ? $width * 12 : $width);
            $heightIn = $unit === 'cm' ? $height / 2.54 : ($unit === 'ft' ? $height * 12 : $height);
            $cuft = round(($lengthIn * $widthIn * $heightIn) / 1728, 4);
            $cbm = round($cuft / 35.3147, 4);
        } elseif ($isSea) {
            $directCuft = max(0, (float) $request->volume_cuft);
            $directCbm = max(0, (float) $request->volume_cbm);

            if ($directCuft > 0 || $directCbm > 0) {
                $cuft = $directCuft > 0 ? $directCuft : $directCbm * 35.3147;
                $cbm = $directCbm > 0 ? $directCbm : $cuft / 35.3147;
                $cuft = round($cuft, 4);
                $cbm = round($cbm, 4);
            }
        }

        return [
            'dimension_length' => $length,
            'dimension_width' => $width,
            'dimension_height' => $height,
            'dimension_unit' => $isSea ? $unit : null,
            'volume_cuft' => $cuft,
            'volume_cbm' => $cbm,
        ];
    }

    private function hasSeaMeasureData(array $dimensionData): bool
    {
        $hasDimensions = ($dimensionData['dimension_length'] ?? 0) > 0
            && ($dimensionData['dimension_width'] ?? 0) > 0
            && ($dimensionData['dimension_height'] ?? 0) > 0;

        return $hasDimensions || ($dimensionData['volume_cuft'] ?? 0) > 0 || ($dimensionData['volume_cbm'] ?? 0) > 0;
    }

    private function assignPackageWorkflowColumns(Package $package, array $values): void
    {
        foreach ($values as $column => $value) {
            if (Schema::hasColumn('packages', $column)) {
                $package->{$column} = $value;
            }
        }
    }

    private function storePackageImages(Request $request): ?string
    {
        if (! $request->hasFile('package_images')) {
            return null;
        }

        $paths = [];
        $destinationPath = base_path('public/packages/images');
        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        foreach (array_slice((array) $request->file('package_images'), 0, 5) as $image) {
            if (! $image || ! $image->isValid()) {
                continue;
            }

            $extension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
            $name = date('YmdHis').'_'.Str::random(8).'.'.$extension;
            $image->move($destinationPath, $name);
            $paths[] = 'packages/images/'.$name;
        }

        return $paths ? json_encode($paths) : null;
    }

    private function filteredExistingPackageImages(Package $package, array $removePaths): array
    {
        $decoded = json_decode((string) ($package->package_image_paths ?? ''), true);
        $existing = is_array($decoded) ? array_values(array_filter($decoded)) : [];
        $remove = array_flip(array_map(fn ($path) => trim((string) $path), $removePaths));

        return array_slice(array_values(array_filter($existing, fn ($path) => ! isset($remove[$path]))), 0, 5);
    }

    private function selectedFreightType($value): string
    {
        return stripos((string) $value, 'sea') !== false ? 'Sea Freight' : 'Air Freight';
    }

    private function priceForWeight($weight, $fallback = 0)
    {
        $weight = (float) $weight;
        $lookupWeight = $weight > 76 ? 76 : $weight;
        $weightRes = DB::table('weights')->where('weight', $lookupWeight)->first();

        if($weightRes && isset($weightRes->price)){
            return $weightRes->price;
        }

        return is_numeric($fallback) ? $fallback : 0;
    }

    private function warehouseStatusName(Request $request): string
    {
        $text = strtolower(trim(implode(' ', [
            $request->region ?? '',
            $request->warehouse ?? '',
            $request->warehouse_code ?? '',
            $request->label_type ?? '',
            $request->freight_type ?? '',
            $request->courier ?? '',
            $request->status_name ?? '',
        ])));

        return $this->warehouseStatusNameFromText($text);
    }

    private function warehouseStatusNameFromText($value): string
    {
        $text = strtolower((string) $value);

        return (str_contains($text, 'china') || str_contains($text, 'cn warehouse') || preg_match('/(^|[^a-z])cn([^a-z]|$)/', $text))
            ? 'Received at CN Warehouse'
            : 'Received at US Warehouse';
    }

    private function packageStatusDisplayName($package): string
    {
        $status = trim((string) ($package->status_name ?? ''));
        $statusKey = strtolower($status);

        if(! str_contains($statusKey, 'received at') || ! str_contains($statusKey, 'warehouse')){
            return $status;
        }

        return $this->warehouseStatusNameFromText(implode(' ', [
            $package->warehouse_code ?? '',
            $package->label_type ?? '',
            $package->freight_type ?? '',
            $package->courier ?? '',
            $status,
        ]));
    }

    private function duplicatePackageExists($supplierTrackingNo, int $courierId, ?int $exceptPackageId = null): bool
    {
        $supplierTrackingNo = $this->normalizeTrackingNumber($supplierTrackingNo);
        if ($supplierTrackingNo === '') {
            return false;
        }

        return Package::where(function ($query) use ($supplierTrackingNo) {
                foreach(['supplier_tracking_no', 'tracking_no', 'manifest_no'] as $column){
                    $query->orWhereRaw(
                        "UPPER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), ' ', ''), '-', ''), '#', ''), '/', '')) = ?",
                        [$supplierTrackingNo]
                    );
                }
            })
            ->when($exceptPackageId, fn ($query) => $query->where('packeg_id', '!=', $exceptPackageId))
            ->exists();
    }

    private function unknownQueueMode($type): string
    {
        return in_array($type, ['assigned', 'assigned-claims', 'claims'], true) ? 'assigned' : 'general';
    }

    private function applyUnknownPackageFilter($query, string $mode = 'general')
    {
        if($mode === 'assigned'){
            return $query->where('packages.courier_id', '>', 0)
                ->where(function ($assigned) {
                    $assigned->where('packages.routing_status', 'ASSIGNED_UNKNOWN')
                        ->orWhere(function ($mailbox) {
                            $mailbox->whereNull('packages.mailbox')
                                ->orWhere('packages.mailbox', '')
                                ->orWhere('packages.mailbox', '0');
                        });
                });
        }

        return $query->where(function ($inner) {
            $inner->where('packages.courier_id', 0)
                ->orWhereNull('packages.courier_id');
        });
    }

    private function applyUnknownAgeFilter($query, $ageBucket)
    {
        if((string) $ageBucket === '2'){
            return $query->whereDate('packages.created_at', '>=', date('Y-m-d', strtotime('-2 days')))
                ->whereDate('packages.created_at', '<=', date('Y-m-d'));
        }

        if((string) $ageBucket === '30'){
            return $query->whereDate('packages.created_at', '>=', date('Y-m-d', strtotime('-32 days')))
                ->whereDate('packages.created_at', '<=', date('Y-m-d', strtotime('-2 days')));
        }

        if((string) $ageBucket === '60'){
            return $query->whereDate('packages.created_at', '>=', date('Y-m-d', strtotime('-60 days')))
                ->whereDate('packages.created_at', '<=', date('Y-m-d', strtotime('-30 days')));
        }

        if((string) $ageBucket === '90'){
            return $query->whereDate('packages.created_at', '>=', date('Y-m-d', strtotime('-90 days')))
                ->whereDate('packages.created_at', '<=', date('Y-m-d', strtotime('-60 days')));
        }

        if((string) $ageBucket === '120'){
            return $query->whereDate('packages.created_at', '>=', date('Y-m-d', strtotime('-120 days')))
                ->whereDate('packages.created_at', '<=', date('Y-m-d', strtotime('-90 days')));
        }

        if((string) $ageBucket === '121'){
            return $query->whereDate('packages.created_at', '<=', date('Y-m-d', strtotime('-121 days')));
        }

        return $query;
    }

    private function packageQueueRedirect($package): string
    {
        $routingStatus = (string) ($package->routing_status ?? '');
        $mailbox = trim((string) ($package->mailbox ?? ''));

        if((int) ($package->courier_id ?? 0) > 0 && ($routingStatus === 'ASSIGNED_UNKNOWN' || $mailbox === '' || $mailbox === '0')){
            return 'admin/packeges/assigned-claims/0';
        }

        if((int) ($package->courier_id ?? 0) === 0 || $routingStatus === 'GENERAL_UNKNOWN'){
            return 'admin/packeges/unknown/0';
        }

        return 'admin/packeges/logs/0';
    }
    
    public function deletePackageApi($courier_id,$tracking_no){
        $couriers = Courier::where('id' , $courier_id)->first();
        $curl = curl_init();
        
        $url = $couriers->base_url.'/api/deletePackage';
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'"}',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function createLabel($tracking_no,$courier_id){
        $couriers = Courier::where('id' , $courier_id)->first();
        if($couriers){
            $base_url = $couriers->base_url;
        }else{
            $base_url = '';
        }
        $packages = DB::table('packages')->where('tracking_no',$tracking_no)->first();
                //dd($packages);
                //return $packages->mailbox;
                if($courier_id > 0){
                    $locationRes = $this->GetUsersLocation($base_url,$courier_id,$packages->mailbox);
                    $responceLoc = json_decode($locationRes,true);
                    if(isset($responceLoc['status']) && $responceLoc['status'] == 'success'){
                        $loc_name = $responceLoc['location'];
                    }else{
                        $loc_name = '';
                    }
                }else{
                    $loc_name = '';
                }
                $user_name = commonHelper::getUserName($packages->mailbox);
                $fileName = time().'_label.pdf';
                $pathPDF = base_path() . '/public/packages/labels/'.$fileName;
                $baseURL = URL::to('/');
                //$expUrl = explode('/index.php' , $baseURL);
                $settings = DB::table('settings')->first();
                $data['user_name'] = $user_name;
                $data['mailbox'] = $packages->mailbox;
                $data['weight'] = $packages->weight;
                $data['tracking_no'] = $packages->package_no ?: ($packages->supplier_tracking_no ?: ($packages->manifest_no ?: (string) $packages->packeg_id));
                $data['location'] = $loc_name ?: 'Jamaica';
                $data['category'] = $packages->item_description;
                $data['company_name'] = $settings->company_name;
                $data['package_shipping_address_1'] = $settings->package_shipping_address_1;
                $data['package_shipping_address_2'] = $settings->package_shipping_address_2;
                $data['city'] = $settings->city;
                $data['zip'] = $settings->zip;
                $data['country'] = $settings->country;
                $this->applyLabelMeasureData($data, $packages);
                //$data['unique_code'] = $package_no;
                //$data['location'] = $settings->country;
                $url = $baseURL . '/packages/labels/'.$fileName;
                $pdf = PDF::setOptions(['defaultMediaType' => 'print'])->loadView('admin/packages/pdfinvoice' , $data)->save($pathPDF);
                Package::where('tracking_no',$tracking_no)->update(['label' => $fileName]);
                return 'label Created';
    }
    
    public function GetUsersLocation($base_url,$courier_id,$mailbox){
        $couriers = Courier::where('id' , $courier_id)->first();
        $curl = curl_init();
        $url = $base_url.'/api/GetUsersLocation';

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"uoe_no":"'.$mailbox.'"}',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function checkPackage($base_url,$courier_id,$tracking_no,$supplier_tracking_no){
        $couriers = Courier::where('id' , $courier_id)->first();
        $curl = curl_init();
        $url = $base_url.'/api/checkPackage';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'","supplier_tracking_no":"'.$supplier_tracking_no.'"}',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function createPackage($base_url,$courier_id,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$weight,$price,$package_code,$item_description,$manifest_no){
        
        $couriers = Courier::where('id' , $courier_id)->first();
        if(!$couriers || empty($base_url)){
            return json_encode(['status' => false, 'message' => 'Courier API is not configured.']);
        }

        $curl = curl_init();
        $headers = ['Content-Type: application/json'];
        if(!empty($couriers->api_key)){
            $headers[] = 'api_key: '.$couriers->api_key;
        }

        $payload = [
            'tracking_no' => $tracking_no,
            'warehouse' => $this->settingsWarehouseName(),
            'mailbox' => $mailbox,
            'courier' => $courier,
            'supplier_tracking_no' => $supplier_tracking_no,
            'package_code' => $package_code,
            'weight' => $weight,
            'price' => $price,
            'manifest_no' => $manifest_no,
            'item_description' => $item_description,
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => rtrim($base_url, '/').'/api/savePackage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if($curlError){
            return json_encode(['status' => false, 'message' => 'CURL Error: '.$curlError]);
        }

        return $response ?: json_encode(['status' => true, 'message' => 'Package processed']);
        
    }
    
    public function checkPackageNew($base_url,$courier_id,$tracking_no,$supplier_tracking_no){
        $curl = curl_init();
        $couriers = Courier::where('id' , $courier_id)->first();
        $url = $base_url.'/api/checkPackage';
        $api_key = $couriers->api_key;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'","supplier_tracking_no":"'.$supplier_tracking_no.'"}',
            CURLOPT_HTTPHEADER => array(
                'api_key: '.$api_key,
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function createPackageNew($base_url,$courier_id,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$weight,$price,$package_type,$category_id,$item_description,$manifest_no){
        $curl = curl_init();
        $couriers = Courier::where('id' , $courier_id)->first();
        $url = $base_url.'/api/savePackage';
        $api_key = $couriers->api_key;
        $payload = [
            'tracking_no' => $tracking_no,
            'warehouse' => $this->settingsWarehouseName(),
            'mailbox' => $mailbox,
            'courier' => $courier,
            'supplier_tracking_no' => $supplier_tracking_no,
            'package_type' => $package_type,
            'package_code' => $category_id,
            'weight' => $weight,
            'price' => $price,
            'manifest_no' => $manifest_no,
            'item_description' => $item_description,
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'api_key: '.$api_key,
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }

    private function settingsWarehouseName(): string
    {
        $settings = DB::table('settings')->first();

        return (string) ($settings->company_name ?? $settings->site_name ?? $settings->site_title ?? config('app.name'));
    }

    public function import(Request $request){
        $data['title'] = 'Import Package Log';
        $data['class'] = 'package';
        $data['table'] = 'Import Package Log';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['uploaded'] = [];
            $data['packages'] = [];
            $data['syncStats'] = null;
            return view('admin/packages/import' , $data);
        }else{
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }
            if ($request->hasFile('input_file')) {
                $image = $request->file('input_file');
                $name = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/users');
                $image->move($destinationPath, $name);
                $file = $destinationPath.'/'.$name;
                $handle = fopen($file, "r");
                $header = true;
                $packageArray = [];
                $packageUploadArray = [];
                $createdPackageIds = [];
                $count = 0;
                while ($csvLine = fgetcsv($handle, 1000, ",")) {
                    //echo count($csvLine);exit;
                    if ($header) {
                        $header = false;
                    } else {
                        if (count($csvLine) < 8) {
                            $packageArray[] = [
                                'name' => $csvLine[0] ?? 'Invalid row',
                                'user_name' => $csvLine[4] ?? '',
                                'courier' => $csvLine[5] ?? '',
                                'date_received' => $csvLine[2] ?? '',
                                'manifest_no' => $csvLine[1] ?? '',
                                'weight' => $csvLine[6] ?? '',
                                'supplier_tracking_no' => $csvLine[7] ?? '',
                                'item_description' => 'Missing required columns',
                            ];
                            continue;
                        }

                        //echo '<pre>'; print_r($csvLine);exit;
                        $nameArr = explode(' ' , $csvLine[4]);
                        $last_name1 = $nameArr[0];
                        if(isset($nameArr[1])){
                            $valueUp = ucfirst(strtolower($nameArr[1]));
                            $valueLower = ucfirst(strtolower($last_name1));
                            $users = DB::table('users')->where('first_name' , $valueLower)->where('last_name' , $valueUp)->first();
                            //dd($users);
                        }else{
                            $users = '';
                        }
                        //dd($users);
                        if($users){
                            $supplierTrackingNo = $this->normalizeTrackingNumber($csvLine[7] ?? '');
                            $package = $this->duplicatePackageExists($supplierTrackingNo, 0)
                                ? Package::where(function ($query) use ($supplierTrackingNo) {
                                    foreach(['supplier_tracking_no', 'tracking_no', 'manifest_no'] as $column){
                                        $query->orWhereRaw(
                                            "UPPER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), ' ', ''), '-', ''), '#', ''), '/', '')) = ?",
                                            [$supplierTrackingNo]
                                        );
                                    }
                                })->first()
                                : null;
                            //dd($package);
                            if($package == null){
                                $count = $count + 1;
                                $mailbox = $users->mailbox;
                                $importCourier = $this->importCourierForRow($csvLine[5] ?? '', $users->courier_id ?? 0);
                                $importCourierId = $importCourier ? (int) $importCourier->id : (int) ($users->courier_id ?? 0);
                                $tracking_no = $this->nextWmsPackageTrackingNumber($importCourierId);
                                $packages = new Package();
                                $packages->courier_id = $importCourierId;
                                $packages->mailbox = $mailbox;
                                $packages->user_name = trim(($users->first_name ?? '').' '.($users->last_name ?? ''));
                                $packages->tracking_no = $tracking_no;
                                $packages->courier = $csvLine[5] ?? '';
                                $packages->merchant = $csvLine[3] ?? '';
                                $packages->price = 0;
                                $packages->package_type = 'ANDROID BOX';
                                $packages->date_received = $csvLine[2] ?? null;
                                $packages->manifest_no = $tracking_no;
                                $packages->weight = $csvLine[6] ?? 0;
                                $packages->supplier_tracking_no = $supplierTrackingNo;
                                $packages->csv_package_status = $csvLine[0] ?? '';
                                $packages->status_name = $this->warehouseStatusNameFromText(($csvLine[0] ?? '').' '.($csvLine[5] ?? ''));
                                $packages->freight_type = 'Air Freight';
                                $packages->item_description = $csvLine[8] ?? '';
                                $packages->addedby_id = session('user_id');
                                $packages->addedby_name = trim(session('name').' '.session('last_name'));
                                $packages->created_at = date('Y-m-d H:i:s');
                                $packages->updated_at = date('Y-m-d H:i:s');
                                $packages->save();
                                $createdPackageIds[] = $packages->packeg_id;
                                $usersRes = User::where('mailbox' , $mailbox)->first();
                                $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                                $link = URL::to('/') . '/admin/dashboard';
                                $settings = DB::table('settings')->first();
                                $email_subject = 'Brand New UPDATE from '.$settings->company_name;
                                $settings = DB::table('settings')->first();
                                if($settings){
                                    $email_from = $settings->administrator_email;
                                }else{
                                    $email_from = 'onepluscourierja@gmail.com';
                                }
                                //$status = 'Arrived in Miami';
                                //$this->send_email_packeg($usersRes->email, $user_name,$tracking_no,$csvLine[7],$mailbox,$csvLine[5],'',$csvLine[6],$csvLine[0],$email_subject, $email_from, 'create_packege');
                                $row['status'] = $csvLine[0];
                                $row['name'] = $csvLine[4];
                                $row['user_name'] = $csvLine[4];
                                $row['courier'] = $csvLine[5];
                                $row['merchant'] = $csvLine[3];
                                $row['date_received'] = $csvLine[2];
                                $row['manifest_no'] = $csvLine[1];
                                $row['weight'] = $csvLine[6];
                                $row['supplier_tracking_no'] = $csvLine[7];
                                $row['item_description'] = '';
                                $packageUploadArray[] = $row;
                            }else{
                                $row['name'] = $csvLine[0];
                                $row['user_name'] = $csvLine[4];
                                $row['courier'] = $csvLine[5];
                                $row['date_received'] = $csvLine[2];
                                $row['manifest_no'] = $csvLine[1];
                                $row['weight'] = $csvLine[6];
                                $row['supplier_tracking_no'] = $csvLine[7];
                                $row['item_description'] = '';
                                $packageArray[] = $row;
                            }

                        }else{
                            $row['name'] = $csvLine[0];
                            $row['user_name'] = $csvLine[4];
                            $row['courier'] = $csvLine[5];
                            $row['date_received'] = $csvLine[2];
                            $row['manifest_no'] = $csvLine[1];
                            $row['weight'] = $csvLine[6];
                            $row['supplier_tracking_no'] = $csvLine[7];
                            $row['item_description'] = '';
                            $packageArray[] = $row;
                        }
                    }
                }
                if (is_resource($handle)) {
                    fclose($handle);
                }
                //dd($packageUploadArray);
                $data['uploaded'] = $count;
                $data['packages'] = $packageArray;
                $data['uploaded'] = $packageUploadArray;
                $data['syncStats'] = CourierSaasSync::packages($createdPackageIds, 25, 250);
                //echo '<pre>';print_r($packageArray);exit;
                Session::flash('success_msg', 'Package has been imported successfully');
                return view('admin/packages/import' , $data);

            }else{
                $data['packages'] = [];
                $data['uploaded'] = [];
                $data['syncStats'] = null;
                Session::flash('success_msg', 'Please select a packages file.');
                return view('admin/packages/import' , $data);
            }

        }
    }

    public function downloadImportSample(){
        $rows = [
            ['Status', 'PACKAGE # / HAWB', 'Date', 'Shipper', 'Consignee / NAME', 'Carrier', 'Weight or Volume', 'Tracking Number', 'Description', 'Region', 'Shipment Type'],
            ['Arrived at Pompano Beach Warehouse', 'WMS000001', now()->format('m/d/Y'), 'AMAZON', 'CHANTIQUE GUSCOTT', 'USPS', '1', 'TBA117416196000', 'ANDROID BOX', 'US', 'AIR'],
            ['Received at CN Warehouse', 'WMS000002', now()->format('m/d/Y'), 'TAOBAO', 'CHANTIQUE GUSCOTT', 'China Carrier', '3.5', 'CN117416196000', 'MERCHANDISE', 'CN', 'SEA'],
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'package_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function importCourierForRow($carrierName = '', $fallbackCourierId = 0)
    {
        if ($fallbackCourierId) {
            $courier = Courier::where('id', $fallbackCourierId)->first();
            if ($courier) {
                return $courier;
            }
        }

        $carrierName = trim((string) $carrierName);
        if ($carrierName === '') {
            return null;
        }

        return Courier::where('name', $carrierName)
            ->orWhere('name', 'LIKE', '%'.$carrierName.'%')
            ->first();
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


    public function viewMenifest($coerier_id = ''){
        $data['title'] = 'Create Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Create Manifest';
        $data['couriers'] = Courier::get();
        $data['coerier_id'] = $coerier_id;
        if($coerier_id != 0){
            $data['package_count'] = DB::table('packages')->where('packages.is_added_manifest' ,  0)->where('packages.is_temprary_logs' ,  0)->where('packages.courier_id' ,  $coerier_id)->count();
        }else{
            $data['package_count'] = 0;
        }
        return view("admin.packages.createMani",$data);
    }

    public function listViewMenifest($coerier_id = '')
    {
        if($coerier_id != 'courier'){
            $data = DB::table('packages')
                    ->select('packages.*')
                    //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    //->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.courier_id','!=' ,  0)
                    ->where('packages.is_added_manifest' ,  0)
                    ->where('packages.is_temprary_logs' ,  0)
                    //->where('packages.is_request' ,  0)
                    ->where('packages.courier_id' ,  $coerier_id);
            return DataTables::of($data)
                    ->editColumn('check_status', function ($data) {
                        return '<input name="check_status[]" type="checkbox" value="'.$data->packeg_id.'"><input type="hidden" name="invoice_no[]" value="'.$data->packeg_id.'">';
                    })
                    ->rawColumns(['check_status' => 'check_status','action' => 'action'])
                    ->make(true);
        }else{
            $data = DB::table('packages')
                    //->select('packages.*','users.first_name','users.last_name','users.location','package_status.name')
                    ->select('packages.*')
                    //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    //->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.is_added_manifest' ,  0)
                    ->where('packages.courier_id','!=' ,  0)
                    ->where('packages.is_temprary_logs' ,  0)
                    ->where('packages.courier_id' ,  0);
            return DataTables::of($data)
                    ->editColumn('check_status', function ($data) {
                        return '<input name="check_status[]" type="checkbox" value="'.$data->packeg_id.'"><input type="hidden" name="invoice_no[]" value="'.$data->packeg_id.'">';
                    })
                    ->rawColumns(['check_status' => 'check_status','action' => 'action'])
                    ->make(true);
        }

    }

    public function createMani(Request $request){
        $courier_id = $request->courier_id;
        $data['title'] = 'Create Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Manifest';
        $data['courier_id'] = $courier_id;
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');

            $packeg_id = $request->packeg_id;
            //echo $request->status;exit;
            //dd($packeg_id);
            $check_status = $request->check_status;
            //dd($check_status);exit;
            if($check_status != ''){
                for($i = 0; $i<count($check_status); $i++){
                    $pack_id = $check_status[$i];
                    $packages = Package::where('packeg_id', $pack_id)->first();
                    //dd($packages);
                    $usersRes = User::where('mailbox' , $packages->mailbox)->first();
                    if($usersRes){
                        $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                        $link = URL::to('/') . '/admin/dashboard';
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
                    }
                    
                    //$this->send_email_packeg($usersRes->email, $user_name,$packages->tracking_no,$packages->mailbox,$packages->courier,$packages->item_description,$packages->weight,$status,$email_subject, $email_from, 'update_packege');
                }
            }else{
                Session::flash('success_msg', 'Please select package first.');
                return redirect('admin/list/manifest/courier');
            }

            $package_id_Arr = [];
            for($i = 0; $i<count($check_status); $i++){
                $pack_id = $check_status[$i];
                $package_id_Arr[] = $pack_id;
            }

            $data['packages'] = DB::table('packages')
                                    ->select('packages.*')
                                    //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                    ->whereIn('packages.packeg_id', $package_id_Arr)
                                    ->orderby('packages.created_at' , 'desc')
                                    ->get();
            $data['manifest_info'] = ManifestInfo::first();

            //dd($package_id_Arr);
            $data['packeg_id'] = implode(',' , $package_id_Arr);
            return view('admin/packages/minifist' , $data);

    }

    public function exportExcel($id = ''){
        $data['title'] = 'Manage Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Manage Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['manifest'] = Manifest::get();
        $data['manifest'] = DB::table('manifest_packeges')
                                ->select('packages.*')
                                ->join('packages', 'packages.packeg_id', '=', 'manifest_packeges.packeges_id')
                                //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->where('manifest_packeges.manifest_id' , $id)
                                ->get();
        return view('admin/packages/export_manifest' , $data);
    }

    public function manifest($courier_id = ''){
        $data['title'] = 'Manage Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Manage Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['couriers'] = Courier::get();
        $data['coerier_id'] = $courier_id;
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if($courier_id == 0){
            $data['manifest'] = Manifest::select('manifests.*','couriers.name')->join('couriers', 'couriers.id', '=', 'manifests.courier_id')->orderby('manifests.created_at' , 'DESC')->get();
        }else{
            $data['manifest'] = Manifest::select('manifests.*','couriers.name')->join('couriers', 'couriers.id', '=', 'manifests.courier_id')->where('manifests.courier_id' , $courier_id)->orderby('manifests.created_at' , 'DESC')->get();
        }
        
        return view('admin/packages/list_manifest' , $data);
    }



    public function updateStatus(Request $request){
        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';
        $packeg_id = $request->packeg_id;
        $check_status = $request->check_status;
        if($check_status != null){
            for($i = 0; $i<count($check_status); $i++){
                $pack_id = $check_status[$i];
                Package::where('packeg_id', $pack_id)
                          ->update([
                            'status' => $request->status
                        ]);
                //exit;
                $packages = Package::where('packeg_id', $pack_id)->first();
                $usersRes = User::where('mailbox' , $packages->mailbox)->first();
                //dd($usersRes);
                $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                $link = URL::to('/') . '/admin/dashboard';
                $settings = DB::table('settings')->first();
                $email_subject = 'Brand New UPDATE from '.$settings->company_name;
                $settings = DB::table('settings')->first();
                if($settings){
                    $email_from = $settings->from_email;
                }
                $name = PackageStatus::where('id' , $packages->status)->first()->name;
                $status = $name;
                if (filter_var($usersRes->email, FILTER_VALIDATE_EMAIL)) {
                    $settings = DB::table('settings')->first();
                    $logo = URL::to('/') . '/settings/'.$settings->site_logo;
                    $company_name = $settings->company_name;
                   // $this->send_email_packeg_update($usersRes->email, $user_name,$packages->tracking_no,$packages->supplier_tracking_no,$packages->mailbox,$packages->courier,$packages->item_description,$packages->weight,$status,$email_subject, $email_from,$logo,$company_name, 'update_packege');
                }
            }
        }else{
            Session::flash('success_msg', 'Please select package first.');
            return redirect('admin/status/packeges/update');
        }

        if($request->status == 7 || $request->isMethod('get')){
            $package_id_Arr = [];
            for($i = 0; $i<count($check_status); $i++){
                $pack_id = $check_status[$i];
                $package_id_Arr[] = $pack_id;
            }
            $data['packages'] = Package::whereIn('packeg_id', $package_id_Arr)->get();
            $data['manifest_info'] = ManifestInfo::first();

            //dd($package_id_Arr);
            $data['packeg_id'] = implode(',' , $package_id_Arr);
            return view('admin/packages/minifist' , $data);
        }else{
            if($request->status == 12){
                Session::flash('success_msg', 'Package status has been updated successfully.');
                return redirect('admin/category/packages/12');
            }else{
                Session::flash('success_msg', 'Package status has been updated successfully.');
                return redirect('admin/status/packeges/update');
            }

        }

    }

    public function addPackage(Request $request , $pack_id = ''){
        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';

        Package::where('packeg_id', $pack_id)
                      ->update([
                        'is_temprary_logs' => 0
                    ]);
        return redirect('admin/packeges/prealerts');
    }

    public function updateDiliveryOption(Request $request){
        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';


        Package::where('tracking_no', $request->tracking_no)
                      ->update([
                        'delivery' => $request->delivery,
                        'dilivery_description' => $request->description
                    ]);
        return redirect('user/invoice/upload/'.$request->tracking_no);
    }

    public function createManifest(Request $request){
        
        $data['title'] = 'Create Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Create Package Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
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
                return redirect('admin/manifest/create')
                        ->withInput()
                        ->withErrors($validator);
        }else{
            $packagesIdArr = [];
            
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
                //view('pdfinvoice' , $data);
                $fileName = time().'_manifest.pdf';
                $pathPDF = base_path() . '/public/pdf/'.$fileName;
                $baseURL = URL::to('/');
                //$expUrl = explode('/index.php' , $baseURL);
                $url = $baseURL . '/public/pdf/'.$fileName;
                $pdf = PDF::loadView('pdfinvoice' , $data)->save($pathPDF);
                $manifest = new Manifest();
                $manifest->courier_id = $request->courier_id;
                $manifest->master_air_billing_no = $request->master_air_billing_no;
                $manifest->file_pdf = $fileName;
                $manifest->addedby_id = session('user_id');
                $manifest->addedby_name = session('name').' '.session('last_name');
                $manifest->created_at = date('Y-m-d H:i:s');
                $manifest->updated_at = date('Y-m-d H:i:s');
                $manifest->save();
                $manifest_id = $manifest->id;
                $packege_ids = explode(',' ,$request->packege_ids);
                for($i = 0; $i<count($packege_ids); $i++){
                    $manifArray = [];
                    $manifArray['manifest_id'] = $manifest_id;
                    $manifArray['packeges_id'] = $packege_ids[$i];
                    Package::where('packeg_id', $packege_ids[$i])->update(['is_added_manifest' => 1,'status' => 2,'status_name' => 'IN TRANSIT']);
                    if($packege_ids[$i]){
                        DB::table('manifest_packeges')->insert($manifArray);
                    }
                }
                $couriers = Courier::where('id' , $request->courier_id)->first();
                $packeges_ids = explode(',' ,$request->packege_ids);
                $packages = DB::table('packages')->select('supplier_tracking_no')->whereIn('packeg_id', $packeges_ids)->get();
                if(count($packages)>0){
                    foreach($packages as $pk){
                        $packagesIdArr[] = $pk->supplier_tracking_no;
                    }
                }
                $packagesIds = implode(',' , $packagesIdArr);
                $this->createPackageManifest($couriers->base_url,$packagesIds,$request->master_air_billing_no);
                if($couriers->base_url == 'https://ssscourierja.com'){
                    $status = 15;
                    $status_name = 'Package In Transit';
                }elseif($couriers->base_url == 'https://jampackcouriers.com/tracking'){
                    $status = 15;
                    $status_name = 'IN TRANSIT';
                }else{
                    $status = 2;
                    $status_name = 'In Transit from Miami';
                }
                $this->updatePackageStatus($couriers->base_url,$packagesIds,$status,$status_name);
                //Add Activity
                $checkPkg = '';
                $type = 'Manifest';
                $dateTime = date('Y-m-d H:i:s');
                $message = 'Manifest created.';
                $admin = session('user_id');
                $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$request->courier_id);
                return redirect('admin/package/manifest/0');
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
                $manInfo->created_at = date('Y-m-d H:i:s');
                $manInfo->updated_at = date('Y-m-d H:i:s');
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
                $manifest->courier_id = $request->courier_id;
                $manifest->master_air_billing_no = $request->master_air_billing_no;
                $manifest->file_pdf = $fileName;
                $manifest->addedby_id = session('user_id');
                $manifest->addedby_name = session('name').' '.session('last_name');
                $manifest->created_at = date('Y-m-d h:i:s');
                $manifest->updated_at = date('Y-m-d h:i:s');
                $manifest->save();
                $manifest_id = $manifest->id;
                $packege_ids = explode(',' ,$request->packege_ids);
                for($i = 0; $i<count($packege_ids); $i++){
                    $manifArray = [];
                    $manifArray['manifest_id'] = $manifest_id;
                    $manifArray['packeges_id'] = $packege_ids[$i];
                    Package::where('packeg_id', $packege_ids[$i])->update(['is_added_manifest' => 1,'status' => 2,'status_name' => 'IN TRANSIT']);
                    if($packege_ids[$i]){
                        DB::table('manifest_packeges')->insert($manifArray);
                    }
                }
                $couriers = Courier::where('id' , $request->courier_id)->first();
                $packeges_ids = explode(',' ,$request->packege_ids);
                $packages = DB::table('packages')->select('supplier_tracking_no')->whereIn('packeg_id', $packeges_ids)->get();
                if(count($packages)>0){
                    foreach($packages as $pk){
                        $packagesIdArr[] = $pk->supplier_tracking_no;
                    }
                }
                $packagesIds = implode(',' , $packagesIdArr);
                $this->createPackageManifest($couriers->base_url,$packagesIds,$request->master_air_billing_no);
                if($couriers->base_url == 'https://ssscourierja.com'){
                    $status = 15;
                    $status_name = 'Package In Transit';
                }elseif($couriers->base_url == 'https://jampackcouriers.com/tracking'){
                    $status = 15;
                    $status_name = 'IN TRANSIT';
                }else{
                    $status = 2;
                    $status_name = 'In Transit from Miami';
                }
                $this->updatePackageStatus($couriers->base_url,$packagesIds,$status,$status_name);
                //Add Activity
                $checkPkg = '';
                $type = 'Manifest';
                $dateTime = date('Y-m-d H:i:s');
                $message = 'Manifest created.';
                $admin = session('user_id');
                $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$request->courier_id);
                Session::flash('success_msg', 'Package has been added successfully');
                return redirect('admin/package/manifest/0');
            }

        }
    }
    
    public function createPackageManifest($base_url,$packege_id_new,$master_air_billing_no){
        $curl = curl_init();
        $url = $base_url.'/api/createPackageManifest';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"package_ids":"'.$packege_id_new.'","master_air_billing_no":"'.$master_air_billing_no.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function updatePackageStatus($base_url,$packege_id_new,$status,$status_name){
        $curl = curl_init();
        $url = $base_url.'/api/updatePackageStatus';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"package_ids":"'.$packege_id_new.'","status":"'.$status.'","status_name":"'.$status_name.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function editManifest(Request $request , $id = ''){
        $data['title'] = 'Update Package Manifest';
        $data['class'] = 'package';
        $data['table'] = 'Update Package Manifest';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $data['manifest_info'] = ManifestInfo::first();
            $data['packages'] = DB::table('manifest_packeges')
                                ->select('packages.*')
                                ->join('packages', 'packages.packeg_id', '=', 'manifest_packeges.packeges_id')
                                //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->where('manifest_packeges.manifest_id' , $id)
                                ->get();
            $data['manifest_id'] = $id;
            return view('admin/packages/minifist_edit' , $data);
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
                return redirect('admin/manifest/edit/'.$id)
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
                Manifest::where('id',$id)->update(['master_air_billing_no' => $request->master_air_billing_no,'addedby_id' => session('user_id'),'addedby_name' => session('name').' '.session('last_name')]);
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
                $manifest = Manifest::where('id',$id)->first();
                //Add Activity
                $checkPkg = '';
                $type = 'Manifest';
                $dateTime = date('Y-m-d H:i:s');
                $message = 'Manifest edited.';
                $admin = session('user_id');
                $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$manifest->courier_id);
                Session::flash('success_msg', 'Manifest has been updated successfully.');
                return redirect('admin/package/manifest/0');
            }
        }

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

        //return response()->view( 'admin/packages/minifist_export', $data )->header( 'Content-Type', 'text/xml' );
        
        $content = response()->view( 'admin/packages/minifist_export',$data );
        return response($content)
            ->header('Content-type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="manifest.txt"');
    }

    public function deleteManifest($packeges_id = '') {
	$manifest_id = DB::table('manifest_packeges')->where('packeges_id' , $packeges_id)->first()->manifest_id;
        $delete = DB::table('manifest_packeges')->where('packeges_id' , $packeges_id)->delete();
        Session::flash('del_msg', 'Packege has been deleted from Manifest successfully.');
        return redirect('admin/manifest/edit/'.$manifest_id);
    }

    public function printInvoice($invoice_id = ''){

		$data['title'] = 'Manage Invoices';
		$data['class'] = 'invoices';
		$data['table'] = 'Manage Invoices';
		$user_id = Session::get('user_id');
		$data['users'] = User::where('user_id' , $user_id)->first();
		$invoice = Invoice::where('invoice_id' , $invoice_id)->first();
		$client_id = $invoice->client_id;
		$currency_id = $invoice->currency_id;
		$client = Client::where('client_id' , $client_id)->first();
		$data['client'] = $client;
		$country_id = $client->country_id;
		$state_id = $client->state_id;
		$city_id = $client->city_id;
		$data['countries'] = Country::where('id' , $country_id)->first();
		$data['states'] = State::where('id' , $state_id)->first();
		$data['cities'] = City::where('id' , $city_id)->first();
		$data['invoice'] = $invoice;
		if($invoice->tex_type == 1){
			$tex = $invoice->SGST_tex + $invoice->CGST_tex;
			$total_tex = $invoice->total_amount * $tex / 100;
			$amount = $invoice->total_amount + $total_tex;
			$data['amountInWord'] = $this->getAmountInWords($amount);
		}else{
			$tex = $invoice->IGST_tex;
			$total_tex = $invoice->total_amount * $tex / 100;
			$amount = $invoice->total_amount + $total_tex;
			$data['amountInWord'] = $this->getAmountInWords($amount);
		}
		$data['currencies'] = Currency::where('currency_id' ,$currency_id)->first();
		//return view('pdfinvoice' , $data);
		$pdf = PDF::loadView('pdfinvoice' , $data);
		return $pdf->download(time().'_invoice.pdf');
	}

    public function getAmountInWords($amount){
		$number = $amount;
		$no = round($number);
		$point = round($number - $no, 2) * 100;
		$hundred = null;
		$digits_1 = strlen($no);
		$i = 0;
		$str = array();
		$words = array('0' => '', '1' => 'one', '2' => 'two',
				'3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
				'7' => 'seven', '8' => 'eight', '9' => 'nine',
				'10' => 'ten', '11' => 'eleven', '12' => 'twelve',
				'13' => 'thirteen', '14' => 'fourteen',
				'15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
				'18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
				'30' => 'thirty', '40' => 'forty', '50' => 'fifty',
				'60' => 'sixty', '70' => 'seventy',
				'80' => 'eighty', '90' => 'ninety');
		$digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
		while ($i < $digits_1) {
			$divider = ($i == 2) ? 10 : 100;
			$number = floor($no % $divider);
			$no = floor($no / $divider);
			$i += ($divider == 10) ? 1 : 2;
			if ($number) {
			$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
			$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
			$str [] = ($number < 21) ? $words[$number] .
			" " . $digits[$counter] . $plural . " " . $hundred
			:
			$words[floor($number / 10) * 10]
			. " " . $words[$number % 10] . " "
			. $digits[$counter] . $plural . " " . $hundred;
			} else $str[] = null;
		}
		$str = array_reverse($str);
		$result = implode('', $str);
		$points = ($point) ?
		"." . $words[$point / 10] . " " .
		$words[$point = $point % 10] : '';
		return  $result . "Rupees";
	}

    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    private function nextWmsPackageTrackingNumber(int $courierId): string
    {
        static $trackingNumbers = [];

        if($courierId <= 0){
            return $this->nextUnknownPackageTrackingNumber();
        }

        if($courierId > 0 && empty($trackingNumbers[$courierId])){
            $trackingNumbers[$courierId] = CourierSaasSync::nextPackageTrackingNumbers($courierId, 5000);
        }

        $tracking = array_shift($trackingNumbers[$courierId]);

        return $tracking ?: $this->generate_password(13);
    }

    private function nextUnknownPackageTrackingNumber(): string
    {
        $prefix = 'UNK100';
        $lastTracking = Package::where('tracking_no', 'like', $prefix.'%')
            ->orWhere('manifest_no', 'like', $prefix.'%')
            ->orderByDesc('packeg_id')
            ->value('tracking_no');
        $lastNumber = 0;

        if($lastTracking && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $lastTracking, $matches)){
            $lastNumber = (int) $matches[1];
        }

        do {
            $next = $prefix.str_pad((string) (++$lastNumber), 6, '0', STR_PAD_LEFT);
        } while (Package::where('tracking_no', $next)->orWhere('manifest_no', $next)->exists());

        return $next;
    }

    private function trackingNumberForReassignment(?Package $package, int $newCourierId): string
    {
        $current = strtoupper(trim((string) optional($package)->tracking_no));

        if($newCourierId > 0 && str_starts_with($current, 'UNK100')){
            return $this->nextWmsPackageTrackingNumber($newCourierId);
        }

        if($newCourierId <= 0 && $current === ''){
            return $this->nextUnknownPackageTrackingNumber();
        }

        return $current ?: $this->nextWmsPackageTrackingNumber($newCourierId);
    }

    public function add(Request $request){
            $data['title'] = 'Add Pre-Alert';
            $data['class'] = 'account';
            $data['table'] = 'Add Pre-Alert';
            $role_id= Session::get('role_id');
            $user_id = Session::get('user_id');
            $mailbox = User::where('user_id' , $user_id)->first()->mailbox;
            //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
            $data['getModues'] = '';
            if ($request->isMethod('get')) {
                $data['mailbox'] = $mailbox;
                 $data['types'] = PackageType::orderBy('id', 'DESC')->get();
                $data['users'] = User::where('role_id' , 0)->get();
                return view('admin/packages/addpackage' , $data);
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
                    $package_type = explode('-' , $request->package_type);
                    $tracking_no = $this->generate_password(13);
                    $packages = DB::table('packages')->orderBy('manifest_no','DESC')->first();

                    if($packages){
                        $manifest_no = $packages->manifest_no + 1;
                    }else{
                        $manifest_no = '10000';
                    }
                    $packages = new Package();
                    $packages->tracking_no = $tracking_no;
                    $packages->nick_name = $request->nick_name;
                    $packages->merchant = $request->merchant;
                    //$packages->permotion_code = $request->permotion_code;
                    //$packages->EEI = $request->EEI;

                    $packages->mailbox = $request->mailbox;
                    $packages->courier = $request->courier;
                    $supplierTrackingNo = $this->normalizeTrackingNumber($request->supplier_tracking_no);
                    if($this->duplicatePackageExists($supplierTrackingNo, 0)){
                        Session::flash('error_msg', 'Package already exists with this supplier tracking number.');
                        return redirect('user/packege/add')->withInput();
                    }

                    $packages->supplier_tracking_no = $supplierTrackingNo;
                    $packages->package_type = $package_type[1];
                    $packages->package_code = $package_type[0];
                    $packages->weight = $request->weight;
                    $packages->price = $request->price;
                    $packages->custom_fee = $request->custom_fee;
                    $packages->manifest_no = $manifest_no;
                    $packages->invoice = $name;
                    $packages->is_temprary_logs = 1;
                    $packages->status = 9;
                    $packages->item_description = $request->item_description;
                    $packages->created_at = date('Y-m-d H:i:s');
                    $packages->updated_at = date('Y-m-d H:i:s');
                    $packages->save();
                    CourierSaasSync::package($packages->fresh());
                    $usersRes = User::where('mailbox' , $request->mailbox)->first();
                    $user_name = $usersRes->first_name.' '.$usersRes->last_name;
                    $link = URL::to('/') . '/admin/dashboard';
                    $settings = DB::table('settings')->first();
                    $email_subject = 'Brand New UPDATE from '.$settings->company_name;
                    $settings = DB::table('settings')->first();
                    if($settings){
                        $email_from = $settings->from_email;
                    }
                    $status = 'Arrived in Miami';
                   // $this->send_email_packeg($usersRes->email, $user_name,$tracking_no,$request->supplier_tracking_no,$request->mailbox,$request->courier,$request->item_description,$request->weight,$status,$email_subject, $email_from, 'create_packege');
                    Session::flash('success_msg', 'Package has been added successfully');
                    return redirect('user/packeges/current');
                }
            }
	}

    public function send_email_packeg($email,$courier_name, $user_name,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$item_description,$weight, $status,$email_subject, $email_from,$view_name) {
        $settings = DB::table('settings')->first();
        $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
        $company_name = $settings->company_name;
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
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name,$company_name, $email_subject,$courier_name) {
            $message->from($email_from, $name = $courier_name);
            $message->to($email, $user_name)->subject($email_subject);
            //$message->to('rizwangill132@gmail.com', $user_name)->subject($email_subject);
            //$message->to('shopvmjbb@gmail.com', $user_name)->subject($email_subject);
        });
    }

    public function send_email_packegold($email, $user_name,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$item_description,$weight, $status,$email_subject, $email_from,$logo,$company_name,$view_name) {
        $subject = $email_subject;
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ".$company_name." <".$email_from.">\n";
        $message = '<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <title>Email</title>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
                            <style>
                                body{
                                    overflow-x: hidden;
                                }
                                @media (max-width:1180px) {

                                }

                                @media (max-width: 767.98px) {

                                }
                                @media (max-width:812px) {
                            .email-bg{
                                       width: 90%  !important;
                                   }
                        }
                        .main_content ul {
                            width: 80% !important;
                        }
                                @media (max-width: 600px) {
                                    .main_content ul {
                            width: 96% !important;
                        }
                                   .email-bg{
                                       width: 90%  !important;
                                       padding: 10px !important;
                                   }
                                }
                                     @media (max-width:480px){
                                    .main_content ul {
                            width: 98% !important;
                            padding: 6px !important;
                            font-size: 14px;
                        }
                        .email_content p {
                            font-size: 16px !important;
                        }
                        .email-bg{
                                       width: 93%  !important;
                                       padding: 10px !important;
                                   }
                                     }
                                @media (max-width:380px){
                                  .small_text{
                                      font-size: 16px  !important;
                                  }
                                  .email_content p {
                            font-size: 16px !important;
                        }
                        .big_head {
                            font-size: 13px !important;
                            width: 25% !important;
                        }
                                }
                                @media (max-width:380px){
                                  .small_text{
                                      font-size: 12px  !important;
                                  }
                                }
                            </style>

                        </head>

                        <body style="background-color: #f1f1f1; font-family: "Lato", sans-serif; overflow-x: hidden; margin: 0;">
                            <main>
                                <!----- EMAIL-SECTOION-START ------>
                                <section class="email-bg"
                                    style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
                                    <div class="container">
                                        <header style="border-bottom: 1px solid #ccc;">
                                            <img class="logo" src="'.$logo.'" alt="logo" style=" width:20%;">
                                        </header>
                                        <div class="email_content" style="
                                        width: 100%;
                                    ">
                                            <h4 style="font-size: 24px;margin-bottom:0;">Brand New UPDATE from '.$company_name.'</h4>
                                            <p style="font-size: 20px;">Dear '.$user_name.',</p>
                                            <p style="font-size: 20px;">We have received the following package at our Miami warehouse. your
                                                package details are listed below</p>

                                                <div class="main_content" style="border-bottom: 1px solid #ccc;">
                                                    <ul style="list-style: none;width: 50%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                                        <li class="big_head"style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373; vertical-align: middle;">
                                                            Supplier Tracking #
                                                        </li>
                                                        <li class="small_text" style="display: inline-block; width: 68%; padding: 0 0 10px 0; vertical-align: middle;">'.$supplier_tracking_no.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;">
                                                            Account no
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$mailbox.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Supplier
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$courier.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Weight
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$weight.' lbs</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Description
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$item_description.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Status
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$status.'</li>
                                                    </ul>
                                                </div>
                                            <p style="font-size: 20px;">You will be notified of your charges as soon as your package arrives in
                                                Jamaica.</p>
                                            <p style="font-size: 20px;">To ensure smooth process kindly update a copy of the invoice(s) of your
                                                purchas(s) provided by the selier</p>
                                            <p style="font-size: 20px;">This procedire will avoid any delays at Customs, upon arrival of your
                                                package.</p>

                                        </div>
                                        <footer style="font-size: 14px; border-top: 1px solid #ccc;">
                                            <h4 style=" font-size: 13px;color: #8c8c8c;">Thanks for your support Enjoy your shopping experience.
                                            </h4>
                                            <p style=" font-size: 13px;color: #8c8c8c;">Best Wishes</p>
                                            <p style=" font-size: 13px;color: #8c8c8c;">'.$company_name.' Client Services</p>
                                        </footer>
                                    </div>
                                </section>
                                <!----- EMAIL-SECTOION-END ------>

                            </main>
                        </body>
                        </html>';
            $user_email = $email;
          //  $user_email = 'rizwangill132@gmail.com';
            mail($user_email,$subject,$message,$headers);
    }

    public function send_email_packeg_update($email, $user_name,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$item_description,$weight, $status,$email_subject, $email_from,$logo,$company_name,$view_name) {
        $subject = $email_subject;
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ".$company_name." <".$email_from.">\n";
        $message = '<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <title>Email</title>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
                            <style>
                                body{
                                    overflow-x: hidden;
                                }
                                @media (max-width:1180px) {

                                }

                                @media (max-width: 767.98px) {

                                }
                                @media (max-width:812px) {
                            .email-bg{
                                       width: 90%  !important;
                                   }
                        }
                        .main_content ul {
                            width: 80% !important;
                        }
                                @media (max-width: 600px) {
                                    .main_content ul {
                            width: 96% !important;
                        }
                                   .email-bg{
                                       width: 90%  !important;
                                       padding: 10px !important;
                                   }
                                }
                                     @media (max-width:480px){
                                    .main_content ul {
                            width: 98% !important;
                            padding: 6px !important;
                            font-size: 14px;
                        }
                        .email_content p {
                            font-size: 16px !important;
                        }
                        .email-bg{
                                       width: 93%  !important;
                                       padding: 10px !important;
                                   }
                                     }
                                @media (max-width:380px){
                                  .small_text{
                                      font-size: 16px  !important;
                                  }
                                  .email_content p {
                            font-size: 16px !important;
                        }
                        .big_head {
                            font-size: 13px !important;
                            width: 25% !important;
                        }
                                }
                                @media (max-width:380px){
                                  .small_text{
                                      font-size: 12px  !important;
                                  }
                                }
                            </style>

                        </head>

                        <body style="background-color: #f1f1f1; font-family: "Lato", sans-serif; overflow-x: hidden; margin: 0;">
                            <main>
                                <!----- EMAIL-SECTOION-START ------>
                                <section class="email-bg"
                                    style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
                                    <div class="container">
                                        <header style="border-bottom: 1px solid #ccc;">
                                            <img class="logo" src="'.$logo.'" alt="logo" style=" width:20%;">
                                        </header>
                                        <div class="email_content" style="
                                        width: 100%;
                                    ">
                                            <h4 style="font-size: 24px;margin-bottom:0;">Brand New UPDATE from '.$company_name.'</h4>
                                            <p style="font-size: 20px;">Dear '.$user_name.',</p>
                                            <p style="font-size: 20px;">Please see your package details listed below:</p>

                                                <div class="main_content" style="border-bottom: 1px solid #ccc;">
                                                    <ul style="list-style: none;width: 50%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                                        <li class="big_head"style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373; vertical-align: middle;">
                                                            Supplier Tracking #
                                                        </li>
                                                        <li class="small_text" style="display: inline-block; width: 68%; padding: 0 0 10px 0; vertical-align: middle;">'.$supplier_tracking_no.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;">
                                                            Account no
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$mailbox.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Supplier
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$courier.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Weight
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$weight.' lbs</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Description
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$item_description.'</li>
                                                        <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                            Status
                                                        </li>
                                                        <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;font-size: 22px;font-weight: 800;">'.$status.'</li>
                                                    </ul>
                                                </div>

                                        </div>
                                        <footer style="font-size: 14px; border-top: 1px solid #ccc;">
                                            <h4 style=" font-size: 13px;color: #8c8c8c;">Thanks for your support Enjoy your shopping experience.
                                            </h4>
                                            <p style=" font-size: 13px;color: #8c8c8c;">Best Wishes</p>
                                            <p style=" font-size: 13px;color: #8c8c8c;">'.$company_name.' Client Services</p>
                                        </footer>
                                    </div>
                                </section>
                                <!----- EMAIL-SECTOION-END ------>

                            </main>
                        </body>
                        </html>';
            $user_email = $email;
          //  $user_email = 'rizwangill132@gmail.com';
            mail($user_email,$subject,$message,$headers);
    }

    public function packeges($id = ''){
        $Date = now()->subDays(100)->toDateString();
    
        $trackingNos = DB::table('invoice_packages')
            ->join('invoices', 'invoices.invoice_id', '=', 'invoice_packages.invoice_id')
            ->where('invoices.courier_id', $id)
            ->whereDate('invoices.created_at', '>', $Date) 
            ->pluck('tracking_no')
            ->toArray();
    
        if (!empty($trackingNos)) {
            $allTrackingNos = [];
            foreach ($trackingNos as $tracking_no) {
                $decoded = json_decode($tracking_no, true);
                if (is_array($decoded)) {
                    $allTrackingNos = array_merge($allTrackingNos, $decoded);
                }
            }
    
            if (!empty($allTrackingNos)) {
                Package::whereIn('tracking_no', $allTrackingNos)->update(['is_invoice' => 1]);
            }
        }
    
        // Optimize package retrieval (Use pagination)
        $packages = Package::where('courier_id', $id)
                        ->where('is_invoice', 0)
                        ->where('status', 2)
                        ->orderByDesc('created_at')
                        ->paginate(5000); // Load only 50 at a time
    
        return view('admin/packages/ajaxpackeage', [
            'packagesInv' => '',
            'packeges' => $packages
        ]);
    }
    
    public function weight($id = '', $courier = ''){
        //return $courier;
        if($id == 'all'){
            $checkWight = DB::table('invice_temp')->delete();
            $array1 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight','<=',10)->where('packages.status' , 2)->get();
            if(count($array1) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.35';
                }
                $packages10 = Arr::pluck($array1, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($packages10),'courier_id' => $courier,'price' => $price,'weight' => 10,'description' => 'packages 0-10','qty' => count($array1)]);
            }
            $array2 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 20)->where('packages.status' , 2)->get();
            if(count($array2) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 11-20')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.70';
                }
                $lesthen20 = Arr::pluck($array2, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen20),'courier_id' => $courier,'price' => $price,'weight' => 20,'description' => 'packages 11-20','qty' => count($array2)]);
            }
            $array3 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 20)->where('weight' ,'<=' , 50)->where('packages.status' , 2)->get();
            if(count($array3) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.20';
                }
                $lesthen50 = Arr::pluck($array3, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen50),'courier_id' => $courier,'price' => $price,'weight' => 50,'description' => 'packages 21-50','qty' => count($array3)]);
            }
            $array4 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 50)->where('weight' ,'<=' , 75)->where('packages.status' , 2)->get();
            if(count($array4) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.80';
                }
                $lesthen75 = Arr::pluck($array4, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages 51-75','qty' => count($array4)]);
            }
            $array5 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 75)->where('packages.status' , 2)->get();
            if(count($array5) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '6.00';
                }
                $greterthen75 = Arr::pluck($array5, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($greterthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages > 76','qty' => count($array5)]);
            }
            $packagesInv = DB::table('invice_temp')->where('courier_id' , $courier)->get();
            //dd($invoice_temp);
            if(count($packagesInv) > 0){
                foreach($packagesInv as $p_inv){
                    $tracking_no = json_decode($p_inv->tracking_no,true);
                    if(count($tracking_no) > 0){
                       // dd($tracking_no);
                        Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 1]);
                    }
                }
                
            }
            $data['packagesInv'] = $packagesInv;
            $data['packeges'] = [];
        }else{
            $trackingNoArr = [];
            $packages = Package::where('tracking_no' , $id)->first();
            Package::where('tracking_no' , $id)->update(['is_invoice' => 1]);
            //dd($packages);
            $weight = $packages->weight;
            if ($weight <= 10) {
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 0)->where('weight' ,'<=' , 10)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo = json_decode($checkWight->tracking_no,true);
                    $trackingArray1[]= $packages->tracking_no;
                    $trackingArr = array_merge($trackingNo,$trackingArray1);
                    $trackingArr = array_values($trackingArr);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr)]);
                }else{
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.35';
                    }
                    $trackingArray1 = [];
                    $trackingArray1[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray1),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 0-10','qty' => 1]);
                }
            }elseif($weight > 10 && $weight <= 20){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 20)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo2 = json_decode($checkWight->tracking_no,true);
                    $trackingArray2[]= $packages->tracking_no;
                    $trackingArr2 = array_merge($trackingNo2,$trackingArray2);
                    $trackingArr2 = array_values($trackingArr2);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr2)]);
                }else{
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 11-20')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.70';
                    }
                    $trackingArray2 = [];
                    $trackingArray2[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray2),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 11-20','qty' => 1]);
                }
            }elseif($weight > 20 && $weight <= 50){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 20)->where('weight' ,'<=' , 50)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo3 = json_decode($checkWight->tracking_no,true);
                    $trackingArray3[]= $packages->tracking_no;
                    $trackingArr3 = array_merge($trackingNo3,$trackingArray3);
                    $trackingArr3 = array_values($trackingArr3);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr3)]);
                }else{
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '3.20';
                    }
                    $trackingArray3 = [];
                    $trackingArray3[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray3),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 21-50','qty' => 1]);
                }
            }elseif($weight > 50 && $weight <= 75){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 50)->where('weight' ,'<=' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo4 = json_decode($checkWight->tracking_no,true);
                    $trackingArray4[]= $packages->tracking_no;
                    $trackingArr4 = array_merge($trackingNo4,$trackingArray4);
                    $trackingArr4 = array_values($trackingArr4);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr4)]);
                }else{
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '3.80';
                    }
                    $trackingArray4 = [];
                    $trackingArray4[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray4),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 51-75','qty' => 1]);
                }
            }elseif($weight > 75){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo5 = json_decode($checkWight->tracking_no,true);
                    $trackingArray5[]= $packages->tracking_no;
                    $trackingArr5 = array_merge($trackingNo5,$trackingArray5);
                    $trackingArr5 = array_values($trackingArr5);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr5)]);
                }else{
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '6.00';
                    }
                    $trackingArray5 = [];
                    $trackingArray5[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray5),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages > 76','qty' => 1]);
                }
            }
            //$data['package'] = $packages;
            //$price = DB::table('weights')->where('weight' , $packages->weight)->first()->price;
           // DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'courier_id' => $packages->courier_id,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $packages->courier_id)->get();
            //dd($data['package']);
            $packegesInvice = Package::where('courier_id' , $packages->courier_id)->where('is_invoice' , 0)->where('packages.status' , 2)->orderBy('created_at','DESC')->get();
            if(count($packegesInvice) > 0){
                $packagesArr = [];
                foreach($packegesInvice as $row){
                        $packagesArr[] = $row;
                }
                if(count($packagesArr) > 0){
                    $data['packeges'] = $packagesArr;
                }else{
                    $data['packeges'] = $packagesArr;
                }
            }
        }
    
        return view('admin/packages/ajaxpackeage' , $data);
    }
    
    public function weightNew($id = '', $courier = '')
    {
        if ($id == 'all') {
            DB::table('invice_temp')->delete();
    
            // Packages 1-10kg
            $array1 = DB::table('packages')
                ->select('tracking_no')
                ->where('courier_id', $courier)
                ->where('is_invoice', 0)
                ->where('weight', '<=', 10)
                ->where('packages.status', '!=', 9)
                ->get();
    
            if ($array1->isNotEmpty()) {
                $checkWeight = WeightNew::where('courier_id', $courier)->where('weight', 'packages 1-10')->first();
                $price = $checkWeight ? $checkWeight->price : '1.35';
                
                $trackingNumbers = Arr::pluck($array1, 'tracking_no');
                DB::table('invice_temp')->insert([
                    'mailbox' => 0,
                    'tracking_no' => json_encode($trackingNumbers),
                    'courier_id' => $courier,
                    'price' => $price,
                    'weight' => 10,
                    'description' => 'packages 0-10lbs',
                    'qty' => count($array1),
                ]);
            }
    
            // Packages 11-20kg ✅ FIXED RANGE
            $array2 = DB::table('packages')
                ->select('tracking_no')
                ->where('courier_id', $courier)
                ->where('is_invoice', 0)
                ->where('weight', '>', 10)
                ->where('weight', '<=', 20) // Fixed: Now correctly matches "11-20"
                ->where('packages.status', '!=', 9)
                ->get();
    
            if ($array2->isNotEmpty()) {
                $checkWeight = WeightNew::where('courier_id', $courier)->where('weight', 'packages 11-20')->first();
                $price = $checkWeight ? $checkWeight->price : '1.70';
                
                $trackingNumbers = Arr::pluck($array2, 'tracking_no');
                DB::table('invice_temp')->insert([
                    'mailbox' => 0,
                    'tracking_no' => json_encode($trackingNumbers),
                    'courier_id' => $courier,
                    'price' => $price,
                    'weight' => 20,
                    'description' => 'packages 11-20lbs', // Fixed description
                    'qty' => count($array2),
                ]);
            }
    
            // Packages 21-50kg ✅ FIXED RANGE
            $array3 = DB::table('packages')
                ->select('tracking_no')
                ->where('courier_id', $courier)
                ->where('is_invoice', 0)
                ->where('weight', '>', 20)
                ->where('weight', '<=', 50) // Fixed: Now correctly matches "21-50"
                ->where('packages.status', '!=', 9)
                ->get();
    
            if ($array3->isNotEmpty()) {
                $checkWeight = WeightNew::where('courier_id', $courier)->where('weight', 'packages 21-50')->first();
                $price = $checkWeight ? $checkWeight->price : '3.20';
                
                $trackingNumbers = Arr::pluck($array3, 'tracking_no');
                DB::table('invice_temp')->insert([
                    'mailbox' => 0,
                    'tracking_no' => json_encode($trackingNumbers),
                    'courier_id' => $courier,
                    'price' => $price,
                    'weight' => 50,
                    'description' => 'packages 21-50lbs', // Fixed description
                    'qty' => count($array3),
                ]);
            }
    
            // Packages 51-75kg
            $array4 = DB::table('packages')
                ->select('tracking_no')
                ->where('courier_id', $courier)
                ->where('is_invoice', 0)
                ->where('weight', '>', 50)
                ->where('weight', '<=', 75)
                ->where('packages.status', '!=', 9)
                ->get();
    
            if ($array4->isNotEmpty()) {
                $checkWeight = WeightNew::where('courier_id', $courier)->where('weight', 'packages 51-75')->first();
                $price = $checkWeight ? $checkWeight->price : '3.80';
                
                $trackingNumbers = Arr::pluck($array4, 'tracking_no');
                DB::table('invice_temp')->insert([
                    'mailbox' => 0,
                    'tracking_no' => json_encode($trackingNumbers),
                    'courier_id' => $courier,
                    'price' => $price,
                    'weight' => 75,
                    'description' => 'packages 51-75lbs',
                    'qty' => count($array4),
                ]);
            }
    
            // Packages above 75kg
            $array5 = DB::table('packages')
                ->select('tracking_no')
                ->where('courier_id', $courier)
                ->where('is_invoice', 0)
                ->where('weight', '>', 75)
                ->where('packages.status', '!=', 9)
                ->get();
    
            if ($array5->isNotEmpty()) {
                $checkWeight = WeightNew::where('courier_id', $courier)->where('weight', 'packages > 76')->first();
                $price = $checkWeight ? $checkWeight->price : '6.00';
                
                $trackingNumbers = Arr::pluck($array5, 'tracking_no');
                DB::table('invice_temp')->insert([
                    'mailbox' => 0,
                    'tracking_no' => json_encode($trackingNumbers),
                    'courier_id' => $courier,
                    'price' => $price,
                    'weight' => 76, // Representing weights above 75
                    'description' => 'packages 76+ lbs',
                    'qty' => count($array5),
                ]);
            }
    
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id', $courier)->get();
            $data['packeges'] = [];
    
        } else {
            // Handling for single package update
            $packages = Package::where('tracking_no', $id)->first();
            if ($packages) {
                Package::where('tracking_no', $id)->update(['is_invoice' => 1]);
            }
        }
    
        return view('admin/packages/ajaxpackeage', $data);
    }


    public function weightOld($id = '', $courier = ''){
        if($id == 'all'){
            $checkWight = DB::table('invice_temp')->delete();
            //Weight from 1 to 10
            $array1 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight','<=',10)->where('packages.status' , '!=' , 9)->get();
            if(count($array1) > 0){
                $packages10 = Arr::pluck($array1, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($packages10),'courier_id' => $courier,'price' => 1.35,'weight' => 10,'description' => 'packages 0-10','qty' => count($array1)]);
            }
            //Weight from 11 to 25
            $array2 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 25)->where('packages.status' , '!=' , 9)->get();
            if(count($array2) > 0){
                $lesthen25 = Arr::pluck($array2, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen25),'courier_id' => $courier,'price' => 1.70,'weight' => 25,'description' => 'packages 11-20','qty' => count($array2)]);
            }
            //Weight from 26 to 51
            $array3 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 25)->where('weight' ,'<=' , 51)->where('packages.status' , '!=' , 9)->get();
            if(count($array3) > 0){
                $lesthen51 = Arr::pluck($array3, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen51),'courier_id' => $courier,'price' => 3.20,'weight' => 51,'description' => 'packages 20-50','qty' => count($array3)]);
            }
            //Weight from 51 to 75
            $array4 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 51)->where('weight' ,'<=' , 75)->where('packages.status' , '!=' , 9)->get();
            if(count($array4) > 0){
                $lesthen75 = Arr::pluck($array4, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($lesthen75),'courier_id' => $courier,'price' => 3.80,'weight' => 75,'description' => 'packages 50-75','qty' => count($array4)]);
            }
            //Weight above 75
            $array5 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 75)->where('packages.status' , '!=' , 9)->get();
            if(count($array5) > 0){
                $greterthen75 = Arr::pluck($array5, 'tracking_no');
                DB::table('invice_temp')->insert(['mailbox' => 0,'tracking_no' => json_encode($greterthen75),'courier_id' => $courier,'price' => 6.00,'weight' => 75,'description' => 'packages > 76','qty' => count($array5)]);
            }
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $courier)->get();
            $data['packeges'] = [];
        }else{
            $trackingNoArr = [];
            $packages = Package::where('tracking_no' , $id)->first();
            Package::where('tracking_no' , $id)->update(['is_invoice' => 1]);
            //dd($packages);
            $weight = $packages->weight;
            if ($weight <= 10) {
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 0)->where('weight' ,'<=' , 10)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo = json_decode($checkWight->tracking_no,true);
                    $trackingArray1[]= $packages->tracking_no;
                    $trackingArr = array_merge($trackingNo,$trackingArray1);
                    $trackingArr = array_values($trackingArr);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr)]);
                }else{
                    $trackingArray1 = [];
                    $trackingArray1[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray1),'courier_id' => $courier,'price' => 1.35,'weight' => $weight,'description' => 'packages 0-10','qty' => 1]);
                }
            }elseif($weight > 10 && $weight <= 20){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 20)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo2 = json_decode($checkWight->tracking_no,true);
                    $trackingArray2[]= $packages->tracking_no;
                    $trackingArr2 = array_merge($trackingNo2,$trackingArray2);
                    $trackingArr2 = array_values($trackingArr2);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr2)]);
                }else{
                    $trackingArray2 = [];
                    $trackingArray2[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray2),'courier_id' => $courier,'price' => 1.70,'weight' => $weight,'description' => 'packages 11-20','qty' => 1]);
                }
            }elseif($weight > 21 && $weight <= 50){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 21)->where('weight' ,'<=' , 50)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo3 = json_decode($checkWight->tracking_no,true);
                    $trackingArray3[]= $packages->tracking_no;
                    $trackingArr3 = array_merge($trackingNo3,$trackingArray3);
                    $trackingArr3 = array_values($trackingArr3);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr3)]);
                }else{
                    $trackingArray3 = [];
                    $trackingArray3[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray3),'courier_id' => $courier,'price' => 3.20,'weight' => $weight,'description' => 'packages 20-50','qty' => 1]);
                }
            }elseif($weight > 51 && $weight <= 75){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 51)->where('weight' ,'<=' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo4 = json_decode($checkWight->tracking_no,true);
                    $trackingArray4[]= $packages->tracking_no;
                    $trackingArr4 = array_merge($trackingNo4,$trackingArray4);
                    $trackingArr4 = array_values($trackingArr4);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr4)]);
                }else{
                    $trackingArray4 = [];
                    $trackingArray4[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray4),'courier_id' => $courier,'price' => 3.80,'weight' => $weight,'description' => 'packages 51-75','qty' => 1]);
                }
            }elseif($weight > 75){
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo5 = json_decode($checkWight->tracking_no,true);
                    $trackingArray5[]= $packages->tracking_no;
                    $trackingArr5 = array_merge($trackingNo5,$trackingArray5);
                    $trackingArr5 = array_values($trackingArr5);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr5)]);
                }else{
                    $trackingArray5 = [];
                    $trackingArray5[]= $packages->tracking_no;
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => json_encode($trackingArray5),'courier_id' => $courier,'price' => 6.00,'weight' => $weight,'description' => 'packages 51-75','qty' => 1]);
                }
            }
            //$data['package'] = $packages;
            //$price = DB::table('weights')->where('weight' , $packages->weight)->first()->price;
           // DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'courier_id' => $packages->courier_id,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $packages->courier_id)->get();
            //dd($data['package']);
            $packegesInvice = Package::where('courier_id' , $packages->courier_id)->where('is_invoice' , 0)->where('packages.status' , '!=' , 9)->orderBy('created_at','DESC')->get();
            $data['packeges'] = $packegesInvice;
            // if(count($packegesInvice) > 0){
            //     $packagesArr = [];
            //     foreach($packegesInvice as $row){
            //             $packagesArr[] = $row;
            //     }
            //     if(count($packagesArr) > 0){
            //         $data['packeges'] = $packegesInvice;
            //     }else{
            //         $data['packeges'] = $packagesArr;
            //     }
            // }
        }

        return view('admin/packages/ajaxpackeage' , $data);
    }
    
    public function weightEdit($id = '', $inv_id = ''){
        $invoice = Invoice::where('invoice_id' , $inv_id)->first();
        $courier = $invoice->courier_id;
        $data['invoice'] = $invoice;
        if($id == 'all'){
            $checkWight = DB::table('invice_temp')->delete();
            //Check 1-10
            $array11 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 0-10')->first();
            $array1 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight','<=',10)->where('packages.status' , '!=' , 9)->get();
            
            if(count($array1) > 0){
                if($array11){
                    $packages101 = json_decode($array11->tracking_no);
                    $packages10 = Arr::pluck($array1, 'tracking_no');
                    $fpackages10 = array_merge($packages101,$packages10);
                }else{
                    $fpackages10 = Arr::pluck($array1, 'tracking_no');
                }
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.35';
                }
                DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages10),'courier_id' => $courier,'price' => $price,'weight' => 10,'description' => 'packages 0-10','qty' => count($fpackages10)]);
            }else{
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.35';
                }
                if($array11){
                    $packages101 = json_decode($array11->tracking_no);
                    DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($packages101),'courier_id' => $courier,'price' => $price,'weight' => 10,'description' => 'packages 0-10','qty' => count($packages101)]);
                }
                
            }
            //Check 21-50
            $array22 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 11-20')->first();
            $array2 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 25)->where('packages.status' , '!=' , 9)->get();
            if(count($array2) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 11-20')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.70';
                }
                if($array22){
                    $packages102 = json_decode($array22->tracking_no);
                    $lesthen25 = Arr::pluck($array2, 'tracking_no');
                    $flesthen25 = array_merge($packages102,$lesthen25);
                }else{
                    $flesthen25 = Arr::pluck($array2, 'tracking_no');
                }
                DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen25),'courier_id' => $courier,'price' => $price,'weight' => 25,'description' => 'packages 11-20','qty' => count($flesthen25)]);
            }else{
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 11-20')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '1.70';
                }
                if($array22){
                    $flesthen25 = json_decode($array22->tracking_no);
                    DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen25),'courier_id' => $courier,'price' => $price,'weight' => 25,'description' => 'packages 11-20','qty' => count($flesthen25)]);
                }
            }
            //Check 21-50
            $array33 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 21-50')->first();
            $array3 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 25)->where('weight' ,'<=' , 51)->where('packages.status' , '!=' , 9)->get();
            if(count($array3) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.20';
                }
                if($array33){
                    $packages103 = json_decode($array33->tracking_no);
                    $lesthen51 = Arr::pluck($array3, 'tracking_no');
                    $flesthen51 = array_merge($packages103,$lesthen51);
                }else{
                    $flesthen51 = Arr::pluck($array3, 'tracking_no');
                }
                DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen51),'courier_id' => $courier,'price' => $price,'weight' => 51,'description' => 'packages 21-50','qty' => count($flesthen51)]);
            }else{
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.20';
                }
                if($array33){
                    $flesthen51 = json_decode($array33->tracking_no);
                    DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen51),'courier_id' => $courier,'price' => $price,'weight' => 51,'description' => 'packages 21-50','qty' => count($flesthen51)]);
                }    
            }
            //Check 51-75
            $array44 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 51-75')->first();
            $array4 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 51)->where('weight' ,'<=' , 75)->where('packages.status' , '!=' , 9)->get();
            if(count($array4) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.80';
                }
                if($array44){
                    $packages104 = json_decode($array44->tracking_no);
                    $lesthen75 = Arr::pluck($array4, 'tracking_no');
                    $flesthen75 = array_merge($packages104,$lesthen75);
                }else{
                    $flesthen75 = Arr::pluck($array4, 'tracking_no');
                }
                DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages 51-75','qty' => count($flesthen75)]);
            }else{
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '3.80';
                }
                if($array44){
                    $flesthen75 = json_decode($array44->tracking_no);
                    DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($flesthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages 51-75','qty' => count($flesthen75)]);
                }
            }
            //Check > 76
            $array55 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages > 76')->first();
            $array5 = DB::table('packages')->select('tracking_no')->where('courier_id' , $courier)->where('is_invoice' , 0)->whereDate('created_at' , '>' , '2022-07-09')->where('weight' ,'>' , 75)->where('packages.status' , '!=' , 9)->get();
            if(count($array5) > 0){
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '6.00';
                }
                if($array55){
                    $packages105 = json_decode($array55->tracking_no);
                    $greterthen75 = Arr::pluck($array5, 'tracking_no');
                    $fgreterthen75 = array_merge($packages105,$greterthen75);
                }else{
                    $fgreterthen75 = Arr::pluck($array5, 'tracking_no');
                }
                DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($fgreterthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages > 76','qty' => count($fgreterthen75)]);
            }else{
                $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                if($checkWeight){
                    $price = $checkWeight->price;
                }else{
                    $price = '6.00';
                }
                if($array55){
                    $fgreterthen75 = json_decode($array55->tracking_no);
                    DB::table('invice_temp')->insert(['mailbox' => 0,'invoice_id' => $inv_id,'tracking_no' => json_encode($fgreterthen75),'courier_id' => $courier,'price' => $price,'weight' => 75,'description' => 'packages > 76','qty' => count($fgreterthen75)]);
                }
            }
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $courier)->get();
            $data['packeges'] = [];
        }else{
            $trackingNoArr = [];
            $packages = Package::where('tracking_no' , $id)->first();
            Package::where('tracking_no' , $id)->update(['is_invoice' => 1]);
            //dd($packages);
            $weight = $packages->weight;
            if ($weight <= 10) {
                $array11 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 0-10')->first();
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 0)->where('weight' ,'<=' , 10)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo = json_decode($checkWight->tracking_no,true);
                    $trackingArray1[]= $packages->tracking_no;
                    $trackingArr = array_merge($trackingNo,$trackingArray1);
                    $trackingArr = array_values($trackingArr);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr)]);
                }else{
                    $trackingArray1 = [];
                    $trackingArray1[]= $packages->tracking_no;
                    if($array11){
                        $packages101 = json_decode($array11->tracking_no);
                        $fpackages10 = array_merge($packages101,$trackingArray1);
                    }else{
                        $fpackages10 = $trackingArray1;
                    }
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.35';
                    }
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages10),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 0-10','qty' => count($fpackages10)]);
                }
            }else{
                $array11 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 0-10')->first();
                if($array11){
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 1-10')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.35';
                    }
                    $fpackages10 = json_decode($array11->tracking_no);
                    $invice_temp = DB::table('invice_temp')->where('description' , 'packages 0-10')->first();
                    if($invice_temp == null){
                        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages10),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 0-10','qty' => count($fpackages10)]);
                    }
                        
                }
            }
            if($weight > 10 && $weight <= 20){
                $array22 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 21-50')->first();
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 10)->where('weight' ,'<=' , 20)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo2 = json_decode($checkWight->tracking_no,true);
                    $trackingArray2[]= $packages->tracking_no;
                    $trackingArr2 = array_merge($trackingNo2,$trackingArray2);
                    $trackingArr2 = array_values($trackingArr2);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr2)]);
                }else{
                    $trackingArray2 = [];
                    $trackingArray2[]= $packages->tracking_no;
                    if($array22){
                        $packages102 = json_decode($array22->tracking_no);
                        $fpackages25 = array_merge($packages102,$trackingArray2);
                    }else{
                        $fpackages25 = $trackingArray2;
                    }
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.70';
                    }
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages25),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 21-50','qty' => count($fpackages25)]);
                }
            }else{
                $array22 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 21-50')->first();
                if($array22){
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 21-50')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '1.70';
                    }
                    $fpackages25 = json_decode($array22->tracking_no);
                    $invice_temp = DB::table('invice_temp')->where('description' , 'packages 21-50')->first();
                    if($invice_temp == null){
                        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages25),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 21-50','qty' => count($fpackages25)]);
                    }
                    
                }
            }
            if($weight > 21 && $weight <= 50){
                $array33 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 26-51')->first();
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 21)->where('weight' ,'<=' , 50)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo3 = json_decode($checkWight->tracking_no,true);
                    $trackingArray3[]= $packages->tracking_no;
                    $trackingArr3 = array_merge($trackingNo3,$trackingArray3);
                    $trackingArr3 = array_values($trackingArr3);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr3)]);
                }else{
                    $trackingArray3 = [];
                    $trackingArray3[]= $packages->tracking_no;
                    if($array33){
                        $packages103 = json_decode($array33->tracking_no);
                        $fpackages51 = array_merge($packages103,$trackingArray3);
                    }else{
                        $fpackages51 = $trackingArray3;
                    }
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 26-51')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '3.20';
                    }
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages51),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 26-51','qty' => count($fpackages51)]);
                }
            }else{
                $array33 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 26-51')->first();
                if($array33){
                    $fpackages51 = json_decode($array33->tracking_no);
                    $invice_temp = DB::table('invice_temp')->where('description' , 'packages 26-51')->first();
                    if($invice_temp == null){
                        $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 26-51')->first();
                        if($checkWeight){
                            $price = $checkWeight->price;
                        }else{
                            $price = '3.20';
                        }
                        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages51),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 26-51','qty' => count($fpackages51)]);
                    }   
                }
            }
            if($weight > 51 && $weight <= 75){
                $array44 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 51-75')->first();
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 51)->where('weight' ,'<=' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo4 = json_decode($checkWight->tracking_no,true);
                    $trackingArray4[]= $packages->tracking_no;
                    $trackingArr4 = array_merge($trackingNo4,$trackingArray4);
                    $trackingArr4 = array_values($trackingArr4);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr4)]);
                }else{
                    $trackingArray4 = [];
                    $trackingArray4[]= $packages->tracking_no;
                    if($array44){
                        $packages104 = json_decode($array44->tracking_no);
                        $fpackages75 = array_merge($packages104,$trackingArray4);
                    }else{
                        $fpackages75 = $trackingArray4;
                    }
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '3.80';
                    }
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages75),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 51-75','qty' => count($fpackages75)]);
                }
            }else{
                $array44 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages 51-75')->first();
                if($array44){
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages 51-75')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '3.80';
                    }
                    $fpackages75 = json_decode($array44->tracking_no);
                    $invice_temp = DB::table('invice_temp')->where('description' , 'packages 51-75')->first();
                    if($invice_temp == null){
                        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages75),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages 51-75','qty' => count($fpackages75)]);
                    }
                }
            }
            if($weight > 75){
                $array55 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages > 76')->first();
                $checkWight = DB::table('invice_temp')->where('weight' ,'>' , 75)->where('courier_id' , $courier)->first();
                if($checkWight){
                    $trackingNo5 = json_decode($checkWight->tracking_no,true);
                    $trackingArray5[]= $packages->tracking_no;
                    $trackingArr5 = array_merge($trackingNo5,$trackingArray5);
                    $trackingArr5 = array_values($trackingArr5);
                    $qty = $checkWight->qty + 1;
                    DB::table('invice_temp')->where('id' , $checkWight->id)->update(['qty' => $qty,'tracking_no' => json_encode($trackingArr5)]);
                }else{
                    $trackingArray5 = [];
                    $trackingArray5[]= $packages->tracking_no;
                    if($array55){
                        $packages105 = json_decode($array55->tracking_no);
                        $fpackages76 = array_merge($packages105,$trackingArray5);
                    }else{
                        $fpackages76 = $trackingArray5;
                    }
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '6.00';
                    }
                    DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages76),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages > 76','qty' => count($fpackages76)]);
                }
            }else{
                $array55 = DB::table('invoice_packages')->select('tracking_no')->where('invoice_id' , $inv_id)->where('package_description' , 'packages > 76')->first();
                if($array55){
                    $checkWeight = WeightNew::where('courier_id',$courier)->where('weight' , 'packages > 76')->first();
                    if($checkWeight){
                        $price = $checkWeight->price;
                    }else{
                        $price = '6.00';
                    }
                    $fpackages76 = json_decode($array55->tracking_no);
                    $invice_temp = DB::table('invice_temp')->where('description' , 'packages 51-75')->first();
                    if($invice_temp == null){
                        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'invoice_id' => $inv_id,'tracking_no' => json_encode($fpackages76),'courier_id' => $courier,'price' => $price,'weight' => $weight,'description' => 'packages > 76','qty' => count($fpackages76)]);
                    }  
                }
            }
            //$data['package'] = $packages;
            //$price = DB::table('weights')->where('weight' , $packages->weight)->first()->price;
           // DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'courier_id' => $packages->courier_id,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $packages->courier_id)->get();
            //dd($data['package']);
            $packegesInvice = Package::where('courier_id' , $packages->courier_id)->where('is_invoice' , 0)->where('packages.status' , '!=' , 9)->orderBy('created_at','DESC')->get();
            if(count($packegesInvice) > 0){
                $packagesArr = [];
                foreach($packegesInvice as $row){
                        $packagesArr[] = $row;
                }
                if(count($packagesArr) > 0){
                    $data['packeges'] = $packagesArr;
                }else{
                    $data['packeges'] = $packagesArr;
                }
            }
        }
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $inv_id)->get();
        $data['invoice_id'] = $inv_id;
        return view('admin/packages/ajaxEditpackeage' , $data);
    }

    public function weightEditOld($id = '',$inv_id = ''){

        $packages = Package::where('tracking_no' , $id)->first();
        //dd($packages);
        $weight = $packages->weight;
        $price = $this->priceForWeight($weight, $packages->price ?? 0);

        //$data['package'] = $packages;
        //$price = DB::table('weights')->where('weight' , $packages->weight)->first()->price;
        DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'courier_id' => $packages->courier_id,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
        $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $packages->courier_id)->get();
        //dd($data['package']);
        $packegesInvice = Package::where('courier_id' , $packages->courier_id)->where('packages.status' , '!=' , 9)->orderBy('created_at','DESC')->get();
        if(count($packegesInvice) > 0){
            $packagesArr = [];
            foreach($packegesInvice as $row){
                $invoices = DB::table('invice_temp')->where('tracking_no' , $row->tracking_no)->first();
                if($invoices == null){
                    $invoicesPack = DB::table('invoice_packages')->where('tracking_no' , $row->tracking_no)->first();
                    if($invoicesPack == null){
                        $packagesArr[] = $row;
                    }
                }
            }
            if(count($packagesArr) > 0){
                $data['packeges'] = $packagesArr;
            }else{
                $data['packeges'] = $packagesArr;
            }
            $data['invoice_packages'] = DB::table('invoice_packages')->where('invoice_id' , $inv_id)->get();
            $invoice = Invoice::where('invoice_id' , $inv_id)->first();
            $data['invoice'] = $invoice;
            $data['service_type'] = DB::table('service_type')->where('invoice_id' , $inv_id)->get();
            $data['invoice_id'] = $inv_id;
        }
        return view('admin/packages/ajaxEditpackeage' , $data);
    }
    public function viewMenifestList($coerier_id = ''){
        $data['title'] = 'Create Manifest';
        $data['class'] = 'manifest';
        $data['table'] = 'Create Manifest';
        $data['couriers'] = Courier::get();
        $data['coerier_id'] = $coerier_id;
        
        return view("admin.packages.list_manifest",$data);
    }
    public function listViewPackageMenifest($courier_id = '')
    {
        if($courier_id == '0'){
            $data = Manifest::select('manifests.*','couriers.name')
                        ->join('couriers', 'couriers.id', '=', 'manifests.courier_id')
                        ->orderby('manifests.created_at' , 'DESC');
        }else{
            $data = Manifest::select('manifests.*','couriers.name')
                        ->join('couriers', 'couriers.id', '=', 'manifests.courier_id')
                        ->where('manifests.courier_id' , $courier_id)
                        ->orderby('manifests.created_at' , 'DESC');
        }
            
        return DataTables::of($data)
                ->editColumn('check_status', function ($data) {
                    return '<input name="check_status[]" type="checkbox" value="'.$data->packeg_id.'"><input type="hidden" name="invoice_no[]" value="'.$data->packeg_id.'">';
                })
                ->addColumn('no_of_packages', function ($data) {
                    return DB::table('manifest_packeges')->where('manifest_id' , $data->id)->count();
                })
                ->rawColumns(['check_status' => 'check_status','action' => 'action','no_of_packages'=>'no_of_packages'])
                ->rawColumns(['check_status' => 'check_status','action' => 'action'])
                ->addColumn('action', function($row){
                    return "<a href='".url('/')."/admin/manifest/edit/".$row->id."'><button type='button' class='btn btn-info'><i class='fa fa-edit'></i> Edit</button></a><a href='".url('/')."/admin/package/export/excel/".$row->id."' target='_blank'><button type='button' class='btn btn-warning'><i class='fa fa fa-code'></i> Export To Excel </button></a><a href='".url('/')."/admin/manifest/export/".$row->id."' target='_blank'><button type='button' class='btn btn-success'><i class='fa fa-code'></i> Export XML </button></a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$row->id."'>Delete</a>";
                })->make(true);
        
    }
    
    public function deleteManifestNew(Request $request){
        $Manifest = Manifest::where('id' , $request->id)->first();
        //dd($Manifest);
        if($Manifest){
            DB::table('manifest_packeges')->where('manifest_id' , $request->id)->delete();
            Manifest::where('id' , $request->id)->delete();
        }
        return response()->json(['status' => true, 'message' => 'Manifest deleted successfully.']);
    }
    
    public function deletePackage($id = ''){
        //return $id;
        $invTemp = DB::table('invice_temp')->where('id' , $id)->first();
        $invoice_temp = DB::table('invice_temp')->where('id' , $id)->first();
        //dd($invoice_temp);
        if($invoice_temp){
            $tracking_no = json_decode($invoice_temp->tracking_no,true);
            //dd($tracking_no);
            if(count($tracking_no) > 0){
                // foreach($tracking_no as $key => $val){
                //     Package::where('tracking_no' , $val)->update(['is_invoice' => 0]);
                // }
                Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 0]);
            }
        }
        DB::table('invice_temp')->where('id' , $id)->delete();
        $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $invTemp->courier_id)->get();
        //dd($data['package']);
        $packegesInvice = Package::where('courier_id' , $invTemp->courier_id)->where('is_invoice' , 0)->where('packages.status' , 2)->orderBy('created_at','DESC')->get();
        $data['packeges'] = $packegesInvice;
        //dd($data['packeges']);
        
        //$data['service_type'] = DB::table('service_type')->where('invoice_id' , $invID)->get();
        return view('admin/packages/ajaxpackeage' , $data);
    }

    public function deletePackageEdit($id = '',$invID = ''){
        //return $invID;
        $invoicesPack = DB::table('invoice_packages')->where('id' , $id)->first();
        $courier_id = $invoicesPack->courier_id;
        $invID = $invoicesPack->invoice_id;
        //dd($invoicesPack);
        if($invoicesPack){
            $tracking_no = json_decode($invoicesPack->tracking_no,true);
            if(count($tracking_no) > 0){
                // foreach($tracking_no as $key => $val){
                //     Package::where('tracking_no' , $val)->update(['is_invoice' => 0]);
                // }
                Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 1]);
            }
        }
        $invoicesPack = DB::table('invoice_packages')->where('id' , $id)->delete();
        $invoice = Invoice::where('invoice_id' , $invID)->first();
        $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $courier_id)->get();
        //dd($data['package']);
        $packegesInvice = Package::where('courier_id' , $courier_id)->where('is_invoice' , 0)->where('packages.status' , '!=' , 9)->orderBy('created_at','DESC')->get();
        if(count($packegesInvice) > 0){
            $packagesArr = [];
            foreach($packegesInvice as $row){
                $packagesArr[] = $row;
            }
            if(count($packagesArr) > 0){
                $data['packeges'] = $packagesArr;
            }else{
                $data['packeges'] = $packagesArr;
            }
        }
        
        $data['invoice'] = $invoice;
        $data['invoice_packages'] = DB::table('invoice_packages')->where('invoice_id' , $invID)->get();
        $data['invoice_id'] = $invID;
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invID)->get();
        return view('admin/packages/ajaxEditpackeage' , $data);
    }

  public function delete()
{
    try {
        $packeges_id = request()->id;
        
        \Log::info('Attempting to delete package ID: ' . $packeges_id);
        
        $package = Package::where('packeg_id', $packeges_id)->first();

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found.'
            ]);
        }

        // Store package data for activity log before deletion
        $supplier_tracking_no = $package->supplier_tracking_no;
        $courier_id = $package->courier_id;
        $saasDeleted = CourierSaasSync::deletePackage($package);

        // If no courier linked, delete locally only
        if (empty($package->courier_id) || $package->courier_id == 0 || $package->courier_id == '') {

            // Delete from manifest_packeges first
            DB::table('manifest_packeges')
                ->where('packeges_id', $packeges_id)
                ->delete();

            // Delete the package
            $package->delete();

            // Activity Log
            commonHelper::addActivity(
                'Package',
                now(),
                'Package deleted with Supplier Tracking Number: <b>' . $supplier_tracking_no . '</b>',
                session('user_id'),
                null,
                null
            );

            return response()->json([
                'status' => true,
                'message' => 'Package deleted successfully (Local Only).'
            ]);
        }

        // Get courier
        $courier = Courier::where('id', $courier_id)->first();

        if (!$courier) {
            return response()->json([
                'status' => false,
                'message' => 'Courier not found.'
            ]);
        }

        // Call API FIRST to delete from remote system
        $apiResponse = commonHelper::deletePackage(
            $courier->base_url,
            $courier_id,
            $supplier_tracking_no // Using supplier_tracking_no
        );

        // Parse API response
        $apiResult = json_decode($apiResponse, true);
        
        \Log::info('API Delete Result', ['result' => $apiResult]);

        // Check if API call was successful
        $apiSuccess = false;
        if ($apiResult && isset($apiResult['status']) && $apiResult['status'] == true) {
            $apiSuccess = true;
        } elseif ($apiResult && isset($apiResult['status']) && $apiResult['status'] == 1) {
            $apiSuccess = true;
        } elseif (strpos($apiResponse, 'success') !== false || strpos($apiResponse, 'Success') !== false) {
            $apiSuccess = true;
        }

        // Delete from manifest_packeges
        DB::table('manifest_packeges')
            ->where('packeges_id', $packeges_id)
            ->delete();

        // Delete local package
        $package->delete();

        // Activity Log
        commonHelper::addActivity(
            'Package',
            now(),
            'Package deleted with Supplier Tracking Number: <b>' . $supplier_tracking_no . '</b>',
            session('user_id'),
            $apiResponse,
            $courier_id
        );

        $message = ($apiSuccess && $saasDeleted) ? 'Package deleted successfully from WMS, courier API, and SaaS.' 
                               : 'Package deleted locally, but one remote deletion may have failed. Check logs.';

        return response()->json([
            'status' => true,
            'message' => $message,
            'api_response' => $apiResult,
            'saas_deleted' => $saasDeleted
        ]);

    } catch (\Exception $e) {
        \Log::error('Delete package error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'status' => false,
            'message' => 'Error deleting package: ' . $e->getMessage()
        ], 500);
    }
}

        public function markInTransit(Request $request) {
         $dd=  Package::where('courier_id', 0)->get();
         //  dd(count($dd));
            try {
                Package::where('courier_id', 0)->update(['status' => 2]);
        
                return response()->json([
                    'status' => true,
                    'message' => 'All unknown packages marked as In Transit successfully.'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error updating packages: ' . $e->getMessage()
                ]);
            }
        }

    /**
     * Get package type codes (HS 6 digit)
     *
     * @return \Illuminate\Http\Response
     */
    public function types()
    {
        $s = isset( $_GET[ 'q' ] )? $_GET[ 'q' ] : null;
        $results = HSCode::orderBy( 'description' )
            ->orWhere( 'description', 'like', '%' . $s . '%' )
            ->get();
        foreach ( $results as $result )
            $data[ 'items' ][] = [ 'id' => $result->code, 'text' => $result->description ];
        return response()->json( $data, 200 );
    }
    public function requestView(){
        $data['title'] = 'Manage Package Request';
        $data['class'] = 'package';
        $data['table'] = 'Manage Package Request';

        return view("admin/packages.request",$data);
    }

    public function requestListView()
    {
        $courier_id = Session::get('courier_id');
        $data = DB::table('package_requests')
                    ->join('packages','packages.tracking_no','=','package_requests.tracking_no')
                    ->select('packages.*')
                    ->where('packages.is_request' , 1)
                    ->where('package_requests.status' , 0)
                    ->get();
        return DataTables::of($data)
        ->addColumn('action', function($data){
                return "<a href='".url('/')."/admin/request/packages/1/".$data->tracking_no."'  class='all_button btn  btn-sm btn-success'  id='".$data->tracking_no."'>Accept Package</a><a href='".url('/')."/admin/request/packages/0/".$data->tracking_no."'  class='all_button btn  btn-sm btn-danger'  id='".$data->tracking_no."'>Reject Package</a>";
        })
        ->make(true);
    }
    public function updatePackageRequest($status = '', $id = ''){
        $checkPkg = '';
        //return $id;
        if($status == 0){
            $packageReq = PackageRequest::where('tracking_no' , $id)->first();
            PackageRequest::where('tracking_no' , $id)->delete();
            Package::where('tracking_no' , $id)->update(['is_request' => 0,'user_name' => '','mailbox'=>0,'courier_id' =>0]);
            
            //Add Activity
            $type = 'Request Rejected';
            $dateTime = date('Y-m-d H:i:s');
            $message = 'Package request rejected for Tracking Number:<b>'.$id.'</b>';
            $admin = $admin = session('user_id');;
            $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$packageReq->courier_id); 
            Session::flash('success_msg', 'Package has been rejected successfully');
            return redirect('admin/package/request');
        }else{
            $packageReq = PackageRequest::where('tracking_no' , $id)->first();
            if(! $packageReq){
                Session::flash('error_msg', 'Package request not found.');
                return redirect('admin/package/request');
            }
            //PackageRequest::where('tracking_no' , $id)->update(['status' => 1]);
            $package = Package::where('tracking_no' , $id)->first();
            if(! $package){
                Session::flash('error_msg', 'Package not found.');
                return redirect('admin/package/request');
            }
            $nextTrackingNo = $this->trackingNumberForReassignment($package, (int) $packageReq->courier_id);
            $requestUser = User::where('courier_id', $packageReq->courier_id)->where('mailbox', $packageReq->mailbox)->first();
            $requestCourier = Courier::where('id', $packageReq->courier_id)->first();
            $acceptUpdate = [
                'tracking_no' => $nextTrackingNo,
                'manifest_no' => $nextTrackingNo,
                'is_request' => 0,
                'courier_id' => $packageReq->courier_id,
                'mailbox' => $packageReq->mailbox,
                'user_name' => trim((string) (($requestUser->first_name ?? '').' '.($requestUser->last_name ?? ''))),
                'courier' => $requestCourier->name ?? $package->courier,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (Schema::hasColumn('packages', 'routing_status')) {
                $acceptUpdate['routing_status'] = 'VERIFIED';
            }
            if (Schema::hasColumn('packages', 'entry_mode')) {
                $acceptUpdate['entry_mode'] = 'STANDARD';
            }
            if (Schema::hasColumn('packages', 'unknown_reason')) {
                $acceptUpdate['unknown_reason'] = null;
            }

            Package::where('tracking_no' , $id)->update($acceptUpdate);
            $package = Package::where('tracking_no', $nextTrackingNo)->first();
            if($package){
                CourierSaasSync::package($package);
            }
            //Add Activity
            $type = 'Request Rejected';
            $dateTime = date('Y-m-d H:i:s');
            $message = 'Package request accepted for Tracking Number:<b>'.$id.'</b>';
            $admin = $admin = session('user_id');
            $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$packageReq->courier_id); 
            Session::flash('success_msg', 'Package request has been accepted successfully');
            //return redirect('admin/package/request');
            return redirect('admin/package/edit/'.$nextTrackingNo);
        }
    }
    
    public function checkTrackingNum($supplier_tracking_no = '',$courier_id = ''){
        $supplier_tracking_no = $this->normalizeTrackingNumber($supplier_tracking_no);
        if($courier_id == 0){
            if ($this->duplicatePackageExists($supplier_tracking_no, 0)) {
                return response()->json(['status'=>"failed"],200);
            }else{
                return response()->json(['status'=>"success"],200);
            }
        }else{
            //$package = Package::where('supplier_tracking_no' , $supplier_tracking_no)->where('courier_id' , $courier_id)->first();
            if ($this->duplicatePackageExists($supplier_tracking_no, (int) $courier_id)) {
                return response()->json(['status'=>"failed"],200);
            }else{
                if($courier_id != 0){
                    $couriers = Courier::where('id' , $courier_id)->first();
                    if($couriers){
                        $checkPkg = $this->checkPackage($couriers->base_url,$courier_id,'',$supplier_tracking_no);
                        $responce = json_decode($checkPkg,true);
                        //dd($responce);
                        //return $responce['status'];
                        if(is_array($responce) && array_key_exists('status', $responce) && $responce['status'] == false){
                            return response()->json(['status'=>"failed"],200);
                        }
                    }
                    //dd($checkPkg);
                }else{
                    return response()->json(['status'=>"success"],200);
                }

                return response()->json(['status'=>"success"],200);
            }
        }
        
    }
    
    public function GetPackagesTypes($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetPackagesTypes';
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            return  $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
    
    public function GetVendors($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetVendors';

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                    'api_key: Barrier yS94T5O1gbI4G0pUQuiLRYUJeGhfDM'
              ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
    
    public function GetCategories($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetCategories';
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                'api_key: Barrier yS94T5O1gbI4G0pUQuiLRYUJeGhfDM'
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            return  $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
    
    public function generateUniqueNumber($number, $name){
        
        if(Str::length($number) > 4){
            return $name.sprintf('%'.Str::length($number).'d', $number);//Add four digits number to string and return value
        }else{
            return $name.sprintf('%04d', $number);//Add four digits number to string and return value
        }
        
    }
    
    public function resendPackage() {
        $packeges_id = request()->id;
        $package = Package::where('packeg_id' , $packeges_id)->first();
        $couriers = Courier::where('id' , $package->courier_id)->first();
        $weight = round($package->weight);
        $price = $this->priceForWeight($weight, $package->price ?? 0);
        //dd($couriers);
        //return $couriers->id;
        if($couriers->id == 9 || $couriers->id == 19){
            $courier = $package->vendor_id;
            $package_type = $package->type_id;
            $package_code = $package->category_id;
            $price = $package->price;
        }else{
            $courier = $package->courier;
            $package_type = $package->package_type;
            $package_code = $package->package_code;
        }
        //return $courier;
        if($couriers->id == 12){
            $checkPkg = $this->createPackage($couriers->base_url,$couriers->id,$package->supplier_tracking_no,$package->tracking_no,$package->mailbox,$courier,$weight,$price,$package_type,$package_code,$package->item_description,$package->manifest_no);
            //dd($checkPkg);
        }else{
            $checkPkg = $this->resendPackageApi($couriers->base_url,$couriers->id,$package->tracking_no,$package->supplier_tracking_no,$package->mailbox,$courier,$weight,$price,$package_type,$package_code,$package->item_description,$package->manifest_no);
        }
        
        //dd($checkPkg);
        //Save Label
        //$this->createLabel($package->tracking_no,$couriers->courier_id);
        return ["status" => true,"message" =>"Package resend successfully" , 'data' => $checkPkg];
    }
    
   public function resendPackageApi($base_url, $courier_id, $tracking_no, $supplier_tracking_no, $mailbox, $courier, $weight, $price, $package_type, $category_id, $item_description, $manifest_no)
{
    try {
        \Log::info('Resending package to API', [
            'courier_id' => $courier_id,
            'tracking_no' => $tracking_no,
            'supplier_tracking_no' => $supplier_tracking_no
        ]);
        
        $curl = curl_init();
        $couriers = Courier::where('id', $courier_id)->first();
        
        if (!$couriers) {
            \Log::error('Courier not found: ' . $courier_id);
            return json_encode(['status' => false, 'message' => 'Courier not found']);
        }
        
        $url = $base_url . '/api/resendPackage';
        
        $postData = [
            'tracking_no' => $tracking_no,
            'mailbox' => $mailbox,
            'courier' => $courier,
            'supplier_tracking_no' => $supplier_tracking_no,
            'package_type' => $package_type,
            'package_code' => $category_id,
            'weight' => $weight,
            'price' => $price,
            'manifest_no' => $manifest_no,
            'item_description' => $item_description
        ];
        
        \Log::info('API Request Data:', $postData);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        
        curl_close($curl);
        
        if ($curlError) {
            \Log::error('CURL Error: ' . $curlError);
            return json_encode(['status' => false, 'message' => 'CURL Error: ' . $curlError]);
        }
        
        \Log::info('API Response:', ['response' => $response, 'http_code' => $httpCode]);
        
        // If response is empty or not JSON, return a default response
        if (empty($response)) {
            return json_encode(['status' => true, 'message' => 'Package processed']);
        }
        
        return $response;
        
    } catch (\Exception $e) {
        \Log::error('Exception in resendPackageApi: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_encode(['status' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    }
}
    public function downloadLabel($packeg_id = null){
        //return $packeg_id;
        $packages = DB::table('packages')->where('packeg_id',$packeg_id)->first();
        if($packages && ! $this->isSeaPackage($packages) && ! $this->hasSeaSaasLabel($packages)){
            $cmsLabelHtml = $this->cmsLabelHtmlForPackage($packages);
            if($cmsLabelHtml){
                return response($this->normalizeCmsLabelHtml($cmsLabelHtml, $packages), 200)->header('Content-Type', 'text/html; charset=UTF-8');
            }
        }
        //dd($packages);
        $tracking_no = $packages->tracking_no;
        $courier_id = $packages->courier_id;
        $couriers = Courier::where('id' , $courier_id)->first();
        if($couriers){
            $base_url = $couriers->base_url;
        }else{
            $base_url = '';
        }
        $packages = DB::table('packages')->where('tracking_no',$tracking_no)->first();
                //dd($packages);
        //return $packages->mailbox;
        //return $courier_id;
        if($courier_id > 0){
            $locationRes = $this->GetUsersLocation($base_url,$courier_id,$packages->mailbox);
            $responceLoc = json_decode($locationRes,true);
            if(isset($responceLoc['status']) && $responceLoc['status'] == 'success'){
                $loc_name = $responceLoc['location'];
            }else{
                $loc_name = '';
            }
        }else{
            $loc_name = '';
        }
        $user_name = commonHelper::getUserName($packages->mailbox);
        $outlet_name = commonHelper::getOutletName($packages->mailbox);
        $fileName = time().'_label.pdf';
        $pathPDF = base_path() . '/public/packages/labels/'.$fileName;
        $baseURL = URL::to('/');
        //$expUrl = explode('/index.php' , $baseURL);
        $settings = DB::table('settings')->first();
        $data['user_name'] = $user_name;
        $data['outlet_name'] = $outlet_name;
        $data['mailbox'] = $packages->mailbox;
        $data['weight'] = $packages->weight;
        $data['tracking_no'] = $packages->package_no ?: ($packages->supplier_tracking_no ?: ($packages->manifest_no ?: (string) $packages->packeg_id));
        $data['location'] = $loc_name ?: 'Jamaica';
        $data['category'] = $packages->item_description;
        $this->applySaasLabelData($data, $packages);
        $this->applyLabelMeasureData($data, $packages);
        $this->applyLabelDestinationData($data, $packages);
        $this->applyLabelTypeData($data, $packages);
        $data['auto_print'] = request()->boolean('print');
        $data['company_name'] = $settings->company_name;
        $data['package_shipping_address_1'] = $settings->package_shipping_address_1;
        $data['package_shipping_address_2'] = $settings->package_shipping_address_2;
        $data['city'] = $settings->city;
        $data['zip'] = $settings->zip;
        $data['country'] = $settings->country;
         $data['pkgid'] = $packages->courier_id;
         $datee = $packages->created_at;
         $datee = strtotime($datee);
        $data['dte'] = date("Y-m-d",$datee);
        //dd($data);
        //$data['unique_code'] = $package_no;
        //$data['location'] = $settings->country;
        return response()
            ->view('admin/packages/pdfinvoice', $data)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
    
    public function downloadLabelNew($packeg_id = null){
        $packages = DB::table('packages')->where('packeg_id',$packeg_id)->first();
        if($packages && ! $this->isSeaPackage($packages) && ! $this->hasSeaSaasLabel($packages)){
            $cmsLabelHtml = $this->cmsLabelHtmlForPackage($packages);
            if($cmsLabelHtml){
                return response($this->normalizeCmsLabelHtml($cmsLabelHtml, $packages), 200)->header('Content-Type', 'text/html; charset=UTF-8');
            }
        }
        $tracking_no = $packages->tracking_no;
        $courier_id = $packages->courier_id;
        $couriers = Courier::where('id' , $courier_id)->first();
        if($couriers){
            $base_url = $couriers->base_url;
        }else{
            $base_url = '';
        }
        $packages = DB::table('packages')->where('tracking_no',$tracking_no)->first();
                //dd($packages);
        //return $packages->mailbox;
        if($courier_id > 0){
            $locationRes = $this->GetUsersLocation($base_url,$courier_id,$packages->mailbox);
            $responceLoc = json_decode($locationRes,true);
            if(isset($responceLoc['status']) && $responceLoc['status'] == 'success'){
                $loc_name = $responceLoc['location'];
            }else{
                $loc_name = '';
            }
        }else{
            $loc_name = '';
        }
        $user_name = commonHelper::getUserName($packages->mailbox);
        $fileName = time().'_label.pdf';
        $pathPDF = base_path() . '/public/packages/labels/'.$fileName;
        $baseURL = URL::to('/');
        //$expUrl = explode('/index.php' , $baseURL);
        $settings = DB::table('settings')->first();
        $data['user_name'] = $user_name;
        $data['mailbox'] = $packages->mailbox;
        $data['weight'] = $packages->weight;
        $data['tracking_no'] = $packages->package_no ?: ($packages->supplier_tracking_no ?: ($packages->manifest_no ?: (string) $packages->packeg_id));
        $data['location'] = $loc_name ?: 'Jamaica';
        $data['category'] = $packages->item_description;
        $this->applySaasLabelData($data, $packages);
        $this->applyLabelMeasureData($data, $packages);
        $this->applyLabelDestinationData($data, $packages);
        $this->applyLabelTypeData($data, $packages);
        $data['company_name'] = $settings->company_name;
        $data['package_shipping_address_1'] = $settings->package_shipping_address_1;
        $data['package_shipping_address_2'] = $settings->package_shipping_address_2;
        $data['city'] = $settings->city;
        $data['zip'] = $settings->zip;
        $data['country'] = $settings->country;
        //$data['unique_code'] = $package_no;
        //$data['location'] = $settings->country;
        return response()
            ->view('admin/packages/pdfinvoice', $data)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function isSeaPackage($package): bool
    {
        $parts = [
            $package->freight_type ?? '',
            $package->shipment_type ?? '',
            $package->route ?? '',
            $package->type ?? '',
        ];

        return str_contains(strtolower(implode(' ', $parts)), 'sea');
    }

    private function hasSeaSaasLabel($package): bool
    {
        foreach($this->labelLookupCandidates($package) as $lookup){
            $label = CourierSaasSync::label((string) $lookup);
            if(is_array($label) && strtoupper((string) ($label['shipment_type'] ?? '')) === 'SEA'){
                return true;
            }
        }

        return false;
    }

    private function applyLabelMeasureData(array &$data, $package): void
    {
        if($this->isSeaPackage($package) || ! empty($data['is_sea_label'])){
            $data['measure_label'] = 'Volume:';
            $data['measure_value'] = $this->packageVolumeDisplay($package, $this->numericValue($data['weight_label'] ?? null));
            $data['weight_label'] = $data['measure_value'];

            if(isset($data['cms_label']) && is_array($data['cms_label'])){
                $data['cms_label']['measure_label'] = $data['measure_label'];
                $data['cms_label']['weight_display'] = $data['measure_value'];
            }

            return;
        }

        $unit = strtoupper((string) ($package->weight_unit ?? 'lb')) === 'KG' ? 'KG' : 'LBS';
        $weight = $this->formatDecimal((float) ($package->weight ?? $data['weight'] ?? 0), 2);
        $data['measure_label'] = 'Weight:';
        $data['measure_value'] = $weight.' '.$unit;
    }

    private function applyLabelTypeData(array &$data, $package): void
    {
        $shipmentType = $data['is_sea_label'] ?? false ? 'SEA' : null;
        $labelType = $this->packageLabelDisplayType($package, $shipmentType);

        $data['label_type'] = $labelType;
        $data['freight_type'] = $labelType;

        if(isset($data['cms_label']) && is_array($data['cms_label'])){
            $data['cms_label']['freight_type'] = $labelType;
        }
    }

    private function applyLabelDestinationData(array &$data, $package): void
    {
        $destination = $this->packageLabelDestination(
            $data['cms_label']['destination'] ?? null,
            $data['location'] ?? null,
            $data['outlet_name'] ?? null,
            $package->destination ?? null,
            $package->location ?? null
        );

        $data['destination'] = $destination;

        if(isset($data['cms_label']) && is_array($data['cms_label'])){
            $data['cms_label']['destination'] = $destination;
        }
    }

    private function packageLabelDestination(...$candidates): string
    {
        foreach($candidates as $candidate){
            $value = trim((string) $candidate);
            if($value !== '' && ! in_array(strtolower($value), ['n/a', 'na', 'null', '-'], true)){
                return $value;
            }
        }

        return 'Jamaica';
    }

    private function packageLabelDestinationForPackage($package): string
    {
        $outletName = commonHelper::getOutletName($package->mailbox ?? '');
        $locationName = '';
        $courierId = (int) ($package->courier_id ?? 0);

        if($courierId > 0){
            $courier = Courier::where('id', $courierId)->first();
            if($courier && trim((string) $courier->base_url) !== ''){
                $locationResponse = $this->GetUsersLocation($courier->base_url, $courierId, $package->mailbox ?? '');
                $decoded = json_decode($locationResponse, true);
                if(isset($decoded['status'], $decoded['location']) && $decoded['status'] === 'success'){
                    $locationName = $decoded['location'];
                }
            }
        }

        return $this->packageLabelDestination(
            $locationName,
            $outletName,
            $package->destination ?? null,
            $package->location ?? null
        );
    }

    private function packageLabelDisplayType($package, ?string $shipmentType = null): string
    {
        $region = $this->packageLabelRegion($package);
        $methodSource = strtolower(trim((string) ($shipmentType ?: '')));

        if($methodSource === ''){
            $methodSource = strtolower(implode(' ', [
                $package->freight_type ?? '',
                $package->shipment_type ?? '',
                $package->label_type ?? '',
                $package->route ?? '',
                $package->type ?? '',
            ]));
        }

        $method = str_contains($methodSource, 'sea') ? 'Sea' : 'Air';

        return $region.' '.$method;
    }

    private function packageLabelRegion($package): string
    {
        $regionSource = strtoupper(trim((string) ($package->warehouse_code ?? '')));
        $labelType = strtoupper(trim((string) ($package->label_type ?? '')));

        if(in_array($regionSource, ['CHINA', 'CN'], true) || str_starts_with($labelType, 'CN ')){
            return 'CN';
        }

        return 'USA';
    }

    private function packageVolumeDisplay($package, ?float $fallbackCuft = null): string
    {
        $cuft = (float) ($package->volume_cuft ?? 0);
        $cbm = (float) ($package->volume_cbm ?? 0);

        if($cuft <= 0 || $cbm <= 0){
            [$calculatedCuft, $calculatedCbm] = $this->calculatePackageVolume($package);
            $cuft = $cuft > 0 ? $cuft : $calculatedCuft;
            $cbm = $cbm > 0 ? $cbm : $calculatedCbm;
        }

        if($cuft <= 0 && $fallbackCuft && $fallbackCuft > 0){
            $cuft = $fallbackCuft;
            $cbm = $cuft / 35.3147;
        }

        if($cuft <= 0 && (float) ($package->chargeable_weight ?? 0) > 0){
            $cuft = (float) $package->chargeable_weight;
            $cbm = $cuft / 35.3147;
        }

        if($cbm <= 0 && $cuft > 0){
            $cbm = $cuft / 35.3147;
        }

        if($cuft <= 0 && $cbm > 0){
            $cuft = $cbm * 35.3147;
        }

        return $this->formatDecimal($cuft, 2).' cu.ft / '.$this->formatDecimal($cbm, 3).' CBM';
    }

    private function packageMeasureDisplay($package): string
    {
        if($this->isSeaPackage($package)){
            return $this->packageVolumeDisplay($package);
        }

        $unit = strtolower((string) ($package->weight_unit ?? 'lb')) === 'kg' ? 'kg' : 'lb';

        return $this->formatDecimal((float) ($package->weight ?? 0), 2).' '.$unit;
    }

    private function calculatePackageVolume($package): array
    {
        $length = (float) ($package->dimension_length ?? 0);
        $width = (float) ($package->dimension_width ?? 0);
        $height = (float) ($package->dimension_height ?? 0);
        $unit = strtolower((string) ($package->dimension_unit ?? 'in'));

        if($length <= 0 || $width <= 0 || $height <= 0){
            return [0, 0];
        }

        $lengthIn = $unit === 'cm' ? $length / 2.54 : ($unit === 'ft' ? $length * 12 : $length);
        $widthIn = $unit === 'cm' ? $width / 2.54 : ($unit === 'ft' ? $width * 12 : $width);
        $heightIn = $unit === 'cm' ? $height / 2.54 : ($unit === 'ft' ? $height * 12 : $height);
        $cuft = ($lengthIn * $widthIn * $heightIn) / 1728;

        return [$cuft, $cuft / 35.3147];
    }

    private function formatDecimal(float $value, int $precision): string
    {
        return rtrim(rtrim(number_format(max(0, $value), $precision, '.', ''), '0'), '.') ?: '0';
    }

    private function numericValue($value): ?float
    {
        if(preg_match('/\d+(?:\.\d+)?/', (string) $value, $matches)){
            return (float) $matches[0];
        }

        return null;
    }

    private function normalizeCmsLabelHtml(string $html, $package): string
    {
        $labelType = $this->packageLabelDisplayType($package);
        $destination = $this->packageLabelDestinationForPackage($package);

        $patterns = [
            '/(<div[^>]*class=["\'][^"\']*label-text[^"\']*["\'][^>]*>\s*Type:\s*<\/div>\s*<div[^>]*class=["\'][^"\']*value-medium[^"\']*["\'][^>]*>)(.*?)(<\/div>)/is',
            '/(<[^>]*>\s*Type:\s*<\/[^>]*>\s*<[^>]*>)(AIR|SEA|US\s+AIR|US\s+SEA|USA\s+AIR|USA\s+SEA|CN\s+AIR|CN\s+SEA)(<\/[^>]*>)/i',
        ];

        foreach($patterns as $pattern){
            $updated = preg_replace($pattern, '$1'.e($labelType).'$3', $html, 1);
            if(is_string($updated) && $updated !== $html){
                $html = $updated;
                break;
            }
        }

        $destinationPatterns = [
            '/(<div[^>]*class=["\'][^"\']*label-text[^"\']*["\'][^>]*>\s*Destination:\s*<\/div>\s*<div[^>]*class=["\'][^"\']*value-medium[^"\']*["\'][^>]*>)(.*?)(<\/div>)/is',
            '/(<[^>]*>\s*Destination:\s*<\/[^>]*>\s*<[^>]*>)(.*?)(<\/[^>]*>)/is',
        ];

        foreach($destinationPatterns as $pattern){
            $updated = preg_replace($pattern, '$1'.e($destination).'$3', $html, 1);
            if(is_string($updated) && $updated !== $html){
                $html = $updated;
                break;
            }
        }

        return str_replace(['>US Air<', '>US Sea<', '>AIR<', '>SEA<'], ['>USA Air<', '>USA Sea<', '>'.e($labelType).'<', '>'.e($labelType).'<'], $html);
    }

    private function cmsLabelHtmlForPackage($package): ?string
    {
        foreach($this->labelLookupCandidates($package, true) as $tracking){
            $html = CourierSaasSync::labelHtml($tracking);
            if($this->isUsableCmsLabelHtml($html)){
                return $html;
            }
        }

        return null;
    }

    private function labelLookupCandidates($package, bool $includeId = false)
    {
        $values = [
            $package->manifest_no ?? null,
            $package->tracking_no ?? null,
            $package->supplier_tracking_no ?? null,
            $package->package_no ?? null,
        ];

        if($includeId){
            $values[] = $package->packeg_id ?? null;
        }

        return collect($values)
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values();
    }

    private function isUsableCmsLabelHtml(?string $html): bool
    {
        if(! $html){
            return false;
        }

        $body = strtolower($html);

        return str_contains($body, 'barcode')
            || str_contains($body, 'download pdf')
            || str_contains($body, 'print label')
            || str_contains($body, 'package contents')
            || str_contains($body, 'tracking no');
    }

    private function applySaasLabelData(array &$data, $package): void
    {
        $label = null;
        $candidates = collect([
                $package->manifest_no ?? null,
                $package->tracking_no ?? null,
                $package->supplier_tracking_no ?? null,
                $package->package_no ?? null,
                $data['tracking_no'] ?? null,
            ])
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values();

        foreach($candidates as $lookup){
            $label = CourierSaasSync::label((string) $lookup);
            if(is_array($label)){
                break;
            }
        }

        if(! is_array($label)){
            return;
        }

        $shipmentType = strtoupper((string) ($label['shipment_type'] ?? ''));
        $data['tracking_no'] = $label['hawb'] ?? $data['tracking_no'];
        $data['category'] = $label['description'] ?? ($data['category'] ?? '');
        $data['user_name'] = $label['customer_name'] ?? $data['user_name'];
        $data['cms_label'] = $label['label'] ?? [];

        if($shipmentType === 'SEA' && isset($label['volume'])){
            $data['is_sea_label'] = true;
            $data['weight_label'] = $label['volume'].' CU.FT';
        }elseif(isset($label['weight'])){
            $data['weight'] = $label['weight'];
            $data['weight_label'] = $label['weight'].'LBS';
        }
    }
    
    public function getPackageNumber(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://uniquemediadesigns.com/ctms_new/public/api/getPackageNumber',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"supplier_tracking_no":"TBA132238246301"}',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
    }
    public function massResend()
{
    $data['title'] = 'Mass Resend Packages';
    $data['class'] = 'package';
    
    // Get all couriers with count of status=3 packages
    $data['couriers'] = Courier::select('couriers.*', DB::raw('COUNT(packages.packeg_id) as package_count'))
        ->leftJoin('packages', function($join) {
            $join->on('couriers.id', '=', 'packages.courier_id')
                 ->where('packages.status', '=', 3)
                 ->where('packages.is_temprary_logs', '=', 0);
        })
        ->groupBy('couriers.id')
        ->orderBy('couriers.name')
        ->get();
    
    // Get total count of status=3 packages
    $data['total_count'] = Package::where('status', 3)
        ->where('is_temprary_logs', 0)
        ->count();
    
    return view('admin.packages.mass_resend', $data);
}
public function processMassResendAll(Request $request)
{
    try {
        set_time_limit(0);
        
        $couriers = Courier::whereHas('packages', function($query) {
            $query->where('status', 3)->where('is_temprary_logs', 0);
        })->get();
        
        if ($couriers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No couriers with status=3 packages found.'
            ]);
        }
        
        $overall_results = [
            'total_packages' => 0,
            'total_success' => 0,
            'total_failed' => 0,
            'total_created' => 0,
            'courier_results' => []
        ];
        
        foreach ($couriers as $courier) {
            // Reuse the existing method but capture results
            $request->merge(['courier_id' => $courier->id]);
            $response = $this->processMassResend($request, $courier->id);
            $result = json_decode($response->getContent(), true);
            
            if ($result['success'] && isset($result['results'])) {
                $overall_results['total_packages'] += $result['results']['total'];
                $overall_results['total_success'] += $result['results']['success'];
                $overall_results['total_failed'] += $result['results']['failed'];
                $overall_results['total_created'] += $result['results']['created'];
                $overall_results['courier_results'][$courier->name] = $result['results'];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Mass resend completed for all couriers!',
            'results' => $overall_results
        ]);
        
    } catch (\Exception $e) {
        Log::error("Mass Resend All Error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error processing mass resend: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get package count for a specific courier (AJAX)
 */
public function getCourierPackageCount($courier_id)
{
    $count = Package::where('courier_id', $courier_id)
        ->where('status', 3)
        ->where('is_temprary_logs', 0)
        ->count();
    
    return response()->json([
        'success' => true,
        'count' => $count
    ]);
}
    /**
 * Process mass resend for a specific courier
 */
public function processMassResend(Request $request, $courier_id)
{
    try {
        \Log::info('Mass Resend Started for Courier ID: ' . $courier_id);
        
        set_time_limit(0); // Prevent timeout for large batches
        
        $courier = Courier::find($courier_id);
        
        if (!$courier) {
            \Log::error('Courier not found: ' . $courier_id);
            return response()->json([
                'success' => false,
                'message' => 'Courier not found.'
            ]);
        }
        
        \Log::info('Processing for courier: ' . $courier->name);
        
        // Get all packages with status = 3 for this courier
        $packages = Package::where('courier_id', $courier_id)
            ->where('status', 3)
            ->where('is_temprary_logs', 0)
            ->get();
        
        if ($packages->isEmpty()) {
            \Log::info('No packages found for courier: ' . $courier->name);
            return response()->json([
                'success' => false,
                'message' => 'No packages found with status = 3 for this courier.'
            ]);
        }
        
        \Log::info('Found ' . $packages->count() . ' packages for courier: ' . $courier->name);
        
        $results = [
            'total' => $packages->count(),
            'success' => 0,
            'failed' => 0,
            'created' => 0,
            'errors' => []
        ];
        
       foreach ($packages as $package) {
    try {
        \Log::info('Processing package: ' . $package->supplier_tracking_no . ' for courier: ' . $courier->name);
        
        // FIRST: Check if package already exists in courier's database
        $exists = $this->checkPackageExistsInCourier(
            $courier->base_url,
            $courier->id,
            $package->tracking_no,
            $package->supplier_tracking_no
        );
        
        $existsResult = json_decode($exists, true);
        
        // If package already exists, count as "created" (already there) and skip
        if ($existsResult && isset($existsResult['status']) && $existsResult['status'] === false) {
            \Log::info('Package already exists in courier DB: ' . $package->supplier_tracking_no);
            $results['created']++;
            continue;
        }
        
        // If there was an error checking existence, log it but proceed with caution
        if ($existsResult && isset($existsResult['error'])) {
            \Log::warning('Error checking package existence: ' . $existsResult['error']);
        }
        
        // Calculate price based on weight if needed
        $weight = round($package->weight);
        $price = $package->price;
        
        if (!$price || $price == 0) {
            if ($weight > 76) {
                $weightRes = DB::table('weights')->where('weight', 76)->first();
                $price = $weightRes ? $weightRes->price : 0;
            } else {
                $weightRes = DB::table('weights')->where('weight', $weight)->first();
                $price = $weightRes ? $weightRes->price : 0;
            }
        }
        
        $response = null;
        
        // Log the request details
        \Log::info('Sending request to courier API', [
            'courier_id' => $courier->id,
            'courier_name' => $courier->name,
            'base_url' => $courier->base_url,
            'package' => $package->supplier_tracking_no
        ]);
        
        $response = $this->resendPackageApi(
            $courier->base_url,
            $courier->id,
            $package->tracking_no,
            $package->supplier_tracking_no,
            $package->mailbox,
            $package->courier,
            $weight,
            $price,
            $package->package_type,
            $package->package_code,
            $package->item_description,
            $package->manifest_no
        );
        
        // Log the raw response for debugging
        \Log::info('RAW API Response for package ' . $package->supplier_tracking_no . ':', ['response' => $response]);
        
        // Try to decode the response
        $result = json_decode($response, true);
        
        if (!$result) {
            // Check if response is HTML (common error page)
            if (strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false) {
                $results['failed']++;
                $results['errors'][] = "Package {$package->supplier_tracking_no}: API returned HTML instead of JSON - Possible server error";
                \Log::error("HTML Response received for package {$package->supplier_tracking_no}");
                continue;
            }
            
            // Check if response is empty
            if (empty($response)) {
                $results['failed']++;
                $results['errors'][] = "Package {$package->supplier_tracking_no}: Empty response from API";
                \Log::error("Empty response for package {$package->supplier_tracking_no}");
                continue;
            }
            
            // Check if it's a simple success message (not JSON)
            if (stripos($response, 'success') !== false || stripos($response, 'created') !== false) {
                $results['success']++;
                \Log::info("Success (non-JSON) for package: {$package->supplier_tracking_no}");
                continue;
            }
            
            if (stripos($response, 'already exist') !== false) {
                $results['created']++;
                \Log::info("Package already exists: {$package->supplier_tracking_no}");
                continue;
            }
            
            // If we can't determine, log the first 200 chars of response
            $preview = substr($response, 0, 200);
            $results['failed']++;
            $results['errors'][] = "Package {$package->supplier_tracking_no}: Invalid JSON - Response preview: {$preview}";
            \Log::error("Invalid JSON for package {$package->supplier_tracking_no}. Preview: {$preview}");
            continue;
        }
        
        // We have valid JSON, process it
        \Log::info('Valid JSON response for package ' . $package->supplier_tracking_no . ':', ['result' => $result]);
        
        // Check response status
        if (isset($result['status'])) {
            $status = $result['status'];
            
            if ($status === true || $status === 1 || $status === 'true' || $status === 'success' || $status === 'Success') {
                $results['success']++;
                
                // Update package_no if returned
                if (isset($result['Package_no']) || isset($result['package_no'])) {
                    $packageNo = $result['Package_no'] ?? $result['package_no'] ?? null;
                    if ($packageNo) {
                        Package::where('packeg_id', $package->packeg_id)
                            ->update(['package_no' => $packageNo]);
                    }
                }
                
                \Log::info("Mass Resend Success - Courier: {$courier->name}, Package: {$package->supplier_tracking_no}");
                
            } elseif ($status === false || $status === 0 || $status === 'false') {
                // Check if it's an "already exists" message
                $message = isset($result['message']) ? strtolower($result['message']) : '';
                if (strpos($message, 'already exist') !== false || strpos($message, 'already exists') !== false) {
                    $results['created']++;
                    \Log::info("Mass Resend - Package already exists: {$package->supplier_tracking_no}");
                } else {
                    $results['failed']++;
                    $errorMsg = $result['message'] ?? 'Unknown error';
                    $results['errors'][] = "Package {$package->supplier_tracking_no}: {$errorMsg}";
                    \Log::error("Mass Resend Failed - Package: {$package->supplier_tracking_no}, Error: {$errorMsg}");
                }
            } else {
                $results['success']++; // Assume success if status is set but not false
                \Log::info("Mass Resend - Package processed with status: {$status}");
            }
        } else {
            // No status field, check if there's any indication of success
            if (isset($result['message']) && (stripos($result['message'], 'success') !== false)) {
                $results['success']++;
            } elseif (isset($result['error']) && stripos($result['error'], 'already exist') !== false) {
                $results['created']++;
            } else {
                // If we got a valid JSON response but no status field, log it for debugging
                \Log::warning("JSON response without status field for package {$package->supplier_tracking_no}:", $result);
                $results['success']++; // Assume success
            }
        }
        
    } catch (\Exception $e) {
        $results['failed']++;
        $results['errors'][] = "Package {$package->supplier_tracking_no}: " . $e->getMessage();
        \Log::error("Mass Resend Exception - Package: {$package->supplier_tracking_no}, Error: " . $e->getMessage());
        \Log::error($e->getTraceAsString());
    }
}
        
        // Add activity log
        $type = 'Mass Resend';
        $dateTime = date('Y-m-d H:i:s');
        $message = "Mass resend completed for courier: {$courier->name}. Total: {$results['total']}, Success: {$results['success']}, Already Exist: {$results['created']}, Failed: {$results['failed']}";
        $admin = session('user_id');
        
        if (class_exists('commonHelper')) {
            commonHelper::addActivity($type, $dateTime, $message, $admin, json_encode($results), $courier_id);
        }
        
        \Log::info('Mass Resend Completed', $results);
        
        return response()->json([
            'success' => true,
            'message' => "Mass resend completed successfully!",
            'results' => $results
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Mass Resend Error: " . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error processing mass resend: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Check if package already exists in courier's database
 */
public function checkPackageExistsInCourier($base_url, $courier_id, $tracking_no, $supplier_tracking_no)
{
    try {
        \Log::info('Checking if package exists in courier DB', [
            'courier_id' => $courier_id,
            'supplier_tracking_no' => $supplier_tracking_no
        ]);
        
        $couriers = Courier::where('id', $courier_id)->first();
        
        if (!$couriers) {
            return json_encode(['error' => 'Courier not found']);
        }
        
        $curl = curl_init();
        $url = $base_url . '/api/checkPackage';
        
        $postData = [
            'tracking_no' => $tracking_no,
            'supplier_tracking_no' => $supplier_tracking_no
        ];
        
        $headers = ['Content-Type: application/json'];
        
        if (!empty($couriers->api_key)) {
            $headers[] = 'api_key: ' . $couriers->api_key;
        }
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $headers,
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        
        curl_close($curl);
        
        if ($curlError) {
            \Log::error('CURL Error in checkPackageExists: ' . $curlError);
            return json_encode(['error' => 'CURL Error: ' . $curlError]);
        }
        
        \Log::info('Check package response:', ['response' => $response, 'http_code' => $httpCode]);
        
        return $response;
        
    } catch (\Exception $e) {
        \Log::error('Exception in checkPackageExistsInCourier: ' . $e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}
}
