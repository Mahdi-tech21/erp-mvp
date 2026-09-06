@php
    /** Seam #2 partial — rendered inside every core document line. $i, $row. */
    $variants = \App\Modules\Clothing\Models\ItemVariant::query()->with('item:id,name')->orderBy('sku')->get();

    $current = old("lines.{$i}.variant_id")
        ?? (isset($row['variant_id']) ? $row['variant_id'] : null)
        ?? (isset($row['id'])
            ? \App\Modules\Clothing\Models\DocumentLineVariant::where('document_line_id', $row['id'])->value('item_variant_id')
            : null);
@endphp

<select name="lines[{{ $i }}][variant_id]"
        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <option value="">—</option>
    @foreach ($variants as $variant)
        <option value="{{ $variant->id }}" @selected((int) $current === $variant->id)>
            {{ $variant->item->name }} · {{ $variant->size }}/{{ $variant->color }} · {{ $variant->stock_qty }} in stock
        </option>
    @endforeach
</select>
