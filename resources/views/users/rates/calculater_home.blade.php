
@extends('admin.templateCal')
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
                    <?php echo $table; ?> | <strong style="font-size: 10px;">Attention : Please note the above is just an estimate  to clear  your package . </strong>
                </header>
                <div class="panel-body">
                    <div class="position-center" style="width:80%;">
                        <form role="form" method="post" action="" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Weight</label>
                            <input type="text" name="weight" value="{{Request::old('weight')}}" placeholder="Weight" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Product Type</label>
                            <select class="form-control" name="name">
                                <option value="">Select Product Type</option>
                                @if($rate)
                                    @foreach($rate as $rt)
                                        <option value="{{$rt->custom_rate_id}}">{{$rt->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Item Cost(USD)</label>
                            <input type="text" name="amount" value="{{Request::old('amount')}}" placeholder="Item Cost(USD)" class="form-control">
                        </div>
                        <input type="submit" class="btn btn-info" value="Request Quote">
                    </form>
                    </div>
                </div>
    </div>
</div> 

@endsection 

      