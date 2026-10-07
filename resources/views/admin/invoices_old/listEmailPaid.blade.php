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
                                <form role="form" id="form-validation" action="" method="get" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6" style="padding: 10px 30px;">
                                            <label>Start Date</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="start_date" id="start_date" value="{{$start_date}}" placeholder="Start Date">
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="padding: 10px 30px;">
                                            <label>End Date</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date" value="{{$end_date}}" placeholder="End Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" style="padding: 10px 30px;">
                                            <div class="form-group">
                                                <!--<button id="editable-sample_new" class="btn btn-default pull-right" onclick="clearFiels();" style=" margin-top: px; margin-right: 0px;">Reset <i class="ei-exchange-2"></i></button>-->
                                                <button id="editable-sample_new" class="btn btn-danger pull-right">GENERATE <i class="fa fa-plus"></i></button>
                                                <a href="{{URL::to('admin/invoices/email/1?start_date=&end_date=')}}"><button type="button" class="btn btn-default pull-right">Clear</button></a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="table-overflow">

                                <table  id="dataTable" class="table table-lg table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Invoice #</th>
                                            <th>User Name</th>
                                            <th>Invoice Total</th>
                                            <th>Date Paid</th>
                                            <th>Status</th>
                                            <th>Action</th>
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

        $(document).ready(function(e){
            setTimeout(function() {
                $('.show-total').show();
            }, 2000);
            id =0;
            base_url = "{{url('/')}}";

            delete_id = 0;
            var table = $('#dataTable').DataTable( {
                dom: 'Blfrtip',

                "processing": true,
                //"pageLength": 10,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/invoices-email-list-old/".$status."?start_date=".$start_date.'&end_date='.$end_date) ?>',
                    "type": "GET"
                },
                "columns": [
                    {data: "invoice_id", name: "invoice_id", "visible": false},
                    {data: "invoice_no", name: "invoice_no"},
                    {data: "user_name", name: "user_name"},
                    {data: "totalInv", name: "totalInv", orderable: false, searchable: false},
                    {data: "updated_at", name: "updated_at"},
                    {data: "status", name: "status", orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
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
                    url:'<?php echo url("/delete-invoice-old") ?>',
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



            $('#dataTable tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected') ) {
                    $(this).removeClass('selected');
                }
                else {
                    table.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                }
            } );

        });
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endsection
