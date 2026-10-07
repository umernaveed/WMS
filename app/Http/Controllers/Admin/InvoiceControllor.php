<?php
namespace App\Http\Controllers\Admin;

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
use App\Models\Courier;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\Console\Input\Input;
use Session;
use Validator;
use DB;
use PDF;
use URL;
use DateTime;
use DateInterval;
use commonHelper;

class InvoiceControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    public function testInvoice(){
        $invoice = DB::table('payments')->select('invoice_id')->where('courier_id' , 9)->groupby('invoice_id')->get()->pluck('invoice_id');
        //dd($invoice);
        $package_price = DB::table('invoice_packages')->select(DB::raw("SUM(total) as pk_price"))
                            ->whereIn('invoice_id' , $invoice)
                            ->first();
                            dd($package_price);
    }

    public function view(){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $totalInv = commonHelper::getCourierInvoiceCountEmailUnpaidFilter($start_date,$end_date);
        $data['totalInvoice'] = number_format($totalInv,2);
        return view("admin.invoices.list",$data);
    }

    public function listView($type = '')
    {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        if($start_date != '' && $end_date != ''){
            $data = DB::table('invoices')
                        ->select('invoices.*','couriers.name')
                        ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                        ->where('invoices.email_sent' , 0)
                        ->where('invoices.invoice_type' , 1)
                        ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"]);
        }else{
            $data = DB::table('invoices')
                        ->select('invoices.*','couriers.name')
                        ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                        ->where('invoices.email_sent' , 0)->where('invoices.invoice_type' , 1);
        }
        //dd($data);
        return DataTables::of($data)
                ->editColumn('totalInv', function ($data) {
                    $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                    return $totalInv.' USD';
                })
                ->editColumn('status', function ($data) {
                    if($data->status == '0'){
                        return 'Unpaid';
                    }
                    if($data->status == '1'){
                        return 'Unpaid';
                    }
                })
                ->editColumn('check_status', function ($data) {
                    return '<input name="check_status[]" type="checkbox"><input type="hidden" name="invoice_no[]" value="'.$data->invoice_no.'">';
                })
                ->rawColumns(['check_status' => 'check_status','action' => 'action'])
                ->addColumn('action', function($data){
                    //<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a>
                    $btn = "<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a><a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a>";
                    return $btn;
                })
                ->make(true);
    }
    public function viewHistory($id = ''){
        $data['title'] = 'Invoice History';
        $data['class'] = 'invoices';
        $data['table'] = 'Invoice History';
        $data['id'] = $id;
           // $invoice = DB::table('invoices')->where('invoices.email_sent' , 0)->where('invoices.invoice_type' , 1)->get();
        return view("admin.invoices.history",$data);
    }

    public function listViewHistory($id = '')
    {
        
            $data = DB::table('payments')->join('invoices','invoices.invoice_id' , '=' , 'payments.invoice_id' , 'left')->select('payments.*','invoices.invoice_no')->where('payments.invoice_id' , $id);
      
        //dd($data);
        return DataTables::of($data)
                ->make(true);
    }
    public function viewEmail($status = ''){
        $data['status'] = $status;
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        if($status == 0){
            $data['title'] = 'Manage Unpaid Invoices';
            $data['class'] = 'invoices';
            $data['table'] = 'Manage Unpaid Invoices';

            // if($start_date != '' && $end_date != ''){
            //     $invoice = DB::table('invoices')->where('invoices.invoice_type' , 1)->where('invoices.status' , $status)->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])->get();
            //     $invoiceImount = DB::table('invoices')
            //                 ->join('invoice_packages' , 'invoice_packages.invoice_id' ,'=','invoices.invoice_id')
            //                 ->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"),DB::raw("SUM(invoice_packages.custom_fee) as cus_fee"),DB::raw("SUM(invoice_packages.service_fee) as ser_fee"))
            //               // ->where('invoices.email_sent' , 0)
            //                 ->where('invoices.status' , $status)
            //                 ->where('invoice_packages.status' , 1)
            //                 ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
            //                 ->first();
            // }else{
            //     $invoice = DB::table('invoices')->where('invoices.invoice_type' , 1)->where('invoices.status' , $status)->get();
            //     $invoiceImount = DB::table('invoices')
            //                 ->join('invoice_packages' , 'invoice_packages.invoice_id' ,'=','invoices.invoice_id')
            //                 ->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"),DB::raw("SUM(invoice_packages.custom_fee) as cus_fee"),DB::raw("SUM(invoice_packages.service_fee) as ser_fee"))
            //               // ->where('invoices.email_sent' , 0)
            //                 ->where('invoices.status' , $status)
            //                 ->where('invoice_packages.status' , 1)
            //                 ->first();

            // }
            // $total = 0;
            // if(count($invoice) > 0){
            //     foreach($invoice as $row){
            //         $totalInv = commonHelper::getAllInvoiceCountUnpaid($row->invoice_id);
            //         $total = $total + $totalInv;
            //     }
            // }
            $totalInv = commonHelper::getCourierInvoiceCountUnpaidFilter($start_date,$end_date);
            $data['totalInvoice'] = number_format($totalInv,2);
            return view("admin.invoices.listEmailUnPaid",$data);
        }else{
            $data['title'] = 'Manage Paid Invoices';
            $data['class'] = 'invoices';
            $data['table'] = 'Manage Paid Invoices';
            // if($start_date != '' && $end_date != ''){
            //     $invoice = DB::table('invoices')->where('invoices.invoice_type' , 1)->where('invoices.status' , $status)->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])->get();
            //     $invoiceImount = DB::table('invoices')
            //                 ->join('invoice_packages' , 'invoice_packages.invoice_id' ,'=','invoices.invoice_id')
            //                 ->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"),DB::raw("SUM(invoice_packages.custom_fee) as cus_fee"),DB::raw("SUM(invoice_packages.service_fee) as ser_fee"))
            //               // ->where('invoices.email_sent' , 0)
            //                 ->where('invoices.status' , $status)
            //                 ->where('invoice_packages.status' , 1)
            //                 ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
            //                 ->first();
            // }else{
            //     $invoice = DB::table('invoices')->where('invoices.invoice_type' , 1)->where('invoices.status' , $status)->get();
            //     $invoiceImount = DB::table('invoices')
            //                 ->join('invoice_packages' , 'invoice_packages.invoice_id' ,'=','invoices.invoice_id')
            //                 ->select(DB::raw("SUM(invoice_packages.package_price) as pk_price"),DB::raw("SUM(invoice_packages.custom_fee) as cus_fee"),DB::raw("SUM(invoice_packages.service_fee) as ser_fee"))
            //               // ->where('invoices.email_sent' , 0)
            //                 ->where('invoices.status' , $status)
            //                 ->where('invoice_packages.status' , 1)
            //                 ->first();
            // }
            // $total = 0;
            // $us_rate = DB::table('settings')->first()->us_rate;
            // if($invoiceImount){
            //     if($invoiceImount->pk_price != ''){
            //         $totalInv = ($invoiceImount->pk_price + $invoiceImount->cus_fee + $invoiceImount->ser_fee) * $us_rate;
            //         $total = $total + $totalInv;
            //     }
            // }
            $totalInv = commonHelper::getCourierInvoiceCountPaidFilter($start_date,$end_date);
            $data['totalInvoice'] = number_format($totalInv,2);
            return view("admin.invoices.listEmailPaid",$data);
        }

    }

    public function listViewEmail($status = '')
    {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        if($status == 0){
            if($start_date != '' && $end_date != ''){
                $data = DB::table('invoices')
                            ->select('invoices.*','couriers.name')
                            ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                            //->where('invoices.email_sent' , 0)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                            ->orderby('invoices.updated_at' , 'DESC');

                return DataTables::of($data)
                        ->editColumn('totalInv', function ($data) {
                            $btn = '';
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            $partialAmount = DB::table('payments')->where('invoice_id' ,$data->invoice_id)->sum('amount');
                            $outstanding =  $totalInv - $partialAmount;
                            $btn .= '<p style="margin-bottom: 0px;"><b>Total Amount: </b>'.number_format($totalInv,2).'</p>';
                            $btn .= '<p style="margin-bottom: 0px;"><b>Amount Paid: </b>'.number_format($partialAmount,2).'</p>';
                            $btn .= '<p style="margin-bottom: 0px;"><b>Outstanding Amount: </b>'.number_format($outstanding,2).'</p>';
                            return $btn;
                        })
                        // ->editColumn('total_paid', function ($data) {
                        //     $totalPaid = commonHelper::getInvoiceCountPaid($data->invoice_id);
                        //     return $totalPaid.' USD';
                        // })
                        ->editColumn('status', function ($data) {
                            return '<a href="'.url('/')."/admin/invoice/update/1/".$data->invoice_id.'"><button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>';
                        })
                        ->rawColumns(['status' => 'status','action' => 'action', 'totalInv' => 'totalInv'])
                        ->addColumn('action', function($data){
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            $partialAmount = DB::table('payments')->where('invoice_id' ,$data->invoice_id)->sum('amount');
                            $outstanding =  $totalInv - $partialAmount;
                            //<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a>
                            $btn = "<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a><a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='javascript:void(0)'  class='all_button btn  btn-sm btn-success partial-pay'  total='".$outstanding."'  id='".$data->invoice_id."'>Partial Pay</a><a href='".url('/')."/admin/resend/email/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Resend Email</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a><a href='".url('/')."/admin/invoice/history/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Payment History</a>";
                            return $btn;
                        })
                        ->make(true);
            }else{
                $data = DB::table('invoices')
                            ->select('invoices.*','couriers.name')
                            ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                           // ->where('invoices.email_sent' , 0)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->orderby('invoices.updated_at' , 'DESC');;
                return DataTables::of($data)
                        ->editColumn('totalInv', function ($data) {
                            $btn = '';
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            $partialAmount = DB::table('payments')->where('invoice_id' ,$data->invoice_id)->sum('amount');
                            $outstanding =  $totalInv - $partialAmount;
                            $btn .= '<p style="margin-bottom: 0px;"><b>Total Amount: </b>'.number_format($totalInv,2).'</p>';
                            $btn .= '<p style="margin-bottom: 0px;"><b>Amount Paid: </b>'.number_format($partialAmount,2).'</p>';
                            $btn .= '<p style="margin-bottom: 0px;"><b>Outstanding Amount: </b>'.number_format($outstanding,2).'</p>';
                            // $btn .= '<p style="margin-bottom: 0px;"><b>Total Amount: </b>'.$totalInv.'</p>';
                            // $btn .= '<p style="margin-bottom: 0px;"><b>Amount Paid: </b>'.$partialAmount.'</p>';
                            // $btn .= '<p style="margin-bottom: 0px;"><b>Outstanding Amount: </b>'.$outstanding.'</p>';
                            return $btn;
                        })
                        // ->editColumn('total_paid', function ($data) {
                        //     $totalPaid = commonHelper::getInvoiceCountPaid($data->invoice_id);
                        //     return $totalPaid.' USD';
                        // })
                        ->editColumn('status', function ($data) {
                            return '<a href="'.url('/')."/admin/invoice/update/1/".$data->invoice_id.'"><button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>';
                        })
                        ->rawColumns(['status' => 'status','action' => 'action' , 'totalInv' => 'totalInv'])
                        ->addColumn('action', function($data){
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            $partialAmount = DB::table('payments')->where('invoice_id' ,$data->invoice_id)->sum('amount');
                            $outstanding =  $totalInv - $partialAmount;
                            //<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a>
                            $btn = "<a href='".url('/')."/admin/invoice/edit/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Edit</a><a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='javascript:void(0)'  class='all_button btn  btn-sm btn-success partial-pay'  total='".$outstanding."'  id='".$data->invoice_id."'>Partial Pay</a><a href='".url('/')."/admin/resend/email/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Resend Email</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a><a href='".url('/')."/admin/invoice/history/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Payment History</a>";
                            return $btn;
                        })
                        ->make(true);
            }
        }else{
            if($start_date != '' && $end_date != ''){
                $data = DB::table('invoices')
                            ->select('invoices.*','couriers.name')
                            ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                           // ->where('invoices.email_sent' , 0)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->whereBetween('invoices.updated_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                            ->orderby('invoices.updated_at' , 'DESC');
                return DataTables::of($data)
                        ->editColumn('totalInv', function ($data) {
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            return $totalInv.' USD';
                        })
                        ->editColumn('status', function ($data) {
                            return '<a href="javascript:void(0)"><button type="button" class="btn btn-sm btn-info "><i class="fa fa-check"></i> Paid</button></a>';

                        })
                        ->rawColumns(['status' => 'status','action' => 'action'])
                        ->addColumn('action', function($data){
                            $btn = "<a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a><a href='".url('/')."/admin/invoice/history/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Payment History</a>";
                            return $btn;
                        })
                        ->make(true);
            }else{
                $data = DB::table('invoices')
                            ->select('invoices.*','couriers.name')
                            ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                           // ->where('invoices.email_sent' , 0)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->orderby('invoices.updated_at' , 'DESC');;
                return DataTables::of($data)
                        ->editColumn('totalInv', function ($data) {
                            $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                            return $totalInv.' USD';
                        })
                        ->editColumn('status', function ($data) {
                            return '<a href="javascript:void(0)"><button type="button" class="btn btn-sm btn-info "><i class="fa fa-check"></i> Paid</button></a>';

                        })
                        ->rawColumns(['status' => 'status','action' => 'action'])
                        ->addColumn('action', function($data){
                            $btn = "<a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a><a href='".url('/')."/admin/invoice/history/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Payment History</a>";
                            return $btn;
                        })
                        ->make(true);
            }
        }
    }

    public function viewMassPayments(Request $request , $status = ''){
        $data['status'] = $status;
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['title'] = 'Manage Mass Payments';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Mass Payments';
        if ($request->isMethod('get')) {
            if($start_date != '' && $end_date != ''){
                $data['invoices'] = DB::table('invoices')
                            ->select('invoices.*','users.first_name','users.last_name')
                            ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                            ->where('invoices.email_sent' , 1)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                            ->orderby('invoices.updated_at' , 'DESC')->get();
            }else{
                $data['invoices'] = DB::table('invoices')
                            ->select('invoices.*','users.first_name','users.last_name')
                            ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                            ->where('invoices.email_sent' , 1)
                            ->where('invoices.invoice_type' , 1)
                            ->where('invoices.status' , $status)
                            ->orderby('invoices.updated_at' , 'DESC')->get();
            }

            return view("admin.invoices.listMassPayments",$data);
        }else{
            $package_ids = explode(',' , $request->package_ids);
            $invoice_ids = array_unique(explode(',' , $request->invoice_ids));
            //dd($invoice_ids);

            if(count($package_ids) > 0){
                foreach($package_ids as $key => $val){
                    $invoicesPack = DB::table('invoice_packages')->where('tracking_no' , $val)->update(['status' => 1]);
                    Package::where('tracking_no', $val)
                      ->update([
                        'status' => 12
                    ]);
                }
            }

            if(count($invoice_ids) > 0){
                foreach($invoice_ids as $keyInv => $valInv){
                    $invoicesPack = DB::table('invoice_packages')->where('invoice_id' , $valInv)->where('status' , 0)->count();
                    if($invoicesPack == 0){
                        Invoice::where('invoice_id' , $valInv)->update(['status' => 1,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }

            }

            Session::flash('success_msg', 'Invoice status changed to paid successfully.');
            return redirect('admin/invoices/email/1'.'?start_date=&end_date=');
        }

    }

    public function listViewMassPayments($status = '')
    {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        if($start_date != '' && $end_date != ''){
            $data = DB::table('invoices')
                        ->select('invoices.*','users.first_name','users.last_name')
                        ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                        ->where('invoices.email_sent' , 1)
                        ->where('invoices.invoice_type' , 1)
                        ->where('invoices.status' , $status)
                        ->whereBetween('invoices.created_at', [$start_date." 00:00:00", $end_date." 23:59:59"])
                        ->orderby('invoices.updated_at' , 'DESC');

            return DataTables::of($data)
                    ->editColumn('totalInv', function ($data) {
                        $totalInv = commonHelper::getInvoiceCount($data->invoice_id);
                        return $totalInv.' USD';
                    })
                    ->editColumn('total_paid', function ($data) {
                        $totalPaid = commonHelper::getInvoiceCountPaid($data->invoice_id);
                        return $totalPaid.' USD';
                    })
                    ->editColumn('status', function ($data) {
                        return '<a href="'.url('/')."/admin/invoice/update/1/".$data->invoice_id.'"><button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>';
                    })
                    ->rawColumns(['status' => 'status','action' => 'action'])
                    ->addColumn('action', function($data){
                        //$btn = "<a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a><a href='".url('/')."/admin/resend/email/".$data->invoice_id."'  class='all_button btn  btn-sm btn-info'  id='".$data->invoice_id."'>Resend Email</a><a href='javascript:void(0)' class='all_button btn btn-sm btn-danger delete_btn'  id='".$data->invoice_id."'>Delete</a>";
                        $btn = "<a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a>";
                        return $btn;
                    })
                    ->make(true);
        }else{
            $data = DB::table('invoices')
                        ->select('invoices.*','users.first_name','users.last_name')
                        ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                        ->where('invoices.email_sent' , 1)
                        ->where('invoices.invoice_type' , 1)
                        ->where('invoices.status' , $status)
                        ->orderby('invoices.updated_at' , 'DESC');;
            return DataTables::of($data)
                    ->editColumn('totalInv', function ($data) {
                        $totalInv = commonHelper::getInvoiceCount($data->invoice_id);
                        return $totalInv.' USD';
                    })
                    ->editColumn('total_paid', function ($data) {
                        $totalPaid = commonHelper::getInvoiceCountPaid($data->invoice_id);
                        return $totalPaid.' USD';
                    })
                    ->editColumn('status', function ($data) {
                        return '<a href="'.url('/')."/admin/invoice/update/1/".$data->invoice_id.'"><button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>';
                    })
                    ->rawColumns(['status' => 'status','action' => 'action'])
                    ->addColumn('action', function($data){
                        $btn = "<a href='".url('/')."/admin/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a>";
                        return $btn;
                    })
                    ->make(true);
        }
    }

    public function userInvoices(){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'account';
        $data['table'] = 'Manage Invoices';
        $mailbox = Session::get('mailbox');
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['invoices'] = DB::table('invoices')
                                ->select('invoices.*','users.first_name','users.last_name')
                                ->join('users', 'users.mailbox', '=', 'invoices.mailbox_no')
                                ->where('invoices.mailbox_no' , $mailbox)
                                ->get();
        return view('admin/invoices/user_index' , $data);
    }

    public function create(Request $request){
       // return 'yhhj';
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $mailbox = Session::get('mailbox');
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        if ($request->isMethod('get')) {
             //$data['users'] = User::where('role_id' , 0)->get();
            $data['couriers'] = Courier::get();
            $invoice_temp = DB::table('invice_temp')->get();
            if(count($invoice_temp) > 0){
                foreach($invoice_temp as $row){
                    $tracking_no = json_decode($row->tracking_no,true);
                    if(count($tracking_no) > 0){
                        //foreach($tracking_no as $key => $val){
                            Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 0]);
                        //}
                    }
                    
                }
            }
            
            DB::table('invice_temp')->delete();
            Session::put('mailbox' , '');
          //  return 'fgfggsdsdsgfnnj';
            
            return view('admin/invoices/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                    //'mailbox' => 'required',
                                    'tracking_no' => 'required',
                                   // 'service_fee' => 'required'
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
                $invoice_no = time();
                $user_name = commonHelper::getUserName($request->mailbox);
                $gct = isset($request->gct) ? 1 : 0;
                $invoices = new Invoice();
                $invoices->invoice_no = $invoice_no;
                $invoices->courier_id = $request->courier_id;
                $invoices->mailbox_no = 0;
                $invoices->user_name = $user_name;
                $invoices->service_fee = 0;
                $invoices->gct = $gct;
                $invoices->status = '0';
                $invoices->addedby_id = session('user_id');
                $invoices->addedby_name = session('name').' '.session('last_name');
                $invoices->created_at = date('Y-m-d h:i:s');
                $invoices->updated_at = date('Y-m-d h:i:s');
                $invoices->save();
                $invoice_id = $invoices->invoice_id;
                $item_description = $request->item_description;
                $supplier_invoice = $request->supplier_invoice;
                // $custom_fee = $request->custom_fee;
                // $service_fee = $request->service_fee;
                //$weight = $request->weight;
                $quantity = $request->quantity;
                $total = $request->total;
                $tracking_no = $request->tracking_no;
                if(!empty($item_description)){
                    for ($i = 0; $i < count($item_description); $i++) {
                        $pack_dataArr = array();
                        $pack_dataArr['invoice_id'] = $invoice_id;
                        $pack_dataArr['courier_id'] = $request->courier_id;
                        $pack_dataArr['tracking_no'] = $tracking_no[$i];
                        $pack_dataArr['package_description'] = $item_description[$i];
                        $pack_dataArr['package_price'] = $supplier_invoice[$i];
                        //$pack_dataArr['custom_fee'] = $custom_fee[$i];
                        //$pack_dataArr['service_fee'] = $service_fee[$i];
                        $pack_dataArr['package_weight'] = 0;
                        $pack_dataArr['total'] = $total[$i];
                        $pack_dataArr['quantity'] = $quantity[$i];
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
                //Add Activity
                $checkPkg = '';
                $type = 'Invoice';
                $dateTime = date('Y-m-d H:i:s');
                $message = 'Invoice created.';
                $admin = session('user_id');
                $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$request->courier_id);


                //Save invoice in pdf
                $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
                $data['invoice'] = $invoice;
                $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice_id)->get();
                $data['invoicesPack'] = DB::table('invoice_packages')
                    ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                    ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                    ->where('invoice_packages.invoice_id' , $invoice->invoice_id)
                    ->where('invoice_packages.status',0)
                    ->get();
                $data['settings'] = DB::table('settings')->first();
                $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
                $us_rate = DB::table('settings')->first()->us_rate;
                $data['usRate'] = $us_rate;
                //dd($data);
                $unique = time().'_invoice.pdf';
                //view('admin/invoices/invoice_print_pdf' , $data);
                $pdfpathforgenerate = 'invoices/'.$unique;
                //$pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data);
                //$pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data)->save($pdfpathforgenerate);
                Invoice::where('invoice_id' , $invoice_id)->update(['pdf_invoice' => $unique]);
                DB::table('invice_temp')->delete();
                $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
                $courier_id = $invoice->courier_id;
                $service_type = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
                $invoicesPack = DB::table('invoice_packages')
                    ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                    ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                    ->where('invoice_packages.invoice_id' , $invoice->invoice_id)
                    ->get();
                $settings = DB::table('settings')->first();
                $courier = DB::table('couriers')->where('id' , $courier_id)->first();
                if($courier){
                    $us_rate = DB::table('settings')->first()->us_rate;
    
                    $user_name = $courier->name;
                    $email_subject = $settings->company_name.' invoice detail';
                    $settings = DB::table('settings')->first();
                    if($settings){
                        $email_from = $settings->from_email;
                    }
                    $settings = DB::table('settings')->first();
                    $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
                    $company_name = $settings->company_name;
                    $this->send_email_invoice($courier->email, $user_name,$invoice,$service_type,$settings,$courier,$invoicesPack,$us_rate,$email_subject, $email_from,$logo,$company_name, 'invoice_print_email');
                }
                return redirect('admin/invoices?start_date=&end_date=');
            }
        }
    }

    public function findPackeg(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
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

    public function createInvoice(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        $mailbox = $request->mailbox;
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
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
            return redirect('admin/invoices?start_date=&end_date=');
        }
    }

    public function submitInvoice(Request $request){
        $data['title'] = 'Create Invoice';
        $data['class'] = 'invoices';
        $data['table'] = 'Create Invoice';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
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
        return redirect('admin/invoices?start_date=&end_date=');
    }

    public function edit(Request $request , $invoice_id = ''){
        //return 'eeeeee';
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Edit Invoices';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        if ($request->isMethod('get')) {
            $treckingNoArr = [];
            $data['couriers'] = Courier::get();
            $invoice_packages = DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->get();
            //dd( $data['invoice_packages']);
            $data['invoice_packages'] = $invoice_packages;
            if(count($invoice_packages) > 0){
                foreach($invoice_packages as $pkg){
                    $tracking_no = json_decode($pkg->tracking_no);
                    $treckingNoArr[] = $tracking_no;
                }
            }
            //dd($treckingNoArr[0]);
            $invoice_temp = DB::table('invice_temp')->get();
            if(count($invoice_temp) > 0){
                foreach($invoice_temp as $row){
                    $tracking_no = json_decode($row->tracking_no,true);
                    if(count($tracking_no) > 0){
                        //foreach($tracking_no as $key => $val){
                        //dd($tracking_no);
                        Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 0]);
                        //}
                    }
                    
                }
            }
            if(count($treckingNoArr) > 0){
                //Package::whereIn('tracking_no' , $tracking_no)->update(['is_invoice' => 0]);
                Package::whereIn('tracking_no' , $treckingNoArr[0])->update(['is_invoice' => 1]);
            }
            DB::table('invice_temp')->delete();
            $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
            //$data['users'] = User::where('role_id' , 0)->get();
            //dd($data['users']);
            
            $data['invoice'] = $invoice;
            $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice_id)->get();
            
            //dd($data['invoice_packages']);
            //DB::table('invice_temp')->insert(['mailbox' => $packages->mailbox,'tracking_no' => $packages->tracking_no,'price' => $price,'weight' => $packages->weight,'description' => $packages->item_description]);
            $data['packagesInv'] = DB::table('invice_temp')->where('courier_id' , $invoice->courier_id)->get();
            $packegesInvice = Package::where('courier_id' , $invoice->courier_id)->where('is_invoice' , 0)->where('packages.status' , '!=' , 9)->get();
            if(count($packegesInvice) > 0){
                $packagesArr = [];
                $data['packeges'] = $packegesInvice;
            }else{
                $data['packeges'] = [];
            }
            $data['invoice_id'] = $invoice_id;
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
                                'addedby_id' => session('user_id'),
                                'addedby_name' => session('name').' '.session('last_name')
                                //'service_fee' => $request->service_fee,
                            ]);
                $item_description = $request->item_description;
                $supplier_invoice = $request->supplier_invoice;
                //$custom_fee = $request->custom_fee;
                //$service_fee = $request->service_fee;
                $weight = $request->weight;
                //dd($weight);
                $tracking_no = $request->tracking_no;
                $quantity = $request->quantity;
                $total = $request->total;
                //dd($weight);
                if(!empty($item_description)){
                    DB::table('invoice_packages')->where('invoice_id' , $invoice_id)->delete();
                    for ($i = 0; $i < count($item_description); $i++) {
                        $pack_dataArr = array();
                        $pack_dataArr['invoice_id'] = $invoice_id;
                        $pack_dataArr['tracking_no'] = $tracking_no[$i];
                        $pack_dataArr['package_description'] = $item_description[$i];
                        $pack_dataArr['package_price'] = $supplier_invoice[$i];
                        //$pack_dataArr['custom_fee'] = $custom_fee[$i];
                        //$pack_dataArr['service_fee'] = $service_fee[$i];
                        $pack_dataArr['package_weight'] = 0;
                        $pack_dataArr['total'] = $total[$i];
                        $pack_dataArr['quantity'] = $quantity[$i];
                        DB::table('invoice_packages')->insert($pack_dataArr);
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
                $data['settings'] = DB::table('settings')->first();
                $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
                $us_rate = DB::table('settings')->first()->us_rate;
                $data['usRate'] = $us_rate;
                $unique = time().'_invoice.pdf';
                //return view('admin/invoices/invoice_print_pdf' , $data);
                // $pdfpathforgenerate = 'invoices/'.$unique;
                // $pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data);
                //$pdf = PDF::loadView('admin/invoices/invoice_print_pdf' , $data)->save($pdfpathforgenerate);
                Invoice::where('invoice_id' , $invoice_id)->update(['pdf_invoice' => $unique]);
                Session::flash('success_msg', 'Invoice has been updated successfully.');
                return redirect('admin/invoices?start_date=&end_date=');
            }
        }

    }

    public function invoice($invoice_no = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices_detail';
        $data['table'] = 'Manage Invoices';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
        $data['invoice'] = $invoice;
        //return $invoice->invoice_id;
        //return $invoice->status;
        if($invoice->status == 1){
            $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->get();
        }else{
            $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->get();
        }
        //dd($data['invoicesPack']);
        $data['couriers'] = Courier::where('id' , $invoice->courier_id)->first();
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['settings'] = DB::table('settings')->first();
        $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
        $us_rate = DB::table('settings')->first()->us_rate;
        $data['usRate'] = $us_rate;
        return view('admin/invoices/invoice' , $data);
    }

    public function invoicePrint($invoice_no = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
        $data['invoice'] = $invoice;
        $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['settings'] = DB::table('settings')->first();
        $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
        $us_rate = DB::table('settings')->first()->us_rate;
        $data['usRate'] = $us_rate;
        return view('admin/invoices/invoice_print' , $data);
    }

    public function delete() {
        $invoicesPack = DB::table('invoice_packages')
            ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
            ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
            ->where('invoice_packages.invoice_id' , request()->id)
            ->get();
        if($invoicesPack){
            foreach($invoicesPack as $row){
                $tracking_no = json_decode($row->tracking_no,true);
                if(count($tracking_no) > 0){
                    foreach($tracking_no as $key => $val){
                        Package::where('tracking_no' , $val)->update(['is_invoice' => 0]);
                    }
                }
            }
            
        }
        $invoice = DB::table('invoice_packages')->where('invoice_id' , request()->id)->first();
        $delete = DB::table('invoice_packages')->where('invoice_id' , request()->id)->delete();
        $delete = DB::table('payments')->where('invoice_id' , request()->id)->delete();
        $delete = Invoice::where('invoice_id' , request()->id)->delete();
        //Add Activity
        $checkPkg = '';
        $type = 'Invoice';
        $dateTime = date('Y-m-d H:i:s');
        $message = 'Invoice deleted.';
        $admin = session('user_id');
        $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$invoice->courier_id);
        return ["status" => true,"message" =>"Invoice deleted successfully"];
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
            $courier_id = $invoice->courier_id;
            $service_type = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
            $invoicesPack = DB::table('invoice_packages')
                ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                ->where('invoice_packages.invoice_id' , $invoice->invoice_id)
                ->get();
            //dd($invoicesPack);
            $settings = DB::table('settings')->first();
            $courier = DB::table('couriers')->where('id' , $courier_id)->first();
            if($courier){
                $us_rate = DB::table('settings')->first()->us_rate;

                $user_name = $courier->name;
                $email_subject = $settings->company_name.' invoice detail';
                $settings = DB::table('settings')->first();
                if($settings){
                    $email_from = $settings->from_email;
                }
                $settings = DB::table('settings')->first();
                $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
                $company_name = $settings->company_name;
                $this->send_email_invoice($courier->email, $user_name,$invoice,$service_type,$settings,$courier,$invoicesPack,$us_rate,$email_subject, $email_from,$logo,$company_name, 'invoice_print_email');
            }

            Invoice::where('invoice_no' , $invoice_no[$i])
                    ->update([
                      'email_sent' => 1
                  ]);
        }
        Session::flash('success_msg', 'Invoice has been sent successfully.');
        return redirect('admin/invoices?start_date=&end_date=');

    }

    public function resendInvoice($invoice_id = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
        $service_type = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $invoicesPack = DB::table('invoice_packages')
            ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
            ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
            ->where('invoice_packages.invoice_id' , $invoice->invoice_id)
            ->get();
        $settings = DB::table('settings')->first();
        $courier = DB::table('couriers')->where('id' , $invoice->courier_id)->first();
            
        $us_rate = DB::table('settings')->first()->us_rate;
        if($courier){
            $user_name = $courier->name;
            $email_subject = $settings->company_name.' invoice detail';
            $settings = DB::table('settings')->first();
            if($settings){
                $email_from = $settings->from_email;
            }
            $settings = DB::table('settings')->first();
            $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
            $company_name = $settings->company_name;
            $this->send_email_invoice($courier->email, $user_name,$invoice,$service_type,$settings,$courier,$invoicesPack,$us_rate,$email_subject, $email_from,$logo,$company_name, 'invoice_print_email');
        }

        Invoice::where('invoice_id' , $invoice_id)
                ->update([
                  'email_sent' => 1
              ]);
        $status = $invoice->status;
        Session::flash('success_msg', 'Invoice has been sent successfully.');
        return redirect('admin/invoices/email/'.$status.'?start_date=&end_date=');

    }

    public function exportInvoice( Request $request )
    {
        $data['title'] = 'Export Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Export Invoices';

        $role_id = Session::get('role_id');
        $user_id = Session::get('user_id');

        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $data['settings'] = DB::table( 'settings' )->first();

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
        $data[ 'settings' ] = DB::table( 'settings' )->first();

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

    public function send_email_invoice($email, $user_name,$invoice,$service_type,$settings,$user,$invoicesPack,$us_rate,$email_subject, $email_from,$logo,$company_name,$view_name) {
        $res['invoice'] = $invoice;
        $res['us_rate'] = $us_rate;
        $res['company_name'] = $company_name;
        $res['service_type'] = $service_type;
        $res['logo'] = $logo;
        $res['settings'] = $settings;
        $res['user'] = $user;
        $res['invoicesPack'] = $invoicesPack;
        $res['usRate'] = $us_rate;
        //return view('admin/invoices/'.$view_name , $res);
        Mail::send('admin/invoices/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject, $company_name) {
            $message->from($email_from, $company_name);
            $message->to($email, $user_name)->subject($email_subject);
           // $message->to('lessanne.roper@freighttrees.com', $user_name)->subject($email_subject);
            $message->to('umernaveed59@gmail.com', $user_name)->subject($email_subject);
        });
    }

    public function send_email_invoiceold($email, $user_name,$invoice,$service_type,$settings,$user,$invoicesPack,$us_rate,$email_subject, $email_from,$logo,$company_name,$view_name) {
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
                                body {
                                    overflow-x: hidden;
                                }
                                @media (max-width:1280px) {
                                    .main_content ul {
                                        width: 60% !important;
                                    }
                                }
                                @media (max-width:1180px) {
                                    .account_info {
                                        width: 90% !important;
                                    }
                                }
                                @media (max-width:812px) {
                                    .email-bg {
                                        width: 90% !important;
                                    }
                                }
                                @media (max-width: 767.98px) {
                                    .main_content ul {
                                        width: 90% !important;
                                    }
                                }
                                @media (max-width: 600px) {
                                    .email-bg {
                                        width: 90% !important;
                                    }
                                }
                                @media (max-width:480px) {
                                    .main_content ul {
                                        width: 98% !important;
                                        padding: 6px !important;
                                        font-size: 14px;
                                    }
                                }
                                @media (max-width:380px) {
                                    .small_text {
                                        font-size: 16px !important;
                                    }
                                    .email_content p {
                                        font-size: 16px !important;
                                    }
                                    .main_content li {
                                        font-size: 14px !important;
                                    }
                                }
                                @media (max-width:320px) {
                                    .small_text {
                                        font-size: 14px !important;
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
                                        <div class="email_content" style="width: 100%;">
                                            <div style="border-bottom: 1px solid #ccc;">
                                                <h4 style="font-size: 18px;margin-bottom: 0;color: #757575cc; ">BILL FROM:</h4>
                                                <h3 style="font-size: 26px; margin:0; padding-top: 5px;">'.$company_name.'</h3>
                                                <p style="font-size: 20px; margin: 0; padding-top: 5px;">'.$settings->local_address.'</p>
                                                <p style="font-size: 18px; margin: 0; padding: 6px 0;">Phone: '.$settings->phone.'</p>
                                            </div>
                                            <div style="border-bottom: 1px solid #ccc;">
                                                <h4 style="font-size: 18px;margin-bottom: 0;color: #757575cc;">BILL TO:</h4>
                                                <h3 style="font-size: 26px; margin:0;  padding-top: 5px;">'.$user->name.'</h3>
                                                <p style="font-size: 20px; margin: 0; padding-top: 5px;">'.$user->local_address.'</p>
                                                <p style="font-size: 20px; margin: 0; padding-top: 5px;">City & State: '.$user->city.' '.$user->state.'</p>

                                                <p style="margin: 0; padding: 6px 0;">Email: <a href="mailto:'.$user->email.'">'.$user->email.'</a></p>
                                            </div>
                                            <div style="border-bottom: 1px solid #ccc;">
                                                <p style="font-size: 20px;margin-bottom: 0;color: #757575cc;">Invoice #: <span style="color: #000; font-weight: 600;">'.$invoice->invoice_no.'</span></p>

                                                <p style="font-size: 20px; margin: 0; padding: 5px 0; color: #757575cc;">Date #: <span style="color: #000; font-weight: 600;">'.date('d M, Y' , strtotime($invoice->created_at)).'</span></p>
                                            </div>
                                            <div class="main_content" style="border-bottom: 1px solid #ccc;">';
                                                $count = 0;
                                                $totalAmount = 0;
                                                if(count($invoicesPack) > 0){
                                                    foreach($invoicesPack as $pack){
                                                        $count1 = $count + 1;
                                                                $message .= '<ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                                        #
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$count1.'</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                                                        Description
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$pack->package_description.'</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Unit Price</li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.number_format(($pack->package_price) * $us_rate ,2).' USD</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Quantity</li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$pack->quantity.'</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                                        Amount
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">'.number_format(($pack->package_price * $pack->quantity) * $us_rate ,2).' USD</li>
                                                                </ul>';
                                                        $count ++;
                                                        $package_price = $pack->package_price * $us_rate;
                                                        $pk_price = $package_price * $pack->quantity;
                                                        $custom_fee = $pack->custom_fee * $us_rate;
                                                        $totalAmount = $totalAmount + $pk_price;
                                                    }
                                                }
                                                
                                                if(count($service_type) > 0){
                                                    foreach($service_type as $row){
                                                        $count1 = $count + 1;
                                                                $message .= '<ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                                        #
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$count1.'</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                                                        Description
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$row->name.'</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Unit Price</li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.number_format($row->service_fee * $us_rate ,2).' USD</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Quantity</li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">-</li>
                                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                                        Amount
                                                                    </li>
                                                                    <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">'.number_format($row->service_fee * $us_rate ,2).' USD</li>
                                                                </ul>';
                                                        $count ++;
                                                        $totalAmount = $totalAmount + $row->service_fee * $us_rate;
                                                    }
                                                }


                                                $message .= '<ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                        #
                                                    </li>
                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">3</li>
                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                                        Description
                                                    </li>
                                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">GCT</li>
                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                        Price
                                                    </li>';
                                                    if($invoice->gct == 0){
                                                        $gct_label = '0%';
                                                    }else{
                                                        $gct_label = '7%';
                                                    }

                                        $message .= '<li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">'.$gct_label.'</li>
                                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                                        Amount
                                                    </li>';
                                                    if($invoice->gct == 0){
                                                        $total_gct = 0  * $us_rate;
                                                        $gct = number_format(0  , 2).' USD';
                                                    }else{
                                                        $total_gct = $totalAmount * 7 / 100;
                                                        $gct = number_format($totalAmount * 7 / 100  ,2).' USD';
                                                    }
                                        $message .= '<li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">'.$gct.'</li>
                                                </ul>';
                                                    $message .= '

                                                <ul
                                                style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">


                                                <li
                                                    style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#000;">
                                                    Sub- Total amount
                                                </li>
                                                <li style="display: inline-block; width: 68%; padding: 0 0 10px 0; ">'.number_format($totalAmount + $total_gct ,2).' USD</li>
                                                <li
                                                    style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#000;">
                                                    Grand Total
                                                </li>
                                                <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">'.number_format($totalAmount + $total_gct ,2).' USD</li>
                                            </ul>
                                            </div>
                                            <div>
                                                <h4 style="color: #636363;">Thank You for your business</h4>
                                                <h4 style="color: #636363;">Invoice powered by '.$company_name.'</h4>
                                            </div>
                                        </div>

                                    </div>
                                    <footer>
                                        <h4 style="border-top: 1px solid #ccc; font-size: 13px; color: #8c8c8c;">Thanks for your support Enjoy
                                            your shopping experience. </h4>
                                        <p style="font-size: 13px; color: #8c8c8c;">Best Wishes</p>
                                        <p style="font-size: 13px; color: #8c8c8c;">'.$company_name.' Client Services</p>
                                    </footer>
                                    </div>
                                </section>
                                <!----- EMAIL-SECTOION-END ------>
                            </main>
                        </body>
                        </html>';
            $user_email = $email;
            //$user_email = 'rizwangill132@gmail.com';
            mail($user_email,$subject,$message,$headers);
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
    
    public function partialPay(Request $request) {
        $addedby_id = session('user_id');
        $addedby_name = session('name').' '.session('last_name');
        $invoice = Invoice::where('invoice_id' , $request->id)->first();
        //dd($invoice);
        DB::table('payments')->insert(['courier_id' => $invoice->courier_id ,'invoice_id' => $request->id , 'amount' => $request->amount,'status' => 0 ,'addedby_id' => $addedby_id,'addedby_name' => $addedby_name]);
        $remainingTotal = $request->totalInv - $request->amount;
        if(round($remainingTotal) > 0){
            //Add Activity
            $checkPkg = '';
            $type = 'Partial Payment';
            $dateTime = date('Y-m-d H:i:s');
            $message = 'Partial Payment created.';
            $admin = session('user_id');
            $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$invoice->courier_id);
            return ["status" => true,"message" =>"Invoice partially paid successfully", 'type' => 'partial','url' => ''];
        }else{
            DB::table('payments')->where('invoice_id' , $request->id)->update(['status' => 1]);
            Invoice::where('invoice_id' , $request->id)->update(['status' => 1,'updated_at'=>date('Y-m-d H:i:s')]);
            $invoicesPack = DB::table('invoice_packages')
                ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                ->where('invoice_packages.invoice_id' , $request->id)
                ->get();
            if(count($invoicesPack) > 0){
                foreach($invoicesPack as $row){
                    $tracking_no = $row->tracking_no;
                    Package::where('tracking_no', $tracking_no)
                      ->update([
                        'status' => 12
                    ]);
                }
            }
            $url = url('/').'/admin/invoices/email/1?start_date=&end_date=';
            //Add Activity
            $checkPkg = '';
            $type = 'Invoice';
            $dateTime = date('Y-m-d H:i:s');
            $message = 'Invoice paid.';
            $admin = session('user_id');
            $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$invoice->courier_id);
            return ["status" => true,"message" =>"Invoice partially paid successfully" , 'type' => 'full' , 'url' => $url];
        }
    }
    
    public function updateInvoiceStatus($status = '' , $invoice_id = '') {

        Invoice::where('invoice_id' , $invoice_id)->update(['status' => $status,'updated_at'=>date('Y-m-d H:i:s')]);
        if($status == 0){
            Session::flash('success_msg', 'Invoice status changed to unpaid successfully.');
        }else{
            $addedby_id = session('user_id');
            $addedby_name = session('name').' '.session('last_name');
            $invoice = Invoice::where('invoice_id' , $invoice_id)->first();
            $totalInv = commonHelper::getInvoiceCountOld($invoice_id);
            $partialAmount = DB::table('payments')->where('invoice_id' ,$invoice_id)->sum('amount');
            $outstanding =  $totalInv - $partialAmount;
            DB::table('payments')->insert(['courier_id' => $invoice->courier_id ,'invoice_id' => $invoice_id , 'amount' => $outstanding,'addedby_id' => $addedby_id,'addedby_name' => $addedby_name]);
            DB::table('payments')->where('invoice_id' , $invoice_id)->update(['status' => 1]);
            $invoicesPack = DB::table('invoice_packages')
                ->leftJoin('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')
                ->select('invoice_packages.*', 'packages.freight_type', 'packages.volume_cuft', 'packages.volume_cbm', 'packages.chargeable_weight', 'packages.weight')
                ->where('invoice_packages.invoice_id' , $invoice_id)
                ->get();
            if(count($invoicesPack) > 0){
                foreach($invoicesPack as $row){
                    $tracking_no = $row->tracking_no;
                    Package::where('tracking_no', $tracking_no)
                      ->update([
                        'status' => 12
                    ]);
                }
            }
            //Add Activity
            $checkPkg = '';
            $type = 'Invoice';
            $dateTime = date('Y-m-d H:i:s');
            $message = 'Invoice paid.';
            $admin = session('user_id');
            $activity = commonHelper::addActivity($type,$dateTime,$message,$admin,$checkPkg,$invoice->courier_id);
            Session::flash('success_msg', 'Invoice status changed to paid successfully.');
        }
        return redirect('admin/invoices/email/'.$status.'?start_date=&end_date=');

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

        $file = URL::to('/').'/public/users.csv';
        $handle = fopen($file, "r");
        $header = false;
        $csvLine = fgetcsv($handle);
        //dd($csvLine);
        $count = 8;
        while ($csvLine = fgetcsv($handle, 1000, ",")) {
            $count++;
            if ($header) {
                $header = true;
            } else {
                //dd($csvLine);
                
                $users = DB::table('users')->where('email' , $csvLine[0])->where('courier_id' , 20)->first();
                if($users){

                    DB::table('users')->where('email' , $csvLine[0])->where('courier_id' , 20)->update([
                        'first_name' => $csvLine[1],
                        'last_name' => $csvLine[2],
                        'user_name' => $csvLine[1].' '.$csvLine[2],
                        'courier_id' => 20
                    ]);
                    //return 'Updated Successfully';
                }else{
                    
                    DB::table('users')->insert([
                        'mailbox' => 'qls00'.$count,
                        'first_name' => $csvLine[1],
                        'last_name' => $csvLine[2],
                        'user_name' => $csvLine[1].' '.$csvLine[2],
                        'email' => $csvLine[0],
                        'courier_id' => 20
                    ]);
                    //return 'Added Successfully';
                }

            }
        }
        return 'Added Successfully';
    }
}
