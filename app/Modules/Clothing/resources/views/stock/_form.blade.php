@php /** @var \App\Modules\Clothing\Models\ItemVariant $variant */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <x-field label="Item" name="item_id">
            <x-select name="item_id">
                <option value="">— select —</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}" @selected((int) old('item_id', $variant->item_id) === $item->id)>{{ $item->name }}</option>
                @endforeach
            </x-select>
        </x-field>

        <div class="grid grid-cols-3 gap-4">
            <x-field label="Size" name="size">
                <x-input type="text" name="size" :value="old('size', $variant->size)" placeholder="M" />
            </x-field>
            <x-field label="Colour" name="color">
                <x-input type="text" name="color" :value="old('color', $variant->color)" placeholder="Navy" />
            </x-field>
            <x-field label="SKU" name="sku">
                <x-input type="text" name="sku" :value="old('sku', $variant->sku)" />
            </x-field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Reorder level" name="reorder_level">
                <x-input type="number" min="0" name="reorder_level" :value="old('reorder_level', $variant->reorder_level ?? 0)" />
            </x-field>
            @unless ($variant->exists)
                <x-field label="Opening stock" name="opening_qty" hint="Writes an opening movement.">
                    <x-input type="number" min="0" name="opening_qty" :value="old('opening_qty', 0)" />
                </x-field>
            @endunless
        </div>

        @if ($variant->exists)
            <p class="text-xs text-gray-400">In stock now: {{ $variant->stock_qty }}. Change it from the Stock list's adjust box.</p>
        @endif
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route('clothing.stock.index')">Cancel</x-btn>
    </div>
</form>
