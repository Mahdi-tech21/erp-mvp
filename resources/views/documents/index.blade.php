<x-app-layout :title="$type['doc_plural']">
    <x-slot:actions>
        <x-btn :href="route($type['route'] . '.create')">
            <x-icon name="plus" class="size-4" /> New {{ $type['doc_singular'] }}
        </x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route($type['route'] . '.index')"
                  :reset="($q !== '' || $status !== '') ? route($type['route'] . '.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search number or {{ strtolower($type['party_singular']) }}…" class="w-64" />
        <x-select name="status" class="w-40">
            <option value="">Any status</option>
            @foreach (['draft', 'posted', 'partial', 'settled', 'void'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </x-select>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>Number</x-th>
            <x-th>Date</x-th>
            <x-th>{{ $type['party_singular'] }}</x-th>
            <x-th>Status</x-th>
            <x-th right>Total</x-th>
            <x-th right>Balance</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($documents as $document)
                <tr class="hover:bg-gray-50/70">
                    <x-td>
                        <a href="{{ route($type['route'] . '.show', $document) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $document->number ?? 'Draft #' . $document->id }}
                        </a>
                    </x-td>
                    <x-td class="text-gray-500">{{ $document->doc_date->format('Y-m-d') }}</x-td>
                    <x-td class="text-gray-600">{{ $document->party->name }}</x-td>
                    <x-td><x-status-badge :status="$document->status" /></x-td>
                    <x-td num>{{ number_format($document->total, 2) }}</x-td>
                    <x-td num class="text-gray-500">{{ number_format($document->total - $document->settled_total, 2) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="6">No {{ strtolower($type['doc_plural']) }} found.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $documents->links() }}</div>
</x-app-layout>
