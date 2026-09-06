<x-app-layout title="New Patient">
    @include('clinic::patients._form', ['action' => route('clinic.patients.store'), 'method' => 'POST'])
</x-app-layout>
