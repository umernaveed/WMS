@extends('admin.template')
@section('content')
<!-- Content Wrapper START -->
<div class="main-content">
    <div class="container-fluid">
        
        <div class="row">
            @include('common.errors')
            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-heading border bottom">
                        <h4 class="card-title">{{$table}}</h4>
                    </div>
                    <div class="card-block">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-8 ml-auto mr-auto">
                                    <form role="form" method="post" action="{{URL::to('admin/manifest/create')}}">
                        {!! csrf_field() !!}
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Cargo Company</label>
                            <input type="hidden" name="courier_id" value="{{$courier_id}}" placeholder="" class="form-control">
                            <input type="hidden" name="packege_ids" value="{{$packeg_id}}" placeholder="" class="form-control">
                            <input type="text" name="cargo_company" value="<?php echo isset($manifest_info->cargo_company) ? $manifest_info->cargo_company : '' ?>" placeholder="Cargo Company" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Custom Office Code</label>
                            <input type="text" name="custom_office_code" value="<?php echo isset($manifest_info->custom_office_code) ? $manifest_info->custom_office_code : '' ?>" placeholder="Custom Office Code" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Carrier Code</label>
                            <input type="text" name="carrier_code" value="<?php echo isset($manifest_info->carrier_code) ? $manifest_info->carrier_code : '' ?>" placeholder="Carrier Code" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Voyage Number</label>
                            <input type="text" name="voyage_number" value="<?php echo isset($manifest_info->voyage_number) ? $manifest_info->voyage_number : '' ?>" placeholder="Voyage Number" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Date of Departure</label>
                            <input type="text" name="date_of_departure" value="<?php echo isset($manifest_info->date_of_departure) ? $manifest_info->date_of_departure : '' ?>" placeholder="Date of Departure" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Shipping Agent Code (TRN)</label>
                            <input type="text" name="shipping_agent_code" value="<?php echo isset($manifest_info->shipping_agent_code) ? $manifest_info->shipping_agent_code : '' ?>" placeholder="Shipping Agent Code (TRN)" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Exporter Name</label>
                            <input type="text" name="exporter_name" value="<?php echo isset($manifest_info->exporter_name) ? $manifest_info->exporter_name : '' ?>" placeholder="Exporter Name" class="form-control" required="">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Loading Port</label>
                            <input type="text" name="loading_port" value="<?php echo isset($manifest_info->loading_port) ? $manifest_info->loading_port : '' ?>" placeholder="Loading Port" class="form-control" required="">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Exporter Address</label>
                            <input type="text" name="exporter_address" value="<?php echo isset($manifest_info->exporter_address) ? $manifest_info->exporter_address : '' ?>" placeholder="Exporter Address" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Master Air Billing #</label>
                            <input type="text" name="master_air_billing_no" value="<?php echo isset($manifest_info->master_air_billing_no) ? $manifest_info->master_air_billing_no : '' ?>" placeholder="Master Air Billing #" class="form-control" required="">
                        </div>
                        <table  class="display table table-bordered table-striped" id="dynamic-table1">
                                <thead>
                                    <tr>
                                        <th width="10%">HAWB.</th>
                                        <th>Carrier</th>
                                        <th>Package Category</th>
                                        <th>Weight / Volume</th>
                                        <th>User</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($packages)
                                    @foreach($packages as $row)
                                        <tr>
                                            <td>{{$row->manifest_no}}</td>
                                            <td>{{$row->courier}}</td>
                                            <td>{{$row->package_type}}</td>
                                            <td>{{ method_exists($row, 'measureDisplay') ? $row->measureDisplay() : $row->weight }}</td>
                                            <td>{{$row->user_name}}</td>
                                            <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                                            
                                        </tr>
                                    @endforeach
                                @endif
                            </table>
                        <input type="submit" class="btn btn-info" value="<?php echo $table; ?>" />
                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Content Wrapper END -->
<script>
    function clearFields(){
        $('#form-1-5').val('');
        $('#title').val('');
        
    }
    $('#role').on('change', function() {
        var id = this.value;
        if(id == '1'){
            $('#show_module').hide();
        }else{
            $('#show_module').show();
        }
    });
</script>
@endsection  
