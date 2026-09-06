<x-auth-layout title="Sign in">
    <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <x-field label="Email" name="email">
            <x-input type="email" name="email" :value="old('email')" required autofocus />
        </x-field>

        <x-field label="Password" name="password">
            <x-input type="password" name="password" required />
        </x-field>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Remember me
        </label>

        <x-btn class="w-full">Sign in</x-btn>
    </form>
</x-auth-layout>
