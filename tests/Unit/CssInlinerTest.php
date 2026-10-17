<?php

use Replworks\KrMailTheme\CssInliner;

function krThemeCssHtml(): string
{
    return '<html><head></head><body><div class="kr-text">본문</div></body></html>';
}

it('inlines base.css onto elements', function () {
    $out = CssInliner::fromTheme()->inline(krThemeCssHtml());

    expect($out)->toContain('class="kr-text" style="color: #333333;')
        ->toContain('background-color: #ffffff');
});

it('keeps the dark and mobile CSS present and unchanged as a <style> element in <head>', function () {
    $inliner = CssInliner::fromTheme();
    $out = $inliner->inline(krThemeCssHtml());

    expect($out)->toContain("<head><style>\n")
        ->toContain('@media (prefers-color-scheme: dark) {')
        ->toContain(".kr-text {\n    color: #eeeeee !important;\n}")
        ->toContain('@media only screen and (max-width: 375px) {')
        ->toContain(".kr-text {\n    padding: 12px;\n}");
});

it('appends the forced dark variant without the media condition', function () {
    $out = CssInliner::fromTheme()->inline(krThemeCssHtml(), CssInliner::SCHEME_DARK);

    expect($out)->not->toContain('prefers-color-scheme')
        ->toContain('html, body { color-scheme: dark; }')
        ->toContain(".kr-text {\n    color: #eeeeee !important;\n}");
});

it('pins the document color scheme for a forced light scheme', function () {
    $out = CssInliner::fromTheme()->inline(krThemeCssHtml(), CssInliner::SCHEME_LIGHT);

    expect($out)->not->toContain('prefers-color-scheme')
        ->not->toContain('color: #eeeeee')
        ->toContain('html, body { color-scheme: light; }')
        ->toContain('@media only screen and (max-width: 375px)');
});
