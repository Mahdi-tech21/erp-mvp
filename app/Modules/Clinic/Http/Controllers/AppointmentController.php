<?php

namespace App\Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Modules\Clinic\Http\Requests\StoreAppointmentRequest;
use App\Modules\Clinic\Models\Appointment;
use App\Modules\Clinic\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date('date') ?? Carbon::today();

        $appointments = Appointment::query()
            ->with(['patient.party', 'serviceItem'])
            ->whereBetween('starts_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        return view('clinic::appointments.index', compact('appointments', 'date'));
    }

    public function create(): View
    {
        return view('clinic::appointments.create', $this->formData(new Appointment([
            'starts_at' => Carbon::today()->setHour(9),
            'duration_minutes' => 30,
            'status' => 'scheduled',
        ])));
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $appointment = Appointment::create($request->validated());

        return redirect()
            ->route('clinic.appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('status', 'Appointment booked.');
    }

    public function edit(Appointment $appointment): View
    {
        return view('clinic::appointments.edit', $this->formData($appointment));
    }

    public function update(StoreAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update($request->validated());

        return redirect()
            ->route('clinic.appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('status', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $date = $appointment->starts_at->toDateString();
        $appointment->delete();

        return redirect()->route('clinic.appointments.index', ['date' => $date])->with('status', 'Appointment removed.');
    }

    public function markDone(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status === 'scheduled') {
            $appointment->update(['status' => 'done']);
        }

        return back()->with('status', 'Appointment marked done.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Appointment $appointment): array
    {
        return [
            'appointment' => $appointment,
            'patients' => Patient::query()
                ->select('patients.*')
                ->join('parties', 'parties.id', '=', 'patients.party_id')
                ->with('party')
                ->orderBy('parties.name')
                ->get(),
            'services' => Item::query()
                ->where('type', 'service')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'unit_price']),
        ];
    }
}
