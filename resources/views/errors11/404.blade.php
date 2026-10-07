@extends('errors.template')
@section('content')
    <!------ ERROR-SECTION-START  ------>
    <section class="error-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="error-bg text-center">
                        <img class="error-img" src="{{asset('front/assets/images/404-error-img.jpg')}}">
                        <div class="error-text">
                            <h2>Sorry :)-</h2>
                            <h4>Page Not Found</h4>
                            <a href="{{url('/')}}" class=" pink-btn" type="button">Go To Home</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!------ ERROR-SECTION-END  ------>
@endsection 