<x-app-layout :title="$document->number ?? 'Draft #' . $document->id">
    <x-slot:actions>
        @if ($document->isDraft())
            <x-btn variant="secondary" :href="route($type['route'] . '.edit', $document)">Edit</x-btn>
            <form method="POST" action="{{ route($type['route'] . '.post', $document) }}"
                  onsubmit="return confirm('Post this {{ strtolower($type['doc_singular']) }}? It can no longer be edited.')">
                @csrf
                <x-btn>Post</x-btn>
            </form>
            <form method="POST" action="{{ route($type['route'] . '.destroy', $document) }}"
                  onsubmit="return confirm('Delete this draft?')">
                @csrf @method('DELETE')
                <x-btn variant="danger">Delete</x-btn>
            </form>
        @else
            <x-btn variant="secondary" :href="route($type['route'] . '.print', $document)" target="_blank">
                <x-icon name="print" class="size-4" /> Print
            </x-btn>
            @if ($document->status !== 'void' && $document->allocations->isEmpty())
                <form method="POST" action="{{ route($type['route'] . '.void', $document) }}"
                      onsubmit="return confirm('Void {{ $document->number }}?')">
                    @csrf
                    <x-btn variant="danger">Void</x-btn>
                </form>
            @endif
        @endif
    </x-slot:actions>

    <div class="max-w-4xl space-y-6">
        <x-card class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-400">{{ $type['party_singular'] }}</div>
                <div class="font-medium text-gray-900">{{ $document->party->name }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-wide text-gray-400">Status</div>
                <x-status-badge :status="$document->status" />
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-400">Date</div>
                {{ $document->doc_date->format('Y-m-d') }}
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-wide text-gray-400">Due</div>
                {{ optional($document->due_date)->format('Y-m-d') ?? '—' }}
            </div>
            @if ($type['has_external_ref'] && $document->external_ref)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ $type['party_singular'] }}'s invoice no.</div>
                    {{ $document->external_ref }}
                </div>
            @endif
        </x-card>

        <x-table>
            <x-slot:head>
                <x-th>Description</x-th>
                <x-th right>Qty</x-th>
                <x-th right>Unit price</x-th>
                <x-th right>Line total</x-th>
            </x-slot:head>
            <tbody class="divide-y divide-gray-100">
                @foreach ($document->lines as $line)
                    <tr>
                        <x-td>
                            {{ $line->description }}
                            @if ($line->item)<span class="ml-1 text-xs text-gray-400">{{ $line->item->sku }}</span>@endif
                        </x-td>
                        <x-td num>{{ rtrim(rtrim(number_format($line->qty, 3), '0'), '.') }}</x-td>
                        <x-td num>{{ number_format($line->unit_price, 2) }}</x-td>
                        <x-td num>{{ number_format($line->line_total, 2) }}</x-td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="text-sm text-gray-600">
                <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Subtotal</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->subtotal, 2) }}</td></tr>
                <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Discount</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->discount, 2) }}</td></tr>
                <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Tax</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->tax_amount, 2) }}</td></tr>
                <tr class="font-semibold text-gray-900"><td colspan="3" class="px-4 py-1 text-right">Total</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->total, 2) }}</td></tr>
                @if ($document->settled_total > 0)
                    <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Settled</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->settled_total, 2) }}</td></tr>
                    <tr class="font-semibold text-gray-900"><td colspan="3" class="px-4 py-1 text-right">Balance</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->total - $document->settled_total, 2) }}</td></tr>
                @endif
            </tfoot>
        </x-table>

        @if ($document->allocations->isNotEmpty())
            <x-card>
                <div class="mb-2 text-xs uppercase tracking-wide text-gray-400">Payments</div>
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($document->allocations as $allocation)
                        <li class="flex justify-between py-1.5">
                            <span class="text-gray-600">
                                {{ $allocation->payment->payment_date->format('Y-m-d') }} &middot;
                                {{ ucfirst($allocation->payment->method) }}@if ($allocation->payment->reference) ({{ $allocation->payment->reference }})@endif
                            </span>
                            <span class="tabular-nums">{{ number_format($allocation->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        @if ($document->notes)
            <x-card class="text-sm text-gray-600">{{ $document->notes }}</x-card>
        @endif
    </div>
</x-app-layout>
