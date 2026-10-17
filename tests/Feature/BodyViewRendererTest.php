<?php

use Replworks\KrMailTheme\BodyViewRenderer;

beforeEach(function () {
    app('view')->addNamespace('kr-test', __DIR__.'/../Fixtures/views');
});

it('turns a Recipient Name into "이름님" (FR-061, AC-021)', function () {
    expect(BodyViewRenderer::displayName('홍길동'))->toBe('홍길동님');
});

it('uses "회원님" for a null or empty Recipient Name (FR-061, AC-021)', function () {
    expect(BodyViewRenderer::displayName(null))->toBe('회원님')
        ->and(BodyViewRenderer::displayName(''))->toBe('회원님');
});

it('makes a Service variable and $recipientDisplayName available in the view (FR-060, AC-021)', function () {
    $out = (new BodyViewRenderer)->render('kr-test::greeting', ['message' => '안녕하세요'], '홍길동');

    expect($out)->toContain('안녕하세요')
        ->toContain('홍길동님');
});

it('escapes HTML in a Service variable (AC-021)', function () {
    $out = (new BodyViewRenderer)->render('kr-test::greeting', ['message' => '<b>안녕</b>'], '홍길동');

    expect($out)->toContain('&lt;b&gt;안녕&lt;/b&gt;')
        ->not->toContain('<b>안녕</b>');
});
