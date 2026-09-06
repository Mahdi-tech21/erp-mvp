<?php

use App\Events\PaymentRecorded;
use App\Exceptions\DomainException;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Party;
use App\Models\Payment;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    // No tax keeps the arithmetic obvious: total == subtotal.
    CompanySetting::factory()->create(['tax_rate' => 0]);
    $this->payments = app(PaymentService::class);
});

function postedSale(float $amount): Document
{
    return app(DocumentService::class)->post(
        draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => $amount]])
    );
}

it('records a payment with no allocations', function () {
    $party = Party::factory()->customer()->create();

    $payment = $this->payments->record([
        'direction' => 'in',
        'party_id' => $party->id,
        'payment_date' => now()->toDateString(),
        'amount' => 100,
        'method' => 'cash',
    ]);

    expect($payment->exists)->toBeTrue()
        ->and($payment->allocations)->toHaveCount(0);
});

it('moves a document posted -> partial -> settled as payments land', function () {
    $doc = postedSale(100);

    $this->payments->record(paymentFor($doc, 40), [['document_id' => $doc->id, 'amount' => 40]]);

    expect($doc->fresh()->status)->toBe('partial')
        ->and($doc->fresh()->settled_total)->toBe('40.00');

    $this->payments->record(paymentFor($doc, 60), [['document_id' => $doc->id, 'amount' => 60]]);

    expect($doc->fresh()->status)->toBe('settled')
        ->and($doc->fresh()->settled_total)->toBe('100.00');
});

it('rejects an allocation beyond the remaining balance', function () {
    $doc = postedSale(100);

    expect(fn () => $this->payments->record(
        paymentFor($doc, 150),
        [['document_id' => $doc->id, 'amount' => 150]],
    ))->toThrow(DomainException::class);

    expect($doc->fresh()->status)->toBe('posted')
        ->and($doc->fresh()->settled_total)->toBe('0.00');
});

it('rejects a wrong-direction allocation', function () {
    $doc = postedSale(100); // a sales invoice

    expect(fn () => $this->payments->record([
        'direction' => 'out',
        'party_id' => $doc->party_id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'method' => 'cash',
    ], [['document_id' => $doc->id, 'amount' => 50]]))->toThrow(DomainException::class);
});

it('rejects an allocation to a different party', function () {
    $doc = postedSale(100);
    $stranger = Party::factory()->customer()->create();

    expect(fn () => $this->payments->record([
        'direction' => 'in',
        'party_id' => $stranger->id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'method' => 'cash',
    ], [['document_id' => $doc->id, 'amount' => 50]]))->toThrow(DomainException::class);
});

it('rejects allocations that add up to more than the payment', function () {
    $doc = postedSale(100);

    expect(fn () => $this->payments->record(
        paymentFor($doc, 30),
        [['document_id' => $doc->id, 'amount' => 50]],
    ))->toThrow(DomainException::class);
});

it('will not allocate to a draft document', function () {
    $doc = draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 100]]);

    expect(fn () => $this->payments->record(
        paymentFor($doc, 50),
        [['document_id' => $doc->id, 'amount' => 50]],
    ))->toThrow(DomainException::class);
});

it('fires PaymentRecorded', function () {
    $doc = postedSale(100);
    Event::fake([PaymentRecorded::class]);

    $this->payments->record(paymentFor($doc, 100), [['document_id' => $doc->id, 'amount' => 100]]);

    Event::assertDispatched(PaymentRecorded::class);
});

/**
 * A payment payload for the given document's party.
 *
 * @return array<string, mixed>
 */
function paymentFor(Document $doc, float $amount): array
{
    return [
        'direction' => 'in',
        'party_id' => $doc->party_id,
        'payment_date' => now()->toDateString(),
        'amount' => $amount,
        'method' => 'cash',
    ];
}
