<x-app-layout title="Reports">
    @php
        $reports = [
            ['reports.sales', 'Sales by period', 'Posted sales invoices by day, net and VAT.'],
            ['reports.purchases', 'Purchases by period', 'Posted bills by day, net and VAT.'],
            ['reports.ar-aging', 'A/R aging', 'What customers owe, by how overdue.'],
            ['reports.ap-aging', 'A/P aging', 'What we owe suppliers, by how overdue.'],
            ['reports.statement', 'Party statement', "One party's documents and payments, running balance."],
            ['reports.vat-return', 'VAT return', 'Output VAT minus input VAT for a period.'],
            ['reports.margin', 'Gross margin', 'Revenue minus cost of goods and expenses.'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as [$route, $name, $desc])
            <a href="{{ route($route) }}"
               class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:shadow">
                <div class="flex items-center gap-2 font-medium text-gray-900 group-hover:text-indigo-700">
                    <x-icon name="reports" class="size-4 text-indigo-500" /> {{ $name }}
                </div>
                <div class="mt-1 text-sm text-gray-500">{{ $desc }}</div>
            </a>
        @endforeach
    </div>
</x-app-layout>
