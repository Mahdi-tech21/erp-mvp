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

    @if (config('app.debug'))
        @php $modules = app(\App\Support\ModuleRegistry::class); @endphp
        <p class="mt-4 text-center text-xs text-gray-400">
            Demo &mdash; <span class="font-mono">admin@erp.test</span>
            @if ($modules->isActive('clinic')) &middot; <span class="font-mono">reception@erp.test</span> @endif
            @if ($modules->isActive('clothing')) &middot; <span class="font-mono">shopfloor@erp.test</span> @endif
            &middot; password <span class="font-mono">password</span>
        </p>
    @endif
</x-auth-layout>
