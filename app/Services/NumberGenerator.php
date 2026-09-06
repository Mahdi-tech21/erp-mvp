<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Document numbering, e.g. "INV-2026-0001".
 *
 * The counter lives in the `sequences` table, one row per (type, year). The
 * row is locked with `lockForUpdate()` for the life of the caller's
 * transaction, so two concurrent posts can never claim the same number.
 * Never `max(id) + 1`.
 */
class NumberGenerator
{
    public function next(string $docType): string
    {
        if (DB::transactionLevel() === 0) {
            throw new RuntimeException('NumberGenerator::next() must run inside a database transaction.');
        }

        $year = (int) now()->year;

        DB::table('sequences')->insertOrIgnore([
            'key' => $docType,
            'year' => $year,
            'next_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('sequences')
            ->where('key', $docType)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $number = (int) $row->next_number;

        DB::table('sequences')
            ->where('key', $docType)
            ->where('year', $year)
            ->update([
                'next_number' => $number + 1,
                'updated_at' => now(),
            ]);

        return sprintf('%s-%d-%04d', $this->prefix($docType), $year, $number);
    }

    private function prefix(string $docType): string
    {
        $settings = CompanySetting::current();

        return match ($docType) {
            'sales_invoice' => $settings->sales_prefix,
            'purchase_invoice' => $settings->purchase_prefix,
            default => throw new RuntimeException("Unknown document type [{$docType}]."),
        };
    }
}
