@php /** @var \App\Models\Item $item */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <x-field label="SKU" name="sku">
                <x-input type="text" name="sku" :value="old('sku', $item->sku)" autofocus />
            </x-field>
            <x-field label="Type" name="type">
                <x-select name="type">
                    <option value="product" @selected(old('type', $item->type) === 'product')>Product</option>
                    <option value="service" @selected(old('type', $item->type) === 'service')>Service</option>
                </x-select>
            </x-field>
        </div>

        <x-field label="Name" name="name">
            <x-input type="text" name="name" :value="old('name', $item->name)" />
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Unit price" name="unit_price">
                <x-input type="number" step="0.01" min="0" name="unit_price" :value="old('unit_price', $item->unit_price)" />
            </x-field>
            <x-field label="Cost price" name="cost_price">
                <x-input type="number" step="0.01" min="0" name="cost_price" :value="old('cost_price', $item->cost_price ?? 0)" />
            </x-field>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   @checked(old('is_active', $item->exists ? $item->is_active : true))>
            Active
        </label>
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route('items.index')">Cancel</x-btn>
    </div>
</form>
