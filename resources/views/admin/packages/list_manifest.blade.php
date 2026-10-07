@extends('admin.template')
@section('content')
<!-- Content Wrapper START -->
<input type="hidden" id="_token" value="{{csrf_token()}}">
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
                                <form role="form" id="form-validation" action="" method="get" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-4 pull-right">
                                            <div class="form-group">
                                                <label>Select Courier</label>
                                                <div class="mrg-top-10">
                                                    <select class="selectize-group" name="area_id" id="area_id" required="" onchange="getCouriersPackages(value)">
                                                        <option value="0" >All Courier Manifest</option>
                                                        @if(count($couriers) > 0)
                                                            @foreach($couriers as $row)
                                                                <option value="{{$row->id}}" <?php if($coerier_id == $row->id){ ?> selected <?php } ?>>{{$row->name}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
<!--                                <div class="btn-group">
                                    <a href="{{URL::to('admin/category/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                                </div>-->
                            </div>
                            <div class="table-overflow">
                                <table id="dataTable" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th style="display:none;">ID</th>
                                            <th>Date Created</th>
                                            <th>Airway Bill No.</th>
                                            <th>Courier Name.</th>
                                            <th>No Of Packages</th>
                                            <th>Logged By</th>
                                            <th>Print Manifest</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
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
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="delete_modal" class="modal fade" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Delete</h4>
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                
            </div>
            <div class="modal-body">
                Are you sure you want to delete this manifest?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger delete_yes" data-dismiss="modal"> Confirm</button>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript"> 
    var delete_id = 0;
    $(document).ready(function(e){
            id =0;
            base_url = "{{url('/')}}";

            var table = $('#dataTable').DataTable( {
                //dom: 'Blfrtip',
                "processing": true,
                //"pageLength": 30,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/view-menifest-list/".$coerier_id) ?>',
                    "type": "GET"
                },
                "columns": [
                    {data: "created_at", name: "created_at", "visible": false},
                    {data: "created_at", name: "created_at"},
                    {data: "master_air_billing_no", name: "master_air_billing_no"},
                    {data: "name", name: "couriers.name"},
                    {data: "no_of_packages", name: "no_of_packages", orderable: false, searchable: false},
                    {data: "addedby_name", name: "addedby_name"},
                    {data: "action", name: "action", orderable: false, searchable: false}
                ],
            } );
            $("body").on("click",".close_remove",function(e){
                $("#title").val('');
                $("#area_val").val('');
            });
            $(".dataTables_filter input")
                .unbind()
                .bind("input", function(e) {
                    if(this.value.length >= 2 || e.keyCode == 13) {
                        table.search(this.value).draw();
                    }
                    if(this.value == "") {
                        table.search("").draw();
                    }
                    return;
                });

            $("body").on("click",".edit_button",function(e){
                let value = JSON.parse($(this).attr("data-value"));
                $("#name").val(value.name);
                $("#record_id").val(value.role_id);
                 $("#custom_message").modal("show");
            });

            $("body").on("click",".add_new_record",function(e){
                $("#modal_title").text("Add New Record");
                $("#record_id").val(0);
                $("#custom_message").modal("show");
            });


            $("body").on("click",".btn_save",function(e){
                var _token = $('input#_token').val();
                var id = $("#record_id").val();
                var name = $("#name").val();
                $.ajax({
                    method:"POST",
                    url:'<?php echo url("save-role") ?>',
                    data:{id:id,name:name,_token:_token},
                    success:function(res){
                        if(res.status == true){
                            $("#custom_message").modal("hide");
                            table.ajax.reload( null, false );
                        }else{
                            $.notify(res.message, 'error');
                        }
                        $("#name").val('');
                    },
                    error: function(err) {
                        $.notify('Error occurred while saving.', 'error');
                    }
                });
            });

            $("body").on("click",".btn_cancel",function(e){
                $("#custom_message").modal("hide");
                $("#delete_modal").modal("hide");
                $("#resend_modal").modal("hide");

            });
            
            $("body").on("click",".resend_package",function(e){
                delete_id = $(this).attr("id");
                $("#resend_modal").modal("show");
            });
            
            $("body").on("click",".resend_yes",function(e){
                var _token = $('input#_token').val();
                $.ajax({
                    method:"POST",
                    data:{id:delete_id,_token:_token},
                    url:'<?php echo url("/resend-package") ?>',
                    success:function(res){
                        if(res.status){
                            $.notify(res.message, 'success');
                            table.ajax.reload( null, false );

                        }else{
                            $.notify(res.message, 'error');
                        }

                    }
                });
            });

            $("body").on("click",".delete_btn",function(e){
                delete_id = $(this).attr("id");
                $("#delete_modal").modal("show");
            });

            $("body").on("click",".delete_yes",function(e){
                var _token = $('input#_token').val();
                $.ajax({
                    method:"POST",
                    data:{id:delete_id,_token:_token},
                    url:'<?php echo url("/delete-manifest") ?>',
                    success:function(res){
                        if(res.status){
                            $.notify(res.message, 'success');
                            table.ajax.reload( null, false );

                        }else{
                            $.notify(res.message, 'error');
                        }

                    }
                });
            });



//            $('#dataTable tbody').on( 'click', 'tr', function () {
//                if ( $(this).hasClass('selected') ) {
//                    $(this).removeClass('selected');
//                }
//                else {
//                    table.$('tr.selected').removeClass('selected');
//                    $(this).addClass('selected');
//                }
//            } );

        });
    function delete_confirm(){
        if(delID != 0){
            window.location = APP_URL+'/admin/category/delete/'  + delID;
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }
    
    function getCouriersPackages(id){
        window.location.href=APP_URL+'/admin/package/manifest/'+id;
    }
</script>
@endsection 
 