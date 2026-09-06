<?php

namespace App\Support;

use App\Models\CompanySetting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use RuntimeException;

/**
 * Boots the enabled industry modules and assembles the sidebar.
 *
 * This is the one place the core knows modules exist. The core never names a
 * specific module; a module reaches back in through three seams only - the
 * menu (here), the document events, and the line-fields partial.
 */
class ModuleRegistry
{
    /** @var list<string> */
    protected array $active;

    /** @var array<string, class-string> */
    protected array $providers;

    /** @var list<array{label: string, route: string, section: ?string, icon: ?string, order: int}> */
    protected array $moduleItems = [];

    /** @var list<class-string> */
    protected array $seeders = [];

    public function __construct(protected Application $app)
    {
        $this->active = config('modules.active', []);
        $this->providers = config('modules.providers', []);
    }

    /**
     * Register the service provider of every active module.
     *
     * A module in ACTIVE_MODULES with no usable provider is a hard failure,
     * never a silent skip - a broken config should be loud.
     */
    public function boot(): void
    {
        foreach ($this->active as $module) {
            $provider = $this->providers[$module] ?? null;

            if ($provider === null || ! class_exists($provider)) {
                throw new RuntimeException(
                    "Module [{$module}] is listed in ACTIVE_MODULES but has no usable service provider."
                );
            }

            $this->app->register($provider);
        }
    }

    /** @return list<string> */
    public function active(): array
    {
        return $this->active;
    }

    public function isActive(string $module): bool
    {
        return in_array($module, $this->active, true);
    }

    /**
     * Seam #1: a module registers a sidebar entry from its service provider.
     */
    public function addMenuItem(string $label, string $route, ?string $section = null, ?string $icon = null, int $order = 100): void
    {
        $this->moduleItems[] = compact('label', 'route', 'section', 'icon', 'order');
    }

    /**
     * The full sidebar: core sections from config/menu.php merged with module
     * items. Any entry whose route does not exist yet is dropped, so there is
     * never a dead link no matter how far along the build is.
     *
     * @return list<array{label: ?string, items: list<array{label: string, route: string}>}>
     */
    public function menu(): array
    {
        $sections = [];

        foreach (config('menu.sections', []) as $section) {
            $key = $section['label'] ?? '';

            foreach ($section['items'] as $item) {
                if (Route::has($item['route'])) {
                    $sections[$key]['label'] = $section['label'] ?? null;
                    $sections[$key]['items'][] = [
                        'label' => $item['label'],
                        'route' => $item['route'],
                        'icon' => $item['icon'] ?? null,
                    ];
                }
            }
        }

        $moduleItems = $this->moduleItems;
        usort($moduleItems, fn ($a, $b) => $a['order'] <=> $b['order']);

        foreach ($moduleItems as $item) {
            if (! Route::has($item['route'])) {
                continue;
            }

            $key = $item['section'] ?? '';
            $sections[$key]['label'] ??= $item['section'];
            $sections[$key]['items'][] = [
                'label' => $item['label'],
                'route' => $item['route'],
                'icon' => $item['icon'] ?? null,
            ];
        }

        return array_values($sections);
    }

    /**
     * A module registers a demo-data seeder; DatabaseSeeder runs it after the
     * core demo data, only when the module is active.
     *
     * @param  class-string  $seeder
     */
    public function addSeeder(string $seeder): void
    {
        $this->seeders[] = $seeder;
    }

    /** @return list<class-string> */
    public function seeders(): array
    {
        return $this->seeders;
    }

    /**
     * The single company-settings row, or null before it is seeded.
     */
    public function company(): ?CompanySetting
    {
        return CompanySetting::query()->first();
    }
}
