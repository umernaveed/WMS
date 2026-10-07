<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use phpDocumentor\Reflection\Types\Self_;
use Illuminate\Support\Facades\Hash;
use App\Models\UserLoginData;
use App\Models\User;
use App\Models\Courier;
use App\Models\Package;
use App\Models\UserAddress;
use App\Models\Category;
use App\Models\Area;
use App\Models\Product;
use App\Models\Follower;
use App\Models\Order;
use App\Models\TransactionItem;
use App\Models\Transaction;
use App\Models\CardDetail;
use App\Models\ClosePallyUser;
use App\Models\Setting;
use App\Models\Copoun;
use App\Models\CouponUser;
use App\Models\CartItem;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Stripe\Error\Card;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Paystack;
use Session;
//use Barryvdh\DomPDF\PDF;
use Barryvdh\DomPDF\Facade as PDF;
use DB;
use Mail;
use URL;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    
    public  function __construct()
    {

    }
    
    public function updatePackageStatusNew(Request $request){
        //try{
            $packages = Package::where('courier_id' , '1')->where('status' , 2)->get();
            dd(count($packages));
            $packagesIdArr = [];
            if(count($packages) > 0){
                $couriers = Courier::where('id' , 1)->first();
                foreach($packages as $row){
                    $packagesIdArr[] = $row->tracking_no;
                }
                $packagesIds = implode(',' , $packagesIdArr);
                //dd($packagesIds);
                $status = 2;
                $status_name = 'IN TRANSIT';
                return $checkPkg = $this->updatePackageStatus($couriers->base_url,$packagesIds,$status,$status_name);
            }
            
        //}catch(\Exception $e){
            //return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        //}
    }
    
    public function updateManifestStatus(Request $request){
        //try{
            $packages = DB::table('manifest_packeges')->select('supplier_tracking_no')->join('packages','packages.packeg_id','=','manifest_packeges.packeges_id')->where('manifest_id' , '54')->get();
            //dd($packages);
            $packagesIdArr = [];
            if(count($packages) > 0){
                $couriers = Courier::where('id' , 1)->first();
                foreach($packages as $row){
                    $packagesIdArr[] = $row->supplier_tracking_no;
                }
                //return count($packagesIdArr);
                $packagesIds = implode(',' , $packagesIdArr);
                //dd($packagesIds);
                $status = 2;
                $status_name = 'IN TRANSIT';
                $master_air_billing_no = '12345';
                return $checkPkg = $this->createPackageManifest($couriers->base_url,$packagesIds,$master_air_billing_no);
            }
            
        //}catch(\Exception $e){
            //return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        //}
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
      //  return'hhhh';
        $curl = curl_init();
        $url = $base_url.'/api/updatePackageStatus';
        //dd($url);
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
    
    public function getPackages(Request $request){
        try{
            $packages = Package::where('courier_id' , '4')->whereDate('created_at', '>','2023-11-02')->whereDate('created_at', '<','2023-11-04')->get();
            //dd($packages);
            if($packages){
                foreach($packages as $row){
                    $package = Package::where('tracking_no' , $row->tracking_no)->first();
                        $couriers = Courier::where('id' , 4)->first();
                        //dd($couriers);
                        //$tracking_no = 'tyru64ur8di5';
                        //$supplier_tracking_no = 'tycrttru64ddur8i5';
                        $supplier_tracking_no = $package->supplier_tracking_no;
                        $manifest_no = $package->manifest_no;
                        $weight = $row->weight;
                        if($weight > 76){
                            $weightRes = DB::table('weights')->where('weight' , 76)->first();
                            if($weightRes){
                                $price = $weightRes->price;
                            }
                        }else{
                            $weightRes = DB::table('weights')->where('weight' , $weight)->first();
                            if($weightRes){
                                $price = $weightRes->price;
                            }
                        }
                    $checkPkg = $this->resendPackageApi($couriers->base_url,4,$row->tracking_no,$supplier_tracking_no,$package->mailbox,$package->courier,$weight,$price,$package->package_type,0,$package->item_description,$manifest_no);
                }
            }
            
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        }
    }
    
    public function resendPackageApi($base_url,$courier_id,$tracking_no,$supplier_tracking_no,$mailbox,$courier,$weight,$price,$package_type,$category_id,$item_description,$manifest_no){
        //return $supplier_tracking_no;
        $curl = curl_init();
        $couriers = Courier::where('id' , $courier_id)->first();
        $url = $base_url.'/api/resendPackage';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'","mailbox":"'.$mailbox.'","courier":"'.$courier.'","supplier_tracking_no":"'.$supplier_tracking_no.'","package_type":"'.$package_type.'","package_code":"'.$category_id.'","weight":"'.$weight.'","price":"'.$price.'","manifest_no":"'.$manifest_no.'","item_description":"'.$item_description.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function saveUser(Request $request){
        try{
            $checkUser = User::where('courier_id' , $request->courier_id)->where('mailbox',$request->mailbox)->first();
            if($checkUser){
                User::where('courier_id' , $request->courier_id)->where('mailbox',$request->mailbox)->update(['first_name' => $request->first_name,'last_name' => $request->last_name,'email' => $request->email,'user_name' =>$request->first_name.' '.$request->last_name]);
                Log::info("Courier ID - ".$request->courier_id.':'.$request->mailbox.' This user already exists');
                return response()->json(['status'=>"success","message"=>'This user already exists.'],200);
            }else{
                $users = new User();
                $users->mailbox = $request->mailbox;
                $users->first_name = $request->first_name;
                
                $users->outlet_id = $request->outlet_id;
                $users->courier_id = $request->courier_id;
                $users->last_name = $request->last_name;
                $users->user_name = $request->first_name.' '.$request->last_name;
                $users->email = $request->email;
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();
                Log::info("Courier ID - ".$request->courier_id.':'.$request->mailbox.' User has been inserted successfully');
                return response()->json(['status'=>"success","message"=>'User has been inserted successfully'],200);
            }
            
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        }
    }
    
    public function updateUser(Request $request){
        try{
            $checkUser = User::where('courier_id' , $request->courier_id)->where('mailbox',$request->mailbox)->first();
            if($checkUser){
                User::where('courier_id' , $request->courier_id)->where('mailbox',$request->mailbox)->update(['first_name' => $request->first_name,'last_name' => $request->last_name,'email' => $request->email,'user_name' =>$request->first_name.' '.$request->last_name]);
                Log::info("Courier ID - ".$request->courier_id.':'.$request->mailbox.' This user update successfull');
                return response()->json(['status'=>"success","message"=>'User has been updated successfully'],200);
            }else{
                Log::info("Courier ID - ".$request->courier_id.':'.$request->mailbox.' User Not found');
                return response()->json(['status'=>"success","message"=>'User Not found'],200);
            }
            
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        }
    }
    
    public function updateManifestNumber(Request $request){
        try{
            $manifest_no = explode('PK',$request->manifest_no);
            $checkUser = Package::where('supplier_tracking_no' , $request->supplier_tracking_no)->update(['manifest_no' => $manifest_no[1]]);
            
            return response()->json(['status'=>"success","message"=>'Manifest no has been updated successfully'],200);
            
            
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        }
    }

    public function GetUsers(Request $request){
        try{
            $courier_id = $request->courier_id;
            $offset = $request->offset;
            $userArray = [];
            $responce = $this->getApiUsers($courier_id,$offset);
            $result = json_decode($responce,true);
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

                $payload = [
                    'mailbox' => $mailbox,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $row['email'] ?? '',
                    'phone' => $row['phone'] ?? '',
                    'user_name' => trim($firstName.' '.$lastName) ?: $name,
                    'courier_id' => $courier_id,
                ];

                $user = User::where('mailbox',$mailbox)->where('courier_id',$courier_id)->first();
                if($user == null){
                    $userArray[] = $payload;
                }else{
                    User::where('mailbox',$mailbox)->where('courier_id',$courier_id)->update($payload);
                }
            }

            if(count($userArray) > 0){
                User::insert($userArray);
                return 'user inserted';
            }

            return 'No user inserted';
            $responce = $this->getApiUsers($courier_id);
            $settings = DB::table('settings')->first();
            if($settings){
                return response()->json(['status'=>"success","message"=>'Youtube Key found.','youtube_key' => $settings->youtube_key,'payment_mod' => $settings->payment_mod,'api_key' => $api_key,'contract' => $contract],200,[],JSON_NUMERIC_CHECK);
            }else{
                return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200,[],JSON_NUMERIC_CHECK);
            }
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
        }
    }
    
    public function getApiUsers($courier_id = '',$offset = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            if(!$couriers || empty($couriers->base_url)){
                return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
            }

            $headers = ['Content-Type: application/json'];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }

            $url = rtrim($couriers->base_url, '/').'/api/GetUsers';
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode(['offset' => $offset]),
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
    
    public function GetPackagesTypesAdd(Request $request){
        try{
            $courier_id = $request->courier_id;
            $offset = $request->offset;
            $categoryArray = [];
             $responceTypes = $this->GetPackagesTypes($courier_id);
            $resultType = json_decode($responceTypes,true);
            if(isset($resultType['status']) && $resultType['status'] == 'success'){
                if(count($resultType['data']) > 0){
                    foreach($resultType['data'] as $row){
                        $package_categories = DB::table('types')->where('TypeCode',$row['TypeCode'])->first();
                        if($package_categories == null){
                            $categoryArray[] = $row;
                        }
                        
                    }
                    DB::table('types')->insert($categoryArray);
                    return 'Types inserted';
                    $data['types'] = $resultType['data'];
                }else{
                    $data['types'] = [];
                }
                
            }else{
                $data['types'] = [];
            }
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
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
    
    public function GetVendorsAdd(Request $request){
        try{
            $courier_id = $request->courier_id;
            $offset = $request->offset;
            $categoryArray = [];
            $responceVendor = $this->GetVendors($courier_id);
            $resultVendor = json_decode($responceVendor,true);
            if(isset($resultVendor['status']) && $resultVendor['status'] == 'success'){
                if(count($resultVendor['data']) > 0){
                    foreach($resultVendor['data'] as $row){
                        $package_categories = DB::table('vendors')->where('name',$row['name'])->first();
                        if($package_categories == null){
                            $categoryArray[] = $row;
                        }
                        
                    }
                    DB::table('vendors')->insert($categoryArray);
                    return 'Vendor inserted';
                    $data['vendors'] = $resultVendor['data'];
                }else{
                    $data['vendors'] = [];
                }
                
            }else{
                $data['vendors'] = [];
            }
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
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
    
    public function GetPackageCategories(Request $request){
        try{
            $courier_id = $request->courier_id;
            $offset = $request->offset;
            $categoryArray = [];
            $responceCategory = $this->GetCategories($courier_id);
            $resultCategory = json_decode($responceCategory,true);
            if(isset($resultCategory['status']) && $resultCategory['status'] == 'success'){
                if(count($resultCategory['data']) > 0){
                    foreach($resultCategory['data'] as $row){
                        $package_categories = DB::table('package_categories')->where('title',$row['title'])->first();
                        if($package_categories == null){
                            $categoryArray[] = $row;
                        }
                        
                    }
                    DB::table('package_categories')->insert($categoryArray);
                    return 'category inserted';
                    //dd($categoryArray);
                    //$data['categories'] = $resultCategory['data'];
                }else{
                    $data['categories'] = [];
                }
                
            }else{
                $data['categories'] = [];
            }
        }catch(\Exception $e){
            return response()->json(['status'=>"failed","message"=>$e->getMessage(),"user_access" => 1],200,[],JSON_NUMERIC_CHECK);
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
    
    /**
 * Show mass resend page with courier-wise package counts
 */

	
}
