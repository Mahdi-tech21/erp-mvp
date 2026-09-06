<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Services\DocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * All sales-invoice and purchase-invoice CRUD. Children declare only
 * docType(); wording comes from config/documents.php.
 */
abstract class BaseDocumentController extends Controller
{
    /** 'sales_invoice' or 'purchase_invoice'. */
    abstract protected function docType(): string;

    public function __construct(protected DocumentService $documents) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $documents = Document::query()
            ->where('doc_type', $this->docType())
            ->with('party')
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('number', 'ilike', "%{$q}%")
                ->orWhereHas('party', fn ($p) => $p->where('name', 'ilike', "%{$q}%"))))
            ->when(in_array($status, ['draft', 'posted', 'partial', 'settled', 'void'], true),
                fn ($query) => $query->where('status', $status))
            ->orderByDesc('doc_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('documents.index', $this->withType([
            'documents' => $documents,
            'q' => $q,
            'status' => $status,
        ]));
    }

    public function create(): View
    {
        $document = new Document([
            'doc_type' => $this->docType(),
            'doc_date' => now()->toDateString(),
            'discount' => 0,
            'status' => 'draft',
        ]);
        $document->setRelation('lines', collect());

        return view('documents.form', $this->formData($document));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($error = $this->partyRoleError($data['party_id'])) {
            return back()->withInput()->with('error', $error);
        }

        try {
            $document = DB::transaction(function () use ($data) {
                $document = Document::create(array_merge(
                    ['doc_type' => $this->docType(), 'status' => 'draft'],
                    $this->headerData($data),
                ));

                $this->syncLines($document, $data['lines']);
                $this->documents->recalculateTotals($document);

                return $document;
            });
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($this->config('route').'.show', $document)
            ->with('status', $this->config('doc_singular').' draft saved.');
    }

    public function show(Document $document): View
    {
        $this->assertType($document);

        $document->load(['party', 'lines.item', 'allocations.payment']);

        return view('documents.show', $this->withType(['document' => $document]));
    }

    public function edit(Document $document): View|RedirectResponse
    {
        $this->assertType($document);

        if (! $document->isDraft()) {
            return redirect()
                ->route($this->config('route').'.show', $document)
                ->with('error', 'Only a draft '.strtolower($this->config('doc_singular')).' can be edited.');
        }

        $document->load('lines.item');

        return view('documents.form', $this->formData($document));
    }

    public function update(StoreDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->assertType($document);
        abort_unless($document->isDraft(), 403);

        $data = $request->validated();

        if ($error = $this->partyRoleError($data['party_id'])) {
            return back()->withInput()->with('error', $error);
        }

        try {
            DB::transaction(function () use ($data, $document) {
                $document->update($this->headerData($data));

                $this->syncLines($document, $data['lines']);
                $this->documents->recalculateTotals($document->load('lines'));
            });
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($this->config('route').'.show', $document)
            ->with('status', $this->config('doc_singular').' draft updated.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->assertType($document);

        if (! $document->isDraft()) {
            return back()->with('error', 'Only a draft can be deleted.');
        }

        $document->delete();

        return redirect()
            ->route($this->config('route').'.index')
            ->with('status', 'Draft deleted.');
    }

    public function print(Document $document): View
    {
        $this->assertType($document);

        $document->load(['party', 'lines']);

        return view('documents.print', $this->withType([
            'document' => $document,
            'company' => CompanySetting::current(),
        ]));
    }

    // -- helpers ------------------------------------------------------------

    protected function assertType(Document $document): void
    {
        abort_unless($document->doc_type === $this->docType(), 404);
    }

    protected function config(string $key): string
    {
        return config("documents.types.{$this->docType()}.{$key}");
    }

    protected function supportsExternalRef(): bool
    {
        return (bool) config("documents.types.{$this->docType()}.has_external_ref");
    }

    /**
     * Header fields shared by store and update. external_ref only for the
     * document types that declare it (purchase invoices).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function headerData(array $data): array
    {
        $header = [
            'party_id' => $data['party_id'],
            'doc_date' => $data['doc_date'],
            'due_date' => $data['due_date'] ?? null,
            'discount' => round((float) ($data['discount'] ?? 0), 2),
            'notes' => $data['notes'] ?? null,
        ];

        if ($this->supportsExternalRef()) {
            $header['external_ref'] = $data['external_ref'] ?? null;
        }

        return $header;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withType(array $data): array
    {
        return array_merge($data, ['type' => config("documents.types.{$this->docType()}")]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Document $document): array
    {
        return $this->withType([
            'document' => $document,
            'parties' => Party::query()
                ->where($this->config('party_flag'), true)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'items' => Item::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'unit_price']),
            'taxRate' => (float) CompanySetting::current()->tax_rate,
        ]);
    }

    private function partyRoleError(int $partyId): ?string
    {
        $ok = Party::whereKey($partyId)->where($this->config('party_flag'), true)->exists();

        return $ok ? null : 'The selected party is not a '.strtolower($this->config('party_singular')).'.';
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncLines(Document $document, array $lines): void
    {
        $document->lines()->delete();

        foreach (array_values($lines) as $i => $line) {
            $qty = round((float) $line['qty'], 3);
            $price = round((float) $line['unit_price'], 2);

            $document->lines()->create([
                'item_id' => $line['item_id'] ?? null,
                'description' => $line['description'],
                'qty' => $qty,
                'unit_price' => $price,
                'line_total' => round($qty * $price, 2),
                'sort_order' => $i,
            ]);
        }

        $document->load('lines');
    }
}
