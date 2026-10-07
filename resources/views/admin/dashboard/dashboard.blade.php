@extends('admin.template')
@section('content')
<div class="main-content portal-dashboard">
    <div class="container-fluid">
        <div class="portal-page-title">
            <div>
                <h1>System Analytics</h1>
                <p>Global overview of processing, inventory, and finances.</p>
            </div>
        </div>

        <div class="row portal-grid-gap">
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat">
                    <div class="portal-stat-label"><i class="ti-home"></i> USA Warehouse</div>
                    <strong>{{number_format($wherehouse)}}</strong>
                    <small>Total Packages Stored</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-red">
                    <div class="portal-stat-label"><i class="ti-home"></i> China Warehouse</div>
                    <strong>{{number_format($china_warehouse)}}</strong>
                    <small>Total Packages Stored</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-purple">
                    <div class="portal-stat-label"><i class="ti-truck"></i> In Transit</div>
                    <strong>{{number_format($in_transit)}}</strong>
                    <small>Global Active Shipments</small>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="portal-card portal-stat is-green">
                    <div class="portal-stat-label"><i class="ti-check-box"></i> Processed Today</div>
                    <strong>{{number_format($today_packages)}}</strong>
                    <small style="color:#059669;">+15% from yesterday</small>
                </div>
            </div>
        </div>

        <div class="row portal-grid-gap" style="margin-top:18px;">
            <div class="col-lg-2 col-sm-6">
                <div class="portal-mini-stat">
                    <strong>{{number_format($dropoffs_today)}}</strong>
                    <span>Drop-offs Today</span>
                </div>
            </div>
            <div class="col-lg-2 col-sm-6">
                <div class="portal-mini-stat warning">
                    <strong>{{number_format($assigned_unknowns)}}</strong>
                    <span>Assigned Unknowns</span>
                </div>
            </div>
            <div class="col-lg-2 col-sm-6">
                <div class="portal-mini-stat danger">
                    <strong>{{number_format($unknown_packages)}}</strong>
                    <span>Gen. Unknowns</span>
                </div>
            </div>
            <div class="col-lg-2 col-sm-6">
                <div class="portal-mini-stat info">
                    <strong>{{number_format($pending_requests)}}</strong>
                    <span>Pending Claims</span>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="portal-mini-stat money">
                    <strong>{{number_format($outstanding_bills, 2)}}</strong>
                    <span>Outstanding Bills</span>
                </div>
            </div>
        </div>

        <div class="portal-toggle-band">
            <div>
                <h3><i class="ti-wand"></i> System Settings &amp; Toggles</h3>
                <p>AI Auto-Fill (Camera/Vision): Allow processors to use AI to extract text from images.</p>
            </div>
            <label class="portal-switch" title="AI Auto-Fill">
                <input type="checkbox" checked>
                <span></span>
            </label>
        </div>

        <div class="row portal-grid-gap" style="margin-top:24px;">
            <div class="col-lg-8">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Processing Analytics & Staff Online</h3>
                        <div class="portal-dashboard-actions">
                            <button class="portal-action-btn" type="button"><i class="ti-filter"></i> Filter</button>
                            <button class="portal-action-btn" type="button" onclick="exportAnalyticsCsv()"><i class="ti-export"></i> Extract</button>
                            <button class="portal-action-btn" type="button" onclick="exportAnalyticsCsv()"><i class="ti-download"></i> Download</button>
                            <button class="portal-action-btn" onclick="window.print()"><i class="ti-printer"></i> Print</button>
                        </div>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
                        <button class="portal-filter-btn active" data-filter="today">Today</button>
                        <button class="portal-filter-btn" data-filter="week">Week</button>
                        <button class="portal-filter-btn" data-filter="month">Month</button>
                        <button class="portal-filter-btn" data-filter="all">All</button>
                    </div>

                    <div class="row" style="margin-bottom:14px;">
                        <div class="col-sm-4">
                            <span class="portal-badge">Processors: <span id="totalProcessors" style="margin-left:5px;">0</span></span>
                        </div>
                        <div class="col-sm-4">
                            <span class="portal-badge success">Packages: <span id="totalPackagesProcessed" style="margin-left:5px;">0</span></span>
                        </div>
                        <div class="col-sm-4">
                            <span class="portal-badge warning">Average: <span id="avgPerProcessor" style="margin-left:5px;">0</span></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Processor</th>
                                    <th class="text-right">Packages</th>
                                    <th class="text-center">% of Total</th>
                                </tr>
                            </thead>
                            <tbody id="analyticsTableBody">
                                <tr>
                                    <td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                        <div style="font-size:13px;color:#64748b;">
                            Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalRecords">0</span>
                        </div>
                        <div>
                            <button id="prevPageBtn" class="portal-filter-btn"><i class="fa fa-chevron-left"></i> Previous</button>
                            <span id="pageInfo" style="font-size:13px;color:#64748b;padding:0 8px;">Page 1 of 1</span>
                            <button id="nextPageBtn" class="portal-filter-btn">Next <i class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="portal-quick-actions">
                        <a href="{{URL::to('admin/packege/add')}}"><i class="ti-plus"></i><span>Log Package</span></a>
                        <a href="{{URL::to('admin/packeges/unknown/0')}}"><i class="ti-help-alt"></i><span>Unknowns</span></a>
                        <a href="{{URL::to('admin/list/manifest/courier')}}"><i class="ti-clipboard"></i><span>Manifest</span></a>
                        <a href="{{URL::to('admin/invoice/add')}}"><i class="ti-receipt"></i><span>Invoice</span></a>
                        <a href="{{URL::to('admin/users')}}"><i class="ti-user"></i><span>Clients</span></a>
                        <a href="{{URL::to('send/email')}}"><i class="ti-email"></i><span>Send Email</span></a>
                    </div>
                </div>

                <div class="portal-card portal-section" style="margin-top:18px;">
                    <div class="portal-section-header">
                        <h3>Live Summary</h3>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <span class="portal-badge">Clients {{number_format($active_clients)}}</span>
                        <span class="portal-badge">Couriers {{number_format($active_couriers)}}</span>
                        <span class="portal-badge warning">Requests {{number_format($pending_requests)}}</span>
                        <span class="portal-badge success">Manifest {{number_format($manifest_ready)}}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row portal-grid-gap" style="margin-top:18px;">
            <div class="col-lg-8">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Recent Packages</h3>
                        <a href="{{URL::to('admin/packeges/logs/0')}}" class="portal-action-btn">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>HAWB</th>
                                    <th>Tracking</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_packages as $package)
                                    <tr>
                                        <td><strong>{{$package->manifest_no ?: '-'}}</strong></td>
                                        <td>{{$package->supplier_tracking_no ?: $package->tracking_no}}</td>
                                        <td>{{$package->user_name ?: 'Unknown'}}<br><small>{{$package->mailbox}}</small></td>
                                        <td><span class="portal-badge">{{$package->status_name ?: 'No status'}}</span></td>
                                        <td>{{date('M d, Y', strtotime($package->created_at))}}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No packages found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Status Mix</h3>
                    </div>
                    @forelse($status_breakdown as $status)
                        @php $percent = $total_packages > 0 ? round(($status->total / $total_packages) * 100) : 0; @endphp
                        <div style="margin-bottom:13px;">
                            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:13px;">
                                <span>{{$status->status_name ?: 'No status'}}</span>
                                <span>{{$percent}}%</span>
                            </div>
                            <div style="height:7px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:6px;">
                                <div style="height:100%;width:{{$percent}}%;background:#4f46e5;"></div>
                            </div>
                        </div>
                    @empty
                        <p style="color:#64748b;">No status data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row portal-grid-gap" style="margin-top:18px;">
            <div class="col-lg-12">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Unknown Package Queue</h3>
                        <a href="{{URL::to('admin/packeges/unknown/0')}}" class="portal-action-btn">Manage Unknowns</a>
                    </div>
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>HAWB</th>
                                    <th>Tracking</th>
                                    <th>Carrier</th>
                                    <th>Weight / Volume</th>
                                    <th>Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_unknowns as $package)
                                    <tr>
                                        <td><strong>{{$package->manifest_no ?: '-'}}</strong></td>
                                        <td>{{$package->supplier_tracking_no}}</td>
                                        <td>{{$package->courier}}</td>
                                        <td>
                                            @if(stripos((string) ($package->freight_type ?? ''), 'sea') !== false)
                                                @php($cuft = (float) (($package->volume_cuft ?? 0) ?: ($package->chargeable_weight ?? 0)))
                                                @php($cbm = (float) (($package->volume_cbm ?? 0) ?: ($cuft > 0 ? $cuft / 35.3147 : 0)))
                                                {{ number_format($cuft, 2) }} cu.ft / {{ number_format($cbm, 3) }} CBM
                                            @else
                                                {{ number_format((float) $package->weight, 2) }} lb
                                            @endif
                                        </td>
                                        <td>{{\Carbon\Carbon::parse($package->created_at)->diffForHumans()}}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No unknown packages in the queue.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-bar-mini {
    height: 6px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 6px;
}
.progress-fill {
    height: 100%;
    background: #4f46e5;
    border-radius: 999px;
}
#prevPageBtn:disabled,
#nextPageBtn:disabled {
    opacity: .5;
    cursor: not-allowed;
}
</style>

<script>
$(document).ready(function() {
    let currentFilter = 'today';
    let currentPage = 1;
    let totalPages = 1;
    let allData = [];
    const rowsPerPage = 5;

    function fetchAnalytics(filter) {
        $('#analyticsTableBody').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading data...</td></tr>');

        $.ajax({
            url: '{{ url("admin/processing-analytics") }}',
            type: 'GET',
            data: { filter: filter },
            dataType: 'json',
            success: function(response) {
                if(response.success && response.data && response.data.length) {
                    allData = response.data;
                    $('#totalProcessors').text(response.total_processors || response.data.length);
                    $('#totalPackagesProcessed').text(formatNumber(response.total_packages || 0));
                    let avg = response.data.length > 0 ? Math.round((response.total_packages || 0) / response.data.length) : 0;
                    $('#avgPerProcessor').text(formatNumber(avg));
                    currentPage = 1;
                    totalPages = Math.ceil(allData.length / rowsPerPage);
                    renderTable();
                    renderPagination();
                } else {
                    allData = [];
                    $('#analyticsTableBody').html('<tr><td colspan="4" class="text-center">No data available for this period.</td></tr>');
                    $('#totalProcessors,#totalPackagesProcessed,#avgPerProcessor,#totalRecords,#showingStart,#showingEnd').text('0');
                    renderPagination();
                }
            },
            error: function() {
                $('#analyticsTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading data. Please try again.</td></tr>');
            }
        });
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function escapeHtml(str) {
        if(!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;'}[m];
        });
    }

    function renderTable() {
        const tbody = $('#analyticsTableBody');
        tbody.empty();

        if(allData.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">No data available for this period.</td></tr>');
            return;
        }

        const start = (currentPage - 1) * rowsPerPage;
        const end = Math.min(start + rowsPerPage, allData.length);
        const pageData = allData.slice(start, end);
        const totalPackages = allData.reduce((sum, item) => sum + item.packages, 0);

        $('#totalRecords').text(allData.length);
        $('#showingStart').text(start + 1);
        $('#showingEnd').text(end);

        pageData.forEach((item, index) => {
            const percentage = totalPackages > 0 ? ((item.packages / totalPackages) * 100).toFixed(1) : 0;
            tbody.append(`
                <tr>
                    <td>${start + index + 1}</td>
                    <td><strong>${escapeHtml(item.processor)}</strong>${item.email ? `<br><small>${escapeHtml(item.email)}</small>` : ''}</td>
                    <td class="text-right"><strong>${formatNumber(item.packages)}</strong><div class="progress-bar-mini"><div class="progress-fill" style="width:${percentage}%;"></div></div></td>
                    <td class="text-center"><span class="portal-badge">${percentage}%</span></td>
                </tr>
            `);
        });
    }

    function renderPagination() {
        $('#pageInfo').text(`Page ${currentPage} of ${totalPages || 1}`);
        $('#prevPageBtn').prop('disabled', currentPage <= 1);
        $('#nextPageBtn').prop('disabled', currentPage >= totalPages);
    }

    window.exportAnalyticsCsv = function() {
        const rows = [['Processor', 'Email', 'Packages', 'Type']];
        allData.forEach(item => {
            rows.push([item.processor || '', item.email || '', item.packages || 0, item.type || '']);
        });

        const csv = rows.map(row => row.map(value => {
            const text = String(value).replace(/"/g, '""');
            return `"${text}"`;
        }).join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `processing-analytics-${currentFilter}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    $('.portal-filter-btn[data-filter]').click(function() {
        $('.portal-filter-btn[data-filter]').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        fetchAnalytics(currentFilter);
    });

    $('#prevPageBtn').click(function() {
        if(currentPage > 1) {
            currentPage--;
            renderTable();
            renderPagination();
        }
    });

    $('#nextPageBtn').click(function() {
        if(currentPage < totalPages) {
            currentPage++;
            renderTable();
            renderPagination();
        }
    });

    fetchAnalytics('today');
});
</script>
@endsection
