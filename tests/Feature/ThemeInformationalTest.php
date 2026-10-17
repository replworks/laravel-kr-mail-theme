<?php

use Replworks\KrMailTheme\BrandInformation;

function krRenderInformational(array $config, string $body = '<p>본문 내용입니다.</p>'): string
{
    return view('kr-mail-theme::informational', [
        'brand' => BrandInformation::fromConfig($config),
        'body' => $body,
    ])->render();
}

it('renders header, Mail Body, and footer in this order (FR-002, AC-001)', function () {
    $html = krRenderInformational([
        'service_name' => '위피노트',
        'logo' => 'https://cdn.example.com/logo.png',
        'company_name' => '위피컴퍼니',
    ]);

    $logo = strpos($html, 'https://cdn.example.com/logo.png');
    $body = strpos($html, '본문 내용입니다.');
    $footer = strpos($html, '회사명: 위피컴퍼니');

    expect($logo)->not->toBeFalse()
        ->and($body)->not->toBeFalse()
        ->and($footer)->not->toBeFalse()
        ->and($logo)->toBeLessThan($body)
        ->and($body)->toBeLessThan($footer);
});

it('shows the Service Name and Logo placeholders when not supplied (FR-003, FR-004, AC-002)', function () {
    $html = krRenderInformational([]);

    expect($html)->toContain('[서비스명]')
        ->toContain('[로고]');
});

it('shows the supplied Service Name and Logo (FR-003, FR-004, AC-002)', function () {
    $html = krRenderInformational([
        'service_name' => '위피노트',
        'logo' => 'https://cdn.example.com/logo.png',
    ]);

    expect($html)->toContain('위피노트')
        ->toContain('src="https://cdn.example.com/logo.png"')
        ->not->toContain('[서비스명]')
        ->not->toContain('[로고]');
});

it('shows only the supplied footer fields and never their labels otherwise (FR-005, FR-006, AC-003)', function () {
    $html = krRenderInformational([
        'company_name' => '위피컴퍼니',
        'address' => '서울특별시 강남구',
    ]);

    expect($html)->toContain('회사명: 위피컴퍼니')
        ->toContain('주소: 서울특별시 강남구')
        ->not->toContain('고객센터')
        ->not->toContain('사업자등록번호')
        ->not->toContain('이용약관');
});

it('shows no footer field at all when none is supplied (FR-005, FR-006, AC-003)', function () {
    $html = krRenderInformational([]);

    expect($html)->not->toContain('회사명')
        ->not->toContain('고객센터')
        ->not->toContain('사업자등록번호')
        ->not->toContain('주소:')
        ->not->toContain('이용약관');
});

it('shows the Terms Link as a link when supplied (FR-005)', function () {
    $html = krRenderInformational(['terms_url' => 'https://example.com/terms']);

    expect($html)->toContain('href="https://example.com/terms"');
});

it('contains no Unsubscribe Link and no advertising slot (FR-009, FR-011, FR-012)', function () {
    $html = krRenderInformational(['service_name' => '위피노트']);

    expect($html)->toBeString()
        ->not->toContain('unsubscribe')
        ->not->toContain('Unsubscribe');
});

it('uses the Korean font stack (Work: Korean font stack)', function () {
    $html = krRenderInformational([]);

    expect($html)->toContain("font-family:'Apple SD Gothic Neo','Malgun Gothic','Noto Sans KR',sans-serif");
});
