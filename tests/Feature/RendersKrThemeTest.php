<?php

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\Tests\Support\ThemeMailable;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;
use Symfony\Component\Mailer\SentMessage;

beforeEach(function () {
    Route::get('/unsubscribe', fn () => 'ok')->name(UnsubscribeUrlGenerator::ROUTE_NAME);
    app('view')->addNamespace('kr-test', __DIR__.'/../Fixtures/views');

    config()->set('kr-mail-theme.service_name', '위피노트');
    config()->set('kr-mail-theme.company_name', '위피컴퍼니');
    config()->set('kr-mail-theme.customer_center', '1588-0000');
    config()->set('kr-mail-theme.business_registration_number', '123-45-67890');
    config()->set('kr-mail-theme.address', '서울특별시 강남구');
    config()->set('kr-mail-theme.terms_url', 'https://example.com/terms');
});

function sendThemeMail(Mailable $mailable): SentMessage
{
    Mail::mailer('array')->to('dev@example.com')->send($mailable);

    return Mail::mailer('array')->getSymfonyTransport()->messages()->last();
}

it('sends a Mailable through the array mailer with Subject, HTML part, TEXT part, and headers (FR-038, AC-013)', function () {
    $sent = sendThemeMail(new ThemeMailable(
        MailKind::Advertising,
        '이벤트 안내',
        'kr-test::body',
        ['message' => '주문이 완료되었습니다.'],
        'user-1',
        '홍길동',
    ));

    $email = $sent->getOriginalMessage();
    $url = UnsubscribeUrlGenerator::generate('user-1');

    expect($email->getSubject())->toBe('(광고) 이벤트 안내')
        ->and($email->getHtmlBody())
        ->toContain('주문이 완료되었습니다.')
        ->toContain('위피노트')
        ->toContain('unsubscribe')
        ->and($email->getTextBody())
        ->toContain('주문이 완료되었습니다.')
        ->toContain('홍길동님')
        ->toContain($url)
        ->toContain('수신거부')
        ->and($email->getHeaders()->get('List-Unsubscribe')->getBodyAsString())->toBe('<'.$url.'>')
        ->and($email->getHeaders()->get('List-Unsubscribe-Post')->getBodyAsString())->toBe('List-Unsubscribe=One-Click');
});

it('sends Informational Mail without the Unsubscribe Link and headers', function () {
    $sent = sendThemeMail(new ThemeMailable(
        MailKind::Informational,
        '주문 안내',
        'kr-test::body',
        ['message' => '주문이 완료되었습니다.'],
    ));

    $email = $sent->getOriginalMessage();

    expect($email->getSubject())->toBe('주문 안내')
        ->and($email->getHeaders()->has('List-Unsubscribe'))->toBeFalse()
        ->and($email->getHtmlBody())->not->toContain('unsubscribe')
        ->and($email->getTextBody())->not->toContain('unsubscribe');
});

it('renders without copying any Package file into the Service (FR-038)', function () {
    expect(config_path('kr-mail-theme.php'))->not->toBeFile();

    $sent = sendThemeMail(new ThemeMailable(MailKind::Informational, '주문 안내', 'kr-test::body'));

    expect($sent->getOriginalMessage()->getHtmlBody())->toContain('위피노트');
});

it('uses Brand Information supplied through configuration (AC-013)', function () {
    config()->set('kr-mail-theme.service_name', '설정값서비스');

    $sent = sendThemeMail(new ThemeMailable(MailKind::Informational, '주문 안내', 'kr-test::body'));

    expect($sent->getOriginalMessage()->getHtmlBody())->toContain('설정값서비스');
});
