@extends('layouts.app')

@section('title', 'New ' . $role['singular'])

@section('content')
    @include('parties._form', [
        'action' => route($role['route'] . '.store'),
        'method' => 'POST',
    ])
@endsection
