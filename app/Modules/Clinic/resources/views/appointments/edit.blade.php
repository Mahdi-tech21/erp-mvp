<x-app-layout title="Edit Appointment">
    @include('clinic::appointments._form', ['action' => route('clinic.appointments.update', $appointment), 'method' => 'PUT'])
</x-app-layout>
