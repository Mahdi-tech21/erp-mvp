<x-print-layout :title="$type['doc_singular']"
                :subtitle="($document->number ?? 'DRAFT') . ' · ' . $document->doc_date->format('Y-m-d')">

    <div class="mb-6 text-sm">
        <div class="text-xs uppercase tracking-wide text-gray-400">{{ $type['party_singular'] }}</div>
        <div class="font-medium text-gray-900">{{ $document->party->name }}</div>
        <div class="whitespace-pre-line text-xs text-gray-500">{{ $document->party->address }}</div>
        @if ($document->party->tax_number)
            <div class="text-xs text-gray-500">Tax #: {{ $document->party->tax_number }}</div>
        @endif
        @if ($type['has_external_ref'] && $document->external_ref)
            <div class="mt-1 text-xs text-gray-500">Ref: {{ $document->external_ref }}</div>
        @endif
    </div>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b-2 border-gray-800 text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="py-1.5 pr-2">Description</th>
                <th class="py-1.5 px-2 text-right">Qty</th>
                <th class="py-1.5 px-2 text-right">Unit price</th>
                <th class="py-1.5 pl-2 text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lines as $line)
                <tr class="border-b border-gray-200">
                    <td class="py-1.5 pr-2">{{ $line->description }}</td>
                    <td class="py-1.5 px-2 text-right tabular-nums">{{ rtrim(rtrim(number_format($line->qty, 3), '0'), '.') }}</td>
                    <td class="py-1.5 px-2 text-right tabular-nums">{{ number_format($line->unit_price, 2) }}</td>
                    <td class="py-1.5 pl-2 text-right tabular-nums">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="text-sm">
            <tr><td colspan="3" class="py-0.5 px-2 text-right text-gray-500">Subtotal</td><td class="py-0.5 pl-2 text-right tabular-nums">{{ number_format($document->subtotal, 2) }}</td></tr>
            @if ($document->discount > 0)
                <tr><td colspan="3" class="py-0.5 px-2 text-right text-gray-500">Discount</td><td class="py-0.5 pl-2 text-right tabular-nums">{{ number_format($document->discount, 2) }}</td></tr>
            @endif
            <tr><td colspan="3" class="py-0.5 px-2 text-right text-gray-500">Tax</td><td class="py-0.5 pl-2 text-right tabular-nums">{{ number_format($document->tax_amount, 2) }}</td></tr>
            <tr class="border-t-2 border-gray-800 font-bold">
                <td colspan="3" class="py-1 px-2 text-right">Total ({{ $company->currency }})</td>
                <td class="py-1 pl-2 text-right tabular-nums">{{ number_format($document->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($document->notes)
        <p class="mt-6 text-xs text-gray-500">{{ $document->notes }}</p>
    @endif
</x-print-layout>
