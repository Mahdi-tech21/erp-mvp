<x-app-layout title="New Item">
    @include('items._form', ['action' => route('items.store'), 'method' => 'POST'])
</x-app-layout>
