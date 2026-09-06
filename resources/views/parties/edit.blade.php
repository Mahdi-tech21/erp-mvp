<x-app-layout :title="'Edit ' . $role['singular'] . ' — ' . $party->name">
    @include('parties._form', ['action' => route($role['route'] . '.update', $party), 'method' => 'PUT'])
</x-app-layout>
