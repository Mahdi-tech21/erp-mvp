<x-app-layout title="Patients">
    <x-slot:actions>
        <x-btn :href="route('clinic.patients.create')"><x-icon name="plus" class="size-4" /> New Patient</x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route('clinic.patients.index')" :reset="$q !== '' ? route('clinic.patients.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search patients…" class="w-64" />
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>Name</x-th>
            <x-th>Date of birth</x-th>
            <x-th>Gender</x-th>
            <x-th right>Appointments</x-th>
            <x-th />
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($patients as $patient)
                <tr class="hover:bg-gray-50/70">
                    <x-td>
                        <a href="{{ route('clinic.patients.edit', $patient) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $patient->party->name }}
                        </a>
                    </x-td>
                    <x-td class="text-gray-500">{{ optional($patient->date_of_birth)->format('Y-m-d') ?? '—' }}</x-td>
                    <x-td class="capitalize text-gray-500">{{ $patient->gender ?? '—' }}</x-td>
                    <x-td num class="text-gray-500">{{ $patient->appointments_count }}</x-td>
                    <x-td right>
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('clinic.patients.edit', $patient) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                            <form method="POST" action="{{ route('clinic.patients.destroy', $patient) }}"
                                  onsubmit="return confirm('Delete this patient?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="5">No patients yet.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $patients->links() }}</div>
</x-app-layout>
