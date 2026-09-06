@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &middot; {{ $company?->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-gray-900 antialiased">
<div class="flex min-h-full">

    <aside class="hidden w-60 flex-col border-r border-gray-200 bg-white lg:flex">
        <div class="flex h-16 items-center gap-2 border-b border-gray-100 px-5">
            <span class="flex size-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                {{ \Illuminate\Support\Str::of($company?->name ?? config('app.name'))->substr(0, 1)->upper() }}
            </span>
            <a href="{{ route('dashboard') }}" class="truncate text-sm font-semibold text-gray-900">
                {{ $company?->name ?? config('app.name') }}
            </a>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 text-sm">
            @foreach ($menu as $section)
                <div>
                    @if ($section['label'])
                        <p class="px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            {{ $section['label'] }}
                        </p>
                    @endif
                    <ul class="space-y-0.5">
                        @foreach ($section['items'] as $item)
                            @php $active = request()->routeIs($item['route']) || request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*'); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'flex items-center gap-2.5 rounded-lg px-3 py-2 font-medium transition',
                                       'bg-indigo-50 text-indigo-700' => $active,
                                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => ! $active,
                                   ])>
                                    <x-icon :name="$item['icon'] ?? 'dot'" @class(['text-indigo-600' => $active, 'text-gray-400' => ! $active]) />
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-gray-100 px-5 py-3 text-xs text-gray-400">
            @if ($company)
                VAT {{ rtrim(rtrim($company->tax_rate, '0'), '.') }}% &middot; {{ $company->currency }}
            @endif
        </div>

        @auth
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3 text-sm">
                <span class="truncate font-medium text-gray-700">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex items-center gap-1 text-gray-400 hover:text-gray-700">
                        <x-icon name="logout" class="size-4" /> Sign out
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-10 flex min-h-16 flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-white/80 px-4 py-3 backdrop-blur sm:px-6">
            <h1 class="text-lg font-semibold tracking-tight text-gray-900">{{ $title }}</h1>
            <div class="flex items-center gap-2">{{ $actions ?? '' }}</div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6">
            @if (session('status'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <x-icon name="dot" class="mt-0.5 size-4 text-emerald-500" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <x-icon name="dot" class="mt-0.5 size-4 text-red-500" />
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

</div>
@stack('scripts')
</body>
</html>
