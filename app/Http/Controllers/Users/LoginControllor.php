<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use URL;
use Mail;

class LoginControllor extends Controller
{
	
	public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
	
	public function showLoginForm(Request $request) {    
         return view('front/login/login');
    }
	
	public function loginProcess(Request $request) {    
		$email = $request->email;
		$password = $request->password;
		$validator = Validator::make($request->all(), [
						'email' => 'required',
						'password' => 'required',
					]);
		if ($validator->fails()) {
			return redirect('login')
					->withInput()
					->withErrors($validator);
		}else{
			$users = User::where('email', $email)->first();
			if($users){
				$user_password = $users->password;
				if(Hash::check($password, $user_password)){
					Session::put('user_id', $users->user_id);
					Session::put('name', $users->name);
					Session::put('email', $users->email);
					Session::put('phone', $users->phone);
					Session::flash('error_msg', 'You are logined in successfully!'); 
					return redirect('user/donation');
				}else{
					Session::flash('error_msg', 'Email or password is invalid!'); 
					return redirect('login');
				}
				
			}else{
				Session::flash('error_msg', 'Email or password is invalid!'); 
				return redirect('login');
			} 
		}
		   
    }
	
	public function logout(Request $request) {
        $request->session()->flush();
        //$request->session()->regenerate();
        return redirect('login');
    }
	
    public function register(Request $request) {
        $data['title'] = 'Register';
        if ($request->isMethod('get')) {
                return view('front/login/registr', $data);
        }else{
            
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|unique:users|max:255',
                'password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
                'phone' => 'required|max:10|numeric',
                'address' => 'required',
            ]);
            if ($validator->fails()) {
                    return redirect('register')
                            ->withInput()
                            ->withErrors($validator);
            }else{
                $users = new User();
                $users->name = $request->name;
                $users->email = $request->email;
                $users->phone = $request->phone;
                $users->address = $request->address;
                $users->password = Hash::make($request->password);
                $users->created_at = date('Y-m-d h:i:s');
                $users->updated_at = date('Y-m-d h:i:s');
                $users->save();

                Session::flash('error_msg', 'User has been register successfully!'); 
                return redirect('login');
            }
        }
    }
	
    public function forgetPassword(Request $request){
             $email = $request->forgot_email;
             $users = User::where('email', $email)->first();
            if ($users) {
                    $pin = base64_encode($email);
                    $link = URL::to('/') . '/resetPassword/' . $pin;
                    $email_subject = 'Forgot Password';
                    $user_name = $users->name;
                    $email_from = 'info@being-generous.com';
                    $this->send_email($email, $user_name, $email_subject, $email_from, $link, 'forget_email');
                    Session::flash('error_msg', 'Please check email to reset password.'); 
                    return redirect('login');

            }else{
                    Session::flash('error_msg', 'Email does not exist on our database!'); 
                    return redirect('login');
            }
    }

    public function send_email($email, $user_name, $email_subject, $email_from, $link,$view_name) {
    $res['userName'] = $user_name;
            $res['activationLink'] = $link;
    Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject) {
        $message->from($email_from, $name = 'being-generous');
        //$message->to($email, $user_name)->subject($email_subject);
        $message->to('rizwangill132@gmail.com', $user_name)->subject($email_subject);
    });
}

    public function resetPasswordWeb(Request $request , $email = '') {
		if ($request->isMethod('get')) {
			$data['email'] = $email;
			return view('front/login/reset_password', $data);
		}else{
			$validator = Validator::make($request->all(), [
				'new_password' => 'required|same:confirm_password',
				'confirm_password' => 'required',
			]);
			if ($validator->fails()) {
				return redirect('resetPassword/'.$email)
					->withInput()
					->withErrors($validator);
				
			}else{
				$user_email = base64_decode($email);
				$password = $request->input("new_password");
				
				User::where('email', $user_email)
          			->update(['password' => Hash::make($password)]);
				
				Session::flash('error_msg', 'your password has been reset successfully'); 
				return redirect('login');
			}
		}
	}
}
