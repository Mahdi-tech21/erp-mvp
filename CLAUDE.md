# ERP MVP — Project Rules

## What this is
A modular accounting ERP demo. Laravel + PostgreSQL + Blade. A shared **core**
(parties, items, sales & purchase documents, payments, reports) plus
**per-industry modules** that plug into it (clothing retail, doctor clinic).

This is a demo of software craftsmanship. Optimize for clean seams, readable
code, and working screens. Do NOT add multi-tenancy, queues, caching, API
layers, or a general ledger.

## Non-negotiables
- PHP 8.3+, Laravel 12, PostgreSQL 16, Blade + Tailwind. No Livewire, no Inertia, no Vue.
- No module package (`nwidart` etc). Modules are hand-rolled — the architecture
  is part of what is being demoed.
- Money is `decimal(12,2)`, cast `decimal:2` in Eloquent. Quantities are
  `decimal(12,3)`. Never float. Round at the point of storage, not display.
- Every schema change is a new migration. Never rewrite a migration that has run.
- Pest feature tests for every action that writes data.
- Thin controllers. Business rules live in `app/Services/`. Validation lives in
  FormRequest classes.

## PostgreSQL rules
The database is PostgreSQL, not MySQL. These differences are not optional:
- Search uses `ilike`, never `like`. `like` is case-sensitive here, so customer
  search will silently fail on capitalisation.
- Every non-aggregated column in a `SELECT` must appear in `GROUP BY`. Postgres
  rejects what MySQL tolerates — this hits the sales and purchases reports.
- JSON columns use `$table->jsonb()`, not `$table->json()`.
- Booleans are real booleans (`true`/`false`), not `0`/`1`. Never compare a
  boolean column to an integer in a raw query.
- Keep `$table->enum()` in migrations — Laravel renders it as a varchar with a
  check constraint, which is correct. Adding a value later needs a new migration
  that drops and recreates the constraint; do not try to `ALTER TYPE`.
- Date truncation in reports uses `date_trunc('day', doc_date)`, not `DATE()`.

## Controller pattern
`parties` and `documents` are each one table, but each gets an abstract parent
controller plus thin named children, so routes and file names use business words:

- `BasePartyController` (abstract) -> `CustomerController`, `SupplierController`
- `BaseDocumentController` (abstract) -> `SalesInvoiceController`, `PurchaseInvoiceController`

The parent holds every method. Each child declares only its role or doc type
(`role(): string`, `docType(): string`) — roughly six lines. Never duplicate a
CRUD method into a child. Views are shared; the labels ("Customer" vs "Supplier",
"Invoice" vs "Bill") come from a config map, not from hardcoded strings in Blade.

## The document model — read this before touching invoices
Sales invoices and purchase invoices are **one table**, `documents`, separated by
`doc_type` (`sales_invoice` | `purchase_invoice`). Payments are one table with a
`direction` (`in` | `out`). There is one `DocumentService` and one
`PaymentService` for both.

Do not create `invoices` and `purchase_invoices` tables. Do not fork the service.
If a rule differs by direction, branch on `doc_type` inside the service, once.

Statuses: `draft` -> `posted` -> (`partial` | `settled`), plus `void`.
- Only `draft` is editable or deletable.
- Posting recomputes totals, assigns the number, freezes the document, fires `DocumentPosted`.
- Numbers come from `NumberGenerator::next($docType)` using `lockForUpdate()` on
  the `sequences` row inside the transaction. Never `max(id)+1`.
- Voiding fires `DocumentVoided` *before* the status flips so listeners can veto
  by throwing. A document with payment allocations cannot be voided.
- A payment may only be allocated to documents of the matching direction and the
  same party, never beyond the remaining balance.
- Tax is a single company-level rate applied at header level:
  `total = subtotal - discount + tax`.

## Module architecture
```
app/Modules/Clothing/
  ClothingServiceProvider.php    # routes, views, migrations, menu, listeners
  Http/Controllers/
  Models/
  Services/
  Listeners/
  database/migrations/
  resources/views/               # namespaced: view('clothing::stock.index')
  routes.php
```

Rules:
- Enabled modules come from `ACTIVE_MODULES` (comma-separated) via
  `config/modules.php`. `App\Support\ModuleRegistry` boots only those.
- The core NEVER references a module. No `if ($module === 'clothing')` in core
  code, ever. If the core needs something, the module registers it.
- Three seams, and only three:
  1. **Menu** — `ModuleRegistry::menu()`.
  2. **Line fields** — a module registers a Blade partial the core line form renders,
     and stores its data in its own table (e.g. `document_line_variants`) keyed by
     `document_line_id`. `document_lines.meta` JSON is for lightweight scalars only.
  3. **Events** — `DocumentPosted`, `DocumentVoided`, `PaymentRecorded`. Stock moves
     in a listener, never inside `DocumentService`.
- A module owns its own tables and migrations. It may NOT add columns to core tables.

Correctness test: `ACTIVE_MODULES=` (empty) must leave a fully working core app
with no errors and no dead menu links. Check this after every module change.

## UI conventions
- One layout: `resources/views/layouts/app.blade.php`. Sidebar built from core
  menu merged with `ModuleRegistry::menu()`.
- Labels are per doc_type: "Invoice"/"Customer" for sales, "Bill"/"Supplier" for
  purchases. Keep them in a config map, not hardcoded in views.
- Tables get a search box and pagination. Nothing fancier.
- Flash messages via `session('status')` / `session('error')`.
- Tailwind through Laravel's stock Vite setup. No component library.

## Working agreement
- Before coding a new feature, state the plan in 3-5 bullets and wait for my go-ahead.
- After each feature: run `php artisan test` and `php artisan migrate:fresh --seed`.
  Report the result. Do not move on if either fails.
- Commit after each working feature, conventional-commit message.
- If something I ask conflicts with this file, say so before writing code.
- Work through `docs/BUILD_PLAN.md` one numbered step at a time. Do not run ahead.
- `docs/PROJECT_MAP.md` has the folder layout and every table definition. Follow
  it exactly — do not invent tables, columns, or a different folder structure.

## Commands you may run without asking
`php artisan *`, `composer *`, `npm run *`, `./vendor/bin/pest`, `git status`,
`git diff`, `git add`, `git commit`, `psql -d erp_mvp -c "..."`.
Ask first: `git push`, `migrate:fresh` on a non-test database, deleting files,
installing new composer packages.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.2. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
