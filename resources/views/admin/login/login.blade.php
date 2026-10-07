<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $getSettings = commonHelper::getSettings(); ?>
    <title>{{$getSettings->site_title}} | {{$title}}</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/bootstrap/dist/css/bootstrap.css')}}">
    <link href="{{asset('admins/assets/css/font-awesome.min.css')}}" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #0b3a6e;
        }

        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            background: #f4f6f9;
            padding: 40px 30px;
            width: 420px;
            border-radius: 8px;
            text-align: center;
        }

        .login-title {
            font-weight: 700;
            margin-bottom: 25px;
        }

        .logo-box img {
            width: 120px;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        .custom-input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #cfd6e4;
            background: #e9edf5;
            outline: none;
        }

        .password-box {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 38px;
            color: #666;
            cursor: pointer;
        }

        /* Checkbox styling */
        .checkbox-group {
            text-align: left;
            margin: 15px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0b3a6e;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            font-size: 14px;
        }

        .login-btn {
            width: 100%;
            background: #0b3a6e;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #0a2d55;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
        }

        .forgot-link {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-link a {
            color: #0b3a6e;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Title -->
        <h2 class="login-title">Staff Warehouse Login</h2>

        <!-- Logo -->
        <div class="logo-box">
            @if(file_exists(public_path().'/settings/'.$getSettings->site_logo))
                <img src="{{url('').'/public/settings/'.$getSettings->site_logo}}">
            @else
                <span>{{ $getSettings->site_name ?? $getSettings->company_name ?? $getSettings->site_title }}</span>
            @endif
        </div>

        <!-- Alerts -->
        @include('common.errors')

        @if(Session::has('success_msg'))
            <div class="alert alert-success text-center">
                {{ Session::get('success_msg') }}
            </div>
        @endif

        @if(Session::has('error_msg'))
            <div class="alert alert-danger text-center">
                {{ Session::get('error_msg') }}
            </div>
        @endif

        <!-- Form -->
        <form method="post" id="loginForm">
            {!! csrf_field() !!}

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" class="custom-input"
                       value="<?php if(isset($_COOKIE['username'])) echo htmlspecialchars($_COOKIE['username']); ?>">
            </div>

            <div class="form-group password-box">
                <label>Password</label>
                <input type="password" name="password" id="password" class="custom-input"
                       value="<?php if(isset($_COOKIE['password'])) echo htmlspecialchars($_COOKIE['password']); ?>">
                <span class="eye-icon"><i class="fa fa-eye"></i></span>
            </div>

            <!-- Keep Me Signed In Checkbox -->
            <div class="checkbox-group">
                <input type="checkbox" name="keep_signed_in" id="keep_signed_in" 
                       <?php if(isset($_COOKIE['username']) && isset($_COOKIE['password'])) echo 'checked'; ?>>
                <label for="keep_signed_in">Keep Me Signed In</label>
            </div>

            <button type="submit" class="login-btn">Sign In</button>

            <div class="forgot-link">
                <a href="{{URL::to('admin/forget/password')}}">Forgot Password?</a>
            </div>
        </form>

    </div>
</div>

<!-- JS -->
<script src="{{asset('admins/assets/vendors/jquery/dist/jquery.min.js')}}"></script>

<script>
    // Toggle password visibility
    $('.eye-icon').click(function(){
        let input = $(this).siblings('input');
        if(input.attr('type') === 'password'){
            input.attr('type','text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type','password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Auto hide alerts
    $(".alert").delay(3000).fadeOut();

    // Optional: Additional client-side validation and cookie persistence note
    // The cookie is set on the server-side after successful login
    // The checkbox is pre-filled if cookies exist (as shown above)
</script>

</body>
</html>
