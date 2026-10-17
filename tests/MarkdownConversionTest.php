<?php

use Replworks\KrMailTheme\Markdown\MarkdownConverter;

// T-006: one parse of a Markdown sample feeds two renderers. The sample has a table,
// a blockquote, an inline link, and a link-only (button) paragraph.

function krSampleMarkdown(): string
{
    return <<<'MD'
| 항목 | 값 |
| --- | --- |
| 주문번호 | 12345-6789 |

> 환불은 7일 이내에 가능합니다.

자세한 내용은 [안내 페이지](https://example.com/guide)를 확인하세요.

[확인하기](https://example.com/button)
MD;
}

it('renders the sample to HTML with a table-based button for the link-only paragraph', function () {
    $html = (new MarkdownConverter)->toHtml(krSampleMarkdown());

    expect($html)->toContain('<table>')
        ->toContain('<blockquote>')
        ->toContain('<a href="https://example.com/guide">안내 페이지</a>')
        ->toContain('<a href="https://example.com/button">확인하기</a>');

    expect($html)->not->toMatch('/<p>\s*<a href="https:\/\/example\.com\/button">/');
});

it('renders the same sample to the documented TEXT forms', function () {
    $text = (new MarkdownConverter)->toText(krSampleMarkdown());

    expect($text)->toBe(
        "항목 | 값\n".
        "주문번호 | 12345-6789\n\n".
        "> 환불은 7일 이내에 가능합니다.\n\n".
        "자세한 내용은 안내 페이지: https://example.com/guide를 확인하세요.\n\n".
        "확인하기: https://example.com/button\n"
    );

    expect($text)->not->toContain('<')
        ->not->toContain('](');
});
