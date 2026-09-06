<?php

use App\Models\Document;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;

it('produces a coherent demo dataset', function () {
    $this->seed();

    expect(Party::count())->toBe(15)
        ->and(Item::count())->toBe(30)
        ->and(Document::where('doc_type', 'sales_invoice')->count())->toBe(18)
        ->and(Document::where('doc_type', 'purchase_invoice')->count())->toBe(10)
        ->and(Document::where('status', 'settled')->exists())->toBeTrue()
        ->and(Document::where('status', 'partial')->exists())->toBeTrue()
        ->and(Document::where('status', 'void')->exists())->toBeTrue()
        ->and(Document::where('status', 'draft')->exists())->toBeTrue()
        ->and(Payment::where('direction', 'in')->exists())->toBeTrue()
        ->and(Payment::where('direction', 'out')->exists())->toBeTrue()
        ->and(Expense::count())->toBe(20);
});

it('numbers every posted document uniquely and sequentially', function () {
    $this->seed();

    $numbers = Document::whereNotNull('number')->pluck('number');

    expect($numbers->count())->toBe($numbers->unique()->count())
        ->and(Document::where('doc_type', 'sales_invoice')->whereNotNull('number')->orderBy('id')->value('number'))
        ->toBe('INV-'.now()->year.'-0001')
        ->and(Document::where('doc_type', 'purchase_invoice')->whereNotNull('number')->orderBy('id')->value('number'))
        ->toBe('BILL-'.now()->year.'-0001');
});

it('keeps settled_total equal to the sum of allocations', function () {
    $this->seed();

    Document::where('settled_total', '>', 0)->with('allocations')->each(function (Document $document) {
        expect((float) $document->settled_total)->toBe(round((float) $document->allocations->sum('amount'), 2));
    });
});
