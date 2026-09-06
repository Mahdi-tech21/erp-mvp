<?php

namespace App\Modules\Clothing;

use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The clothing deployment's own sell side: a garment catalogue with size/colour
 * variants, opening stock, restocking purchases and a run of retail sales — all
 * posted through the core engine, so stock moves through the listeners and the
 * A/R, A/P and stock reports have real data.
 */
class ClothingDemoSeeder extends Seeder
{
    public function __construct(
        private DocumentService $documents,
        private PaymentService $payments,
    ) {}

    public function run(): void
    {
        if (ItemVariant::query()->exists()) {
            return;
        }

        $variants = $this->buildCatalogue();

        $suppliers = collect(['Textile Wholesale Ltd', 'Northern Fabric Co.'])
            ->map(fn ($name) => Party::factory()->supplier()->create(['name' => $name]));

        $customers = collect(range(1, 8))
            ->map(fn () => Party::factory()->customer()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
            ]))
            ->push(Party::factory()->customer()->create(['name' => 'High Street Boutique']));

        // Restocking purchases first — stock in.
        foreach (range(1, 4) as $ignored) {
            $this->postWithVariants('purchase_invoice', $suppliers->random(), $variants->random(fake()->numberBetween(3, 6)), 'in');
        }

        // Retail sales — stock out, settled in various states.
        foreach (range(1, 10) as $ignored) {
            $document = $this->postWithVariants('sales_invoice', $customers->random(), $variants->random(fake()->numberBetween(1, 3)), 'out');

            if ($document !== null) {
                $this->maybePay($document);
            }
        }
    }

    /**
     * @return Collection<int, ItemVariant>
     */
    private function buildCatalogue(): Collection
    {
        $catalogue = [
            'Cotton T-Shirt' => 18,
            'Denim Jacket' => 75,
            'Chino Trousers' => 45,
            'Wool Sweater' => 60,
            'Oxford Shirt' => 38,
        ];

        $sizes = ['S', 'M', 'L', 'XL'];
        $colors = ['Black', 'Navy', 'White', 'Olive'];
        $variants = collect();

        foreach ($catalogue as $name => $price) {
            $item = Item::firstOrCreate(
                ['sku' => 'CLO-'.strtoupper(substr(md5($name), 0, 6))],
                ['name' => $name, 'type' => 'product', 'unit_price' => $price, 'cost_price' => round($price * 0.45, 2), 'is_active' => true],
            );

            foreach (collect($sizes)->random(3) as $size) {
                foreach (collect($colors)->random(2) as $color) {
                    $opening = fake()->numberBetween(0, 40);

                    $variant = ItemVariant::create([
                        'item_id' => $item->id,
                        'size' => $size,
                        'color' => $color,
                        'sku' => $item->sku.'-'.$size.'-'.strtoupper(substr($color, 0, 2)),
                        'reorder_level' => fake()->randomElement([5, 8, 10]),
                        'stock_qty' => $opening,
                    ]);

                    if ($opening > 0) {
                        StockMovement::create([
                            'item_variant_id' => $variant->id,
                            'direction' => 'in',
                            'qty' => $opening,
                            'reference_type' => 'opening',
                            'moved_at' => now()->subDays(45),
                            'note' => 'Opening stock',
                        ]);
                    }

                    $variants->push($variant);
                }
            }
        }

        return $variants;
    }

    /**
     * @param  Collection<int, ItemVariant>  $variants
     */
    private function postWithVariants(string $type, Party $party, Collection $variants, string $direction): ?Document
    {
        $document = Document::create([
            'doc_type' => $type,
            'party_id' => $party->id,
            'doc_date' => now()->subDays(fake()->numberBetween(1, 60))->toDateString(),
            'status' => 'draft',
        ]);

        $lines = 0;

        foreach ($variants as $i => $variant) {
            $variant->refresh();

            if ($direction === 'out') {
                $qty = min(fake()->numberBetween(1, 3), (int) $variant->stock_qty);

                if ($qty < 1) {
                    continue;
                }

                $price = $variant->item->unit_price;
            } else {
                $qty = fake()->numberBetween(10, 30);
                $price = $variant->item->cost_price;
            }

            $line = $document->lines()->create([
                'item_id' => $variant->item_id,
                'description' => $variant->item->name,
                'qty' => $qty,
                'unit_price' => $price,
                'line_total' => round($qty * $price, 2),
                'sort_order' => $i,
            ]);

            DocumentLineVariant::create([
                'document_line_id' => $line->id,
                'item_variant_id' => $variant->id,
            ]);

            $lines++;
        }

        if ($lines === 0) {
            $document->delete();

            return null;
        }

        return $this->documents->post($document->load('lines', 'party'))->fresh();
    }

    private function maybePay(Document $document): void
    {
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 30) {
            return; // left open
        }

        $amount = $roll <= 70
            ? (float) $document->total
            : round((float) $document->total * fake()->randomFloat(2, 0.3, 0.6), 2);

        $this->payments->record([
            'direction' => $document->doc_type === 'sales_invoice' ? 'in' : 'out',
            'party_id' => $document->party_id,
            'payment_date' => Carbon::parse($document->doc_date)->toDateString(),
            'amount' => $amount,
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'reference' => null,
        ], [
            ['document_id' => $document->id, 'amount' => $amount],
        ]);
    }
}
