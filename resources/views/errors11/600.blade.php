@extends('errors.template')
@section('content')
    <!------ ERROR-SECTION-START  ------>
    <section class="error-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="error-bg text-center">
                        <img class="mb-3" src="{{asset('front/assets/images/500-img.jpg')}}">
                        <div class="error-text">
                            <h4>Internet Server errror</h4>
                            <a href="{{url('/')}}" class=" pink-btn" type="button">Go To Home</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!------ ERROR-SECTION-END  ------>
@endsection 