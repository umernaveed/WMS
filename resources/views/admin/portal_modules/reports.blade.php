@extends('admin.template')
@section('content')
<div class="main-content portal-dashboard">
    <div class="container-fluid">
        <div class="portal-page-title">
            <div>
                <h1>Reports</h1>
                <p>Operational and financial summary across all configured admin portal modules.</p>
            </div>
            <button class="portal-action-btn" onclick="window.print()"><i class="ti-printer"></i> Print</button>
        </div>

        <div class="row portal-grid-gap">
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat">
                    <div class="portal-stat-label"><i class="ti-package"></i> Total Packages</div>
                    <strong>{{number_format($packageCount)}}</strong>
                    <small>All package records</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-red">
                    <div class="portal-stat-label"><i class="ti-help-alt"></i> Unknown Packages</div>
                    <strong>{{number_format($unknownCount)}}</strong>
                    <small>Needs review</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-purple">
                    <div class="portal-stat-label"><i class="ti-clipboard"></i> Manifests</div>
                    <strong>{{number_format($manifestCount)}}</strong>
                    <small>Generated manifests</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-green">
                    <div class="portal-stat-label"><i class="ti-headphone-alt"></i> Open Support</div>
                    <strong>{{number_format($supportOpen)}}</strong>
                    <small>Active tickets</small>
                </div>
            </div>
        </div>

        <div class="row portal-grid-gap" style="margin-top:36px;">
            <div class="col-md-3">
                <div class="portal-mini-stat money">
                    <strong>{{number_format($income + $invoicePaid, 2)}}</strong>
                    <span>Total Income</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="portal-mini-stat danger">
                    <strong>{{number_format($expenses, 2)}}</strong>
                    <span>Total Expense</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="portal-mini-stat info">
                    <strong>{{number_format($profit, 2)}}</strong>
                    <span>Profit / Loss</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="portal-mini-stat warning">
                    <strong>{{number_format($invoiceOutstanding, 2)}}</strong>
                    <span>Balance Sheet</span>
                </div>
            </div>
        </div>

        <div class="portal-card portal-section" style="margin-top:36px;">
            <div class="portal-section-header">
                <h3>Report Links</h3>
            </div>
            <div class="portal-quick-actions">
                <a href="{{URL::to('admin/portal/finance-entries')}}"><i class="ti-money"></i><span>Income / Expense</span></a>
                <a href="{{URL::to('admin/invoices?start_date=&end_date=')}}"><i class="ti-receipt"></i><span>Courier Billing</span></a>
                <a href="{{URL::to('admin/packeges/logs/0')}}"><i class="ti-package"></i><span>Package Report</span></a>
                <a href="{{URL::to('admin/package/manifest/0')}}"><i class="ti-clipboard"></i><span>Manifest Report</span></a>
            </div>
        </div>
    </div>
</div>
@endsection
