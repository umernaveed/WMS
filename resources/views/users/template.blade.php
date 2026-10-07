<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <?php $getSettings = commonHelper::getSettings(); ?>
    <title>{{$getSettings->site_title}} | {{$title}}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('front/assets/images/favicon-32x32.png')}}">

    <!-- plugins css -->
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/bootstrap/dist/css/bootstrap.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/PACE/themes/blue/pace-theme-minimal.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/perfect-scrollbar/css/perfect-scrollbar.min.css')}}" />

    <!-- page plugins css -->
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/bower-jvectormap/jquery-jvectormap-1.2.2.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/nvd3/build/nv.d3.min.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/datatables/media/css/jquery.dataTables.css')}}" />
    

    <!-- core css -->
    <link href="{{asset('admins/assets/css/ei-icon.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/themify-icons.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/dropzone.css')}}" rel="stylesheet">
    <link href="{{asset('admins/assets/css/wms-action-dropdowns.css')}}?v={{ file_exists(public_path('admins/assets/css/wms-action-dropdowns.css')) ? filemtime(public_path('admins/assets/css/wms-action-dropdowns.css')) : time() }}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/selectize/dist/css/selectize.default.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/vendors/summernote/dist/summernote.css')}}" />
    
    <link rel="stylesheet" type="text/css" href="{{asset('admins/assets/vendors/select2/select2.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('admin/js/jquery-multi-select/css/multi-select.css')}}" />
    <link rel="stylesheet" href="{{asset('admins/assets/css/bootstrap-material-datetimepicker.css')}}" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="{{asset('admins/assets/vendors/jquery/dist/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('admins/assets/js/notify.min.js') }}"></script>
    <script type="text/javascript">
        var APP_URL = {!! json_encode(url('/')) !!}
    </script>
    <style>
          td {
            padding: 15px;
            word-wrap: anywhere;
        }
    </style>
</head>
<body>
    <div class="app">
        <div class="layout">
            <!--sidebar start-->
                @include('users.side_menu')
            <!--sidebar end-->
            <!--header start-->
            <div class="page-container">
                @include('users.header')   
                <!--header end-->
                <!--main content start-->
                @yield('content')
                <!--main content end-->
                <!-- Footer START -->
                @include('users.footer')
                <!-- Footer END -->
            </div>
            <!-- Page Container END -->
        </div>
    </div>
    <!-- build:js assets/js/vendor.js -->
    <!-- plugins js -->
    
    <script src="{{asset('admins/assets/vendors/popper.js/dist/umd/popper.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/bootstrap/dist/js/bootstrap.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/PACE/pace.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}}"></script>
    <!-- endbuild -->
    <!-- page plugins js -->
    <script src="{{asset('admins/assets/vendors/bower-jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/jquery.sparkline/index.js')}}"></script>
    <!-- build:js assets/js/app.min.js -->
    <!-- core js -->
    <script src="{{asset('admins/assets/js/app.js')}}"></script>
    <!-- page plugins js -->
    <script src="{{asset('admins/assets/vendors/datatables/media/js/jquery.dataTables.js')}}"></script>
    <!-- endbuild -->
    <!-- page js -->
    <script src="{{asset('admins/assets/js/table/data-table.js')}}"></script>
    <script src="{{asset('admins/assets/js/common_script.js')}}"></script>
    <!--<script src="{{asset('admins/assets/js/dashboard/dashboard.js')}}"></script>-->
    <script src="{{asset('admins/assets/vendors/jquery-validation/dist/jquery.validate.min.js')}}"></script>
    <!-- page js -->
    <script src="{{asset('admins/assets/js/forms/form-validation.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/selectize/dist/js/standalone/selectize.min.js')}}"></script>
    <script src="{{asset('admins/assets/vendors/summernote/dist/summernote.min.js')}}"></script>
    <script src="{{asset('admins/assets/js/forms/form-elements.js')}}"></script>
    <script src="{{asset('admins/assets/js/dropzone.js')}}"></script>
    
    <script src="{{asset('admins/assets/vendors/select2/select2.js')}}"></script>
    <script src="{{asset('admins/assets/js/select-init.js')}}"></script>
    <script src="{{asset('admins/assets/js/scripts.js')}}"></script>
    <script src="{{asset('admins/assets/js/wms-action-dropdowns.js')}}?v={{ file_exists(public_path('admins/assets/js/wms-action-dropdowns.js')) ? filemtime(public_path('admins/assets/js/wms-action-dropdowns.js')) : time() }}"></script>
    <!--<script type="text/javascript" src="{{ asset('admins/assets/js/bootstrap-material-datetimepicker.js')}}"></script>-->
    <script type="text/javascript" src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
        <script type="text/javascript" src="{{asset('admins/assets/js/bootstrap-material-datetimepicker.js')}}"></script>
    
    <script>
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
        });
        $( '#checkAll' ).click(function () {
            $( '#exportFrm input:checkbox' ).not( this ).prop( 'checked', this.checked );
        });
        $(".alert-success").delay(3000).fadeOut();
        $(".alert-danger").delay(3000).fadeOut();
        
        var imgArray = [];
        var myDropzone = new Dropzone(".dropzone", {		   
            url: APP_URL+"/product/files/upload",
            headers:{'X-CSRF-Token': $('input[name="_token"]').val()},
            maxFiles: 100,
            success : function(file, response){
                file.temp_name = response;
                imgArray.push(response);
                $("#save_img_btn").removeAttr('disabled');
                $("#product_images").val(imgArray);
                console.log(imgArray);
            }
        });
        $('#pally_size').bind('keyup paste', function(){
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
