<?php

use Replworks\KrMailTheme\Markdown\MarkdownConverter;

it('renders an inline link as "label: URL" and never as Markdown syntax (FR-035)', function () {
    $text = (new MarkdownConverter)->toText('자세한 내용은 [안내 페이지](https://example.com/guide)를 확인하세요.');

    expect($text)->toBe("자세한 내용은 안내 페이지: https://example.com/guide를 확인하세요.\n")
        ->not->toContain('](')
        ->not->toContain('[');
});

it('renders a link whose label equals its URL as the URL alone (FR-035)', function () {
    $text = (new MarkdownConverter)->toText('[https://example.com/guide](https://example.com/guide)');

    expect($text)->toBe("https://example.com/guide\n");
});

it('keeps every URL in full, including the query string (FR-035)', function () {
    $text = (new MarkdownConverter)->toText('확인: [여기](https://example.com/guide?utm_source=mail&id=42)');

    expect($text)->toContain('https://example.com/guide?utm_source=mail&id=42');
});

it('renders a table as one line per row (FR-035)', function () {
    $text = (new MarkdownConverter)->toText("| 항목 | 값 |\n| --- | --- |\n| 주문번호 | 12345 |");

    expect($text)->toBe("항목 | 값\n주문번호 | 12345\n");
});

it('renders a blockquote with "> " prefixes (FR-035)', function () {
    $text = (new MarkdownConverter)->toText('> 환불은 7일 이내에 가능합니다.');

    expect($text)->toBe("> 환불은 7일 이내에 가능합니다.\n");
});

it('contains no HTML tags from inline HTML (FR-035)', function () {
    $text = (new MarkdownConverter)->toText('본문 <b>굵게</b> 입니다.');

    expect($text)->toBe("본문 굵게 입니다.\n")
        ->not->toContain('<')
        ->not->toContain('>');
});

it('drops a raw HTML block (FR-035)', function () {
    $text = (new MarkdownConverter)->toText("첫 문단입니다.\n\n<script>alert(1)</script>\n\n둘째 문단입니다.");

    expect($text)->not->toContain('<script>')
        ->not->toContain('alert(1)')
        ->toContain('첫 문단입니다.')
        ->toContain('둘째 문단입니다.');
});
