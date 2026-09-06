@extends('layouts.app')

@section('title', 'New Item')

@section('content')
    @include('items._form', ['action' => route('items.store'), 'method' => 'POST'])
@endsection
