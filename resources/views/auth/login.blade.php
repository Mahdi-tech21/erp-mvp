<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full items-center justify-center bg-gray-100 text-gray-900 antialiased">
    <div class="w-full max-w-sm">
        <h1 class="mb-6 text-center text-lg font-semibold">{{ config('app.name') }}</h1>

        <form method="POST" action="{{ route('login') }}"
              class="space-y-4 rounded-lg border border-gray-200 bg-white p-6">
            @csrf

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <button class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>
