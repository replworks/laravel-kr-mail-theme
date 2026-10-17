<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Replworks\KrMailTheme\Tests\Support\RawPartsMailable;

// Verified live in T-005 against Mailpit v1.30.3 (container image axllent/mailpit) under
// Laravel 12 and Laravel 13 on 2026-10-17. Working calls are recorded in ARCHITECTURE.md
// under RendersKrTheme.

function krRawPartsBase(): string
{
    return 'http://127.0.0.1:38025';
}

function krRawPartsReachable(): bool
{
    try {
        return Http::timeout(2)->get(krRawPartsBase().'/api/v1/info')->successful();
    } catch (Throwable) {
        return false;
    }
}

it('sends a Mailable with raw HTML, raw TEXT, and the two List-Unsubscribe headers to Mailpit', function () {
    if (! krRawPartsReachable()) {
        $this->markTestSkipped('Mailpit is not reachable at '.krRawPartsBase());
    }

    $base = krRawPartsBase();
    $html = '<html><body><p>HTML 본문</p></body></html>';
    $text = "TEXT 본문\n둘째 줄\n";
    $unsubscribe = '<https://example.com/unsubscribe?user=abc>';

    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => '127.0.0.1',
        'mail.mailers.smtp.port' => 3025,
        'mail.from.address' => 'dev@example.com',
        'mail.from.name' => 'Dev',
    ]);

    Http::delete($base.'/api/v1/messages');

    Mail::to('dev@example.com')->send(new RawPartsMailable(
        rawHtml: $html,
        rawText: $text,
        textHeaders: [
            'List-Unsubscribe' => $unsubscribe,
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ],
    ));

    $id = null;
    for ($i = 0; $i < 20; $i++) {
        $list = Http::get($base.'/api/v1/messages')->json();
        if (! empty($list['messages'][0]['ID'])) {
            $id = $list['messages'][0]['ID'];
            break;
        }
        usleep(300000);
    }

    expect($id)->not->toBeNull();

    $summary = Http::get($base.'/api/v1/message/'.$id)->json();
    expect($summary['HTML'])->toBe($html)
        ->and(str_replace("\r\n", "\n", $summary['Text']))->toBe($text);

    $headers = Http::get($base.'/api/v1/message/'.$id.'/headers')->json();
    expect($headers)->toHaveKey('List-Unsubscribe')
        ->and($headers['List-Unsubscribe'])->toBe([$unsubscribe])
        ->and($headers)->toHaveKey('List-Unsubscribe-Post')
        ->and($headers['List-Unsubscribe-Post'])->toBe(['List-Unsubscribe=One-Click']);

    expect(Http::get($base.'/view/'.$id.'.html')->body())->toBe($html)
        ->and(str_replace("\r\n", "\n", Http::get($base.'/view/'.$id.'.txt')->body()))->toBe($text);
})->group('mailpit');
