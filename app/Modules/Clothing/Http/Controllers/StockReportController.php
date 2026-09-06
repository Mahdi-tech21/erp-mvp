<?php

namespace App\Modules\Clothing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use Illuminate\Contracts\View\View;

class StockReportController extends Controller
{
    public function onHand(): View
    {
        $variants = ItemVariant::query()
            ->with('item:id,name,cost_price')
            ->addSelect(['last_cost' => StockMovement::select('unit_cost')
                ->whereColumn('item_variant_id', 'item_variants.id')
                ->whereNotNull('unit_cost')
                ->orderByDesc('moved_at')
                ->orderByDesc('id')
                ->limit(1)])
            ->orderBy('sku')
            ->get()
            ->map(function (ItemVariant $variant) {
                $cost = (float) ($variant->last_cost ?? $variant->item->cost_price);

                return [
                    'variant' => $variant,
                    'qty' => $variant->stock_qty,
                    'unit_cost' => $cost,
                    'value' => round($variant->stock_qty * $cost, 2),
                ];
            });

        return view('clothing::reports.on-hand', [
            'rows' => $variants,
            'total_qty' => $variants->sum('qty'),
            'total_value' => round($variants->sum('value'), 2),
        ]);
    }

    public function lowStock(): View
    {
        $variants = ItemVariant::query()
            ->with('item:id,name')
            ->whereColumn('stock_qty', '<=', 'reorder_level')
            ->orderBy('stock_qty')
            ->orderBy('sku')
            ->get();

        return view('clothing::reports.low', compact('variants'));
    }
}
