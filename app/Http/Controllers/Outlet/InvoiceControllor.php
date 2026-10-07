<?php
namespace App\Http\Controllers\Outlet;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\PackageStatus;
use App\Models\Weight;
use App\Models\User;

use Symfony\Component\Console\Input\Input;

use Session;
use Validator;
use DB;
use PDF;
use URL;
use DateTime;
use DateInterval;
use Yajra\DataTables\Facades\DataTables;
use commonHelper;

class InvoiceControllor extends Controller
{
	
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function view(){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        return view("outlet.invoices.list",$data);
    }

    public function listView()
    {
        $outlet_id= Session::get('outlet_id');
        $data = DB::table('invoices')
                    ->select('invoices.*','users.first_name','users.last_name')
                    ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                    ->where('invoices.email_sent' , 0)
                    ->where('users.outlet_id' , $outlet_id);
                    //->get();
        //dd($data);
        return DataTables::of($data)
                ->editColumn('totalInv', function ($data) {
                    $totalInv = commonHelper::getInvoiceCount($data->invoice_id);
                    return $totalInv.' JMD';
                })
                ->addColumn('action', function($data){
                    $btn = "<a href='".url('/')."/outlet/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a>";
                    return $btn;
                })
                ->make(true);
    }
	
    public function index(){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $outlet_id = Session::get('outlet_id');
        $data['invoices'] = DB::table('invoices')
                                ->select('invoices.*','users.first_name','users.last_name')
                                ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                                ->where('invoices.email_sent' , 0)
                                ->where('users.outlet_id' , $outlet_id)
                                ->get();
        return view('outlet/invoices/index' , $data);
    }
    
    public function emailInvoice(){
        $data['title'] = 'Manage Email Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Email Invoices';
        $user_id = Session::get('user_id');
        $role_id= Session::get('role_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['invoices'] = DB::table('invoices')
                                ->select('invoices.*','users.first_name','users.last_name')
                                ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                                ->where('invoices.email_sent' , 1)
                                ->get();
        return view('admin/invoices/emailInv' , $data);
    }
    
    public function userInvoices(){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $mailbox = Session::get('mailbox');
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['invoices'] = DB::table('invoices')
                                ->select('invoices.*','users.first_name','users.last_name')
                                ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                                ->where('invoices.mailbox_no' , $mailbox)
                                ->get();
        return view('admin/invoices/user_index' , $data);
    }
	
    public function create(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $mailbox = Session::get('mailbox');
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            DB::table('invice_temp')->delete();
            Session::put('mailbox' , '');
            $data['users'] = User::where('role_id' , 0)->get();
            return view('admin/invoices/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                    'mailbox' => 'required',
                                    'tracking_no' => 'required',
                                    'service_fee' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/invoice/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                
//                $weightUpdate = Package::where('tracking_no' , $request->tracking_no)->update(['weight' => $request->weight]);
//                $weight = $request->weight;
//                $location = User::where('mailbox' , $request->mailbox)->first()->location;
//                $supplier_invoice = $request->supplier_invoice;
//                if($weight == '0.5'){
//                    $actuallWeight = $weight;
//                }else{
//                    $actuallWeight = round($weight);
//                }
                //echo $service_fee;exit;
                $gct = isset($request->gct) ? 1 : 0;
                $invoices = new Invoice();
                $invoices->invoice_no = time();
                $invoices->mailbox_no = $request->mailbox;
                $invoices->service_fee = $request->service_fee;
                $invoices->gct = $gct;
                $invoices->status = '0';
                $invoices->created_at = date('Y-m-d h:i:s');
                $invoices->updated_at = date('Y-m-d h:i:s');
                $invoices->save();
                $invoice_id = $invoices->invoice_id;
                $item_description = $request->item_description;
                $supplier_invoice = $request->supplier_invoice;
                $weight = $request->weight;
                $tracking_no = $request->tracking_no;
                if(!empty($item_description)){
                    for ($i = 0; $i < count($item_description); $i++) {
                        $pack_dataArr = array();
                        $pack_dataArr['invoice_id'] = $invoice_id;
                        $pack_dataArr['tracking_no'] = $tracking_no[$i];
                        $pack_dataArr['package_description'] = $item_description[$i];
                        $pack_dataArr['package_price'] = $supplier_invoice[$i];
                        $pack_dataArr['package_weight'] = $weight[$i];
                        if($item_description[$i]){
                            DB::table('invoice_packages')->insert($pack_dataArr);			
                        }
                    }
                }
                $name = $request->name;
                $custome_fee = $request->custome_fee;
                if(!empty($name)){
                    for ($i = 0; $i < count($name); $i++) {
                        $service_dataArr = array();
                        $service_dataArr['invoice_id'] = $invoice_id;
                        $service_dataArr['name'] = $name[$i];
                        $service_dataArr['service_fee'] = $custome_fee[$i];
                        if($name[$i]){
                            DB::table('service_type')->insert($service_dataArr);			
                        }
                    }
                }
                
                
                
                //Save invoice in pdf
                $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
                $data['invoice'] = $invoice;
                $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice_id)->get();
                $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->get();
                $data['settings'] = DB::table('settings')->where('sessting_id' , 1)->first();
                $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
                $us_rate = DB::table('settings')->first()->us_rate;
                $data['usRate'] = $us_rate;
                $unique = time().'_invoice.pdf';
                //return view('admin/invoices/invoice_print_pdf' , $data);
                $pdfpathforgenerate = 'public/invoices/'.$unique;
                $pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data);
                $pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data)->save($pdfpathforgenerate);
                Invoice::where('invoice_id' , $invoice_id)->update(['pdf_invoice' => $unique]);
                
                return redirect('admin/invoices');
            }
        }
    }
        
    public function findPackeg(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            $mailbox = Session::get('mailbox');
            $usersRes = Package::where('mailbox' , $mailbox)->get();
            //dd($usersRes);
            $data['packeges'] = $usersRes;
            $data['mailbox'] = $mailbox;
            return view('admin/invoices/create1' , $data);
        }else{
            $mailbox = $request->mailbox;
            Session::put('mailbox' , $mailbox);
            $usersRes = Package::where('mailbox' , $mailbox)->get();
            $data['packeges'] = $usersRes;
            $data['mailbox'] = $mailbox;
            return view('admin/invoices/create1' , $data);
        }      
    }
    
    public function createInvoiceold(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $mailbox = $request->mailbox;
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            $usersRes = Package::where('mailbox' , $mailbox)->get();
            $data['packeges'] = $usersRes;
            $data['mailbox'] = $mailbox;
            $data['total_rows'] = Session::get('total_rows');
            return view('admin/invoices/create2' , $data);
        }else{
            $mailbox = Session::get('mailbox');
            $total_rows = $request->total_rows;
            Session::put('total_rows' , $total_rows);
            $usersRes = Package::where('mailbox' , $mailbox)->get();
            $data['packeges'] = $usersRes;
            $data['total_rows'] = Session::get('total_rows');
            $data['mailbox'] = $mailbox;
            return view('admin/invoices/create2' , $data);
        }      
    }
    
    public function createInvoice(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $mailbox = $request->mailbox;
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            $usersRes = Package::where('mailbox' , $mailbox)->get();
            $data['packeges'] = $usersRes;
            $data['mailbox'] = $mailbox;
            $data['total_rows'] = Session::get('total_rows');
            return view('admin/invoices/create2' , $data);
        }else{
            $invoices = new Invoice();
            $invoices->invoice_no = time();
            $invoices->tracking_no = $request->tracking_no;
            $invoices->mailbox_no = $request->mailbox_no;
            $invoices->type = $request->type;
            $invoices->gct = $request->gct;
            $invoices->weight = $request->weight;
            $invoices->processing_fee = $request->processing_fee;
            $invoices->CAF = $request->CAF;
            $invoices->ENV_levy = $request->ENV_levy;
            $invoices->service_fee = $request->service_fee;
            $invoices->SCF = $request->SCF;
            $invoices->status = '0';
            $invoices->added_by = $user_id;
            $invoices->created_at = date('Y-m-d h:i:s');
            $invoices->updated_at = date('Y-m-d h:i:s');
            $invoices->save();
            return redirect('admin/invoices');
        }      
    }
    
    public function submitInvoice(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $invoices = new Invoice();
        $invoices->invoice_no = time();
        $invoices->tracking_no = $request->tracking_no;
        $invoices->mailbox_no = $request->mailbox_no;
        $invoices->type = $request->type;
        $invoices->gct = $request->gct;
        $invoices->status = '0';
        $invoices->added_by = $user_id;
        $invoices->created_at = date('Y-m-d h:i:s');
        $invoices->updated_at = date('Y-m-d h:i:s');
        $invoices->save();
        $invoice_no = $invoices->invoice_no;
        $name = $request->name;
        $usd = $request->usd;
        $jmd = $request->jmd;
        if(!empty($name)){
            for ($i = 0; $i < count($name); $i++) {
                $service_dataArr = array();
                $service_dataArr['invoice_no'] = $invoice_no;
                $service_dataArr['name'] = $name[$i];
                $service_dataArr['usd'] = $usd[$i];
                $service_dataArr['jmd'] = $jmd[$i];
                $service_dataArr['created_at'] = date('Y-m-d h:i:s');
                $service_dataArr['updated_at'] = date('Y-m-d h:i:s');
                if($name[$i]){
                    DB::table('service_type')->insert($service_dataArr);			
                }
            }
        }
        return redirect('admin/invoices');
    }

    public function edit(Request $request , $invoice_id = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Edit Invoices';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
            DB::table('invice_temp')->delete();
            $data['users'] = User::where('role_id' , 0)->get();
            $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
            $data['invoice'] = $invoice;
            $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice_id)->get();
            $data['invoice_packages'] = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->get();
            
            //DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
            $data['packagesInv'] = DB::table('invice_temp')->where('mailbox' , $invoice->mailbox_no)->get();
            //dd($data['package']);
            $packegesInvice = Package::where('mailbox' , $invoice->mailbox_no)->get();
            if(count($packegesInvice) > 0){
                $packagesArr = [];
                foreach($packegesInvice as $row){

                    $invoices = DB::table('invice_temp')->where('tracking_no' , $row->tracking_no)->first();
                    if(count($invoices) == 0){
                        $invoicesPack = DB::table('invoice_packages')->where('tracking_no' , $row->tracking_no)->first();
                        if(count($invoicesPack) == 0){
                            $packagesArr[] = $row;
                        }
                    }
                }
                if(count($packagesArr) > 0){
                    $data['packeges'] = $packagesArr; 
                }else{
                    $data['packeges'] = $packagesArr; 
                }
            }
            return view('admin/invoices/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                    
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/invoice/edit/'.$invoice_id)
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                $gct = isset($request->gct) ? 1 : 0;
               
                Invoice::where('invoice_id', $invoice_id)
                        ->update([
                                'gct' => $gct,
                                'service_fee' => $request->service_fee,
                            ]);
                $item_description = $request->item_description;
                $supplier_invoice = $request->supplier_invoice;
                $weight = $request->weight;
                $tracking_no = $request->tracking_no;
                if(!empty($item_description)){
                    DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->delete();
                    for ($i = 0; $i < count($item_description); $i++) {
                        $pack_dataArr = array();
                        $pack_dataArr['invoice_id'] = $invoice_id;
                        $pack_dataArr['tracking_no'] = $tracking_no[$i];
                        $pack_dataArr['package_description'] = $item_description[$i];
                        $pack_dataArr['package_price'] = $supplier_invoice[$i];
                        $pack_dataArr['package_weight'] = $weight[$i];
                        if($item_description[$i]){
                            DB::table('invoice_packages')->insert($pack_dataArr);			
                        }
                    }
                }
                $name = $request->name;
                $custome_fee = $request->custome_fee;
                if(!empty($name)){
                    DB::table('service_type')->where('invoice_id' , $invoice_id)->delete();
                    for ($i = 0; $i < count($name); $i++) {
                        $service_dataArr = array();
                        $service_dataArr['invoice_id'] = $invoice_id;
                        $service_dataArr['name'] = $name[$i];
                        $service_dataArr['service_fee'] = $custome_fee[$i];
                        if($name[$i]){
                            DB::table('service_type')->insert($service_dataArr);			
                        }
                    }
                }
                //Save invoice in pdf
                $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
                $data['invoice'] = $invoice;
                $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice_id)->get();
                $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->get();
                $data['settings'] = DB::table('settings')->where('sessting_id' , 1)->first();
                $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
                $us_rate = DB::table('settings')->first()->us_rate;
                $data['usRate'] = $us_rate;
                $unique = time().'_invoice.pdf';
                //return view('admin/invoices/invoice_print_pdf' , $data);
                $pdfpathforgenerate = 'public/invoices/'.$unique;
                $pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data);
                $pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data)->save($pdfpathforgenerate);
                Invoice::where('invoice_id' , $invoice_id)->update(['pdf_invoice' => $unique]);
                Session::flash('success_msg', 'Invoice has been updated successfully.'); 
                return redirect('admin/invoices');
            }
        }
		
    }
    
    public function invoice($invoice_no = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
        $data['invoice'] = $invoice;
        $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['settings'] = DB::table('settings')->first();
        $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
        $us_rate = DB::table('settings')->first()->us_rate;
        $data['usRate'] = $us_rate;
        return view('outlet/invoices/invoice' , $data);
    }
    
    public function invoicePrint($invoice_no = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
        $data['invoice'] = $invoice;
        $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['settings'] = DB::table('settings')->where('sessting_id' , 1)->first();
        $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
        $us_rate = DB::table('settings')->first()->us_rate;
        $data['usRate'] = $us_rate;
        return view('outlet/invoices/invoice_print' , $data);
    }
	
    public function delete($invoice_id = '') {	
        
        $delete = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->delete();
        $delete = Invoice::where('invoice_id' , $invoice_id)->delete();
        
        Session::flash('del_msg', 'Invoice has been deleted successfully.'); 
        return redirect('admin/invoices');
    }
    
    public function sendInvoice(Request $request){ 
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $invoice_no = $request->invoice_no;
        $check_status = $request->check_status;
        //dd($check_status);
        for($i = 0; $i<count($check_status); $i++){
            $invoice = Invoice::where('invoice_no' , $invoice_no[$i])->first();
            $service_type = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
            $invoicesPack = DB::table('invoice_packages')
                ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                ->where('invoice_packages.invoice_id' , $invoice->invoice_id)
                ->get();
            $settings = DB::table('settings')->where('sessting_id' , 1)->first();
            $user = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
            $us_rate = DB::table('settings')->first()->us_rate;
            $user_name = $user->first_name.' '.$user->last_name;
            $email_subject = $settings->company_name.' invoice detail';
            $settings = DB::table('settings')->first();
            if($settings){
                $email_from = $settings->administrator_email;
            }else{
                $email_from = 'info@virtualmallglobal.com';
            }
            $this->send_email_invoice($user->email, $user_name,$invoice,$service_type,$settings,$user,$invoicesPack,$us_rate,$email_subject, $email_from, 'invoice_print_email');
            Invoice::where('invoice_no' , $invoice_no[$i])
                    ->update([
                      'email_sent' => 1
                  ]);
        }
        Session::flash('success_msg', 'Invoice has been sent successfully.'); 
        return redirect('admin/invoices');
        	
    }
	
    public function exportInvoice( Request $request )
    {
        $data['title'] = 'Export Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Export Invoices';
        
        $role_id = Session::get('role_id');
        $user_id = Session::get('user_id');
        
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();

        $data['settings'] = DB::table( 'settings' )->where( 'sessting_id', 1 )->first();

        // set default dates week period
        $date = new DateTime( 'now' );
        $data[ 's' ][ 'start_date' ] = $date->format( 'm/d/Y' );
        $date->add( new DateInterval( 'P7D' ) );
        $data[ 's' ][ 'end_date' ] = $date->format( 'm/d/Y' );
        
        // set default search values
        $data[ 's' ][ 'invoice_no' ] = null;
        $data[ 's' ][ 'tracking_no' ] = null;
        $data[ 's' ][ 'mailbox_no' ] = null;
        $data[ 's' ][ 'price' ] = null;
        $data[ 's' ][ 'type' ] = null;

        // default invoices results
        $data[ 'invoices' ] = [];

        // serach for invoices
        if ( $request->isMethod( 'post' ) && $request->input( 'search' ) == 1 ) 
        {
            // get search date period
            $data[ 's' ][ 'start_date' ] = $request->input( 'start_date' );
            $start_date = date( 'Y-m-d', strtotime( $data[ 's' ][ 'start_date' ] ) );

            $data[ 's' ][ 'end_date' ] = $request->input( 'end_date' );
            $end_date = date( 'Y-m-d', strtotime( $data[ 's' ][ 'end_date' ] ) );
            
            // get search values
            $data[ 's' ][ 'invoice_no' ] = $request->input( 'invoice_no' );
            $data[ 's' ][ 'tracking_no' ] = $request->input( 'tracking_no' );
            $data[ 's' ][ 'mailbox_no' ] = $request->input( 'mailbox_no' );
            $data[ 's' ][ 'price' ] = $request->input( 'price' );
            $data[ 's' ][ 'type' ] = $request->input( 'type' );

            // build search query and get results
            $sql = 'SELECT 
                I.invoice_no AS invoice_no, 
                I.invoice_id AS invoice_id,
                U.first_name AS first_name, 
                U.last_name AS last_name, 
                I.email_sent AS email_sent, 
                I.created_at AS created_at 
            FROM invoices AS I JOIN users AS U ON U.mailbox = I.mailbox_no 
            WHERE ( I.created_at BETWEEN ? AND ? )
            ';

            if( !empty( $data[ 's' ][ 'invoice_no' ] ) )
                $sql .= ' AND invoice_no LIKE \'%'. $data[ 's' ][ 'invoice_no' ] .'%\' ';

            if( !empty( $data[ 's' ][ 'tracking_no' ] ) )
                $sql .= ' AND tracking_no LIKE \'%'. $data[ 's' ][ 'tracking_no' ] .'%\' ';

            if( !empty( $data[ 's' ][ 'mailbox_no' ] ) )
                $sql .= ' AND mailbox_no LIKE \'%'. $data[ 's' ][ 'mailbox_no' ] .'%\' ';

            if( !empty( $data[ 's' ][ 'price' ] ) )
                $sql .= ' AND price LIKE \'%'. $data[ 's' ][ 'price' ] .'%\' ';

            if( !empty( $data[ 's' ][ 'type' ] ) )
                $sql .= ' AND type LIKE \'%'. $data[ 's' ][ 'type' ] .'%\' ';

            $data[ 'invoices' ] = DB::select( $sql, [ $start_date, $end_date ] );
        }
        
        return view( 'admin/invoices/export', $data );
    }

    public function exportDownloadInvoice( Request $request )
    {
        $data[ 'title' ] = 'Export_Invoices';
        $data[ 'class' ] = 'invoices';
        $data[ 'table' ] = 'Export Invoices';
        
        $role_id = Session::get( 'role_id' );
        $user_id = Session::get( 'user_id' );
        
        $data[ 'getModues' ] = DB::table( 'role_modules' )->where( 'user_id', '=', $user_id)->where( 'role_id', '=', $role_id )->first();
        $data[ 'settings' ] = DB::table( 'settings' )->where( 'sessting_id', 1 )->first();

        $file = $data[ 'title' ] . '_' . date( 'Y-m-d' ) . '.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$file}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        // set JMD rate
        // $rate = 130;

        $invoice_nos = $request->input( 'invoice_no' );
        //dd($invoice_nos);
        $list[] = [ "Invoice #", "Customer", "Date", "Item", "Item Description", "Amount" , "Package Description" , "Package Amount" ];

        $invoice_nos = $request->input( 'invoice_no' );
        if( isset( $invoice_nos ) && is_array( $invoice_nos ) && !empty( $invoice_nos ) )
        {
            foreach ( $invoice_nos as $invoice_no ) 
            {
                //return $invoice_no;
                /*/
                $sql = 'SELECT 
                    I.invoice_no AS invoice_no, 
                    I.created_at AS created_at, 
                    I.type AS type, 
                    I.price AS price, 
                    I.weight AS weight,
                    U.first_name AS first_name,
                    U.last_name AS last_name,
                    I.mailbox_no AS mailbox_no,
                    U.email AS email,
                    I.tracking_no AS tracking_no, 
                    I.processing_fee AS processing_fee,  
                    I.ENV_levy AS env_levy, 
                    I.service_fee AS service_fee, 
                    I.CAF AS caf, 
                    I.SCF AS scf 
                FROM invoices AS I JOIN users AS U ON U.mailbox = I.mailbox_no 
                WHERE ( I.invoice_no = ? )';
                //*/

                $sql = 'SELECT 
                    I.invoice_no AS invoice_no, 
                    U.first_name AS first_name,
                    U.last_name AS last_name,
                    I.created_at AS created_at, 
                    S.name AS item, 
                    S.name AS item_discription, 
                    S.service_fee AS amount, 
                    I.service_fee AS service_fee, 
                    I.mailbox_no AS mailbox_no, 
                    U.email AS email,
                    P.package_description,
                    P.package_price
                FROM invoices AS I JOIN users AS U ON U.mailbox = I.mailbox_no 
                LEFT JOIN service_type AS S ON S.invoice_id = I.invoice_id  
                JOIN invoice_packages AS P ON P.invoice_id = I.invoice_id  
                WHERE ( I.invoice_id = ? )';

                $i = DB::select( $sql, [ $invoice_no ] );
                //echo '<pre>';print_r($i);exit;
                foreach ( $i as $tmp ) 
                {
                    $CreateTime = date( 'j/n/Y G:i', strtotime( $tmp->created_at ) );
                    $CustomerName = "{$tmp->first_name} {$tmp->last_name}";
                    $list[] = [ $tmp->invoice_no, $CustomerName, $CreateTime, $tmp->item, $tmp->item_discription, $tmp->amount,$tmp->package_description,$tmp->package_price ];
                }
                if(isset($i[0]->created_at)){
                    // Service Fee
                    $CreateTime = date( 'j/n/Y G:i', strtotime( $i[0]->created_at ) );
                    $CustomerName = "{$i[0]->first_name} {$i[0]->last_name}";
                    $list[] = [ $i[0]->invoice_no, $CustomerName, $CreateTime, 'Services Charges', 'Services Charges', $i[0]->service_fee ];
    
                    // GCT
                    $CreateTime = date( 'j/n/Y G:i', strtotime( $i[0]->created_at ) );
                    $CustomerName = "{$i[0]->first_name} {$i[0]->last_name}";
                    $list[] = [ $i[0]->invoice_no, $CustomerName, $CreateTime, 'GCT', 'GCT', ( $i[0]->service_fee * 0.165 ) ];
                }

                /*/
                $CreateTime = date( 'j/n/Y G:i', strtotime( $i[0]->created_at ) );
                $CustomerName = "{$i[0]->first_name} {$i[0]->last_name} {$i[0]->mailbox_no}";
                $DocumentNo = $invoice_no;
                $LineDescription = $i[0]->type;

                $LineAmount = (float) ( $i[0]->price * $rate );
                $LineAmount = number_format( $LineAmount, 2, '.', '' );

                $LineSalesItemName = "RCJ Package ({$i[0]->weight})";
                $BillEmail = $i[0]->email;
                $PackageNumber = $i[0]->tracking_no;

                // item
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineSalesItemName, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];

                // processing fee
                $LineDescription = 'Processing fee';
                $LineAmount = (float) ( $i[0]->processing_fee * $rate );
                $LineAmount = number_format( $LineAmount, 2, '.', '' );
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineDescription, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];

                // env levy
                $LineDescription = 'Env Levy';
                $LineAmount = (float) ( $i[0]->env_levy * $rate );
                $LineAmount = number_format( $LineAmount, 2, '.', '' );
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineDescription, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];

                // service fee
                $LineDescription = 'Service fee';
                $LineAmount = (float) ( $i[0]->service_fee );
                $LineAmount = number_format( $LineAmount, 2, '.', '' );
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineDescription, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];

                // C.A.F
                $LineDescription = 'C.A.F';
                $LineAmount = (float)$i[0]->caf;
                $LineAmount = number_format( $LineAmount, 2, '.', '' );
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineDescription, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];

                // S.C.F
                $LineDescription = 'S.C.F';
                $LineAmount = (float) $i[0]->scf;
                $LineAmount = number_format( $LineAmount, 2, '.', '' );
                $list[] = [ $CreateTime, $DocumentNo, "JMD", $LineDescription, $LineAmount, $LineDescription, $LineAmount, 1, $CustomerName, $BillEmail, $CreateTime, $LineAmount, $PackageNumber ];
                //*/
            }
        }

        $callback = function() use ( $list ) 
        {
            $FH = fopen( 'php://output', 'w' );
            foreach ( $list as $row )
            { 
                fputcsv( $FH, $row );
            }
            fclose( $FH );
        };

        // column names
        // echo "<pre>";
        // build search query and get export results
        //echo "</pre>";
        //exit();

        return Response::stream( $callback, 200, $headers ); //use Illuminate\Support\Facades\Response;
    }
    
    public function send_email_invoice($email, $user_name,$invoice,$service_type,$settings,$user,$invoicesPack,$us_rate,$email_subject, $email_from,$view_name) {
        $res['invoice'] = $invoice;
        $res['service_type'] = $service_type;
        $res['settings'] = $settings;
        $res['user'] = $user;
        $res['invoicesPack'] = $invoicesPack;
        $res['usRate'] = $us_rate;
        //return view('admin/invoices/'.$view_name , $res);
        Mail::send('admin/invoices/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject) {
            $message->from($email_from, $name = 'Virtual Mall Global');
            $message->to($email, $user_name)->subject($email_subject);
            //$message->to('rizwan@decodershub.com', $user_name)->subject($email_subject);
            $message->to('shopvmjbb@gmail.com', $user_name)->subject($email_subject);
        });
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
                    $total_amount = $invoice->total_amount + $total_tex;
                    $amount = number_format($total_amount);
                    $data['amountInWord'] = $this->getAmountInWords($total_amount);
            }elseif($invoice->tex_type == 2){
                    $tex = $invoice->IGST_tex;
                    $total_tex = $invoice->total_amount * $tex / 100;
                    $total_amount = $invoice->total_amount + $total_tex;
                    $amount = number_format($total_amount);
                    $data['amountInWord'] = $this->getAmountInWords($total_amount);
            }else{
            $total_amount = $invoice->total_amount;
                    $amount = number_format($total_amount);
                    $data['amountInWord'] = $this->getAmountInWords($total_amount);
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
		//$points = ($point) ?"." . $words[$point / 10] . " " . $words[$point = $point % 10] : '';
		return  $result . "Rupees only";
	}
    
    public function uploadInvoice(Request $request , $traking_no = ''){
        $data['title'] = 'Upload Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Shipment Tracking Details';
        $mailbox = Session::get('mailbox');
        if ($request->isMethod('get')) {
            $packege = Package::where('tracking_no' , $traking_no)->first();
            $data['packege'] = $packege;
            $data['title'] = PackageStatus::where('id' , $packege->status)->first()->name;
            return view('admin/invoices/upload_invoice' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                'input_img' => 'required',      
                            ]);
            if ($validator->fails()) {
                    return redirect('user/invoice/upload/'.$traking_no)
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                if ($request->hasFile('input_img')) {
                    $image = $request->file('input_img');
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/invoices');
                    $image->move($destinationPath, $name);
                }
                $usersRes = Package::where('tracking_no' , $traking_no)->update(['invoice' => $name]);
                return redirect('user/invoice/upload/'.$traking_no);
            }
        }
    }
    
    public function deleteInvoice($tracking_no = '') {	
        $packeg = Package::where('tracking_no' , $tracking_no)->first();
        if($packeg){
            @unlink(base_path() . '/public/invoices/' . $packeg->invoice);
            $usersRes = Package::where('tracking_no' , $tracking_no)->update(['invoice' => '']);
        }
        Session::flash('del_msg', 'Invoice has been deleted successfully.'); 
        return redirect('user/invoice/upload/'.$tracking_no);
    }
    
    public function ratesUploadCSV(Request $request){
        $data['title'] = 'Upload CSV';
        $data['class'] = 'csv';
        $data['table'] = 'Upload CSV';
        
        set_time_limit(1000);

        $file = URL::to('/').'/public/users/ratesheet.csv';
        $handle = fopen($file, "r");
        $header = false;
        $csvLine = fgetcsv($handle);
        //dd($csvLine);
        while ($csvLine = fgetcsv($handle, 1000, ",")) {
            if ($header) {
                $header = true;
            } else {
                //dd($csvLine);
                $weights = explode('LBS' , $csvLine[0]);
                //return $weights[0];
                $products = DB::table('weights')->where('weight' , $weights[0])->first();
                if($products){
                    $price = $csvLine[1];
                    DB::table('weights')->where('weight' , $weights[0])->update([
                        'weight' => $weights[0],
                        'price' => $price
                    ]);
                }else{
                    $price = $csvLine[1];
                    DB::table('weights')->insert([
                        'weight' => $weights[0],
                        'price' => $price
                    ]);
                }

            }
        }
          return 'Added Successfully';  
    } 
    
    
    public function uploadUserCSV(Request $request){
        $data['title'] = 'Upload CSV';
        $data['class'] = 'csv';
        $data['table'] = 'Upload CSV';
        
        set_time_limit(1000);

        $file = URL::to('/').'/public/users/customerlisting.csv';
        $handle = fopen($file, "r");
        $header = false;
        $csvLine = fgetcsv($handle);
        //dd($csvLine);
        while ($csvLine = fgetcsv($handle, 1000, ",")) {
            if ($header) {
                $header = true;
            } else {
                $username = explode(' ' , $csvLine[0]);
                if(isset($username[0])){
                    $first_name = $username[0];
                }else{
                    $first_name = '';
                }
                if(isset($username[0])){
                    $last_name = $username[1];
                }else{
                    $last_name = '';
                }
                //return $first_name.' '.$last_name;
                $users = DB::table('users')->where('mailbox' , 'PE-'.$csvLine[8])->first();
                if($users){
                   
                    DB::table('users')->where('mailbox' , 'PE-'.$csvLine[8])->update([
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $csvLine[3],
                        'phone' => $csvLine[4],
                        'address_1' => $csvLine[5],
                        'state' => $csvLine[6],
                        'country' => $csvLine[7]
                    ]);
                    //return 'Updated Successfully'; 
                }else{
                    
                    DB::table('users')->insert([
                        'mailbox' => 'PE-'.$csvLine[8],
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $csvLine[3],
                        'phone' => $csvLine[4],
                        'address_1' => $csvLine[5],
                        'state' => $csvLine[6],
                        'country' => $csvLine[7],
                        'password' => Hash::make('1234'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    //return 'Added Successfully';
                }

            }
        }
        return 'Added Successfully';   
    }  
}
