@extends('admin.template')
@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-block" style="padding: 20px 10px;">
                        <div class="inline-block">
                            <h1 class="no-mrg-vertical">{{$wherehouse}}</h1>
                            <p style="font-size: 10px;">TOTAL PACKAGES AT AMERICAN WAREHOOUSE</p>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card ">
                    <div class="card-block" style="padding: 20px;">
                        <div class="inline-block">
                            <h1 class="no-mrg-vertical">{{$in_transit}}</h1>
                            <p style="font-size: 10px;">TOTAL PACKAGES IN TRANSIT</p>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-block" style="padding: 20px;">
                        <div class="inline-block">
                            <h1 class="no-mrg-vertical">{{$outstanding_package}}</h1>
                            <p style="font-size: 10px;">TOTAL PACKAGE READY FOR PICK UP</p>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-block" style="padding: 20px;">
                        <div class="inline-block">
                            <h1 class="no-mrg-vertical">{{$outstanding_balance}}</h1>
                            <p style="font-size: 10px;">TOTAL OUSTANIG BALANCE</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
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
                                        <th>HAWB.</th>
                                            <th>Carrier Tracking No</th>
                                            <th>Carrier</th>
                                            <th>Description</th>
                                            <th>User Name</th>
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
            <div class="col-md-4">
                <div class="col-lg-12" style="padding: 0px;">
                    <div class="card">
                        <div class="card-block">
                            <div class="pdd-top-5 inline-block">
                                <h3 class="no-mrg-vertical">Your Shipping Address</h3>
                                <p class=" mrg-btm-5">Name:{{$user->first_name.' '.$user->last_name}}</p>
                                <p class=" mrg-btm-5">ADDRESS LINE 1:{{$setting->package_shipping_address_1}}</p>
                                <p class=" mrg-btm-5">ADDRESS LINE 2:{{$mailbox}}</p>
                                <p class=" mrg-btm-5">CITY:{{$setting->city}}</p>
                                <p class=" mrg-btm-5">STATE:{{$setting->state}}</p>
                                <p class=" mrg-btm-5">COUNTRY:{{$setting->country}}</p>
                                <p class=" mrg-btm-5">ZIP:{{$setting->zip}}</p>
                                <hr>
                                <p class=" mrg-btm-5">*please to ensure your mailbox number is added to address when shipping either beside last or in line 2 of address to prevent delays.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12" style="padding: 0px;">
                    <div class="card">
                        <div class="card-block">
                            <div class="pdd-top-5 inline-block">
                                <h3 class="no-mrg-vertical">Your Sea Shipping Address</h3>
                                <p class=" mrg-btm-5">Name:{{$user->first_name.' '.$user->last_name}}</p>
                                <p class=" mrg-btm-5">ADDRESS LINE 1:{{$setting->sea_shipping_address_1}}</p>
                                <p class=" mrg-btm-5">ADDRESS LINE 2:{{$setting->sea_shipping_address_2}}</p>
                                <p class=" mrg-btm-5">
                                    <?php
                                        //$mailbox = explode('(PE)' , $mailbox);
                                        echo $mailbox;
                                    ?>
                                </p>
                                <p class=" mrg-btm-5">CITY:{{$setting->sea_city}}</p>
                                <p class=" mrg-btm-5">STATE:{{$setting->sea_state}}</p>
                                <p class=" mrg-btm-5">COUNTRY:{{$setting->sea_country}}</p>
                                <p class=" mrg-btm-5">ZIP:{{$setting->sea_zip}}</p>
                                <hr>
                                <p class=" mrg-btm-5">*please to ensure your mailbox number is added to address when shipping either beside last or in line 2 of address to prevent delays.</p>
                            </div>
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
                "url": '<?php echo url("/user-ready-list/".$mailbox); ?>',
                "type": "GET"
            },

            "columns": [

                { "data": "manifest_no" },
                { "data": "supplier_tracking_no" },
                { "data": "courier" },
                { "data": "item_description" },
                { "data": "first_name" },
                { "data": "name" },
                { "data": "created_at" }

            ],
        } );

        var table = $('#dataTable1').DataTable( {
            dom: 'Blfrtip',
            "processing": true,
            "pageLength": 10,
            "serverSide": true,
            "ajax": {
                "url": '<?php echo url("/recent-user-list") ?>',
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
