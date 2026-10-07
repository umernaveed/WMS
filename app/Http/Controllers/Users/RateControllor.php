<?php

namespace App\Http\Controllers\Outlet;

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
    
    public function calculater(Request $request){
        $data['title'] = 'Manage Calculator';
        $data['class'] = 'calculator';
        
        if ($request->isMethod('get')) {
            $data['table'] = 'Manage Calculator';
            $data['rate'] = CustomRate::orderby('name' , 'ASC')->get();
            return view('outlet/rates/calculater' , $data);
         }else{
             $data['table'] = 'Calculator Results';
             $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'weight' => 'required',
                                'amount' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('outlet/calculater')
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
                return view('outlet/rates/result' , $data);
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
