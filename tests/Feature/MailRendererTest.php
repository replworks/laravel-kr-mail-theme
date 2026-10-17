<?php

use Illuminate\Support\Facades\Route;
use Replworks\KrMailTheme\BrandInformation;
use Replworks\KrMailTheme\Exceptions\MissingRecipientIdentifier;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\MailRenderer;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

function krMailRenderer(array $config = []): MailRenderer
{
    Route::get('/unsubscribe', fn () => 'ok')->name(UnsubscribeUrlGenerator::ROUTE_NAME);

    return new MailRenderer(brand: BrandInformation::fromConfig($config));
}

function krFullBrand(): array
{
    return [
        'service_name' => '위피노트',
        'logo' => 'https://cdn.example.com/logo.png',
        'company_name' => '위피컴퍼니',
        'customer_center' => '1588-0000',
        'business_registration_number' => '123-45-67890',
        'address' => '서울특별시 강남구',
        'terms_url' => 'https://example.com/terms',
    ];
}

it('returns both an HTML part and a TEXT part (FR-033)', function () {
    $mail = krMailRenderer()->render(MailKind::Informational, '제목', '본문입니다.');

    expect($mail->html)->toContain('본문입니다.')
        ->and($mail->text)->toContain('본문입니다.')
        ->and($mail->subject)->toBe('제목');
});

it('renders the TEXT part as Service Name, Mail Body, footer values, in this order (FR-034, FR-007, FR-005, FR-006, AC-003)', function () {
    $mail = krMailRenderer(krFullBrand())->render(MailKind::Informational, '제목', '본문입니다.');

    $name = strpos($mail->text, '위피노트');
    $body = strpos($mail->text, '본문입니다.');
    $footer = strpos($mail->text, '회사명: 위피컴퍼니');

    expect($name)->not->toBeFalse()
        ->and($body)->not->toBeFalse()
        ->and($footer)->not->toBeFalse()
        ->and($name)->toBeLessThan($body)
        ->and($body)->toBeLessThan($footer)
        ->and($mail->text)->toContain('고객센터: 1588-0000')
        ->toContain('이용약관: https://example.com/terms');
});

it('shows the Service Name placeholder and no footer field in the TEXT part when nothing is supplied (FR-007, FR-006, AC-003)', function () {
    $mail = krMailRenderer()->render(MailKind::Informational, '제목', '본문입니다.');

    expect($mail->text)->toContain('[서비스명]')
        ->not->toContain('회사명')
        ->not->toContain('고객센터')
        ->not->toContain('이용약관');
});

it('produces a TEXT part with no HTML tags, no Markdown link syntax, and full URLs (FR-035, AC-012)', function () {
    $mail = krMailRenderer()->render(
        MailKind::Informational,
        '제목',
        '자세한 내용은 [안내](https://example.com/guide?utm=1)를 보세요.',
    );

    expect($mail->text)->not->toContain('<')
        ->not->toContain('](')
        ->toContain('https://example.com/guide?utm=1');
});

it('adds the List-Unsubscribe headers to Advertising Mail only (FR-021, FR-022, FR-023, FR-024, AC-008)', function () {
    $advertising = krMailRenderer()->render(MailKind::Advertising, '이벤트', '본문입니다.', 'user-1');

    expect($advertising->headers)->toHaveKey('List-Unsubscribe')
        ->and($advertising->headers['List-Unsubscribe'])->toMatch('#^<http.*>$#')
        ->and($advertising->headers['List-Unsubscribe-Post'])->toBe('List-Unsubscribe=One-Click');

    $informational = krMailRenderer()->render(MailKind::Informational, '안내', '본문입니다.');

    expect($informational->headers)->toBe([]);
});

it('puts the Unsubscribe Link in the TEXT part of Advertising Mail only (FR-036, FR-037)', function () {
    $advertising = krMailRenderer()->render(MailKind::Advertising, '이벤트', '본문입니다.', 'user-1');
    $url = UnsubscribeUrlGenerator::generate('user-1');

    expect($advertising->text)->toContain($url);

    $informational = krMailRenderer()->render(MailKind::Informational, '안내', '본문입니다.');

    expect($informational->text)->not->toContain('unsubscribe');
});

it('fails Advertising Mail without a Recipient User Identifier (ERR-001, AC-006)', function () {
    expect(fn () => krMailRenderer()->render(MailKind::Advertising, '이벤트', '본문입니다.'))
        ->toThrow(MissingRecipientIdentifier::class, 'Recipient User Identifier');
});

it('ignores a Recipient User Identifier for Informational Mail (ERR-004, AC-004)', function () {
    $mail = krMailRenderer()->render(MailKind::Informational, '안내', '본문입니다.', 'user-1');

    expect($mail->headers)->toBe([])
        ->and($mail->html)->not->toContain('unsubscribe')
        ->and($mail->text)->not->toContain('unsubscribe');
});

it('keeps neither Company Name nor Customer Center Contact out of both parts for Advertising Mail (FR-013)', function () {
    $mail = krMailRenderer(['service_name' => '위피노트'])->render(MailKind::Advertising, '이벤트', '본문입니다.', 'user-1');

    expect($mail->html)->not->toContain('회사명')
        ->not->toContain('고객센터')
        ->and($mail->html)->toContain('unsubscribe')
        ->and($mail->text)->not->toContain('회사명')
        ->and($mail->text)->not->toContain('고객센터')
        ->and($mail->text)->toContain('unsubscribe');
});
