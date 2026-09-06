<?php

beforeEach(fn () => asAdmin());

it('redirects the root url to the dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});

it('renders the dashboard with the sidebar and KPI tiles', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Customers')
        ->assertSee('Receivables')
        ->assertSee('Open documents');
});

it('serves every core screen and has no dead menu links', function () {
    $this->seed();

    $routes = [
        'dashboard', 'customers.index', 'suppliers.index', 'items.index',
        'sales-invoices.index', 'sales-invoices.create',
        'purchase-invoices.index', 'purchase-invoices.create',
        'payments.index', 'payments.in.create', 'payments.out.create',
        'expenses.index', 'expenses.create', 'audit.index',
        'reports.index', 'reports.sales', 'reports.purchases',
        'reports.ar-aging', 'reports.ap-aging', 'reports.statement',
        'reports.vat-return', 'reports.margin',
    ];

    foreach ($routes as $name) {
        $this->get(route($name))->assertOk();
    }
});
