<?php

namespace App\Modules\Clothing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function __invoke(Request $request, ItemVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'delta' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $delta = (int) $data['delta'];

        if ($variant->stock_qty + $delta < 0) {
            return back()->with('error', 'That adjustment would take stock below zero.');
        }

        DB::transaction(function () use ($variant, $delta, $data) {
            $variant = ItemVariant::query()->lockForUpdate()->find($variant->id);

            StockMovement::create([
                'item_variant_id' => $variant->id,
                'direction' => $delta > 0 ? 'in' : 'out',
                'qty' => abs($delta),
                'reference_type' => 'adjustment',
                'moved_at' => now(),
                'note' => $data['note'] ?? 'Manual adjustment',
            ]);

            $variant->update(['stock_qty' => $variant->stock_qty + $delta]);
        });

        return back()->with('status', 'Stock adjusted.');
    }
}
