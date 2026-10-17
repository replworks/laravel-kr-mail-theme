<?php

it('documents the verified Mailpit API shape', function () {
    $fixture = json_decode(
        file_get_contents(__DIR__.'/Fixtures/mailpit-html-check.json'),
        true
    );

    expect($fixture)->toHaveKey('Warnings')
        ->and($fixture['Warnings'][0])->toHaveKeys(['Slug', 'Results'])
        ->and($fixture['Warnings'][0]['Results'][0])->toHaveKey('Support');
});
