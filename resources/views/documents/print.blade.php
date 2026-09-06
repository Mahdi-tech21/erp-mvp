<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->number ?? 'Draft' }} — {{ $company->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font: 13px/1.5 -apple-system, Segoe UI, Roboto, sans-serif; color: #111; margin: 40px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        .muted { color: #666; }
        .head { display: flex; justify-content: space-between; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; text-align: left; }
        thead th { border-bottom: 2px solid #333; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        tbody td { border-bottom: 1px solid #ddd; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { padding: 3px 8px; }
        tfoot .total { font-weight: 700; border-top: 2px solid #333; }
        .actions { margin-bottom: 24px; }
        @media print { .actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="head">
        <div>
            <h1>{{ $company->name }}</h1>
            <div class="muted">{{ $company->address }}</div>
            <div class="muted">{{ $company->phone }}</div>
        </div>
        <div style="text-align:right">
            <h1>{{ $type['doc_singular'] }}</h1>
            <div>{{ $document->number ?? 'DRAFT' }}</div>
            <div class="muted">{{ $document->doc_date->format('Y-m-d') }}</div>
            @if ($type['has_external_ref'] && $document->external_ref)
                <div class="muted">Ref: {{ $document->external_ref }}</div>
            @endif
        </div>
    </div>

    <div>
        <strong>{{ $type['party_singular'] }}:</strong> {{ $document->party->name }}<br>
        <span class="muted">{{ $document->party->address }}</span>
        @if ($document->party->tax_number)<br><span class="muted">Tax #: {{ $document->party->tax_number }}</span>@endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($line->qty, 3), '0'), '.') }}</td>
                    <td class="num">{{ number_format($line->unit_price, 2) }}</td>
                    <td class="num">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="num muted">Subtotal</td><td class="num">{{ number_format($document->subtotal, 2) }}</td></tr>
            @if ($document->discount > 0)
                <tr><td colspan="3" class="num muted">Discount</td><td class="num">{{ number_format($document->discount, 2) }}</td></tr>
            @endif
            <tr><td colspan="3" class="num muted">Tax</td><td class="num">{{ number_format($document->tax_amount, 2) }}</td></tr>
            <tr class="total"><td colspan="3" class="num">Total ({{ $company->currency }})</td><td class="num">{{ number_format($document->total, 2) }}</td></tr>
        </tfoot>
    </table>

    @if ($document->notes)
        <p class="muted" style="margin-top:24px">{{ $document->notes }}</p>
    @endif
</body>
</html>
