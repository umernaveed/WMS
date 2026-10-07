@extends('admin.template')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@php
    $editingPackage = $package ?? null;
    $isPackageEdit = (bool) ($isEdit ?? false);
    $packageTypeValue = old('package_type');
    if (! $packageTypeValue && $editingPackage) {
        $packageTypeValue = trim(($editingPackage->package_code ?? '').'-'.($editingPackage->package_type ?? ''), '-');
    }
    $selectedFreight = old('freight_type', $editingPackage->freight_type ?? 'Air Freight');
    $selectedWarehouse = old('warehouse_code', $editingPackage->warehouse_code ?? 'USA');
    $selectedLabelType = old('label_type', $editingPackage->label_type ?? ((strtoupper((string) $selectedWarehouse) === 'CHINA' ? 'CN' : 'US').' '.(stripos((string) $selectedFreight, 'sea') !== false ? 'Sea' : 'Air')));
    $selectedRouting = old('routing_status', $editingPackage->routing_status ?? ((int) ($editingPackage->courier_id ?? 0) > 0 ? 'VERIFIED' : 'GENERAL_UNKNOWN'));
    $selectedEntryMode = old('entry_mode', $editingPackage->entry_mode ?? 'STANDARD');
    $selectedCourierId = old('courier_id', $editingPackage->courier_id ?? '');
    $selectedMailbox = old('mailbox', $editingPackage && ($editingPackage->mailbox ?? '0') !== '0' ? $editingPackage->mailbox : '');
    $selectedUserName = old('customer_name', $editingPackage->user_name ?? '');
    $selectedCourierName = $selectedCourierId ? (($couriers ?? collect())->firstWhere('id', (int) $selectedCourierId)->name ?? '') : '';
    $selectedConsignee = old('consignee_name_on_package', $editingPackage->consignee_name_on_package ?? (($selectedRouting === 'VERIFIED') ? '' : ($editingPackage->user_name ?? '')));
    $selectedUnknownReason = old('unknown_reason', $editingPackage->unknown_reason ?? '');
    $selectedDescription = old('item_description', $editingPackage->item_description ?? '');
    $packageTypeLabel = $editingPackage ? trim(($editingPackage->package_type ?? '') ?: ($editingPackage->package_code ?? 'Current Package Type')) : '';
    $existingPackageImages = [];
    if ($editingPackage && ! empty($editingPackage->package_image_paths)) {
        $decodedImages = json_decode((string) $editingPackage->package_image_paths, true);
        $existingPackageImages = is_array($decodedImages) ? array_slice(array_values(array_filter($decodedImages)), 0, 5) : [];
    }
    $initialPackageJson = [
        'entry_mode' => strtolower($selectedEntryMode === 'FORCED_UNKNOWN' ? 'unknown' : ($selectedEntryMode === 'DROPOFF' ? 'dropoff' : 'standard')),
        'routing_status' => $selectedRouting,
        'courier_id' => (string) $selectedCourierId,
        'courier_name' => $selectedCourierName,
        'mailbox' => $selectedMailbox,
        'customer_name' => $selectedUserName,
        'consignee' => $selectedConsignee,
        'unknown_reason' => $selectedUnknownReason,
        'tracking' => old('supplier_tracking_no', $editingPackage->supplier_tracking_no ?? ''),
        'carrier' => old('courier', $editingPackage->courier ?? ''),
        'weight' => old('weight', $editingPackage->weight ?? ''),
        'package_type' => $packageTypeValue,
        'package_type_label' => $packageTypeLabel,
    ];
@endphp

<style>
    .content-wrapper, .page-container, .main-content { background: #f8fafc; }
    .wms-shell { min-height: calc(100vh - 80px); margin: -25px -15px 0; display: flex; background: #f8fafc; color: #1e293b; font-family: Arial, sans-serif; }
    .wms-sidebar { width: 256px; background: #0f172a; color: #cbd5e1; display: flex; flex-direction: column; flex: 0 0 256px; }
    .wms-logo { height: 64px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #1e293b; padding: 0 24px; }
    .wms-logo img { max-height: 40px; width: auto; object-fit: contain; }
    .wms-warehouse { padding: 16px; border-bottom: 1px solid rgba(30,41,59,.55); }
    .wms-label-mini { display: block; color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 6px; }
    .wms-select-dark { width: 100%; background: #1e293b; border: 1px solid #334155; color: #fff; border-radius: 8px; padding: 9px 10px; outline: 0; }
    .wms-nav { padding: 10px 0; flex: 1; overflow-y: auto; }
    .wms-nav-title { padding: 12px 16px 6px; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .wms-nav-link, .wms-nav-head { display: flex; align-items: center; gap: 12px; width: 100%; padding: 10px 16px; border: 0; background: transparent; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 600; }
    .wms-nav-link:hover, .wms-nav-head:hover { background: #1e293b; color: #fff; text-decoration: none; }
    .wms-nav-link.active { color: #818cf8; background: rgba(30,41,59,.7); border-left: 2px solid #6366f1; padding-left: 14px; }
    .wms-nav-sub { background: rgba(30,41,59,.35); padding: 4px 0; }
    .wms-nav-sub .wms-nav-link { padding-left: 44px; color: #94a3b8; font-weight: 500; }
    .wms-user { border-top: 1px solid #1e293b; background: rgba(2,6,23,.45); padding: 14px 16px; display: flex; align-items: center; gap: 12px; }
    .wms-avatar { width: 36px; height: 36px; border-radius: 999px; border: 2px solid #334155; }
    .wms-user strong { display: block; color: #fff; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wms-user span { color: #4ade80; font-size: 12px; }
    .wms-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .wms-topbar { height: 64px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .wms-search { position: relative; width: min(390px, 42vw); }
    .wms-search i { position: absolute; top: 50%; left: 13px; transform: translateY(-50%); color: #94a3b8; }
    .wms-search input { width: 100%; border: 0; background: #f1f5f9; border-radius: 8px; padding: 10px 12px 10px 40px; font-size: 14px; outline: 0; }
    .wms-top-actions { display: flex; align-items: center; gap: 14px; }
    .wms-mode-pill { display: flex; align-items: center; gap: 8px; border-right: 1px solid #e2e8f0; padding-right: 16px; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .wms-dot { width: 12px; height: 12px; border-radius: 999px; background: #6366f1; box-shadow: 0 0 0 6px rgba(99,102,241,.12); }
    .wms-log-btn { border: 0; border-radius: 8px; background: #4f46e5; color: #fff; padding: 10px 16px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 18px rgba(79,70,229,.18); }
    .wms-content { padding: 24px 32px 32px; overflow-y: auto; }
    .wms-page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
    .wms-page-head h1 { margin: 0; color: #1e293b; font-size: 26px; font-weight: 800; }
    .wms-page-head p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
    .wms-head-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .wms-soft-btn, .wms-btn { border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 8px; padding: 8px 12px; display: inline-flex; gap: 7px; align-items: center; font-size: 13px; font-weight: 700; }
    .wms-entry { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; }
    .wms-entry-header { padding: 18px 22px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
    .wms-entry-title { display: flex; align-items: center; gap: 10px; font-size: 20px; font-weight: 800; color: #1e293b; }
    .wms-entry-title i { color: #4f46e5; font-size: 26px; }
    .wms-mode-row { padding: 16px 22px; display: flex; flex-wrap: wrap; gap: 8px; border-bottom: 1px solid #f1f5f9; }
    .wms-mode-button { border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 8px; padding: 9px 14px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
    .wms-mode-button.active { color: #4338ca; background: #eef2ff; border-color: #c7d2fe; }
    .wms-form { padding: 22px; background: rgba(248,250,252,.65); }
    .wms-grid-top, .wms-logistics { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(0, 2fr); gap: 22px; margin-bottom: 22px; }
    .wms-logistics { grid-template-columns: repeat(2, minmax(0, 1fr)); margin: 22px 0 0; }
    .wms-vision { min-height: 188px; background: #0f172a; border-radius: 12px; padding: 16px; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; }
    .wms-vision:before { content: ""; position: absolute; inset: 0; opacity: .16; background-image: linear-gradient(45deg, rgba(255,255,255,.12) 25%, transparent 25%), linear-gradient(-45deg, rgba(255,255,255,.12) 25%, transparent 25%); background-size: 18px 18px; }
    .wms-vision-center { position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #cbd5e1; text-align: center; }
    .wms-vision-center i { font-size: 46px; color: #94a3b8; margin-bottom: 7px; }
    .wms-input-methods { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .wms-tool-btn { border: 0; color: #fff; border-radius: 6px; min-height: 62px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; font-size: 12px; font-weight: 800; cursor: pointer; }
    .wms-tool-btn i { font-size: 20px; }
    .wms-blue { background: #2563eb; } .wms-purple { background: #9333ea; } .wms-slate { background: #334155; }
    .wms-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 2px rgba(15,23,42,.04); padding: 20px; }
    .wms-card h3 { margin: 0 0 16px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; font-weight: 900; }
    .wms-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .wms-field.full { grid-column: 1 / -1; }
    .wms-field label { display: block; color: #334155; font-size: 13px; font-weight: 800; margin-bottom: 6px; }
    .wms-input, .wms-select, .wms-textarea { width: 100%; border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 8px; padding: 10px 12px; outline: 0; min-height: 42px; }
    .wms-input:focus, .wms-select:focus, .wms-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.16); }
    .wms-readonly { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
    .wms-input-group { display: flex; }
    .wms-input-group .wms-input { border-radius: 8px 0 0 8px; font-family: Consolas, Monaco, monospace; letter-spacing: .03em; }
    .wms-input-addon { border: 1px solid #cbd5e1; border-left: 0; background: #f1f5f9; color: #64748b; border-radius: 0 8px 8px 0; width: 44px; display: inline-flex; align-items: center; justify-content: center; }
    .wms-note { display: block; color: #64748b; font-size: 12px; margin-top: 5px; }
    .wms-note.primary { color: #4f46e5; font-weight: 700; }
    .wms-error { color: #e11d48; font-size: 12px; margin-top: 6px; display: block; min-height: 16px; }
    .wms-routing { position: relative; border-left: 4px solid #6366f1; }
    .wms-card-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; }
    .wms-card-head h3 { margin: 0; padding: 0; border: 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: .05em; }
    .wms-badge { background: #e0e7ff; color: #3730a3; border-radius: 6px; padding: 5px 8px; font-size: 11px; font-weight: 900; }
    .wms-hidden { display: none !important; }
    .wms-manual { margin-top: 16px; padding: 14px; background: rgba(254,243,199,.55); border: 1px solid #fde68a; border-radius: 10px; }
    .wms-manual p { margin: 0 0 8px; color: #b45309; font-size: 12px; font-weight: 900; text-transform: uppercase; display: flex; align-items: center; gap: 5px; }
    .wms-route-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .wms-route-option input { position: absolute; opacity: 0; pointer-events: none; }
    .wms-route-box { border: 1px solid #e2e8f0; border-radius: 9px; padding: 13px 12px; display: flex; align-items: center; justify-content: center; gap: 8px; color: #334155; font-weight: 800; cursor: pointer; }
    .wms-route-option input:checked + .wms-route-box { background: #eef2ff; border-color: #6366f1; box-shadow: 0 0 0 1px #6366f1; color: #3730a3; }
    .wms-summary { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; background: #fff; margin-top: 16px; }
    .wms-summary-row { display: flex; justify-content: space-between; gap: 16px; color: #64748b; font-size: 13px; margin-bottom: 8px; }
    .wms-summary-row strong { color: #334155; }
    .wms-summary-total { border-top: 1px solid #f1f5f9; padding-top: 10px; display: flex; align-items: center; justify-content: space-between; color: #312e81; font-size: 16px; font-weight: 900; }
    .wms-summary-total strong { color: #4f46e5; font-size: 22px; }
    .wms-volume { margin-top: 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 14px; }
    .wms-dims { display: flex; align-items: center; gap: 8px; }
    .wms-dims input { min-width: 0; }
    .wms-direct-volume { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px solid #c7d2fe; }
    .wms-textarea { min-height: 92px; resize: vertical; }
    .wms-image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(82px, 1fr)); gap: 10px; margin-top: 10px; }
    .wms-image-tile { position: relative; display: block; border: 1px solid #dbe4ef; border-radius: 8px; overflow: hidden; background: #f8fafc; aspect-ratio: 1 / 1; }
    .wms-image-tile a { display: block; width: 100%; height: 100%; color: inherit; }
    .wms-image-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .wms-image-tile span { position: absolute; left: 0; right: 0; bottom: 0; padding: 4px 6px; background: rgba(15,23,42,.72); color: #fff; font-size: 10px; font-weight: 800; text-align: center; }
    .wms-image-remove { position: absolute; top: 5px; right: 5px; z-index: 2; width: 24px; height: 24px; border: 0; border-radius: 999px; background: rgba(225,29,72,.95); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; line-height: 1; cursor: pointer; box-shadow: 0 5px 14px rgba(15,23,42,.22); }
    .wms-image-remove:hover { background: #be123c; }
    .wms-footer { padding: 16px 22px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .wms-status { color: #64748b; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .wms-status i { color: #22c55e; font-size: 20px; }
    .wms-footer-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .wms-btn { padding: 10px 16px; }
    .wms-btn-primary { border-color: #4f46e5; background: #4f46e5; color: #fff; }
    .wms-btn-success { border-color: #059669; background: #059669; color: #fff; }
    .wms-alert-wrap { padding: 16px 24px 0; }
    .wms-scanner .modal-dialog { max-width: 520px; }
    #simple-scanner { min-height: 220px; background: #020617; border-radius: 10px; position: relative; overflow: hidden; }
    #simple-scanner video { width: 100% !important; min-height: 220px; object-fit: cover; }
    .google-corner { position: absolute; width: 40px; height: 40px; border: 3px solid #22c55e; z-index: 1000; }
    .google-line { position: absolute; left: 20px; right: 20px; height: 2px; background: linear-gradient(90deg, transparent, #22c55e, transparent); animation: wmsScan 2s ease-in-out infinite; z-index: 1000; }
    @keyframes wmsScan { 0% { top: 20px; opacity: 0; } 25%, 75% { opacity: 1; } 100% { top: calc(100% - 20px); opacity: 0; } }
    @media (max-width: 992px) { .wms-sidebar { display: none; } .wms-shell { margin: -20px -15px 0; } .wms-grid-top, .wms-logistics { grid-template-columns: 1fr; } .wms-content { padding: 18px; } }
    @media (max-width: 640px) { .wms-topbar { padding: 0 14px; } .wms-search, .wms-mode-pill { display: none; } .wms-page-head, .wms-footer { align-items: flex-start; flex-direction: column; } .wms-fields, .wms-route-row, .wms-direct-volume { grid-template-columns: 1fr; } .wms-footer-actions, .wms-btn { width: 100%; justify-content: center; } }
</style>

<div class="main-content wms-package-page">
    @if(Session::has('error_msg') || Session::has('success_msg') || $errors->any())
        <div class="wms-alert-wrap">
            @include('common.errors')
            @if(Session::has('error_msg')) <div class="alert alert-danger">{{ Session::get('error_msg') }}</div> @endif
            @if(Session::has('success_msg')) <div class="alert alert-success">{{ Session::get('success_msg') }}</div> @endif
        </div>
    @endif

    <div class="wms-content">
                <div class="wms-page-head">
                    <div><h1>Processor Dashboard</h1><p>{{ $isPackageEdit ? 'Update packages with the same smart entry workflow as package creation.' : 'Log packages with the same smart entry workflow as the processor portal.' }}</p></div>
                    <div class="wms-head-actions">
                        <button type="button" class="wms-soft-btn"><i class="ph ph-funnel"></i> Filter Progress</button>
                        <button type="button" class="wms-soft-btn"><i class="ph ph-export"></i> Export</button>
                        <button type="button" class="wms-soft-btn" onclick="window.print()"><i class="ph ph-printer"></i> Print</button>
                    </div>
                </div>

                <div class="wms-entry">
                    <div class="wms-entry-header"><div class="wms-entry-title"><i class="ph-fill ph-scan"></i> {{ $isPackageEdit ? 'Smart Package Update' : 'Smart Package Entry' }}</div></div>
                    <div class="wms-mode-row">
                        <button type="button" id="btn-mode-standard" class="wms-mode-button active" onclick="setEntryMode('standard')">Standard Entry</button>
                        <button type="button" id="btn-mode-dropoff" class="wms-mode-button" onclick="setEntryMode('dropoff')"><i class="ph ph-arrow-circle-down"></i> Drop-off</button>
                        <button type="button" id="btn-mode-unknown" class="wms-mode-button" onclick="setEntryMode('unknown')"><i class="ph ph-question"></i> Force General Unknown</button>
                    </div>

                    <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <input type="hidden" name="price" id="price" value="{{ old('price', $editingPackage->price ?? 0) }}">
                        <input type="hidden" name="mailbox_search" id="mailbox_search" value="{{ old('mailbox_search', $selectedMailbox) }}">
                        <input type="hidden" name="entry_mode" id="entry_mode" value="{{ $selectedEntryMode }}">
                        <input type="hidden" name="routing_status" id="routing_status" value="{{ $selectedRouting }}">
                        <input type="hidden" name="warehouse_code" id="warehouse_code" value="{{ $selectedWarehouse }}">
                        <input type="hidden" name="label_type" id="label_type" value="{{ $selectedLabelType }}">
                        <input type="hidden" name="weight_unit" id="weight_unit" value="{{ old('weight_unit', $editingPackage->weight_unit ?? 'lb') }}">
                        <input type="hidden" name="chargeable_weight" id="chargeable_weight" value="{{ old('chargeable_weight', $editingPackage->chargeable_weight ?? '') }}">
                        <input type="hidden" name="capture_method" id="capture_method" value="{{ old('capture_method', $editingPackage->capture_method ?? 'manual') }}">
                        <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ old('idempotency_key', $editingPackage->idempotency_key ?? '') }}">
                        <input type="hidden" name="print_after_save" id="print_after_save" value="0">

                        <div class="wms-form">
                            <div class="wms-grid-top">
                                <div class="wms-vision">
                                    <div class="wms-vision-center"><i class="ph-light ph-aperture"></i><strong>Input Method</strong></div>
                                    <div class="wms-input-methods">
                                        <button type="button" class="wms-tool-btn wms-blue" onclick="startTrackingScanner()"><i class="ph ph-barcode"></i> Scan</button>
                                        <button type="button" id="ai-btn" class="wms-tool-btn wms-purple" onclick="triggerAIFill()"><i class="ph ph-magic-wand"></i> AI Fill</button>
                                        <label class="wms-tool-btn wms-slate" for="pkg-images"><i class="ph ph-camera"></i> Photo</label>
                                    </div>
                                </div>

                                <div class="wms-card">
                                    <h3>Package Details</h3>
                                    <div class="wms-fields">
                                        <div class="wms-field full">
                                            <label>Shipment Route</label>
                                            <input type="hidden" name="freight_type" id="freight_type" value="{{ $selectedFreight }}">
                                            <div class="wms-route-row">
                                                <label class="wms-route-option"><input type="radio" name="shipment_type_choice" value="air" {{ stripos($selectedFreight, 'sea') === false ? 'checked' : '' }} onchange="toggleShipmentRoute()"><span class="wms-route-box"><i class="ph-fill ph-airplane-takeoff"></i> Air Cargo</span></label>
                                                <label class="wms-route-option"><input type="radio" name="shipment_type_choice" value="sea" {{ stripos($selectedFreight, 'sea') !== false ? 'checked' : '' }} onchange="toggleShipmentRoute()"><span class="wms-route-box"><i class="ph-fill ph-boat"></i> Sea Vessel</span></label>
                                            </div>
                                        </div>
                                        <div class="wms-field">
                                            <input type="hidden" name="courier_id" id="courier_id" value="{{ $selectedCourierId }}">
                                            <label for="package-region">Region</label>
                                            <select id="package-region" class="wms-select" onchange="changeWarehouse(this.value)">
                                                <option value="USA" {{ strtoupper((string) $selectedWarehouse) !== 'CHINA' ? 'selected' : '' }}>USA</option>
                                                <option value="China" {{ strtoupper((string) $selectedWarehouse) === 'CHINA' ? 'selected' : '' }}>China</option>
                                            </select>
                                            <span id="label-type-preview" class="wms-note primary">{{ $selectedLabelType }}</span>
                                        </div>
                                        <div class="wms-field" id="package-weight-field">
                                            <label id="lbl-weight" for="pkg-weight">Weight (decimal lbs or lb:oz) *</label>
                                            <div class="wms-input-group">
                                                <input type="text" name="weight" id="pkg-weight" class="wms-input" value="{{ old('weight', $editingPackage->weight ?? '') }}" placeholder="e.g. 4.5 or 2:9.1" required oninput="convertWeight()" onblur="formatWeightInput()">
                                                <select id="weight-unit-select" class="wms-input-addon" onchange="setWeightUnit(this.value)">
                                                    <option value="lb" {{ old('weight_unit', $editingPackage->weight_unit ?? 'lb') === 'lb' ? 'selected' : '' }}>lbs</option>
                                                    <option value="kg" {{ old('weight_unit', $editingPackage->weight_unit ?? '') === 'kg' ? 'selected' : '' }}>kg</option>
                                                </select>
                                            </div>
                                            <span id="weight-conversion" class="wms-note primary wms-hidden"></span>
                                        </div>
                                        <div class="wms-field full">
                                            <label for="supplier_tracking_no">Tracking Number *</label>
                                            <div class="wms-input-group">
                                                <input type="text" class="wms-input supplier_tracking_no" name="supplier_tracking_no" id="supplier_tracking_no" value="{{ old('supplier_tracking_no', $editingPackage->supplier_tracking_no ?? '') }}" placeholder="Scan or type tracking..." required>
                                                <button type="button" class="wms-input-addon" onclick="copyTracking()" title="Copy tracking"><i class="ph ph-copy"></i></button>
                                            </div>
                                            <label id="traking_no-message" class="wms-error"></label>
                                        </div>
                                        <div class="wms-field full">
                                            <label for="courier">Carrier</label>
                                            <select name="courier" id="courier" class="wms-select" required>
                                                <option value="">Select Carrier</option>
                                                @foreach(($carriers ?? []) as $carrier)
                                                    <option value="{{ $carrier->name }}" {{ old('courier', $editingPackage->courier ?? '') == $carrier->name ? 'selected' : '' }}>{{ $carrier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="wms-card wms-routing">
                                <div class="wms-card-head">
                                    <h3><i class="ph-fill ph-identification-card"></i> Identity & Routing</h3>
                                    <span id="routing-badge" class="wms-badge wms-hidden">Assigned Unknown Mode</span>
                                </div>
                                <div class="wms-fields">
                                    <div class="wms-field" id="col-mailbox">
                                        <label for="mailbox-number">Mailbox / Courier Code</label>
                                        <input type="hidden" id="mailbox" name="mailbox" value="{{ $selectedMailbox }}">
                                        <input type="text" id="mailbox-number" class="wms-input" list="mailbox-options" placeholder="e.g. JAM-4829 or JAM" autocomplete="off" oninput="syncMailboxDetails()" value="{{ $selectedMailbox }}">
                                        <datalist id="mailbox-options"></datalist>
                                        <span id="mailbox-hint" class="wms-note">Full mailbox auto-fills customer.</span>
                                    </div>
                                    <div class="wms-field wms-hidden" id="col-intended-courier">
                                        <label for="intended-courier-select">Intended Courier (Drop-off)</label>
                                        <select id="intended-courier-select" class="wms-select" onchange="setIntendedCourier()">
                                            <option value="">-- Select Courier --</option>
                                            @foreach(($couriers ?? []) as $row)
                                                <option value="{{$row->name}}">{{$row->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="wms-field" id="col-customer">
                                        <label for="customer-name">Customer Name</label>
                                        <input type="text" id="customer-name" class="wms-input wms-readonly" placeholder="Auto-filled" readonly value="{{ $selectedUserName ? $selectedUserName.($selectedRouting === 'VERIFIED' ? ' (Verified)' : '') : '' }}">
                                    </div>
                                    <div class="wms-field" id="col-assigned-courier">
                                        <label for="courier-name">Assigned Courier</label>
                                        <input type="text" id="courier-name" class="wms-input wms-readonly" placeholder="Auto-filled" readonly value="{{ $selectedCourierName }}">
                                    </div>
                                </div>
                                <div id="manual-consignee-section" class="wms-manual wms-hidden">
                                    <p id="manual-consignee-msg"><i class="ph-fill ph-warning-circle"></i> Customer not found. Logging as Assigned Unknown.</p>
                                    <label for="manual-consignee">Type Consignee Name on Package</label>
                                    <input type="text" id="manual-consignee" name="consignee_name_on_package" class="wms-input" placeholder="Enter name exactly as it appears" value="{{ $selectedConsignee }}">
                                    <div class="wms-field" style="margin-top:12px;">
                                        <label for="unknown_reason">Reason if no readable name</label>
                                        <select name="unknown_reason" id="unknown_reason" class="wms-select">
                                            <option value="">Select reason</option>
                                            <option value="unreadable" {{ $selectedUnknownReason === 'unreadable' ? 'selected' : '' }}>Unreadable label</option>
                                            <option value="no_name_visible" {{ $selectedUnknownReason === 'no_name_visible' ? 'selected' : '' }}>No name visible</option>
                                            <option value="other" {{ $selectedUnknownReason === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="wms-logistics">
                                <div class="wms-card">
                                    <div class="wms-fields">
                                        <div class="wms-field">
                                            <label for="package_type">Package Type</label>
                                            <select name="package_type" id="package_type" class="wms-select" required>
                                                <option value="">Select A Package Category</option>
                                                @if($isPackageEdit && $packageTypeValue)
                                                    <option value="{{ $packageTypeValue }}" selected>{{ $packageTypeLabel ?: $packageTypeValue }}</option>
                                                @endif
                                                @foreach(($types ?? []) as $row)
                                                    @php
                                                        $typeCode = $row->code ?? $row->TypeCode ?? '';
                                                        $typeName = $row->type ?? $row->TypeName ?? '';
                                                        $typeOptionValue = trim($typeCode.'-'.$typeName, '-');
                                                    @endphp
                                                    <option value="{{$typeOptionValue}}" {{ $packageTypeValue == $typeOptionValue ? 'selected' : '' }}>{{$typeName}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="wms-field wms-hidden" id="dropoff-pieces-container">
                                            <label for="dropoff-pieces">Pieces</label>
                                            <input type="number" id="dropoff-pieces" name="pieces" class="wms-input" value="{{ old('pieces', $editingPackage->pieces ?? 1) }}" min="1">
                                        </div>
                                    </div>

                                    <div class="wms-summary">
                                        <div class="wms-summary-row"><span id="summary-actual-label">Actual Weight:</span><strong id="summary-actual">0.00 lbs</strong></div>
                                        <div id="summary-dim-row" class="wms-summary-row wms-hidden"><span>Sea Volume:</span><strong id="summary-dim">0.00 cu ft</strong></div>
                                        <div class="wms-summary-total"><span id="summary-total-label">Chargeable Weight:</span><strong id="summary-chargeable">0 lbs</strong></div>
                                    </div>

                                    <div id="volume-section" class="wms-volume wms-hidden">
                                        <div class="wms-card-head">
                                            <h3><i class="ph ph-cube"></i> Dimensions - Sea Volume</h3>
                                            <select id="dim-unit" name="dimension_unit" onchange="calculateBilling()" class="wms-select" style="max-width:150px; min-height:34px; padding:5px 8px;">
                                                <option value="in" {{ old('dimension_unit', $editingPackage->dimension_unit ?? 'in') === 'in' ? 'selected' : '' }}>Inches (in)</option>
                                                <option value="cm" {{ old('dimension_unit', $editingPackage->dimension_unit ?? '') === 'cm' ? 'selected' : '' }}>Centimeters (cm)</option>
                                                <option value="ft" {{ old('dimension_unit', $editingPackage->dimension_unit ?? '') === 'ft' ? 'selected' : '' }}>Feet (ft)</option>
                                            </select>
                                        </div>
                                        <div class="wms-dims">
                                            <input type="number" id="dim-l" name="dimension_length" placeholder="L" class="wms-input" min="0" step="0.01" oninput="calculateBilling()" value="{{ old('dimension_length', $editingPackage->dimension_length ?? '') }}"><span>x</span>
                                            <input type="number" id="dim-w" name="dimension_width" placeholder="W" class="wms-input" min="0" step="0.01" oninput="calculateBilling()" value="{{ old('dimension_width', $editingPackage->dimension_width ?? '') }}"><span>x</span>
                                            <input type="number" id="dim-h" name="dimension_height" placeholder="H" class="wms-input" min="0" step="0.01" oninput="calculateBilling()" value="{{ old('dimension_height', $editingPackage->dimension_height ?? '') }}">
                                        </div>
                                        <div class="wms-direct-volume">
                                            <div class="wms-field">
                                                <label for="volume_cuft">Cubic Feet</label>
                                                <input type="number" id="volume_cuft" name="volume_cuft" class="wms-input" min="0" step="0.0001" placeholder="cu ft" oninput="calculateBilling('direct-cuft')" value="{{ old('volume_cuft', $editingPackage->volume_cuft ?? '') }}">
                                            </div>
                                            <div class="wms-field">
                                                <label for="volume_cbm">Cubic Meter</label>
                                                <input type="number" id="volume_cbm" name="volume_cbm" class="wms-input" min="0" step="0.0001" placeholder="CBM" oninput="calculateBilling('direct-cbm')" value="{{ old('volume_cbm', $editingPackage->volume_cbm ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wms-card">
                                    <div class="wms-field">
                                        <label for="pkg-images">Package Images</label>
                                        <input type="file" id="pkg-images" name="package_images[]" multiple accept="image/*" class="wms-input">
                                        <span id="pkg-images-note" class="wms-note">Attach up to 5 images. Selecting new images on edit replaces the existing image set.</span>
                                        @if(count($existingPackageImages))
                                            <div id="existing-image-preview" class="wms-image-grid">
                                                @foreach($existingPackageImages as $index => $imagePath)
                                                    <div class="wms-image-tile" data-existing-path="{{ $imagePath }}">
                                                        <button type="button" class="wms-image-remove remove-existing-image" title="Remove image">&times;</button>
                                                        <a href="{{ asset($imagePath) }}" target="_blank" title="View image {{ $index + 1 }}">
                                                            <img src="{{ asset($imagePath) }}" alt="Package image {{ $index + 1 }}">
                                                            <span>View {{ $index + 1 }}</span>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div id="removed-existing-images"></div>
                                        <div id="selected-image-preview" class="wms-image-grid wms-hidden"></div>
                                    </div>
                                    <div class="wms-field" style="margin-top:16px;">
                                        <label for="unknown_fee">Unknown Fee</label>
                                        <input type="number" id="unknown_fee" name="unknown_fee" class="wms-input" min="0" step="0.01" value="{{ old('unknown_fee', $editingPackage->unknown_fee ?? 0) }}" placeholder="0.00">
                                    </div>
                                    <div class="wms-field" style="margin-top:16px;">
                                        <label for="unknown_fee_reason">Unknown Fee Reason</label>
                                        <input type="text" id="unknown_fee_reason" name="unknown_fee_reason" class="wms-input" value="{{ old('unknown_fee_reason', $editingPackage->unknown_fee_reason ?? '') }}" placeholder="Optional reason">
                                    </div>
                                    <div class="wms-field" style="margin-top:16px;">
                                        <label for="item_description">Description / Content</label>
                                        <select class="wms-select" name="item_description" id="item_description" required>
                                            <option value="">Select Item Description</option>
                                            @foreach(['Clothing','Footwear','Electronics','Toys','Kids Items','Beauty Products','Personal Care Items','Household Items','Kitchen Items','Bags','Accessories','Jewelry','Watches','Sports Equipment','Fitness Equipment','Office Supplies','Automotive Parts','Tools','Home Decor','Small Appliances','Baby Supplies','Documents','Books','Miscellaneous Items','Merchandise'] as $description)
                                                <option value="{{ $description }}" {{ $selectedDescription == $description ? 'selected' : '' }}>{{ $description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="wms-field" style="margin-top:16px;"><label for="internal-notes">Internal Notes</label><textarea id="internal-notes" name="internal_notes" class="wms-textarea" placeholder="Any damage noted?">{{ old('internal_notes', $editingPackage->internal_notes ?? '') }}</textarea></div>
                                </div>
                            </div>
                        </div>

                        <div class="wms-footer">
                            <div class="wms-status" id="system-status"><i class="ph-fill ph-check-circle"></i> System Ready</div>
                            <div class="wms-footer-actions">
                                <a href="{{ url('admin/packeges/logs/0') }}" class="wms-btn">Cancel</a>
                                <button type="submit" id="submitBtn" class="wms-btn wms-btn-primary"><i class="ph ph-floppy-disk"></i><span id="btnText">{{ $isPackageEdit ? 'Update Package' : 'Save Entry' }}</span><span id="btnLoader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Processing...</span></button>
                                <button type="button" id="printSaveBtn" class="wms-btn wms-btn-success" onclick="saveAndPrintPackage()"><i class="ph ph-printer"></i> {{ $isPackageEdit ? 'Print & Update' : 'Print & Save' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
    </div>
</div>

<div class="modal fade wms-scanner" id="trackingScannerModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Scan Barcode/QR</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="text-center mb-2"><i class="fa fa-camera fa-3x text-muted mb-2"></i><p class="small mb-2">Point camera at barcode</p><div id="simple-scanner"></div></div>
                <div class="form-group mb-0">
                    <label class="small">Or type tracking:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" id="quick-tracking-input" placeholder="Enter tracking #">
                        <div class="input-group-append"><button type="button" class="btn btn-primary btn-sm" id="quick-search-btn"><i class="fa fa-search"></i></button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const state = { warehouse: @json(strtoupper((string) $selectedWarehouse) === 'CHINA' ? 'China' : 'USA'), entryMode: 'standard' };
    const isPackageEdit = @json($isPackageEdit);
    const initialPackage = @json($initialPackageJson);
    const originalSupplierTrackingNo = @json($editingPackage->supplier_tracking_no ?? '');
    let activeScanner = null;
    let scannerStream = null;
    let videoElement = null;
    let detectionInterval = null;
    let scannerInputTimer = null;
    let mailboxLookupTimer = null;
    let isSubmitting = false;
    let mailboxDirectory = {};
    let selectedPackageImages = [];

    $(document).ready(function() {
        if (!$('#idempotency_key').val()) {
            $('#idempotency_key').val('pkg-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10));
        }
        setEntryMode(initialPackage.entry_mode || 'standard');
        if (isPackageEdit) {
            initializePackageEdit();
        }
        if ($('#freight_type').val() === 'Sea Freight') {
            $('input[name="shipment_type_choice"][value="sea"]').prop('checked', true);
        }
        toggleShipmentRoute();
        convertWeight();
        bindTrackingValidation();
        bindPackageImagePreview();

        $('#form-validation').on('keydown', 'input, select', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $('#form-validation').on('submit', function(e) {
            if ($(this).data('submitted') === true || isSubmitting) {
                e.preventDefault();
                return false;
            }

            syncWorkflowState();

            if ($.fn.validate && $(this).data('validator') && !$(this).valid()) {
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            if (typeof this.checkValidity === 'function' && !this.checkValidity()) {
                resetSubmitState();
                return true;
            }

            if (!$('#supplier_tracking_no').val().trim()) {
                alert('Please enter tracking number');
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            if (($('#courier_id').val() === '' || $('#courier_id').val() === '0') && $('#routing_status').val() === 'ASSIGNED_UNKNOWN') {
                alert('Courier system must be identified from the mailbox code.');
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            if ($('#routing_status').val() !== 'VERIFIED' && !$('#manual-consignee').val().trim() && !$('#unknown_reason').val()) {
                alert('Please enter the consignee name or select why the name is not readable.');
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            if ($('input[name="shipment_type_choice"]:checked').val() === 'sea') {
                const hasDimensions = parseFloat($('#dim-l').val()) > 0 && parseFloat($('#dim-w').val()) > 0 && parseFloat($('#dim-h').val()) > 0;
                const hasDirectVolume = parseFloat($('#volume_cuft').val()) > 0 || parseFloat($('#volume_cbm').val()) > 0;
                if (!hasDimensions && !hasDirectVolume) {
                    alert('Sea freight requires dimensions or a direct cubic value.');
                    resetSubmitState();
                    e.preventDefault();
                    return false;
                }
            }

            if ($('#supplier_tracking_no').data('validating') === true) {
                alert('Please wait, tracking number validation in progress...');
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            if ($('#supplier_tracking_no').data('invalid') === true) {
                alert('Please enter a valid tracking number');
                resetSubmitState();
                e.preventDefault();
                return false;
            }

            isSubmitting = true;
            $(this).data('submitted', true);
            $('#submitBtn, #printSaveBtn').prop('disabled', true).addClass('disabled');
            $('#btnText').hide();
            $('#btnLoader').show();
            return true;
        });

        $('#form-validation').on('invalid', 'input, select, textarea', function() {
            resetSubmitState();
        });

        setTimeout(configureSmartFormValidator, 0);

        $('#quick-search-btn').on('click', function() {
            const value = $('#quick-tracking-input').val().trim();
            if (value) handleTrackingScan(value);
        });

        $('#quick-tracking-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#quick-search-btn').click();
            }
        });

        $('#trackingScannerModal').on('hidden.bs.modal', cleanupAllScanners);
    });

    function initializePackageEdit() {
        $('#supplier_tracking_no').val(initialPackage.tracking || '').prop('readonly', false);
        $('#pkg-weight').val(initialPackage.weight || '');
        setCourierSystem(initialPackage.courier_id || '0', initialPackage.courier_name || 'Unknown');
        $('#mailbox, #mailbox_search, #mailbox-number').val(initialPackage.mailbox || '');
        $('#customer-name').val(initialPackage.customer_name ? initialPackage.customer_name + (initialPackage.routing_status === 'VERIFIED' ? ' (Verified)' : '') : '');
        $('#courier-name').val(initialPackage.courier_name || ((initialPackage.routing_status === 'GENERAL_UNKNOWN') ? 'General Unknown Storage' : ''));
        $('#manual-consignee').val(initialPackage.consignee || '');
        $('#unknown_reason').val(initialPackage.unknown_reason || '');
        $('#routing_status').val(initialPackage.routing_status || 'GENERAL_UNKNOWN');
        if (initialPackage.carrier) selectCarrierByText(initialPackage.carrier);
        if (initialPackage.package_type && !$('#package_type').val()) {
            $('#package_type').append($('<option>', { value: initialPackage.package_type, text: initialPackage.package_type_label || initialPackage.package_type, selected: true }));
        }

        if (initialPackage.routing_status === 'VERIFIED') {
            $('#manual-consignee-section, #routing-badge').addClass('wms-hidden');
            $('#col-customer, #col-assigned-courier').removeClass('wms-hidden');
            $('#mailbox-hint').text('Mailbox verified. Courier and customer auto-filled.');
        } else if (initialPackage.routing_status === 'ASSIGNED_UNKNOWN') {
            triggerMailboxLogic(initialPackage.mailbox || '');
        } else {
            triggerMailboxLogic('');
        }
    }

    function bindPackageImagePreview() {
        $('#existing-image-preview').on('click', '.remove-existing-image', function() {
            const tile = $(this).closest('.wms-image-tile');
            const path = tile.data('existing-path');
            if (path) {
                $('#removed-existing-images').append($('<input>', { type: 'hidden', name: 'remove_package_images[]', value: path }));
            }
            tile.remove();
            if (!$('#existing-image-preview .wms-image-tile').length) {
                $('#existing-image-preview').addClass('wms-hidden');
            }
        });

        $('#pkg-images').on('change', function() {
            selectedPackageImages = Array.from(this.files || []);
            renderSelectedPackageImages();
        });
    }

    function renderSelectedPackageImages() {
        const input = document.getElementById('pkg-images');
        const preview = $('#selected-image-preview');
        preview.empty().addClass('wms-hidden');

        if (selectedPackageImages.length > 5) {
            alert('You can attach up to 5 package images.');
            selectedPackageImages = selectedPackageImages.slice(0, 5);
        }

        if (!selectedPackageImages.length) {
            if (input) input.value = '';
            return;
        }

        const transfer = new DataTransfer();
        selectedPackageImages.forEach(function(file) {
            transfer.items.add(file);
        });
        if (input) input.files = transfer.files;

        $('#existing-image-preview').addClass('wms-hidden');
        selectedPackageImages.forEach(function(file, index) {
            if (!file.type || file.type.indexOf('image/') !== 0) return;
            const reader = new FileReader();
            reader.onload = function(event) {
                const tile = $('<div>', { class: 'wms-image-tile' });
                const remove = $('<button>', { type: 'button', class: 'wms-image-remove', title: 'Remove image' }).html('&times;');
                remove.on('click', function() {
                    selectedPackageImages.splice(index, 1);
                    renderSelectedPackageImages();
                });
                const link = $('<a>', { href: event.target.result, target: '_blank', title: 'View selected image ' + (index + 1) });
                link.append($('<img>', { src: event.target.result, alt: 'Selected package image ' + (index + 1) }));
                link.append($('<span>').text('New ' + (index + 1)));
                tile.append(remove).append(link);
                preview.append(tile).removeClass('wms-hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    $(window).on('pageshow', function() {
        resetSubmitState();
    });

    function resetSubmitState() {
        isSubmitting = false;
        $('#form-validation').data('submitted', false);
        $('#submitBtn, #printSaveBtn').prop('disabled', false).removeClass('disabled');
        $('#btnText').show();
        $('#btnLoader').hide();
    }

    function configureSmartFormValidator() {
        if (!$.fn.validate) return;
        const form = $('#form-validation');
        const validator = form.data('validator') || form.validate();
        validator.settings.invalidHandler = function() {
            resetSubmitState();
        };
        validator.settings.errorPlacement = function(error, element) {
            error.insertAfter(element.closest('.wms-input-group').length ? element.closest('.wms-input-group') : element);
        };
    }

    function changeWarehouse(warehouse) {
        state.warehouse = warehouse;
        $('#warehouse_code').val(warehouse === 'China' ? 'CHINA' : 'USA');
        setWeightUnit(warehouse === 'China' ? 'kg' : 'lb');
        updateLabelType();
        convertWeight();
        showToast('Warehouse Shifted', 'System shifted to ' + warehouse + ' standards.');
    }

    function updateLabelType() {
        const region = $('#warehouse_code').val() === 'CHINA' ? 'CN' : 'US';
        const method = $('#freight_type').val().toLowerCase().indexOf('sea') !== -1 ? 'Sea' : 'Air';
        const labelType = region + ' ' + method;
        $('#label_type').val(labelType);
        $('#label-type-preview').text(labelType);
    }

    function setWeightUnit(unit) {
        const normalized = unit === 'kg' ? 'kg' : 'lb';
        $('#weight_unit').val(normalized);
        $('#weight-unit-select').val(normalized);
        $('#lbl-weight').text(normalized === 'kg' ? 'Weight (decimal kg) *' : 'Weight (decimal lbs or lb:oz) *');
        $('#pkg-weight').attr('placeholder', normalized === 'kg' ? 'e.g. 5.2' : 'e.g. 4.5 or 2:9.1');
        convertWeight();
    }

    function setEntryMode(mode) {
        state.entryMode = mode;
        $('#entry_mode').val(mode === 'dropoff' ? 'DROPOFF' : (mode === 'unknown' ? 'FORCED_UNKNOWN' : 'STANDARD'));
        $('.wms-mode-button').removeClass('active');
        $('#btn-mode-' + mode).addClass('active');
        resetIdentityFields();

        const tracking = $('#supplier_tracking_no');
        if (mode === 'standard') {
            tracking.prop('readonly', false);
            $('#dropoff-pieces-container').addClass('wms-hidden');
            $('#col-mailbox').removeClass('wms-hidden');
            selectCarrierByText('');
        } else if (mode === 'dropoff') {
            tracking.val('DROP-' + Date.now().toString().slice(-6) + '-99').prop('readonly', true).trigger('input');
            $('#dropoff-pieces-container, #col-intended-courier').removeClass('wms-hidden');
            $('#courier-name').val('Pending selection...');
            selectCarrierByText('Local Drop-off');
        } else {
            tracking.val('GEN-UNK-' + (Math.floor(Math.random() * 9000) + 1000)).prop('readonly', true).trigger('input');
            setCourierSystem('0', 'Unknown');
            $('#mailbox, #mailbox-number').val('');
            $('#courier-name').val('General Unknown Storage');
            selectCarrierByText('Unknown Carrier');
            $('#col-mailbox, #col-customer').addClass('wms-hidden');
            $('#manual-consignee-section').removeClass('wms-hidden');
            $('#manual-consignee-msg').html('<i class="ph-fill ph-warning-circle"></i> Forced General Unknown mode.');
            $('#routing-badge').text('General Unknown Mode Active').removeClass('wms-hidden');
        }
        syncWorkflowState();
    }

    function resetIdentityFields() {
        $('#customer-name, #courier-name, #manual-consignee, #mailbox_search, #mailbox, #mailbox-number, #unknown_reason').val('');
        $('#col-mailbox, #col-customer, #col-assigned-courier').removeClass('wms-hidden');
        $('#manual-consignee-section, #routing-badge, #col-intended-courier').addClass('wms-hidden');
    }

    function syncMailboxDetails() {
        const typed = ($('#mailbox-number').val() || '').trim();
        const raw = mailboxDirectory[typed] || typed;
        $('#mailbox, #mailbox_search').val(raw);
        const parts = raw.split('+');
        const mailbox = (parts[0] || '').toUpperCase();
        const customerName = parts[1] || '';

        if (customerName) {
            lookupMailboxGlobally(mailbox);
            return;
        }

        clearTimeout(mailboxLookupTimer);
        if (mailbox) {
            mailboxLookupTimer = setTimeout(function() {
                lookupMailboxGlobally(mailbox);
            }, 300);
        } else {
            triggerMailboxLogic(mailbox);
        }
    }

    function lookupMailboxGlobally(mailbox) {
        const code = (mailbox || '').trim().toUpperCase();
        if (!code) {
            triggerMailboxLogic('');
            return;
        }

        $('#mailbox-hint').text('Checking mailbox...');
        $.ajax({
            type: 'GET',
            url: APP_URL + '/admin/mailbox/lookup/' + encodeURIComponent(code),
            success: function(res) {
                const current = ($('#mailbox-number').val() || '').trim().toUpperCase();
                if (current && current !== code) return;

                if (res.routing_status === 'VERIFIED') {
                    setCourierSystem(String(res.courier_id || ''), res.courier_name || '');
                    $('#mailbox, #mailbox_search').val(res.mailbox_value || res.mailbox || code);
                    $('#customer-name').val((res.customer_name || 'Customer') + ' (Verified)');
                    $('#courier-name').val(res.courier_name || getCourierSystemName());
                    $('#manual-consignee-section, #routing-badge').addClass('wms-hidden');
                    $('#col-customer, #col-assigned-courier').removeClass('wms-hidden');
                    $('#mailbox-hint').text('Mailbox verified. Courier and customer auto-filled.');
                    syncWorkflowState('VERIFIED');
                    return;
                }

                if (res.routing_status === 'ASSIGNED_UNKNOWN') {
                    setCourierSystem(String(res.courier_id || ''), res.courier_name || '');
                    $('#mailbox, #mailbox_search').val(res.mailbox || code);
                    $('#courier-name').val(res.courier_name || getCourierSystemName());
                    $('#mailbox-hint').text('Courier identified. Customer not found.');
                    triggerMailboxLogic(res.mailbox || code);
                    syncWorkflowState('ASSIGNED_UNKNOWN');
                    return;
                }

                setCourierSystem('0', 'Unknown');
                $('#mailbox, #mailbox_search').val(code);
                selectCarrierByText('Unknown Carrier');
                $('#mailbox-hint').text('Code not recognized.');
                triggerMailboxLogic('');
                syncWorkflowState('GENERAL_UNKNOWN');
            },
            error: function() {
                $('#mailbox-hint').text('Could not verify mailbox. Please try again.');
                triggerMailboxLogic(code);
            }
        });
    }

    function triggerMailboxLogic(val) {
        $('#col-intended-courier, #manual-consignee-section, #routing-badge').addClass('wms-hidden');
        $('#col-customer').removeClass('wms-hidden');

        if (state.entryMode === 'dropoff' && val === '') {
            $('#col-intended-courier').removeClass('wms-hidden');
            $('#courier-name').val('Pending selection...');
            syncWorkflowState();
            return;
        }

        if (val !== '') {
            $('#courier-name').val(getCourierSystemName() || 'General Unknown Storage');
            $('#col-customer').addClass('wms-hidden');
            $('#manual-consignee-section').removeClass('wms-hidden');
            $('#manual-consignee-msg').html('<i class="ph-fill ph-warning-circle"></i> Customer not found. Logging as Assigned Unknown.');
            $('#routing-badge').text('Assigned Unknown Mode Active').removeClass('wms-hidden');
            syncWorkflowState('ASSIGNED_UNKNOWN');
        } else if (state.entryMode === 'standard') {
            $('#courier-name').val('General Unknown Storage');
            $('#col-customer').addClass('wms-hidden');
            $('#manual-consignee-section').removeClass('wms-hidden');
            $('#manual-consignee-msg').html('<i class="ph-fill ph-warning-circle"></i> No Code. Logging as General Unknown.');
            syncWorkflowState('GENERAL_UNKNOWN');
        } else {
            syncWorkflowState();
        }
    }

    function setIntendedCourier() {
        $('#courier-name').val($('#intended-courier-select').val() || 'Pending selection...');
        syncWorkflowState();
    }

    function setCourierSystem(id, name) {
        const value = String(id || '');
        $('#courier_id').val(value);
        if (name) {
            $('#courier-name').val(String(name));
        }
    }

    function getCourierSystemName() {
        return $('#courier-name').val();
    }

    function convertWeight() {
        const input = document.getElementById('pkg-weight');
        const display = document.getElementById('weight-conversion');
        if (!input || !display) return;

        const value = input.value.trim();
        const unit = $('#weight_unit').val() === 'kg' ? 'kg' : 'lb';
        if (!value) {
            display.classList.add('wms-hidden');
            input.dataset.decimalValue = 0;
            calculateBilling();
            return;
        }

        let decimalWeight = 0;
        if (unit === 'kg') {
            decimalWeight = parseFloat(value);
        } else if (value.includes(':')) {
            const parts = value.split(':');
            decimalWeight = (parseFloat(parts[0]) || 0) + ((parseFloat(parts[1]) || 0) / 16);
        } else {
            decimalWeight = parseFloat(value);
        }

        if (isNaN(decimalWeight) || decimalWeight < 0) {
            display.classList.add('wms-hidden');
            input.dataset.decimalValue = 0;
            calculateBilling();
            return;
        }

            decimalWeight = Math.round(decimalWeight * 100) / 100;
        if (unit === 'kg') {
            display.innerText = 'Stored: ' + decimalWeight + ' kg';
        } else {
            const pounds = Math.floor(decimalWeight);
            const ounces = ((decimalWeight - pounds) * 16).toFixed(1).replace(/\.0$/, '');
            display.innerText = 'Stored: ' + decimalWeight + ' lbs (' + pounds + ' lb' + (pounds === 1 ? '' : 's') + ' ' + ounces + ' oz)';
        }

        display.classList.remove('wms-hidden');
        input.dataset.decimalValue = decimalWeight;
        calculateBilling();
    }

    function formatWeightInput() {
        const input = document.getElementById('pkg-weight');
        if (input && input.dataset.decimalValue && parseFloat(input.dataset.decimalValue) > 0) {
            input.value = input.dataset.decimalValue;
        }
    }

    function toggleShipmentRoute() {
        const isAir = $('input[name="shipment_type_choice"]:checked').val() === 'air';
        $('#freight_type').val(isAir ? 'Air Freight' : 'Sea Freight');
        $('#volume-section').toggleClass('wms-hidden', isAir);
        $('#summary-dim-row').addClass('wms-hidden');
        $('#package-weight-field').toggleClass('wms-hidden', !isAir);
        $('#pkg-weight').prop('required', isAir);
        $('#weight-unit-select').prop('disabled', !isAir);
        if (!isAir) {
            $('#pkg-weight').val('');
            $('#weight-conversion').addClass('wms-hidden').text('');
            const weightEl = $('#pkg-weight').get(0);
            if (weightEl) weightEl.dataset.decimalValue = 0;
        }
        $('#lbl-weight').text(isAir ? ($('#weight_unit').val() === 'kg' ? 'Weight (decimal kg) *' : 'Weight (decimal lbs or lb:oz) *') : 'Weight (optional for sea)');
        $('#pkg-weight').attr('placeholder', isAir ? ($('#weight_unit').val() === 'kg' ? 'e.g. 5.2' : 'e.g. 4.5 or 2:9.1') : 'Optional');
        updateLabelType();
        calculateBilling();
    }

    function calculateBilling(source) {
        const l = parseFloat($('#dim-l').val()) || 0;
        const w = parseFloat($('#dim-w').val()) || 0;
        const h = parseFloat($('#dim-h').val()) || 0;
        const weightEl = $('#pkg-weight').get(0);
        const actual = parseFloat(weightEl ? weightEl.dataset.decimalValue : 0) || 0;
        const isAir = $('input[name="shipment_type_choice"]:checked').val() === 'air';
        const unit = $('#dim-unit').val() || 'in';
        const weightSuffix = $('#weight_unit').val() === 'kg' ? 'kg' : 'lbs';
        let lin = l, win = w, hin = h;
        if (unit === 'cm') { lin = l / 2.54; win = w / 2.54; hin = h / 2.54; }
        if (unit === 'ft') { lin = l * 12; win = w * 12; hin = h * 12; }
        if (!isAir) {
            const hasDimensions = l > 0 && w > 0 && h > 0;
            let cuft = hasDimensions ? (lin * win * hin) / 1728 : 0;
            let cbm = cuft > 0 ? cuft / 35.3147 : 0;

            if (source === 'direct-cuft') {
                cuft = parseFloat($('#volume_cuft').val()) || 0;
                cbm = cuft > 0 ? cuft / 35.3147 : 0;
            } else if (source === 'direct-cbm') {
                cbm = parseFloat($('#volume_cbm').val()) || 0;
                cuft = cbm > 0 ? cbm * 35.3147 : 0;
            } else if (!hasDimensions) {
                cuft = parseFloat($('#volume_cuft').val()) || 0;
                cbm = parseFloat($('#volume_cbm').val()) || 0;
                if (cuft <= 0 && cbm > 0) cuft = cbm * 35.3147;
                if (cbm <= 0 && cuft > 0) cbm = cuft / 35.3147;
            }

            if (source !== 'direct-cuft') $('#volume_cuft').val(cuft > 0 ? cuft.toFixed(4) : '');
            if (source !== 'direct-cbm') $('#volume_cbm').val(cbm > 0 ? cbm.toFixed(4) : '');
            const volumeText = (cuft > 0 ? cuft.toFixed(2) : '0.00') + ' cu ft / ' + (cbm > 0 ? cbm.toFixed(3) : '0.000') + ' CBM';
            $('#chargeable_weight').val(cuft > 0 ? cuft.toFixed(4) : '');
            $('#summary-actual-label').text('Sea Volume:');
            $('#summary-total-label').text('Chargeable Cu.ft / CBM:');
            $('#summary-actual').text(volumeText);
            $('#summary-chargeable').text(volumeText);
        } else {
            $('#volume_cuft, #volume_cbm').val('');
            $('#chargeable_weight').val(actual > 0 ? actual.toFixed(2) : '');
            $('#summary-actual-label').text('Actual Weight:');
            $('#summary-total-label').text('Chargeable Weight:');
            $('#summary-actual').text(actual.toFixed(2) + ' ' + weightSuffix);
            $('#summary-chargeable').text(actual.toFixed(2) + ' ' + weightSuffix);
        }
    }

    function bindTrackingValidation() {
        $('#supplier_tracking_no').on('input', function() {
            clearTimeout(scannerInputTimer);
            const cleaned = $(this).val().replace(/[^\x20-\x7E]/g, '');
            $(this).val(cleaned);
            const courierId = $('#courier_id').val();
            autoDetectCarrier(cleaned);
            if (isPackageEdit && cleaned === originalSupplierTrackingNo) {
                $(this).data('invalid', false);
                $('#traking_no-message').html('');
                return;
            }
            if (!cleaned || courierId === '') return;
            scannerInputTimer = setTimeout(function() { validateTrackingNumber(cleaned, courierId); }, 400);
        });

        $('#supplier_tracking_no').on('blur', function() {
            const val = $(this).val().trim();
            const courierId = $('#courier_id').val();
            if (isPackageEdit && val === originalSupplierTrackingNo) return;
            if (val && courierId) validateTrackingNumber(val, courierId);
        });
    }

    function autoDetectCarrier(value) {
        const val = (value || '').toUpperCase();
        if (val.indexOf('1Z') === 0) selectCarrierByText('UPS');
        else if (val.indexOf('TBA') === 0) selectCarrierByText('Amazon Logistics');
        else if (val.length === 12 && /^\d+$/.test(val)) selectCarrierByText('FedEx');
        else if (val.length === 22 && (val.indexOf('90') === 0 || val.indexOf('9334') === 0 || val.indexOf('9544') === 0 || val.indexOf('490') === 0)) selectCarrierByText('USPS');
    }

    function validateTrackingNumber(supplier_tracking_no, courier_id) {
        $('#supplier_tracking_no').data('validating', true);
        $.ajax({
            type: 'GET',
            url: APP_URL + '/admin/package/check/tracking/number/' + encodeURIComponent(supplier_tracking_no) + '/' + courier_id,
            complete: function() { $('#supplier_tracking_no').data('validating', false); },
            success: function(msg) {
                if (msg.status === 'failed') {
                    $('#supplier_tracking_no').val('').data('invalid', true).css({ borderColor: '#ff3c7e', backgroundColor: '#ffeff4' });
                    $('#traking_no-message').html('This tracking number (' + supplier_tracking_no + ') has already been taken. Try another one.');
                } else {
                    $('#supplier_tracking_no').data('invalid', false).css({ borderColor: '#37c936', backgroundColor: '#ebfaeb' });
                    $('#traking_no-message').html('');
                }
            }
        });
    }

    function getCourierUser(id) {
        if (state.entryMode !== 'unknown') $('#courier-name').val(getCourierSystemName());
        $.ajax({
            type: 'GET',
            url: APP_URL + '/admin/courier/users/' + id + '/0',
            processData: false,
            contentType: false,
            success: function(html) {
                const response = $('<div>').html(html);
                const options = response.find('#mailbox option');
                const datalist = $('#mailbox-options');
                datalist.empty();
                mailboxDirectory = {};
                if (options.length) {
                    options.each(function() {
                        const optionValue = $(this).attr('value') || '';
                        if (!optionValue) return;
                        const parts = optionValue.split('+');
                        const mailboxCode = parts[0] || '';
                        const display = $(this).text().trim();
                        mailboxDirectory[mailboxCode] = optionValue;
                        mailboxDirectory[display] = optionValue;
                        datalist.append('<option value="' + mailboxCode + '" label="' + display.replace(/"/g, '&quot;') + '"></option>');
                    });
                }
                syncMailboxDetails();
            }
        });
    }

    function triggerAIFill() {
        $('#ai-btn').html('<i class="ph ph-spinner fa-spin"></i> Process');
        setTimeout(function() {
            $('#capture_method').val('ai_fill');
            $('#supplier_tracking_no').val('9400100000000000000000').trigger('input');
            selectCarrierByText('USPS');
            $('#pkg-weight').val('2:12');
            convertWeight();
            showToast('AI Complete', 'Populated fields from photo.');
            $('#ai-btn').html('<i class="ph ph-magic-wand"></i> AI Fill');
        }, 800);
    }

    function selectCarrierByText(text) {
        if (!text) return;
        const carrier = $('#courier');
        let found = false;
        carrier.find('option').each(function() {
            if ($(this).text().toUpperCase() === text.toUpperCase()) {
                carrier.val($(this).val());
                found = true;
            }
        });
        if (!found) carrier.append('<option value="' + text + '" selected>' + text + '</option>');
    }

    function copyTracking() {
        const value = $('#supplier_tracking_no').val();
        if (value && navigator.clipboard) {
            navigator.clipboard.writeText(value);
            showToast('Copied', 'Tracking number copied.');
        }
    }

    function clearFields() {
        document.getElementById('form-validation').reset();
        $('#mailbox-options').empty();
        mailboxDirectory = {};
        $('#idempotency_key').val('pkg-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10));
        setEntryMode('standard');
        convertWeight();
    }

    function saveAndPrintPackage() {
        $('#print_after_save').val('1');
        $('#form-validation').trigger('submit');
    }

    function syncWorkflowState(forcedStatus) {
        let status = forcedStatus || 'GENERAL_UNKNOWN';
        const mode = state.entryMode;
        const selectedCourier = parseInt($('#courier_id').val() || '0', 10);
        const rawMailbox = ($('#mailbox').val() || $('#mailbox_search').val() || $('#mailbox-number').val() || '').trim();
        const hasCustomer = rawMailbox.indexOf('+') > -1;
        const hasVerifiedCustomer = ($('#customer-name').val() || '').indexOf('(Verified)') > -1;

        if (!forcedStatus) {
            if (mode === 'unknown') status = 'GENERAL_UNKNOWN';
            else if (hasCustomer || hasVerifiedCustomer) status = 'VERIFIED';
            else if ((selectedCourier > 0 && rawMailbox !== '') || (mode === 'dropoff' && (selectedCourier > 0 || $('#intended-courier-select').val()))) status = 'ASSIGNED_UNKNOWN';
        }

        $('#routing_status').val(status);
        if (status === 'VERIFIED') {
            $('#unknown_reason').val('');
        }
    }

    function showToast(title, msg) {
        if (typeof toastr !== 'undefined') toastr.success(msg, title);
    }

    function startTrackingScanner() {
        cleanupAllScanners();
        $('#trackingScannerModal').modal('show');
        setTimeout(function() { startGoogleScanner('simple-scanner'); }, 400);
    }

    function cleanupAllScanners() {
        if (detectionInterval) clearInterval(detectionInterval);
        detectionInterval = null;
        if (scannerStream) {
            scannerStream.getTracks().forEach(track => track.stop());
            scannerStream = null;
        }
        if (activeScanner) {
            try { activeScanner.reset(); } catch (e) {}
            activeScanner = null;
        }
        if (videoElement) {
            videoElement.srcObject = null;
            videoElement = null;
        }
        $('#simple-scanner').empty();
    }

    async function startGoogleScanner(elementId) {
        const container = document.getElementById(elementId);
        if (!container) return;
        container.innerHTML = '';

        try {
            if ('BarcodeDetector' in window) {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } });
                scannerStream = stream;
                videoElement = document.createElement('video');
                videoElement.srcObject = stream;
                videoElement.setAttribute('playsinline', true);
                videoElement.setAttribute('autoplay', true);
                videoElement.muted = true;
                container.appendChild(videoElement);
                addGoogleOverlay(container);
                await new Promise(resolve => { videoElement.onplaying = resolve; });
                const detector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'upc_a'] });
                detectionInterval = setInterval(async function() {
                    if (!videoElement || videoElement.readyState < 2) return;
                    const barcodes = await detector.detect(videoElement);
                    if (barcodes && barcodes.length > 0) handleTrackingScan(barcodes[0].rawValue);
                }, 250);
                return;
            }

            activeScanner = new ZXing.BrowserMultiFormatReader();
            const devices = await activeScanner.listVideoInputDevices();
            if (!devices.length) throw new Error('No cameras found');
            const device = devices.find(c => /back|rear|environment/i.test(c.label)) || devices[0];
            videoElement = document.createElement('video');
            videoElement.setAttribute('playsinline', true);
            container.appendChild(videoElement);
            addGoogleOverlay(container);
            activeScanner.decodeFromVideoDevice(device.deviceId, videoElement, function(result) {
                if (result) handleTrackingScan(result.text);
            });
        } catch (err) {
            container.innerHTML = '<div style="text-align:center; padding:30px; background:white;"><i class="fa fa-camera fa-3x text-muted mb-3"></i><h6>Camera Access Required</h6><p class="small text-muted">' + (err.message || 'Please allow camera access') + '</p><button type="button" class="btn btn-primary btn-sm" onclick="$(\'#quick-tracking-input\').focus()">Manual Entry</button></div>';
        }
    }

    function addGoogleOverlay(container) {
        $('.google-corner, .google-line').remove();
        ['top-left', 'top-right', 'bottom-left', 'bottom-right'].forEach(function(pos) {
            const corner = document.createElement('div');
            corner.className = 'google-corner';
            corner.style.cssText = (pos.includes('top') ? 'top:15px;' : 'bottom:15px;') +
                (pos.includes('left') ? 'left:15px;' : 'right:15px;') +
                (pos === 'top-left' ? 'border-right:0;border-bottom:0;' : '') +
                (pos === 'top-right' ? 'border-left:0;border-bottom:0;' : '') +
                (pos === 'bottom-left' ? 'border-right:0;border-top:0;' : '') +
                (pos === 'bottom-right' ? 'border-left:0;border-top:0;' : '');
            container.appendChild(corner);
        });
        const line = document.createElement('div');
        line.className = 'google-line';
        container.appendChild(line);
    }

    function handleTrackingScan(value) {
        cleanupAllScanners();
        if (navigator.vibrate) navigator.vibrate(50);
        $('#capture_method').val('scanner');
        $('#supplier_tracking_no').val(value).trigger('input').trigger('change');
        $('#trackingScannerModal').modal('hide');
        showToast('Scan Successful', 'Barcode detected via camera.');
        setTimeout(function() { $('#courier').focus(); }, 300);
    }

    window.getCourierUser = getCourierUser;
    window.startTrackingScanner = startTrackingScanner;
    window.setEntryMode = setEntryMode;
    window.changeWarehouse = changeWarehouse;
    window.toggleShipmentRoute = toggleShipmentRoute;
    window.convertWeight = convertWeight;
    window.formatWeightInput = formatWeightInput;
</script>
@endsection
