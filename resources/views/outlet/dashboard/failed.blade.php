@extends('outlet.template')
<style>
.error-desk {
	margin-top:0px !important;
}
.container {
	width:100% !important;
	padding:0px !important;
	margin:0px !important;
}
.back-btn {
	margin:25px 0 !important;
}
</style>
@section('content')
	<div class="error-head"> </div>

    <div class="container body-404" >

      <section class="error-wrapper text-center">
          <h1><img src="{{asset('public/admin/images/500.png')}}" width="180" height="140" alt=""></h1>
          <div class="error-desk">
              <h2>Payments Failed!</h2>
              <!--<p class="nrml-txt">We Couldnot Find This Page</p>-->
          </div>
          <a href="{{URL::to('user/donation')}}" class="back-btn"><i class="fa fa-home"></i> Back To Home</a>
      </section>

    </div>
@endsection 

      