<?php

use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\SubjectFormatter;

it('leaves the Informational Mail subject unchanged (FR-026)', function () {
    expect(SubjectFormatter::format(MailKind::Informational, '주문이 완료되었습니다'))
        ->toBe('주문이 완료되었습니다');
});

it('prefixes the Advertising Mail subject with "(광고) " (FR-025)', function () {
    expect(SubjectFormatter::format(MailKind::Advertising, '신규 이벤트 안내'))
        ->toBe('(광고) 신규 이벤트 안내');
});

it('does not add a second "(광고)" prefix (FR-027)', function () {
    expect(SubjectFormatter::format(MailKind::Advertising, '(광고) 신규 이벤트 안내'))
        ->toBe('(광고) 신규 이벤트 안내')
        ->and(SubjectFormatter::format(MailKind::Advertising, '(광고)신규 이벤트 안내'))
        ->toBe('(광고)신규 이벤트 안내');
});

it('handles an empty subject (AC-009)', function () {
    expect(SubjectFormatter::format(MailKind::Informational, ''))->toBe('')
        ->and(SubjectFormatter::format(MailKind::Advertising, ''))->toBe('(광고) ');
});
