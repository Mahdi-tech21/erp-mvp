<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Document;
use App\Models\Party;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Money in and money out - one table, one service. The direction comes from
 * the route (`payments.in.*` / `payments.out.*`); wording from
 * config/payments.php.
 */
class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(Request $request): View
    {
        $direction = (string) $request->query('direction', '');
        $q = trim((string) $request->query('q', ''));

        $payments = Payment::query()
            ->with('party')
            ->withSum('allocations as allocated_total', 'amount')
            ->when(in_array($direction, ['in', 'out'], true), fn ($query) => $query->where('direction', $direction))
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('reference', 'ilike', "%{$q}%")
                ->orWhereHas('party', fn ($p) => $p->where('name', 'ilike', "%{$q}%"))))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments', 'direction', 'q'));
    }

    public function create(Request $request): View
    {
        $direction = (string) $request->route('direction');
        $config = config("payments.directions.{$direction}");

        $parties = Party::query()
            ->where($config['party_flag'], true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $party = $request->filled('party_id')
            ? $parties->firstWhere('id', (int) $request->query('party_id'))
            : null;

        $openDocuments = $party
            ? Document::query()
                ->where('doc_type', $config['doc_type'])
                ->where('party_id', $party->id)
                ->whereIn('status', ['posted', 'partial'])
                ->orderBy('doc_date')
                ->get()
                ->filter(fn (Document $d) => (float) $d->total - (float) $d->settled_total > 0.001)
                ->values()
            : collect();

        return view('payments.create', compact('direction', 'config', 'parties', 'party', 'openDocuments'));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $direction = (string) $request->route('direction');
        $config = config("payments.directions.{$direction}");
        $data = $request->validated();

        $rightRole = Party::whereKey($data['party_id'])->where($config['party_flag'], true)->exists();

        if (! $rightRole) {
            return back()->withInput()
                ->with('error', 'The selected party is not a '.strtolower($config['party_singular']).'.');
        }

        $allocations = collect($data['allocations'] ?? [])
            ->map(fn ($row) => ['document_id' => (int) $row['document_id'], 'amount' => (float) $row['amount']])
            ->all();

        try {
            $payment = $this->payments->record([
                'direction' => $direction,
                'party_id' => $data['party_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ], $allocations);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('payments.show', $payment)->with('status', $config['noun'].' recorded.');
    }

    public function show(Payment $payment): View
    {
        $payment->load('party', 'allocations.document');

        return view('payments.show', compact('payment'));
    }
}
