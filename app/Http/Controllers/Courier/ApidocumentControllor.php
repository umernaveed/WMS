<?php

namespace App\Http\Controllers\Courier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;
use Yajra\DataTables\Facades\DataTables;
use commonHelper;

class ApidocumentControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index(Request $request) {
        $courier_id = Session::get('courier_id');
        $data['title'] = 'Dashboard';
        $data['class'] = 'dashboard';
        $invoiceTotal = 0;
        $data['setting'] = DB::table('settings')->first();
        $data['outstanding_package'] = DB::table('packages')->where('packages.status' , 1)->where('packages.courier_id' , $courier_id)->count();
        $data['in_transit'] = DB::table('packages')->where('packages.status' , 2)->where('packages.courier_id' , $courier_id)->count();
        $data['outstanding_invoice'] = DB::table('invoices')->where('courier_id' , $courier_id)->where('invoices.status' , 0)->count();
        $outstanding_balance = DB::table('invoices')->where('courier_id' , $courier_id)->where('invoices.status' , 0)->get();
        $totalPayment = commonHelper::getAllPayments($courier_id); 
        $total = commonHelper::getCourierDashboardInvoiceUnpaid($courier_id);
                
        $data['outstanding_balance'] = number_format($total - $totalPayment,2).' USD';
        $data['wherehouse'] = DB::table('packages')->where('packages.courier_id' , $courier_id)->where('packages.status' , 3)->count();
        return view('couriers/api_documents/api_document' , $data);
    }
   public function updateBaseUrl(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url',
        ]);
    
        $courier_id = Session::get('courier_id');
    
        $courier = Courier::find($courier_id);
    
        if ($courier) {
            // Update base_url
            $courier->base_url = $request->base_url;
            $courier->save();
    Session::put('base_url', $request->base_url);
    
            return back()->with('success', 'Base URL updated successfully.');
        } else {
            // Optionally create new courier if it doesn't exist (optional logic)
            // Courier::create(['id' => $courier_id, 'base_url' => $request->base_url]);
    
            return back()->with('error', 'Courier not found.');
        }
    }

    public function testApi(Request $request) {
        try{
    		$courier_id = Session::get('courier_id');
    		$request_type = $request->type;
    		$request_url = $request->url;
    		if($request_type == 'user'){
    		    $responce = $this->getApiUsers($request_url,$courier_id);
    		    return ["status" => true,"message" =>$responce];
    		}elseif($request_type == 'package'){
    		    $responce = $this->createPackage($request_url,$courier_id);
    		    return ["status" => true,"message" =>$responce];
    		}elseif($request_type == 'manifest'){
    		    $responce = $this->createPackageManifest($request_url,$courier_id);
    		    return ["status" => true,"message" =>$responce];
    		}
        }catch(\Exception $e){
            return ["status" => false,"message" =>$e->getMessage()];
        }
		

    }
    
    public function getApiUsers($request_url,$courier_id){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            $url =$request_url;
            //$url = 'http://urtech.com.pk/ctms_2020/public/api/GetUsers';
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{"offset":"0"}',
              CURLOPT_HTTPHEADER => array(
                'api_key: '.$couriers->api_key,
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
    
    public function createPackage($request_url,$courier_id){
        $couriers = Courier::where('id' , $courier_id)->first();
        $tracking_no = $this->generate_password(13);
        $supplier_tracking_no = 'TRACK'.$this->generate_password(20);
        $manifest_no = '10'.substr(str_shuffle('123456789123456789123456789321654987'),0,8);
        $curl = curl_init();
        $url = $request_url;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'","mailbox":"ABC-12345","courier":"Test Courier","supplier_tracking_no":"'.$supplier_tracking_no.'","package_code":"Package Code","weight":"1","price":"10","manifest_no":"'.$manifest_no.'","item_description":"Test Package"}',
            CURLOPT_HTTPHEADER => array(
                'api_key: '.$couriers->api_key,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function createPackageManifest($request_url,$courier_id){
        $couriers = Courier::where('id' , $courier_id)->first();
        $packege_id_new = '2345678,345678,456789';
        $master_air_billing_no = 'test-3456';
        $curl = curl_init();
        $url = $request_url;
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
                'api_key: '.$couriers->api_key,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}
