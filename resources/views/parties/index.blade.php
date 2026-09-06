<x-app-layout :title="$role['plural']">
    <x-slot:actions>
        <x-btn :href="route($role['route'] . '.create')">
            <x-icon name="plus" class="size-4" /> New {{ $role['singular'] }}
        </x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route($role['route'] . '.index')" :reset="$q !== '' ? route($role['route'] . '.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search {{ strtolower($role['plural']) }}…" class="w-64" />
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>Name</x-th>
            <x-th>Contact</x-th>
            <x-th>Tax number</x-th>
            <x-th>Status</x-th>
            <x-th />
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($parties as $party)
                <tr class="hover:bg-gray-50/70">
                    <x-td>
                        <a href="{{ route($role['route'] . '.edit', $party) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $party->name }}
                        </a>
                        @if ($party->is_customer && $party->is_supplier)
                            <x-badge class="ml-1">customer &amp; supplier</x-badge>
                        @endif
                    </x-td>
                    <x-td class="text-gray-500">
                        {{ $party->email }}@if ($party->email && $party->phone) &middot; @endif{{ $party->phone }}
                    </x-td>
                    <x-td class="text-gray-500">{{ $party->tax_number ?: '—' }}</x-td>
                    <x-td>
                        <x-badge :color="$party->is_active ? 'green' : 'gray'">{{ $party->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </x-td>
                    <x-td right>
                        <div class="flex justify-end gap-3">
                            <a href="{{ route($role['route'] . '.edit', $party) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                            <form method="POST" action="{{ route($role['route'] . '.destroy', $party) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($party->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="5">No {{ strtolower($role['plural']) }} found.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $parties->links() }}</div>
</x-app-layout>
