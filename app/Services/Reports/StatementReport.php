<?php

namespace App\Services\Reports;

use App\Models\Document;
use App\Models\Party;
use App\Models\Payment;
use Illuminate\Support\Carbon;

/**
 * One party's ledger for a date range: every document and payment on the
 * chosen side (customer = sales + receipts, supplier = bills + payments),
 * with an opening balance and a running balance.
 */
class StatementReport
{
    /**
     * @return array{party: Party, role: string, opening: float, rows: list<array<string, mixed>>, closing: float}
     */
    public function build(Party $party, string $role, Carbon $from, Carbon $to): array
    {
        $docType = $role === 'supplier' ? 'purchase_invoice' : 'sales_invoice';
        $direction = $role === 'supplier' ? 'out' : 'in';
        $docWord = $docType === 'sales_invoice' ? 'Invoice' : 'Bill';

        $events = Document::query()
            ->where('party_id', $party->id)
            ->where('doc_type', $docType)
            ->whereIn('status', ['posted', 'partial', 'settled'])
            ->get(['number', 'doc_date', 'total'])
            ->map(fn (Document $d) => [
                'date' => $d->doc_date,
                'label' => "{$docWord} {$d->number}",
                'charge' => (float) $d->total,
                'payment' => 0.0,
            ])
            ->concat(
                Payment::query()
                    ->where('party_id', $party->id)
                    ->where('direction', $direction)
                    ->get(['payment_date', 'amount', 'method', 'reference'])
                    ->map(fn (Payment $p) => [
                        'date' => $p->payment_date,
                        'label' => 'Payment · '.ucfirst($p->method).($p->reference ? " ({$p->reference})" : ''),
                        'charge' => 0.0,
                        'payment' => (float) $p->amount,
                    ])
            )
            ->sortBy('date')
            ->values();

        $opening = round(
            $events->filter(fn ($e) => $e['date']->lt($from))->sum(fn ($e) => $e['charge'] - $e['payment']),
            2,
        );

        $balance = $opening;
        $rows = [];

        foreach ($events->filter(fn ($e) => $e['date']->betweenIncluded($from, $to)) as $e) {
            $balance = round($balance + $e['charge'] - $e['payment'], 2);
            $rows[] = [...$e, 'balance' => $balance];
        }

        return [
            'party' => $party,
            'role' => $role,
            'opening' => $opening,
            'rows' => $rows,
            'closing' => $balance,
        ];
    }
}
