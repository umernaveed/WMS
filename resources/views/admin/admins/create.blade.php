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
                                        <!--<div class="row">-->
                                        <!--    <div class="col-md-12">-->
                                        <!--        <p class="mrg-top-10 text-dark"> <b>User Image</b></p>-->
                                        <!--    </div>-->
                                        <!--    <div class="col-md-6">-->
                                        <!--        <div>-->
                                        <!--            <label for="img-upload" class="pointer">-->
                                        <!--                <img id="uploadPreview" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="120" height="120px" alt="">-->
                                        <!--                <span class="btn btn-default display-block no-mrg-btm" style="border-radius: 0px 0px 5px 5px;">Choose file</span>-->
                                        <!--                <input class="d-none" type="file" name="user_logo" id="img-upload" width="120" height="120px" onchange="PreviewImage();">-->
                                        <!--            </label>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--    <div class="col-md-12">-->
                                        <!--        <p class="mrg-top-10 text-dark" style=" color: #ff3c7e;" id="upload_logo_error"></p>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        <!--<hr>-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">First Name</label>
                                                    <input type="text" name="first_name" class="form-control" value="{{Request::old('first_name')}}" placeholder="First Name" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Last Name</label>
                                                    <input type="text" name="last_name" class="form-control" value="{{Request::old('last_name')}}" placeholder="Last Name" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Email</label>
                                                    <input type="text" name="email" class="form-control" value="{{Request::old('email')}}" placeholder="Email" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Password</label>
                                                    <input type="text" name="password" class="form-control" value="{{Request::old('password')}}" placeholder="Password" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Confirm Password</label>
                                                    <input type="text" name="confirm_password" class="form-control" value="{{Request::old('confirm_password')}}" placeholder="Confirm Password" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Roles</label>
                                                    <select class="selectize-group" name="role_id" id="role" required="" >
                                                        @if(count($roles) > 0)
                                                            @foreach($roles as $role)
                                                                <option value="{{$role->role_id}}">{{$role->title}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Status</label>
                                                    <select class="selectize-group" name="status" id="status" required="" >
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option> 
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <button type="button" class="btn btn-default" onclick="clearFields();">Clear</button>
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
    // $('#role').on('change', function() {
    //     var id = this.value;
    //     if(id == '1'){
    //         $('#show_module').hide();
    //     }else{
    //         $('#show_module').show();
    //     }
    // });
</script>
@endsection       
        
      