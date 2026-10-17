<?php

use Replworks\KrMailTheme\BrandInformation;

function krRenderAdvertising(array $config, string $unsubscribeUrl = 'https://example.com/unsubscribe?user=abc&signature=sig'): string
{
    return view('kr-mail-theme::advertising', [
        'brand' => BrandInformation::fromConfig($config),
        'body' => '<p>본문 내용입니다.</p>',
        'unsubscribeUrl' => $unsubscribeUrl,
    ])->render();
}

it('renders the Advertising layout with header, body, footer, and the Unsubscribe Link (FR-010, FR-013, AC-005)', function () {
    $html = krRenderAdvertising(['service_name' => '위피노트', 'company_name' => '위피컴퍼니']);

    expect($html)->toContain('본문 내용입니다.')
        ->toContain('href="https://example.com/unsubscribe?user=abc&amp;signature=sig"')
        ->toContain('회사명: 위피컴퍼니');
});

it('renders the Unsubscribe Link and neither missing field when Company Name and Customer Center are not supplied (FR-013)', function () {
    $html = krRenderAdvertising(['service_name' => '위피노트']);

    expect($html)->toContain('href="https://example.com/unsubscribe?user=abc&amp;signature=sig"')
        ->not->toContain('회사명')
        ->not->toContain('고객센터');
});

it('keeps header before body before footer, with the Unsubscribe Link last (FR-002, FR-013)', function () {
    $html = krRenderAdvertising(['service_name' => '위피노트', 'company_name' => '위피컴퍼니', 'logo' => 'https://cdn.example.com/logo.png']);

    $logo = strpos($html, 'https://cdn.example.com/logo.png');
    $body = strpos($html, '본문 내용입니다.');
    $footer = strpos($html, '회사명: 위피컴퍼니');
    $unsubscribe = strpos($html, 'https://example.com/unsubscribe');

    expect($logo)->toBeLessThan($body)
        ->and($body)->toBeLessThan($footer)
        ->and($footer)->toBeLessThan($unsubscribe);
});
