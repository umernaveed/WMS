@extends('users.template')
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
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>
                                            <!--<input id="checkAll" type="checkbox">-->
                                        </th>
                                        <th>HAWB.</th>
                                        <th>Supplier Tracking No</th>
                                        <th>Supplier</th>
                                        <th>Date Created</th>
                                        <th>Description</th>
                                        <th>Name</th>
                                        <th>Package Amount</th>
                                        <th>Shipment Status</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if($packages)
                                            @foreach($packages as $row)
                                                <tr>
                                                    <td>   
                                                        <?php $packageInvoice = commonHelper::getPackageAmount($row->tracking_no); ?>
                                                        <input name="check_status[]" value="{{$row->tracking_no}}" type="checkbox" class="dark" packageId="{{$row->tracking_no}}" amountTotal="{{$packageInvoice}}">
                                                        <input type="hidden" name="packeg_id[]" value="">
                                                    </td>
                                                    <td>{{$row->manifest_no}}</td>
                                                    <td>{{$row->supplier_tracking_no}}</td>
                                                    <td>{{$row->courier}}</td>
                                                    <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                                                    <td>{{$row->item_description}}</td>
                                                    <td>{{$row->first_name}}</td>
                                                    <td align="right">{{$packageInvoice}}</td>
                                                    <td>{{$row->name}}</td>
                                                </tr>
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
                                                <td colspan="7" align="right"><a href=""><button type="button" class="btn btn-default" style="width: 115px;">Clear</button></a></td>
                                                <td align="right">
                                                    <button type="submit" class="btn btn-danger removeAttrBtn" style="width: 180px;margin-right: 0px;" disabled="">Begin Schedule</button>
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
        
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endsection
