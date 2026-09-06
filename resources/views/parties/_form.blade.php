@php
    /** @var \App\Models\Party $party */
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none';
@endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label class="text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $party->name) }}" class="{{ $inputClass }}" autofocus>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $party->phone) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Email</label>
            <input type="text" name="email" value="{{ old('email', $party->email) }}" class="{{ $inputClass }}">
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Address</label>
        <textarea name="address" rows="2" class="{{ $inputClass }}">{{ old('address', $party->address) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Tax number</label>
            <input type="text" name="tax_number" value="{{ old('tax_number', $party->tax_number) }}" class="{{ $inputClass }}">
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $party->notes) }}</textarea>
    </div>

    <div class="space-y-2 border-t border-gray-100 pt-4">
        <p class="text-sm text-gray-500">
            This party is a <span class="font-medium text-gray-800">{{ $role['singular'] }}</span>.
        </p>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="{{ $otherRole['flag'] }}" value="1"
                   @checked(old($otherRole['flag'], $party->{$otherRole['flag']}))>
            Also a {{ strtolower($otherRole['singular']) }}
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $party->exists ? $party->is_active : true))>
            Active
        </label>
    </div>

    <div class="flex gap-2">
        <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
            Save
        </button>
        <a href="{{ route($role['route'] . '.index') }}"
           class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
            Cancel
        </a>
    </div>
</form>
