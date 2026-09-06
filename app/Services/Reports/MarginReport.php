<?php

namespace App\Services\Reports;

use App\Models\Document;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Gross margin for a date range, without a general ledger:
 *   revenue (sales ex-VAT) - COGS - expenses = operating result.
 *
 * COGS uses each item's *current* cost_price, not a per-line snapshot - an
 * approximation, surfaced on the report itself.
 */
class MarginReport
{
    /**
     * @return array{
     *     from: Carbon, to: Carbon, revenue: float, cogs: float, gross_profit: float,
     *     gross_margin_pct: float|null, expenses: float, operating_result: float
     * }
     */
    public function build(Carbon $from, Carbon $to): array
    {
        $revenue = round((float) Document::query()
            ->where('doc_type', 'sales_invoice')
            ->whereNotIn('status', ['draft', 'void'])
            ->whereBetween('doc_date', [$from, $to])
            ->sum(DB::raw('subtotal - discount')), 2);

        $cogs = round((float) DB::table('document_lines')
            ->join('documents', 'documents.id', '=', 'document_lines.document_id')
            ->join('items', 'items.id', '=', 'document_lines.item_id')
            ->where('documents.doc_type', 'sales_invoice')
            ->whereNotIn('documents.status', ['draft', 'void'])
            ->whereBetween('documents.doc_date', [$from, $to])
            ->sum(DB::raw('document_lines.qty * items.cost_price')), 2);

        $expenses = round((float) Expense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount'), 2);

        $grossProfit = round($revenue - $cogs, 2);

        return [
            'from' => $from,
            'to' => $to,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $revenue > 0 ? round($grossProfit / $revenue * 100, 1) : null,
            'expenses' => $expenses,
            'operating_result' => round($grossProfit - $expenses, 2),
        ];
    }
}
