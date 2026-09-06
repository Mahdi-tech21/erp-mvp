<?php

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Party;
use App\Models\Payment;
use App\Services\DocumentService;
use App\Services\PaymentService;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 0]); // total == subtotal
});

/** Post a sales invoice for a fresh customer and return it. */
function postedInvoiceFor(Party $customer, float $amount): Document
{
    $document = Document::factory()->sales()->create(['party_id' => $customer->id, 'discount' => 0]);
    DocumentLine::factory()->for($document)->create(['qty' => 1, 'unit_price' => $amount, 'line_total' => $amount]);

    return app(DocumentService::class)->post($document->load('lines', 'party'));
}

it('lists payments and filters by direction', function () {
    Payment::factory()->in()->create(['reference' => 'IN-REF']);
    Payment::factory()->out()->create(['reference' => 'OUT-REF']);

    $this->get(route('payments.index', ['direction' => 'in']))
        ->assertOk()
        ->assertSee('IN-REF')
        ->assertDontSee('OUT-REF');
});

it('asks for a party first, then shows their open documents', function () {
    $customer = Party::factory()->customer()->create(['name' => 'Paying Customer']);
    $invoice = postedInvoiceFor($customer, 100);

    $this->get(route('payments.in.create'))
        ->assertOk()
        ->assertSee('Paying Customer')
        ->assertDontSee($invoice->number);

    $this->get(route('payments.in.create', ['party_id' => $customer->id]))
        ->assertOk()
        ->assertSee($invoice->number);
});

it('records a customer payment and settles the invoice', function () {
    $customer = Party::factory()->customer()->create();
    $invoice = postedInvoiceFor($customer, 100);

    $response = $this->post(route('payments.in.store'), [
        'party_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'amount' => 100,
        'method' => 'transfer',
        'allocations' => [
            ['document_id' => $invoice->id, 'amount' => 100],
        ],
    ]);

    $payment = Payment::latest('id')->first();
    $response->assertRedirect(route('payments.show', $payment));

    expect($payment->direction)->toBe('in')
        ->and($invoice->fresh()->status)->toBe('settled')
        ->and($invoice->fresh()->settled_total)->toBe('100.00')
        ->and(AuditLog::where('action', 'payment.recorded')->exists())->toBeTrue();
});

it('records an unallocated payment when nothing is picked', function () {
    $customer = Party::factory()->customer()->create();

    $this->post(route('payments.in.store'), [
        'party_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'amount' => 250,
        'method' => 'cash',
    ])->assertRedirect();

    expect(Payment::latest('id')->first()->allocations)->toHaveCount(0);
});

it('rejects a customer-payment party that is not a customer', function () {
    $supplier = Party::factory()->supplier()->create();

    $this->post(route('payments.in.store'), [
        'party_id' => $supplier->id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'method' => 'cash',
    ])->assertSessionHas('error');

    expect(Payment::count())->toBe(0);
});

it('rejects an over-allocation and saves nothing', function () {
    $customer = Party::factory()->customer()->create();
    $invoice = postedInvoiceFor($customer, 100);

    $this->post(route('payments.in.store'), [
        'party_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'amount' => 500,
        'method' => 'cash',
        'allocations' => [
            ['document_id' => $invoice->id, 'amount' => 500],
        ],
    ])->assertSessionHas('error');

    expect(Payment::count())->toBe(0)
        ->and($invoice->fresh()->status)->toBe('posted');
});

it('validates amount and method', function () {
    $customer = Party::factory()->customer()->create();

    $this->post(route('payments.in.store'), [
        'party_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'amount' => 0,
        'method' => 'bitcoin',
    ])->assertSessionHasErrors(['amount', 'method']);
});

it('renders the payment detail page with its allocations', function () {
    $customer = Party::factory()->customer()->create();
    $invoice = postedInvoiceFor($customer, 100);

    $payment = app(PaymentService::class)->record([
        'direction' => 'in',
        'party_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'amount' => 60,
        'method' => 'card',
    ], [['document_id' => $invoice->id, 'amount' => 60]]);

    $this->get(route('payments.show', $payment))
        ->assertOk()
        ->assertSee($invoice->number)
        ->assertSee('60.00');
});

it('allocates a supplier payment to a bill', function () {
    $supplier = Party::factory()->supplier()->create();
    $bill = Document::factory()->purchase()->create(['party_id' => $supplier->id, 'discount' => 0]);
    DocumentLine::factory()->for($bill)->create(['qty' => 1, 'unit_price' => 80, 'line_total' => 80]);
    app(DocumentService::class)->post($bill->load('lines', 'party'));

    $this->post(route('payments.out.store'), [
        'party_id' => $supplier->id,
        'payment_date' => now()->toDateString(),
        'amount' => 80,
        'method' => 'transfer',
        'allocations' => [['document_id' => $bill->id, 'amount' => 80]],
    ])->assertRedirect();

    expect($bill->fresh()->status)->toBe('settled')
        ->and(Payment::latest('id')->first()->direction)->toBe('out');
});
