<?php

use App\Models\Document;
use App\Models\Party;
use App\Modules\Clinic\Models\Patient;

beforeEach(fn () => asAdmin());

it('lists the patients screen', function () {
    $this->get(route('clinic.patients.index'))->assertOk();
});

it('creating a patient creates a linked customer party', function () {
    $this->post(route('clinic.patients.store'), [
        'name' => 'Jane Roe',
        'phone' => '555 0100',
        'gender' => 'female',
        'date_of_birth' => '1990-04-12',
    ])->assertRedirect(route('clinic.patients.index'));

    $patient = Patient::first();

    expect($patient)->not->toBeNull()
        ->and($patient->party->name)->toBe('Jane Roe')
        ->and($patient->party->is_customer)->toBeTrue()
        ->and($patient->gender)->toBe('female');
});

it('updates the patient and the underlying party together', function () {
    $party = Party::factory()->customer()->create(['name' => 'Old Name']);
    $patient = Patient::create(['party_id' => $party->id]);

    $this->put(route('clinic.patients.update', $patient), [
        'name' => 'New Name',
        'gender' => 'male',
        'status' => 'scheduled',
    ])->assertRedirect(route('clinic.patients.index'));

    expect($patient->party->fresh()->name)->toBe('New Name')
        ->and($patient->fresh()->gender)->toBe('male');
});

it('validates the name', function () {
    $this->post(route('clinic.patients.store'), ['name' => ''])->assertSessionHasErrors('name');
});

it('refuses to delete a patient who has invoices', function () {
    $party = Party::factory()->customer()->create();
    $patient = Patient::create(['party_id' => $party->id]);
    Document::factory()->create(['party_id' => $party->id]);

    $this->delete(route('clinic.patients.destroy', $patient))->assertSessionHas('error');

    expect(Patient::find($patient->id))->not->toBeNull();
});

it('deletes a patient with no history, cascading to the party', function () {
    $party = Party::factory()->customer()->create();
    $patient = Patient::create(['party_id' => $party->id]);

    $this->delete(route('clinic.patients.destroy', $patient))->assertRedirect(route('clinic.patients.index'));

    expect(Patient::find($patient->id))->toBeNull()
        ->and(Party::find($party->id))->toBeNull();
});
