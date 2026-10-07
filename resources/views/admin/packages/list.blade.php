@extends('admin.template')
@section('content')
<style>
    :root {
        --pkg-ink: #172033;
        --pkg-muted: #667085;
        --pkg-line: #e6ebf2;
        --pkg-soft: #f6f8fb;
        --pkg-panel: #ffffff;
        --pkg-primary: #3b82f6;
        --pkg-primary-dark: #2563eb;
        --pkg-indigo: #4f46e5;
        --pkg-green: #0f9f6e;
        --pkg-amber: #b7791f;
        --pkg-red: #dc2626;
        --pkg-radius: 8px;
    }

    .modal-content{
        width: 100%;
        height: 100%;
        top: 0;
        position: relative;

    }
    .list-info img.thumb-img {
        height: 100px;
        width: 100px;
    }

    .package-page {
        background: #f5f7fb;
        min-height: calc(100vh - 80px);
        padding-top: 22px;
    }

    .package-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
    }

    .package-title h3 {
        color: var(--pkg-ink);
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0 0 6px;
    }

    .package-title p {
        color: var(--pkg-muted);
        font-size: 14px;
        line-height: 1.5;
        margin: 0;
    }

    .package-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(150px, 1fr));
        gap: 10px;
        min-width: 320px;
    }

    .package-stat {
        background: var(--pkg-panel);
        border: 1px solid var(--pkg-line);
        border-radius: var(--pkg-radius);
        padding: 14px 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .package-stat span {
        display: block;
        color: var(--pkg-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .package-stat strong {
        color: var(--pkg-ink);
        font-size: 22px;
        line-height: 1;
    }

    .package-panel {
        background: var(--pkg-panel);
        border: 1px solid var(--pkg-line);
        border-radius: 10px;
        box-shadow: 0 12px 36px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .package-toolbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--pkg-line);
        background: linear-gradient(180deg, #fff 0%, #fbfcfe 100%);
    }

    .package-filter {
        width: min(420px, 100%);
    }

    .package-filter label {
        color: var(--pkg-muted);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .package-filter .selectize-control.single .selectize-input,
    .package-filter select {
        border: 1px solid #d8e0eb;
        border-radius: var(--pkg-radius);
        box-shadow: none;
        min-height: 44px;
        padding: 11px 14px;
        color: var(--pkg-ink);
    }

    .package-advanced-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--pkg-line);
        background: #fff;
    }

    .package-advanced-filters .filter-field label {
        display: block;
        color: var(--pkg-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .package-advanced-filters input,
    .package-advanced-filters select {
        width: 100%;
        border: 1px solid #d8e0eb;
        border-radius: 7px;
        min-height: 38px;
        padding: 7px 10px;
        color: var(--pkg-ink);
        box-shadow: none;
    }

    .package-filter-actions {
        display: flex;
        align-items: end;
        gap: 8px;
    }

    .package-filter-actions button {
        min-height: 38px;
        border-radius: 7px;
        border: 1px solid #d8e0eb;
        padding: 7px 13px;
        font-weight: 800;
        background: #fff;
        color: var(--pkg-ink);
    }

    .package-filter-actions .apply-filters {
        border-color: var(--pkg-primary);
        background: var(--pkg-primary);
        color: #fff;
    }

    .package-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        border: 0;
        border-radius: var(--pkg-radius);
        padding: 0 18px;
        background: var(--pkg-primary);
        color: #fff;
        font-weight: 800;
        box-shadow: 0 10px 20px rgba(37, 99, 235, .22);
        transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
    }

    .package-add-btn:hover,
    .package-add-btn:focus {
        background: var(--pkg-primary-dark);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(37, 99, 235, .26);
        text-decoration: none;
    }

    .package-table-wrap {
        padding: 0 20px 18px;
        overflow-x: auto;
    }

    .package-table-wrap .dataTables_wrapper {
        padding-top: 16px;
    }

    .package-table-wrap .dataTables_length,
    .package-table-wrap .dataTables_filter {
        color: var(--pkg-muted);
        font-weight: 700;
        margin-bottom: 12px;
    }

    .package-table-wrap .dataTables_length select,
    .package-table-wrap .dataTables_filter input {
        border: 1px solid #d8e0eb;
        border-radius: var(--pkg-radius);
        min-height: 40px;
        box-shadow: none;
        color: var(--pkg-ink);
    }

    .package-table-wrap .dataTables_filter input {
        width: 260px;
        margin-left: 8px;
        padding: 8px 12px;
    }

    #dataTable {
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1280px;
        color: var(--pkg-ink);
    }

    #dataTable thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        border-top: 1px solid var(--pkg-line);
        border-bottom: 1px solid var(--pkg-line);
        color: #344054;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 14px 14px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #dataTable tbody td {
        border-top: 0;
        border-bottom: 1px solid var(--pkg-line);
        padding: 16px 14px;
        vertical-align: top;
        font-size: 14px;
        line-height: 1.45;
        background: #fff;
    }

    #dataTable tbody tr:hover td {
        background: #f8fbff;
    }

    #dataTable .pkg-code,
    #dataTable .pkg-weight {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 3px 10px;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 800;
        white-space: nowrap;
    }

    #dataTable .pkg-weight {
        background: #ecfdf3;
        color: #067647;
    }

    #dataTable .pkg-tracking {
        display: block;
        max-width: 190px;
        overflow-wrap: anywhere;
        font-family: Consolas, "Courier New", monospace;
        color: #111827;
        font-size: 13px;
    }

    #dataTable .pkg-description {
        display: block;
        max-width: 230px;
        color: #344054;
    }

    #dataTable .pkg-status {
        display: inline-block;
        max-width: 180px;
        border-left: 3px solid var(--pkg-primary);
        padding-left: 10px;
        color: #1f2937;
        font-weight: 700;
    }

    #dataTable .pkg-carrier,
    #dataTable .pkg-user,
    #dataTable .pkg-courier {
        display: block;
        max-width: 160px;
        overflow-wrap: anywhere;
    }

    .pkg-actions-dropdown {
        display: inline-block;
        position: relative;
    }

    .pkg-actions-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        border: 1px solid #d7e1ef;
        border-radius: 7px;
        padding: 0 12px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
        transition: background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .pkg-actions-toggle:hover,
    .pkg-actions-toggle:focus {
        background: #f8fafc;
        border-color: #b9c7d8;
        color: #0f172a;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .1);
    }

    .pkg-actions-menu {
        min-width: 168px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        padding: 6px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, .16);
    }

    .pkg-action-item {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        border: 0;
        border-radius: 6px;
        padding: 9px 10px;
        background: transparent;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
        text-align: left;
        cursor: pointer;
    }

    .pkg-action-item:hover,
    .pkg-action-item:focus {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }

    .pkg-action-item i {
        width: 15px;
        text-align: center;
    }

    .pkg-action-item-label {
        color: var(--pkg-amber);
    }

    .pkg-action-item-sync {
        color: var(--pkg-green);
    }

    .pkg-action-item-edit {
        color: var(--pkg-primary-dark);
    }

    .pkg-action-item-delete {
        color: var(--pkg-red);
    }

    .package-table-wrap .dataTables_info {
        color: var(--pkg-muted);
        padding-top: 16px;
    }

    .package-table-wrap .pagination .page-link,
    .package-table-wrap .dataTables_paginate .paginate_button {
        border-radius: 7px !important;
    }

    @media (max-width: 991px) {
        .package-hero,
        .package-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .package-stats {
            min-width: 0;
        }

        .package-advanced-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .package-table-wrap .dataTables_filter input {
            width: 100%;
            margin: 8px 0 0;
        }
    }

    @media (max-width: 640px) {
        .package-advanced-filters {
            grid-template-columns: 1fr;
        }
    }
</style>
<input type="hidden" id="_token" value="{{csrf_token()}}">
    <!-- Content Wrapper START -->
    <div class="main-content package-page">
        <?php if(Session::has('success_msg')) { ?>
        <div class="alert alert-block alert-success fade in" style="margin:10px 15px;">
            <button type="button" class="close close-sm" data-dismiss="alert">
                <i class="fa fa-times"></i>
            </button>
            <p style="text-align:center; margin-bottom:0px; ">  {{ Session::get('success_msg') }}  </p>
        </div>
        <?php } ?>
        <div class="container-fluid">
            <div class="package-hero">
                <div class="package-title">
                    <h3>Package Management</h3>
                    <p>Manage warehouse packages currently marked as arrived at Pompano Beach Warehouse.</p>
                </div>
                <div class="package-stats">
                    <div class="package-stat">
                        <span>Packages</span>
                        <strong>{{number_format($package_count)}}</strong>
                    </div>
                    <div class="package-stat">
                        <span>Total Air Weight</span>
                        <strong>{{number_format($package_weight_lb, 2)}} lb</strong>
                        <p class="mb-0">{{number_format($package_weight_kg, 2)}} kg</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="package-panel">
                        <div>
                            <div class="package-toolbar">
                                <form role="form" id="form-validation" action="" method="get" enctype="multipart/form-data">
                                    <div class="package-filter">
                                            <div class="form-group mb-0">
                                                <label>Select Courier</label>
                                                <div class="mrg-top-10">
                                                    <select class="selectize-group" name="area_id" id="area_id" required="" onchange="getCouriersPackages(value)">
                                                        <option value="0" >All Courier Packages</option>
                                                        @if(count($couriers) > 0)
                                                            @foreach($couriers as $row)
                                                                <option value="{{$row->id}}" <?php if($coerier_id == $row->id){ ?> selected <?php } ?>>{{$row->name}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                    </div>
                                </form>
                                @if($type == 'logs')
                                    <a href="{{URL::to('admin/packege/add')}}" class="package-add-btn">Add New <i class="fa fa-plus"></i></a>
                                @endif
                            </div>
                            <div class="package-advanced-filters" id="package-filters">
                                <div class="filter-field">
                                    <label for="filter_date_from">From Date</label>
                                    <input type="date" id="filter_date_from">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_date_to">To Date</label>
                                    <input type="date" id="filter_date_to">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_freight">Shipment Route</label>
                                    <select id="filter_freight">
                                        <option value="">All Routes</option>
                                        <option value="air">Air</option>
                                        <option value="sea">Sea</option>
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label for="filter_status">Status</label>
                                    <select id="filter_status">
                                        <option value="">All Statuses</option>
                                        @foreach(($package_statuses ?? []) as $status)
                                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label for="filter_carrier">Carrier</label>
                                    <select id="filter_carrier">
                                        <option value="">All Carriers</option>
                                        @foreach(($package_carriers ?? []) as $carrier)
                                            <option value="{{ $carrier }}">{{ $carrier }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label for="filter_hawb">HAWB</label>
                                    <input type="text" id="filter_hawb" placeholder="HAWB">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_tracking">Tracking</label>
                                    <input type="text" id="filter_tracking" placeholder="Carrier or WMS tracking">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_mailbox">Mailbox</label>
                                    <input type="text" id="filter_mailbox" placeholder="Mailbox">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_customer">Customer</label>
                                    <input type="text" id="filter_customer" placeholder="Customer name">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_logged_by">Logged By</label>
                                    <select id="filter_logged_by">
                                        <option value="">Anyone</option>
                                        @foreach(($package_loggers ?? []) as $logger)
                                            <option value="{{ $logger }}">{{ $logger }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label for="filter_invoice">Invoice</label>
                                    <select id="filter_invoice">
                                        <option value="">Any</option>
                                        <option value="yes">Has Invoice</option>
                                        <option value="no">No Invoice</option>
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label for="filter_measure_min">Min Weight / Volume</label>
                                    <input type="number" step="0.01" id="filter_measure_min" placeholder="Min">
                                </div>
                                <div class="filter-field">
                                    <label for="filter_measure_max">Max Weight / Volume</label>
                                    <input type="number" step="0.01" id="filter_measure_max" placeholder="Max">
                                </div>
                                <div class="package-filter-actions">
                                    <button type="button" class="apply-filters" id="apply-package-filters">Apply</button>
                                    <button type="button" id="clear-package-filters">Clear</button>
                                </div>
                            </div>
                            <div class="package-table-wrap">
                                <table id="dataTable" class="table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>id</th>
                                            <th>HAWB.</th>
                                            <th>Tracking No</th>
                                            <th>Carrier</th>
                                            <th>Weight / Volume</th>
                                            <th>Description</th>
                                            <th>User Name</th>
                                            <th>Shipment Status</th>
                                            <th>Courier Name</th>
                                            <th>Logged By</th>
                                            <th>Date Created</th>
                                            <th>Actions</th>
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
    <!-- Modal -->
    <div id="custom_message" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title"></h5>
                    <button type="button" class="close close_remove" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message">
                    <form class="form-horizontal mrg-top-40 pdd-right-30" id="artical_form" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label for="form-1-1" class="col-md-4 control-label">Name</label>
                            <div class="col-md-8">
                                <input type="hidden" value="0" name="id" class="form-control" id="record_id" placeholder="id">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Name">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save_btn btn_save" >Save</button>
                    <button type="button" class="btn btn-danger btn_cancel close_remove">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="delete_modal" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message">
                    Are you sure to delete this record ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save_btn delete_yes" data-dismiss="modal">Yes</button>
                    <button type="button" class="btn btn-danger btn_cancel">No</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="resend_modal" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">Re-send Package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message">
                    Are you sure to re-send this package ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save_btn resend_yes" data-dismiss="modal">Yes</button>
                    <button type="button" class="btn btn-danger btn_cancel">No</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
    <script type="text/javascript">

        $(document).ready(function(e){
            id =0;
            base_url = "{{url('/')}}";

            delete_id = 0;
            
            re_courier_id = 0;
            re_tracking_no = 0;

            var table = $('#dataTable').DataTable( {
                //dom: 'Blfrtip',
                "processing": true,
                //"pageLength": 30,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo url("/packeges-list/".$type.'/'.$coerier_id) ?>',
                    "type": "GET",
                    "data": function(data) {
                        data.filter_date_from = $('#filter_date_from').val();
                        data.filter_date_to = $('#filter_date_to').val();
                        data.filter_freight = $('#filter_freight').val();
                        data.filter_status = $('#filter_status').val();
                        data.filter_carrier = $('#filter_carrier').val();
                        data.filter_hawb = $('#filter_hawb').val();
                        data.filter_tracking = $('#filter_tracking').val();
                        data.filter_mailbox = $('#filter_mailbox').val();
                        data.filter_customer = $('#filter_customer').val();
                        data.filter_logged_by = $('#filter_logged_by').val();
                        data.filter_invoice = $('#filter_invoice').val();
                        data.filter_measure_min = $('#filter_measure_min').val();
                        data.filter_measure_max = $('#filter_measure_max').val();
                    }
                },
                "columns": [
                    { "data": "created_at" , "visible": false},
                    { "data": "manifest_no" },
                    { "data": "supplier_tracking_no" },
                    { "data": "courier" },
                    { "data": "weight" },
                    { "data": "item_description" },
                    { "data": "first_name" },
                    { "data": "name" },
                    { "data": "outlet_name" },
                    { "data": "addedby_name" },
                    { "data": "created_at" },
                    { "data": "action" }
                ],
            } );
            $("body").on("click",".close_remove",function(e){
                $("#title").val('');
                $("#area_val").val('');
            });
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

            $('#apply-package-filters').on('click', function() {
                table.ajax.reload();
            });

            $('#clear-package-filters').on('click', function() {
                $('#package-filters').find('input, select').val('');
                table.search('');
                $('.dataTables_filter input').val('');
                table.ajax.reload();
            });

            $('#package-filters input, #package-filters select').on('change keyup', function(e) {
                if(e.type === 'change' || e.keyCode === 13) {
                    table.ajax.reload();
                }
            });

            $("body").on("click",".edit_button",function(e){
                let value = JSON.parse($(this).attr("data-value"));
                $("#name").val(value.name);
                $("#record_id").val(value.role_id);
                 $("#custom_message").modal("show");
            });

            $("body").on("click",".add_new_record",function(e){
                $("#modal_title").text("Add New Record");
                $("#record_id").val(0);
                $("#custom_message").modal("show");
            });


            $("body").on("click",".btn_save",function(e){
                var _token = $('input#_token').val();
                var id = $("#record_id").val();
                var name = $("#name").val();
                $.ajax({
                    method:"POST",
                    url:'<?php echo url("save-role") ?>',
                    data:{id:id,name:name,_token:_token},
                    success:function(res){
                        if(res.status == true){
                            $("#custom_message").modal("hide");
                            table.ajax.reload( null, false );
                        }else{
                            $.notify(res.message, 'error');
                        }
                        $("#name").val('');
                    },
                    error: function(err) {
                        $.notify('Error occurred while saving.', 'error');
                    }
                });
            });

            $("body").on("click",".btn_cancel",function(e){
                $("#custom_message").modal("hide");
                $("#delete_modal").modal("hide");
                $("#resend_modal").modal("hide");

            });
            
            $("body").on("click",".resend_package",function(e){
                delete_id = $(this).attr("id");
                $("#resend_modal").modal("show");
            });
            
            $("body").on("click",".resend_yes",function(e){
                var _token = $('input#_token').val();
                $.ajax({
                    method:"POST",
                    data:{id:delete_id,_token:_token},
                    url:'<?php echo url("/resend-package") ?>',
                    success:function(res){
                        if(res.status){
                            $.notify(res.message, 'success');
                            table.ajax.reload( null, false );

                        }else{
                            $.notify(res.message, 'error');
                        }

                    }
                });
            });

            $("body").on("click",".delete_btn",function(e){
                delete_id = $(this).attr("id");
                $("#delete_modal").modal("show");
            });

            $("body").on("click",".delete_yes",function(e){
                var _token = $('input#_token').val();
                $.ajax({
                    method:"POST",
                    data:{id:delete_id,_token:_token},
                    url:'<?php echo url("/delete-package") ?>',
                    success:function(res){
                        if(res.status){
                            $.notify(res.message, 'success');
                            table.ajax.reload( null, false );

                        }else{
                            $.notify(res.message, 'error');
                        }

                    }
                });
            });



//            $('#dataTable tbody').on( 'click', 'tr', function () {
//                if ( $(this).hasClass('selected') ) {
//                    $(this).removeClass('selected');
//                }
//                else {
//                    table.$('tr.selected').removeClass('selected');
//                    $(this).addClass('selected');
//                }
//            } );

        });
        
        function getCouriersPackages(id){
            window.location.href=APP_URL+'/admin/packeges/logs/'+id;
        }

    </script>
@endsection
