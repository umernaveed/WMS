<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>Package Label</title>
    <style>
        @page { size: 4in 6in; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #e5e7eb;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .toolbar {
            padding: 12px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f3f4f6;
            z-index: 100;
        }
        .toolbar button {
            background: #111827;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            padding: 9px 14px;
            font-weight: 600;
            margin: 0 4px;
        }
        .label {
            background: #fff;
            border: 2px solid #111;
            box-shadow: 8px 0 0 #6b7280;
            display: flex;
            flex-direction: column;
            height: 6in;
            margin: 60px auto 0 auto;
            overflow: hidden;
            padding: 0.19in 0.16in 0.15in;
            position: relative;
            width: 4in;
        }
        .header {
            align-items: flex-start;
            display: grid;
            gap: 0.26in;
            grid-template-columns: 0.9in 1fr;
            margin-bottom: 0.02in;
        }
        .logo-box {
            align-items: center;
            background: #07182d;
            color: #fff;
            display: flex;
            font-size: 30px;
            font-weight: 900;
            height: 0.78in;
            justify-content: center;
            overflow: hidden;
            width: 0.78in;
        }
        .logo-box img {
            background: #fff;
            height: 100%;
            object-fit: contain;
            padding: 5px;
            width: 100%;
        }
        .sender-name {
            font-size: 19px;
            font-weight: 800;
            line-height: 1.2;
            margin-top: 0.01in;
        }
        .sender-address {
            font-size: 11px;
            line-height: 1.2;
            margin-top: 0.02in;
        }
        .rule {
            border-top: 4px solid #000;
            margin: 0.055in 0 0.09in;
        }
        .row {
            display: grid;
            gap: 0.2in;
            grid-template-columns: 1.35fr 0.65fr;
            margin-bottom: 0.05in;
        }
        .block { min-width: 0; }
        .label-text {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }
        .value-large {
            font-size: 22px;
            font-weight: 900;
            line-height: 1.1;
            word-wrap: break-word;
        }
        .value {
            font-size: 15px;
            font-weight: normal;
            line-height: 1.2;
            word-wrap: break-word;
        }
        .value-medium {
            font-size: 17px;
            font-weight: 900;
            line-height: 1.2;
            word-wrap: break-word;
        }
        .right { text-align: left; }
        .ship-details { padding-bottom: 0.02in; }
        .detail-row {
            display: grid;
            gap: 0.2in;
            grid-template-columns: 1.45fr 0.55fr;
            margin-bottom: 0.1in;
        }
        .contents { min-height: 0.52in; }
        .created-row {
            align-items: baseline;
            display: grid;
            gap: 0.22in;
            grid-template-columns: auto 1fr;
        }
        .created-row .label-text {
            font-size: 17px;
        }
        .barcode-panel {
            flex-shrink: 0;
            margin-top: auto;
            padding-top: 0.08in;
            text-align: center;
        }
        .generator {
            font-family: "Courier New", monospace;
            font-size: 31px;
            font-weight: 900;
            letter-spacing: 1.1px;
            line-height: 1;
            margin-bottom: 0.08in;
        }
        .barcode {
            height: 0.74in;
            margin: 0 auto;
            overflow: hidden;
            width: 2.25in;
        }
        .barcode-svg,
        .barcode svg {
            display: block;
            height: 100%;
            overflow: visible;
            width: 100%;
        }
        .barcode-value {
            font-family: "Courier New", monospace;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-top: 5px;
            word-wrap: anywhere;
        }
        .extra-ref {
            border-top: 1px dashed #aaa;
            display: flex;
            flex-shrink: 0;
            font-family: monospace;
            font-size: 11px;
            font-weight: 700;
            justify-content: space-between;
            padding-top: 0.02in;
        }
        .footer-credit {
            color: #111;
            flex-shrink: 0;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.3px;
            line-height: 1;
            margin-top: 0.04in;
            text-align: center;
            text-transform: uppercase;
        }
        @media print {
            html,
            body {
                background: #fff;
                display: block;
                height: 6in;
                padding: 0;
                margin: 0;
                min-height: 0;
                overflow: hidden;
                width: 4in;
            }
            .toolbar {
                display: none;
            }
            .label {
                border-width: 2px;
                box-shadow: none;
                margin: 0;
                height: 5.98in;
                padding: 0.19in 0.16in 0.15in;
                page-break-after: avoid;
                page-break-inside: avoid;
                break-after: avoid;
                break-inside: avoid;
                width: 4in;
            }
            .header {
                gap: 0.18in;
                grid-template-columns: 0.75in 1fr;
            }
            .logo-box {
                height: 0.68in;
                width: 0.68in;
            }
            .sender-name {
                font-size: 16px;
            }
            .sender-address {
                font-size: 10px;
                line-height: 1.15;
            }
            .rule {
                margin: 0.04in 0 0.06in;
            }
            .row,
            .detail-row {
                gap: 0.14in;
                margin-bottom: 0.05in;
            }
            .detail-row {
                margin-bottom: 0.07in;
            }
            .label-text {
                font-size: 14px;
            }
            .value-large {
                font-size: 19px;
            }
            .value-medium {
                font-size: 15px;
            }
            .value {
                font-size: 13px;
            }
            .contents {
                min-height: 0;
            }
            .created-row .label-text {
                font-size: 14px;
            }
            .barcode-panel {
                padding-top: 0.04in;
            }
            .generator {
                font-size: 31px;
                margin-bottom: 0.08in;
            }
            .barcode {
                height: 0.74in;
                width: 2.25in;
            }
            .barcode-value {
                font-size: 11px;
                margin-top: 2px;
            }
            .extra-ref {
                font-size: 10px;
            }
            .footer-credit {
                font-size: 7px;
                margin-top: 0.015in;
            }
        }
    </style>
</head>
<body>
@php
    $label = $cms_label ?? [];
    $tenantName = $label['tenant_name'] ?? ($company_name ?? 'System Admin Logistics');
    $tenantLogoUrl = $label['tenant_logo_data_uri'] ?? ($label['tenant_logo_url'] ?? null);
    $tenantAddress = $label['tenant_address_html'] ?? collect([
        $package_shipping_address_1 ?? null,
        $package_shipping_address_2 ?? null,
        trim(collect([$city ?? null, $country ?? null, $zip ?? null])->filter()->implode(', ')),
    ])->filter()->implode('<br>');
    $consignee = $label['consignee'] ?? ($user_name ?? 'Unassigned Customer');
    $accountDisplay = $label['account_display'] ?? ($mailbox ?? 'N/A');
    $shipperName = $label['shipper_name'] ?? $tenantName;
    $measureLabel = $label['measure_label'] ?? ($measure_label ?? 'Weight:');
    $weightDisplay = $label['weight_display'] ?? ($measure_value ?? ($weight_label ?? (($weight ?? 0).' LBS')));
    $trackingOther = $label['tracking_other'] ?? ($tracking_no ?? '-');
    $destination = $label['destination'] ?? ($destination ?? ($outlet_name ?? ($location ?? 'N/A')));
    $freightType = $label['freight_type'] ?? ($label_type ?? ($freight_type ?? 'USA Air'));
    $packageContents = $label['package_contents'] ?? ($category ?? $consignee);
    $createdDate = $label['created_date'] ?? ($dte ?? date('M j, Y'));
    $displayGenerator = $label['display_generator'] ?? ($tracking_no ?? 'COU000001');
    $barcodeValue = $label['barcode_value'] ?? $displayGenerator;
    $barcodeSvg = \App\Support\Code128Barcode::svg((string) $barcodeValue);
    $referenceNumber = $label['reference_number'] ?? preg_replace('/\D+/', '', (string) $displayGenerator);
    $olReference = $label['ol_reference'] ?? $displayGenerator;
@endphp
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print Label</button>
        <button type="button" onclick="window.print()">Download PDF</button>
    </div>

    <main class="label">
        <header class="header">
            <div class="logo-box">
                @if($tenantLogoUrl)
                    <img src="{{ $tenantLogoUrl }}" alt="{{ $tenantName }} logo">
                @else
                    {{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $tenantName), 0, 1)) ?: 'C' }}
                @endif
            </div>
            <div>
                <div class="sender-name">{{ $tenantName }}</div>
                <div class="sender-address">{!! $tenantAddress ?: '&nbsp;' !!}</div>
            </div>
        </header>

        <div class="rule"></div>

        <div class="row">
            <div class="block">
                <div class="label-text">Consignee:</div>
                <div class="value-large">{{ $consignee }}</div>
            </div>
            <div class="block right">
                <div class="label-text">Account:</div>
                <div class="value">{{ $accountDisplay }}</div>
            </div>
        </div>

        <div class="rule"></div>

        <section class="ship-details">
            <div class="detail-row">
                <div class="block">
                    <div class="label-text">Shipper:</div>
                    <div class="value">{{ $shipperName }}</div>
                </div>
                <div class="block right">
                    <div class="label-text">{{ $measureLabel }}</div>
                    <div class="value">{{ $weightDisplay }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="block">
                    <div class="label-text">Tracking No - Other</div>
                    <div class="value-medium">{{ $trackingOther ?: '-' }}</div>
                </div>
                <div></div>
            </div>

            <div class="detail-row">
                <div class="block">
                    <div class="label-text">Destination:</div>
                    <div class="value-medium">{{ $destination }}</div>
                </div>
                <div class="block right">
                    <div class="label-text">Type:</div>
                    <div class="value-medium">{{ $freightType }}</div>
                </div>
            </div>
        </section>

        <div class="rule"></div>

        <section class="contents">
            <div class="label-text">Package Contents:</div>
            <div class="value">{{ $packageContents }}</div>
            <div class="created-row">
                <div class="label-text">Created:</div>
                <div class="value">{{ $createdDate }}</div>
            </div>
        </section>

        <div class="rule"></div>

        <section class="barcode-panel">
            <div class="generator">{{ $displayGenerator }}</div>
            <div class="barcode">{!! $barcodeSvg !!}</div>
            <div class="barcode-value">{{ $barcodeValue }}</div>
        </section>

        <div class="extra-ref">
            <span>{{ $referenceNumber }}</span>
            <span>{{ $olReference }}</span>
        </div>

        <footer class="footer-credit">powered by freitora.com</footer>
    </main>
    <script>
        document.title = 'label-{{ preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $barcodeValue) }}';
        @if(! empty($auto_print))
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 250);
            });
        @endif
    </script>
</body>
</html>
