@extends('admin.template')
@section('content')
<!-- Content Wrapper START -->
<div class="main-content">
    <div class="container-fluid">

        <div class="row">
            @include('common.errors')
            <div class="col-md-12">
                <div class="card">

                    <div class="card-heading border bottom">
                        <h4 class="card-title">{{$table}}</h4>
                    </div>
                    <div class="card-block">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-12 ml-auto mr-auto">
                                    <form role="form" id="invoice" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Courier System</label>
                                                    <select name="courier_id" class="form-control" style="margin-bottom: 15px;" onchange="getPackeges(value);" required>
                                                        <option value="" >Select Courier</option>
                                                        @if(count($couriers) > 0)
                                                            @foreach($couriers as $row)
                                                                <option value="{{$row->id}}" >{{$row->name}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <!--<div class="form-group list-users">-->
                                        <!--    <label for="exampleInputEmail1">Account no.</label>-->
                                        <!--    <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">-->
                                        <!--        <select id="e1" style=" width: 333px;" name="mailbox" onchange="getPackeges(value);">-->
                                        <!--            <option value="">Select A Account no.</option>-->
                                        <!--            {{-- @if($users)-->
                                        <!--                @foreach($users as $row)-->
                                        <!--                    <option value="{{$row->mailbox}}">{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</option>-->
                                        <!--                @endforeach-->
                                        <!--            @endif --}}-->
                                        <!--        </select>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        <!--<hr>-->
                                        <div id="packeges"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
                <button type="button" class="btn btn-success save_btn" data-dismiss="modal" onclick="delete_confirm()">Yes</button>
                <button type="button" class="btn btn-danger btn_cancel">No</button>
            </div>
        </div>
    </div>
</div>
<!-- Content Wrapper END -->
<script>
localStorage.clear();
    var courierID = 0;
    function clearFields(){
        $('#form-1-5').val('');
        $('#title').val('');

    }
    $('#role').on('change', function() {
        var id = this.value;
        if(id == '1'){
            $('#show_module').hide();
        }else{
            $('#show_module').show();
        }
    });

    function getCourierUser(id){
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/courier/users/'+id,
            data: '',
            processData: false,
            contentType: false,
            success: function (d) {
                $('.list-users').html(d);
            }
        });
    }
    function getPackeges(id){
        courierID = id;
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/user/packeges/'+id,
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

    function addPackage(id){
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/packege/weight/'+id+'/'+courierID,
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
        
        addPackage23(value);
    }
    function submitDetailsForm() {
        $("#invoice").submit();
    }
</script>
@endsection
