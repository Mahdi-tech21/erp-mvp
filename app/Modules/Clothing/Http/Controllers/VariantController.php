<?php

namespace App\Modules\Clothing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Modules\Clothing\Http\Requests\StoreVariantRequest;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VariantController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $low = $request->boolean('low');

        $variants = ItemVariant::query()
            ->with('item:id,name')
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('sku', 'ilike', "%{$q}%")
                ->orWhereHas('item', fn ($i) => $i->where('name', 'ilike', "%{$q}%"))))
            ->when($low, fn ($query) => $query->whereColumn('stock_qty', '<=', 'reorder_level'))
            ->orderBy('sku')
            ->paginate(20)
            ->withQueryString();

        return view('clothing::stock.index', compact('variants', 'q', 'low'));
    }

    public function create(): View
    {
        return view('clothing::stock.create', [
            'variant' => new ItemVariant(['reorder_level' => 0]),
            'items' => $this->items(),
        ]);
    }

    public function store(StoreVariantRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $opening = (int) ($data['opening_qty'] ?? 0);

        DB::transaction(function () use ($data, $opening) {
            $variant = ItemVariant::create([
                'item_id' => $data['item_id'],
                'size' => $data['size'],
                'color' => $data['color'],
                'sku' => $data['sku'],
                'reorder_level' => $data['reorder_level'],
                'stock_qty' => $opening,
            ]);

            if ($opening > 0) {
                StockMovement::create([
                    'item_variant_id' => $variant->id,
                    'direction' => 'in',
                    'qty' => $opening,
                    'reference_type' => 'opening',
                    'moved_at' => now(),
                    'note' => 'Opening stock',
                ]);
            }
        });

        return redirect()->route('clothing.stock.index')->with('status', 'Variant added.');
    }

    public function edit(ItemVariant $variant): View
    {
        return view('clothing::stock.edit', [
            'variant' => $variant->load('item'),
            'items' => $this->items(),
        ]);
    }

    public function update(StoreVariantRequest $request, ItemVariant $variant): RedirectResponse
    {
        $data = $request->validated();

        $variant->update([
            'item_id' => $data['item_id'],
            'size' => $data['size'],
            'color' => $data['color'],
            'sku' => $data['sku'],
            'reorder_level' => $data['reorder_level'],
        ]);

        return redirect()->route('clothing.stock.index')->with('status', 'Variant updated.');
    }

    /**
     * @return Collection<int, Item>
     */
    private function items()
    {
        return Item::query()
            ->where('type', 'product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
