<?php
namespace App\Classes;
use Session;
use Redirect;
// use Illuminate\Http\Request;
use Request;
use Cookie;
use URL;
use DB;
use App\CommonModel;
use App\Models\Package;
use App\Models\Courier;
use App\Models\Setting;
use App\Models\Activity;
use File;
use App\Models\Invoice;

class CommonLibrary {



    public static function addActivity($type,$dateTime,$message,$admin,$checkPkg,$courier_id){
        $activities = new Activity();
        $activities->type = $type;
        $activities->activity = $message;
        $activities->responce = $checkPkg;
        $activities->courier_id = $courier_id;
        $activities->added_by = $admin;
        $activities->created_at = $dateTime;
        $activities->save();
    }
    
    // public static function getPermission($module_id = '' , $role_id = ''){
    //     $role_permissions = DB::table('role_permissions')->where('module_id' , $module_id)->where('role_id' , $role_id)->first();
    //     return $role_permissions;
    // }

    // public static function getCheckPermission($module_id = ''){
    //     $role_id = Session::get('role_id');
    //     $role_permissions = DB::table('role_permissions')->where('module_id' , $module_id)->where('role_id' , $role_id)->first();
        
    //     return $role_permissions;
    // }

    public static function getUserPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function getLocationPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function getEmailPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->first();
        if($userPermission){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function getVendorPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    // public static function getRolePermission($type = ''){
    //     $role_id = Session::get('role_id');
    //     $userPermission = DB::table('role_modules')
    //                         ->select('role_modules.*','role_permissions.*')
    //                         ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
    //                         ->where('role_modules.type' , $type)
    //                         ->where('role_permissions.role_id' , $role_id)
    //                         ->get();
    //     if(count($userPermission) > 0){
    //         return $userPermission;
    //     }else{
    //         return $userPermission;
    //     }
    // }

    public static function getReportPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function getCategoryPermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules')
                            ->select('role_modules.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules.id')
                            ->where('role_modules.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function deletePackage($base_url, $courier_id, $tracking_no)
{
    $curl = curl_init();
    $couriers = Courier::where('id', $courier_id)->first();
    $url = $base_url . '/api/deletePackage';
    $api_key = $couriers->api_key ?? '';
    
    // Fix: Send the correct parameter name that API expects
    $postData = json_encode(['supplier_tracking_no' => $tracking_no]); // Changed from tracking_no to supplier_tracking_no
    
    $headers = ['Content-Type: application/json'];
    
    if (!empty($api_key)) {
        $headers[] = 'api_key: ' . $api_key;
    }
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30, // Increased timeout
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false // Add this for local/testing
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    
    curl_close($curl);
    
    // Log the response for debugging
    \Log::info('Delete API Response', [
        'url' => $url,
        'tracking_no' => $tracking_no,
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $curlError
    ]);
    
    return $response;
}
    
    public static function getUserName($mailbox = ''){
        $user = DB::table('users')->where('mailbox',  $mailbox)->first();
        if($user){
            return $user->first_name.' '.$user->last_name;
        }else{
            return '';
        }
    }

    public static function getTotalUsers($id = ''){
        return $user = DB::table('users')->where('courier_id',  $id)->count();
       
    }
    
    public static function getCourierName($id = ''){
        $user = DB::table('couriers')->where('id',  $id)->first();
        if($user){
            return $user->name;
        }else{
            return '';
        }
    }
    
    public static function getCourierNameUnknown($tracking_no = ''){
        $package_requests = DB::table('package_requests')->where('tracking_no',  $tracking_no)->first();
        if($package_requests){
            $user = DB::table('couriers')->where('id',  $package_requests->courier_id)->first();
            if($user){
                return $user->name;
            }else{
                return '';
            }
        }else{
            return '';
        }
    }


    public static function getOutletName($mailbox = ''){
        $user = DB::table('users')->where('mailbox',  $mailbox)->first();
        if(!$user || empty($user->outlet_id) || $user->outlet_id == 0){
            return '';
        }

        $outlet = DB::table('outlets')->where('outlet_id',  $user->outlet_id)->first();

        return $outlet->outlet_name ?? '';
    }

    public static function getModues($user_id , $role_id){
        return DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
    }
    
    public static function getAllDashboardInvoiceUnpaid($invoice_id = ''){
        $invoice = Invoice::where('status' ,0)->orderby('invoice_id','ASC')->get();
        //dd($invoice);
        $package_price = 0;
        $services_price = 0;
        if($invoice){
            foreach($invoice as $row1){
                $invPackage = DB::table('invoice_packages')->where('invoice_id' , $row1->invoice_id)->get();
                //dd($invPackage);
                if(count($invPackage) > 0){
                    foreach($invPackage as $inv){
                        $pkPrice = $inv->total;
                        $package_price += $pkPrice;
                    }
                    //return $package_price;
                }
            }
        }
        //return $package_price;
        if($invoice){
            foreach($invoice as $row2){
                $invService = DB::table('service_type')->where('invoice_id' , $row2->invoice_id)->get();
                if(count($invService) > 0){
                    foreach($invService as $serv){
                        $serPrice = $serv->service_fee;
                        $services_price += $serPrice;
                    }
                }
                
            }
        }
        //return $services_price;
        //$package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"))->where('invoices.email_sent' , 1)->where('invoices.status' , 0)->first();
        //dd($package_price);
        //$service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')->select(DB::raw("SUM(service_type.service_fee) as ser_price"))->where('invoices.email_sent' , 1)->where('invoices.status' , 0)->first();
        //dd($service_type);
        //->sum('service_type.service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        //return $package_price->pk_price;
       $subTotal = $package_price + $services_price;
        $total_gct = 0;
        if(count($invoice) > 0){
            foreach($invoice as $row){
                $invPackage = DB::table('invoice_packages')->where('invoice_id' , $row->invoice_id)->sum('total');
                $invService = DB::table('service_type')->where('invoice_id' , $row->invoice_id)->sum('service_fee');
                $invoiceSubTotal = $invPackage + $invService;
                $gct = $invoiceSubTotal * 7 / 100;
                $total_gct += $gct;
            }
        }
        $total_gct;
        $total = $subTotal + $total_gct;
        return $incoiceTotal = $total * $us_rate;
    }
    
    public static function getCourierDashboardInvoiceUnpaid($courier_id = ''){
        //return $courier_id;
        //$invoice = Invoice::where('courier_id' , $courier_id)->where('status' ,0)->where('gct' , 1)->orderby('invoice_id','ASC')->get();
        $invoice = Invoice::where('courier_id' , $courier_id)->where('status' ,0)->orderby('invoice_id','ASC')->get();
        //dd($invoice);
        $package_price = 0;
        $services_price = 0;
        if($invoice){
            foreach($invoice as $row1){
                $invPackage = DB::table('invoice_packages')->where('invoice_id' , $row1->invoice_id)->get();
                //dd($invPackage);
                if(count($invPackage) > 0){
                    foreach($invPackage as $inv){
                        $pkPrice = $inv->total;
                        $package_price += $pkPrice;
                    }
                    //return $package_price;
                }
            }
        }
        //return $package_price;
        if($invoice){
            foreach($invoice as $row2){
                $invService = DB::table('service_type')->where('invoice_id' , $row2->invoice_id)->get();
                if(count($invService) > 0){
                    foreach($invService as $serv){
                        $serPrice = $serv->service_fee;
                        $services_price += $serPrice;
                    }
                }
                
            }
        }
        //return $services_price;
        //$package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"))->where('invoices.email_sent' , 1)->where('invoices.status' , 0)->first();
        //dd($package_price);
        //$service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')->select(DB::raw("SUM(service_type.service_fee) as ser_price"))->where('invoices.email_sent' , 1)->where('invoices.status' , 0)->first();
        //dd($service_type);
        //->sum('service_type.service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        //return $package_price->pk_price;
       $subTotal = $package_price + $services_price;
        $total_gct = 0;
        if(count($invoice) > 0){
            foreach($invoice as $row){
                if($row->gct == 1){
                    $invPackage = DB::table('invoice_packages')->where('invoice_id' , $row->invoice_id)->sum('total');
                    $invService = DB::table('service_type')->where('invoice_id' , $row->invoice_id)->sum('service_fee');
                    $invoiceSubTotal = $invPackage + $invService;
                    $gct = $invoiceSubTotal * 7 / 100;
                    $total_gct += $gct;
                }
                
            }
        }
        $total_gct;
        $total = $subTotal + $total_gct;
        return $incoiceTotal = $total * $us_rate;
    }
    
    public static function getAllPayments($courier_id = ''){
        if($courier_id == 0){
            return $partialAmount = DB::table('payments')->where('status' , 0)->sum('amount');
        }else{
            return $partialAmount = DB::table('payments')->where('courier_id' ,$courier_id)->where('status' , 0)->sum('amount');
        }
        
    }
    
    public static function getRunningBalance($courier_id = ''){
        $invoicePayment = DB::table('payments')->select('invoice_id')->where('courier_id' , $courier_id)->groupby('invoice_id')->get()->pluck('invoice_id');
        //return count($invoicePayment);
        if(count($invoicePayment) > 0){
            $invoice = Invoice::whereIn('invoice_id' , $invoicePayment)->where('gct' , 1)->get();
            //dd($invoice);
            $package_price = DB::table('invoice_packages')->select(DB::raw("SUM(total) as pk_price"))
                                ->whereIn('invoice_id' , $invoicePayment)
                                ->first();
            //return $package_price->pk_price;
            $service_type = DB::table('service_type')->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->whereIn('service_type.invoice_id' , $invoicePayment)
                                ->first();
            //dd($service_type);
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            //return $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = DB::table('invoice_packages')->select(DB::raw("SUM(total) as pk_price"))
                                ->where('invoice_id' , $row->invoice_id)
                                ->first();
                    $pk_service_type = DB::table('service_type')->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoice_id' , $row->invoice_id)
                                ->first();            
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total * $us_rate;
        }else{
            return 0;
        }
        
        
    }
    
    public static function getCourierInvoiceCountEmailUnpaidFilter($start_date='',$end_date = ''){
        if($start_date == '' && $end_date == ''){
            $invoice = Invoice::where('invoices.email_sent' , 0)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                ->where('invoices.email_sent' , 0)
                                //->where('invoices.status' , 0)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                ->where('invoices.email_sent' , 0)
                                //->where('invoices.status' , 0)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                //->where('invoices.status' , 0)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                //->where('invoices.status' , 0)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }else{
            $invoice = Invoice::where('invoices.email_sent' , 0)->whereDate('created_at' , '>=' ,$start_date)->whereDate('created_at' , '<=' ,$end_date)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                ->where('invoices.email_sent' , 0)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                //->where('invoices.status' , 0)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                ->where('invoices.email_sent' , 0)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                //->where('invoices.status' , 0)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                //->where('invoices.status' , 0)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                //->where('invoices.status' , 0)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }
        
        
    }
    
    public static function getCourierInvoiceCountUnpaidFilter($start_date='',$end_date = ''){
        if($start_date == '' && $end_date == ''){
            $invoice = Invoice::where('status' ,0)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                //->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }else{
            $invoice = Invoice::where('status' ,0)->whereDate('created_at' , '>=' ,$start_date)->whereDate('created_at' , '<=' ,$end_date)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                //->where('invoices.email_sent' , 1)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                ->where('invoices.status' , 0)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                ->where('invoices.status' , 0)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 0)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }
        
        
    }
    
    public static function getCourierInvoiceCountPaidFilter($start_date='',$end_date = ''){
        if($start_date == '' && $end_date == ''){
            $invoice = Invoice::where('status' ,1)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                //->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }else{
            $invoice = Invoice::where('status' ,1)->whereDate('created_at' , '>=' ,$start_date)->whereDate('created_at' , '<=' ,$end_date)->where('gct' , 1)->get();
            //dd($invoice);
            // if($invoice){
            //     $service_fee = $invoice->service_fee;
            // }
            $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                //->where('invoices.courier_id' , $courier_id)
                                //->where('invoices.email_sent' , 1)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                ->where('invoices.status' , 1)
                                ->first();
            //dd($package_price);
            $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                //->where('invoices.courier_id' , $courier_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->whereDate('invoices.created_at' , '>=' ,$start_date)
                                ->whereDate('invoices.created_at' , '<=' ,$end_date)
                                ->where('invoices.status' , 1)
                                ->first();
            //dd($service_type);
            $service_type->ser_price;
            //->sum('service_type.service_fee');
            $us_rate = DB::table('settings')->first()->us_rate;
            $package_price->pk_price;
            $subTotal = $package_price->pk_price + $service_type->ser_price;
            $total_gct = 0;
            if(count($invoice) > 0){
                foreach($invoice as $row){
                    $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                              //  ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();
                    $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                                ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                                ->where('invoices.invoice_id' , $row->invoice_id)
                               // ->where('invoices.email_sent' , 1)
                                ->where('invoices.status' , 1)
                                ->first();  
                    $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                    $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
                }
            }
            //return $total_gct;
            $total = $subTotal + $total_gct;
            return $incoiceTotal = $total;
        }
        
        
    }
    
    public static function getCourierInvoiceCountUnpaid($courier_id = ''){
        $invoice = Invoice::where('courier_id' , $courier_id)->where('status' ,0)->where('gct' , 1)->get();
        //dd($invoice);
        // if($invoice){
        //     $service_fee = $invoice->service_fee;
        // }
        $package_price = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                            ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                            ->where('invoices.courier_id' , $courier_id)
                            //->where('invoices.email_sent' , 1)
                            ->where('invoices.status' , 0)
                            ->first();
        //dd($package_price);
        $service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                            ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                            ->where('invoices.courier_id' , $courier_id)
                          //  ->where('invoices.email_sent' , 1)
                            ->where('invoices.status' , 0)
                            ->first();
        //dd($service_type);
        $service_type->ser_price;
        //->sum('service_type.service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price->pk_price;
        $subTotal = $package_price->pk_price + $service_type->ser_price;
        $total_gct = 0;
        if(count($invoice) > 0){
            foreach($invoice as $row){
                $package_total = Invoice::join('invoice_packages','invoice_packages.invoice_id','=','invoices.invoice_id')
                            ->select(DB::raw("SUM(invoice_packages.total) as pk_price"))
                            ->where('invoices.invoice_id' , $row->invoice_id)
                          //  ->where('invoices.email_sent' , 1)
                            ->where('invoices.status' , 0)
                            ->first();
                $pk_service_type = Invoice::join('service_type','service_type.invoice_id','=','invoices.invoice_id')
                            ->select(DB::raw("SUM(service_type.service_fee) as ser_price"))
                            ->where('invoices.invoice_id' , $row->invoice_id)
                           // ->where('invoices.email_sent' , 1)
                            ->where('invoices.status' , 0)
                            ->first();  
                $PKsubTotal = $package_total->pk_price + $pk_service_type->ser_price;            
                $total_gct = $total_gct + ($PKsubTotal * 7 / 100);
            }
        }
        //return $total_gct;
        $total = $subTotal + $total_gct;
        return $incoiceTotal = $total;
        
    }

    public static function getInvoiceCountOld($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->sum('total');
        $service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $subTotal = $package_price + $service_type + $service_fee;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
             $gct = $subTotal * 7 / 100;
        }
        $total = $subTotal + $gct;
        return $incoiceTotal = $total * $us_rate;
    }

    public static function getInvoicePackages($invoice_id = ''){
        return $packages = DB::table('invoice_packages')
                                ->select('packages.*','users.first_name','users.location','users.address_1','package_status.name','outlets.outlet_name')
                                ->join('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                ->join('package_status', 'package_status.id', '=', 'packages.status')
                                ->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where('invoice_packages.status' , 0)
                                ->where('invoice_packages.invoice_id' , $invoice_id)
                                ->get();
    }

    public static function getInvoice($tracking_no){
        $invoice = Invoice::where('tracking_no', '=', $tracking_no)->first();
        if($invoice){
            return $invoice->invoice_no;
        }else{
            return 0;
        }
    }

    

    public static function getAllInvoiceCountUnpaid($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }else{
            $service_fee = 0;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('package_price');
        $custom_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('custom_fee');
        $service_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('service_fee');
        //$service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price = ($package_price + $custom_fee + $service_fee) * $us_rate;
        //$service_fees = $service_fee * $us_rate;
        //$service_type = $service_type * $us_rate;
        $sub_total = $package_price;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
            if($sub_total > 0){
                $gct = $sub_total * 7 / 100;
            }else{
                $gct = 0;
            }
        }
        $total = $sub_total + $gct;
        return $total;
    }

    public static function getAllInvoiceCount($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }else{
            $service_fee = 0;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->sum('package_price');
        $custom_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->sum('custom_fee');
        $service_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->sum('service_fee');
        //$service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price = ($package_price + $custom_fee + $service_fee) * $us_rate;
        //$service_fees = $service_fee * $us_rate;
        //$service_type = $service_type * $us_rate;
        $sub_total = $package_price;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
            if($sub_total > 0){
                $gct = $sub_total * 7 / 100;
            }else{
                $gct = 0;
            }
        }
        $total = $sub_total + $gct;
        return $total;
    }

    public static function getInvoiceCount($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }else{
            $service_fee = 0;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('package_price');
        $custom_fee = DB::table('invoice_packages')->where('status' , 0)->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('custom_fee');
        $service_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('service_fee');
        //$service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price = ($package_price + $custom_fee + $service_fee) * $us_rate;
        //$service_fees = $service_fee * $us_rate;
        //$service_type = $service_type * $us_rate;
        $sub_total = $package_price;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
            if($sub_total > 0){
                $gct = $sub_total * 7 / 100;
            }else{
                $gct = 0;
            }
        }
        $total = $sub_total + $gct;
        return number_format($total,2);
    }

    public static function getInvoiceCountPaid($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }else{
            $service_fee = 0;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 1)->sum('package_price');
        $custom_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 1)->sum('custom_fee');
        $service_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 1)->sum('service_fee');
        //$service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price = ($package_price + $custom_fee + $service_fee) * $us_rate;
        //$service_fees = $service_fee * $us_rate;
        //$service_type = $service_type * $us_rate;
        $sub_total = $package_price;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
            if($sub_total > 0){
                $gct = $sub_total * 7 / 100;
            }else{
                $gct = 0;
            }
        }
        $total = $sub_total + $gct;
        return number_format($total,2);
    }

    public static function getInvoiceOutstanding($invoice_id = ''){
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        if($invoice){
            $service_fee = $invoice->service_fee;
        }else{
            $service_fee = 0;
        }
        $package_price = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('package_price');
        $custom_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('custom_fee');
        $service_fee = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('service_fee');
        //$service_type = DB::table('service_type')->where('invoice_id' , $invoice_id)->where('status' , 0)->sum('service_fee');
        $us_rate = DB::table('settings')->first()->us_rate;
        $package_price = ($package_price + $custom_fee + $service_fee) * $us_rate;
        $sub_total = $package_price;
        if($invoice->gct == 0){
            $gct = 0;
        }else{
            if($sub_total > 0){
                $gct = $sub_total * 7 / 100;
            }else{
                $gct = 0;
            }
        }
        return $total = $sub_total + $gct;
    }

    public static function getPackageAmount($tracking_no = ''){
        //return $tracking_no;
        $us_rate = DB::table('settings')->first()->us_rate;
        $package = DB::table('invoice_packages')->join('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')->where('invoice_packages.tracking_no' , $tracking_no)->first();
        //dd($package_price);

        if($package){
            $custom_fee = $package->custom_fee  * $us_rate;
            $service_fee = $package->service_fee  * $us_rate;
            $package_price = $package->package_price * $us_rate;
        }else{
            $service_fee = 0;
            $custom_fee = 0;
            $package_price = 0;
        }

        //return $price;
        // $package_price = DB::table('invoice_packages')->where('tracking_no' , $tracking_no)->sum('package_price');
        // $custom_fee = DB::table('invoice_packages')->where('tracking_no' , $tracking_no)->sum('custom_fee');

        // $subTotal = $package_price + $price;
        // $total = $subTotal;
        // return $custom_fee = $custom_fee * $us_rate;
        // return $incoiceTotal = $total * $us_rate;
        return number_format($package_price + $custom_fee + $service_fee, 2, '.', '');
    }

    public static function textLimit($text,$limit){
        if(strlen($text) > $limit){
            return $text  = substr($text,0,$limit).'...';
        }else{
          return  $text;
        }
    }

    public static function getSettings(){
        return $settings = Setting::first();
    }
     public static function getCheckPermission($module_id = ''){
        if(self::isAdminSuperUser()){
            return (object) ['allowed' => true];
        }
        $role_id = Session::get('role_id');
        $role_permissions = DB::table('role_permissions')->where('module_id' , $module_id)->where('role_id' , $role_id)->first();
        return $role_permissions;
    }
 public static function getPermission($module_id = '' , $role_id = ''){
        $role_permissions = DB::table('role_permissions')->where('module_id' , $module_id)->where('role_id' , $role_id)->first();
        return $role_permissions;
    }
    public static function getRolePermission($type = ''){
        $role_id = Session::get('role_id');
        $userPermission = DB::table('role_modules_new')
                            ->select('role_modules_new.*','role_permissions.*')
                            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules_new.id')
                            ->where('role_modules_new.type' , $type)
                            ->where('role_permissions.role_id' , $role_id)
                            ->get();
        if(count($userPermission) > 0){
            return $userPermission;
        }else{
            return $userPermission;
        }
    }

    public static function isAdminSuperUser(){
        return (int) Session::get('is_superadmin') === 1 || (int) Session::get('role_id') === 1;
    }

    public static function canAccessAny($permissions = []){
        if(self::isAdminSuperUser()){
            return true;
        }

        foreach((array) $permissions as $permission){
            if(self::canAccess($permission)){
                return true;
            }
        }

        return false;
    }

    public static function canAccess($permission = ''){
        if(self::isAdminSuperUser()){
            return true;
        }

        $role_id = Session::get('role_id');
        if($role_id === null || $role_id === ''){
            return false;
        }

        if(is_numeric($permission)){
            return self::getPermission($permission, $role_id) != null;
        }

        $permission = trim((string) $permission);
        if($permission === ''){
            return false;
        }

        if(!DB::getSchemaBuilder()->hasTable('role_permissions') || !DB::getSchemaBuilder()->hasTable('role_modules_new')){
            return true;
        }

        $value = strtolower($permission);
        $slug = self::permissionSlug($permission);
        $slugAlt = substr($slug, -1) === 's' ? rtrim($slug, 's') : $slug.'s';
        $allowed = self::allowedPermissionTokens($role_id);

        if(in_array($value, $allowed) || in_array($slug, $allowed) || in_array($slugAlt, $allowed)){
            return true;
        }

        foreach($allowed as $token){
            if($slug !== '' && (strpos($token, $slug) !== false || strpos($slug, $token) !== false || strpos($token, $slugAlt) !== false || strpos($slugAlt, $token) !== false)){
                return true;
            }
        }

        return false;
    }

    public static function canAccessAdminPath($path = ''){
        if(self::isAdminSuperUser()){
            return true;
        }

        $path = trim((string) $path, '/');
        $allowed = [
            'admin/dashboard',
            'admin/logout',
            'admin/profile',
            'admin/change/password',
            'admin/global-search',
        ];

        if(in_array($path, $allowed)){
            return true;
        }

        foreach(self::adminPermissionMap() as $pattern => $permissions){
            if(self::pathMatchesPermissionPattern($path, $pattern)){
                return self::canAccessAny($permissions);
            }
        }

        return true;
    }

    private static function adminPermissionMap(){
        return [
            'admin/packege/add' => ['Log Packages', 'Packages'],
            'admin/packeges/logs/*' => ['Package Management', 'Packages'],
            'packeges-list/*' => ['Package Management', 'Packages'],
            'admin/package/mass-resend*' => ['Mass Resend Packages', 'Packages'],
            'admin/package/request*' => ['Package Requests', 'Packages', 'Unknowns'],
            'admin-request-list*' => ['Package Requests', 'Packages', 'Unknowns'],
            'admin/packege/import*' => ['Bulk Package Import', 'Packages'],
            'admin/packeges/unknown/*' => ['General Unknowns', 'Unknowns'],
            'admin/users' => ['Clients', 'CRM', 'Accounts'],
            'users-list' => ['Clients', 'CRM', 'Accounts'],
            'admin/user/add' => ['Clients', 'CRM', 'Accounts'],
            'admin/user/edit/*' => ['Clients', 'CRM', 'Accounts'],
            'admin/users/list' => ['Employees', 'Employee', 'Accounts'],
            'admin/user/create' => ['Employees', 'Employee', 'Accounts'],
            'admin/edit/*' => ['Employees', 'Employee', 'Accounts'],
            'admin/couriers*' => ['Couriers', 'CRM', 'Accounts'],
            'admin/courier/*' => ['Couriers', 'CRM', 'Accounts'],
            'admin/outlets*' => ['Outlets', 'CRM'],
            'admin/portal/support*' => ['Support', 'CRM'],
            'send/email*' => ['Send Email', 'CRM'],
            'admin/list/manifest*' => ['Create Manifest', 'Manifest'],
            'admin/package/manifest/*' => ['Manage Manifest', 'Manifest'],
            'admin/create/manifest*' => ['Build Master Container', 'Manifest'],
            'admin/manifest/*' => ['Manifest'],
            'admin/portal/shipment-bookings*' => ['Shipment (Air/Sea)', 'Bookings'],
            'admin/portal/flight-schedules*' => ['Flight Schedule', 'Bookings'],
            'admin/portal/sea-vessel-schedules*' => ['Sea Vessel Schedules', 'Bookings'],
            'admin/portal/warehouse-supplies*' => ['Warehouse Supplies', 'Operations'],
            'admin/portal/internal-inventory*' => ['Internal Inventory', 'Operations'],
            'admin/portal/internal-waste*' => ['Log Internal Waste', 'Operations'],
            'admin/carriers*' => ['Shipping Partners', 'Operations', 'Settings'],
            'admin/package/status*' => ['Tracking Status', 'Operations'],
            'admin/status/*' => ['Tracking Status', 'Operations'],
            'admin/pickup/location*' => ['Pickup Locations', 'Operations'],
            'admin/areas*' => ['Delivery Areas', 'Operations'],
            'admin/delivery/packages*' => ['Delivery Packages', 'Operations'],
            'admin/invoices*' => ['Courier Billing', 'View Invoices', 'Finance'],
            'invoices-*' => ['Courier Billing', 'View Invoices', 'Finance'],
            'admin/invoice/add*' => ['Create Invoice', 'Finance'],
            'admin/invoice/*' => ['View Invoices', 'Finance'],
            'admin/portal/finance-entries*' => ['Income / Expense', 'Finance'],
            'admin/portal/reports*' => ['Reports (All Modules)', 'Profit / Loss', 'Balance Sheet', 'Finance'],
            'admin/settings*' => ['General Settings', 'Settings'],
            'admin/portal-settings*' => ['SMTP Integration', 'API Integration', 'Settings'],
            'admin/roles*' => ['Roles & Permissions', 'Settings'],
            'admin/permission/assign*' => ['Permission Matrix', 'Settings'],
            'admin/role/permission/*' => ['Permission Matrix', 'Settings'],
            'admin/assign/role/*' => ['Permission Matrix', 'Settings'],
            'admin/unassign/role/*' => ['Permission Matrix', 'Settings'],
            'admin/weight*' => ['Weight Groups', 'Settings'],
            'weight-list*' => ['Weight Groups', 'Settings'],
            'admin/package/type*' => ['Package Types', 'Settings'],
            'admin/activies*' => ['Activity Log', 'Settings'],
            'activies-list*' => ['Activity Log', 'Settings'],
        ];
    }

    private static function pathMatchesPermissionPattern($path, $pattern){
        $pattern = trim($pattern, '/');
        $regex = '#^'.str_replace('\*', '.*', preg_quote($pattern, '#')).'$#i';
        return (bool) preg_match($regex, $path);
    }

    private static function permissionSlug($permission){
        return preg_replace('/[^a-z0-9]/', '', strtolower((string) $permission));
    }

    private static function allowedPermissionTokens($role_id){
        static $cache = [];

        if(isset($cache[$role_id])){
            return $cache[$role_id];
        }

        $columns = ['main_title', 'sub_title', 'title', 'name', 'lable', 'label', 'type', 'url', 'route', 'path', 'slug'];
        $select = ['role_modules_new.id'];
        foreach($columns as $column){
            if(DB::getSchemaBuilder()->hasColumn('role_modules_new', $column)){
                $select[] = 'role_modules_new.'.$column;
            }
        }

        $modules = DB::table('role_modules_new')
            ->select($select)
            ->join('role_permissions', 'role_permissions.module_id', '=', 'role_modules_new.id')
            ->where('role_permissions.role_id', $role_id)
            ->get();

        $tokens = [];
        foreach($modules as $module){
            foreach($columns as $column){
                if(isset($module->{$column}) && trim((string) $module->{$column}) !== ''){
                    $value = strtolower(trim((string) $module->{$column}));
                    $tokens[] = $value;
                    $tokens[] = self::permissionSlug($value);
                }
            }
        }

        return $cache[$role_id] = array_values(array_unique($tokens));
    }

}

?>
