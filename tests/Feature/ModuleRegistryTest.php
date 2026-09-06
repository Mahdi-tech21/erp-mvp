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

it('drops a menu entry whose route does not exist', function () {
    config()->set('menu.sections', [
        ['label' => null, 'items' => [
            ['label' => 'Real', 'route' => 'dashboard'],
            ['label' => 'Ghost', 'route' => 'does.not.exist'],
        ]],
    ]);

    $routes = collect(app(ModuleRegistry::class)->menu())
        ->flatMap(fn ($section) => $section['items'])
        ->pluck('route');

    expect($routes)->toContain('dashboard')
        ->and($routes)->not->toContain('does.not.exist');
});

it('lets a module add a sidebar entry only once its route exists', function () {
    $registry = app(ModuleRegistry::class);
    $registry->addMenuItem('Stock', 'clothing.stock.index', 'Clothing');

    $routes = collect($registry->menu())->flatMap(fn ($s) => $s['items'])->pluck('route');

    expect($routes)->not->toContain('clothing.stock.index');
});
