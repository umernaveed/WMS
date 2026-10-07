@extends('admin.templateExp')
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
<!--                                <div class="btn-group">
                                    <a href="{{URL::to('admin/category/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                                </div>-->
                            </div>
                            <div class="table-overflow">
                                <table id="example" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10%">HAWB.</th>
                                            <th>Supplier</th>
                                            <th>Tracking No.</th>
                                            <th>Package Type</th>
                                            
                                            <th>Weight / Volume</th>
                                            <th>User</th>
                                            <th>Mailbox</th>
                                            <th>Date Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($manifest)
                                            @foreach($manifest as $row)
                                                <tr>
                                                    <td>{{$row->manifest_no}}</td>
                                                    <td>{{$row->courier}}</td>
                                                    <td>{{$row->supplier_tracking_no}}</td>
                                                    <td>{{$row->package_type}}</td>
                                                    <td>{{ method_exists($row, 'measureDisplay') ? $row->measureDisplay() : $row->weight }}</td>
                                                    <td>{{$row->user_name}}</td>
                                                    <td>{{$row->mailbox}}</td>
                                                    <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
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
    
                Are you sure you want to delete this Category?
                
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
        alert();
        if(delID != 0){
            window.location = APP_URL+'/admin/category/delete/'  + delID;
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    }      
</script>
@endsection
 
