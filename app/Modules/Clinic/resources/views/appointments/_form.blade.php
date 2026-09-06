@php /** @var \App\Modules\Clinic\Models\Appointment $appointment */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <x-field label="Patient" name="patient_id">
            <x-select name="patient_id">
                <option value="">— select —</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" @selected((int) old('patient_id', $appointment->patient_id) === $p->id)>
                        {{ $p->party->name }}
                    </option>
                @endforeach
            </x-select>
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Doctor" name="doctor_name">
                <x-input type="text" name="doctor_name" :value="old('doctor_name', $appointment->doctor_name)" />
            </x-field>
            <x-field label="Status" name="status">
                <x-select name="status">
                    @foreach (['scheduled', 'done', 'cancelled', 'invoiced'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $appointment->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </x-select>
            </x-field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Starts at" name="starts_at">
                <x-input type="datetime-local" name="starts_at"
                         :value="old('starts_at', optional($appointment->starts_at)->format('Y-m-d\TH:i'))" />
            </x-field>
            <x-field label="Duration (minutes)" name="duration_minutes">
                <x-input type="number" min="5" max="480" step="5" name="duration_minutes"
                         :value="old('duration_minutes', $appointment->duration_minutes ?? 30)" />
            </x-field>
        </div>

        <x-field label="Service (optional — needed to invoice)" name="service_item_id">
            <x-select name="service_item_id">
                <option value="">—</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected((int) old('service_item_id', $appointment->service_item_id) === $service->id)>
                        {{ $service->name }} ({{ number_format($service->unit_price, 2) }})
                    </option>
                @endforeach
            </x-select>
        </x-field>

        <x-field label="Notes" name="notes">
            <x-textarea name="notes" rows="2">{{ old('notes', $appointment->notes) }}</x-textarea>
        </x-field>
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route('clinic.appointments.index')">Cancel</x-btn>
    </div>
</form>
