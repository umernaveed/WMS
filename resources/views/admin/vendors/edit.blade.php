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
                                                <p class="mrg-top-10 text-dark"> <b>Course Image</b></p>
                                            </div>
                                            <div class="col-md-6">
                                                <div>
                                                    <label for="img-upload1" class="pointer">
                                                        @if($vendor->CourseImage != '')
                                                            <img id="uploadPreview" src="{{asset('courses/'.$vendor->CourseImage)}}" width="118" alt="">
                                                        @else
                                                            <img id="uploadPreview" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="118" alt="">
                                                        @endif
                                                        <span class="btn btn-default display-block no-mrg-btm" style="border-radius: 0px 0px 5px 5px;">Choose file</span>
                                                        <input class="d-none" type="file" name="CourseImage" id="img-upload1" onchange="PreviewImage1();">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <p class="mrg-top-10 text-dark" style=" color: #ff3c7e;" id="upload_logo_error1"></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Course Name</label>
                                                    <input type="text" class="form-control" name="CourseName" id="CourseName" value="{{$vendor->CourseName}}" placeholder="Course Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="text" class="form-control" name="PhoneNumber" id="PhoneNumber" value="{{$vendor->PhoneNumber}}" placeholder="Phone Number">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="text" class="form-control" name="email" id="email" value="{{$vendor->email}}" placeholder="Email" readonly="">
                                                </div>
                                            </div>
<!--                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" class="form-control" name="password" id="password" value="{{Request::old('password')}}" placeholder="Password">
                                                </div>
                                            </div>-->
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <input type="text" class="form-control" name="Address" id="Address" value="{{$vendor->Address}}" placeholder="Address">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Zip Code</label>
                                                    <input type="text" class="form-control" name="Zipcode" id="Zipcode" value="{{$vendor->Zipcode}}" placeholder="Zip Code">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contact Name</label>
                                                    <input type="text" class="form-control" name="contactName" id="contactName" value="{{$vendor->contactName}}" placeholder="Contact Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contact Position</label>
                                                    <input type="text" class="form-control" name="contactPosition" id="contactPosition" value="{{$vendor->contactPosition}}" placeholder="Contact Position">
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
</script>
@endsection