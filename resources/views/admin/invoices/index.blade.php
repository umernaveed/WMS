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
                <div class="btn-group">
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
            <form method="post" action="{{URL::to('admin/invoice/send')}}">
                {!! csrf_field() !!}
                <table  class="display table table-bordered table-striped" id="dynamic-table">
                <thead>
                <tr>
                    <th><input id="checkAll" type="checkbox"></th>
                    <th>Invoice #</th>
                    <th>Name</th>
<!--                    <th>Status</th>-->
                    <th>Email Sent</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @if($invoices)
                    @foreach($invoices as $row)
                        <tr>
                            <td>   
                                <input name="check_status[]" type="checkbox">
                                <input type="hidden" name="invoice_no[]" value="{{$row->invoice_no}}">
                            </td>
                            <td><a href="{{URL::to('admin/invoice/'.$row->invoice_no)}}">{{$row->invoice_no}}</a></td>
                            <td>{{$row->first_name.' '.$row->last_name}}</td>
<!--                            <td>
                                @if($row->status == 0)
                                    Unpaid
                                @else
                                    Paid
                                @endif
                            </td>-->
                            <td>
                                @if($row->email_sent == 0)
                                    <i class="fa fa-times"></i>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </td>
                            <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                            <td align="center" width="25%">
<!--                                    <a href="{{URL::to('admin/invoice/print/'.$row->invoice_id)}}" target="_blank"><button type="button" class="btn btn-success"><i class="fa fa-print"></i> Print </button></a>-->
                                <a href="{{URL::to('admin/invoice/edit/'.$row->invoice_id)}}"><button type="button" class="btn btn-info "><i class="fa fa-edit"></i> Edit</button></a>
                                <button type="button" class="btn btn-danger" onClick="delete_record('{{$row->invoice_id}}')"><i class="fa fa-trash-o"></i> Delete </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </table>
                <div class="col-sm-12">
                    <span class="table-responsive">
                        <input name="update" value="Send Invoice" class="btn btn-info" type="submit">
                        
                   </span>
                   <span style="margin-left: 33%;border: 1px solid rgba(29,50,140,.9);padding: 10px 10px;background: #334697;color: #fff;"><strong>Invoice Total: {{number_format($totalInvoice,2)}} JMD</strong></span>
                </div>
            </form>
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
 