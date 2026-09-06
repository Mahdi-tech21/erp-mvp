<?php

namespace App\Modules\Clothing;

use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ClothingDemoSeeder extends Seeder
{
    public function __construct(private DocumentService $documents) {}

    public function run(): void
    {
        if (ItemVariant::query()->exists()) {
            return;
        }

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
                            'moved_at' => now()->subDays(30),
                            'note' => 'Opening stock',
                        ]);
                    }

                    $variants->push($variant);
                }
            }
        }

        $supplier = Party::factory()->supplier()->create(['name' => 'Textile Wholesale Ltd']);
        $customer = Party::factory()->customer()->create(['name' => 'High Street Boutique']);

        // A purchase that moves stock in, and a sale that moves it out.
        $this->postWithVariants('purchase_invoice', $supplier, $variants->random(4), 'in');
        $this->postWithVariants('sales_invoice', $customer, $variants->random(3), 'out');
    }

    /**
     * @param  Collection<int, ItemVariant>  $variants
     */
    private function postWithVariants(string $type, Party $party, $variants, string $direction): void
    {
        $document = Document::create([
            'doc_type' => $type,
            'party_id' => $party->id,
            'doc_date' => now()->subDays(fake()->numberBetween(1, 20)),
            'status' => 'draft',
        ]);

        foreach ($variants as $i => $variant) {
            $qty = $direction === 'out'
                ? min(fake()->numberBetween(1, 3), max($variant->stock_qty, 1))
                : fake()->numberBetween(10, 30);

            $price = $direction === 'out' ? $variant->item->unit_price : $variant->item->cost_price;

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
        }

        $this->documents->post($document->load('lines', 'party'));
    }
}
