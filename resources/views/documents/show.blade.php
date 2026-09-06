@extends('layouts.app')

@section('title', ($document->number ?? 'Draft #' . $document->id))

@section('actions')
    <div class="flex gap-2">
        @if ($document->isDraft())
            <a href="{{ route($type['route'] . '.edit', $document) }}"
               class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Edit</a>
            <form method="POST" action="{{ route($type['route'] . '.post', $document) }}"
                  onsubmit="return confirm('Post this {{ strtolower($type['doc_singular']) }}? It can no longer be edited.')">
                @csrf
                <button class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">Post</button>
            </form>
            <form method="POST" action="{{ route($type['route'] . '.destroy', $document) }}"
                  onsubmit="return confirm('Delete this draft?')">
                @csrf @method('DELETE')
                <button class="rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @else
            <a href="{{ route($type['route'] . '.print', $document) }}" target="_blank"
               class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Print</a>
            @if ($document->status !== 'void' && $document->allocations->isEmpty())
                <form method="POST" action="{{ route($type['route'] . '.void', $document) }}"
                      onsubmit="return confirm('Void {{ $document->number }}?')">
                    @csrf
                    <button class="rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Void</button>
                </form>
            @endif
        @endif
    </div>
@endsection

@section('content')
    <div class="max-w-4xl space-y-6">
        <div class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-white p-4 text-sm">
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-400">{{ $type['party_singular'] }}</div>
                <div class="font-medium text-gray-900">{{ $document->party->name }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-wide text-gray-400">Status</div>
                @include('documents._status', ['status' => $document->status])
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
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Unit price</th>
                        <th class="px-4 py-2 text-right">Line total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($document->lines as $line)
                        <tr>
                            <td class="px-4 py-2">
                                {{ $line->description }}
                                @if ($line->item)<span class="ml-1 text-xs text-gray-400">{{ $line->item->sku }}</span>@endif
                            </td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ rtrim(rtrim(number_format($line->qty, 3), '0'), '.') }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($line->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($line->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="text-sm">
                    <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Subtotal</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->subtotal, 2) }}</td></tr>
                    <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Discount</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->discount, 2) }}</td></tr>
                    <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Tax</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->tax_amount, 2) }}</td></tr>
                    <tr class="font-semibold"><td colspan="3" class="px-4 py-1 text-right">Total</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->total, 2) }}</td></tr>
                    @if ($document->settled_total > 0)
                        <tr><td colspan="3" class="px-4 py-1 text-right text-gray-500">Settled</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->settled_total, 2) }}</td></tr>
                        <tr class="font-semibold"><td colspan="3" class="px-4 py-1 text-right">Balance</td><td class="px-4 py-1 text-right tabular-nums">{{ number_format($document->total - $document->settled_total, 2) }}</td></tr>
                    @endif
                </tfoot>
            </table>
        </div>

        @if ($document->allocations->isNotEmpty())
            <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm">
                <div class="mb-2 text-xs uppercase tracking-wide text-gray-400">Payments</div>
                <ul class="divide-y divide-gray-100">
                    @foreach ($document->allocations as $allocation)
                        <li class="flex justify-between py-1">
                            <span class="text-gray-600">
                                {{ $allocation->payment->payment_date->format('Y-m-d') }} &middot;
                                {{ ucfirst($allocation->payment->method) }}
                                @if ($allocation->payment->reference)({{ $allocation->payment->reference }})@endif
                            </span>
                            <span class="tabular-nums">{{ number_format($allocation->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($document->notes)
            <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
                {{ $document->notes }}
            </div>
        @endif
    </div>
@endsection
