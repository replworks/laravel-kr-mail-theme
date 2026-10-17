<?php

use Illuminate\Support\Facades\Route;
use Replworks\KrMailTheme\BodyViewRenderer;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\MailRenderer;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

function krWorkbenchSamples(): array
{
    return require __DIR__.'/../../workbench/samples.php';
}

beforeEach(function () {
    $samples = krWorkbenchSamples();

    app('view')->addNamespace($samples['view_namespace'], $samples['views_path']);
    Route::get('/unsubscribe', fn () => 'ok')->name(UnsubscribeUrlGenerator::ROUTE_NAME);

    foreach ($samples['brand'] as $key => $value) {
        config()->set("kr-mail-theme.{$key}", $value);
    }
});

it('renders every Sample Body inside both Mail Kinds without error (FR-041)', function (string $body) {
    $samples = krWorkbenchSamples();
    $view = $samples['view_namespace'].'::'.$body;

    $markdown = (new BodyViewRenderer)->render($view, [], $samples['recipient_name']);

    expect($markdown)->not->toBe('');

    foreach (MailKind::cases() as $kind) {
        $mail = (new MailRenderer)->render($kind, '제목', $markdown, $samples['user_identifier']);

        expect($mail->html)->toContain($samples['brand']['service_name'])
            ->and($mail->text)->not->toBeEmpty();
    }
})->with([
    'receipt' => 'bodies.receipt',
    'button' => 'bodies.button',
    'long' => 'bodies.long',
    'quote' => 'bodies.quote',
]);

it('renders the long Sample Body with the Workbench Recipient Name (FR-041)', function () {
    $samples = krWorkbenchSamples();

    $markdown = (new BodyViewRenderer)->render(
        $samples['view_namespace'].'::bodies.long',
        [],
        $samples['recipient_name'],
    );

    expect($markdown)->toContain('홍길동님');
});
