<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use App\Models\UserLoginData;
use App\Models\Subscription;
use Session;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use URL;
use Mail;
use Image;
use Validator;
use App\Models\Package;
use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Courier;
use App\Models\Notification;
use App\Classes\CommonLibrary;
use App\Classes\CourierSaasSync;
use commonHelper;
use App\Models\Outlet;

class UserControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index(){
        $data['title'] = 'Manage Users';
        $data['class'] = 'users';
        $data['table'] = 'Manage Users';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['users'] = User::where('role_id' , '=' , 0)->get();
        return view('admin/users/index' , $data);
    }

    public function view(){
        $data['title'] = 'Manage Users';
        $data['class'] = 'users';
        $data['table'] = 'Manage Users';
        return view("admin.users.list",$data);
    }

    public function listView(){

        return DataTables::of(User::where('role_id' , 0))
                ->editColumn('delete', function ($data) {
                    if($data->status == 0){
                        $btn = "<a href='".url('/')."/admin/users/1/".$data->user_id."'  class='btn btn-warning' style='padding: 5px 10px;' id='".$data->user_id."' title='Block'><i class='ti-na pdd-right-0'></i><span>Block</span></a>";
                    }else{
                        $btn = "<a href='".url('/')."/admin/users/0/".$data->user_id."'  class='btn btn-success' style='padding: 5px 10px;' id='".$data->user_id."' title='Access'><i class='ei-circle pdd-right-0'></i><span>Access</span></a>";
                    }
                    return $btn;
                })
                ->rawColumns(['delete' => 'delete','action' => 'action'])
                ->addColumn('action', function($data){
                    return "<a href='".url('/')."/admin/user/profile/".$data->user_id."'  class='all_button btn  btn-sm btn-warning'  data-value='".$data."' id='".$data->user_id."'>User Profile</a><a href='".url('/')."/admin/user/edit/".$data->user_id."'  class='all_button btn  btn-sm btn-info edit_button'  data-value='".$data."' id='".$data->user_id."'>Edit</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  data-value='".$data."' id='".$data->user_id."'>Delete</a>";
                })->make(true);
    }

    public function listViewReady($mailbox = ''){
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' , 1)
                    ->where('packages.mailbox' , $mailbox);
        return DataTables::of($query)->make(true);
    }


    public function userProfile($user_id = '') {
        $data['title'] = 'User Profile';
        $data['class'] = 'users';
        $user = User::where('user_id' , $user_id)->first();
        $mailbox = $user->mailbox;
        $data['mailbox'] = $mailbox;
        $data['user'] = $user;

        $invoiceTotal = 0;
        $data['setting'] = DB::table('settings')->first();
        $data['outstanding_package'] = DB::table('packages')->where('packages.status' , 1)->where('packages.mailbox' , $mailbox)->count();
        $data['in_transit'] = DB::table('packages')->where('packages.status' , 15)->count();
        $data['outstanding_invoice'] = DB::table('invoices')->where('invoices.mailbox_no' , $mailbox)->where('invoices.status' , 0)->count();
        $outstanding_balance = DB::table('invoices')->where('invoices.mailbox_no' , $mailbox)->where('invoices.status' , 0)->get();
        //dd($outstanding_balance);
        if(count($outstanding_balance) > 0){
            foreach($outstanding_balance as $row){
                $totalInv = commonHelper::getInvoiceOutstanding($row->invoice_id);
                $invoiceTotal = $invoiceTotal + $totalInv;
            }
        }
        $data['outstanding_balance'] = number_format($invoiceTotal,2).' JMD';
        $data['wherehouse'] = DB::table('packages')->where('packages.status' , 3)->where('packages.mailbox' , $mailbox)->count();
        return view('admin.users.dashboard' , $data);
    }

    public function create(Request $request){
        $data['title'] = 'Add User';
        $data['class'] = 'users';
        $data['table'] = 'Add User';
        $setting = DB::table('settings')->first();
        if ($request->isMethod('get')) {
            $data['couriers'] = Courier::get();
            return view('admin/users/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users|max:255',
                'password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
                'address_1' => 'required',
                'city' => 'required',
                'courier_id' => 'required',
                //'postal_code' => 'required',
                'phone' => 'required',
                //'mobile' => 'required',
                'trn' => 'required',
                //'g-recaptcha-response' => 'required|recaptcha'
                //'terms' => 'required|in:1',
            ]);
            if ($validator->fails()) {
                    return redirect('/admin/users')
                            ->withInput()
                            ->withErrors($validator);
            }else{

                if($request->address_2 == null){
                    $address_2 = '';
                }else{
                    $address_2 = $request->address_2;
                }
                if($request->tcc_issue_date == null){
                    $tcc_issue_date = '';
                }else{
                    $tcc_issue_date = $request->tcc_issue_date;
                }
                if($request->tcc_expiry_date == null){
                    $tcc_expiry_date = '';
                }else{
                    $tcc_expiry_date = $request->tcc_expiry_date;
                }
                if($request->Business_trn == null){
                    $Business_trn = '';
                }else{
                    $Business_trn = $request->Business_trn;
                }
                if($request->mobile == null){
                    $mobile = '';
                }else{
                    $mobile = $request->mobile;
                }
                $usersRes = User::orderby('user_id' , 'DESC')->first();
                //dd($usersRes);
                //return $usersRes->user_id;
                $user_id_mail = $usersRes->user_id + 1;
                if($usersRes){
                    if($usersRes->user_id > 1000){
                        $mailBoxNo = $setting->pre_fix.'0';
                    }elseif($usersRes->user_id > 100){
                        $mailBoxNo = $setting->pre_fix.'00';
                    }else{
                        $mailBoxNo = $setting->pre_fix.'000';
                    }
                }else{
                    $mailBoxNo = $setting->pre_fix.'0001';
                }
                $mailbox = $mailBoxNo.$user_id_mail;
                $users = new User();
                $users->mailbox = $mailbox;
                $users->first_name = $request->first_name;
                $users->courier_id = $request->courier_id;
                $users->last_name = $request->last_name;
                $users->email = $request->email;
                $users->password = Hash::make($request->password);
                $users->dob = 01;
                $users->address_1 = $request->address_1;
                $users->address_2 = $address_2;
                $users->city = $request->city;
                $users->state = $request->state;
                //$users->postal_code = $request->postal_code;
                $users->country = $request->country;
                $users->currency = $request->currency;
                $users->phone = $request->phone;
                $users->mobile = $mobile;
                //$users->location = $request->location;
                $users->user_type = $request->user_type;
                $users->trn = $request->trn;
                $users->tcc_issue_date = $tcc_issue_date;
                $users->tcc_expiry_date = $tcc_expiry_date;
                $users->Business_trn = $Business_trn;
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();
                CourierSaasSync::customer($users->fresh());
                $user_id = $users->user_id;
                //User::where('user_id' , $user_id)->update(['mailbox'=>'OPC-00'.$user_id]);
                $userRes = User::where('user_id' , $user_id)->first();
                if($userRes){
                    $mailboxRes = $userRes->mailbox;
                    $outlet = Outlet::where('outlet_id',$request->outlet_id)->first();
                    if($outlet){
                        $short_name = $outlet->short_name;
                    }else{
                        $short_name = '';
                    }
                    $malRes = explode('-',$mailboxRes);
                    $mailbox = $malRes[0].'-'.$short_name.'-'.$malRes[1];
                }


                $link = URL::to('/');
                $email_subject = 'User Registration';
                $user_name = $request->first_name.' '.$request->last_name;
                //$address = $request->address_1.','.$request->city.','.$request->state.','.$request->country.','.$request->postal_code;
                //$address = $user_name.' '.$mailBoxNo.$user_id.' '.$setting->package_shipping_address_1;
                $address1 = $user_name;
                $address2 = $setting->package_shipping_address_1;
                $address3 = $mailbox;
                $address4 = $setting->city;
                $address5 = $setting->state;
                $address6 = $setting->zip;
                $email_from = $setting->from_email;
                $logo = URL::to('/') . '/settings/'.$setting->site_logo;
                $company_name = $setting->company_name;
                //$this->send_email_register($request->email, $user_name,$request->password,$mailbox,$setting->phone,$address1,$address2,$address3,$address4,$address5,$address6,$email_subject, $email_from, $link,$logo,$company_name, 'register_email');
                Session::flash('success_msg', 'New user has been added successfully');
                return redirect('/admin/users');
            }
        }
    }

    public function edit(Request $request , $id = ''){
        $data['title'] = 'Edit User';
        $data['class'] = 'users';
        $data['table'] = 'Edit User';
        if ($request->isMethod('get')) {
            $data['outlets'] = Outlet::get();
            $data['user'] = User::where('user_id' , $id)->first();
            return view('admin/users/edit' , $data);
        }else{
            if($request->old_email != $request->email){
                $validator = Validator::make($request->all(), [
                                'email' => 'required|unique:users',
                            ]);
            }else{
                $validator = Validator::make($request->all(), [
                                    'first_name' => 'required'
                            ]);
            }

            if ($validator->fails()) {
                    return redirect('admin/user/edit/'.$id)
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                if ($request->hasFile('user_logo')) {
                    $image_name = '';
                    $user_img = User::where('user_id' , $id)->first();
                    if($user_img){
                        $logo = $user_img->image;
                        @unlink(base_path() . '/public/users/' . $logo);
                    }
                    $imageTempName = $request->file('user_logo')->getPathname();
                    $venue_img_extension = $request->user_logo->extension();
                    $imageName = uniqid() . '.' . $venue_img_extension;
                    $path = base_path() . '/public/users/';
                    $request->file('user_logo')->move($path, $imageName);
                    $image = $imageName;
                } else {
                    $image = '';
                    $user_img = User::where('user_id' , $id)->first();
                    if($user_img){
                        $image = $user_img->image;
                    }
                }
                User::where('user_id', $id)
                        ->update([
                            'first_name' => $request->first_name,
                            'last_name' => $request->last_name,
                            'email' => $request->email,
                            'address_1' => $request->address_1,
                            'address_2' => $request->address_2,
                            'city' => $request->city,
                            'image' => $image,
                            'phone' => $request->phone,
                            'mobile' => $request->mobile,
                            //'trn' => $request->trn,
                            'country' => $request->country,
                            'currency' => 'JMD',
                            'location' => $request->location,
                            //'tcc_issue_date' => $request->tcc_issue_date,
                            //'tcc_expiry_date' => $request->tcc_expiry_date,
                            //'Business_trn' => $request->Business_trn,
                            'updated_at' => date('Y-m-d h:i:s')
                        ]);
                CourierSaasSync::customer(User::where('user_id', $id)->first());
                Session::flash('success_msg', 'user has been updated successfully.');
                return redirect('admin/users');
            }
        }
    }

    public function updateStatus($status = '' , $user_id = '') {
        User::where('user_id', $user_id)->update(['status' => $status]);
        if($status == 0){
            Session::flash('success_msg', 'User has been inactive successfully.');
        }else{
            Session::flash('success_msg', 'User has been active successfully.');
        }
        return redirect('admin/users');
    }

    public function delete($user_id = '') {
        $users = User::where('user_id' , $user_id)->first();
        if($users){
            DB::table('packages')->where('mailbox' , $users->mailbox )->delete();
            DB::table('package_requests')->where('mailbox' , $users->mailbox )->delete();
            $invoice = DB::table('invoices')->where('mailbox_no' , $users->mailbox )->get();
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    DB::table('invoice_packages')->where('invoice_id' , $row->invoice_id )->delete();
                }
            }

            $invoice = DB::table('invoices')->where('mailbox_no' , $users->mailbox )->delete();
        }
        $delete = User::where('user_id' , $user_id)->delete();

        Session::flash('del_msg', 'User has been deleted successfully.');
        return redirect('admin/users');
    }

    public function sendPushNotification(Request $request){
            $data['title'] = 'Send Push Notification';
            $data['class'] = 'users';
            $data['table'] = 'Send Push Notification';
            if ($request->isMethod('get')) {
                $data['users'] = User::get();
                return view('admin/users/notification' , $data);
            }else{
                    $validator = Validator::make($request->all(), [
                                            'title' => 'required',
                                    ]);
                    if ($validator->fails()) {
                            return redirect('admin/send/notifications')
                                            ->withInput()
                                            ->withErrors($validator);
                    }else{
                        $title = $request->title;
                        $message_noti = $request->message;
                        $is_user = $request->is_user;
                        if($is_user == 'All'){
                            $users = User::get();
                        }else{
                            $user_ids = $request->user_id;
                            $users = User::whereIn('user_id' , $user_ids)->get();
                        }
                        if(count($users) > 0){
                            foreach($users as $row){
                                //Send Notification
                                $this->sendNotificationToUser($row->user_id ,0,$request->title,$request->message);
                                //End Notification
                            }
                        }
                        Session::flash('success_msg', 'Push Notification has been send to all users successfully');
                        return redirect('admin/send/notifications');
                    }
            }
    }
    
    public function getMembers($courier_id = '' , $keyword = ''){
        $courierIds = $this->localCourierUserIds($courier_id);

        $data['users'] = User::whereIn('courier_id' , $courierIds)
                            ->where(function($query) use ($keyword){
                                    return $query
                                        ->where('first_name', 'LIKE',"%{$keyword}%")
                                        ->orWhere('last_name', 'LIKE',"%{$keyword}%")
                                        ->orWhere('user_name', 'LIKE',"%{$keyword}%")
                                        ->orWhere('mailbox', 'LIKE',"%{$keyword}%")
                                        ->orWhere('email', 'LIKE',"%{$keyword}%");
                                    })
                            ->orderBy('first_name')
                            ->limit(50)
                            ->get();
        
        $data['courier_id'] = $courier_id;
        return view('admin/users/ajaxCourierUsersSearch' , $data);
    }

    public function getCourierUsers($courier_id = '' , $trecking_number = ''){
        $data['users'] = [];
        $data['categories'] = [];
        $data['vendors'] = [];
        $data['types'] = PackageType::get();
        $data['carriers'] = Carrier::get();

        $data['customer_courier_ids'] = [];

        if($courier_id != 0){
            $customerCourierIds = $this->localCourierUserIds($courier_id);
            $data['customer_courier_ids'] = $customerCourierIds;
            $data['users'] = User::whereIn('courier_id' , $customerCourierIds)
                ->orderBy('mailbox')
                ->get();
        }
        if($trecking_number == '0'){
            $data['type'] = 'add';
            $package = (object) array();
            $package->courier_id = '';
            $package->tracking_no = '';
            $package->mailbox = '';
            $package->user_name = '';
            $package->supplier_tracking_no = '';
            $package->courier = '';
            $package->price = '0';
            $package->package_type = '';
            $package->package_code = '';
            $package->vendor_id = '';
            $package->category_id = '';
            $package->weight = '';
            $package->freight_type = '';
        }else{
            $data['type'] = 'edit';
            $package = Package::select('courier_id','tracking_no','mailbox','user_name','supplier_tracking_no','courier','price','package_type','package_code','vendor_id','category_id','weight','freight_type')->where('tracking_no' , $trecking_number)->first();
        }
        //dd($package);
        $data['package'] = $package;
        $data['courier_id'] = $courier_id;
        return view('admin/users/ajaxCourierUsers' , $data);
    }

    public function lookupMailbox($mailbox = '')
    {
        $code = strtoupper(trim(urldecode((string) $mailbox)));
        if ($code === '') {
            return response()->json([
                'status' => 'general_unknown',
                'routing_status' => 'GENERAL_UNKNOWN',
                'message' => 'No mailbox code entered.',
            ]);
        }

        $user = $this->matchedMailboxUser($code);
        if ($user) {
            $courier = Courier::where('id', (int) $user->courier_id)->first();
            $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->user_name ?? '');

            return response()->json([
                'status' => 'verified',
                'routing_status' => 'VERIFIED',
                'mailbox' => $user->mailbox,
                'mailbox_value' => ($user->mailbox ?? '').'+'.$name.'+'.($user->email ?? ''),
                'customer_name' => $name,
                'email' => $user->email ?? '',
                'courier_id' => (int) $user->courier_id,
                'courier_name' => $courier->name ?? '',
            ]);
        }

        $courier = $this->courierForMailboxPrefix($code);
        if ($courier) {
            return response()->json([
                'status' => 'assigned_unknown',
                'routing_status' => 'ASSIGNED_UNKNOWN',
                'mailbox' => $code,
                'courier_id' => (int) $courier->id,
                'courier_name' => $courier->name ?? '',
                'message' => 'Courier identified but customer mailbox was not found.',
            ]);
        }

        return response()->json([
            'status' => 'general_unknown',
            'routing_status' => 'GENERAL_UNKNOWN',
            'mailbox' => $code,
            'message' => 'Mailbox/courier code was not recognized.',
        ]);
    }

    private function courierForMailboxPrefix(string $code): ?Courier
    {
        foreach ($this->mailboxPrefixCandidates($code) as $prefix) {
            $courierId = $this->courierIdForMailboxPrefix($prefix);

            if ($courierId) {
                return Courier::where('id', $courierId)->first();
            }
        }

        return null;
    }

    private function matchedMailboxUser(string $code): ?User
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        $compactCode = str_replace('-', '', $code);

        return User::where(function ($query) use ($code, $compactCode) {
                $query->whereRaw('UPPER(mailbox) = ?', [$code])
                    ->orWhereRaw('UPPER(SUBSTRING_INDEX(mailbox, "-", -1)) = ?', [$code])
                    ->orWhereRaw('REPLACE(UPPER(mailbox), "-", "") = ?', [$compactCode]);
            })
            ->whereNotNull('courier_id')
            ->where('courier_id', '>', 0)
            ->first();
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

    private function localCourierUserIds($courier_id): array
    {
        $ids = array_filter([(int) $courier_id]);
        $courier = Courier::where('id', $courier_id)->first();

        if($courier){
            foreach (['courier_id', 'saas_tenant_id'] as $field) {
                if(isset($courier->{$field}) && (int) $courier->{$field} > 0){
                    $ids[] = (int) $courier->{$field};
                }
            }
        }

        return array_values(array_unique($ids));
    }
    
    public function GetPackagesTypes($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = rtrim($couriers->base_url, '/').'/api/GetPackagesTypes';
            $headers = [];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => $headers,
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
            $url = rtrim($couriers->base_url, '/').'/api/GetVendors';
            $headers = [];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
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
    }
    
    public function GetCategories($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = rtrim($couriers->base_url, '/').'/api/GetCategories';
            $headers = [];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => $headers,
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            return  $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
    
    public function getApiUsers($courier_id = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            if(!$couriers || empty($couriers->base_url)){
                return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
            }
            $headers = ['Content-Type: application/json'];
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
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode(['offset' => 0]),
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

    private function importCourierUsers($courier_id): void
    {
        $response = $this->getApiUsers($courier_id);
        $result = json_decode((string) $response, true);
        $rows = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

        foreach($rows as $row){
            if(!is_array($row)){
                continue;
            }

            $mailbox = $row['mailbox'] ?? $row['account_num'] ?? $row['mailbox_no'] ?? null;
            if(!$mailbox){
                continue;
            }

            $name = trim((string) ($row['name'] ?? $row['user_name'] ?? ''));
            $firstName = $row['first_name'] ?? '';
            $lastName = $row['last_name'] ?? '';
            if($firstName === '' && $name !== ''){
                $parts = explode(' ', $name, 2);
                $firstName = $parts[0] ?? '';
                $lastName = $parts[1] ?? '';
            }

            $values = [
                'courier_id' => $courier_id,
                'mailbox' => $mailbox,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_name' => trim($firstName.' '.$lastName) ?: $name,
                'email' => $row['email'] ?? '',
                'phone' => $row['phone'] ?? '',
                'status' => '1',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $existing = DB::table('users')->where('courier_id', $courier_id)->where('mailbox', $mailbox)->first();
            if($existing){
                DB::table('users')->where('user_id', $existing->user_id)->update($values);
            }else{
                $values['created_at'] = date('Y-m-d H:i:s');
                DB::table('users')->insert($values);
            }
        }
    }
    public function getApiSearchUsers($courier_id = '',$keyword = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/SearchUsers';
            $api_key = $couriers->api_key;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{"keyword":"'.$keyword.'"}',
                CURLOPT_HTTPHEADER => array(
                    'api_key: '.$api_key,
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }

    // public function getCourierUsers($courier_id = ''){
    //     //$data['users'] = User::where('courier_id' , $courier_id)->get();
    //     $responce = $this->getApiUsers($courier_id);
    //     $result = json_decode($responce,true);
    //     //dd($result['data']);
    //     if(isset($result['status']) && $result['status'] == 'success'){
    //         if(count($result['data']) > 0){
    //             $data['users'] = $result['data'];
    //         }else{
    //             $data['users'] = [];
    //         }
            
    //     }else{
    //         $data['users'] = [];
    //     }
    //     return view('admin/users/ajaxCourierUsers' , $data);
    // }
    
    // public function getApiUsers($courier_id = ''){
    //     try{
            
    //         $couriers = Courier::where('id' , $courier_id)->first();
    //         $curl = curl_init();
    //         $url = $couriers->base_url.'/api/GetUsers';
    //         curl_setopt_array($curl, array(
    //           CURLOPT_URL => $url,
    //           CURLOPT_RETURNTRANSFER => true,
    //           CURLOPT_ENCODING => '',
    //           CURLOPT_MAXREDIRS => 10,
    //           CURLOPT_TIMEOUT => 0,
    //           CURLOPT_FOLLOWLOCATION => true,
    //           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //           CURLOPT_CUSTOMREQUEST => 'GET',
    //         ));
    //         $response = curl_exec($curl);
    //         curl_close($curl);
    //         return $response;
    //     }catch(\Exception $e){
    //         return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
    //     }
    //     //return view('admin/users/ajaxCourierUsers' , $data);
    // }

    public function sendNotificationToUser($user_id , $follower_id,$title,$body){

        $username = User::where('user_id' , $user_id)->first()->first_name;
        $users = UserLoginData::select('deviceToken','userId')->where('userId' , $user_id)->where('tokenStatus',0)->get();
        if(count($users) > 0){
            $deviceToken = [];
            foreach($users as $key => $val ){
                $deviceToken[] = $val->deviceToken;
                //$deviceToken[] = 'ep4JTpZFT-KsB8ejoM4NoI:APA91bHduV1A7Q3TtGI6PgX0GNEaZ-wPvEOQmctFxCqpf615h1v1JRLfx9lqinxF1n8_wYU0T9koz1g5k2H3Be6DAjwX-8o8qNg094O7tJ4WtW3aj9_jQSyhq7w3IAxa1-z89-d_kWXn';
            }
            $type = 'Admin';
            $message =  array(
                            'type' => 'notification',
                            'title' => $title,
                            'body' => $body,
                            'username' => $username,
                            'user_id' => 1,
                            'notification_userid' => $user_id,
                            'type1' => 'admin',
                            'time' => $this->getdate(time()),
                            'sound'=> 'default',
                            'content-available'=> true,
                            'icon' => 'chat1'
                        );
            commonHelper::firebase($deviceToken,$message);
            commonHelper::saveNotification($follower_id,$user_id,$username,$title,$body,$type,0);
        }
    }

    function getdate($second_time){
        $string = "";
        $seconds = time()- $second_time;
        //$days = date('d-M-Y' , strtotime($second_time));
        $year = intval(intval($seconds) / (365 * 24 * 60 * 60));
        $momth = intval(intval($seconds) / (30 * 24 * 60 * 60));
        $weeks = intval(intval($seconds) / (7 * 24 * 60 * 60));
        $days = intval(intval($seconds) / (3600*24));
        $hours = (intval($seconds) / 3600) % 24;
        $minutes = (intval($seconds) / 60) % 60;
        $seconds = (intval($seconds)) % 60;
        //echo $weeks.'======'.$days.'===='. $hours .'====' . $minutes. '=====' . $seconds;exit;
        if($year > 0){
            if($year == 1){
                return  $string .= "$year Year ago";
            }
            return  $string .= "$year Years ago";
        }
        if($momth > 0){
            if($momth == 1){
                return  $string .= "$momth Month ago";
            }
            return  $string .= "$momth Months ago";
        }
        if($weeks > 0){
            if($weeks == 1){
                return  $string .= "$weeks week ago";
            }
            return  $string .= "$weeks weeks ago";
        }
//        if($hours > 24 && $hours < 48){
//                return $string .= "Yesterday";
//        }
//        if($hours > 1 && $hours < 24){
//                return $string .= "Today";
//        }
        if($days > 0){
            if($days == 1){
                return  $string .= "$days day ago";
            }
                return  $string .= "$days days ago";
        }
        if($hours > 0){
            if($hours == 1){
                return $string .= "$hours hour ago";
            }
            return $string .= "$hours hours ago";
        }
        if($minutes > 0){
            if($minutes == 1){
                return $string .= "$minutes minute ago";
            }
            return $string .= "$minutes minutes ago";
        }
        if ($seconds < 59){
                return $string .= "Just now";
        }
    }
}
