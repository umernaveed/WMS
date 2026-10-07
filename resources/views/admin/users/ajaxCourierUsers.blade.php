<style>
    .modal-dialog {
        max-width: 1000px;
    }
    /* Formatting search box */
    
    .search-box input[type="text"] {
        padding: 6px 12px;
        border: 1px solid #e2e2e4;
        font-size: 14px;
        border-radius: 4px;
    }
    .result {
        position: absolute;
        z-index: 999;
        left: 15px;
        max-height: 290px;
        overflow: hidden;
        overflow-y: scroll;
        background: #fff;
        display:none;
    }
    .search-box input[type="text"], .result{
        /* width: 350px; */
        box-sizing: border-box;
    }
    /* Formatting result items */
    .result p{
        margin: 0;
        padding: 7px 10px;
        border: 1px solid #CCCCCC;
        border-top: none;
        cursor: pointer;
        width: 350px;
    }
    .result p:hover{
        background: #f2f2f2;
    }
    
    /* Scanner button styles */
    .input-group-append .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .input-group .form-control:not(:first-child):not(:last-child) {
        border-radius: 4px 0 0 4px;
    }
    
    /* Scanner container */
    #scanner-container {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        background: #000;
        min-height: 300px;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
    }
    
    /* Video element - FIXED ORIENTATION */
    #scanner-video {
        width: 100%;
        height: auto;
        min-height: 300px;
        object-fit: cover;
        /* Remove transform: scaleX(-1) - this was causing mirroring */
        transform: none;
        /* Ensure video fills container correctly */
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        min-width: 100%;
        min-height: 100%;
    }
    
    /* Fix for iOS orientation issues */
    @supports (-webkit-touch-callout: none) {
        #scanner-video {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
    }
    
    /* Google-style scanner overlay */
    .google-corner {
        position: absolute;
        width: 40px;
        height: 40px;
        border: 3px solid #4CAF50;
        z-index: 1000;
    }
    
    .google-line {
        position: absolute;
        left: 20px;
        right: 20px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #4CAF50, #4CAF50, transparent);
        animation: googleScan 2s ease-in-out infinite;
        z-index: 1000;
    }
    
    @keyframes googleScan {
        0% { top: 20px; opacity: 0; }
        30% { opacity: 1; }
        70% { opacity: 1; }
        100% { top: calc(100% - 20px); opacity: 0; }
    }
    
    @keyframes fadeOut {
        0% { opacity: 0; transform: translateX(-50%) scale(0.8); }
        20% { opacity: 1; transform: translateX(-50%) scale(1); }
        80% { opacity: 1; transform: translateX(-50%) scale(1); }
        100% { opacity: 0; transform: translateX(-50%) scale(0.8); }
    }
    
    #scanner-message {
        font-size: 14px;
        min-height: 30px;
    }
    
    .btn-group {
        gap: 5px;
    }
    
    #scanner-error ul {
        text-align: left;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    /* Container for video to maintain aspect ratio */
    .video-wrapper {
        position: relative;
        width: 100%;
        padding-top: 75%; /* 4:3 Aspect Ratio */
        overflow: hidden;
        background: #000;
        border-radius: 8px;
    }
    
    .video-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<!-- Add required libraries -->
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
<!-- Toastr for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel">Scan Barcode or QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div id="scanner-container">
                            <div class="video-wrapper">
                                <video id="scanner-video" playsinline autoplay></video>
                            </div>
                            <canvas id="scanner-canvas" style="display: none;"></canvas>
                        </div>
                        <div id="scanner-message" class="mt-3" style="color: #666;"></div>
                        <div id="scanner-error" class="mt-3 alert alert-danger" style="display: none;"></div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" onclick="switchCamera()">
                                <i class="fa fa-refresh"></i> Switch Camera
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="captureImage()">
                                <i class="fa fa-camera"></i> Capture
                            </button>
                            <button type="button" class="btn btn-info" onclick="tryManualEntry()">
                                <i class="fa fa-pencil"></i> Manual Entry
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <script src="{{asset('admins/assets/vendors/select2/select2.js')}}"></script>
    <script src="{{asset('admins/assets/js/select-init.js')}}"></script>
    <div class="col-md-6">
        <div class="form-group ">
            <label for="exampleInputEmail1">Account no.</label>
            <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">
                <select id="mailbox" class="form-control js-account-dropdown" style="width:333px;" name="mailbox" @if($courier_id != 0) required @endif>
                    <option value="">Select A Account no.</option>
                    @if($users)
                        @foreach($users as $row)
                            @php
                                $customerName = trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: ($row->user_name ?? '');
                                $customerValue = ($row->mailbox ?? '').'+'.$customerName.'+'.($row->email ?? '');
                                $selectedMailbox = $package->mailbox ?? '';
                            @endphp
                            <option value="{{ $customerValue }}" @if($selectedMailbox == ($row->mailbox ?? '')) selected @endif>{{ ($customerName ?: 'Customer').' Account no: '.($row->mailbox ?? '') }}</option>
                        @endforeach
                    @endif
                </select>
                @if($courier_id != 0 && $users && count($users) > 0)
                    <small class="text-muted d-block mt-2">{{ count($users) }} local customer(s) loaded.</small>
                @endif
                @if($courier_id != 0 && $users && count($users) === 0)
                    <small class="text-danger d-block mt-2">
                        No local customers found for this courier.
                        @if(!empty($customer_courier_ids))
                            Checked local courier ID(s): {{ implode(', ', $customer_courier_ids) }}.
                        @endif
                    </small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>Tracking Number</label>
            <div class="input-group">
                <input type="text" class="form-control supplier_tracking_no" name="supplier_tracking_no" id="supplier_tracking_no" value="{{$package->supplier_tracking_no}}" placeholder="Carrier tracking number" required="">
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary" onclick="startScanner()">
                        <i class="fa fa-camera"></i> Scan
                    </button>
                </div>
            </div>
            <label id="traking_no-message" class="error"></label>
        </div>
    </div>
    <div class="col-md-12">
         <div class="form-group">
                                                    <label class="text-normal text-dark">Carrier</label>
                                                    <select name="courier" id="courier" class="form-control" required>
                                                        <option value="">Select Carrier</option>
                                                
                                                        @foreach(($carriers ?? []) as $carrier)
                                                            <option value="{{ $carrier->name }}" 
                                                                {{ old('courier') == $carrier->name ? 'selected' : '' }}>
                                                                {{ $carrier->name }}
                                                            </option>
                                                        @endforeach
                                                
                                                    </select>
                                                </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="text-normal text-dark">Freight Type</label>
            <select name="freight_type" class="form-control" style="margin-bottom: 15px;" required>
                <option value="Air Freight" @if($package->freight_type == 'Air Freight') selected @endif>Air Freight</option>
                <option value="Sea Freight" @if($package->freight_type == 'Sea Freight') selected @endif>Sea Freight</option>
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="text-normal text-dark">Weight</label>
            <input type="text" name="weight" class="form-control" value="{{$package->weight}}" placeholder="Weight" required="">
        </div>
    </div>
    {{--
    <div class="col-md-12">
        <div class="form-group">
            <label class="text-normal text-dark">Select A Package Category</label>
            <select name="package_type" class="form-control" required="">
                <option value="">Select A Package Category</option>
                @if($types)
                    @foreach($types as $row)
                        <option value="{{($row->code ?? '').'-'.$row->type}}" <?php if($package->package_code == ($row->code ?? '')){ ?> selected="" <?php } ?>>{{$row->type}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    --}}
<!-- Scanner JavaScript - FIXED ORIENTATION -->
<script>
    // Global scanner variables
    let codeReader = null;
    let selectedDeviceId = null;
    let videoElement = null;
    let isScanning = false;
    let availableCameras = [];
    let scannerInitialized = false;
    let currentFacingMode = 'environment'; // Default to back camera

    // Scanner input buffer for physical scanner
    let scannerInput = '';
    let scannerTimer = null;

    $(document).ready(function() {
        console.log('Document ready');
        
        // Initialize scanner when modal is shown
        $('#scannerModal').on('shown.bs.modal', function() {
            console.log('Scanner modal shown');
            setTimeout(function() {
                initializeScanner();
            }, 500);
        });

        // Stop scanner when modal is hidden
        $('#scannerModal').on('hidden.bs.modal', function() {
            console.log('Scanner modal hidden');
            stopScanner();
        });

        // Physical scanner support
        $(document).on('keypress', function(e) {
            if (!$('#scannerModal').is(':visible')) {
                return;
            }
            
            if (scannerTimer) {
                clearTimeout(scannerTimer);
            }
            
            if (e.which === 13) { // Enter key
                e.preventDefault();
                
                if (scannerInput.length > 0) {
                    console.log('Physical scanner detected:', scannerInput);
                    if (navigator.vibrate) navigator.vibrate(50);
                    handleScanResult(scannerInput);
                    scannerInput = '';
                }
            } else {
                scannerInput += String.fromCharCode(e.which);
                
                scannerTimer = setTimeout(function() {
                    if (scannerInput.length > 0) {
                        console.log('Scanner input timed out');
                        scannerInput = '';
                    }
                }, 200);
            }
        });

        $(document).on('keydown', function(e) {
            if (e.which === 13 && $('#scannerModal').is(':visible')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Start scanner
    function startScanner() {
        console.log('Start scanner clicked');
        $('#scannerModal').modal('show');
    }

    // Initialize scanner with proper orientation
    async function initializeScanner() {
        try {
            $('#scanner-error').hide();
            $('#scanner-message').html('<i class="fa fa-spinner fa-spin"></i> Requesting camera permission...');
            
            // Check camera permission with specific constraints for better orientation
            try {
                const constraints = {
                    video: {
                        facingMode: currentFacingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        aspectRatio: { ideal: 1.7777777778 }
                    }
                };
                
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                stream.getTracks().forEach(track => track.stop());
                console.log('Camera permission granted');
            } catch (permError) {
                console.error('Camera permission denied:', permError);
                throw new Error('Camera access denied. Please allow camera access and try again.');
            }
            
            $('#scanner-message').html('<i class="fa fa-spinner fa-spin"></i> Initializing camera...');
            
            // Create code reader
            codeReader = new ZXing.BrowserMultiFormatReader();
            
            // Get cameras
            availableCameras = await codeReader.listVideoInputDevices();
            
            if (availableCameras.length === 0) {
                throw new Error('No cameras found');
            }
            
            // Log available cameras
            availableCameras.forEach((cam, i) => {
                console.log(`Camera ${i}:`, cam.label);
            });
            
            // Select camera based on facing mode
            if (availableCameras.length > 1) {
                // Try to find camera matching desired facing mode
                const selectedCamera = availableCameras.find(camera => 
                    (currentFacingMode === 'environment' && 
                        (camera.label.toLowerCase().includes('back') || 
                         camera.label.toLowerCase().includes('rear') ||
                         camera.label.toLowerCase().includes('environment'))) ||
                    (currentFacingMode === 'user' && 
                        (camera.label.toLowerCase().includes('front') || 
                         camera.label.toLowerCase().includes('user')))
                );
                
                selectedDeviceId = selectedCamera ? selectedCamera.deviceId : availableCameras[0].deviceId;
            } else {
                selectedDeviceId = availableCameras[0].deviceId;
            }
            
            console.log('Selected camera ID:', selectedDeviceId);
            
            // Start decoding
            startDecoding();
            
        } catch (error) {
            console.error('Scanner error:', error);
            showScannerError(error.message);
        }
    }

    // Start decoding with proper video settings
    function startDecoding() {
        if (!codeReader || !selectedDeviceId) return;
        
        isScanning = true;
        videoElement = document.getElementById('scanner-video');
        
        if (!videoElement) {
            showScannerError('Video element not found');
            return;
        }
        
        // Reset video element attributes
        videoElement.setAttribute('playsinline', true);
        videoElement.setAttribute('autoplay', true);
        videoElement.setAttribute('muted', true);
        
        $('#scanner-message').html('<i class="fa fa-camera"></i> Scanning... Position barcode/QR code in the frame');
        
        // Add Google-style overlay
        addScannerOverlay();
        
        const loadingTimeout = setTimeout(() => {
            if (isScanning) {
                $('#scanner-message').html('Camera taking too long. <button class="btn btn-link" onclick="retryScanner()">Retry</button>');
            }
        }, 10000);
        
        // Configure decoding options
        const hints = new Map();
        hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
            ZXing.BarcodeFormat.QR_CODE,
            ZXing.BarcodeFormat.CODE_128,
            ZXing.BarcodeFormat.CODE_39,
            ZXing.BarcodeFormat.EAN_13,
            ZXing.BarcodeFormat.EAN_8,
            ZXing.BarcodeFormat.UPC_A,
            ZXing.BarcodeFormat.UPC_E,
            ZXing.BarcodeFormat.DATA_MATRIX,
            ZXing.BarcodeFormat.PDF_417,
            ZXing.BarcodeFormat.CODABAR,
            ZXing.BarcodeFormat.ITF
        ]);
        
        codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, error) => {
            clearTimeout(loadingTimeout);
            
            if (result) {
                console.log('Scan result:', result.text);
                // Vibrate on successful scan
                if (navigator.vibrate) navigator.vibrate(200);
                handleScanResult(result.text);
            }
            
            // Only log real errors, not normal "not found" messages
            if (error && error.message && !error.message.includes('NotFoundException')) {
                console.debug('Scan error:', error);
            }
        }, hints);
    }

    // Add Google-style overlay
    function addScannerOverlay() {
        $('.google-corner, .google-line').remove();
        
        const container = document.getElementById('scanner-container');
        
        const corners = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        corners.forEach(pos => {
            const corner = document.createElement('div');
            corner.className = 'google-corner';
            corner.style.cssText = `
                position: absolute;
                width: 40px;
                height: 40px;
                border: 3px solid #4CAF50;
                ${pos.includes('top') ? 'top: 15px;' : 'bottom: 15px;'}
                ${pos.includes('left') ? 'left: 15px;' : 'right: 15px;'}
                ${pos.includes('top') && pos.includes('left') ? 'border-right: none; border-bottom: none;' : ''}
                ${pos.includes('top') && pos.includes('right') ? 'border-left: none; border-bottom: none;' : ''}
                ${pos.includes('bottom') && pos.includes('left') ? 'border-right: none; border-top: none;' : ''}
                ${pos.includes('bottom') && pos.includes('right') ? 'border-left: none; border-top: none;' : ''}
                z-index: 1000;
                pointer-events: none;
            `;
            container.appendChild(corner);
        });
        
        const line = document.createElement('div');
        line.className = 'google-line';
        line.style.cssText = `
            position: absolute;
            left: 20px;
            right: 20px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4CAF50, #4CAF50, transparent);
            animation: googleScan 2s ease-in-out infinite;
            z-index: 1000;
            pointer-events: none;
        `;
        container.appendChild(line);
    }

    // Handle scan result
    function handleScanResult(text) {
        if (!isScanning) return;
        
        console.log('Scanned:', text);
        
        // Show success
        $('#scanner-message').html('✅ Success! Scanned: ' + text);
        
        // Fill tracking number
        $('#supplier_tracking_no').val(text).trigger('input').trigger('change');
        
        // Validate if courier is selected
        setTimeout(function() {
            var courier_id = $('#courier_id').val();
            if (courier_id) {
                validateTrackingNumber(text, courier_id);
            }
        }, 100);
        
        // Close modal
        setTimeout(function() {
            stopScanner();
            $('#scannerModal').modal('hide');
            setTimeout(function() {
                $('#supplier_tracking_no').focus();
            }, 500);
        }, 1500);
    }

    // Stop scanner
    function stopScanner() {
        isScanning = false;
        
        if (codeReader) {
            try {
                codeReader.reset();
                codeReader = null;
            } catch (e) {}
        }
        
        if (videoElement && videoElement.srcObject) {
            const tracks = videoElement.srcObject.getTracks();
            tracks.forEach(track => {
                track.stop();
                console.log('Stopped track:', track.label);
            });
            videoElement.srcObject = null;
        }
        
        $('.google-corner, .google-line').remove();
        $('#scanner-message').html('');
        $('#scanner-error').hide();
    }

    // Switch camera with proper orientation handling
    async function switchCamera() {
        // Toggle facing mode
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        console.log('Switching to camera mode:', currentFacingMode);
        
        stopScanner();
        setTimeout(() => {
            initializeScanner();
        }, 500);
    }

    // Capture image
    function captureImage() {
        if (!videoElement || !videoElement.videoWidth) {
            alert('Camera not ready');
            return;
        }
        
        const canvas = document.getElementById('scanner-canvas');
        const context = canvas.getContext('2d');
        
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        
        // Draw with proper orientation
        context.save();
        
        // No mirroring - draw as is
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        
        context.restore();
        
        $('#scanner-message').html('Processing...');
        
        if (codeReader) {
            codeReader.decodeFromCanvas(canvas)
                .then(result => {
                    if (result) {
                        handleScanResult(result.text);
                    } else {
                        $('#scanner-message').html('No barcode found in image');
                    }
                })
                .catch(() => {
                    $('#scanner-message').html('Failed to decode image');
                });
        }
    }

    // Manual entry
    function tryManualEntry() {
        stopScanner();
        $('#scannerModal').modal('hide');
        setTimeout(() => $('#supplier_tracking_no').focus(), 500);
    }

    // Show error
    function showScannerError(message) {
        $('#scanner-message').html('');
        $('#scanner-error').show().html(`
            <strong>Error:</strong> ${message}
            <br><br>
            <button class="btn btn-sm btn-primary" onclick="retryScanner()">
                <i class="fa fa-refresh"></i> Retry
            </button>
            <button class="btn btn-sm btn-info" onclick="tryManualEntry()">
                <i class="fa fa-pencil"></i> Manual Entry
            </button>
        `);
    }

    // Retry scanner
    function retryScanner() {
        $('#scanner-error').hide();
        stopScanner();
        setTimeout(() => initializeScanner(), 500);
    }

    // Validation function
    function validateTrackingNumber(supplier_tracking_no, courier_id) {
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/package/check/tracking/number/'+supplier_tracking_no+'/'+courier_id,
            success: function (msg) {
                if(msg.status == 'failed'){
                    $('#supplier_tracking_no').val('').css({
                        "border-color": "#ff3c7e",
                        "background-color": "#ffeff4"
                    });
                    $("#traking_no-message").html('This tracking number has already been taken.');
                } else {
                    $("#supplier_tracking_no").css({
                        "border-color": "#37c936",
                        "background-color": "#ebfaeb"
                    });
                    $("#traking_no-message").html('');
                }
            }
        });
    }

    // Blur event
    $(".supplier_tracking_no").blur(function(){
        var val = $(this).val();
        var courier_id = $('#courier_id').val();
        if (val && courier_id) validateTrackingNumber(val, courier_id);
    });

    // Clean input
    $(".supplier_tracking_no").on('input', function() {
        $(this).val($(this).val().replace(/[^\x20-\x7E]/g, ''));
    });

    // Make functions global
    window.startScanner = startScanner;
    window.switchCamera = switchCamera;
    window.captureImage = captureImage;
    window.tryManualEntry = tryManualEntry;
    window.retryScanner = retryScanner;
</script>
