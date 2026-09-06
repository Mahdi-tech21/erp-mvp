<x-app-layout title="New Appointment">
    @include('clinic::appointments._form', ['action' => route('clinic.appointments.store'), 'method' => 'POST'])
</x-app-layout>
