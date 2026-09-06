@extends('layouts.app')

@section('title', $role['plural'])

@section('actions')
    <a href="{{ route($role['route'] . '.create') }}"
       class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
        New {{ $role['singular'] }}
    </a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="q" value="{{ $q }}"
               placeholder="Search {{ strtolower($role['plural']) }}&hellip;"
               class="w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">
            Search
        </button>
        @if ($q !== '')
            <a href="{{ route($role['route'] . '.index') }}"
               class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Contact</th>
                    <th class="px-4 py-2">Tax number</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($parties as $party)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route($role['route'] . '.edit', $party) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $party->name }}
                            </a>
                            @if ($party->is_customer && $party->is_supplier)
                                <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">customer &amp; supplier</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ $party->email }}
                            @if ($party->email && $party->phone) &middot; @endif
                            {{ $party->phone }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $party->tax_number ?: '—' }}</td>
                        <td class="px-4 py-2">
                            @if ($party->is_active)
                                <span class="text-green-700">Active</span>
                            @else
                                <span class="text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route($role['route'] . '.edit', $party) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                            <form method="POST" action="{{ route($role['route'] . '.destroy', $party) }}" class="ml-2 inline"
                                  onsubmit="return confirm('Delete {{ addslashes($party->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            No {{ strtolower($role['plural']) }} found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $parties->links() }}
    </div>
@endsection
