@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="text-sm font-semibold text-gray-900">Welcome</h2>
        <p class="mt-1 text-sm text-gray-600">
            The core accounting app is running. Use the sidebar to manage customers,
            suppliers, items, invoices and payments.
        </p>
        <p class="mt-4 text-xs text-gray-400">
            Summary tiles (receivables, payables, open documents) arrive in a later build step.
        </p>
    </div>
@endsection
