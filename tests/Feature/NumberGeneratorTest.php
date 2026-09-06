<?php

use App\Models\CompanySetting;
use App\Services\NumberGenerator;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    CompanySetting::factory()->create(['sales_prefix' => 'INV', 'purchase_prefix' => 'BILL']);
    $this->gen = app(NumberGenerator::class);
});

it('produces sequential, zero-padded numbers per type and year', function () {
    $a = DB::transaction(fn () => $this->gen->next('sales_invoice'));
    $b = DB::transaction(fn () => $this->gen->next('sales_invoice'));
    $c = DB::transaction(fn () => $this->gen->next('purchase_invoice'));

    $year = now()->year;

    expect($a)->toBe("INV-{$year}-0001")
        ->and($b)->toBe("INV-{$year}-0002")
        ->and($c)->toBe("BILL-{$year}-0001");
});

it('advances the sequences row rather than counting documents', function () {
    DB::transaction(fn () => $this->gen->next('sales_invoice'));

    $row = DB::table('sequences')->where('key', 'sales_invoice')->first();

    expect((int) $row->next_number)->toBe(2);
});

it('rejects an unknown document type', function () {
    expect(fn () => DB::transaction(fn () => $this->gen->next('credit_note')))
        ->toThrow(RuntimeException::class);
});
