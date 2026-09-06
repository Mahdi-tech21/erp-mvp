@php
    /** @var \App\Models\Item $item */
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none';
@endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">SKU</label>
            <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" class="{{ $inputClass }}" autofocus>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Type</label>
            <select name="type" class="{{ $inputClass }}">
                <option value="product" @selected(old('type', $item->type) === 'product')>Product</option>
                <option value="service" @selected(old('type', $item->type) === 'service')>Service</option>
            </select>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" class="{{ $inputClass }}">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Unit price</label>
            <input type="number" step="0.01" min="0" name="unit_price"
                   value="{{ old('unit_price', $item->unit_price) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Cost price</label>
            <input type="number" step="0.01" min="0" name="cost_price"
                   value="{{ old('cost_price', $item->cost_price ?? 0) }}" class="{{ $inputClass }}">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $item->exists ? $item->is_active : true))>
        Active
    </label>

    <div class="flex gap-2">
        <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save</button>
        <a href="{{ route('items.index') }}"
           class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
    </div>
</form>
