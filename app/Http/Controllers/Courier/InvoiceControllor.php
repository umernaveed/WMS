<?php
namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\PackageStatus;
use App\Models\Weight;
use App\Models\Courier;
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
        return view("couriers.invoices.list",$data);
    }

    public function listView()
    {
        $courier_id = Session::get('courier_id');
        $data = DB::table('invoices')
                    ->select('invoices.*','couriers.name')
                    ->join('couriers', 'couriers.id', '=', 'invoices.courier_id')
                   // ->where('invoices.status' , 0)
                    ->where('invoices.courier_id' , $courier_id);
                    //->get();
        //dd($data);
        return DataTables::of($data)
                ->editColumn('totalInv', function ($data) {
                    $totalInv = commonHelper::getInvoiceCountOld($data->invoice_id);
                    return $totalInv.' USD';
                })
                ->editColumn('status', function ($data) {
                    if($data->status == 0){
                        return '<a href="javascript:void(0)"><button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>';
                    }else{
                        return '<a href="javascript:void(0)"><button type="button" class="btn btn-sm btn-info "><i class="fa fa-check"></i> Paid</button></a>';
                    }

                })
                ->rawColumns(['status' => 'status','action' => 'action'])
                ->addColumn('action', function($data){
                    $btn = "<a href='".url('/')."/courier/invoice/".$data->invoice_no."'  class='all_button btn  btn-sm btn-warning'  id='".$data->invoice_id."'>Invoice Detail</a>";
                    return $btn;
                })
                ->make(true);
    }

    public function invoice($invoice_no = ''){
        $data['title'] = 'Manage Invoices';
        $data['class'] = 'invoices';
        $data['table'] = 'Manage Invoices';
        $role_id= Session::get('role_id');
        $user_id= Session::get('user_id');
        //$data['getModues'] = DB::table('role_modules')->where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->first();
        $data['getModues'] = '';
        $invoice = Invoice::where('invoice_no' , $invoice_no)->first();
        $data['invoice'] = $invoice;
        if($invoice->status == 1){
            $data['invoicesPack'] = DB::table('invoice_packages')->join('packages', 'invoice_packages.tracking_no', '=', 'packages.tracking_no')->where('invoice_id' , $invoice->invoice_id)->where('invoice_packages.status',1)->get();
        }else{
            $data['invoicesPack'] = DB::table('invoice_packages')->where('invoice_id' , $invoice->invoice_id)->where('invoice_packages.status',0)->get();
        }
        //dd($data['invoicesPack']);
        $data['couriers'] = Courier::where('id' , $invoice->courier_id)->first();
        $data['service_type'] = DB::table('service_type')->where('invoice_id' , $invoice->invoice_id)->get();
        $data['settings'] = DB::table('settings')->first();
        $data['user'] = DB::table('users')->where('mailbox' , $invoice->mailbox_no)->first();
        $us_rate = DB::table('settings')->first()->us_rate;
        $data['usRate'] = $us_rate;
        return view('couriers/invoices/invoice' , $data);
    }
}
