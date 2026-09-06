<?php

namespace App\Modules\Clothing\Listeners;

use App\Events\DocumentPosted;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;

/**
 * When a document is posted: a purchase invoice moves stock IN, a sales
 * invoice moves it OUT - one movement per line that carries a variant.
 * Runs inside DocumentService::post()'s transaction.
 */
class MoveStockOnPost
{
    public function handle(DocumentPosted $event): void
    {
        $document = $event->document;

        if (! in_array($document->doc_type, ['sales_invoice', 'purchase_invoice'], true)) {
            return;
        }

        $direction = $document->doc_type === 'purchase_invoice' ? 'in' : 'out';
        $sign = $direction === 'in' ? 1 : -1;

        $document->loadMissing('lines');

        foreach ($document->lines as $line) {
            $variantId = DocumentLineVariant::where('document_line_id', $line->id)->value('item_variant_id');

            if ($variantId === null) {
                continue;
            }

            $qty = (int) round((float) $line->qty);
            $variant = ItemVariant::query()->lockForUpdate()->findOrFail($variantId);

            StockMovement::create([
                'item_variant_id' => $variant->id,
                'direction' => $direction,
                'qty' => $qty,
                'unit_cost' => $line->unit_price,
                'reference_type' => 'document',
                'reference_id' => $document->id,
                'moved_at' => now(),
                'note' => $document->number,
            ]);

            $variant->update(['stock_qty' => $variant->stock_qty + $sign * $qty]);
        }
    }
}
