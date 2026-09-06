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
