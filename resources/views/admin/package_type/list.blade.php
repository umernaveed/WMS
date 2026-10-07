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
                                <div class="btn-group">
                                    <a href="{{URL::to('admin/package/type/create')}}"><button class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>

                                </div>
                            </div>
                            <div class="table-overflow">
                                <table id="dt-opt" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Code</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($packagetype)
                                            @foreach($packagetype as $row)
                                                <tr>
                                                    <td width="5%">
                                                        <div class="relative mrg-top-15">
                                                            <span class="">{{$row->id}}</span>
                                                        </div>
                                                    </td>
                                                    
                                                    <td width="15%">
                                                        <div class="relative mrg-top-15" style="word-break: break-all;">
                                                            <span class="">{{$row->type}}</span>
                                                        </div>
                                                    </td>
                                                    <td width="15%">
                                                        <div class="relative mrg-top-15" style="word-break: break-all;">
                                                            <span class="">{{$row->code}}</span>
                                                        </div>
                                                    </td>
                                                   

                                                     <td width="10%">
                                                        <a href="{{URL::to('admin/package/type/edit/'.$row->id)}}" class="btn btn-info">
                                                            <i class="ti-pencil pdd-right-5"></i>
                                                            <span>Edit</span>
                                                        </a>
                                                        <a href="javascript:void(0);" class="btn btn-danger" onclick="delete_record({{$row->id}})">
                                                            <i class="ti-trash pdd-right-5"></i>
                                                            <span>Delete</span>
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

                Are you sure you want to delete this Package Type?

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
            window.location = APP_URL+'/admin/package/type/delete/'  + delID;
        }
    }

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }
</script>
@endsection
