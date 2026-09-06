<x-app-layout :title="'Edit Variant — ' . $variant->sku">
    @include('clothing::stock._form', ['action' => route('clothing.stock.update', $variant), 'method' => 'PUT'])
</x-app-layout>
