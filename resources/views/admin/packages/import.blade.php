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
                        @if(!empty($syncStats))
                            <div class="alert alert-info" style="margin-bottom: 20px;">
                                SaaS sync completed in chunks:
                                <strong>{{ $syncStats['synced'] ?? 0 }}</strong> synced,
                                <strong>{{ $syncStats['failed'] ?? 0 }}</strong> failed,
                                <strong>{{ $syncStats['skipped'] ?? 0 }}</strong> skipped.
                            </div>
                        @endif

                        @if($uploaded)
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;">Number Of Packages Upload : {{count($uploaded)}}</label>
                                <label class="col-md-12" style="padding:10px 0px;">Number Of Packages Not Upload : {{count($packages)}}</label>
                            </div>
                            <table  class="display table table-bordered table-striped" id="example">
                                <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Supplier Tracking No</th>
                                    <th>Status</th>
                                    <th>Supplier</th>
                                    <th>Manifest No</th>
                                    <th>Weight</th>
                                    <th>Date Received</th>
                                    <!--<th>Description</th>-->
                                </tr>
                                </thead>
                                <tbody>

                                    @foreach($uploaded as $row)
                                        <tr>
                                            <td>{{$row['user_name']}}</td>
                                            <td>{{$row['supplier_tracking_no']}}</td>
                                            <td>{{$row['name']}}</td>
                                            <td>{{$row['courier']}}</td>
                                            <td>{{$row['manifest_no']}}</td>
                                            <td>{{$row['weight']}}</td>
                                            <td>{{$row['date_received']}}</td>
                                            <!--<td>{{$row['item_description']}}</td>-->
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        </div>
                    <div class="card-block">
                        @if($packages)
                            <div class="form-group">
                                <label class="col-md-12" style="padding:10px 0px;">Number Of Packages Upload : {{count($uploaded)}}</label>
                                <label class="col-md-12" style="padding:10px 0px;">Number Of Packages Not Upload : {{count($packages)}}</label>
                            </div>
                            <table  class="display table table-bordered table-striped" id="example">
                                <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Supplier Tracking No</th>
                                    <th>Status</th>
                                    <th>Supplier</th>
                                    <th>Manifest No</th>
                                    <th>Weight</th>
                                    <th>Date Received</th>
                                    <!--<th>Description</th>-->
                                </tr>
                                </thead>
                                <tbody>

                                    @foreach($packages as $row)
                                        <tr>
                                            <td>{{$row['user_name']}}</td>
                                            <td>{{$row['supplier_tracking_no']}}</td>
                                            <td>{{$row['name']}}</td>
                                            <td>{{$row['courier']}}</td>
                                            <td>{{$row['manifest_no']}}</td>
                                            <td>{{$row['weight']}}</td>
                                            <td>{{$row['date_received']}}</td>
                                            <!--<td>{{$row['item_description']}}</td>-->
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        </div>
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-10 ml-auto mr-auto">
                                    <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                            <label class="col-md-12" style="padding:10px 0px;">Attach File </label>
                                            <hr>
                                            <input type="file" name="input_file" class="default" required />
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <a href="{{URL::to('admin/packege/import/sample')}}" class="btn btn-success" style="float: right;">Download Sample</a>
                                    </form>
                                    <div class="form-group">
                                        <img src="{{asset('csv_format.png')}}" style="width: 100%;margin: 11px auto;">
                                    </div>
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
