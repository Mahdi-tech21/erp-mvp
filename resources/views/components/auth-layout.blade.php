@props(['title' => 'Sign in'])

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full items-center justify-center bg-gray-100 px-4 text-gray-900 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex items-center justify-center gap-2">
            <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white">
                {{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}
            </span>
            <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
