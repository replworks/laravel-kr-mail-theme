<?php

beforeEach(function () {
    $samples = require __DIR__.'/../../workbench/samples.php';

    foreach ($samples['brand'] as $key => $value) {
        config()->set("kr-mail-theme.{$key}", $value);
    }

    app('view')->addNamespace($samples['view_namespace'], $samples['views_path']);
    app('view')->addLocation($samples['views_path']);

    require __DIR__.'/../../workbench/routes/web.php';
});

it('serves SCR-001 with every element EL-001 to EL-011 (FLOW-002 step 1)', function () {
    $response = $this->get('/');

    $response->assertOk();

    foreach (range(1, 11) as $number) {
        $response->assertSee(sprintf('el-%03d', $number), false);
    }

    $response->assertSee('data-state="initial"', false);
});

it('returns the HTML part for the selected Mail Kind and Sample Body (FR-041, EL-006)', function () {
    $light = $this->get('/preview?kind=informational&body=receipt&scheme=light');
    $dark = $this->get('/preview?kind=informational&body=receipt&scheme=dark');

    $light->assertOk()->assertSee('위피노트', false)->assertSee('주문 내역을 안내드립니다.', false);
    $dark->assertOk()->assertSee('위피노트', false);

    expect($light->getContent())->not->toContain('#eeeeee')
        ->and($dark->getContent())->toContain('#eeeeee')
        ->and($dark->getContent())->not->toContain('prefers-color-scheme')
        ->and($light->getContent())->toContain('@media only screen and (max-width: 375px)');
});

it('returns the Final Subject and the TEXT part for EL-005 and EL-007', function () {
    $meta = $this->getJson('/meta?kind=advertising&body=button&scheme=light');

    $meta->assertOk()
        ->assertJsonPath('subject', '(광고) 위피노트 안내');

    expect($meta->json('text'))->toContain('주문 내역 확인하기')
        ->and($meta->json('text'))->toContain('https://example.com/orders/sample-user-1');
});

it('keeps the Informational subject without the advertising prefix (EL-005)', function () {
    $meta = $this->getJson('/meta?kind=informational&body=long&scheme=light');

    $meta->assertOk()->assertJsonPath('subject', '위피노트 안내');
});

it('applies the Mobile width of 375 px to EL-006 (EL-003)', function () {
    expect(file_get_contents(__DIR__.'/../../workbench/resources/js/scr-001.js'))
        ->toContain('375px');
});
