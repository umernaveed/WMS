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
                    <a href="{{URL::to('admin/packege/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                </div>
                <div class="btn-group pull-right">
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
            <form method="post" action="{{URL::to('admin/packege/update/status')}}">
                {!! csrf_field() !!}
                <table  class="display table table-bordered table-striped" id="dynamic-table">
                <thead>
                <tr>
                    <th><input id="checkAll" type="checkbox"></th>
                    <th>HAWB.</th>
                    <th>Supplier Tracking No</th>
                    <th>Supplier</th>
                    <th>Date Created</th>
                    <th>Package Type</th>
<!--                    <th>Item Description</th>-->
                    <th>Name</th>
                    <th>Location</th>
                    <th>Shipment Status</th>
                    
                </tr>
                </thead>
                <tbody>
                @if($packages)
                    @foreach($packages as $row)
                        <tr>
                            <td>   
                                <input name="check_status[]" value="{{$row->packeg_id}}" type="checkbox">
                                <input type="hidden" name="packeg_id[]" value="{{$row->packeg_id}}">
                            </td>
                            <td>{{$row->tracking_no}}</td>
                            <td>{{$row->supplier_tracking_no}}</td>
                            <td>{{$row->courier}}</td>
                            <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                            <td>{{$row->package_type}}</td>
<!--                            <td>{{$row->item_description}}</td>-->
                            <td>{{$row->first_name}}</td>
                            <td>{{$row->location}}</td>
                            <td>{{$row->name}}</td>
                            
                            <!--<td align="center" width="25%">
                                    <a href="{{URL::to('admin/invoice/print/'.$row->packeg_id)}}" target="_blank"><button type="button" class="btn btn-success"><i class="fa fa-print"></i> Print </button></a>
                                <a href="{{URL::to('admin/invoice/edit/'.$row->packeg_id)}}"><button type="button" class="btn btn-info "><i class="fa fa-edit"></i> Edit</button></a>
                                <button type="button" class="btn btn-danger" onClick="delete_record('{{$row->packeg_id}}')"><i class="fa fa-trash-o"></i> Delete </button>
                            </td>-->
                        </tr>
                    @endforeach
                @endif
                </table>
                <div class="col-sm-12">
                    <span class="table-responsive">
                        <input name="update" value="Update Status" class="btn btn-info" type="submit">
                        <select name="status" id="status" style="padding: 7px;border-radius: 6px;border: 1px solid #cecece;">
                            @if($packeg_status)
                                @foreach($packeg_status as $status)
                                    @if($status->id != 7)
                                        <option value="{{$status->id}}">{{$status->name}}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                   </span>
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
    
                Are you sure you want to delete this School?
                
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
 