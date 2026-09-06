<x-app-layout title="Audit log">
    <x-filter-bar :action="route('audit.index')"
                  :reset="($q !== '' || $action !== '') ? route('audit.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search summary…" class="w-64" />
        <x-select name="action" class="w-44">
            <option value="">Any action</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
            @endforeach
        </x-select>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>When</x-th>
            <x-th>User</x-th>
            <x-th>Action</x-th>
            <x-th>Summary</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($logs as $log)
                <tr>
                    <x-td class="whitespace-nowrap text-gray-500">{{ $log->created_at->format('Y-m-d H:i') }}</x-td>
                    <x-td class="text-gray-600">{{ $log->user?->name ?? 'system' }}</x-td>
                    <x-td><span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-600">{{ $log->action }}</span></x-td>
                    <x-td class="text-gray-800">{{ $log->summary }}</x-td>
                </tr>
            @empty
                <x-empty :cols="4">No audit entries.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-app-layout>
