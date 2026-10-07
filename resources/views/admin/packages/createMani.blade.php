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
                                        <div class="col-md-4 pull-right">
                                            <div class="form-group">
                                                <label>Select Courier</label>
                                                <div class="mrg-top-10">
                                                    <select class="selectize-group" name="area_id" id="area_id" required="" onchange="getCouriersPackages(value)">
                                                        <option value="" >Select Courier</option>
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
                            </div>
                            <form method="post" action="{{URL::to('admin/create/manifest')}}">
                                {!! csrf_field() !!}
                                <input type="hidden" name="courier_id" value="{{$coerier_id}}">
                                <div class="table-overflow">
                                    <table  id="dataTable" class="table table-lg table-hover" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th><input id="checkAll" type="checkbox"></th>
                                                <th>HAWB.</th>
                                                <th>Carrier Tracking No</th>
                                                <th>Carrier</th>
                                                <th>Item Description</th>
                                                <th>User Name</th>
                                                <!--<th>LName</th>-->
                                                <!--<th>Location</th>-->
                                                <th>Shipment Status</th>
                                                <th>Date Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <div class="col-sm-12">
                                        <span class="table-responsive">
                                            <input name="update" value="Create Manifest" class="btn btn-info" type="submit" @if($package_count == 0) disabled @endif>
                                       </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        $(document).ready(function(e){
            id =0;
            base_url = "{{url('/')}}";

            delete_id = 0;

            var table = $('#dataTable').DataTable( {
                dom: 'Blfrtip',

                "processing": true,
                "pageLength": 10,
                "aLengthMenu": [10,25,50,100,500],
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/packeges-menifest-list/".$coerier_id) ?>',
                    "type": "GET"
                },
                "columns": [

                    {data: "check_status", name: "check_status", orderable: false, searchable: false},
                    {data: "manifest_no", name: "manifest_no"},
                    {data: "supplier_tracking_no", name: "supplier_tracking_no"},
                    {data: "courier", name: "courier"},
                    {data: "item_description", name: "item_description"},
                    {data: "user_name", name: "user_name"},
                    //{data: "last_name", name: "users.last_name"},
                    //{data: "location", name: "users.location"},
                    {data: "status_name", name: "status_name"},
                    {data: "created_at", name: "created_at"}

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
                    url:'<?php echo url("/delete-role") ?>',
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

        function getCouriersPackages(id){
            window.location.href=APP_URL+'/admin/list/manifest/'+id;
        }
    </script>
@endsection
