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
                                        <!--                @if($user->image == '')-->
                                        <!--                    <img id="uploadPreview" src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="120" alt="" />-->
                                        <!--                @else-->
                                        <!--                    <img id="uploadPreview" src="{{asset('users/'.$user->image)}}" width="120" alt="" />-->
                                        <!--                @endif-->
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
                                                    <input type="text" name="first_name" class="form-control" value="{{$user->first_name}}" placeholder="First Name" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Last Name</label>
                                                    <input type="text" name="last_name" class="form-control" value="{{$user->last_name}}" placeholder="Last Name" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Email</label>
                                                    <input type="text" name="email" class="form-control" value="{{$user->email}}" placeholder="Email" readonly="" required="">
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
                                                                <option value="{{$role->role_id}}" <?php if($role->role_id == $user->role_id){ ?> selected="" <?php } ?>>{{$role->title}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
<!--                                        <div id="show_module" style=" display: none;">
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox " >
                                                        <input id="form-2-7" id="is_user" name="is_user" type="checkbox" checked="">
                                                        <label for="form-2-7">Manage Users</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox ">
                                                        <input id="form-2-6" id="is_trak_status" name="is_trak_status" type="checkbox" checked="">
                                                        <label for="form-2-6">Manage Tracking Status</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox " >
                                                        <input id="form-2-5" id="is_packeges" name="is_packeges" type="checkbox" checked="">
                                                        <label for="form-2-5">Manage Package log</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox ">
                                                        <input id="form-2-4" id="is_manifist" name="is_manifist" type="checkbox" checked="">
                                                        <label for="form-2-4">Manage Manifest</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox " >
                                                        <input id="form-2-3" id="is_invoice" name="is_invoice" type="checkbox" checked="">
                                                        <label for="form-2-3">Manage Invoices</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <div class="checkbox ">
                                                        <input id="form-2-2" id="is_settings" name="is_settings" type="checkbox" checked="">
                                                        <label for="form-2-2">Manage Settings</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Status</label>
                                                    <select class="selectize-group" name="status" id="status" required="" >
                                                        <option value="1" <?php if($user->status == 1){ ?> selected="" <?php } ?>>Active</option>
                                                        <option value="0" <?php if($user->status == 0){ ?> selected="" <?php } ?>>Inactive</option> 
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
        
      