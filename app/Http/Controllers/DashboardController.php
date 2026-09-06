<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Expense;
use App\Support\ModuleRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(ModuleRegistry $modules): View
    {
        $receivable = (float) Document::query()
            ->where('doc_type', 'sales_invoice')
            ->whereIn('status', ['posted', 'partial'])
            ->sum(DB::raw('total - settled_total'));

        $payable = (float) Document::query()
            ->where('doc_type', 'purchase_invoice')
            ->whereIn('status', ['posted', 'partial'])
            ->sum(DB::raw('total - settled_total'));

        $openDocuments = Document::query()
            ->whereIn('status', ['draft', 'posted', 'partial'])
            ->count();

        $expensesThisMonth = (float) Expense::query()
            ->where('expense_date', '>=', now()->startOfMonth())
            ->sum('amount');

        $tiles = [
            ['label' => 'Receivables', 'value' => number_format($receivable, 2), 'hint' => 'Owed by customers', 'route' => 'sales-invoices.index', 'icon' => 'invoices'],
            ['label' => 'Payables', 'value' => number_format($payable, 2), 'hint' => 'Owed to suppliers', 'route' => 'purchase-invoices.index', 'icon' => 'bills'],
            ['label' => 'Open documents', 'value' => (string) $openDocuments, 'hint' => 'Drafts and unsettled', 'route' => null, 'icon' => 'reports'],
            ['label' => 'Expenses this month', 'value' => number_format($expensesThisMonth, 2), 'route' => 'expenses.index', 'icon' => 'expenses'],
        ];

        foreach ($modules->dashboardTiles() as $tile) {
            $tiles[] = $tile;
        }

        return view('dashboard', ['tiles' => $tiles]);
    }
}
