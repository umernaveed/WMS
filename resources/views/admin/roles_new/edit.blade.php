@extends('admin.template')
@section('content')
<!-- Content Wrapper START -->
<div class="main-content">
    <div class="container-fluid">
        
        <div class="row">
            @include('common.errors')
            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-heading border bottom">
                        <h4 class="card-title">{{$table}}</h4>
                    </div>
                    <div class="card-block">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-8 ml-auto mr-auto">
                                    <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="hidden" class="form-control" name="old_name" value="{{$role->title}}" placeholder="title" required>
                                                    <input type="text" class="form-control" name="title" id="title" value="{{$role->title}}" placeholder="title" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Label</label>
                                                    <input type="text" class="form-control" name="lable" id="lable" value="{{$role->lable}}" placeholder="Label" required>
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <button type="submit" class="btn btn-danger">{{$table}}</button>
                                        <a href="{{ URL::to('admin/roles') }}"><button type="button" class="btn btn-warning" style="margin-top: 0px;"><i class="fa fa-backward" ></i> Back</button></a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Content Wrapper END -->
<script>
    function clearFields(){
        $('#name').val('');
        $('#currency').val('');
        $('#time_zone').val('');
    }
    $('#price').bind('keyup paste', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    $('#no_of_days').bind('keyup paste', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endsection