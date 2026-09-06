<x-app-layout title="New Expense">
    @include('expenses._form', ['action' => route('expenses.store'), 'method' => 'POST'])
</x-app-layout>
