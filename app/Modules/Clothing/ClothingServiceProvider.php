<?php

namespace App\Modules\Clothing;

use App\Events\DocumentPosted;
use App\Events\DocumentVoided;
use App\Modules\Clothing\Listeners\MoveStockOnPost;
use App\Modules\Clothing\Listeners\ReverseStockOnVoid;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ClothingServiceProvider extends ServiceProvider
{
    public function boot(ModuleRegistry $registry): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'clothing');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $registry->addMenuItem('Stock', 'clothing.stock.index', 'Clothing', 'items', 10);
        $registry->addMenuItem('Stock on hand', 'clothing.reports.on-hand', 'Clothing', 'reports', 20);
        $registry->addMenuItem('Low stock', 'clothing.reports.low', 'Clothing', 'reports', 30);
        $registry->addSeeder(ClothingDemoSeeder::class);

        // Seam #2: the variant picker on every core document line.
        $registry->addLineField(
            partial: 'clothing::partials.line-fields',
            header: 'Variant',
            handler: function ($line, array $input): void {
                if (! empty($input['variant_id']) && ItemVariant::whereKey($input['variant_id'])->exists()) {
                    DocumentLineVariant::create([
                        'document_line_id' => $line->id,
                        'item_variant_id' => (int) $input['variant_id'],
                    ]);
                }
            },
            display: function ($line): ?string {
                $variant = DocumentLineVariant::with('variant')
                    ->where('document_line_id', $line->id)
                    ->first()?->variant;

                return $variant ? "{$variant->size} / {$variant->color}" : null;
            },
        );

        // Seam #3: stock moves in a listener, never inside DocumentService.
        Event::listen(DocumentPosted::class, MoveStockOnPost::class);
        Event::listen(DocumentVoided::class, ReverseStockOnVoid::class);
    }
}
