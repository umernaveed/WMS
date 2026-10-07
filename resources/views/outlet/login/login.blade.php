<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="images/favicon.png">
    <title>Login</title>
    <!--Core CSS -->
    <link href="{{asset('public/admin/bs3/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/bootstrap-reset.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/font-awesome/css/font-awesome.css')}}" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="{{asset('public/admin/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/style-responsive.css')}}" rel="stylesheet" />
</head>

  <body class="login-body">

    <div class="container">
		@include('common.errors')
		<? if(Session::has('error_msg')) { ?>
            <div class="alert alert-block alert-success fade in" style="margin:10px auto; width:30%;">
            <button type="button" class="close close-sm" data-dismiss="alert">
                <i class="fa fa-times"></i>
            </button>
            
             <p style="text-align:center;">  {{ Session::get('error_msg') }}  </p>
            
            </div>
        <? } ?>
      <form class="form-signin" action="{{URL::to('user/login')}}" method="post">
      {!! csrf_field() !!}
        <h2 class="form-signin-heading">Being Generous</h2>
        <div class="login-wrap">
            <div class="user-login-info">
                <input type="text" class="form-control" placeholder="User ID" name="email" autofocus>
                <input type="password" class="form-control" placeholder="Password" name="password" >
            </div>
            <label class="checkbox">
                <input type="checkbox" value="remember-me"> Remember me
                <span class="pull-right">
                    <a data-toggle="modal" href="#myModal"> Forgot Password?</a>

                </span>
            </label>
            <button class="btn btn-lg btn-login btn-block" type="submit">Sign in</button>

            <div class="registration">
                Don't have an account yet?
                <a class="" href="{{URL::to('register')}}">
                    Create an account
                </a>
            </div>

        </div>
	 </form>
          <!-- Modal -->
          <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Forgot Password ?</h4>
                      </div>
                      <form  id="formForget" action="{{URL::to('forget/password')}}" method="post">
      				  {!! csrf_field() !!}
                          <div class="modal-body">
                              <div class="alert alert-danger alert-dismissable fade in" id="email_msg" style="display:none;margin-top:5px; padding-top:10px; padding-bottom:10px;">
                                    <button type="button" class="close close-sm" data-dismiss="alert">
                                        <i class="fa fa-times"></i>
                                    </button>
                                     <p>The email field is required.</p>
                              </div>
                              <p>Enter your e-mail address below to reset your password.</p>
                              <input type="text" name="forgot_email" placeholder="Email" id="forgot_email" autocomplete="off" class="form-control placeholder-no-fix">
                              
                          </div>
                          <div class="modal-footer">
                              <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                              <button class="btn btn-success" type="button" onClick="checkValidation()">Submit</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
          <!-- modal -->

    </div>



    <!-- Placed js at the end of the document so the pages load faster -->

    <!--Core js-->
    <script src="{{asset('public/admin/js/jquery.js')}}"></script>
    <script src="{{asset('public/admin/bs3/js/bootstrap.min.js')}}"></script>
<script>
	function checkValidation() {
		var forgot_email  = $("#forgot_email").val();
		
		var count = 0;
		if(forgot_email == ""){
			$("#email_msg").show().delay(3000).fadeOut();
			count++;
		} else{
			$("#email_msg").hide();
		}
	
		if(count == 0){
			$("#myModal").hide();
		   $("#formForget").submit() 
		}
	}
</script>
  </body>
</html>
