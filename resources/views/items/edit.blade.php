<x-app-layout :title="'Edit Item — ' . $item->name">
    @include('items._form', ['action' => route('items.update', $item), 'method' => 'PUT'])
</x-app-layout>
