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
                                {{-- <div class="btn-group">
                                    <a href="{{URL::to('admin/user/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                                </div> --}}
                            </div>
                            <div class="table-overflow">
                                <table id="dt-opt" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th width="15%">Name</th>
                                            <th width="13%">Account no</th>
                                            <th width="20%">Email</th>
                                            <!--<th width="10%">TRN</th>-->
                                            <th>Phone</th>
                                            <!--<th>Address</th>-->
                                            <!--<th>User Type</th>-->
                                            <th width="12%">Created</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($users)
                                            @foreach($users as $row)
                                                <tr>
                                                    <td>{{$row->user_id}}</td>
                                                    <td>{{$row->first_name.' '.$row->last_name}}</td>
                                                    <td>
                                                        <?php
                                                            echo $row->mailbox;
                                                        ?>
                                                    </td>
                                                    <td>{{$row->email}}</td>
                                                    <!--<td>{{$row->tcc}}</td>-->
                                                    <td>{{$row->phone}}</td>
                                                    <!--<td>{{$row->address_1}}</td>-->


                                                    <!--<td>-->
                                                    <!--    {{$row->user_type}}-->
                                                    <!--</td>-->
                                                    <td>
                                                        {{date('d M, Y' , strtotime($row->created_at))}}
                                                    </td>
                                                    <td align="center">
                                                        @if($row->status == '1')
                                                            <a href="{{URL::to('admin/users/1/'.$row->user_id)}}"  class='btn btn-warning' style='padding: 5px 10px;' title='Block'><i class='ti-na pdd-right-0'></i><span>Block</span></a>
                                                        @else
                                                            <a href="{{URL::to('admin/users/0/'.$row->user_id)}}"  class='btn btn-success' style='padding: 5px 10px;' title='Access'><i class='ei-circle pdd-right-0'></i><span>Access</span></a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{URL::to('admin/user/profile/'.$row->user_id)}}" class='all_button btn  btn-sm btn-warning'>User Profile</a>
                                                        <a href="{{URL::to('admin/user/edit/'.$row->user_id)}}"><button type="button" class="btn btn-info"><i class="fa fa-edit"></i> Edit </button></a>
                                                        <button type="button" class="btn btn-danger" onClick="delete_record('{{$row->user_id}}')"><i class="fa fa-trash-o"></i> Delete </button>
                                                    </td>
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

                Are you sure you want to delete this rocord?

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="delete_confirm()"> Confirm</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var delID = 0;
    function delete_confirm(){
        if(delID != 0){
            window.location = APP_URL+'/admin/user/delete/'  + delID;
        }
    }

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }
</script>
@endsection
