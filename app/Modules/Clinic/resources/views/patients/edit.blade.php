<x-app-layout :title="'Edit Patient — ' . $patient->party->name">
    @include('clinic::patients._form', ['action' => route('clinic.patients.update', $patient), 'method' => 'PUT'])
</x-app-layout>
