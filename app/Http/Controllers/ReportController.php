<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Party;
use App\Services\Reports\AgingReport;
use App\Services\Reports\MarginReport;
use App\Services\Reports\StatementReport;
use App\Services\Reports\VatReturnReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function __construct(
        private AgingReport $aging,
        private StatementReport $statement,
        private VatReturnReport $vat,
        private MarginReport $margin,
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function sales(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.period', [
            'title' => 'Sales by period',
            'route' => 'reports.sales',
            'partyLabel' => 'invoices',
            'rows' => $this->periodRows('sales_invoice', $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function purchases(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.period', [
            'title' => 'Purchases by period',
            'route' => 'reports.purchases',
            'partyLabel' => 'bills',
            'rows' => $this->periodRows('purchase_invoice', $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function arAging(Request $request): View
    {
        return view('reports.aging', [
            'title' => 'A/R aging',
            'partyLabel' => 'Customer',
            'route' => 'reports.ar-aging',
            'report' => $this->aging->forSales($request->date('as_of')),
        ]);
    }

    public function apAging(Request $request): View
    {
        return view('reports.aging', [
            'title' => 'A/P aging',
            'partyLabel' => 'Supplier',
            'route' => 'reports.ap-aging',
            'report' => $this->aging->forPurchases($request->date('as_of')),
        ]);
    }

    public function statement(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $parties = Party::query()->orderBy('name')->get(['id', 'name', 'is_customer', 'is_supplier']);
        $party = $request->filled('party_id') ? $parties->firstWhere('id', $request->integer('party_id')) : null;

        $role = $request->query('role')
            ?: ($party && ! $party->is_customer && $party->is_supplier ? 'supplier' : 'customer');

        return view('reports.statement', [
            'parties' => $parties,
            'party' => $party,
            'role' => $role,
            'from' => $from,
            'to' => $to,
            'statement' => $party ? $this->statement->build($party, $role, $from, $to) : null,
        ]);
    }

    public function vatReturn(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.vat-return', ['report' => $this->vat->build($from, $to), 'from' => $from, 'to' => $to]);
    }

    public function margin(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.margin', ['report' => $this->margin->build($from, $to), 'from' => $from, 'to' => $to]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->date('from') ?? Carbon::now()->startOfYear();
        $to = $request->date('to') ?? Carbon::now();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    /**
     * @return Collection<int, object>
     */
    private function periodRows(string $docType, Carbon $from, Carbon $to)
    {
        return Document::query()
            ->where('doc_type', $docType)
            ->where('status', '!=', 'void')
            ->whereBetween('doc_date', [$from, $to])
            ->selectRaw("date_trunc('day', doc_date) as day, count(*) as count, sum(subtotal - discount) as net, sum(tax_amount) as vat, sum(total) as total")
            ->groupByRaw("date_trunc('day', doc_date)")
            ->orderByRaw("date_trunc('day', doc_date)")
            ->get();
    }
}
