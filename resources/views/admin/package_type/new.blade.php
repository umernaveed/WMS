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
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Type</label>
                                                    <input type="text" class="form-control" placeholder="type" name="type" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Code</label>
                                                    <input type="text" class="form-control" placeholder="code" name="code" required="">
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <!--<button type="button" class="btn btn-default" onclick="clearFields();">Clear</button>-->
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
        $('#form-1-5').val('');
        $('#title').val('');

    }
</script>
@endsection
