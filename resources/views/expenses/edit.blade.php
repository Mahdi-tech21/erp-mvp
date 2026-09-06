<x-app-layout title="Edit Expense">
    @include('expenses._form', ['action' => route('expenses.update', $expense), 'method' => 'PUT'])
</x-app-layout>
