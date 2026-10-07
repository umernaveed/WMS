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
                                                <p class="mrg-top-10 text-dark"> <b>Website Logo</b></p>
                                            </div>
                                            <div class="col-md-6">
                                                <div>
                                                    <label for="img-upload" class="pointer">
                                                        @if(file_exists( public_path().'/settings/'.$setting->site_logo ))
                                                            <img id="uploadPreview" src=" {{url('').'/public/settings/'.$setting->site_logo}}" width="118" alt="">
                                                        @else
                                                            <img id="uploadPreview" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" width="118" alt="">
                                                        @endif
                                                        <span class="btn btn-default display-block no-mrg-btm" style="border-radius: 0px 0px 5px 5px;">Choose file</span>
                                                        <input class="d-none" type="file" name="site_logo" id="img-upload" onchange="PreviewImage();">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <p class="mrg-top-10 text-dark" style=" color: #ff3c7e;" id="upload_logo_error"></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Company Name</label>
                                                    <input type="text" class="form-control" value="{{$setting->site_name}}" placeholder="Website Name" name="site_name" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Website Title</label>
                                                    <input type="text" class="form-control" value="{{$setting->site_title}}" placeholder="Website Title" name="site_title" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Website Email</label>
                                                    <input type="text" class="form-control" value="{{$setting->site_email}}" placeholder="Website Email" name="site_email" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Email From</label>
                                                    <input type="text" class="form-control" value="{{$setting->from_email}}" placeholder="Email From" name="from_email" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Phone Number</label>
                                                    <input type="text" class="form-control" value="{{$setting->phone}}" placeholder="Phone Number" name="phone" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Us Rate</label>
                                                    <input type="text" class="form-control" value="{{$setting->us_rate}}" placeholder="Us Rate" name="us_rate" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group11">
                                                    <label class="text-normal text-dark"><b>Package Shipping Address</b></label>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Package Shipping Address 1</label>
                                                    <input type="text" class="form-control" value="{{$setting->package_shipping_address_1}}" placeholder="Package Shipping Address 1" name="package_shipping_address_1" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Package Shipping Address 2</label>
                                                    <input type="text" class="form-control" value="{{$setting->package_shipping_address_2}}" placeholder="Package Shipping Address 2" name="package_shipping_address_2" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">City</label>
                                                    <input type="text" class="form-control" value="{{$setting->city}}" placeholder="City" name="city" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">State</label>
                                                    <input type="text" class="form-control" value="{{$setting->state}}" placeholder="State" name="state" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Zip</label>
                                                    <input type="text" class="form-control" value="{{$setting->zip}}" placeholder="Zip" name="zip" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Local Address</label>
                                                    <input type="text" class="form-control" value="{{$setting->local_address}}" placeholder="Local Address" name="local_address" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Country</label>
                                                    <input type="text" class="form-control" value="{{$setting->country}}" placeholder="Countrey" name="country" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group11">
                                                    <label class="text-normal text-dark"><b>Sea Shipping Address</b></label>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Sea Shipping Address 1</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_shipping_address_1}}" placeholder="Sea Shipping Address 1" name="sea_shipping_address_1" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Sea Shipping Address 2</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_shipping_address_2}}" placeholder="Sea Shipping Address 2" name="sea_shipping_address_2" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">City</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_city}}" placeholder="City" name="sea_city" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">State</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_state}}" placeholder="State" name="sea_state" required="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Zip</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_zip}}" placeholder="Zip" name="sea_zip" required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Local Address</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_local_address}}" placeholder="Local Address" name="sea_local_address" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Country</label>
                                                    <input type="text" class="form-control" value="{{$setting->sea_country}}" placeholder="Countrey" name="sea_country" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Footer Text</label>
                                                    <textarea class="form-control" id="summernote-usage" rows="3" name="site_footer_text" required>{{$setting->site_footer_text}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update</button>
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