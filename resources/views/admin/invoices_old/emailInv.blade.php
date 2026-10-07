@extends('admin.template')
@section('content')
<div class="row">
	<? if(Session::has('success_msg')) { ?>
        <div class="alert alert-block alert-success fade in" style="margin:10px 15px;">
        <button type="button" class="close close-sm" data-dismiss="alert">
            <i class="fa fa-times"></i>
        </button>
        
         <p style="text-align:center;">  {{ Session::get('success_msg') }}  </p>
        
        </div>
    <? } ?>
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <?php echo $table; ?>
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
            <div class="adv-table">
            <div class="clearfix">
                <!--<div class="btn-group">
                    <a href="{{URL::to('admin/invoice/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                </div>
                <!--<div class="btn-group pull-right">
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">Tools <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="#">Print</a></li>
                        <li><a href="#">Save as PDF</a></li>
                        <li><a href="#">Export to Excel</a></li>
                    </ul>
                </div>-->
            </div>
            <div class="space15"></div>
            
                <table  class="display table table-bordered table-striped" id="dynamic-table">
                <thead>
                <tr>
                    
                    <th>Invoice #</th>
                    <th>Name</th>
                    
                    <th>Email Sent</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @if($invoices)
                    @foreach($invoices as $row)
                        <tr>
                            
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
                            <td>
                                @if($row->status == 0)
                                    <a href="{{URL::to('admin/invoice/update/1/'.$row->invoice_id)}}"><button type="button" class="btn btn-danger"><i class="fa fa-times"></i> Unpaid</button></a>
                                @else
                                    <a href="{{URL::to('admin/invoice/update/0/'.$row->invoice_id)}}"><button type="button" class="btn btn-info "><i class="fa fa-check"></i> Paid</button></a>
                                @endif
                            </td>
                            <td align="center" width="25%">
                                <a href="{{URL::to('admin/resend/email/'.$row->invoice_id)}}"><button type="button" class="btn btn-info "><i class="fa fa-envelope"></i> Resend Email</button></a>  
                                <button type="button" class="btn btn-danger" onClick="delete_record('{{$row->invoice_id}}')"><i class="fa fa-trash-o"></i> Delete </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </table>
                
            
            </div>
            </div>
        </section>
    </div>
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="confirmDelete" class="modal fade" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
    
                Are you sure you want to delete this Invoice?
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal" onclick="delete_confirm()"> Confirm</button>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript"> 
	var delID = 0;
	function delete_confirm(){
		if(delID != 0){
			window.location = APP_URL+'/admin/invoice/delete/'  + delID;
		}
	} 
 
	function delete_record(del_id){
		delID = del_id;
		$("#confirmDelete").modal("show");
	} 
        
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
</script>
@endsection 
 