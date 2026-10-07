<!-- Footer START -->
<footer class="content-footer">
    <div class="footer">
        <div class="copyright">
            @php
                $footerText = trim((string) ($getSettings->site_footer_text ?? ''));
                $companyName = $getSettings->company_name ?? $getSettings->site_name ?? $getSettings->site_title ?? config('app.name');
            @endphp
            @if($footerText !== '')
                <span>{!! htmlspecialchars_decode(stripslashes($footerText)) !!}</span>
            @else
                <b>&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</b>
            @endif
        </div>
    </div>
</footer>
<!-- Footer END -->
