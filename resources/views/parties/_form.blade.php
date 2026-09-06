@php /** @var \App\Models\Party $party */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <x-field label="Name" name="name">
            <x-input type="text" name="name" :value="old('name', $party->name)" autofocus />
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Phone" name="phone">
                <x-input type="text" name="phone" :value="old('phone', $party->phone)" />
            </x-field>
            <x-field label="Email" name="email">
                <x-input type="text" name="email" :value="old('email', $party->email)" />
            </x-field>
        </div>

        <x-field label="Address" name="address">
            <x-textarea name="address" rows="2">{{ old('address', $party->address) }}</x-textarea>
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Tax number" name="tax_number">
                <x-input type="text" name="tax_number" :value="old('tax_number', $party->tax_number)" />
            </x-field>
        </div>

        <x-field label="Notes" name="notes">
            <x-textarea name="notes" rows="2">{{ old('notes', $party->notes) }}</x-textarea>
        </x-field>

        <div class="space-y-2 border-t border-gray-100 pt-4">
            <p class="text-sm text-gray-500">
                This party is a <span class="font-medium text-gray-800">{{ $role['singular'] }}</span>.
            </p>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="{{ $otherRole['flag'] }}" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       @checked(old($otherRole['flag'], $party->{$otherRole['flag']}))>
                Also a {{ strtolower($otherRole['singular']) }}
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       @checked(old('is_active', $party->exists ? $party->is_active : true))>
                Active
            </label>
        </div>
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route($role['route'] . '.index')">Cancel</x-btn>
    </div>
</form>
