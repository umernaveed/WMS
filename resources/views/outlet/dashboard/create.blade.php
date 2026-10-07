@extends('outlet.template')
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
                            <label for="exampleInputEmail1">User Name</label>
                            <input type="text" name="name" value="{{$users->name}}" placeholder="User Name" class="form-control">
                        </div>
                        
                        <div class="form-group">
                        	<label for="exampleInputEmail1">User Email</label>
                            <input type="text" name="email " value="{{$users->email}}" readonly class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Phone</label>
                            <input type="text" name="phone" value="{{$users->phone}}" placeholder="Phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Address</label>
                            <input type="text" name="address" value="{{$users->address}}" placeholder="Address" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Password</label>
                            <input type="password" name="password" value="" placeholder="Password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Confirm Password</label>
                            <input type="password" name="confirm_pass" value="" placeholder="Confirm Password" class="form-control">
                        </div>
                        
                        <input type="submit" class="btn btn-info" value="Update Profile">
                    </form>
                    </div>
                </div>
    </div>
</div>  
@endsection 

      