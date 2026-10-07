<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <?php $getSettings = commonHelper::getSettings(); ?>
    <title>{{$getSettings->site_title}} | {{$title}}</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/logo/favicon.png">
    <!-- plugins css -->
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/bootstrap/dist/css/bootstrap.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/PACE/themes/blue/pace-theme-minimal.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/perfect-scrollbar/css/perfect-scrollbar.min.css')}}" />
    <!-- core css -->
    <link href="{{asset('admins/assets/css/ei-icon.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/themify-icons.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/app.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="{{asset('admins/assets/css/bootstrap-material-datetimepicker.css')}}" />
    <style>
        p{
            margin-bottom: 5px !important;
        }
    </style>
</head>
<body>
    <div class="app">
        <div class="authentication">
            <div class="sign-up">
                <div class="row no-mrg-horizon">
                    <div class="col-md-4 no-pdd-horizon d-none d-md-block">
                        <div class="full-height bg" style="background-image: url('public/admins/assets/images/others/img-29.jpg')">
                            <div class="vertical-align full-height pdd-horizon-70">
                                <div class="table-cell">
                                    <div class="row">
                                        <div class="mr-auto ml-auto col-md-10">
                                            <div class="text-right">
                                                @if(file_exists(public_path().'/settings/'.$getSettings->site_logo))
                                                    <img class="img-responsive mrg-left-auto mrg-btm-15" src="{{url('').'/public/settings/'.$getSettings->site_logo}}" alt="{{$getSettings->site_name}}" width="170">
                                                @endif
                                                <!--<p class="text-white lead text-opacity lh-1-7">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has</p>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 bg-white no-pdd-horizon">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="full-height height-100">
                                    <div class="vertical-align full-height pdd-horizon-70">
                                        <div class="table-cell">
                                            <div class="pdd-horizon-15 mt-5">
                                                @include('common.errors')
                                                <?php if(Session::has('error_msg')) { ?>
                                                    <div class="alert alert-block alert-success fade in" style="margin:10px auto; width:30%;">
                                                    <button type="button" class="close close-sm" data-dismiss="alert">
                                                        <i class="fa fa-times"></i>
                                                    </button>

                                                     <p style="text-align:center;color: #3c763d;">  {{ Session::get('error_msg') }}  </p>

                                                    </div>
                                                <?php } ?>
                                                <h1 class="mrg-btm-30">Create Your account</h1>
                                                <form class="form-signin" method="post" action="" style=" max-width: 100%;">
                                                    {!! csrf_field() !!}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Outlet</label>
                                                                <select name="outlet_id" class="form-control" style="margin-bottom: 15px;">
                                                                <option value="" >Select Outlet</option>
                                                                @if(count($outlets) > 0)
                                                                    @foreach($outlets as $loc)
                                                                        <option value="{{$loc->outlet_id}}">{{$loc->outlet_name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">User Type</label>
                                                                <select class="form-control" name="user_type" id="demo">
                                                                    <option value="personal" @if(Request::old('type')== 'personal') selected ="" @endif>Personal</option>
                                                                    <option value="business" @if(Request::old('type')== 'business') selected ="" @endif>Business</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">First Name</label>
                                                                <input type="text" class="form-control" placeholder="First Name" name="first_name" value="{{Request::old('first_name')}}" >

                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Last Name</label>
                                                                <input type="text" class="form-control" placeholder="Last Name" value="{{Request::old('last_name')}}" name="last_name" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Email</label>
                                                                <input type="email" class="form-control" placeholder="Email" name="email" value="{{Request::old('email')}}">

                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Confirm Email</label>
                                                                <input type="email" class="form-control" placeholder="Confirm Email" value="{{Request::old('confirm_email')}}" name="confirm_email" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Password</label>
                                                                <input type="password" class="form-control" placeholder="Password" value="{{Request::old('password')}}" name="password">

                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Re-type Password</label>
                                                                <input type="password" class="form-control" placeholder="Re-type Password" value="{{Request::old('confirm_password')}}" name="confirm_password">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">TRN</label>
                                                                <input type="text" class="form-control" placeholder="TRN #" value="{{Request::old('trn')}}" maxlength="9" name="trn" >
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Phone</label>
                                                                <input type="text" class="form-control only_numbers" placeholder="Phone" value="{{Request::old('phone')}}" maxlength="10" name="phone">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Mobile</label>
                                                                <input type="text" class="form-control only_numbers" placeholder="Mobile" value="{{Request::old('mobile')}}" maxlength="10" name="mobile" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row business_info" style=" display: none;">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">TCC Issue Date</label>
                                                                <input type="text" class="form-control form_datetime" placeholder="TCC Issue Date" id="start_date" name="tcc_issue_date">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">TCC Expiry Date</label>
                                                                <input type="text" class="form-control form_datetime" placeholder="TCC Expiry Date" id="end_date" name="tcc_expiry_date">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Business TRN #</label>
                                                                <input type="text" class="form-control" placeholder="Business TRN #" name="Business_trn">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Address 1</label>
                                                                <input type="text" class="form-control" placeholder="Address 2" name="address_1" value="{{Request::old('address_1')}}">

                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Address 2</label>
                                                                <input type="text" class="form-control" placeholder="Address 2" name="address_2" value="{{Request::old('address_2')}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">City</label>
                                                                <input type="text" class="form-control" placeholder="City" value="{{Request::old('city')}}" name="city">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">State/Parish</label>
                                                                <input type="text" class="form-control" placeholder="State/Parish" value="{{Request::old('state')}}" name="state">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Country</label>
                                                                <select name="country" class="form-control" style="margin-bottom: 15px;">
                                                                    <option value="Jamaica">Jamaica</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="text-normal text-dark">Currency</label>
                                                                <select name="currency" class="form-control" style="margin-bottom: 15px;">
                                                                    <option value="JMD">JMD</option>
                                                                    <option value="USD">USD</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
<!--                                                    <div class="checkbox font-size-13 mrg-btm-20">
                                                        <input id="agreement" name="agreement" type="checkbox" checked="">
                                                        <label for="agreement">Remember Me</label>
                                                    </div>-->
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary btn-block border-radius-6">Create New Account</button>
                                                    </div>
                                                </form>
                                                <p>Already have an account? <a href="{{url('/')}}">Sign In</a></p>
<!--                                                <hr>
                                                <small>By signing up you agree to out <a href="">Terms & Policy</a></small>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- plugins js -->
    <script src="{{asset('admins/assets/vendors/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/popper.js/dist/umd/popper.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/bootstrap/dist/js/bootstrap.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/PACE/pace.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}}"></script>
    <!-- endbuild -->
    <!-- build:js assets/js/app.min.js -->
    <!-- core js -->
    <script src="{{asset('admins/assets/js/app.js')}}"></script>
    <!-- endbuild -->
    <!-- page js -->
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script type="text/javascript" src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="{{asset('admins/assets/js/bootstrap-material-datetimepicker.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#start_date').bootstrapMaterialDatePicker({ 
                weekStart : 0, 
                time: false,
                minDate: new Date('2019-05-11'),
            });
            $('#end_date').bootstrapMaterialDatePicker({ 
                weekStart : 0, 
                time: false,
                maxDate: new Date(),
            });
            
            $('#demo').on('change', function() {
                var id = this.value;
                if(id == 'business'){
                    $('.business_info').show();
                }else{
                    $('.business_info').hide();
                }
            });
	});
	$(".alert-success").delay(2000).fadeOut();
        $(".alert-danger").delay(2000).fadeOut();
        $('.only_numbers').keyup(function(e)
        {
            if (/\D/g.test(this.value))
            {
              // Filter non-digits from input value.
              this.value = this.value.replace(/\D/g, '');
            }
        });
</script>
</body>
</html>
