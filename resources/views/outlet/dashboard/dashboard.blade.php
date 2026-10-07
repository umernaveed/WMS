@extends('outlet.template')
@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content text-center p-md">

                        <h2><span class="text-navy">Shipping Address:  </span></h2>
                        <p>
                            <strong>Name: </strong> {{Session::get('outlet_name')}}</p>					
                        <p>
                           <strong>Address Line 1: </strong> {{$setting->package_shipping_address_1}}                  
                        </p>
                        <p><strong>Address Line 2:</strong>
                            <?php 
                                echo Session::get('outlet_trak_id');
                            ?>
                        </p>
                        <p>
                            <strong>City:</strong>{{$setting->city}}             
                        </p>
                        <p>
                            <strong>State:</strong>{{$setting->state}}             
                        </p>
                        <p>
                            <strong>Zip:</strong>{{$setting->zip}}             
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="clearfix">
                                <div class="btn-group">
                                <a href="Javascript:void(0)"><button id="editable-sample_new" class="btn btn-primary">Packages Ready at Counter</button></a>
                            </div>
                        </div>
                        <div class="table-overflow">
                            <table  id="dataTable" class="table table-lg table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>                                        
                                        <th>Tracking #</th>
                                        <th>Supplier Tracking No</th>
                                        <th>Supplier</th>
                                        <th>Description</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Shipment Status</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="clearfix">
                            <div class="btn-group">
                                <a href="Javascript:void(0)"><button id="editable-sample_new" class="btn btn-primary">Recent Activity </button></a>
                            </div>
                        </div>
                        <div class="table-overflow">
                            <table  id="dataTable1" class="table table-lg table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tracking #</th>
                                        <th>Supplier Tracking No</th>
                                        <th>Supplier</th>
                                        <th> Description</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Shipment Status</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--mini statistics end-->
<script type="text/javascript">

    $(document).ready(function(e){
        id =0;
        base_url = "{{url('/')}}";

        delete_id = 0;

        var table = $('#dataTable').DataTable( {
            dom: 'Blfrtip',
            "processing": true,
            "pageLength": 10,
            "serverSide": true,
            "ajax": {
                "url": '<?php echo url("/outlet-ready-list") ?>',
                "type": "GET"
            },
            "columns": [

                {data: "packeg_id", name: "packeg_id", "visible": false},
                {data: "manifest_no", name: "manifest_no"},
                {data: "supplier_tracking_no", name: "supplier_tracking_no"},
                {data: "courier", name: "courier"},
                {data: "item_description", name: "item_description"},
                {data: "first_name", name: "users.first_name"},
                {data: "last_name", name: "users.last_name"},
                {data: "name", name: "package_status.name"},
                {data: "created_at", name: "package_status.created_at"}
                
            ],
        } );
    
        var table = $('#dataTable1').DataTable( {
            dom: 'Blfrtip',
            "processing": true,
            "pageLength": 10,
            "serverSide": true,
            "ajax": {
                "url": '<?php echo url("/outlet-recent-list") ?>',
                "type": "GET"
            },
            "columns": [

                {data: "packeg_id", name: "packeg_id", "visible": false},
                {data: "manifest_no", name: "manifest_no"},
                {data: "supplier_tracking_no", name: "supplier_tracking_no"},
                {data: "courier", name: "courier"},
                {data: "item_description", name: "item_description"},
                {data: "first_name", name: "users.first_name"},
                {data: "last_name", name: "users.last_name"},
                {data: "name", name: "package_status.name"},
                {data: "created_at", name: "package_status.created_at"}
                
            ],
        } );

        $(".dataTables_filter input")
            .unbind()
            .bind("input", function(e) {
            if(this.value.length >= 2 || e.keyCode == 13) {
                table.search(this.value).draw();
            }
            if(this.value == "") {
                table.search("").draw();
            }
            return;
        });

        $('#dataTable tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        } );
    });

</script>
@endsection 