<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clinic\Models\Appointment;
use App\Modules\Clinic\Models\Patient;
use App\Modules\Clothing\Models\ItemVariant;
use App\Services\DocumentService;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 0]);
});

it('shows the four core KPI tiles with real figures', function () {
    $customer = Party::factory()->customer()->create();
    $document = Document::factory()->sales()->create(['party_id' => $customer->id, 'discount' => 0]);
    DocumentLine::factory()->for($document)->create(['qty' => 1, 'unit_price' => 400, 'line_total' => 400]);
    app(DocumentService::class)->post($document->load('lines', 'party')); // receivables 400

    Expense::factory()->create(['expense_date' => now(), 'amount' => 120]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Receivables')
        ->assertSee('400.00')
        ->assertSee('Payables')
        ->assertSee('Open documents')
        ->assertSee('Expenses this month')
        ->assertSee('120.00');
});

it('shows the clinic tile with today\'s appointment count', function () {
    $patient = Patient::create(['party_id' => Party::factory()->customer()->create()->id]);
    Appointment::create(['patient_id' => $patient->id, 'doctor_name' => 'Dr A', 'starts_at' => now()->setTime(10, 0), 'status' => 'scheduled']);
    Appointment::create(['patient_id' => $patient->id, 'doctor_name' => 'Dr B', 'starts_at' => now()->addDay(), 'status' => 'scheduled']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee("Today's appointments");
});

it('shows the clothing tile with the low-stock count', function () {
    $item = Item::factory()->create(['type' => 'product']);
    ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Red', 'sku' => 'LOW', 'stock_qty' => 1, 'reorder_level' => 5]);
    ItemVariant::create(['item_id' => $item->id, 'size' => 'L', 'color' => 'Red', 'sku' => 'OK', 'stock_qty' => 40, 'reorder_level' => 5]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Low stock variants');
});
