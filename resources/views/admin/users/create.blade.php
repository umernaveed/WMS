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
                                            <div class="col-md-12">
                                                <p class="mrg-top-10 text-dark"> <b>Upload an ID (Driver licence, Passport, National ID)</b></p>
                                            </div>
                                            <div class="col-md-6">
                                                <div>
                                                    <label for="img-upload" class="pointer">
                                                        <img id="uploadPreview" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="118" alt="">
                                                        <span class="btn btn-default display-block no-mrg-btm" style="border-radius: 0px 0px 5px 5px;">Choose file</span>
                                                        <input class="d-none" type="file" name="user_logo" id="img-upload" onchange="PreviewImage();">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <p class="mrg-top-10 text-dark" style=" color: #ff3c7e;" id="upload_logo_error"></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Courier System</label>
                                                    <select name="courier_id" class="form-control" style="margin-bottom: 15px;">
                                                    <option value="" >Select Courier</option>
                                                    @if(count($couriers) > 0)
                                                        @foreach($couriers as $row)
                                                            <option value="{{$row->id}}" >{{$row->name}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">User Type</label>
                                                    <select class="selectize-group" name="user_type" id="demo">
                                                        <option value="personal" >Personal</option>
                                                        <option value="business" >Business</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" name="first_name" id="first_name" value="" placeholder="First Name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name="last_name" id="last_name" value="" placeholder="Last Name" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="hidden" class="form-control" name="old_email" id="old_email" value="" placeholder="Email">
                                                    <input type="email" class="form-control" name="email" id="email" value="" placeholder="Email"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone</label>
                                                    <input type="text" class="form-control" name="phone" id="phone" value="" maxlength="10" placeholder="Phone" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Password</label>
                                                    <input type="password" class="form-control" placeholder="Password" value="{{Request::old('password')}}" name="password">

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Re-type Password</label>
                                                    <input type="password" class="form-control" placeholder="Re-type Password" value="{{Request::old('confirm_password')}}" name="confirm_password">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Mobile</label>
                                                    <input type="text" class="form-control" name="mobile" id="mobile" value="" maxlength="10" placeholder="Mobile" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>TRN #</label>
                                                    <input type="text" class="form-control" name="trn" id="trn" value="" placeholder="TRN #" maxlength="9" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row business_info" style=" display: none;">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Business TRN #</label>
                                                    <input type="text" class="form-control" name="Business_trn" id="Business_trn" value="" placeholder="Business TRN #" maxlength="9" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>TRN Issue Date</label>
                                                    <input type="date" class="form-control" name="tcc_issue_date" id="tcc_issue_date" value="" placeholder="TRN Issue Date" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>TRN Expiry Date</label>
                                                    <input type="date" class="form-control" name="tcc_expiry_date" id="tcc_expiry_date" value="" placeholder="TRN Expiry Date" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Country</label>
                                                    <select name="country" class="selectize-group" >
                                                        <option value="Jamaica">Jamaica</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>City</label>
                                                    <input type="text" class="form-control" name="city" id="city" value="" placeholder="City" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address Line 1</label>
                                                    <textarea class="form-control" placeholder="Address 1" name="address_1" placeholder="Address Line 1"></textarea>

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address Line 2</label>
                                                    <textarea class="form-control" name="address_2" placeholder="Address Line 2"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Save</button>
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
        $('#first_name').val('');
    }
</script>
@endsection
