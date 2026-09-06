<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Party;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 10]);
});

/**
 * @param  list<array{0: float, 1: float, 2?: float}>  $lines  [qty, unit_price, cost_price?]
 */
function postDoc(string $type, Party $party, array $lines, string $date, float $discount = 0): Document
{
    $document = Document::factory()->create([
        'doc_type' => $type,
        'party_id' => $party->id,
        'doc_date' => $date,
        'due_date' => Carbon::parse($date)->addDays(30),
        'discount' => $discount,
        'status' => 'draft',
    ]);

    foreach (array_values($lines) as $i => $line) {
        $item = Item::factory()->create(['unit_price' => $line[1], 'cost_price' => $line[2] ?? 0]);
        $document->lines()->create([
            'item_id' => $item->id,
            'description' => 'Line',
            'qty' => $line[0],
            'unit_price' => $line[1],
            'line_total' => round($line[0] * $line[1], 2),
            'sort_order' => $i,
        ]);
    }

    return app(DocumentService::class)->post($document->load('lines', 'party'));
}

it('shows the reports hub with every report', function () {
    $this->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Sales by period')
        ->assertSee('A/R aging')
        ->assertSee('VAT return')
        ->assertSee('Gross margin');
});

it('sums sales by period and excludes void invoices', function () {
    $customer = Party::factory()->customer()->create();
    postDoc('sales_invoice', $customer, [[2, 100]], '2026-05-10');  // net 200, vat 20, total 220
    $void = postDoc('sales_invoice', $customer, [[1, 50]], '2026-05-10'); // total 55
    app(DocumentService::class)->void($void);

    $this->get(route('reports.sales', ['from' => '2026-05-01', 'to' => '2026-05-31']))
        ->assertOk()
        ->assertSee('2026-05-10')
        ->assertSee('200.00')
        ->assertSee('220.00')
        ->assertDontSee('55.00');
});

it('buckets an overdue invoice in A/R aging', function () {
    $customer = Party::factory()->customer()->create(['name' => 'Late Payer']);
    // doc 75 days ago, due +30 => 45 days overdue => the 31-60 bucket
    postDoc('sales_invoice', $customer, [[1, 500]], now()->subDays(75)->toDateString());

    $this->get(route('reports.ar-aging'))
        ->assertOk()
        ->assertSee('Late Payer')
        ->assertSee('550.00'); // 500 + 10% VAT
});

it('renders A/P aging', function () {
    $supplier = Party::factory()->supplier()->create(['name' => 'Owed Supplier']);
    postDoc('purchase_invoice', $supplier, [[1, 200]], now()->subDays(10)->toDateString());

    $this->get(route('reports.ap-aging'))->assertOk()->assertSee('Owed Supplier');
});

it('sums purchases by period', function () {
    $supplier = Party::factory()->supplier()->create();
    postDoc('purchase_invoice', $supplier, [[3, 100]], '2026-04-10'); // net 300, VAT 30, total 330

    $this->get(route('reports.purchases', ['from' => '2026-04-01', 'to' => '2026-04-30']))
        ->assertOk()
        ->assertSee('300.00')
        ->assertSee('330.00');
});

it('runs a supplier-side statement', function () {
    $supplier = Party::factory()->supplier()->create();
    $bill = postDoc('purchase_invoice', $supplier, [[1, 500]], '2026-02-01'); // total 550

    app(PaymentService::class)->record([
        'direction' => 'out',
        'party_id' => $supplier->id,
        'payment_date' => '2026-02-20',
        'amount' => 200,
        'method' => 'transfer',
    ], [['document_id' => $bill->id, 'amount' => 200]]);

    $this->get(route('reports.statement', [
        'party_id' => $supplier->id, 'role' => 'supplier', 'from' => '2026-01-01', 'to' => '2026-12-31',
    ]))
        ->assertOk()
        ->assertSee('550.00')
        ->assertSee('350.00'); // closing: we still owe 350
});

it('runs a party statement balance', function () {
    $customer = Party::factory()->customer()->create();
    $invoice = postDoc('sales_invoice', $customer, [[1, 1000]], '2026-03-01'); // total 1100

    app(PaymentService::class)->record([
        'direction' => 'in',
        'party_id' => $customer->id,
        'payment_date' => '2026-03-15',
        'amount' => 400,
        'method' => 'cash',
    ], [['document_id' => $invoice->id, 'amount' => 400]]);

    $this->get(route('reports.statement', ['party_id' => $customer->id, 'from' => '2026-01-01', 'to' => '2026-12-31']))
        ->assertOk()
        ->assertSee('1,100.00')
        ->assertSee('400.00')
        ->assertSee('700.00'); // closing balance
});

it('nets output VAT against input VAT', function () {
    $customer = Party::factory()->customer()->create();
    $supplier = Party::factory()->supplier()->create();
    postDoc('sales_invoice', $customer, [[1, 1000]], '2026-06-01');   // output VAT 100
    postDoc('purchase_invoice', $supplier, [[1, 300]], '2026-06-05'); // input VAT 30

    $this->get(route('reports.vat-return', ['from' => '2026-06-01', 'to' => '2026-06-30']))
        ->assertOk()
        ->assertSee('100.00')
        ->assertSee('30.00')
        ->assertSee('70.00'); // net payable
});

it('computes gross margin from revenue, cogs and expenses', function () {
    $customer = Party::factory()->customer()->create();
    postDoc('sales_invoice', $customer, [[10, 100, 40]], '2026-07-01'); // revenue 1000, COGS 400
    Expense::factory()->create(['expense_date' => '2026-07-10', 'amount' => 150]);

    $this->get(route('reports.margin', ['from' => '2026-07-01', 'to' => '2026-07-31']))
        ->assertOk()
        ->assertSee('1,000.00') // revenue
        ->assertSee('400.00')   // COGS
        ->assertSee('600.00')   // gross profit
        ->assertSee('150.00')   // expenses
        ->assertSee('450.00');  // operating result
});
