@extends('admin.template')
@section('content')
<!-- Content Wrapper START -->
<style>
    .modal-dialog {
        max-width: 1000px;
    }
    /* Formatting search box */
    
    .search-box input[type="text"] {
        padding: 6px 12px;
        border: 1px solid #e2e2e4;
        font-size: 14px;
        border-radius: 4px;
    }
    .result {
        position: absolute;
        z-index: 999;
        left: 15px;
        max-height: 290px;
        overflow: hidden;
        overflow-y: scroll;
        background: #fff;
        display:none;
    }
    .search-box input[type="text"], .result{
        /* width: 350px; */
        box-sizing: border-box;
    }
    /* Formatting result items */
    .result p{
        margin: 0;
        padding: 7px 10px;
        border: 1px solid #CCCCCC;
        border-top: none;
        cursor: pointer;
        width: 350px;
    }
    .result p:hover{
        background: #f2f2f2;
    }
</style>
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
                                        <div class="row" style="padding: 0 15px;">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Shipment Route</label>
                                                    <select name="freight_type" class="form-control shipment-route-select" style="margin-bottom: 15px;" required>
                                                        <option value="Air Freight" @if($package->freight_type == 'Air Freight') selected @endif>Air Freight</option>
                                                        <option value="Sea Freight" @if($package->freight_type == 'Sea Freight') selected @endif>Sea Freight</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" style="padding: 0 15px;">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Region</label>
                                                    @if($package->is_request == '1')
                                                        <input type="hidden" name="courier_id" id="courier_id" value="{{$package->courier_id}}">
                                                    @else
                                                        <input type="hidden" name="courier_id" id="courier_id" value="">
                                                    @endif
                                                    @php($selectedEditWarehouse = old('warehouse_code', $package->warehouse_code ?? 'USA'))
                                                    <select name="warehouse_code" class="form-control" style="margin-bottom: 15px;">
                                                        <option value="USA" @if(strtoupper((string) $selectedEditWarehouse) !== 'CHINA') selected @endif>USA</option>
                                                        <option value="CHINA" @if(strtoupper((string) $selectedEditWarehouse) === 'CHINA') selected @endif>China</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <input type="hidden" name="tracking_no_edit" id="tracking_no_edit" value="{{$tracking_no}}">
                                        @if(false)
                                            <div class="list-users row">
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group" >
                                                        <label>Account no.</label>
                                                        <input type="text" class="form-control search-box" autocomplete="off" id="member" placeholder="Search Members" value="{{$package->user_name.' Account no: '.$package->mailbox }}" />
                                                        <input type="hidden" name="courier_user_id" id="courier_user_id" value="{{$package->courier_id}}">
                                                        <input type="hidden" name="mailbox_search" id="mailbox" value="{{$package->mailbox.'+'.$package->user_name.'+email'}}">
                                                        <div class="result">
                                                            <p class="member-list">Enter at least <b>3 characters</b></p>
                                                            <script>
                                                                $('.search-box').on("keyup", function(){
                                                                    /* Get input value on change */
                                                                    
                                                                    $('.result').show();
                                                                    var inputVal = $(this).val();
                                                                    var courier_id = $('#courier_user_id').val();
                                                                    var strCount = inputVal.length;
                                                                    if(strCount > 2 && courier_id > 0){
                                                                        $('.member-list').html('Searching Users ...');
                                                                        var resultDropdown = $(this).siblings(".result");
                                                                        if(inputVal.length){
                                                                            $.get(APP_URL+'/get-members/'+courier_id+'/'+inputVal, {}).done(function(data){
                                                                                // Display the returned data in browser
                                                                                resultDropdown.html(data);
                                                                            });
                                                                        } else{
                                                                            resultDropdown.empty();
                                                                        }
                                                                    }else{
                                                                        $('.member-list').html('Enter at least <b>3 characters</b>');
                                                                    }
                                                                });
                                                                
                                                                $(document).on("click", ".result p", function(){
                                                                    var mailbox = $(this).attr('mailbox');
                                                                    $('#mailbox').val(mailbox);
                                                                    $(".search-box").val($(this).text());
                                                                    $(this).parent(".result").empty();
                                                                    //getUserOrders(uoe_no);
                                                                    //$('#search_school').submit();
                                                                });
                                                            </script>
                                                        </div>
                                                        <label id="uoe-message" class="error" style=" display: none;">This field is required.</label>
                                                    </div>
                                                </div>
                                                <!--<div class="col-md-12">-->
                                                <!--    <div class="form-group">-->
                                                <!--        <label>Tracking Number</label>-->
                                                <!--        <input type="text" class="form-control supplier_tracking_no" name="supplier_tracking_no" id="supplier_tracking_no" value="{{Request::old('supplier_tracking_no')}}" placeholder="Carrier tracking number" required="">-->
                                                <!--        <label id="traking_no-message" class="error"></label>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Select Vendor</label>
                                                        <select name="courier" class="form-control" required="" >
                                                            <option value="">Select Vendor</option>
                                                            @if($vendors)
                                                                @foreach($vendors as $row)
                                                                    <option value="{{$row->id.'-'.$row->name}}" @if($package->vendor_id == $row->id) selected @endif>{{$row->name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Shipment Route</label>
                                                    <select name="freight_type" class="form-control shipment-route-select" style="margin-bottom: 15px;" required>
                                                            <option value="Air Freight" @if($package->freight_type == 'Air Freight') selected @endif>Air Freight</option>
                                                            <option value="Sea Freight" @if($package->freight_type == 'Sea Freight') selected @endif>Sea Freight</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Price</label>
                                                        <input type="text" name="price" class="form-control" value="{{$package->price}}" placeholder="0.00" required="">
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Weight</label>
                                                        <input type="text" name="weight" class="form-control" value="{{$package->weight}}" min="0.1" max="1500" placeholder="1-1500" required="">
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Select Package Type</label>
                                                        <select name="package_type" class="form-control" required="" >
                                                            <option value="">Select Package Type</option>
                                                            @if($types)
                                                                @foreach($types as $row)
                                                                    <option value="{{$row->TypeCode.'-'.$row->TypeName.'-'.$row->id}}" @if($package->package_code == $row->TypeCode) selected @endif>{{$row->TypeName}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding: 0 30px;">
                                                    <div class="form-group">
                                                        <label class="text-normal text-dark">Select Package Category</label>
                                                        <select name="category_id" class="form-control" required="" >
                                                            <option value="">Select Package Category</option>
                                                            @if($categories)
                                                                @foreach($categories as $row)
                                                                    <option value="{{$row->id}}" @if($package->category_id == $row->id) selected @endif>{{$row->title}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="list-users">
                                                @if($package->courier_id != 0)
                                                    <input type="hidden" name="mailbox" id="mailbox" value="{{$package->mailbox.'+'.$package->user_name.'+email'}}">
                                                    <div class="row" style="padding: 0 30px;">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1">Account no.</label>
                                                            <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">
                                                                <select id="e1" style=" width: 333px;" name="mailbox1">
                                                                    <option value="0">Select A Account no.</option>
                                                                    @if($users)
                                                                        @foreach($users as $row)
                                                                            <!--<option value="{{$package->mailbox.'+'.$package->user_name.'+'}}" selected="" >{{$package->user_name.' Account no: '.$package->mailbox}}</option>-->
                                                                            <option value="{{$row->mailbox.'+'.$row->first_name.' '.$row->last_name.'+'.$row->email}}" @if($package->mailbox == $row->mailbox) selected="" @endif >{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row" style="padding: 0 15px;">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-normal text-dark">Supplier</label>
                                                            <input type="text" name="courier" class="form-control" value="{{$package->courier}}" placeholder="Supplier" required="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--<div class="row">-->
                                                <!--    <div class="col-md-6">-->
                                                <!--        <div class="form-group">-->
                                                <!--            <label class="text-normal text-dark">supplier tracking number</label>-->
                                                <!--            <input type="text" name="supplier_tracking_no" class="form-control" value="{{$package->supplier_tracking_no}}" placeholder="supplier tracking number" required="">-->
                                                <!--        </div>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                <div class="row package-weight-row" style="padding: 0 15px;">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-normal text-dark">Weight</label>
                                                            <input type="text" name="weight" class="form-control" value="{{$package->weight}}" placeholder="Weight" required="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" style="padding: 0 15px;">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-normal text-dark">Select A Package Type</label>
                                                            <select class="selectize-group" name="package_type" id="package_type" required="" >
                                                                <option value="">Select A Package Type</option>
                                                                @if($types)
                                                                    @foreach($types as $row)
                                                                        <option value="{{$row->code.'-'.$row->type}}" <?php if($package->package_type == $row->type){ ?> selected="" <?php } ?>>{{$row->type}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="row" style="padding: 0 15px;">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Description</label>
                                                    <textarea class="form-control" name="item_description" rows="3" placeholder="Item Description">{{$package->item_description}}</textarea>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary" style="margin-left: 15px;">Submit</button>
                                            <!--<button type="button" class="btn btn-default" onclick="clearFields();">Clear</button>-->
                                        </div>
                                        
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
    function togglePackageWeightByRoute(){
        var isSea = $('.shipment-route-select:visible').val() === 'Sea Freight';
        $('.package-weight-row').toggle(!isSea);
        $('.package-weight-row input[name="weight"]').prop('required', !isSea);
        if (isSea) {
            $('.package-weight-row input[name="weight"]').val('');
        }
    }
    $('.shipment-route-select').on('change', togglePackageWeightByRoute);
    togglePackageWeightByRoute();
    $('#role').on('change', function() {
        var id = this.value;
        if(id == '1'){
            $('#show_module').hide();
        }else{
            $('#show_module').show();
        }
    });
    // function getCourierUser(id){
    //     if(id == 9){
    //         $('#courier_user_id').val(id);
    //         $('.list-users').hide();
    //         $('.search-box-show').show();
    //     }else{
    //         $('.search-box-show').hide();
    //         $('.list-users').show();
    //         $.ajax({
    //             type: 'GET',
    //             url: APP_URL+'/admin/courier/users/'+id,
    //             data: '',
    //             processData: false,
    //             contentType: false,
    //             success: function (d) {
    //                 $('.list-users').html(d);
    //             }
    //         });
    //     }
        
        
    // }
    
    function getCourierUser(id){
        $('.list-users').html('');
        var tracking_no_edit = $('#tracking_no_edit').val();
        //alert(tracking_no_edit);
        if(id == 9){
            $('#courier_user_id').val(id);
            //$('.list-users').hide();
            //$('.search-box-show').show();
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/courier/users/'+id+'/'+tracking_no_edit,
                data: '',
                processData: false,
                contentType: false,
                success: function (d) {
                    $('.list-users').html(d);
                }
            });
        }else{
            //$('.search-box-show').hide();
            //$('.list-users').show();
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/courier/users/'+id+'/'+tracking_no_edit,
                data: '',
                processData: false,
                contentType: false,
                success: function (d) {
                    $('.list-users').html(d);
                }
            });
        }
        
        
    }
    
    $("#supplier_tracking_no").blur(function(){
        var supplier_tracking_no = $('#supplier_tracking_no').val();
        
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/package/check/tracking/number/'+supplier_tracking_no,
            data: '',
            processData: false,
            contentType: false,
            // async:false,
            success: function (msg) {
                if(msg.status == 'failed'){
                    $('#supplier_tracking_no').val('');
                    $("#supplier_tracking_no").css({"border-color": "#ff3c7e", "border-width": "1px", "border-style": "solid","background-color": "#ffeff4"});
                    $("#traking_no-message").html('This tracking number ('+supplier_tracking_no+') has already been taken.Try another one.');
                }else{
                    $("#supplier_tracking_no").css({"border-color": "#37c936", "border-width": "1px", "border-style": "solid","background-color": "#ebfaeb"});
                    $("#traking_no-message").html('');
                }
            }
        });
    });
</script>
@endsection      
        
      
