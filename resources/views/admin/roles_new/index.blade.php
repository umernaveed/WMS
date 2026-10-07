@extends('admin.template')
@section('content')
<?php
    $roles = commonHelper::getRolePermission('role');
?>
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
                                    <div class="btn-group">
                                        <a href="{{URL::to('admin/roles/add')}}"><button id="editable-sample_new" class="btn btn-success">Add New <i class="fa fa-plus"></i></button></a>
                                    </div>
                            </div>
                            <div class="table-overflow">
                                <table id="dt-opt" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Label</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($role) > 0)
                                            @foreach($role as $row)
                                                <tr>
                                                    <td>
                                                        <div class="relative mrg-top-15">
                                                            <span class="">{{$row->title}}</span>
                                                        </div>
                                                    </td>
                                                     <td>
                                                        <div class="relative mrg-top-15">
                                                            <span class="">{{$row->lable}}</span>
                                                        </div>
                                                    </td>
                                                    <td width='25%'>
                                                        <a href="{{URL::to('/admin/roles/edit/'.$row->role_id)}}" class="btn btn-info" style="padding: 5px 10px;">
                                                            <i class="ti-export pdd-right-0"></i>
                                                            <span></span>
                                                        </a>
                                                        <a href="javascript:void(0);" class="btn btn-danger" onclick="delete_record({{$row->role_id}})" style="padding: 5px 10px;">
                                                            <i class="ti-trash pdd-right-0"></i>
                                                            <span></span>
                                                        </a>
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

                Are you sure you want to delete this role?

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
            window.location = APP_URL+'/admin/roles/delete/'  + delID;
        }
    }

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }
</script>
@endsection
