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
                        </div>
                        <div class="table-overflow">
                                <table id="dt-opt" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th style="display: none;">Activity</th>
                                            <th>Activity</th>
                                            <th>Added By</th>
                                            <th>Date Created</th>
                                            <!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total = 0;?>
                                        @if(count($activities) > 0)
                                            @foreach($activities as $row)
                                                <tr>
                                                    <td style="display: none;">
                                                        <div class="relative mrg-top-0">
                                                            <span class="">{{$row->created_at}}</span>
                                                        </div>
                                                    </td>
                                                    <td width="60%">
                                                        <div class="relative mrg-top-0">
                                                            <span class="">{{$row->activity}}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="relative mrg-top-0">
                                                            <span class="">{{$row->admin_name.' '.$row->last_name}}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="relative mrg-top-0">
                                                            <span class="">{{$row->created_at}}</span>
                                                        </div>
                                                    </td>
<!--                                                    <td>
                                                        <div class="relative mrg-top-0">
                                                            <a href="javascript:void(0);" class="btn btn-danger" onclick="delete_record({{$row->id}})" style="padding: 5px 10px;">
                                                                <i class="ti-trash pdd-right-0"></i>
                                                                <span></span>
                                                            </a>
                                                        </div>
                                                    </td>-->
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Content Wrapper END -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="confirmDelete" class="modal fade" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Delete</h4>
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                
            </div>
            <div class="modal-body">
    
                Are you sure you want to delete this Pick Up Request?
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="delete_confirm()"> Confirm</button>
            </div>
        </div>
    </div>
</div> 
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
            window.location = APP_URL+'/admin/pickup/request/delete/'  + delID;
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }      
</script>
@endsection 
 