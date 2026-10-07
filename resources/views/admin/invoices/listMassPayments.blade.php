@extends('admin.template')
@section('content')
<style>
    .modal-content{
        width: 100%;
        height: 100%;
        top: 0;
        position: relative;

    }
    .list-info img.thumb-img {
        height: 100px;
        width: 100px;
    }
    
    .hideTr {
      background: #dee2e6;
      position: relative;
      left: 2%;
    }
</style>
<input type="hidden" id="_token" value="{{csrf_token()}}">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <?php if(Session::has('success_msg')) { ?>
        <div class="alert alert-block alert-success fade in" style="margin:10px 15px;">
            <button type="button" class="close close-sm" data-dismiss="alert">
                <i class="fa fa-times"></i>
            </button>
            <p style="text-align:center; margin-bottom:0px; ">  {{ Session::get('success_msg') }}  </p>
        </div>
        <?php } ?>
        <div class="container-fluid">
            <div class="page-title">
                <h4></h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-block">
                            <div class="clearfix">
                                
                            </div>
                            <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <div class="table-overflow">
                                <table id="dataTable" class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>
                                            <!--<input id="checkAll" type="checkbox">-->
                                        </th>
                                        <th colspan="2">Invoice #</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Invoice Paid</th>
                                        <th>Invoice Unpaid</th>
                                        <th>Date Created</th>
                                        <th width='10%'>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                        
                                        @if($invoices)
                                            @foreach($invoices as $row)
                                                <tr >
                                                    <?php 
                                                        $invoiceID = $row->invoice_id;
                                                        $totalInv = commonHelper::getInvoiceCount($row->invoice_id); 
                                                        $totalPaid = commonHelper::getInvoiceCountPaid($row->invoice_id);
                                                        $a = str_replace(',', '', $totalInv);
                                                        $packagesIds = commonHelper::getInvoicePackages($row->invoice_id); 
                                                        if($packagesIds){
                                                            $packArray = [];
                                                            foreach($packagesIds as $row2){
                                                                $packArray[] = $row2->tracking_no;
                                                            }
                                                        }
                                                    ?>
                                                    <td >   
                                                        <input name="check_status[]" value="{{$row->invoice_id}}" data-toggle="toggle" type="checkbox" class="dark update-amount-{{$row->invoice_id}}" packageId="{{implode(',',$packArray)}}" invoiceId="{{$row->invoice_id}}" amountTotal="{{$a}}" amountTotalOrg="{{$a}}" data-type="main">
                                                    </td>
                                                    <td  colspan="2" width="20%">
                                                        <div class="relative mrg-top-0" style="word-break: break-all;">
                                                            <span class="">{{$row->invoice_no}}
                                                                <!--<span class="arrow">-->
                                                                <!--    <i class=" ti-angle-down"></i>-->
                                                                <!--</span>-->
                                                            </span>
                                                        </div>
                                                        
                                                    </td>
                                                    <td width="15%">{{$row->first_name}}</td>
                                                    <td width="15%">{{$row->last_name}}</td>
                                                    <td width="15%">{{$totalPaid}} JMD</td>
                                                    <td width="15%">{{$totalInv}} JMD</td>
                                                    <td width="20%">{{$row->created_at}}</td>
                                                    <td width="10%"><a href="{{url('/')."/admin/invoice/".$row->invoice_no}}"  class='all_button btn  btn-sm btn-warning'>Invoice Detail</a></td>
                                                </tr>
                                                
                                                <?php $packages = commonHelper::getInvoicePackages($row->invoice_id); ?>

                                                <tbody class="hideTr" style=" backgroung:#ccc;">
                                                    @if(count($packages) > 0)
                                                        @foreach($packages as $row1)
                                                            <tr>
                                                                <td>   
                                                                    <?php $packageInvoice = commonHelper::getPackageAmount($row1->tracking_no); ?>
                                                                    <input name="check_status[]" value="{{$row1->tracking_no}}" type="checkbox" class="checkAllInn_{{$invoiceID}} dark" packageId="{{$row1->tracking_no}}" invoiceId="{{$row->invoice_id}}" amountTotalPackage="{{$packageInvoice}}" data-type="sub">
                                                                    <input type="hidden" name="packeg_id[]" value="">
                                                                </td>
                                                                <td>{{$row1->manifest_no}}</td>
                                                                <td>{{$row1->supplier_tracking_no}}</td>
                                                                <td>{{$row1->courier}}</td>
                                                                <td>{{date('d M Y' , strtotime($row1->created_at))}}</td>
                                                                <td>{{$row1->item_description}}</td>
                                                                <td>{{$row1->first_name}}</td>
                                                                <td align="center">{{$packageInvoice}}</td>
                                                                <!--<td>{{$row1->name}}</td>-->
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @endforeach
                                        @endif
                                        <tr>
                                            <td colspan="7" align="right">No. of Packages</td>
                                                <td align="right">
                                                    <div class="relative mrg-top-0">
                                                        <span class="package_count">0</span>
                                                    </div>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" align="right">Total Amount Due</td>
                                                <td align="right">
                                                    <div class="relative mrg-top-0">
                                                        <span class=""><span class="package_total">0.00</span> <span></span></span>
                                                    </div>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                            <tr>
                                                <input type="hidden" name="schdule_method" id="schdule_method" value="get">
                                                <input type="hidden" name="package_total" id="package_total" value="">
                                                <input type="hidden" name="no_of_packages" id="no_of_packages" value="">
                                                <input type="hidden" name="package_ids" id="package_ids" value="">
                                                <input type="hidden" name="invoice_ids" id="invoice_ids" value="">
                                                <td colspan="7" align="right"><a href=""><button type="button" class="btn btn-default" style="width: 115px;">Clear</button></a></td>
                                                <td align="right">
                                                    <button type="submit" class="btn btn-danger removeAttrBtn" style="width: 180px;margin-right: 0px;" disabled="">Delivered And Paid</button>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="custom_message" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title"></h5>
                    <button type="button" class="close close_remove" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message">
                    <form class="form-horizontal mrg-top-40 pdd-right-30" id="artical_form" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label for="form-1-1" class="col-md-4 control-label">Name</label>
                            <div class="col-md-8">
                                <input type="hidden" value="0" name="id" class="form-control" id="record_id" placeholder="id">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Name">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save_btn btn_save" >Save</button>
                    <button type="button" class="btn btn-danger btn_cancel close_remove">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
                    <button type="button" class="btn btn-success save_btn delete_yes" data-dismiss="modal">Yes</button>
                    <button type="button" class="btn btn-danger btn_cancel">No</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
    <script type="text/javascript">
        var package_count = 0;
        var package_total = 0;
        var packageArray = [];
        var InvoiceArray = [];
        $(document).ready(function(){
            $('.dark').click(function(){
                var type = $(this).attr("data-type");
                if($(this).prop("checked") == true){
                    var packageId = $(this).attr("packageId");
                    var nameArr = packageId.split(',');
                    //console.log(nameArr);
                    var invoiceId = $(this).attr("invoiceId");
                    if(type == 'main'){
                        $('.checkAllInn_'+invoiceId).prop('checked', true);
                    }
                    $('.removeAttrBtn').removeAttr("disabled");
                    if(type == 'main'){
                        package_count = parseInt(package_count) + parseInt(1);
                        $('.package_count').html(package_count);
                        $('#no_of_packages').val(package_count);
                        var p_total = $(this).attr("amountTotal");
                        package_total = parseFloat(package_total) + parseFloat(p_total);
                        $('.package_total').html(package_total.toFixed(2));
                        $('#package_total').val(package_total);
                        $(".update-amount-"+invoiceId).attr("amountTotal",0);
                    }else{
                        var p_total = $(this).attr("amountTotalPackage");
                        package_total = parseFloat(package_total) + parseFloat(p_total);
                        $('.package_total').html(package_total.toFixed(2));
                        $('#package_total').val(package_total);
                        var total = $(".update-amount-"+invoiceId).attr("amountTotal");
                        var updateMainTotal = parseFloat(total) - parseFloat(p_total);
                        $(".update-amount-"+invoiceId).attr("amountTotal",updateMainTotal);
                    }
                    
                    for (let i = 0; i < nameArr.length; i++) {
                      packageArray.push(nameArr[i]);
                    }
                    $('#package_ids').val(packageArray);
                    InvoiceArray.push(invoiceId);
                    $('#invoice_ids').val(InvoiceArray);
                }else{
                    var invoiceIdTop = $(this).attr("packageId");
                    var invoiceId = $(this).attr("invoiceId");
                    if(type == 'main'){
                        $('.checkAllInn_'+invoiceIdTop).prop('checked', false);
                    }
                    if(type == 'main'){
                        
                        var p_total = $(this).attr("amountTotalOrg");
                        var total_p = $('#package_total').val();
                        package_count = parseInt(package_count) - parseInt(1);
                        $('.package_count').html(package_count);
                        $('#no_of_packages').val(package_count);
                        //var p_total = $(this).attr("amountTotal");
                        package_total = parseFloat(package_total) - parseFloat(p_total);
                        $('.package_total').html(package_total.toFixed(2));
                        $('#package_total').val(package_total.toFixed(2));
                        $(".update-amount-"+invoiceId).attr("amountTotal",p_total);
                    }else{
                        var p_total = $(this).attr("amountTotalPackage");
                        package_total = parseFloat(package_total) - parseFloat(p_total);
                        $('.package_total').html(package_total.toFixed(2));
                        $('#package_total').val(package_total);
                        var total = $(".update-amount-"+invoiceId).attr("amountTotal");
                        var updateMainTotal = parseFloat(total) + parseFloat(p_total);
                        $(".update-amount-"+invoiceId).attr("amountTotal",updateMainTotal);
                        
                    }
                    
                    var packageId = $(this).attr("packageId");
                    console.log(packageArray);
                    packageArray.splice($.inArray(packageId, packageArray),1);
                    $('#package_ids').val(packageArray);
                    let length = packageArray.length
                    if(length == 0){
                        InvoiceArray.splice($.inArray(invoiceId, InvoiceArray),1);
                        $('#invoice_ids').val(InvoiceArray);
                    }
                    if(length == 0){
                        $(".removeAttrBtn").attr("disabled", "disabled");
                    }
                }
            });
        });
        
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
        
        $(document).ready(function () {
	        //debugger;
        $('.hideTr').slideUp(100);
        $('[data-toggle="toggle"]').click(function () {
            if ($(this).parents().next(".hideTr").is(':visible')) {
                $(this).parents().next('.hideTr').slideUp(100);
                $(".plusminus" + $(this).children().children().attr("id")).text('+');
                $(this).css('background-color', 'white');
                }
            else {
                $(this).parents().next('.hideTr').slideDown(100);
                $(".plusminus" + $(this).children().children().attr("id")).text('- ');
                $(this).css('background-color', '#c1eaff ');
            }
	    });
    });
    </script>
@endsection