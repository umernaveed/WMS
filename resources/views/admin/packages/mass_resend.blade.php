@extends('admin.template')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                    <div class="card-tools">
                        <a href="{{ url('admin/packeges/logs/0') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Packages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Test Button -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success" id="testButton">
                                <i class="fa fa-check"></i> Test JavaScript
                            </button>
                            <div id="testResult" class="mt-2"></div>
                        </div>
                    </div>
                    
                    @if(session('success_msg'))
                        <div class="alert alert-success">{{ session('success_msg') }}</div>
                    @endif
                    
                    @if(session('error_msg'))
                        <div class="alert alert-danger">{{ session('error_msg') }}</div>
                    @endif
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Total Packages with Status = 3: {{ $total_count }}</strong>
                                <p class="mb-0 mt-2">
                                    <i class="fa fa-info-circle"></i> 
                                    Click on a courier button to resend all packages with status = 3 for that courier. 
                                    The system will check if packages exist in the courier's database and create them if they don't.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                   
                    
                    <div class="row" id="courierContainer">
                        @forelse($couriers as $courier)
                            @if($courier->package_count > 0)
                            <div class="col-md-4 mb-3">
                                <div class="card courier-card" data-courier-id="{{ $courier->id }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                @if($courier->logo)
                                                    <img src="{{ asset('public/courier_logos/'.$courier->logo) }}" 
                                                         alt="{{ $courier->name }}" style="max-width: 60px; max-height: 60px;">
                                                @else
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px; font-size: 24px;">
                                                        {{ strtoupper(substr($courier->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $courier->name }}</h5>
                                                <p class="mb-1 text-muted">
                                                    <span class="badge badge-primary package-count">{{ $courier->package_count }}</span> packages pending
                                                </p>
                                                <p class="mb-0">
                                                    <small class="text-muted">
                                                        @if($courier->is_courier == 'Yes')
                                                            <i class="fa fa-check-circle text-success"></i> API Connected
                                                        @else
                                                            <i class="fa fa-exclamation-circle text-warning"></i> No API
                                                        @endif
                                                    </small>
                                                </p>
                                            </div>
                                            <div>
                                                <button type="button" 
                                                        class="btn btn-primary resend-courier-btn" 
                                                        data-courier-id="{{ $courier->id }}"
                                                        data-courier-name="{{ $courier->name }}"
                                                        data-package-count="{{ $courier->package_count }}">
                                                    <i class="fa fa-refresh"></i> Resend
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    No couriers with status=3 packages found.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalTitle">Processing...</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progressBar" 
                         role="progressbar" 
                         style="width: 0%"
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">0%</div>
                </div>
                
                <div id="progressMessage" class="mb-3">
                    <p>Initializing...</p>
                </div>
                
                <div class="results-container" style="display: none;">
                    <h6>Results:</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Total Packages</th>
                            <td id="resultTotal">0</td>
                        </tr>
                        <tr>
                            <th>Successfully Sent</th>
                            <td id="resultSuccess" class="text-success">0</td>
                        </tr>
                        <tr>
                            <th>Already Exist</th>
                            <td id="resultCreated" class="text-info">0</td>
                        </tr>
                        <tr>
                            <th>Failed</th>
                            <td id="resultFailed" class="text-danger">0</td>
                        </tr>
                    </table>
                    
                    <div id="errorDetails" style="display: none;">
                        <h6>Error Details:</h6>
                        <pre id="errorList" class="bg-light p-2" style="max-height: 200px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn" disabled>Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.courier-card {
    transition: all 0.3s;
    border: 1px solid #dee2e6;
}
.courier-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

@endsection

@section('scripts')
<script>
// Make sure jQuery is loaded before running this
$(document).ready(function() {
    console.log('Document ready - Mass Resend page loaded');
    console.log('jQuery version:', $.fn.jquery);
    
    // Test button
    $('#testButton').click(function() {
        console.log('Test button clicked');
        $('#testResult').html('<div class="alert alert-success">JavaScript is working! jQuery version: ' + $.fn.jquery + '</div>');
        alert('Test button clicked successfully!');
    });
    
    let currentModal = $('#progressModal');
    
    // Individual courier resend
    $('.resend-courier-btn').click(function() {
        console.log('Resend button clicked');
        
        let btn = $(this);
        let courierId = btn.data('courier-id');
        let courierName = btn.data('courier-name');
        let packageCount = btn.data('package-count');
        
        console.log('Courier ID:', courierId);
        console.log('Courier Name:', courierName);
        console.log('Package Count:', packageCount);
        
        if (confirm(`Are you sure you want to resend ${packageCount} packages for ${courierName}?`)) {
            processMassResend(courierId, courierName);
        }
    });
    
    // Resend all
    $('#resendAllBtn').click(function() {
        console.log('Resend All button clicked');
        
        let totalPackages = {{ $total_count }};
        if (confirm(`Are you sure you want to resend all ${totalPackages} packages for all couriers? This may take a while.`)) {
            processMassResend('all', 'All Couriers');
        }
    });
    
    function processMassResend(courierId, courierName) {
        console.log('Processing mass resend for:', courierName);
        
        // Reset and show modal
        $('#progressModalTitle').text(`Resending Packages - ${courierName}`);
        $('#progressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
        $('#progressMessage').html('<p>Processing packages...</p>');
        $('.results-container').hide();
        $('#errorDetails').hide();
        $('#closeModalBtn').prop('disabled', true);
        currentModal.modal('show');
        
        let url = courierId === 'all' 
            ? '{{ url("admin/package/mass-resend/all") }}'
            : '{{ url("admin/package/mass-resend") }}/' + courierId;
        
        console.log('AJAX URL:', url);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            beforeSend: function() {
                console.log('Sending AJAX request...');
                $('#progressMessage').html('<p>Sending request...</p>');
            },
            success: function(response) {
                console.log('AJAX Success:', response);
                
                $('#progressBar').css('width', '100%').attr('aria-valuenow', 100).text('100%');
                
                if (response.success) {
                    $('#progressMessage').html('<p class="text-success">' + response.message + '</p>');
                    
                    // Display results
                    $('.results-container').show();
                    
                    if (response.results) {
                        $('#resultTotal').text(response.results.total || response.results.total_packages || 0);
                        $('#resultSuccess').text(response.results.success || response.results.total_success || 0);
                        $('#resultCreated').text(response.results.created || response.results.total_created || 0);
                        $('#resultFailed').text(response.results.failed || response.results.total_failed || 0);
                        
                        // Show errors if any
                        if (response.results.errors && response.results.errors.length > 0) {
                            $('#errorDetails').show();
                            let errorHtml = '';
                            response.results.errors.forEach(function(error) {
                                errorHtml += error + '\n';
                            });
                            $('#errorList').text(errorHtml);
                        }
                        
                        // Show courier-specific results for "all"
                        if (response.results.courier_results) {
                            let courierHtml = '<h6 class="mt-3">Breakdown by Courier:</h6><ul>';
                            $.each(response.results.courier_results, function(courier, data) {
                                courierHtml += `<li><strong>${courier}</strong>: Total: ${data.total}, Success: ${data.success}, Already: ${data.created}, Failed: ${data.failed}</li>`;
                            });
                            courierHtml += '</ul>';
                            $('#progressMessage').append(courierHtml);
                        }
                    }
                } else {
                    $('#progressMessage').html('<p class="text-danger">Error: ' + response.message + '</p>');
                }
                
                $('#closeModalBtn').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('XHR Response:', xhr.responseText);
                
                $('#progressBar').css('width', '0%').attr('aria-valuenow', 0).text('Error');
                
                let errorMessage = 'An error occurred: ' + error;
                try {
                    let response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch(e) {
                    console.log('Could not parse error response');
                }
                
                $('#progressMessage').html('<p class="text-danger">Error: ' + errorMessage + '</p>');
                $('#closeModalBtn').prop('disabled', false);
            }
        });
    }
    
    $('#closeModalBtn').click(function() {
        console.log('Close modal clicked');
        currentModal.modal('hide');
        location.reload(); // Refresh to show updated counts
    });
});
</script>
@endsection