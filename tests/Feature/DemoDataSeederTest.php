<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;
use Database\Seeders\DemoDataSeeder;

beforeEach(fn () => CompanySetting::factory()->create());

it('produces a coherent core demo dataset', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Party::count())->toBe(15)
        ->and(Item::count())->toBe(30)
        ->and(Document::where('doc_type', 'sales_invoice')->count())->toBe(18)
        ->and(Document::where('doc_type', 'purchase_invoice')->count())->toBe(10)
        ->and(Document::where('status', 'settled')->exists())->toBeTrue()
        ->and(Document::where('status', 'partial')->exists())->toBeTrue()
        ->and(Document::where('status', 'void')->exists())->toBeTrue()
        ->and(Document::where('status', 'draft')->exists())->toBeTrue()
        ->and(Payment::where('direction', 'in')->exists())->toBeTrue()
        ->and(Payment::where('direction', 'out')->exists())->toBeTrue();
});

it('numbers every posted document uniquely and sequentially', function () {
    $this->seed(DemoDataSeeder::class);

    $numbers = Document::whereNotNull('number')->pluck('number');

    expect($numbers->count())->toBe($numbers->unique()->count())
        ->and(Document::where('doc_type', 'sales_invoice')->whereNotNull('number')->orderBy('id')->value('number'))
        ->toBe('INV-'.now()->year.'-0001')
        ->and(Document::where('doc_type', 'purchase_invoice')->whereNotNull('number')->orderBy('id')->value('number'))
        ->toBe('BILL-'.now()->year.'-0001');
});

it('keeps settled_total equal to the sum of allocations', function () {
    $this->seed(DemoDataSeeder::class);

    Document::where('settled_total', '>', 0)->with('allocations')->each(function (Document $document) {
        expect((float) $document->settled_total)->toBe(round((float) $document->allocations->sum('amount'), 2));
    });
});

it('runs the full database seeder including active modules without error', function () {
    $this->seed();

    // With modules active the core seeds only the buy side; the modules bring
    // the customers (patients, shoppers) and the sales.
    expect(Party::count())->toBeGreaterThan(15)
        ->and(Document::where('doc_type', 'purchase_invoice')->exists())->toBeTrue()
        ->and(Expense::exists())->toBeTrue()
        ->and(Document::where('doc_type', 'sales_invoice')->where('status', '!=', 'draft')->exists())->toBeTrue()
        ->and(Item::where('type', 'service')->exists())->toBeTrue();
});
