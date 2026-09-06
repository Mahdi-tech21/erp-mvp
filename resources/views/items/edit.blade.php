@extends('layouts.app')

@section('title', 'Edit Item — ' . $item->name)

@section('content')
    @include('items._form', ['action' => route('items.update', $item), 'method' => 'PUT'])
@endsection
