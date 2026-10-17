<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Replworks\KrMailTheme\ConsentRepository;

uses(RefreshDatabase::class);

function krConsentRow(string $userIdentifier): ?object
{
    return DB::table(ConsentRepository::TABLE)->where('user_identifier', $userIdentifier)->first();
}

it('creates the Consent Record when it does not exist and reports the change (FR-048)', function () {
    $changed = (new ConsentRepository)->revokeMarketing('user-1');

    $row = krConsentRow('user-1');

    expect($changed)->toBeTrue()
        ->and($row)->not->toBeNull()
        ->and((bool) $row->marketing_consent)->toBeFalse();
});

it('sets an existing true value to false and reports the change (FR-047)', function () {
    DB::table(ConsentRepository::TABLE)->insert([
        'user_identifier' => 'user-1',
        'marketing_consent' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $changed = (new ConsentRepository)->revokeMarketing('user-1');

    expect($changed)->toBeTrue()
        ->and((bool) krConsentRow('user-1')->marketing_consent)->toBeFalse();
});

it('leaves an existing false value false and reports no change (FR-051)', function () {
    DB::table(ConsentRepository::TABLE)->insert([
        'user_identifier' => 'user-1',
        'marketing_consent' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $changed = (new ConsentRepository)->revokeMarketing('user-1');

    expect($changed)->toBeFalse()
        ->and((bool) krConsentRow('user-1')->marketing_consent)->toBeFalse();
});

it('keeps one row per Recipient User Identifier', function () {
    $repository = new ConsentRepository;
    $repository->revokeMarketing('user-1');
    $repository->revokeMarketing('user-1');

    expect(DB::table(ConsentRepository::TABLE)->where('user_identifier', 'user-1')->count())->toBe(1);
});

it('stores the marketing consent value as a boolean (FR-049)', function () {
    (new ConsentRepository)->revokeMarketing('user-1');

    $stored = DB::table(ConsentRepository::TABLE)->where('user_identifier', 'user-1')->value('marketing_consent');

    expect(Schema::getColumnType(ConsentRepository::TABLE, 'marketing_consent'))
        ->toBeIn(['boolean', 'tinyint', 'bool'])
        ->and($stored)->toBe(0);
});
