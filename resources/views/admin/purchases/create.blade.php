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
	<div class="col-lg-12">
            <? if(Session::has('success_msg')) { ?>
        <div class="alert alert-block alert-success fade in" style="margin:10px 15px;">
        <button type="button" class="close close-sm" data-dismiss="alert">
            <i class="fa fa-times"></i>
        </button>
        
         <p style="text-align:center;">  {{ Session::get('success_msg') }}  </p>
        
        </div>
    <? } ?>
     @include('common.errors')
         <section class="panel">
                <header class="panel-heading">
                    <?php echo $table; ?>
                </header>
                <div class="panel-body">
                    <div class="position-center">
                        <form id="invoice" method="post" action="">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" name="name" value="{{Request::old('name')}}" placeholder="Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Link</label>
                            <input type="text" name="link" value="{{Request::old('link')}}" placeholder="Link" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Quantity</label>
                            <input type="text" name="qty" value="{{Request::old('qty')}}" placeholder="Quantity" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"> Notes </label>
                            <textarea name="notes" placeholder="Notes" class="form-control">{{Request::old('notes')}}</textarea>
                        </div>
                        
                        <input type="submit" class="btn btn-info" value="<?php echo $table; ?>"/>
                    </form>
                    </div>

                </div>
            </section> 
    </div>
</div> 

@endsection        
        
      