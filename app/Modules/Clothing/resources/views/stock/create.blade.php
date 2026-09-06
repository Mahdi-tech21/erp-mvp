<x-app-layout title="New Variant">
    @include('clothing::stock._form', ['action' => route('clothing.stock.store'), 'method' => 'POST'])
</x-app-layout>
