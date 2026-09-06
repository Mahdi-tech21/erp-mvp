<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clinic\Models\Appointment;
use App\Modules\Clinic\Models\Patient;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 10]);
});

function patient(): Patient
{
    return Patient::create(['party_id' => Party::factory()->customer()->create()->id]);
}

it('shows the day list filtered by date', function () {
    $p = patient();
    $today = Appointment::create(['patient_id' => $p->id, 'doctor_name' => 'Dr A', 'starts_at' => now()->setTime(10, 0), 'status' => 'scheduled']);
    $tomorrow = Appointment::create(['patient_id' => $p->id, 'doctor_name' => 'Dr B', 'starts_at' => now()->addDay()->setTime(10, 0), 'status' => 'scheduled']);

    $this->get(route('clinic.appointments.index'))
        ->assertOk()
        ->assertSee('Dr A')
        ->assertDontSee('Dr B');

    $this->get(route('clinic.appointments.index', ['date' => now()->addDay()->toDateString()]))
        ->assertSee('Dr B')
        ->assertDontSee('Dr A');
});

it('books an appointment', function () {
    $p = patient();
    $service = Item::factory()->service()->create();

    $this->post(route('clinic.appointments.store'), [
        'patient_id' => $p->id,
        'doctor_name' => 'Dr Novak',
        'starts_at' => now()->setTime(14, 30)->format('Y-m-d\TH:i'),
        'duration_minutes' => 30,
        'service_item_id' => $service->id,
        'status' => 'scheduled',
    ])->assertRedirect();

    expect(Appointment::where('doctor_name', 'Dr Novak')->exists())->toBeTrue();
});

it('marks an appointment done', function () {
    $appointment = Appointment::create([
        'patient_id' => patient()->id, 'doctor_name' => 'Dr A', 'starts_at' => now(), 'status' => 'scheduled',
    ]);

    $this->post(route('clinic.appointments.done', $appointment))->assertRedirect();

    expect($appointment->fresh()->status)->toBe('done');
});

it('creates a correct draft invoice from an appointment', function () {
    $p = patient();
    $service = Item::factory()->service()->create(['name' => 'Consultation', 'unit_price' => 80]);

    $appointment = Appointment::create([
        'patient_id' => $p->id,
        'doctor_name' => 'Dr Salah',
        'starts_at' => now(),
        'service_item_id' => $service->id,
        'status' => 'done',
    ]);

    $this->post(route('clinic.appointments.invoice', $appointment))
        ->assertRedirect();

    $document = Document::latest('id')->first();

    expect($document->doc_type)->toBe('sales_invoice')
        ->and($document->status)->toBe('draft')
        ->and($document->party_id)->toBe($p->party_id)
        ->and($document->lines)->toHaveCount(1)
        ->and($document->lines->first()->item_id)->toBe($service->id)
        ->and($document->lines->first()->meta['appointment_id'])->toBe($appointment->id)
        ->and($document->subtotal)->toBe('80.00')
        ->and($document->tax_amount)->toBe('8.00')
        ->and($appointment->fresh()->status)->toBe('invoiced');
});

it('will not invoice an appointment twice', function () {
    $appointment = Appointment::create([
        'patient_id' => patient()->id,
        'doctor_name' => 'Dr A',
        'starts_at' => now(),
        'service_item_id' => Item::factory()->service()->create()->id,
        'status' => 'done',
    ]);

    $this->post(route('clinic.appointments.invoice', $appointment));
    $this->post(route('clinic.appointments.invoice', $appointment->fresh()))->assertSessionHas('error');

    expect(Document::count())->toBe(1);
});

it('will not invoice an appointment with no service', function () {
    $appointment = Appointment::create([
        'patient_id' => patient()->id, 'doctor_name' => 'Dr A', 'starts_at' => now(), 'status' => 'done',
    ]);

    $this->post(route('clinic.appointments.invoice', $appointment))->assertSessionHas('error');

    expect(Document::count())->toBe(0);
});
