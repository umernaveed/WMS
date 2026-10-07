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
                                <div class="btn-group">
                                    <a href="javascript:void(0)"><button id="editable-sample_new" class="btn btn-primary add_new_record">Add New <i class="fa fa-plus"></i></button></a>
                                </div>
                            </div>
                            <div class="table-overflow">
                                <table  id="dataTable" class="table table-lg table-hover" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Courier Name</th>
                                        <th>Weight</th>
                                        <th>Price</th>
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
                            <label for="form-1-1" class="col-md-4 control-label">Courier System</label>
                            <div class="col-md-8">
                                <select name="courier_id" id="courier_id" class="form-control" style="margin-bottom: 15px;" required>
                                    <!--<option value="" >Select Courier</option>-->
                                    <!--<option value="0" >Unknown</option>-->
                                    @if(count($couriers) > 0)
                                        @foreach($couriers as $row)
                                            <option value="{{$row->id}}" >{{$row->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="form-1-1" class="col-md-4 control-label">Weight</label>
                            <div class="col-md-8">
                                <select name="weight" id="weight" class="form-control" style="margin-bottom: 15px;">
                                    <option value="packages 1-10" >packages 0-10</option>
                                    <option value="packages 11-20" >packages 11-20</option>
                                    <option value="packages 21-50" >packages 21-50</option>
                                    <option value="packages 51-75" >packages 51-75</option>
                                    <option value="packages > 76" >packages > 76</option>
                                </select>
                                <input type="hidden" value="0" name="id" class="form-control" id="record_id" placeholder="id">
                                <!--<input type="text" class="form-control" name="weight" id="weight" placeholder="Weight">-->
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="form-1-1" class="col-md-4 control-label">Price</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="price" id="price" placeholder="Price">
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
            id =0;
            base_url = "{{url('/')}}";

            delete_id = 0;
            
            var table = $('#dataTable').DataTable( {
                dom: 'Blfrtip',

                "processing": true,
                "pageLength": 10,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/weight-list") ?>',
                    "type": "GET"
                },
                "columns": [

                    {data: "id", name: "id", "visible": false},
                    {data: "name", name: "name",orderable: false, searchable: false},
                    {data: "weight", name: "weight"},
                    {data: "price", name: "price"},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
            } );
            $("body").on("click",".close_remove",function(e){
                $("#price").val('');
                $("#weight").val('');
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
                $("#courier_id").val(value.courier_id);
                $("#weight").val(value.weight);
                $("#price").val(value.price);
                $("#record_id").val(value.id);
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
                var courier_id = $("#courier_id").val();
                var weight = $("#weight").val();
                var price = $("#price").val();
                $.ajax({
                    method:"POST",
                    url:'<?php echo url("save-weight") ?>',
                    data:{id:id,weight:weight,price:price,courier_id:courier_id,_token:_token},
                    success:function(res){
                        if(res.status == true){
                            $("#custom_message").modal("hide");
                            table.ajax.reload( null, false );
                        }else{
                            $.notify(res.message, 'error');
                        }
                        $("#price").val('');
                        $("#weight").val('');
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

    </script>
@endsection
