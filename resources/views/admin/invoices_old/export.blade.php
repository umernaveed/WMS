@extends('admin.template')
@section('content')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="{{asset('admins/assets/css/bootstrap-material-datetimepicker.css')}}" />
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
                                <form role="form" id="form-validation" action="{{URL::to('admin/invoices/export')}}" method="post" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="search" value="1">
                                    <div class="row">
                                        <div class="col-md-6" style="padding: 10px 30px;">
                                            <label>Start Date</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="start_date" id="start_date" value="{{$s[ 'start_date' ]}}" placeholder="Start Date">
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="padding: 10px 30px;">
                                            <label>End Date</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date" value="{{$s[ 'end_date' ]}}" placeholder="End Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" style="padding: 10px 30px;">
                                            <div class="form-group">
                                                <!--<button id="editable-sample_new" class="btn btn-default pull-right" onclick="clearFiels();" style=" margin-top: px; margin-right: 0px;">Reset <i class="ei-exchange-2"></i></button>-->
                                                <button id="editable-sample_new" class="btn btn-danger pull-right">GENERATE <i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                            </div>
                            <div class="table-overflow">
                                <form method="post" name="exportFrm" id="exportFrm" action="{{URL::to('admin/invoices/export_download')}}">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="export" value="1">
                                    <table id="dt-opt" class="table table-lg table-hover">
                                        <thead>
                                            <tr>
                                                <th><input id="checkAll" type="checkbox"></th>
                                                <th>Invoice #</th>
                                                <th>Name</th>
                                                <th>Email Sent</th>
                                                <th>Date Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($invoices) > 0)
                                                @foreach($invoices as $row)
                                                    <tr>
                                                        <td>   
                                                            <!-- input name="check_status[]" type="checkbox" -->
                                                            <input type="checkbox" name="invoice_no[]" value="{{$row->invoice_id}}">
                                                        </td>
                                                        <td><a href="{{URL::to('admin/invoice/'.$row->invoice_no)}}">{{$row->invoice_no}}</a></td>
                                                        <td>{{$row->first_name.' '.$row->last_name}}</td>
                                                        <td>
                                                            @if($row->email_sent == 0)
                                                                <i class="fa fa-times"></i>
                                                            @else
                                                                <i class="fa fa-check"></i>
                                                            @endif
                                                        </td>
                                                        <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    @if(count($invoices) > 0)
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <input name="update" value="Export Invoice" class="btn btn-info" type="submit">
                                            </div>
                                        </div>
                                    @endif
                                </form>
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
@endsection 

<script type="text/javascript"> 
    var delID = 0;
    function delete_confirm(){
        if(delID != 0){
            window.location = APP_URL+'/admin/category/delete/'  + delID;
        }
    } 

    function delete_record(del_id){
        delID = del_id;
        $("#confirmDelete").modal("show");
    } 
    
</script>
