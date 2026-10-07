<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustomRate;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;

class RateControllor extends Controller
{
	
	public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
    public function index(){
        $data['title'] = 'Manage Custom Rate';
        $data['class'] = 'settings';
        $data['table'] = 'Manage Custom Rate';
        $data['rates'] = CustomRate::get();
        return view('admin/rates/index' , $data);
    }

    public function create(Request $request){
        $data['title'] = 'Manage Custom Rate';
        $data['class'] = 'settings';
        $data['table'] = 'Add Custom Rate';
        $user_id = Session::get('user_id');
        if ($request->isMethod('get')) {
            return view('admin/rates/create' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'icd' => 'required',
                                'scf' => 'required',
                                'icdgct' => 'required',
                                'enl' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/rate/add')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{

                    $rates = new CustomRate();
                    $rates->name = $request->name;
                    $rates->icd = $request->icd;
                    $rates->scf = $request->scf;
                    $rates->icdgct = $request->icdgct;
                    $rates->enl = $request->enl;
                    $rates->added_by = $user_id;
                    $rates->date_added = date('Y-m-d h:i:s');
                    $rates->date_last_updated = date('Y-m-d h:i:s');
                    $rates->save();
                    Session::flash('success_msg', 'Custom Rate has been added successfully'); 
                    return redirect('admin/rates');
            }
        }
    }

    public function edit(Request $request , $id = ''){
        $data['title'] = 'Manage Custom Rate';
        $data['class'] = 'settings';
        $data['table'] = 'Update Custom Rate';
        if ($request->isMethod('get')) {
            $data['rate'] = CustomRate::where('custom_rate_id' , $id)->first();
            return view('admin/rates/edit' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                            'name' => 'required',
                            'icd' => 'required',
                            'scf' => 'required',
                            'icdgct' => 'required',
                            'enl' => 'required'
                        ]);
            if ($validator->fails()) {
                return redirect('admin/rate/edit/'.$id)
                                ->withInput()
                                ->withErrors($validator);
            }else{
                CustomRate::where('custom_rate_id', $id)
                        ->update([
                                'name' => $request->name,
                                'icd' => $request->icd,
                                'scf' => $request->scf,
                                'icdgct' => $request->icdgct,
                                'enl' => $request->enl
                            
                            ]);
                Session::flash('success_msg', 'Custom Rate has been updated successfully.'); 
                return redirect('admin/rates');
            }
        }
    }
    
    public function calculater(Request $request){
        $data['title'] = 'Manage Calculator';
        $data['class'] = 'calculator';
        
        if ($request->isMethod('get')) {
            $data['table'] = 'Manage Calculator';
            $data['rate'] = CustomRate::orderby('name' , 'ASC')->get();
            return view('admin/rates/calculater' , $data);
         }else{
             $data['table'] = 'Calculator Results';
             $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'weight' => 'required',
                                'amount' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/calculater')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                $us_rate = DB::table('settings')->first()->us_rate;
                $usRate = $us_rate;
                $rate = CustomRate::where('custom_rate_id' , $request->name)->first();
                //dd($rate);
                $icdDB 	  = $rate->icd;
                $scfDB    = $rate->scf;
                $icdgctDB = $rate->icdgct;
                $enlDB    = $rate->enl;
                
                
                $weight = $request->weight;
                $amount = $request->amount;
	
                //Cost Determination
if($weight == 1) {
                    $cost = DB::table('weights')->where('weight' , $weight)->first()->price;
                }
                if($weight == 2) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 3) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 4) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 5) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 6) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 7) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 8) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 9) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 10){$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight > 10 && $weight <= 20){
                    $nW = $weight - 10;
                    $cPerPound = $nW * 2.50;
                    $cost = $cPerPound + 31.00;
                }
                if($weight > 20 && $weight < 151){
                    $nW = $weight - 10;
                    $cPerPound = $nW * 2;
                    $cost = $cPerPound + 31.00;
                }
                if($amount < 50){
                    $ship  = $cost;
                    $proc  = 1.50;
                    $gct   = 0.25;
                    $total = $ship + $proc + $gct;
                    $totDesc = "Total Charges";
                    $data['ship'] = $ship;
                    $data['proc'] = $proc;
                    $data['gct'] = $gct;
                    $data['total'] = $total;
                    $data['totDesc'] = $totDesc;
                }else{
                    $ship  = $cost;
                    $proc  = 6;
                    $gct   = 1;
                    $cif   = $ship + $proc + $amount;

                    $ins   = ($cif * 2)/100;
                    $icd   = ($cif * $icdDB)/100;
                    $enl   = ($cif * $enlDB)/100;;
                    $scf   = ($cif * $scfDB)/100;;
                    $caf = 2500 / $usRate;
                    $sd  = 100 / $usRate;
                    $icdgct = ($icd + $enl + $scf + $caf + $sd + $cif + $ins)*$icdgctDB/100;
                    $custCharges = $icd + $enl + $scf + $caf + $sd + $icdgct + $ins;
                    $total = $ship + $proc + $gct + $custCharges;
                    $totDesc = "Total Import Custom Duty Charges";
                    $data['ship'] = $ship;
                    $data['proc'] = $proc;
                    $data['gct'] = $gct;
                    
                    $data['ins'] = $ins;
                    $data['icd'] = $icd;
                    $data['enl'] = $enl;
                    $data['scf'] = $scf;
                    $data['icdgct'] = $icdgct;
                    $data['custCharges'] = $custCharges;
                    $data['caf'] = $caf;
                    $data['sd'] = $sd;
                    
                    $data['total'] = $total;
                    $data['totDesc'] = $totDesc;
                }
                $data['usRate'] = $usRate;
                $data['amount'] = $amount;
                
                $data['gct'] = $gct;
                $data['total'] = $total;
                return view('admin/rates/result' , $data);
            }
         }
    }
    
    public function calculaterHome(Request $request){
        $data['title'] = 'Manage Calculator';
        $data['class'] = 'calculator';
        
        if ($request->isMethod('get')) {
            $data['table'] = 'Manage Calculator';
            $data['rate'] = CustomRate::orderby('name' , 'ASC')->get();
            return view('admin/rates/calculater_home' , $data);
         }else{
             $data['table'] = 'Calculator Results';
             $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'weight' => 'required',
                                'amount' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('admin/calculater/home')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                $us_rate = DB::table('settings')->first()->us_rate;
                $usRate = $us_rate;
                $rate = CustomRate::where('custom_rate_id' , $request->name)->first();
                //dd($rate);
                $icdDB 	  = $rate->icd;
                $scfDB    = $rate->scf;
                $icdgctDB = $rate->icdgct;
                $enlDB    = $rate->enl;
                
                
                $weight = $request->weight;
                $amount = $request->amount;
	
                //Cost Determination
if($weight == 1) {
                    $cost = DB::table('weights')->where('weight' , $weight)->first()->price;
                }
                if($weight == 2) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 3) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 4) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 5) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 6) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 7) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 8) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 9) {$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight == 10){$cost = DB::table('weights')->where('weight' , $weight)->first()->price;}
                if($weight > 10 && $weight <= 20){
                    $nW = $weight - 10;
                    $cPerPound = $nW * 2.50;
                    $cost = $cPerPound + 31.00;
                }
                if($weight > 20 && $weight < 151){
                    $nW = $weight - 10;
                    $cPerPound = $nW * 2;
                    $cost = $cPerPound + 31.00;
                }
                if($amount < 50){
                    $ship  = $cost;
                    $proc  = 1.50;
                    $gct   = 0.25;
                    $total = $ship + $proc + $gct;
                    $totDesc = "Total Charges";
                    $data['ship'] = $ship;
                    $data['proc'] = $proc;
                    $data['gct'] = $gct;
                    $data['total'] = $total;
                    $data['totDesc'] = $totDesc;
                }else{
                    $ship  = $cost;
                    $proc  = 6;
                    $gct   = 1;
                    $cif   = $ship + $proc + $amount;

                    $ins   = ($cif * 2)/100;
                    $icd   = ($cif * $icdDB)/100;
                    $enl   = ($cif * $enlDB)/100;;
                    $scf   = ($cif * $scfDB)/100;;
                    $caf = 2500 / $usRate;
                    $sd  = 100 / $usRate;
                    $icdgct = ($icd + $enl + $scf + $caf + $sd + $cif + $ins)*$icdgctDB/100;
                    $custCharges = $icd + $enl + $scf + $caf + $sd + $icdgct + $ins;
                    $total = $ship + $proc + $gct + $custCharges;
                    $totDesc = "Total Import Custom Duty Charges";
                    $data['ship'] = $ship;
                    $data['proc'] = $proc;
                    $data['gct'] = $gct;
                    
                    $data['ins'] = $ins;
                    $data['icd'] = $icd;
                    $data['enl'] = $enl;
                    $data['scf'] = $scf;
                    $data['icdgct'] = $icdgct;
                    $data['custCharges'] = $custCharges;
                    $data['caf'] = $caf;
                    $data['sd'] = $sd;
                    
                    $data['total'] = $total;
                    $data['totDesc'] = $totDesc;
                }
                $data['usRate'] = $usRate;
                $data['amount'] = $amount;
                
                $data['gct'] = $gct;
                $data['total'] = $total;
                return view('admin/rates/result' , $data);
            }
         }
    }
	
}
