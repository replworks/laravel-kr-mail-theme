<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

// Recorded live in T-004 against Mailpit v1.30.3 (container image axllent/mailpit) on 2026-10-17.
// Fixture: tests/Fixtures/mailpit-html-check-dark-mobile.json

function krMailpitBase(): string
{
    return 'http://127.0.0.1:38025';
}

function krMailpitSmtpDsn(): string
{
    return 'smtp://127.0.0.1:3025';
}

function krMailpitReachable(): bool
{
    try {
        return Http::timeout(2)->get(krMailpitBase().'/api/v1/info')->successful();
    } catch (Throwable) {
        return false;
    }
}

function krThemeCssSampleHtml(): string
{
    return <<<'HTML'
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="color-scheme" content="light dark">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { margin: 0; padding: 0; }
  .outer { background-color: #ffffff; }
  .inner { color: #000000; }
  @media (prefers-color-scheme: dark) {
    .outer { background-color: #1a1a1a !important; }
    .inner { color: #ffffff !important; }
  }
  @media only screen and (max-width: 375px) {
    .container { width: 100% !important; }
    .inner { padding: 12px !important; }
  }
</style>
</head>
<body>
<table role="presentation" class="outer" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#ffffff;">
  <tr>
    <td align="center">
      <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;">
        <tr>
          <td class="inner" style="padding:16px;color:#000000;font-family:'Apple SD Gothic Neo','Malgun Gothic','Noto Sans KR',sans-serif;">안녕하세요. 다크/모바일 CSS 검증용 샘플입니다.</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
}

function krHtmlCheckUnsupportedInEveryClient(array $check): array
{
    return array_values(array_filter($check['Warnings'], function (array $warning) {
        foreach ($warning['Results'] as $result) {
            if ($result['Support'] !== 'no') {
                return false;
            }
        }

        return true;
    }));
}

it('records the Mailpit HTML check for a table mail with dark and narrow media queries', function () {
    $check = json_decode(
        file_get_contents(__DIR__.'/Fixtures/mailpit-html-check-dark-mobile.json'),
        true
    );

    expect($check)->toHaveKey('Warnings')
        ->and($check['Warnings'])->toBeArray()->not->toBeEmpty();

    expect(krHtmlCheckUnsupportedInEveryClient($check))->toBeEmpty();

    $slugs = array_column($check['Warnings'], 'Slug');
    expect($slugs)->toContain('css-at-media')
        ->not->toContain('css-prefers-color-scheme');
});

it('reproduces the dark and narrow media query HTML check against live Mailpit', function () {
    if (! krMailpitReachable()) {
        $this->markTestSkipped('Mailpit is not reachable at '.krMailpitBase());
    }

    Http::delete(krMailpitBase().'/api/v1/messages');

    $transport = Transport::fromDsn(krMailpitSmtpDsn());
    $transport->send(
        (new Email)
            ->from('dev@example.com')
            ->to('dev@example.com')
            ->subject('[검증] 다크/모바일 CSS')
            ->html(krThemeCssSampleHtml())
            ->text("다크/모바일 CSS 검증용 샘플입니다.\n")
    );

    $id = null;
    for ($i = 0; $i < 20; $i++) {
        $list = Http::get(krMailpitBase().'/api/v1/messages')->json();
        if (! empty($list['messages'][0]['ID'])) {
            $id = $list['messages'][0]['ID'];
            break;
        }
        usleep(300000);
    }

    expect($id)->not->toBeNull();

    $check = Http::get(krMailpitBase().'/api/v1/message/'.$id.'/html-check')->json();

    expect(krHtmlCheckUnsupportedInEveryClient($check))->toBeEmpty()
        ->and(array_column($check['Warnings'], 'Slug'))->toContain('css-at-media')
        ->not->toContain('css-prefers-color-scheme');
})->group('mailpit');
