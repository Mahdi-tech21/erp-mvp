<?php

namespace App\Modules\Clothing\Listeners;

use App\Events\DocumentVoided;
use App\Exceptions\DomainException;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;

/**
 * Voiding a posted document reverses its stock movements. Fires before the
 * status flips, so throwing here vetoes the void - which is what we do when
 * reversing a purchase would drive a variant's stock negative.
 */
class ReverseStockOnVoid
{
    public function handle(DocumentVoided $event): void
    {
        $document = $event->document;

        if (! in_array($document->doc_type, ['sales_invoice', 'purchase_invoice'], true)) {
            return;
        }

        // Original: purchase = in, sale = out.  Reversal is the opposite.
        $reverseDirection = $document->doc_type === 'purchase_invoice' ? 'out' : 'in';
        $sign = $reverseDirection === 'in' ? 1 : -1;

        $document->loadMissing('lines');

        foreach ($document->lines as $line) {
            $variantId = DocumentLineVariant::where('document_line_id', $line->id)->value('item_variant_id');

            if ($variantId === null) {
                continue;
            }

            $qty = (int) round((float) $line->qty);
            $variant = ItemVariant::query()->lockForUpdate()->findOrFail($variantId);
            $newQty = $variant->stock_qty + $sign * $qty;

            if ($newQty < 0) {
                throw new DomainException(
                    "Cannot void {$document->number}: it would drive {$variant->sku} stock below zero."
                );
            }

            StockMovement::create([
                'item_variant_id' => $variant->id,
                'direction' => $reverseDirection,
                'qty' => $qty,
                'unit_cost' => null, // a correction, not a real purchase
                'reference_type' => 'void',
                'reference_id' => $document->id,
                'moved_at' => now(),
                'note' => 'Void of '.$document->number,
            ]);

            $variant->update(['stock_qty' => $newQty]);
        }
    }
}
