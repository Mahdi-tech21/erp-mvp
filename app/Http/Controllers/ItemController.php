<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $items = Item::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('sku', 'ilike', "%{$q}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('items.index', compact('items', 'q'));
    }

    public function create(): View
    {
        $item = new Item(['type' => 'product', 'is_active' => true, 'cost_price' => 0]);

        return view('items.create', compact('item'));
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $item = Item::create($this->normalise($request->validated()));

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" created.");
    }

    public function edit(Item $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(StoreItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($this->normalise($request->validated()));

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" updated.");
    }

    public function destroy(Item $item): RedirectResponse
    {
        if ($item->documentLines()->exists()) {
            return back()->with('error', "Item \"{$item->name}\" is used on documents and cannot be deleted.");
        }

        $item->delete();

        return redirect()->route('items.index')->with('status', 'Item deleted.');
    }

    /**
     * Round money to 2dp at the point of storage.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalise(array $data): array
    {
        $data['unit_price'] = round((float) $data['unit_price'], 2);
        $data['cost_price'] = round((float) ($data['cost_price'] ?? 0), 2);

        return $data;
    }
}
