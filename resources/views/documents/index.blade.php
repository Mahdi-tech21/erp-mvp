@extends('layouts.app')

@section('title', $type['doc_plural'])

@section('actions')
    <a href="{{ route($type['route'] . '.create') }}"
       class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
        New {{ $type['doc_singular'] }}
    </a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search number or {{ strtolower($type['party_singular']) }}&hellip;"
               class="w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
            <option value="">Any status</option>
            @foreach (['draft', 'posted', 'partial', 'settled', 'void'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Search</button>
        @if ($q !== '' || $status !== '')
            <a href="{{ route($type['route'] . '.index') }}" class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">Number</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">{{ $type['party_singular'] }}</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <a href="{{ route($type['route'] . '.show', $document) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $document->number ?? 'Draft #' . $document->id }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $document->doc_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $document->party->name }}</td>
                        <td class="px-4 py-2">@include('documents._status', ['status' => $document->status])</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($document->total, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums text-gray-500">
                            {{ number_format($document->total - $document->settled_total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            No {{ strtolower($type['doc_plural']) }} found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
@endsection
