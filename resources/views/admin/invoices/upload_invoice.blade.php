@extends('admin.template')
@section('content')

<style>
.radio, .checkbox{
	padding-left:0px;
}
.icheck div, .icheck .disabled {
    float: left;
}
.icheck .single-row {
    display: inline-block;
    width: 100%;
}
</style>
<div class="row">
<? if(Session::has('success_msg')) { ?>
        <div class="alert alert-block alert-success fade in" style="margin:10px 15px;">
        <button type="button" class="close close-sm" data-dismiss="alert">
            <i class="fa fa-times"></i>
        </button>
        
         <p style="text-align:center;">  {{ Session::get('success_msg') }}  </p>
        
        </div>
    <? } ?>
	<div class="col-lg-12">
            
     @include('common.errors')
         <section class="panel">
                <header class="panel-heading">
                    <?php echo $table; ?>
                </header>
                <div class="panel-body">
                    <div class="position-center" style="width:80%;">
                        <form role="form" method="post" action="" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;color: #1FB5AD;font-size: 22px;">Your Tracking#: {{$packege->tracking_no}} </label>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;">Status: {{$title}} </label>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;">Courier Tracking: <span style="color: #1FB5AD;">{{$packege->courier}}</span> </label>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;">Weight / Volume: <span style="color: #1FB5AD;">{{method_exists($packege, 'measureDisplay') ? $packege->measureDisplay() : $packege->weight.' lb'}}</span> </label>
                            </div>
                            @if($packege->invoice != '')
                                <a href="{{URL::to('public/invoices/'.$packege->invoice)}}" download><button type="button" class="btn btn-success"><i class="fa fa-print"></i> Download invoice </button></a>
                                <a href="{{URL::to('user/invoice/delete/'.$packege->tracking_no)}}"><button type="button" class="btn btn-warning"><i class="fa fa-trash-o"></i> Delete invoice </button></a>
                            @else
                                <div class="form-group">
                                    <label class="col-md-12" style="padding:10px 0px;">Attach an invoice </label>
                                    <hr>
                                    <input type="file" name="input_img" class="default" />
                                 </div>
                                <input type="submit" class="btn btn-info" value="Upload Invoice">
                            @endif
                            
                        </form>
                     </div>
                 </div>
                    
         </section>               
        </div>
    </div>
@endsection 

      
