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
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Account no.</label>
                                            <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">
                                                <select id="e1" style=" width: 400px;" name="mailbox" disabled="">
                                                    <option value="">Select A Account no.</option>
                                                    @if($users)
                                                        @foreach($users as $row)
                                                            <option value="{{$row->mailbox}}" <?php if($row->mailbox == $invoice->mailbox_no){ ?> selected="selected" <?php } ?>>{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <div id="packeges">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Package Tracking No</label>
                                                <div class="col-lg-12" id="tracking_msg" style="padding-left: 0px;margin: 14px 0;">
                                                    <select id="tracking_no"  class="form-control"  name="tracking_no" onchange="addPackage(value , {{$invoice->invoice_id}});">
                                                        <option value="">Select Package</option>
                                                        @if($packeges)
                                                            @foreach($packeges as $row)
                                                                <option value="{{$row->tracking_no}}">{{$row->tracking_no}}</option>
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
                                                            <textarea class="form-control" name="item_description[]" rows="0" placeholder="Item Description">{{$package1->package_description}}</textarea>
                                                        </div>
                                                        <div class="form-group col-sm-2" >
                                                            <label for="exampleInputEmail1">Package Amount</label>
                                                            <input type="text" name="supplier_invoice[]" id="supplier_invoice" value="{{$package1->package_price}}" placeholder="Package Amount" class="form-control">
                                                        </div>
                                                        <div class="form-group col-lg-2">
                                                            <label for="exampleInputEmail1">Custom Fee</label>
                                                            <input type="text" name="custom_fee[]" id="custom_fee" value="{{$package1->custom_fee}}" placeholder="Custom Fee" class="form-control">
                                                        </div>
                                                        <div class="form-group col-lg-2">
                                                        <label for="exampleInputEmail1">Service Fee</label>
                                                            <input type="text" name="custom_fee[]" id="custom_fee" value="{{$package1->service_fee}}" placeholder="Custom Fee" class="form-control">
                                                        </div>
                                                        <div class="form-group col-sm-2">
                                                            <label for="exampleInputEmail1">Weight (In lbs)</label>
                                                            <input type="text" name="weight[]" id="weight" value="{{$package1->package_weight}}" placeholder="Weight" class="form-control">
                                                        </div>   
                                                        <div class="form-group col-sm-1">
                                                            <!--<label for="exampleInputEmail1">Action</label>-->
                                                            <!--<br>-->
                                                            <input type="button" class="btn btn-danger" value="Delete" onClick='delete_record("{{$package1->tracking_no}}","{{$invoice->invoice_id}}")' style="margin-top: 29px;"/>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                <!--                            <div class="form-group">-->
                <!--                                <label for="exampleInputEmail1">Services Fee</label>-->
                <!--                                <input type="text" name="service_fee" id="type" value="{{$invoice->service_fee}}" placeholder="Services Fee" class="form-control">-->
                <!--                            </div>-->
                <!--                            @if($service_type)-->
                <!--                                @foreach($service_type as $type)-->
                <!--                                <div class="row col-lg-12" style="padding-left:0px; ">-->
                <!--                                    <div class="form-group col-md-4">-->
                <!--                                        <input name="name[]" value="{{$type->name}}" placeholder="Title" class="form-control" type="text">-->
                                                        
                <!--                                    </div>-->
                <!--                                    <div class="form-group col-md-4">-->
                <!--                                            <input name="custome_fee[]" value="{{$type->service_fee}}" placeholder="0" class="form-control" type="text">-->
                <!--                                        </div><div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards(2);">
                <!--                                                <i class="fa fa-trash-o"></i> Delete</button>-->
                <!--                                        </div>-->-->
                <!--                                    </div>-->
                <!--                                </div>-->
                <!--                                @endforeach-->
                <!--                            @endif-->
                <!--                            <div id="addedRowsAwards"></div>-->
                <!--                            <div class="form-group">-->
                <!--                                <input type="button" class="btn btn-info" value="+ Add Services" style=" width:100%;" onclick="addAwardsRows(this.form);">-->
                <!--                            </div>-->
                <!--                            <div style=" clear: both"></div>-->
                <!--                            <hr>-->
                                            <div class="form-group">
                                                <input id="checkAll" <?php if($invoice->gct == 1){ ?>  checked="" <?php } ?> name="gct" type="checkbox">GCT
                                            </div>
                                            <input type="submit" class="btn btn-info" value="Update Invoice" />
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
    
    function addPackage(id ,inv_id){
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/packeges/weight/edit/'+id+'/'+inv_id,
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
        var recRow = '<div class="form-group" id="rowCount'+rowCount+'" style="float:left;width: 100%;"><div class="col-md-4" style="padding-left:0px;"><input name="name[]" value="" placeholder="Title" class="form-control" type="text"></div><div class="col-md-4"><input name="custome_fee[]" value="" placeholder="0" class="form-control" type="text"></div><div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards('+rowCount+');"><i class="fa fa-trash-o"></i> Delete</button></div></div>';
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
                url: APP_URL+'/admin/invpackege/delete/edit/'+delID+'/'+invID,
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

    function delete_record(del_id,inv_id){
        delID = del_id;
        invID = inv_id;
        $("#delete_modal").modal("show");
    }
</script>
@endsection        