{{ $brand->serviceName() }}

{!! rtrim($body) !!}
@php
    $footerLines = [];
    if ($brand->companyName() !== null) {
        $footerLines[] = '회사명: '.$brand->companyName();
    }
    if ($brand->customerCenter() !== null) {
        $footerLines[] = '고객센터: '.$brand->customerCenter();
    }
    if ($brand->businessRegistrationNumber() !== null) {
        $footerLines[] = '사업자등록번호: '.$brand->businessRegistrationNumber();
    }
    if ($brand->address() !== null) {
        $footerLines[] = '주소: '.$brand->address();
    }
    if ($brand->termsUrl() !== null) {
        $footerLines[] = '이용약관: '.$brand->termsUrl();
    }
@endphp
@if (count($footerLines) > 0)

{!! implode("\n", $footerLines) !!}
@endif
@if (($unsubscribeUrl ?? null) !== null)

수신거부: {!! $unsubscribeUrl !!}
@endif
