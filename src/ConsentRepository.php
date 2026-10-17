<?php

namespace Replworks\KrMailTheme;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class ConsentRepository
{
    public const TABLE = 'kr_mail_consents';

    public function revokeMarketing(string $userIdentifier): bool
    {
        $now = now();

        if (! $this->hasRecord($userIdentifier)) {
            try {
                DB::table(self::TABLE)->insert([
                    'user_identifier' => $userIdentifier,
                    'marketing_consent' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                return true;
            } catch (QueryException) {
                // Another request inserted the row between the check and the insert.
                // The unique index rejects it; fall through and retry as an update.
            }
        }

        $affected = DB::table(self::TABLE)
            ->where('user_identifier', $userIdentifier)
            ->where('marketing_consent', true)
            ->update([
                'marketing_consent' => false,
                'updated_at' => $now,
            ]);

        return $affected > 0;
    }

    private function hasRecord(string $userIdentifier): bool
    {
        return DB::table(self::TABLE)->where('user_identifier', $userIdentifier)->exists();
    }
}
