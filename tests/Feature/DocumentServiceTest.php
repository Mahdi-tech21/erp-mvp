<?php

use App\Events\DocumentPosted;
use App\Events\DocumentVoided;
use App\Exceptions\DomainException;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Party;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    CompanySetting::factory()->create(['tax_rate' => 11.00, 'sales_prefix' => 'INV', 'purchase_prefix' => 'BILL']);
    $this->service = app(DocumentService::class);
});

it('posts a sales invoice: totals, number, status, timestamp, event', function () {
    Event::fake([DocumentPosted::class]);

    $doc = draftDocument('sales_invoice', [
        ['qty' => 2, 'unit_price' => 50],
        ['qty' => 1, 'unit_price' => 30],
    ]);

    $posted = $this->service->post($doc);

    expect($posted->subtotal)->toBe('130.00')
        ->and($posted->tax_amount)->toBe('14.30')
        ->and($posted->total)->toBe('144.30')
        ->and($posted->status)->toBe('posted')
        ->and($posted->number)->toBe('INV-'.now()->year.'-0001')
        ->and($posted->posted_at)->not->toBeNull();

    Event::assertDispatched(DocumentPosted::class, fn ($e) => $e->document->is($posted));
});

it('applies a header discount before tax', function () {
    $doc = draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 200]], ['discount' => 50]);

    $posted = $this->service->post($doc);

    expect($posted->subtotal)->toBe('200.00')
        ->and($posted->tax_amount)->toBe('16.50')   // (200 - 50) * 11%
        ->and($posted->total)->toBe('166.50');
});

it('numbers each document type in its own sequence', function () {
    $s1 = $this->service->post(draftDocument('sales_invoice'));
    $s2 = $this->service->post(draftDocument('sales_invoice'));
    $p1 = $this->service->post(draftDocument('purchase_invoice'));

    $year = now()->year;

    expect($s1->number)->toBe("INV-{$year}-0001")
        ->and($s2->number)->toBe("INV-{$year}-0002")
        ->and($p1->number)->toBe("BILL-{$year}-0001");
});

it('will not post a draft with no lines', function () {
    $doc = Document::factory()->sales()->create();

    expect(fn () => $this->service->post($doc))->toThrow(DomainException::class);
});

it('will not post a document that is already posted', function () {
    $doc = $this->service->post(draftDocument());

    expect(fn () => $this->service->post($doc->fresh()->load('lines', 'party')))
        ->toThrow(DomainException::class);
});

it('rejects a sales invoice whose party is not a customer', function () {
    $doc = Document::factory()->sales()->create(['party_id' => Party::factory()->supplier()]);
    DocumentLine::factory()->for($doc)->create();

    expect(fn () => $this->service->post($doc->load('lines', 'party')))
        ->toThrow(DomainException::class);
});

it('rejects a purchase invoice whose party is not a supplier', function () {
    $doc = Document::factory()->purchase()->create(['party_id' => Party::factory()->customer()]);
    DocumentLine::factory()->for($doc)->create();

    expect(fn () => $this->service->post($doc->load('lines', 'party')))
        ->toThrow(DomainException::class);
});

it('rejects a discount larger than the subtotal', function () {
    $doc = draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 100]], ['discount' => 150]);

    expect(fn () => $this->service->post($doc))->toThrow(DomainException::class);
});

it('voids a posted document, keeps the number, and fires the event before the flip', function () {
    $statusWhenListenerRan = null;
    Event::listen(DocumentVoided::class, function (DocumentVoided $e) use (&$statusWhenListenerRan) {
        $statusWhenListenerRan = $e->document->status;
    });

    $doc = $this->service->post(draftDocument());
    $number = $doc->number;

    $voided = $this->service->void($doc);

    expect($voided->status)->toBe('void')
        ->and($voided->number)->toBe($number)
        ->and($statusWhenListenerRan)->toBe('posted');
});

it('lets a listener veto a void by throwing', function () {
    Event::listen(DocumentVoided::class, fn () => throw new DomainException('stock would go negative'));

    $doc = $this->service->post(draftDocument());

    expect(fn () => $this->service->void($doc))->toThrow(DomainException::class);
    expect($doc->fresh()->status)->toBe('posted');
});

it('will not void a draft', function () {
    expect(fn () => $this->service->void(draftDocument()))->toThrow(DomainException::class);
});

it('will not void a document that has payment allocations', function () {
    $doc = $this->service->post(draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 100]]));

    app(PaymentService::class)->record([
        'direction' => 'in',
        'party_id' => $doc->party_id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'method' => 'cash',
    ], [['document_id' => $doc->id, 'amount' => 50]]);

    expect(fn () => $this->service->void($doc->fresh()))->toThrow(DomainException::class);
});
