@extends('admin.template')
@section('content')
<div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body profile-information">
                       <div class="col-md-3">
                           <div class="profile-pic text-center">
                                @if($client->client_logo == '')
                                    <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt="" />
                                @else
                                    <img src="{{asset('clients/'.$client->client_logo)}}" alt="" />
                                @endif
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="profile-desk">
                               <h1>{{$client->name}}</h1>
                               <h3 class="prf-border-head">Company   ( <span class="text-muted">{{$client->company}}</span> )</h3>
                               <p>
                                   {{$client->address}}
                               </p><br>
                               <p>
                                   City : {{$cities->name}}
                               </p><br>
                               <p>
                                   State : {{$states->name}}
                               </p><br>
                               <p>
                                   Country : {{$country->name}}
                               </p>
                                
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="profile-statistics">
                           	   <h1>{{$client->phone}}</h1>
                               <p>Phone</p>
                           	   <h1>{{$client->gstin_no}}</h1>
                               <p>GSTN NO</p>
                               <h1>{{$client->pan_no}}</h1>
                               <p>PAN NO</p>
                               <h1>{{$client->postal_code}}</h1>
                               <p>Post Code</p>
                           </div>
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
    
                Are you sure you want to delete this Gate Pass?
                
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
			window.location = APP_URL+'/admin/events/delete/'  + delID;
		}
	} 
 
	function delete_record(del_id){
		delID = del_id;
		$("#confirmDelete").modal("show");
	}      
</script>
@endsection 
 