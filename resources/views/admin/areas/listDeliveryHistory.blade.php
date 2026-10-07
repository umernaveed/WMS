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
                                
                            </div>
                            <div class="table-overflow">
                                <table  id="dataTable" class="table table-lg table-hover" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Person Name</th>
                                        <th>User Name</th>
                                        <th>Contact</th>
                                        <th>Total Package</th>
                                        <th>Amount</th>
                                        <th>Address</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
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
    <!-- Modal -->
    <div id="package_modal" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="width: 800px;margin: auto;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">Delivery Schedule Packages</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message Packages_list">
                    
                </div>
                <!--<div class="modal-footer">-->
                <!--    <button type="button" class="btn btn-success save_btn delete_yes" data-dismiss="modal">Yes</button>-->
                <!--    <button type="button" class="btn btn-danger btn_cancel">No</button>-->
                <!--</div>-->
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
    <script type="text/javascript">

        $(document).ready(function(e){
            id =0;
            base_url = "{{url('/')}}";
            var is_cope = 0;
            delete_id = 0;

            var table = $('#dataTable').DataTable( {
                dom: 'Blfrtip',

                "processing": true,
                "pageLength": 10,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/requests-history-list-admin") ?>',
                    "type": "GET"
                },
                "columns": [

                    {data: "id", name: "id", "visible": false},
                    {data: "name", name: "name"},
                    {data: "user_name", name: "user_name",  searchable: false},
                    {data: "phone", name: "phone",  searchable: false},
                    {data: "no_of_packages", name: "no_of_packages"},
                    {data: "package_total", name: "package_total"},
                    {data: 'address', name: 'address'},
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
            } );

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
                $("#cost").val(value.cost);
                $("#record_id").val(value.id);
                 $("#custom_message").modal("show");
            });
            
            $("body").on("click",".add_new_record",function(e){
                $("#modal_title").text("Add New Record");
                $("#record_id").val(0);
                $("#custom_message").modal("show");
            });
            
            $("body").on("click",".package_modal",function(e){
                id = $(this).attr("id");
                $.ajax({
                    type : 'get',
                    url : '{{url('admin/delivery/packages')}}',
                    data: {'id' : id}, // a JSON object to send back
                    success: function(response){ // What to do if we succeed
                    if(response){
                        $('.Packages_list').html(response);
                        $("#package_modal").modal("show");
                    }
                    //console.log(response);
                },
                error: function(jqXHR, textStatus, errorThrown) { // What to do if we fail
                    console.log(JSON.stringify(jqXHR));
                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });
                
            });
            
            $(function(){
                $('.is_cope').click(function() {
                    if($(this).is(':checked'))
                        is_cope = 1;
                    else
                        is_cope = 0;
                });
            });

            
            $("body").on("click",".btn_save",function(e){
                var _token = $('input#_token').val();
                var id = $("#record_id").val();
                var name = $("#name").val();
                var cost = $("#cost").val();
                $.ajax({
                    method:"POST",
                    url:'<?php echo url("save-area") ?>',
                    data:{id:id,name:name,cost:cost,_token:_token},
                    success:function(res){
                        if(res.status == true){
                            $("#custom_message").modal("hide");
                            $.notify(res.message, 'success');
                            table.ajax.reload( null, false );
                        }else{
                            $.notify(res.message, 'error');
                        }
                        $("#name").val('');
                        $("#cost").val('');
                        $("#record_id").val('');
                    },
                    error: function(err) {
                        $.notify('Error occurred while saving.', 'error');
                    }
                });
            });

            $("body").on("click",".btn_cancel",function(e){
                $('.is_cope').prop('checked', false);
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
                    url:'<?php echo url("/delete-area") ?>',
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



            // $('#dataTable tbody').on( 'click', 'tr', function () {
            //     if ( $(this).hasClass('selected') ) {
            //         $(this).removeClass('selected');
            //     }
            //     else {
            //         table.$('tr.selected').removeClass('selected');
            //         $(this).addClass('selected');
            //     }
            // } );

        });
        function getAreaCostNew(id){
            window.location.href=APP_URL+'/admin/delivery/schedule/'+id;
        }

    </script>
@endsection
