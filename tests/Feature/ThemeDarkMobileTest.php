<?php

use Replworks\KrMailTheme\BrandInformation;
use Replworks\KrMailTheme\CssInliner;

function krInformationalHtml(): string
{
    return view('kr-mail-theme::informational', [
        'brand' => BrandInformation::fromConfig(['service_name' => '위피노트']),
        'body' => '<p>본문 내용입니다.</p>',
    ])->render();
}

it('provides a Light variant and a Dark variant (FR-028)', function () {
    $out = CssInliner::fromTheme()->inline(krInformationalHtml());

    expect($out)->toContain('@media (prefers-color-scheme: dark) {')
        ->toContain('color: #eeeeee;');

    expect(substr($out, 0, strpos($out, '@media')))->not->toContain('#eeeeee');
});

it('provides narrow-width CSS and keeps no fixed width wider than Mobile width (FR-029)', function () {
    $out = CssInliner::fromTheme()->inline(krInformationalHtml());

    preg_match('/@media only screen and \(max-width: 375px\) \{(.*?)<\/style>/s', $out, $match);

    expect($match)->not->toBeEmpty()
        ->and($match[1])->toContain('.kr-container')
        ->and($match[1])->toContain('width: 100% !important');

    preg_match_all('/width:(\d+)px/', $out, $widths);

    expect($widths[1])->toBe(['600']);

    expect($out)->toContain('class="kr-container"')
        ->toContain('width:600px');
});
