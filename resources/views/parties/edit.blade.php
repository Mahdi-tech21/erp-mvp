@extends('layouts.app')

@section('title', 'Edit ' . $role['singular'] . ' — ' . $party->name)

@section('content')
    @include('parties._form', [
        'action' => route($role['route'] . '.update', $party),
        'method' => 'PUT',
    ])
@endsection
