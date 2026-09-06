@php /** @var \App\Modules\Clinic\Models\Patient $patient */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <x-field label="Name" name="name">
            <x-input type="text" name="name" :value="old('name', $patient->party?->name)" autofocus />
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Phone" name="phone">
                <x-input type="text" name="phone" :value="old('phone', $patient->party?->phone)" />
            </x-field>
            <x-field label="Email" name="email">
                <x-input type="text" name="email" :value="old('email', $patient->party?->email)" />
            </x-field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Date of birth" name="date_of_birth">
                <x-input type="date" name="date_of_birth" :value="old('date_of_birth', optional($patient->date_of_birth)->format('Y-m-d'))" />
            </x-field>
            <x-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">—</option>
                    <option value="male" @selected(old('gender', $patient->gender) === 'male')>Male</option>
                    <option value="female" @selected(old('gender', $patient->gender) === 'female')>Female</option>
                </x-select>
            </x-field>
        </div>

        <x-field label="Notes" name="notes">
            <x-textarea name="notes" rows="2">{{ old('notes', $patient->notes) }}</x-textarea>
        </x-field>
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route('clinic.patients.index')">Cancel</x-btn>
    </div>
</form>
