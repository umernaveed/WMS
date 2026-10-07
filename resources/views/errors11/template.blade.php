<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hushdng | 404</title>
        
        <link rel="icon" type="image/png" sizes="32x32" href="{{asset('front/assets/images/favicon-32x32.png')}}">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="{{asset('front/assets/css/bootstrap.min.css')}}">
        <link href="{{asset('front/assets/fonts/font-awesome.min.css')}}" rel="stylesheet" media="all">
        <link href="{{asset('front/assets/css/style.css')}}" rel="stylesheet">
        <!--<link href="{{asset('front/assets/css/style4.css')}}" rel="stylesheet">-->
        <link href="{{asset('front/error11.css')}}" rel="stylesheet">
        <link rel='stylesheet' href="{{asset('front/assets/css/owl.carousel.css')}}">
        <link rel='stylesheet' href="{{asset('front/assets/css/uikit.min.css')}}">
        <link rel='stylesheet' href='https://rawgit.com/tempusdominus/bootstrap-4/master/build/css/tempusdominus-bootstrap-4.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css'>
        <script src="{{asset('front/assets/js/jquery.min.js')}}"></script>
        <link rel='stylesheet' href="{{asset('front/assets/css/tempusdominus-bootstrap-4.css')}}">
        <link rel="stylesheet" href="{{asset('front/assets/css/bootstrap-material-datetimepicker.css')}}" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script type="text/javascript">
            var APP_URL = {!! json_encode(url('/')) !!}
        </script>
    </head>
    <body>
        <main>
            @include('front.header')   
            <!--header end-->
            <!--main content start-->
            @yield('content')
            <!--main content end-->
            <!-- Footer START -->
            @include('front.footer')
            <!-- Footer END -->
            <div class="modal fade quick-view-wrapper" id="quickview">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                          <span type="text" class="close" data-dismiss="modal">&times;</span>
                        </div>
                        <!-- Modal body -->
                        <div class="productModelShow">
                            <div class="loaders-popup" style="display:none; margin: auto; width: 100%;text-align: center;">
                                <img src="{{asset('front/loader.gif')}}" alt="" class="img-fluid" style="width: 50px;"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!--Service Popup-->
            <div class="modal fade select-wrapper" id="select-services">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title services_title">Waxes</h4>
                            <span type="text" class="close close_service" data-dismiss="modal">&times;</span>
                        </div>
                        <!-- Modal body -->
                        <div class="loaders-popup" style="display:none; margin: auto; width: 100%;text-align: center;">
                            <img src="{{asset('front/loader.gif')}}" alt="" class="img-fluid" style="width: 50px;"/>
                        </div>
                        <div class="servicesModelShow">
                            
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <script src="{{asset('front/assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('front/assets/js/uikit.min.js')}}"></script>
        <script src="{{asset('front/assets/js/uikit-icons.min.js')}}"></script>
        <script src="{{asset('front/assets/js/owl.carousel.js')}}"></script>
        <script src="{{asset('front/assets/js/popper.min.js')}}"></script>
        <script src="{{asset('front/assets/js/bootstrap.min.js')}}"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js'></script>
        <script src="{{asset('front/assets/js/script.js')}}"></script>
        <script src="{{asset('front/js/common_script.js')}}"></script>
<!--        <script src="{{asset('front/assets/js/moment.min.js')}}"></script>
        <script src="{{asset('front/assets/js/tempusdominus-bootstrap-4.js')}}"></script>-->
        <script type="text/javascript" src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
        <script type="text/javascript" src="{{asset('front/assets/js/bootstrap-material-datetimepicker.js')}}"></script>
        <script>
            $(".alert-success").delay(3000).fadeOut();
            $(".alert-danger").delay(3000).fadeOut();
            $('.slider-for').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                fade: true,
                asNavFor: '.slider-nav'
            });
            $('.slider-nav').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                vertical:true,
                asNavFor: '.slider-for',
                dots: false,
                focusOnSelect: true,
                verticalSwiping:true,
                responsive: [
                {
                    breakpoint: 992,
                    settings: {
                      vertical: false,
                    }
                },
                {
                  breakpoint: 768,
                  settings: {
                    vertical: false,
                  }
                },
                {
                  breakpoint: 580,
                  settings: {
                    vertical: false,
                    slidesToShow: 3,
                  }
                },
                {
                  breakpoint: 380,
                  settings: {
                    vertical: false,
                    slidesToShow: 2,
                  }
                }
                ]
            });
        </script>
    </body>
</html>