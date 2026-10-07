<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;
use Yajra\DataTables\Facades\DataTables;
use commonHelper;

class DashboardControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function dashboard(Request $request) {
        $mailbox = Session::get('mailbox');
        $data['title'] = 'Dashboard';
        $data['class'] = 'dashboard';
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
        return view('users/dashboard/dashboard' , $data);
    }

    public function listViewReady(){
        $mailbox = Session::get('mailbox');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' , 1)
                    ->where('packages.mailbox' , $mailbox);
        return DataTables::of($query)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
    }


    public function listViewRecent(){
        $mailbox = Session::get('mailbox');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' ,'!=' , 1)
                    ->where('packages.mailbox' , $mailbox);
        return DataTables::of($query)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
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

	public function profile(Request $request) {
		$data['title'] = 'Manages Profile';
		$data['class'] = 'dashboard';
		$data['table'] = 'Update Profile';
		$user_id= Session::get('user_id');
		if ($request->isMethod('get')) {
			$data['users'] = User::where('user_id' , $user_id)->first();
			return view('front/dashboard/create' , $data);
		}else{
			$validator = Validator::make($request->all(), [
				'name' => 'required',
				'password' => 'same:confirm_pass',
				'phone' => 'required|numeric',
				'address' => 'required',
			]);
			if ($validator->fails()) {
				return redirect('user/profile')
					->withInput()
					->withErrors($validator);

			}else{
				$password = $request->password;
				if($password == ''){
					User::where('user_id', $user_id)
							  ->update([
										'name' => $request->name,
										'phone' => $request->phone,
										'address' => $request->address
								]);
					Session::put('name', $request->name);
					Session::flash('success_msg', 'Profile has been updated successfully.');
					return redirect('user/profile');
				}else{
					User::where('user_id', $user_id)
							  ->update([
										'name' => $request->name,
										'phone' => $request->phone,
										'address' => $request->address,
										'password' => Hash::make($password)

								]);
					Session::put('name', $request->name);
					Session::put('phone', $request->phone);
					Session::flash('success_msg', 'Profile has been updated successfully.');
					return redirect('user/profile');
				}
			}
		}

    }
}
