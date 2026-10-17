<?php

use Replworks\KrMailTheme\Markdown\MarkdownConverter;

it('converts a paragraph (FR-030, FR-032)', function () {
    $html = (new MarkdownConverter)->toHtml('첫 문단입니다.');

    expect($html)->toContain('<p>첫 문단입니다.</p>');
});

it('converts a link (FR-032)', function () {
    $html = (new MarkdownConverter)->toHtml('자세한 내용은 [안내](https://example.com/guide)를 보세요.');

    expect($html)->toContain('<a href="https://example.com/guide">안내</a>');
});

it('converts a table (FR-032)', function () {
    $html = (new MarkdownConverter)->toHtml("| 항목 | 값 |\n| --- | --- |\n| 주문번호 | 12345 |");

    expect($html)->toContain('<table>')
        ->toContain('<th>항목</th>')
        ->toContain('<td>12345</td>');
});

it('converts a blockquote (FR-032)', function () {
    $html = (new MarkdownConverter)->toHtml('> 환불은 7일 이내에 가능합니다.');

    expect($html)->toContain('<blockquote>')
        ->toContain('<p>환불은 7일 이내에 가능합니다.</p>');
});

it('turns a link-only paragraph into a button (FR-031)', function () {
    $html = (new MarkdownConverter)->toHtml('[확인하기](https://example.com/button)');

    expect($html)->toContain('<table role="presentation"')
        ->toContain('<a href="https://example.com/button">확인하기</a>')
        ->not->toMatch('/<p>\s*<a href="https:\/\/example\.com\/button">/');
});

it('escapes raw HTML in the Markdown (AC-011)', function () {
    $html = (new MarkdownConverter)->toHtml('<script>alert(1)</script>');

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>');
});
