<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="images/favicon.png">

    <title>Reset Password</title>

    <!--Core CSS -->
    <link href="{{asset('public/admin/bs3/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/bootstrap-reset.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/font-awesome/css/font-awesome.css')}}" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="{{asset('public/admin/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/style-responsive.css')}}" rel="stylesheet" />

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

  <body class="login-body">

    <div class="container">
        @include('common.errors')
		<? if(Session::has('change_success_msg')) { ?>
            <div class="alert alert-danger alert-dismissable" style="margin:10px auto; width:30%;">
            <button type="button" class="close close-sm" data-dismiss="alert">
                <i class="fa fa-times"></i>
            </button>
            
             <p style="text-align:center;">  {{ Session::get('change_success_msg') }}  </p>
            
            </div>
        <? } ?>
      <form class="form-signin" action="{{URL::to('resetPassword/'.$email)}}" method="post">
      {!! csrf_field() !!}
        <h2 class="form-signin-heading">Reset Password</h2>
        
        <div class="login-wrap">
            <div class="user-login-info">
                <input type="password" class="form-control" placeholder="New Password" name="new_password" autofocus>
                <input type="password" class="form-control" placeholder="Confirm Password" name="confirm_password" >
            </div>
            
            <button class="btn btn-lg btn-login btn-block" type="submit">Reset Password</button>
        </div>

      </form>

    </div>



    <!-- Placed js at the end of the document so the pages load faster -->

    <!--Core js-->
    <script src="{{asset('public/admin/js/jquery.js')}}"></script>
    <script src="{{asset('public/admin/bs3/js/bootstrap.min.js')}}"></script>

  </body>
</html>
