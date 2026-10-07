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
@if($packagesInv)
    @foreach($packagesInv as $package)
        <div class="row col-lg-12">
            <div class="form-group col-lg-3" style=" padding-left: 0px;">
                <label for="exampleInputEmail1">Description</label>
                <input type="hidden" name="tracking_no[]" id="tracking_no" value="{{$package->tracking_no}}" placeholder="Package Amount" class="form-control">
                {{-- <textarea class="form-control" name="item_description[]" rows="1" placeholder="Item Description">{{$package->description}}</textarea> --}}
                <input type="text" name="item_description[]" id="item_description" value="{{$package->description}}" placeholder="Item Description" class="form-control">
            </div>
            <div class="form-group col-lg-2" >
                <label for="exampleInputEmail1">Unit Price(in lbs)</label>
                <input type="text" name="supplier_invoice[]" id="supplier_invoice" value="{{$package->price}}" placeholder="Price(in lbs)" class="form-control">
            </div>
            <!--<div class="form-group col-lg-2">-->
            <!--    <label for="exampleInputEmail1">Custom Fee</label>-->
            <!--    <input type="text" name="custom_fee[]" id="custom_fee" value="0" placeholder="Custom Fee" class="form-control">-->
            <!--</div>-->
            <!--<div class="form-group col-lg-2">-->
            <!--    <label for="exampleInputEmail1">Service Fee</label>-->
            <!--    <input type="text" name="service_fee[]" id="service_fee" value="0" placeholder="Service Fee" class="form-control">-->
            <!--</div>-->
            <div class="form-group col-lg-2">
                <label for="exampleInputEmail1">Quantity</label>
                <input type="text" name="quantity[]" id="quantity" value="{{$package->qty}}" placeholder="0" class="form-control">
            </div>

            <div class="form-group col-lg-2">
                <label for="exampleInputEmail1">Total Price</label>
                <input type="text" name="total[]" id="total" value="{{$package->price * $package->qty }}" placeholder="0" class="form-control">
            </div>

            <div class="form-group col-lg-1">
                <!--<label for="exampleInputEmail1">Action</label>-->
                <input type="button" class="btn btn-danger" value="Delete" onClick='delete_record("{{$package->id}}")'  style="margin-top: 29px;" />
            </div>

            <div style=" clear: both;"></div>
            <hr>
        </div>
    @endforeach
@endif
@if($service_type)
    @foreach($service_type as $type)
        <div class="form-group">
            <div class="row col-lg-12" style="padding-left:0px; ">
                <div class="col-md-4">
                    <input name="name[]" value="{{$type->name}}" placeholder="Title" class="form-control" type="text">
                </div>
                <div class="col-md-4">
                    <input name="custome_fee[]" value="{{$type->service_fee}}" placeholder="0" class="form-control" type="text">
                </div>
                <div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards(2);">
                    <i class="fa fa-trash-o"></i> Delete</button>
                </div>
            </div>
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
<script>
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
    
    var rowCount = 1;
    function addAwardsRows(frm) {
        rowCount ++;
        var recRow = '<div class="form-group" id="rowCount'+rowCount+'" style="float:left;width: 100%;"><div class="col-md-4" style="padding-left:0px;"><input name="name[]" value="" placeholder="Title" class="form-control" type="text"></div><div class="col-md-4"><input name="custome_fee[]" value="" placeholder="0" class="form-control" type="text"></div><div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards('+rowCount+');"><i class="fa fa-trash-o"></i> Delete</button></div></div>';
        jQuery('#addedRowsAwards').append(recRow);
    }

    function removeRowAwards(removeNum) {
        jQuery('#rowCount'+removeNum).remove();
    }
</script>
