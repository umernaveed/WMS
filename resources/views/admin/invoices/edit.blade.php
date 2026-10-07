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
                                <div class="col-md-12 ml-auto mr-auto">
                                    <form role="form" id="invoice" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Courier System</label>
                                                    <select name="courier_id" class="form-control" style="margin-bottom: 15px;" onchange="getPackeges(value);" disabled>
                                                        <option value="" >Select Courier</option>
                                                        @if(count($couriers) > 0)
                                                            @foreach($couriers as $row)
                                                                <option value="{{$row->id}}" <?php if($row->id == $invoice->courier_id){ ?> selected="selected" <?php } ?>>{{$row->name}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div id="packeges">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Package Tracking No</label>
                                                <div class="col-lg-12" id="tracking_msg" style="padding-left: 0px;margin: 14px 0;">
                                                    <select id="tracking_no"  class="form-control"  name="tracking_no" onchange="addPackage(value);">
                                                        <option value="">Select All Packages</option>
                                            
                                                        @if(count($packeges) > 0)
                                                            <option value="all">Select All</option>
                                                            @foreach($packeges as $row)
                                                                <option value="{{$row->tracking_no}}">{{substr($row->item_description,0,50)}}-{{$row->manifest_no}}-{{$row->created_at}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                                @if(count($invoice_packages) > 0)
                                                    @foreach($invoice_packages as $package1)
                                                        <div class="row col-lg-12">
                                                            <div class="form-group col-sm-3" style=" padding-left: 0px;">
                                                                <label for="exampleInputEmail1">Description</label>
                                                                <input type="hidden" name="tracking_no[]" id="tracking_no" value="{{$package1->tracking_no}}" placeholder="Package Amount" class="form-control">
                                                                <!--<textarea class="form-control" name="item_description[]" rows="0" placeholder="Item Description">{{$package1->package_description}}</textarea>-->
                                                                <input type="text" name="item_description[]" id="item_description" value="{{$package1->package_description}}" placeholder="Item Description" class="form-control">
                                                            </div>
                                                            <div class="form-group col-lg-2" >
                                                                <label for="exampleInputEmail1">Unit Price(in lbs)</label>
                                                                <input type="text" name="supplier_invoice[]" id="supplier_invoice" value="{{$package1->package_price}}" placeholder="Price(in lbs)" class="form-control">
                                                            </div>
                                                            <div class="form-group col-lg-2">
                                                                <label for="exampleInputEmail1">Quantity</label>
                                                                <input type="text" name="quantity[]" id="quantity" value="{{$package1->quantity}}" placeholder="0" class="form-control">
                                                            </div>
                                                
                                                            <div class="form-group col-lg-2">
                                                                <label for="exampleInputEmail1">Total Price</label>
                                                                <input type="text" name="total[]" id="total" value="{{$package1->package_price * $package1->quantity }}" placeholder="0" class="form-control">
                                                            </div>
                                                            <div class="form-group col-sm-1">
                                                                <!--<label for="exampleInputEmail1">Action</label>-->
                                                                <!--<br>-->
                                                                <input type="button" class="btn btn-danger" value="Delete" onClick='delete_record("{{$package1->id}}")' style="margin-top: 29px;"/>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <!--<div class="form-group">-->
                                                <!--    <label for="exampleInputEmail1">Services Fee</label>-->
                                                <!--    <input type="text" name="service_fee" id="type" value="{{$invoice->service_fee}}" placeholder="Services Fee" class="form-control">-->
                                                <!--</div>-->
                                                @if($service_type)
                                                    @foreach($service_type as $type)
                                                        <div class="row col-lg-12" style="padding-left:0px; ">
                                                            <div class="form-group col-md-4">
                                                                <input name="name[]" value="{{$type->name}}" placeholder="Title" class="form-control" type="text">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <input name="custome_fee[]" value="{{$type->service_fee}}" placeholder="0" class="form-control" type="text">
                                                            </div>
                                                            <!--<div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards(2);">-->
                                                            <!--    <i class="fa fa-trash-o"></i> Delete</button>-->
                                                            <!--</div>-->
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <div id="addedRowsAwards"></div>
                                                <div class="form-group">
                                                    <input type="button" class="btn btn-info" value="+ Add Services" style=" width:100%;" onclick="addAwardsRows(this.form);">
                                                </div>
                                                <div style=" clear: both"></div>
                                                <hr>
                                                <div class="form-group">
                                                    <input id="checkAll" <?php if($invoice->gct == 1){ ?>  checked="" <?php } ?> name="gct" type="checkbox">State Tax
                                                </div>
                                                <input type="submit" class="btn btn-info" value="Update Invoice" />
                                            </div>
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
<div id="delete_modal" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body alert_message">
                Are you sure to delete this record ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success save_btn" data-dismiss="modal" onclick="delete_confirm()">Yes</button>
                <button type="button" class="btn btn-danger btn_cancel">No</button>
            </div>
        </div>
    </div>
</div>
<!-- Content Wrapper END -->
<script>
    var invoiceID = {!! $invoice_id !!};
    function clearFields(){
        $('#form-1-5').val('');
        $('#title').val('');
        
    }
    $('#role').on('change', function() {
        var id = this.value;
        if(id == '1'){
            $('#show_module').hide();
        }else{
            $('#show_module').show();
        }
    });
    function getPackeges(id){
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/packeges/'+id,
            data: '',
            processData: false,
            contentType: false,
            success: function (d) {
                if(d == 'failed'){
                    $('.alert-success').show();
                }else{
                    $('#packeges').html(d);
                }
            }
        });
    }
    
    function addPackage(id){
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/packeges/weight/edit/'+id+'/'+invoiceID,
            data: '',
            processData: false,
            contentType: false,
            success: function (d) {
                if(d == 'failed'){
                    $('.alert-success').show();
                }else{
                    $('#packeges').html(d);
                }
            }
        });
    }
    function submitDetailsForm() {
        $("#invoice").submit(); 
    }
    
    var rowCount = 1;
    function addAwardsRows(frm) {
        rowCount ++;
        var recRow = '<div class="row col-lg-12" id="rowCount'+rowCount+'"><div class="col-md-4" style="padding-left:0px;"><input name="name[]" value="" placeholder="Title" class="form-control" type="text"></div><div class="col-md-4"><input name="custome_fee[]" value="" placeholder="0" class="form-control" type="text"></div><div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards('+rowCount+');"><i class="fa fa-trash-o"></i> Delete</button></div></div>';
        jQuery('#addedRowsAwards').append(recRow);
    }

    function removeRowAwards(removeNum) {
        jQuery('#rowCount'+removeNum).remove();
    }
    
    var delID = 0;

    var invID = 0;
    function delete_confirm(){
        if(delID != 0){
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/invpackege/delete/edit/'+delID,
                data: '',
                processData: false,
                contentType: false,
                success: function (d) {
                    if(d == 'failed'){
                        $('.alert-success').show();
                    }else{
                        $('#packeges').html(d);
                    }
                }
            });
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#delete_modal").modal("show");
    }
</script>
@endsection        