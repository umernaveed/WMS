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
                                        <!--        <p class="mrg-top-10 text-dark"> <b>Upload an ID (Driver licence, Passport, National ID)</b></p>-->
                                        <!--    </div>-->
                                        <!--    <div class="col-md-6">-->
                                        <!--        <div>-->
                                        <!--            <label for="img-upload" class="pointer">-->
                                        <!--                @if($user->image != '')-->
                                        <!--                    <img id="uploadPreview" src="{{asset('users/'.$user->image)}}" width="118" alt="">-->
                                        <!--                @else-->
                                        <!--                    <img id="uploadPreview" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="118" alt="">-->
                                        <!--                @endif-->
                                        <!--                <span class="btn btn-default display-block no-mrg-btm" style="border-radius: 0px 0px 5px 5px;">Choose file</span>-->
                                        <!--                <input class="d-none" type="file" name="user_logo" id="img-upload" onchange="PreviewImage();">-->
                                        <!--            </label>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--    <div class="col-md-12">-->
                                        <!--        <p class="mrg-top-10 text-dark" style=" color: #ff3c7e;" id="upload_logo_error"></p>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        <!--<div class="row">-->
                                        <!--    <div class="col-md-6">-->
                                        <!--        <div class="form-group">-->
                                        <!--            <label class="text-normal text-dark">Outlet</label>-->
                                        <!--            <select name="outlet_id" class="form-control" style="margin-bottom: 15px;">-->
                                        <!--            <option value="" >Select Outlet</option>-->
                                        <!--            @if(count($outlets) > 0)-->
                                        <!--                @foreach($outlets as $loc)-->
                                        <!--                    <option value="{{$loc->outlet_id}}" @if($user->outlet_id == $loc->outlet_id) selected ="" @endif>{{$loc->outlet_name}}</option>-->
                                        <!--                @endforeach-->
                                        <!--            @endif-->
                                        <!--        </select>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--    <div class="col-md-6">-->
                                        <!--        <div class="form-group">-->
                                        <!--            <label class="text-normal text-dark">User Type</label>-->
                                        <!--            <select class="selectize-group" name="user_type" id="demo" disabled="">-->
                                        <!--                <option value="personal" @if($user->user_type == 'personal') selected ="" @endif>Personal</option>-->
                                        <!--                <option value="business" @if($user->user_type == 'business') selected ="" @endif>Business</option>-->
                                        <!--            </select>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        <!--<hr>-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" name="first_name" id="first_name" value="{{$user->first_name}}" placeholder="First Name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name="last_name" id="last_name" value="{{$user->last_name}}" placeholder="Last Name" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="hidden" class="form-control" name="old_email" id="old_email" value="{{$user->email}}" placeholder="Email">
                                                    <input type="email" class="form-control" name="email" id="email" value="{{$user->email}}" placeholder="Email"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone</label>
                                                    <input type="text" class="form-control" name="phone" id="phone" value="{{$user->phone}}" maxlength="10" placeholder="Phone" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Mobile</label>
                                                    <input type="text" class="form-control" name="mobile" id="mobile" value="{{$user->mobile}}" maxlength="10" placeholder="Mobile" required>
                                                </div>
                                            </div>
                                            <!--<?php if($user->user_type == 'business'){ ?>-->
                                            <!--    <div class="col-md-6">-->
                                            <!--        <div class="form-group">-->
                                            <!--            <label>Business TRN #</label>-->
                                            <!--            <input type="text" class="form-control" name="Business_trn" id="Business_trn" value="{{$user->Business_trn}}" placeholder="Business TRN #" maxlength="9" required>-->
                                            <!--        </div>-->
                                            <!--    </div>-->
                                            <!--<?php }else{ ?>-->
                                            <!--    <div class="col-md-6">-->
                                            <!--        <div class="form-group">-->
                                            <!--            <label>TRN #</label>-->
                                            <!--            <input type="text" class="form-control" name="trn" id="trn" value="{{$user->trn}}" placeholder="TRN #" maxlength="9" required>-->
                                            <!--        </div>-->
                                            <!--    </div>-->
                                            <!--<?php } ?>-->
                                        </div>
                                        <!--<?php if($user->user_type == 'business'){ ?>-->
                                        <!--    <div class="row">-->
                                        <!--        <div class="col-md-6">-->
                                        <!--            <div class="form-group">-->
                                        <!--                <label>TRN Issue Date</label>-->
                                        <!--                <input type="date" class="form-control" name="tcc_issue_date" id="tcc_issue_date" value="{{$user->tcc_issue_date}}" placeholder="TRN Issue Date" required>-->
                                        <!--            </div>-->
                                        <!--        </div>-->
                                        <!--        <div class="col-md-6">-->
                                        <!--            <div class="form-group">-->
                                        <!--                <label>TRN Expiry Date</label>-->
                                        <!--                <input type="date" class="form-control" name="tcc_expiry_date" id="tcc_expiry_date" value="{{$user->tcc_expiry_date}}" placeholder="TRN Expiry Date" required>-->
                                        <!--            </div>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--<?php } ?>-->
                                        <div class="row">
<!--                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Currency</label>
                                                    <select name="currency" class="selectize-group">
                                                        <option value="JMD" @if($user->currency == 'JMD') selected ="" @endif>JMD</option>
                                                        <option value="USD" @if($user->currency == 'USD') selected ="" @endif>USD</option>
                                                    </select>
                                                </div>
                                            </div>-->
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
                                                    <input type="text" class="form-control" name="city" id="city" value="{{$user->city}}" placeholder="City" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address Line 1</label>
                                                    <textarea class="form-control" placeholder="Address 1" name="address_1" placeholder="Address Line 1">{{$user->address_1}}</textarea>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address Line 2</label>
                                                    <textarea class="form-control" name="address_2" placeholder="Address Line 2">{{$user->address_2}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update</button>
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