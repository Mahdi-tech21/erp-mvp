<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; {{ $company?->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-100 text-gray-900 antialiased">
<div class="flex min-h-full">

    <aside class="flex w-60 flex-col border-r border-gray-200 bg-white">
        <div class="flex h-14 items-center border-b border-gray-200 px-4">
            <a href="{{ route('dashboard') }}" class="truncate text-sm font-semibold text-gray-900">
                {{ $company?->name ?? config('app.name') }}
            </a>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4 text-sm">
            @foreach ($menu as $section)
                <div>
                    @if ($section['label'])
                        <p class="px-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">
                            {{ $section['label'] }}
                        </p>
                    @endif
                    <ul class="space-y-0.5">
                        @foreach ($section['items'] as $item)
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'block rounded-md px-2 py-1.5 font-medium',
                                       'bg-gray-900 text-white' => request()->routeIs($item['route']),
                                       'text-gray-700 hover:bg-gray-100' => ! request()->routeIs($item['route']),
                                   ])>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-gray-200 px-4 py-3 text-xs text-gray-400">
            @if ($company)
                Tax {{ rtrim(rtrim($company->tax_rate, '0'), '.') }}% &middot; {{ $company->currency }}
            @else
                No company settings
            @endif
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex h-14 items-center justify-between border-b border-gray-200 bg-white px-6">
            <h1 class="text-base font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
            <div>@yield('actions')</div>
        </header>

        <main class="flex-1 px-6 py-6">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>
@stack('scripts')
</body>
</html>
