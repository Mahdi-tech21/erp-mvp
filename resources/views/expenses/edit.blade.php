@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
    @include('expenses._form', ['action' => route('expenses.update', $expense), 'method' => 'PUT'])
@endsection
