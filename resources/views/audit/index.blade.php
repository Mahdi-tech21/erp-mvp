@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search summary&hellip;"
               class="w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <select name="action" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
            <option value="">Any action</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
            @endforeach
        </select>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Search</button>
        @if ($q !== '' || $action !== '')
            <a href="{{ route('audit.index') }}" class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">When</th>
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">Action</th>
                    <th class="px-4 py-2">Summary</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $log->user?->name ?? 'system' }}</td>
                        <td class="px-4 py-2"><span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-600">{{ $log->action }}</span></td>
                        <td class="px-4 py-2 text-gray-800">{{ $log->summary }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No audit entries.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
