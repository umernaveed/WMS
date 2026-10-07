<!-- Include CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Include JS -->

<style>
    .select2-container .select2-selection--single {
    height: 38px; /* Adjust the height to match your input fields */
}

</style>
<div class="form-group">
    <label for="exampleInputEmail1">Package Tracking No</label>
    <div class="col-lg-12" id="tracking_msg" style="padding-left: 0px; margin: 14px 0;">
        <select id="tracking_no" class="form-control select2" name="tracking_no" onchange="addPackage(value);">
            <option value="">Select All Packages</option>
            @if(count($packeges) > 0)
                <option value="all">Select All</option>
                @foreach($packeges as $row)
                    <option value="{{ $row->tracking_no }}">
                        {{ substr($row->supplier_tracking_no, 0, 50) }} - {{ $row->manifest_no }} - {{ $row->created_at }}
                    </option>
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
<!--<div class="form-group">-->
<!--    <label for="exampleInputEmail1">Services Fee</label>-->
<!--    <input type="text" name="service_fee" id="type" value="" placeholder="Services Fee" class="form-control">-->
<!--</div>-->
<div id="addedRowsAwards"></div>
<div class="form-group">
    <input type="button" class="btn btn-info" value="+ Add Services" style=" width:100%;" onclick="addAwardsRows(this.form);">
</div>
<div style=" clear: both"></div>
<hr>
<div class="form-group">
    <input id="checkAll" name="gct" type="checkbox"> State Tax
</div>
<input type="button" class="btn btn-info" value="Create Invoice" onClick='submitDetailsForm()' />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select a package",
            allowClear: true
        });
    });

    function addPackage23(value) {
        if (value !== '') {
            // Find the selected option in the dropdown and hide it
            $('#tracking_no option[value="' + value + '"]').hide();

            // Reset the dropdown selection to the placeholder
            $('#tracking_no').val('').trigger('change');

            // Add any additional logic here to process the selected package
        }
    }
var delID = 0;
    function delete_confirm(){
        if(delID != 0){
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/invpackege/delete/'+delID,
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

    var rowCount = 1;
    function addAwardsRows(frm) {
        rowCount ++;
        var recRow = '<div class="form-group" id="rowCount'+rowCount+'" style="float:left;width: 100%;"><div class="row col-lg-12"><div class="col-md-4" style="padding-left:0px;"><input name="name[]" value="" placeholder="Title" class="form-control" type="text"></div><div class="col-md-4"><input name="custome_fee[]" value="" placeholder="0" class="form-control" type="text"></div><div class="col-md-3" style="padding-right:0px;"><button type="button" class="btn btn-danger" onclick="removeRowAwards('+rowCount+');"><i class="fa fa-trash-o"></i> Delete</button></div></div></div>';
        jQuery('#addedRowsAwards').append(recRow);
    }

    function removeRowAwards(removeNum) {
        jQuery('#rowCount'+removeNum).remove();
    }
</script>
