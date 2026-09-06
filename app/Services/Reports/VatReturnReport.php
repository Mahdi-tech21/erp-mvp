<?php

namespace App\Services\Reports;

use App\Models\Document;
use Illuminate\Support\Carbon;

/**
 * VAT return for a date range: output VAT (non-void sales) minus input VAT
 * (non-void purchases) = net payable, with a month-by-month breakdown.
 */
class VatReturnReport
{
    /**
     * @return array{
     *     from: Carbon, to: Carbon,
     *     output: array{net: float, vat: float},
     *     input: array{net: float, vat: float},
     *     net_vat: float,
     *     months: list<array{month: string, output: float, input: float, net: float}>
     * }
     */
    public function build(Carbon $from, Carbon $to): array
    {
        $output = $this->totals('sales_invoice', $from, $to);
        $input = $this->totals('purchase_invoice', $from, $to);

        return [
            'from' => $from,
            'to' => $to,
            'output' => $output,
            'input' => $input,
            'net_vat' => round($output['vat'] - $input['vat'], 2),
            'months' => $this->monthly($from, $to),
        ];
    }

    /**
     * @return array{net: float, vat: float}
     */
    private function totals(string $docType, Carbon $from, Carbon $to): array
    {
        $row = Document::query()
            ->where('doc_type', $docType)
            ->whereNotIn('status', ['draft', 'void'])
            ->whereBetween('doc_date', [$from, $to])
            ->selectRaw('coalesce(sum(subtotal - discount), 0) as net, coalesce(sum(tax_amount), 0) as vat')
            ->first();

        return ['net' => round((float) $row->net, 2), 'vat' => round((float) $row->vat, 2)];
    }

    /**
     * @return list<array{month: string, output: float, input: float, net: float}>
     */
    private function monthly(Carbon $from, Carbon $to): array
    {
        $byMonth = fn (string $docType) => Document::query()
            ->where('doc_type', $docType)
            ->whereNotIn('status', ['draft', 'void'])
            ->whereBetween('doc_date', [$from, $to])
            ->selectRaw("to_char(date_trunc('month', doc_date), 'YYYY-MM') as month, sum(tax_amount) as vat")
            ->groupByRaw("to_char(date_trunc('month', doc_date), 'YYYY-MM')")
            ->pluck('vat', 'month');

        $output = $byMonth('sales_invoice');
        $input = $byMonth('purchase_invoice');

        return $output->keys()
            ->merge($input->keys())
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $m) => [
                'month' => $m,
                'output' => round((float) ($output[$m] ?? 0), 2),
                'input' => round((float) ($input[$m] ?? 0), 2),
                'net' => round((float) ($output[$m] ?? 0) - (float) ($input[$m] ?? 0), 2),
            ])
            ->all();
    }
}
