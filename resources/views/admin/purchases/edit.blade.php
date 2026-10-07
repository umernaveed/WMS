@extends('admin.template')
@section('content')

<div class="row">
    <div class="col-lg-12">
        @include('common.errors')
        <? if(Session::has('error_msg')) { ?>
            <div class="alert alert-block alert-success fade in" style="margin:10px auto; width:30%;">
            <button type="button" class="close close-sm" data-dismiss="alert">
                <i class="fa fa-times"></i>
            </button>

             <p style="text-align:center;">  {{ Session::get('error_msg') }}  </p>

            </div>
        <? } ?>
        <section class="panel">
            <header class="panel-heading">
                <?php echo $table; ?>
            </header>
            <div class="panel-body">
                <div class="position-center" style=" width: 100%;">
                    <form id="invoice" method="post" action="">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" name="name" value="{{$purchase->name}}" placeholder="Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Link</label>
                            <input type="text" name="link" value="{{$purchase->link}}" placeholder="Link" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Quantity</label>
                            <input type="text" name="qty" value="{{$purchase->qty}}" placeholder="Quantity" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"> Notes </label>
                            <textarea name="notes" placeholder="Notes" class="form-control">{{$purchase->notes}}</textarea>
                        </div>
                        
                        <input type="submit" class="btn btn-info" value="<?php echo $table; ?>"/>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection 