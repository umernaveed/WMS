@php
    $freightType = strtolower((string) ($pack->freight_type ?? ''));
    $isSea = $freightType === 'sea';
    $cuft = (float) ($pack->volume_cuft ?? $pack->chargeable_weight ?? 0);
    $cbm = (float) ($pack->volume_cbm ?? ($cuft > 0 ? $cuft / 35.3147 : 0));
    $measure = $isSea
        ? number_format($cuft, 2).' cu.ft / '.number_format($cbm, 3).' CBM'
        : number_format((float) ($pack->package_weight ?? $pack->weight ?? 0), 2).' lbs';
@endphp
{{ $pack->package_description }} / {{ $measure }}
