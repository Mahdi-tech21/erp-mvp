<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Demo data: 15 parties, 30 items, then a spread of posted sales invoices
 * and bills — some settled, some part-paid, some open, a couple voided, a
 * few left as drafts. Everything goes through DocumentService / PaymentService
 * so numbering, totals and status are real.
 */
class DemoDataSeeder extends Seeder
{
    public function __construct(
        private DocumentService $documents,
        private PaymentService $payments,
    ) {}

    public function run(): void
    {
        if (Party::query()->exists() || Item::query()->exists()) {
            return;
        }

        $both = Party::factory()->count(2)->both()->create();
        $customers = Party::factory()->count(9)->customer()->create()->merge($both);
        $suppliers = Party::factory()->count(4)->supplier()->create()->merge($both);

        $items = Item::factory()->count(24)->product()->create()
            ->merge(Item::factory()->count(6)->service()->create());

        $this->seedDocuments('sales_invoice', $customers, $items, count: 18, drafts: 3, voids: 2);
        $this->seedDocuments('purchase_invoice', $suppliers, $items->where('type', 'product'), count: 10, drafts: 2, voids: 1);
    }

    /**
     * @param  Collection<int, Party>  $parties
     * @param  Collection<int, Item>  $items
     */
    private function seedDocuments(string $type, Collection $parties, Collection $items, int $count, int $drafts, int $voids): void
    {
        $posted = collect();

        foreach (range(1, $count) as $i) {
            $document = $this->buildDraft($type, $parties->random(), $items);

            if ($i <= $drafts) {
                continue;
            }

            $this->documents->post($document->load('lines', 'party'));
            $posted->push($document->fresh());
        }

        $posted->take($voids)->each(fn (Document $d) => $this->documents->void($d));

        $posted->skip($voids)->each(fn (Document $d) => $this->maybePay($d));
    }

    /**
     * @param  Collection<int, Item>  $items
     */
    private function buildDraft(string $type, Party $party, Collection $items): Document
    {
        $docDate = fake()->dateTimeBetween('-100 days', '-2 days');

        $document = Document::create([
            'doc_type' => $type,
            'party_id' => $party->id,
            'doc_date' => $docDate,
            'due_date' => (clone $docDate)->modify('+30 days'),
            'discount' => 0,
            'external_ref' => $type === 'purchase_invoice' ? fake()->numerify('SUP-#####') : null,
            'notes' => fake()->boolean(20) ? fake()->sentence() : null,
            'status' => 'draft',
        ]);

        $lineSum = 0.0;

        foreach (range(1, fake()->numberBetween(1, 4)) as $n) {
            $item = $items->random();
            $qty = fake()->numberBetween(1, 8);
            $lineTotal = round($qty * (float) $item->unit_price, 2);
            $lineSum += $lineTotal;

            $document->lines()->create([
                'item_id' => $item->id,
                'description' => $item->name,
                'qty' => $qty,
                'unit_price' => $item->unit_price,
                'line_total' => $lineTotal,
                'sort_order' => $n,
            ]);
        }

        if (fake()->boolean(25)) {
            $document->update(['discount' => round(min($lineSum * 0.08, 60), 2)]);
        }

        return $document;
    }

    private function maybePay(Document $document): void
    {
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 35) {
            return; // left open
        }

        $amount = $roll <= 70
            ? (float) $document->total
            : round((float) $document->total * fake()->randomFloat(2, 0.2, 0.7), 2);

        $this->payments->record([
            'direction' => $document->doc_type === 'sales_invoice' ? 'in' : 'out',
            'party_id' => $document->party_id,
            'payment_date' => fake()->dateTimeBetween($document->doc_date, 'now')->format('Y-m-d'),
            'amount' => $amount,
            'method' => fake()->randomElement(['cash', 'card', 'transfer', 'cheque']),
            'reference' => fake()->boolean(60) ? fake()->numerify('PMT-#####') : null,
        ], [
            ['document_id' => $document->id, 'amount' => $amount],
        ]);
    }
}
