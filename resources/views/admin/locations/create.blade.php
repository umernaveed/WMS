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
                        <label for="exampleInputEmail1">Title</label>
                        <input type="text" name="title" value="{{Request::old('title')}}" placeholder="Title" class="form-control">
                    </div>
                    <input type="submit" class="btn btn-info" value="<?php echo $table; ?>">
                </form>
                </div>
            </div>
         </section>
    </div>
</div> 
@endsection 

      