<?php

namespace App\Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Modules\Clinic\Http\Requests\StorePatientRequest;
use App\Modules\Clinic\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $patients = Patient::query()
            ->select('patients.*')
            ->join('parties', 'parties.id', '=', 'patients.party_id')
            ->with('party')
            ->withCount('appointments')
            ->when($q !== '', fn ($query) => $query->where('parties.name', 'ilike', "%{$q}%"))
            ->orderBy('parties.name')
            ->paginate(15)
            ->withQueryString();

        return view('clinic::patients.index', compact('patients', 'q'));
    }

    public function create(): View
    {
        return view('clinic::patients.create', ['patient' => new Patient]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $party = Party::create([
                'name' => $data['name'],
                'is_customer' => true,
                'is_active' => true,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            Patient::create([
                'party_id' => $party->id,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('clinic.patients.index')->with('status', 'Patient added.');
    }

    public function edit(Patient $patient): View
    {
        return view('clinic::patients.edit', ['patient' => $patient->load('party')]);
    }

    public function update(StorePatientRequest $request, Patient $patient): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $patient) {
            $patient->party->update([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            $patient->update([
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('clinic.patients.index')->with('status', 'Patient updated.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        if ($patient->party->documents()->exists() || $patient->party->payments()->exists()) {
            return back()->with('error', 'This patient has invoices or payments and cannot be deleted.');
        }

        $patient->party->delete(); // cascades to the patient and its appointments

        return redirect()->route('clinic.patients.index')->with('status', 'Patient deleted.');
    }
}
