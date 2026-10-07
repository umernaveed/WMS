@extends('admin.template')
@section('content')
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
                <h4><?php echo $table; ?></h4>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-block">
                            <div class="clearfix">
                                <div class="dropdown inline-block" style="float: right;">
                                    <a href="{{ url()->previous() }}"><button type="button" class="btn btn-default" style="margin-bottom: 15px;margin-right: 0px;"><i class="fa fa-backward" ></i> Back</button></a>
                                </div>
                            </div>
                            <div class="table-overflow">
                                <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <table id="dt-opt50" class="table table-lg table-hover">
                                        <thead>
                                            <tr>
                                                <th>Shipper</th>
                                                <th>Package #</th>
                                                <th>Category</th>
                                                <th>Weight / Volume</th>
                                                <th>Value (USD)</th>
                                                <th>Invoice #</th>
                                                <th>Amount Outstanding</th>
                                                <th>Date Created</th>
                                                <th>Attachment</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $total = 0;?>
                                            @if(count($packages) > 0)
                                                @foreach($packages as $row)
                                                    <tr>
                                                        
                                                        <td width='15%'>
                                                            <div class="relative mrg-top-0">
                                                                <span class="">{{$row->v_name}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                <a href="{{URL::to('admin/view/package/branch/'.$row->package_no)}}"><span class="">{{$row->package_no}}</span></a>
                                                            </div>
                                                        </td>
                                                        <td width='10%'>
                                                            <div class="relative mrg-top-0" style="word-break: break-all;">
                                                                <span class="">{{$row->title}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                <span class="">
                                                                    @if(stripos((string) ($row->freight_type ?? ''), 'sea') !== false)
                                                                        @php($cuft = (float) (($row->volume_cuft ?? 0) ?: ($row->chargeable_weight ?? 0)))
                                                                        @php($cbm = (float) (($row->volume_cbm ?? 0) ?: ($cuft > 0 ? $cuft / 35.3147 : 0)))
                                                                        {{ number_format($cuft, 2) }} cu.ft / {{ number_format($cbm, 3) }} CBM
                                                                    @else
                                                                        {{ number_format((float) $row->weight, 2) }} lbs
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                <span class="">${{$row->price}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                <a href="javascript:void(0);" onclick="getInvoiceRecepet('{{$row->package_id}}')"><span class="">{{$row->invoice_no}}</span></a>
                                                            </div>
                                                        </td>
                                                        <td align="right">
                                                            <div class="relative mrg-top-0">
                                                                <span class="">{{$row->currency}}{{number_format($row->total,2)}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                <span class="">{{$row->created_at}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="relative mrg-top-0">
                                                                @if($row->is_upload_shipment == '1')
                                                                    <span class=""><a href="{{asset('packages/invoices/'.$row->shipment_invoice)}}" download><div class="ei-printer" style="float: right;font-size: 26px;"></div></a></span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="{{URL::to('admin/package/print/invoice/'.$row->package_id)}}" class="btn btn-warning" style="padding: 5px 10px;">
                                                                <i class="ei-money pdd-right-0"></i>
                                                                <span></span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php $total = $total + $row->total;?>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="6" align="right">No. of Packages</td>
                                                <td align="right">
                                                    <div class="relative mrg-top-0">
                                                        <span class="package_count">{{count($packages)}}</span>
                                                    </div>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" align="right">Total Amount Due</td>
                                                <td align="right">
                                                    <div class="relative mrg-top-0">
                                                        <span class=""><span class="package_total">{{$currency}}{{number_format($total,2)}}</span> <span></span></span>
                                                    </div>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="confirmPaymentRecept" class="modal fade" style="display: none;">
    <div class="modal-dialog show-payment-recept" style="max-width: 800px;">
        
    </div>
</div>
    <!-- Content Wrapper END -->
<script type="text/javascript"> 
    var package_count = 0;
    var package_total = 0;
    var packageArray = [];
    $(document).ready(function(){
        $('.dark').click(function(){
            if($(this).prop("checked") == true){
                $('.removeAttrBtn').removeAttr("disabled");
                package_count = parseInt(package_count) + parseInt(1);
                $('.package_count').html(package_count);
                $('#no_of_packages').val(package_count);
                var p_total = $(this).attr("amountTotal");
                package_total = parseFloat(package_total) + parseFloat(p_total);
                $('.package_total').html(package_total.toFixed(2));
                $('#package_total').val(package_total);
                var packageId = $(this).attr("packageId");
                packageArray.push(packageId);
                $('#package_ids').val(packageArray);
            }else{
                package_count = parseInt(package_count) - parseInt(1);
                $('.package_count').html(package_count);
                $('#no_of_packages').val(package_count);
                var p_total = $(this).attr("amountTotal");
                package_total = parseFloat(package_total) - parseFloat(p_total);
                $('.package_total').html(package_total.toFixed(2));
                $('#package_total').val(package_total);
                var packageId = $(this).attr("packageId");
                packageArray.splice($.inArray(packageId, packageArray),1);
                $('#package_ids').val(packageArray);
                if(package_count == 0){
                    $(".removeAttrBtn").attr("disabled", "disabled");
                }
            }
        });
    });
    var delID = 0;
    function delete_confirm(){
        if(delID != 0){
            window.location = APP_URL+'/member/package/delete/'  + delID;
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }    
    
    function getInvoiceRecepet(package_id){
        var package_id = package_id;
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/get/package/recept/'+package_id,
            data: '',
            processData: false,
            contentType: false,
            success: function (d) {
                $('.show-payment-recept').html(d);
                $("#confirmPaymentRecept").modal("show");
            }
        });
    }
</script>
@endsection 
 
