<x-app-layout title="Appointments">
    <x-slot:actions>
        <x-btn :href="route('clinic.appointments.create')"><x-icon name="plus" class="size-4" /> New Appointment</x-btn>
    </x-slot:actions>

    <form method="GET" class="no-print mb-4 flex items-end gap-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Day</label>
            <x-input type="date" name="date" :value="$date->toDateString()" class="w-auto" />
        </div>
        <x-btn type="submit" variant="secondary" size="sm">Show</x-btn>
        <a href="{{ route('clinic.appointments.index', ['date' => now()->toDateString()]) }}"
           class="px-2 py-1.5 text-sm text-gray-500 hover:text-gray-800">Today</a>
    </form>

    <x-table>
        <x-slot:head>
            <x-th>Time</x-th>
            <x-th>Patient</x-th>
            <x-th>Doctor</x-th>
            <x-th>Service</x-th>
            <x-th>Status</x-th>
            <x-th />
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($appointments as $appointment)
                <tr class="hover:bg-gray-50/70">
                    <x-td class="whitespace-nowrap text-gray-600">{{ $appointment->starts_at->format('H:i') }}</x-td>
                    <x-td>
                        <a href="{{ route('clinic.appointments.edit', $appointment) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $appointment->patient->party->name }}
                        </a>
                    </x-td>
                    <x-td class="text-gray-600">{{ $appointment->doctor_name }}</x-td>
                    <x-td class="text-gray-500">{{ $appointment->serviceItem?->name ?? '—' }}</x-td>
                    <x-td>
                        <x-badge :color="match ($appointment->status) {
                            'done' => 'green', 'invoiced' => 'indigo', 'cancelled' => 'red', default => 'gray',
                        }">{{ ucfirst($appointment->status) }}</x-badge>
                    </x-td>
                    <x-td right>
                        <div class="flex items-center justify-end gap-2">
                            @if ($appointment->status === 'scheduled')
                                <form method="POST" action="{{ route('clinic.appointments.done', $appointment) }}">
                                    @csrf
                                    <button class="text-xs text-gray-500 hover:text-gray-900">Mark done</button>
                                </form>
                            @endif
                            @if ($appointment->canBeInvoiced())
                                <form method="POST" action="{{ route('clinic.appointments.invoice', $appointment) }}">
                                    @csrf
                                    <button class="rounded-md bg-indigo-600 px-2 py-1 text-xs font-medium text-white hover:bg-indigo-500">
                                        Create invoice
                                    </button>
                                </form>
                            @endif
                        </div>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="6">No appointments on {{ $date->format('D j M Y') }}.</x-empty>
            @endforelse
        </tbody>
    </x-table>
</x-app-layout>
