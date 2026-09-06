<?php

namespace App\Services\Reports;

use App\Models\Document;
use Illuminate\Support\Carbon;

/**
 * A/R and A/P aging: per party, the outstanding balance of open documents
 * split into Current / 1-30 / 31-60 / 60+ days past due.
 */
class AgingReport
{
    /**
     * @return array{as_of: Carbon, rows: list<array<string, mixed>>, totals: array<string, float>}
     */
    public function forSales(?Carbon $asOf = null): array
    {
        return $this->build('sales_invoice', $asOf);
    }

    /**
     * @return array{as_of: Carbon, rows: list<array<string, mixed>>, totals: array<string, float>}
     */
    public function forPurchases(?Carbon $asOf = null): array
    {
        return $this->build('purchase_invoice', $asOf);
    }

    /**
     * @return array{as_of: Carbon, rows: list<array<string, mixed>>, totals: array<string, float>}
     */
    private function build(string $docType, ?Carbon $asOf): array
    {
        $asOf = ($asOf ?? Carbon::today())->startOfDay();

        $documents = Document::query()
            ->where('doc_type', $docType)
            ->whereIn('status', ['posted', 'partial'])
            ->with('party:id,name')
            ->get(['id', 'party_id', 'doc_date', 'due_date', 'total', 'settled_total']);

        $totals = ['current' => 0.0, 'b1' => 0.0, 'b2' => 0.0, 'b3' => 0.0];
        $rows = [];

        foreach ($documents as $doc) {
            $remaining = round((float) $doc->total - (float) $doc->settled_total, 2);

            if ($remaining <= 0.001) {
                continue;
            }

            $due = ($doc->due_date ?? $doc->doc_date)->startOfDay();
            $overdue = $due->greaterThanOrEqualTo($asOf) ? 0 : (int) $due->diffInDays($asOf);

            $bucket = match (true) {
                $overdue <= 0 => 'current',
                $overdue <= 30 => 'b1',
                $overdue <= 60 => 'b2',
                default => 'b3',
            };

            $name = $doc->party->name;
            $rows[$name] ??= ['party' => $name, 'current' => 0.0, 'b1' => 0.0, 'b2' => 0.0, 'b3' => 0.0, 'total' => 0.0];
            $rows[$name][$bucket] += $remaining;
            $rows[$name]['total'] += $remaining;
            $totals[$bucket] += $remaining;
        }

        ksort($rows);

        $round = fn (array $r): array => array_map(fn ($v) => is_float($v) ? round($v, 2) : $v, $r);

        return [
            'as_of' => $asOf,
            'rows' => array_values(array_map($round, $rows)),
            'totals' => $round($totals + ['total' => array_sum($totals)]),
        ];
    }
}
