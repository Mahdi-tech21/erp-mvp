<?php

use App\Support\ModuleRegistry;

it('boots cleanly when no modules are active', function () {
    config(['modules.active' => [], 'modules.providers' => []]);

    $registry = new ModuleRegistry($this->app);
    $registry->boot();

    expect($registry->active())->toBe([])
        ->and($registry->isActive('clothing'))->toBeFalse();
});

it('fails loudly when an active module has no provider', function () {
    config(['modules.active' => ['ghost'], 'modules.providers' => []]);

    $registry = new ModuleRegistry($this->app);

    expect(fn () => $registry->boot())
        ->toThrow(RuntimeException::class, 'Module [ghost]');
});

it('hides menu entries whose route does not exist yet', function () {
    $menu = app(ModuleRegistry::class)->menu();

    $routes = collect($menu)->flatMap(fn ($section) => $section['items'])->pluck('route');

    expect($routes)->toContain('dashboard')
        ->and($routes)->toContain('customers.index')
        ->and($routes)->not->toContain('reports.index');
});

it('lets a module add a sidebar entry only once its route exists', function () {
    $registry = app(ModuleRegistry::class);
    $registry->addMenuItem('Stock', 'clothing.stock.index', 'Clothing');

    $routes = collect($registry->menu())->flatMap(fn ($s) => $s['items'])->pluck('route');

    expect($routes)->not->toContain('clothing.stock.index');
});
