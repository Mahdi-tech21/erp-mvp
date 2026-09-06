@props(['title', 'subtitle' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} &middot; {{ $company?->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-white p-8 text-sm text-gray-900 antialiased">
    <div class="no-print mb-6 flex justify-end">
        <button onclick="window.print()"
                class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            Print
        </button>
    </div>

    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-start justify-between border-b border-gray-300 pb-4">
            <div>
                <div class="text-lg font-semibold">{{ $company?->name ?? config('app.name') }}</div>
                @if ($company?->address)
                    <div class="whitespace-pre-line text-xs text-gray-500">{{ $company->address }}</div>
                @endif
            </div>
            <div class="text-right">
                <div class="text-lg font-semibold">{{ $title }}</div>
                @if ($subtitle)
                    <div class="text-xs text-gray-500">{{ $subtitle }}</div>
                @endif
                <div class="text-xs text-gray-400">Generated {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
