# ERP MVP — Project Map

An outside look before you start: where everything lives, and every table you'll create.

---

## 1. Folder structure

`★` = a file or folder you (or Claude Code) create. Everything else ships with Laravel.

```
erp-mvp/
├── CLAUDE.md                                  ★ project rules, Claude Code reads this automatically
├── .env                                       ★ ACTIVE_MODULES=clothing
├── .env.testing                               ★ separate test database
├── docs/
│   └── BUILD_PLAN.md                          ★ the two-night plan
│
├── app/
│   ├── Models/                                ★ core models
│   │   ├── User.php
│   │   ├── CompanySetting.php
│   │   ├── Party.php
│   │   ├── Item.php
│   │   ├── Document.php
│   │   ├── DocumentLine.php
│   │   ├── Payment.php
│   │   ├── PaymentAllocation.php
│   │   ├── Expense.php
│   │   └── AuditLog.php
│   │
│   ├── Services/                              ★ all business logic lives here, not in controllers
│   │   ├── DocumentService.php                   post() / void() / recalculateTotals()
│   │   ├── PaymentService.php                    record() + allocations
│   │   ├── NumberGenerator.php                   INV-2026-0001 numbering
│   │   └── Reports/
│   │       ├── AgingReport.php                   A/R and A/P
│   │       ├── StatementReport.php               party statement
│   │       ├── VatReturnReport.php               output VAT − input VAT
│   │       └── MarginReport.php                  revenue − COGS − expenses
│   │
│   ├── Events/                                ★ the join between core and modules
│   │   ├── DocumentPosted.php
│   │   ├── DocumentVoided.php
│   │   └── PaymentRecorded.php
│   │
│   ├── Listeners/
│   │   └── AuditLogSubscriber.php             ★ writes audit_logs from the three events
│   │
│   ├── Exceptions/
│   │   └── DomainException.php                ★ broken business rule → session('error')
│   │
│   ├── Support/
│   │   └── ModuleRegistry.php                 ★ boots enabled modules, assembles the menu
│   │
│   ├── Http/
│   │   ├── Controllers/                       ★
│   │   │   ├── DashboardController.php
│   │   │   ├── AuthController.php                login / logout (all routes behind `auth`)
│   │   │   ├── BasePartyController.php           abstract, all party CRUD
│   │   │   ├── CustomerController.php            role() = 'customer'
│   │   │   ├── SupplierController.php            role() = 'supplier'
│   │   │   ├── ItemController.php
│   │   │   ├── BaseDocumentController.php        abstract, all document CRUD
│   │   │   ├── SalesInvoiceController.php        docType() = 'sales_invoice'
│   │   │   ├── PurchaseInvoiceController.php     docType() = 'purchase_invoice'
│   │   │   ├── DocumentActionController.php      post / void, both types
│   │   │   ├── PaymentController.php             direction from the route
│   │   │   ├── ExpenseController.php             plain CRUD, no engine
│   │   │   ├── AuditLogController.php            read-only /audit screen
│   │   │   └── ReportController.php
│   │   └── Requests/                          ★ input validation
│   │       ├── LoginRequest.php
│   │       ├── StorePartyRequest.php
│   │       ├── StoreItemRequest.php
│   │       ├── StoreDocumentRequest.php
│   │       ├── StorePaymentRequest.php
│   │       └── StoreExpenseRequest.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php             ★ Event::subscribe(AuditLogSubscriber)
│   │   └── ModuleServiceProvider.php          ★ calls ModuleRegistry::boot()
│   │
│   └── Modules/                               ★★ the whole point of the demo
│       ├── Clothing/
│       │   ├── ClothingServiceProvider.php       routes + views + migrations + menu + listeners
│       │   ├── routes.php
│       │   ├── Models/
│       │   │   ├── ItemVariant.php
│       │   │   └── StockMovement.php
│       │   ├── Http/Controllers/
│       │   │   ├── VariantController.php
│       │   │   ├── StockAdjustmentController.php
│       │   │   └── StockReportController.php
│       │   ├── Listeners/
│       │   │   ├── MoveStockOnPost.php           purchase = in, sale = out
│       │   │   └── ReverseStockOnVoid.php        refuses if it would go negative
│       │   ├── database/migrations/
│       │   └── resources/views/                  clothing::...
│       │       └── partials/line-fields.blade.php   ★ the extra field on the line form
│       │
│       └── Clinic/
│           ├── ClinicServiceProvider.php
│           ├── routes.php
│           ├── Models/
│           │   ├── Patient.php
│           │   └── Appointment.php
│           ├── Http/Controllers/
│           │   ├── PatientController.php
│           │   ├── AppointmentController.php
│           │   └── AppointmentInvoiceController.php   ★ appointment → draft invoice
│           ├── database/migrations/
│           └── resources/views/                  clinic::...
│
├── config/
│   ├── modules.php                            ★ reads ACTIVE_MODULES from .env
│   ├── menu.php                               ★ core sidebar as data
│   ├── parties.php                            ★ customer/supplier labels + flags
│   ├── documents.php                          ★ sales/purchase labels + party role
│   └── expenses.php                           ★ suggested expense categories
│
├── database/
│   ├── migrations/                            ★ core tables only
│   ├── factories/                             ★ PartyFactory, ItemFactory, DocumentFactory
│   └── seeders/
│       ├── DatabaseSeeder.php                 ★ admin user + company settings
│       └── DemoDataSeeder.php                 ★ 15 parties, 30 items, settled invoices
│
├── resources/views/
│   ├── layouts/app.blade.php                  ★ the only layout, sidebar from ModuleRegistry
│   ├── auth/login.blade.php                   ★ standalone, no sidebar
│   ├── dashboard.blade.php                    ★
│   ├── parties/                               ★ index / create / edit
│   ├── items/                                 ★
│   ├── documents/                             ★ index / form / show / print
│   ├── payments/                              ★
│   ├── expenses/                              ★ index / create / edit
│   ├── audit/                                 ★ index (read-only)
│   └── reports/                               ★ sales / purchases / ar-aging / ap-aging / statement / vat-return / margin
│
├── routes/web.php                             ★ core routes only, modules register their own
│
└── tests/Feature/                             ★
    ├── DocumentPostingTest.php
    ├── PaymentAllocationTest.php
    ├── ReportsTest.php
    ├── Clothing/StockMovementTest.php
    └── Clinic/AppointmentInvoiceTest.php
```

---

## 2. Step allocation — where each step happens

### Night 1 — the core

| # | Step | Main files |
|---|------|-----------|
| 1 | Create project and config | `.env`, `.env.testing`, `CLAUDE.md`, `docs/BUILD_PLAN.md` |
| 2 | Tables, models, demo data | `database/migrations/`, `app/Models/`, `database/seeders/` |
| 3 | Layout + module system with no modules | `ModuleRegistry.php`, `config/modules.php`, `layouts/app.blade.php` |
| 4 | Customers, suppliers, items | `BasePartyController` + `CustomerController` + `SupplierController`, `ItemController`, `views/parties`, `views/items` |
| 5 | Document engine | `NumberGenerator`, `DocumentService`, `PaymentService`, `app/Events/` |
| 6 | Sales invoices | `BaseDocumentController` + `SalesInvoiceController`, `DocumentActionController`, `views/documents/` |
| 6a | Login gate | `AuthController`, `LoginRequest`, `auth/login.blade.php`, `auth` middleware on all routes |
| 6b | Audit log | `audit_logs` migration, `AuditLog`, `AuditLogSubscriber`, `AuditLogController`, `views/audit/` |
| 7 | Purchase invoices | `PurchaseInvoiceController` — ~6 lines, everything else is inherited |
| 8 | Payments in and out | `PaymentController`, `views/payments/` |
| 9 | Expenses | `expenses` migration, `Expense`, `ExpenseController`, `StoreExpenseRequest`, `config/expenses.php`, `views/expenses/` |
| 10 | The seven reports | `ReportController`, `app/Services/Reports/`, `views/reports/` |
| 11 | Tests | `tests/Feature/DocumentPostingTest.php`, `PaymentAllocationTest.php`, `ReportsTest.php` |

### Night 2 — the modules

| # | Step | Main files |
|---|------|-----------|
| 1 | Clinic module (first — it's smaller) | all of `app/Modules/Clinic/` |
| 2 | Clothing module | all of `app/Modules/Clothing/` |
| 3 | Stock reports | `StockReportController` + `clothing::reports` |
| 4 | Module tests | `tests/Feature/Clothing/`, `tests/Feature/Clinic/` |
| 5 | Dashboard | `DashboardController` + tiles contributed by modules |
| 6 | Live module switch | `.env` → `ACTIVE_MODULES` |

**On the controller pairs:** `parties` and `documents` are each one table with one
set of rules, but they get two named controllers apiece so the routes, the menu,
and the file names all use the words the business uses. The abstract parent holds
every method; each child only declares which role or document type it is. Views
are shared, with the labels ("Customer" vs "Supplier", "Invoice" vs "Bill") coming
from a config map.

**The rule that matters:** the core knows nothing about the modules. If you find
yourself writing `clothing` or `clinic` anywhere inside `app/Services` or
`app/Http`, stop — the design has gone wrong.

---

## 3. Migrations — order matters

Order is driven by foreign keys: you can't create `documents` before `parties`.

### Core — `database/migrations/`

| # | Migration file | Table | Purpose |
|---|---|---|---|
| 1 | `0001_01_01_000000_create_users_table` | `users` | Laravel default, one user only |
| 2 | `..._create_company_settings_table` | `company_settings` | One row: company name, currency, VAT rate, number prefixes |
| 3 | `..._create_sequences_table` | `sequences` | Number counter per document type per year |
| 4 | `..._create_parties_table` | `parties` | Customers and suppliers in one table |
| 5 | `..._create_items_table` | `items` | Products and services |
| 6 | `..._create_documents_table` | `documents` | Sales and purchase invoices in one table |
| 7 | `..._create_document_lines_table` | `document_lines` | Invoice lines |
| 8 | `..._create_payments_table` | `payments` | Received (`in`) and paid (`out`) |
| 9 | `..._create_payment_allocations_table` | `payment_allocations` | Which payment settles which invoice |
| 10 | `..._create_audit_logs_table` | `audit_logs` | Append-only action log (step 6b) |
| 11 | `..._create_expenses_table` | `expenses` | Non-invoice spend (step 9) |
| 11b | `..._create_assistant_tables` | `assistant_conversations`, `assistant_messages` | Help-assistant chat history (step 6d) |

### Clothing — `app/Modules/Clothing/database/migrations/`

| # | Migration file | Table | Purpose |
|---|---|---|---|
| 12 | `..._create_item_variants_table` | `item_variants` | Size, colour, quantity on hand |
| 13 | `..._create_stock_movements_table` | `stock_movements` | Stock history |
| 14 | `..._create_document_line_variants_table` | `document_line_variants` | Links an invoice line to a variant |

### Clinic — `app/Modules/Clinic/database/migrations/`

| # | Migration file | Table | Purpose |
|---|---|---|---|
| 15 | `..._create_patients_table` | `patients` | Patient linked to a party |
| 16 | `..._create_appointments_table` | `appointments` | Appointments |

**How module migrations work:** the service provider calls
`loadMigrationsFrom(__DIR__.'/database/migrations')` only when the module is
enabled. So with `ACTIVE_MODULES=clinic`, the clothing tables are never created
in the first place.

---

## 4. Where reports come from

Reports have no tables of their own. Nothing is precomputed or stored — every
report is a query against the transaction tables at request time.

| Report | Reads from |
|---|---|
| Sales by period | `documents` where `doc_type = sales_invoice` and `status != void` |
| Purchases by period | `documents` where `doc_type = purchase_invoice` |
| A/R aging | `documents` (sales) + `payment_allocations`, bucketed by `due_date` |
| A/P aging | Same, purchase side |
| Party statement | `documents` + `payments` for one `party_id`, by date |
| VAT return | `documents` (non-void): Σ `tax_amount` sales − Σ `tax_amount` purchases, by `doc_date` range |
| Gross margin | `documents` + `document_lines` + `items.cost_price` + `expenses`, by date range |
| Stock on hand / valuation | `item_variants` + `stock_movements` (clothing module) |
| Low stock | `item_variants` where `stock_qty <= reorder_level` |

Note on naming: `documents` means *business documents* — invoices and bills —
not files or stored reports. It's the same idea as Odoo's `account.move` and
SAP's document tables: one table, many document types, one posting engine.

---

## 5. Table definitions

### company_settings
```
id, name, address, phone,
currency        varchar(3)     default 'USD'
tax_rate        decimal(5,2)   default 11.00
sales_prefix    varchar        default 'INV'
purchase_prefix varchar        default 'BILL'
timestamps
```

### sequences
```
id, key varchar, year smallint, next_number int default 1, timestamps
unique (key, year)
```
`key` is `sales_invoice` or `purchase_invoice`. Read with `lockForUpdate()`
inside the transaction — never `max(id)+1`.

### parties
```
id, name, is_customer bool default 0, is_supplier bool default 0,
phone, email, address, tax_number, notes,
is_active bool default 1, timestamps
index (is_customer), index (is_supplier)
```
The same party can be a customer and a supplier at once.

### items
```
id, sku varchar unique, name,
type       enum('product','service')
unit_price decimal(12,2)
cost_price decimal(12,2) default 0
is_active  bool default 1, timestamps
```
A `service` never touches stock, on either side.

### documents
```
id
doc_type      enum('sales_invoice','purchase_invoice')
number        varchar nullable          -- assigned on posting only
party_id      FK -> parties
doc_date      date
due_date      date nullable
status        enum('draft','posted','partial','settled','void') default 'draft'
subtotal      decimal(12,2) default 0
discount      decimal(12,2) default 0
tax_amount    decimal(12,2) default 0
total         decimal(12,2) default 0
settled_total decimal(12,2) default 0
external_ref  varchar nullable          -- supplier's own invoice number
notes, posted_at datetime nullable, timestamps
unique (doc_type, number)
index (party_id, doc_type, status), index (doc_type, doc_date)
```

### document_lines
```
id, document_id FK cascade, item_id FK nullable,
description, qty decimal(12,3), unit_price decimal(12,2), line_total decimal(12,2),
meta jsonb nullable, sort_order int default 0, timestamps
```

### payments
```
id, direction enum('in','out'), party_id FK, payment_date date,
amount decimal(12,2), method enum('cash','card','transfer','cheque'),
reference, notes, timestamps
```

### payment_allocations
```
id, payment_id FK cascade, document_id FK, amount decimal(12,2), timestamps
unique (payment_id, document_id)
```

### expenses
```
id, expense_date date, category varchar, description varchar nullable,
supplier_id FK -> parties nullable (restrictOnDelete),
amount decimal(12,2),
method enum('cash','card','transfer','cheque'),
reference varchar nullable, notes text nullable, timestamps
index (expense_date), index (category)
```
Standalone. No lifecycle, no allocations, no service — a controller writes it
straight. `category` is free text; `config/expenses.php` supplies the dropdown.

### audit_logs
```
id, user_id FK -> users nullable (nullOnDelete),
action varchar, auditable_type varchar nullable, auditable_id bigint nullable,
summary varchar, properties jsonb nullable, created_at
index (auditable_type, auditable_id), index (created_at)
```
Append-only: no `updated_at`. `App\Listeners\AuditLogSubscriber` writes a row on
`DocumentPosted` / `DocumentVoided` / `PaymentRecorded`, inside the same
transaction as the action, capturing `auth()->id()`.

### item_variants  *(clothing)*
```
id, item_id FK, size varchar, color varchar, sku varchar unique,
stock_qty int default 0, reorder_level int default 0, timestamps
```

### stock_movements  *(clothing)*
```
id, item_variant_id FK, direction enum('in','out'), qty int,
unit_cost decimal(12,2) nullable,
reference_type varchar, reference_id bigint nullable,
moved_at datetime, note, timestamps
index (item_variant_id, moved_at)
```

### document_line_variants  *(clothing)*
```
id, document_line_id FK cascade, item_variant_id FK, timestamps
unique (document_line_id)
```
This table is the whole trick: the module attaches a variant to an invoice line
**without** adding a column to `document_lines`.

### patients  *(clinic)*
```
id, party_id FK -> parties, date_of_birth date nullable,
gender enum('male','female') nullable, notes, timestamps
```

### appointments  *(clinic)*
```
id, patient_id FK, doctor_name varchar, starts_at datetime,
duration_minutes int default 30, service_item_id FK nullable,
status enum('scheduled','done','cancelled','invoiced') default 'scheduled',
notes, timestamps
index (starts_at)
```

---

## 6. Migration style — the template Claude Code should follow

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('doc_type', ['sales_invoice', 'purchase_invoice']);
            $table->string('number')->nullable();
            $table->foreignId('party_id')->constrained('parties')->restrictOnDelete();
            $table->date('doc_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft','posted','partial','settled','void'])
                  ->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('settled_total', 12, 2)->default(0);
            $table->string('external_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['doc_type', 'number']);
            $table->index(['party_id', 'doc_type', 'status']);
            $table->index(['doc_type', 'doc_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
```

Fixed rules for every migration:
- `restrictOnDelete()` on parties and items — they can't be deleted while in use
  (`expenses.supplier_id` follows this too).
- `cascadeOnDelete()` only on dependent lines (`document_lines`, `payment_allocations`).
- `nullOnDelete()` only on `audit_logs.user_id` — deleting a user keeps the history.
- Every amount is `decimal(12,2)`, every quantity `decimal(12,3)`. No floats anywhere.
- JSON columns are `jsonb`. Search uses `ilike`. Reports group with `date_trunc()`.
- Never edit a migration that has already run — write a new one.
