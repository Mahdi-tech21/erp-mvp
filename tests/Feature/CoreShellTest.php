<?php

beforeEach(fn () => asAdmin());

it('redirects the root url to the dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});

it('renders the dashboard with the sidebar', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Customers')
        ->assertSee('Welcome');
});

it('runs with no modules enabled', function () {
    expect(config('modules.active'))->toBe([]);

    $this->get('/dashboard')->assertOk();
});

it('serves every core screen with no modules and no dead links', function () {
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
