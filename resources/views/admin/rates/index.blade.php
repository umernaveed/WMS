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
                                    <a href="{{URL::to('admin/rate/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                                </div>
                            </div>
                            <div class="table-overflow">
                                <table id="dt-opt" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>ICD</th>
                                            <th>SCF</th>
                                            <th>ICDGCT</th>
                                            <th>ENL</th>
                                            <th>Date Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($rates)
                                            @foreach($rates as $row)
                                                <tr>
                                                    <td>{{$row->name}}</td>
                                                    <td>{{$row->icd}}</td>
                                                    <td>{{$row->scf}}</td>
                                                    <td>{{$row->icdgct}}</td>
                                                    <td>{{$row->enl}}</td>
                                                    <td>{{date('M d Y' , strtotime($row->created_at))}}</td>
                                                    <td align="center" width="25%">
                                                            <!--<a href="{{URL::to('admin/clients/view/'.$row->vendor_id)}}"><button type="button" class="btn btn-success"><i class="fa fa-eye"></i> View </button></a>-->
                                                        <a href="{{URL::to('admin/rate/edit/'.$row->custom_rate_id)}}"><button type="button" class="btn btn-info "><i class="fa fa-edit"></i> Edit</button></a>
                            <!--                            <button type="button" class="btn btn-danger" onClick="delete_record('{{$row->cat_id}}')"><i class="fa fa-trash-o"></i> Delete </button>-->
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
    
                Are you sure you want to delete this Review?
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="delete_confirm()"> Confirm</button>
            </div>
        </div>
    </div>
</div> 
<script>
    var APP_URL = {!! json_encode(url('/')) !!}
    var delID = 0;
    function delete_confirm(){

        if(delID != 0){
            window.location = APP_URL + '/admin/reviews/delete/' + delID;
        }
    } 
    function delete_record(del_id){

        delID = del_id;
        $("#confirmDelete").modal("show");
    }  

</script>
@endsection