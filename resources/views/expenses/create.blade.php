@extends('layouts.app')

@section('title', 'New Expense')

@section('content')
    @include('expenses._form', ['action' => route('expenses.store'), 'method' => 'POST'])
@endsection
