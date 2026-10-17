<?php

use Replworks\KrMailTheme\BodyViewRenderer;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\MailRenderer;
use Replworks\KrMailTheme\Markdown\MarkdownConverter;
use Replworks\KrMailTheme\Tests\Support\MailpitClient;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;
use Workbench\App\Support\Preview;

function krCheckSamples(): array
{
    return require __DIR__.'/../../workbench/samples.php';
}

function krCheckDataset(): array
{
    $dataset = [];

    $kinds = [
        'informational' => MailKind::Informational,
        'advertising' => MailKind::Advertising,
    ];

    foreach ($kinds as $kindName => $kind) {
        foreach (array_keys(krCheckSamples()['bodies']) as $body) {
            $dataset[$kindName.'-'.$body] = [$kind, $body];
        }
    }

    return $dataset;
}

function krCheckPrepare(): MailpitClient
{
    static $prepared = false;

    $client = MailpitClient::fromEnv();

    if (! $client->reachable()) {
        test()->fail('Mailpit is unreachable at '.$client->baseUrl());
    }

    if (! $prepared) {
        $client->empty();
        $prepared = true;
    }

    return $client;
}

function krCheckApplyBrand(): void
{
    $samples = krCheckSamples();

    foreach ($samples['brand'] as $key => $value) {
        config()->set("kr-mail-theme.{$key}", $value);
    }

    app('view')->addNamespace($samples['view_namespace'], $samples['views_path']);
}

function krCheckAssertFr(string $fr, bool $condition, string $detail = ''): void
{
    test()->assertTrue($condition, $fr.($detail === '' ? '' : ': '.$detail));
}

function krCheckHeader(array $headers, string $name): ?string
{
    foreach ($headers as $key => $values) {
        if (strcasecmp($key, $name) === 0) {
            return $values[0] ?? null;
        }
    }

    return null;
}

function krCheckUnsupportedFeatures(array $check): array
{
    $unsupported = [];

    foreach ($check['Warnings'] ?? [] as $warning) {
        $results = $warning['Results'] ?? [];

        if ($results === []) {
            continue;
        }

        $everyClientUnsupported = true;

        foreach ($results as $result) {
            if (($result['Support'] ?? 'yes') !== 'no') {
                $everyClientUnsupported = false;
                break;
            }
        }

        if ($everyClientUnsupported) {
            $unsupported[] = $warning;
        }
    }

    return $unsupported;
}

it('checks every Mail Kind and Sample Body combination (FR-054 to FR-057)', function (MailKind $kind, string $body) {
    $client = krCheckPrepare();
    krCheckApplyBrand();

    $samples = krCheckSamples();
    $brand = $samples['brand'];
    $advertising = $kind === MailKind::Advertising;

    $markdown = (new BodyViewRenderer)->render(
        $samples['view_namespace'].'::'.$samples['bodies'][$body],
        [],
        $samples['recipient_name'],
    );

    $mail = (new MailRenderer)->render($kind, Preview::SUBJECT, $markdown, $samples['user_identifier']);

    $id = $client->send($mail);

    $html = $client->html($id);
    $text = $client->text($id);
    $headers = $client->headers($id);
    $check = $client->htmlCheck($id);

    $unsupported = krCheckUnsupportedFeatures($check);
    krCheckAssertFr(
        'FR-054',
        $unsupported === [],
        'features with no support in any mail client: '.implode(', ', array_column($unsupported, 'Slug')),
    );

    $unsubscribe = UnsubscribeUrlGenerator::generate($samples['user_identifier']);
    $bodyText = trim((new MarkdownConverter)->toText($markdown));
    $bodyProbe = trim(explode("\n", $bodyText)[0] ?? '');

    $fr005 = str_contains($html, $brand['company_name'])
        && str_contains($html, $brand['customer_center'])
        && str_contains($html, $brand['business_registration_number'])
        && str_contains($html, $brand['address'])
        && str_contains($html, $brand['terms_url']);
    krCheckAssertFr('FR-005', $fr005, 'the footer shows every supplied field');

    $fr006 = ! str_contains($html, '[서비스명]') && ! str_contains($html, '[로고]');
    krCheckAssertFr('FR-006', $fr006, 'the footer shows no Placeholder for a supplied field');

    $fr033 = $html !== '' && $text !== '';
    krCheckAssertFr('FR-033', $fr033, 'an HTML part and a TEXT part exist');

    $serviceNameAt = strpos($text, $brand['service_name']);
    $bodyAt = $bodyProbe === '' ? false : strpos($text, $bodyProbe);
    $footerAt = strpos($text, $brand['company_name']);
    $fr034 = $serviceNameAt !== false && $bodyAt !== false && $footerAt !== false
        && $serviceNameAt < $bodyAt && $bodyAt < $footerAt
        && str_contains($text, $brand['customer_center'])
        && str_contains($text, $brand['terms_url']);
    krCheckAssertFr('FR-034', $fr034, 'the TEXT part holds Service Name, body, and footer values in order');

    $urls = [];
    if (preg_match_all('#https?://[^\s)]+#', $markdown, $matches) !== false) {
        $urls = $matches[0];
    }

    $fr035 = ! str_contains($text, '<')
        && ! str_contains($text, '](');

    foreach ($urls as $url) {
        $fr035 = $fr035 && str_contains($text, $url);
    }
    krCheckAssertFr('FR-035', $fr035, 'the TEXT part has no tags or link syntax and every URL in full');

    krCheckAssertFr('FR-055', $fr005 && $fr006 && $fr033 && $fr034 && $fr035, 'FR-005, FR-006, FR-033, FR-034, and FR-035 hold');

    if ($advertising) {
        $fr025 = $mail->subject === '(광고) '.Preview::SUBJECT;
        krCheckAssertFr('FR-025', $fr025, 'the Final Subject begins with the advertising prefix');

        $fr013 = str_contains(html_entity_decode($html, ENT_QUOTES | ENT_HTML5), $unsubscribe);
        krCheckAssertFr('FR-013', $fr013, 'the Advertising footer shows the Unsubscribe Link');

        $fr021 = krCheckHeader($headers, 'List-Unsubscribe') === '<'.$unsubscribe.'>';
        krCheckAssertFr('FR-021', $fr021, 'the List-Unsubscribe header carries the Unsubscribe Link');

        $fr022 = krCheckHeader($headers, 'List-Unsubscribe-Post') === 'List-Unsubscribe=One-Click';
        krCheckAssertFr('FR-022', $fr022, 'the List-Unsubscribe-Post header carries the one-click value');

        $fr036 = str_contains($text, $unsubscribe);
        krCheckAssertFr('FR-036', $fr036, 'the TEXT part contains the Unsubscribe Link');

        krCheckAssertFr('FR-056', $fr025 && $fr013 && $fr021 && $fr022 && $fr036, 'FR-025, FR-013, FR-021, FR-022, and FR-036 hold');
    } else {
        $fr011 = ! str_contains($html, 'unsubscribe');
        krCheckAssertFr('FR-011', $fr011, 'the Informational layout has no Unsubscribe Link');

        $fr023 = krCheckHeader($headers, 'List-Unsubscribe') === null;
        krCheckAssertFr('FR-023', $fr023, 'no List-Unsubscribe header');

        $fr024 = krCheckHeader($headers, 'List-Unsubscribe-Post') === null;
        krCheckAssertFr('FR-024', $fr024, 'no List-Unsubscribe-Post header');

        $fr037 = ! str_contains($text, 'unsubscribe');
        krCheckAssertFr('FR-037', $fr037, 'the TEXT part has no Unsubscribe Link');

        krCheckAssertFr('FR-057', $fr011 && $fr023 && $fr024 && $fr037, 'FR-011, FR-023, FR-024, and FR-037 hold');
    }
})->with(krCheckDataset());
