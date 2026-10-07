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

class DashboardControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function dashboard(Request $request) {
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
        return view('couriers/dashboard/dashboard' , $data);
    }

    public function listViewReady(){
        $mailbox = Session::get('mailbox');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' , 1)
                    ->where('packages.mailbox' , $mailbox);
        return DataTables::of($query)->make(true);
    }


    public function listViewRecent(){
        $mailbox = Session::get('mailbox');
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' ,'!=' , 1)
                    ->where('packages.mailbox' , $mailbox);
        return DataTables::of($query)->make(true);
    }

	public function profile(Request $request) {
		$data['title'] = 'Manage Profile';
		$data['class'] = 'profile';
		$data['table'] = 'Update Profile';
		$courierId = (int) Session::get('courier_id');
        $courier = Courier::where('id', $courierId)->first();
        if(! $courier){
            Session::flash('error_msg', 'Courier profile was not found.');
            return redirect('courier/dashboard');
        }

		if ($request->isMethod('get')) {
			$data['courier'] = $courier;
			return view('couriers/dashboard/profile' , $data);
		}else{
			$validator = Validator::make($request->all(), [
				'name' => 'required|string|max:255',
				'email' => 'required|email|max:255|unique:couriers,email,'.$courierId,
				'phone' => 'nullable|string|max:50',
				'city' => 'nullable|string|max:120',
				'state' => 'nullable|string|max:120',
				'zip' => 'nullable|string|max:40',
				'local_address' => 'nullable|string|max:255',
				'base_url' => 'nullable|string|max:255',
				'description' => 'nullable|string|max:1000',
				'logo' => 'nullable|image|max:4096',
			]);
			if ($validator->fails()) {
				return redirect('courier/profile')
					->withInput()
					->withErrors($validator);

			}else{
                $logo = $courier->logo;
                if ($request->hasFile('logo')) {
                    $imageName = uniqid('courier_', true).'.'.$request->logo->extension();
                    $path = public_path().'/couriers/';
                    if(! File::exists($path)){
                        File::makeDirectory($path, 0755, true);
                    }
                    $request->file('logo')->move($path, $imageName);
                    $logo = $imageName;
                }

                $courier->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    'local_address' => $request->local_address,
                    'base_url' => $request->base_url,
                    'description' => $request->description,
                    'logo' => $logo,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                Session::put('courier_name', $courier->name);
                Session::put('email', $courier->email);
                Session::put('avater', $courier->logo);
                Session::flash('success_msg', 'Profile has been updated successfully.');
                return redirect('courier/profile');
			}
		}

    }

    public function changePassword(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect('courier/profile#password');
        }

        $courierId = (int) Session::get('courier_id');
        $courier = Courier::where('id', $courierId)->first();
        if(! $courier){
            Session::flash('error_msg', 'Courier profile was not found.');
            return redirect('courier/dashboard');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'nullable|string',
            'password' => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect('courier/profile#password')->withInput()->withErrors($validator);
        }

        if($courier->password && ! Hash::check((string) $request->current_password, $courier->password)){
            return redirect('courier/profile#password')
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $courier->update([
            'password' => Hash::make($request->password),
            'hint_password' => $request->password,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success_msg', 'Password has been updated successfully.');
        return redirect('courier/profile#password');
    }
}
