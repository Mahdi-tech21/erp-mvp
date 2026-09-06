<x-app-layout :title="'New ' . $role['singular']">
    @include('parties._form', ['action' => route($role['route'] . '.store'), 'method' => 'POST'])
</x-app-layout>
