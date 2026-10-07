@extends('admin.template')
@section('content')
<style>
    .unknown-page {
        --ink: #132238;
        --muted: #667085;
        --line: #e4e7ec;
        --surface: #ffffff;
        --soft: #f6f8fb;
        --primary: #4f46e5;
        --primary-dark: #3730a3;
        --warning: #f59e0b;
        --danger: #e11d48;
        --success: #059669;
        padding-top: 22px;
    }
    .unknown-hero {
        background: linear-gradient(135deg, #172554 0%, #1e3a5f 47%, #0f766e 100%);
        color: #fff;
        border-radius: 8px;
        padding: 24px 26px;
        margin-bottom: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
    }
    .unknown-hero h1 {
        margin: 0 0 6px;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: 0;
    }
    .unknown-hero p {
        margin: 0;
        color: rgba(255,255,255,.78);
        font-size: 14px;
    }
    .unknown-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }
    .unknown-stat {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 16px 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }
    .unknown-stat span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .unknown-stat strong {
        display: block;
        color: var(--ink);
        font-size: 26px;
        line-height: 1.1;
        font-weight: 800;
    }
    .unknown-panel {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .06);
    }
    .unknown-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border-bottom: 1px solid var(--line);
        background: #fbfcfe;
    }
    .unknown-toolbar-title h2 {
        margin: 0 0 4px;
        color: var(--ink);
        font-size: 18px;
        font-weight: 800;
    }
    .unknown-toolbar-title p {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
    }
    .unknown-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .unknown-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        background: #fff;
    }
    .unknown-filters .filter-field label {
        display: block;
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .unknown-filters input,
    .unknown-filters select {
        width: 100%;
        border: 1px solid #d0d5dd;
        border-radius: 7px;
        min-height: 38px;
        padding: 7px 10px;
        color: var(--ink);
        background: #fff;
        outline: none;
    }
    .unknown-filters input:focus,
    .unknown-filters select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
    }
    .unknown-filter-actions {
        display: flex;
        align-items: end;
        gap: 8px;
    }
    .unknown-filter-actions button {
        min-height: 38px;
        border-radius: 7px;
        border: 1px solid #d0d5dd;
        padding: 7px 13px;
        font-weight: 800;
        background: #fff;
        color: var(--ink);
    }
    .unknown-filter-actions .apply-filters {
        border-color: var(--primary);
        background: var(--primary);
        color: #fff;
    }
    .unknown-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        border-radius: 7px;
        border: 0;
        padding: 9px 14px;
        font-weight: 800;
        letter-spacing: 0;
        box-shadow: none;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .unknown-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }
    .unknown-btn-primary {
        background: var(--primary);
        color: #fff !important;
    }
    .unknown-btn-primary:hover {
        background: var(--primary-dark);
    }
    .unknown-btn-warning {
        background: #fef3c7;
        color: #92400e !important;
        border: 1px solid #fde68a;
    }
    .unknown-table-wrap {
        width: 100%;
        overflow-x: auto;
        background: var(--surface);
    }
    .unknown-page table.dataTable {
        min-width: 1320px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
    }
    .unknown-page table.dataTable thead th {
        background: #f8fafc;
        color: #344054;
        border-bottom: 1px solid var(--line) !important;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 14px 16px !important;
        white-space: nowrap;
    }
    .unknown-page table.dataTable tbody td {
        color: #0f172a;
        border-top: 1px solid #edf2f7;
        padding: 16px !important;
        vertical-align: top;
        font-size: 14px;
        line-height: 1.45;
    }
    .unknown-page table.dataTable tbody tr:hover td {
        background: #f9fafb;
    }
    .unknown-page .dataTables_length,
    .unknown-page .dataTables_filter {
        padding: 14px 18px;
        color: var(--muted);
    }
    .unknown-page .dataTables_filter input,
    .unknown-page .dataTables_length select {
        border: 1px solid #d0d5dd;
        border-radius: 7px;
        min-height: 38px;
        padding: 6px 10px;
        margin-left: 8px;
        outline: none;
    }
    .unknown-page .dataTables_filter input:focus,
    .unknown-page .dataTables_length select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
    }
    .unknown-page .dataTables_info,
    .unknown-page .dataTables_paginate {
        padding: 14px 18px !important;
        color: var(--muted) !important;
    }
    .unknown-page .paginate_button {
        border-radius: 6px !important;
        border: 1px solid transparent !important;
    }
    .unknown-page .paginate_button.current {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
    }
    .unknown-page .all_button,
    .unknown-page .btn-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        border-radius: 7px !important;
        padding: 7px 11px !important;
        margin: 0 6px 6px 0;
        font-weight: 800;
        border: 0 !important;
    }
    .unknown-page .btn-info {
        background: #0ea5e9 !important;
        color: #fff !important;
    }
    .unknown-page .btn-danger {
        background: #f43f5e !important;
        color: #fff !important;
    }
    .unknown-page .btn-warning {
        background: #f59e0b !important;
        color: #fff !important;
    }
    #bulk_transit_modal .modal-content,
    #delete_modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
    }
    #bulk_transit_modal .modal-header,
    #delete_modal .modal-header {
        border-bottom: 1px solid #e4e7ec;
        padding: 18px 20px;
    }
    #bulk_transit_modal .modal-body,
    #delete_modal .modal-body {
        color: #132238;
        font-size: 15px;
        padding: 20px;
    }
    #bulk_transit_modal .modal-footer,
    #delete_modal .modal-footer {
        border-top: 1px solid #e4e7ec;
        padding: 14px 20px;
    }
    #bulk_transit_modal .modal-content,
    #delete_modal .modal-content {
        width: 100%;
        top: 0;
        position: relative;
    }
    .list-info img.thumb-img {
        height: 100px;
        width: 100px;
    }
    @media (max-width: 991px) {
        .unknown-stats {
            grid-template-columns: 1fr;
        }
        .unknown-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
        .unknown-actions {
            justify-content: flex-start;
            width: 100%;
        }
        .unknown-filters {
            grid-template-columns: 1fr;
        }
    }
</style>
<input type="hidden" id="_token" value="{{ csrf_token() }}">
@php
    $isAssignedClaims = ($unknown_mode ?? 'general') === 'assigned';
@endphp
<div class="main-content unknown-page">
    @if(Session::has('success_msg'))
        <div class="alert alert-success fade in" style="margin:10px 15px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p class="text-center">{{ Session::get('success_msg') }}</p>
        </div>
    @endif
    <div class="container-fluid">
        <div class="unknown-hero">
            <h1>{{ $isAssignedClaims ? 'Assigned Claims' : 'Unknown Packages' }}</h1>
            <p>{{ $isAssignedClaims ? 'Review packages assigned to a courier but not yet matched to a customer.' : 'Review packages that need courier assignment or cleanup before they move through the warehouse flow.' }}</p>
        </div>
        <div class="unknown-stats">
            <div class="unknown-stat">
                <span>{{ $isAssignedClaims ? 'Assigned Claims' : 'Unknown Packages' }}</span>
                <strong>{{ number_format($unknown_count ?? 0) }}</strong>
            </div>
            <div class="unknown-stat">
                <span>Total Air Weight</span>
                <strong>{{ number_format($unknown_weight_lb ?? 0, 2) }} lb</strong>
            </div>
            <div class="unknown-stat">
                <span>Oldest Age</span>
                <strong>{{ number_format($unknown_oldest_days ?? 0) }} days</strong>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="unknown-panel">
                    <div class="unknown-toolbar">
                        <div class="unknown-toolbar-title">
                            <h2>{{ $isAssignedClaims ? 'Assigned Claim Queue' : 'Package Review Queue' }}</h2>
                            <p>{{ $isAssignedClaims ? 'Search, edit, or delete packages that have courier routing but no customer match.' : 'Search, edit, delete, or move all unknown packages into transit.' }}</p>
                        </div>
                        <div class="unknown-actions">
                            <a href="{{ URL::to('admin/packege/add') }}" class="unknown-btn unknown-btn-primary"><i class="fa fa-plus"></i> Add New</a>
                            @if(! $isAssignedClaims)
                                <button id="mark-in-transit" class="unknown-btn unknown-btn-warning"><i class="fa fa-random"></i> Mark All In Transit</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-block" style="padding:0;">
                        <div class="unknown-filters" id="unknown-filters">
                            <div class="filter-field">
                                <label for="filter_date_from">From Date</label>
                                <input type="date" id="filter_date_from">
                            </div>
                            <div class="filter-field">
                                <label for="filter_date_to">To Date</label>
                                <input type="date" id="filter_date_to">
                            </div>
                            <div class="filter-field">
                                <label for="filter_age">Ageing</label>
                                <select id="filter_age">
                                    <option value="">All Ages</option>
                                    <option value="2">0-2 Days</option>
                                    <option value="30">3-30 Days</option>
                                    <option value="60">31-60 Days</option>
                                    <option value="90">61-90 Days</option>
                                    <option value="120">91-120 Days</option>
                                    <option value="121">121+ Days</option>
                                </select>
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
                            @if($isAssignedClaims)
                                <div class="filter-field">
                                    <label for="filter_assigned_courier_id">Courier System</label>
                                    <select id="filter_assigned_courier_id">
                                        <option value="">All Courier Systems</option>
                                        @foreach(($couriers ?? []) as $courier)
                                            <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
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
                                <label for="filter_customer">Name On Package</label>
                                <input type="text" id="filter_customer" placeholder="Customer or consignee">
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
                                <label for="filter_measure_min">Min Weight / Volume</label>
                                <input type="number" step="0.01" id="filter_measure_min" placeholder="Min">
                            </div>
                            <div class="filter-field">
                                <label for="filter_measure_max">Max Weight / Volume</label>
                                <input type="number" step="0.01" id="filter_measure_max" placeholder="Max">
                            </div>
                            <div class="unknown-filter-actions">
                                <button type="button" class="apply-filters" id="apply-unknown-filters">Apply</button>
                                <button type="button" id="clear-unknown-filters">Clear</button>
                            </div>
                        </div>
                        <div class="unknown-table-wrap">
                            <table id="dataTable" class="table table-lg table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>HAWB</th>
                                        <th>Tracking No</th>
                                        <th>Carrier</th>
                                        <th>Weight / Volume</th>
                                        <th>Description</th>
                                        <th>User Name</th>
                                        <th>Shipment Status</th>
                                        <th>Courier Name</th>
                                        <th>Ageing</th>
                                        <th>Logged By</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
     <div id="bulk_transit_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bulkTransitTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkTransitTitle">Move Unknown Packages</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    This will mark every unknown package as In Transit. Packages will stay in the system and move out of the unknown queue.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning bulk_transit_yes">Mark All In Transit</button>
                </div>
            </div>
        </div>
    </div>

     <div id="delete_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body alert_message">
                    This package will be deleted from the WMS package list.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn_cancel" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger save_btn delete_yes" data-dismiss="modal">Delete Package</button>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        delete_id = 0;
        id =0;
        @if(! $isAssignedClaims && in_array((string) $coerier_id, ['2', '30', '60', '90', '120', '121'], true))
            $('#filter_age').val('{{ $coerier_id }}');
        @endif
        var table = $('#dataTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": '{{ url("/packeges-list/" . $type . '/' . $coerier_id) }}',
                "type": "GET",
                "data": function(data) {
                    data.filter_date_from = $('#filter_date_from').val();
                    data.filter_date_to = $('#filter_date_to').val();
                    data.filter_age = $('#filter_age').val();
                    data.filter_freight = $('#filter_freight').val();
                    data.filter_status = $('#filter_status').val();
                    data.filter_assigned_courier_id = $('#filter_assigned_courier_id').length ? $('#filter_assigned_courier_id').val() : '';
                    data.filter_carrier = $('#filter_carrier').val();
                    data.filter_hawb = $('#filter_hawb').val();
                    data.filter_tracking = $('#filter_tracking').val();
                    data.filter_customer = $('#filter_customer').val();
                    data.filter_logged_by = $('#filter_logged_by').val();
                    data.filter_measure_min = $('#filter_measure_min').val();
                    data.filter_measure_max = $('#filter_measure_max').val();
                }
            },
            "scrollX": true,
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "search": "",
                "searchPlaceholder": "Search HAWB, tracking, carrier, user...",
                "lengthMenu": "Show _MENU_ rows",
                "processing": "Loading packages..."
            },
            "columns": [
                { "data": "packeg_id" },
                { "data": "manifest_no" },
                { "data": "supplier_tracking_no" },
                { "data": "courier" },
                { "data": "weight" },
                { "data": "item_description" },
                { "data": "first_name" },
                { "data": "name" },
                { "data": "outlet_name" },
                { "data": "ageing" },
                { "data": "addedby_name" },
                { "data": "created_at" },
                { "data": "action" }
            ]
        });

        $('#apply-unknown-filters').on('click', function() {
            table.ajax.reload();
        });

        $('#clear-unknown-filters').on('click', function() {
            $('#unknown-filters').find('input, select').val('');
            table.search('');
            $('.dataTables_filter input').val('');
            table.ajax.reload();
        });

        $('#unknown-filters input, #unknown-filters select').on('change keyup', function(e) {
            if(e.type === 'change' || e.keyCode === 13) {
                table.ajax.reload();
            }
        });

        $("#mark-in-transit").click(function() {
            $("#bulk_transit_modal").modal("show");
        });

        $("body").on("click", ".bulk_transit_yes", function() {
            var $button = $(this);
            $button.prop("disabled", true).text("Updating...");

            $.ajax({
                url: "{{ url('/admin/packeges/mark-in-transit') }}",
                type: "POST",
                data: {
                    _token: $("#_token").val()
                },
                success: function(response) {
                    $("#bulk_transit_modal").modal("hide");
                    if (response.status) {
                        $.notify(response.message, "success");
                        table.ajax.reload(null, false);
                    } else {
                        $.notify(response.message, "error");
                    }
                },
                error: function() {
                    $("#bulk_transit_modal").modal("hide");
                    $.notify("An error occurred while updating the packages.", "error");
                },
                complete: function() {
                    $button.prop("disabled", false).text("Mark All In Transit");
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
    });
</script>
@endsection
