# ERP MVP — Build Plan (v2)

## Scope
| In | Out |
|---|---|
| Parties (customers + suppliers), items | General ledger, chart of accounts, journal entries |
| Sales invoices **and** purchase invoices, one engine | Credit notes, quotations, delivery notes |
| Payments in and out, allocated to documents | Multi-currency, multi-company, bank reconciliation |
| Expenses — non-invoice spend (rent, salaries, fuel, fees) | Recurring invoices, bank feeds, cash/bank accounts |
| Single company-level VAT rate | Per-line tax, tax groups, withholding |
| Reports: sales, purchases, A/R & A/P aging, party statement, VAT return, gross margin | Budgets, cost centres, fixed assets, balance sheet |
| Clothing module: variants, stock in/out, valuation, low stock | Purchase orders, goods receipt notes, barcodes |
| Clinic module: patients, appointments, invoice-from-appointment | Calendar UI, conflict detection, medical records |
| Login gate, one seeded admin user, audit log | Roles, permissions, self-registration |

The demo lands on **modularity + a correct document/payment engine**. Feature
count is not the point. Cut from the bottom of the Night 2 list if you run late.

---

## Core schema

### company_settings
`id, name, address, phone, currency (default 'USD'), tax_rate decimal(5,2) default 11.00,
sales_prefix (default 'INV'), purchase_prefix (default 'BILL')`
Single row, seeded.

### parties
```
id, name, is_customer bool default 0, is_supplier bool default 0,
phone, email, address, tax_number, notes, is_active bool default 1, timestamps
index (is_customer), index (is_supplier)
```
A party can be both. UI has two list screens (Customers / Suppliers) filtered on
the flags, and one abstract `BasePartyController` with thin `CustomerController`
and `SupplierController` children. One shared form, labels from config.

### items
```
id, sku unique, name, type ENUM('product','service'),
unit_price decimal(12,2), cost_price decimal(12,2) default 0,
is_active bool default 1, timestamps
```
`type = 'service'` never touches stock, in either direction.

### sequences
`id, key, year, next_number` — `unique(key, year)`.
Keys: `sales_invoice`, `purchase_invoice`.

### documents
```
id,
doc_type ENUM('sales_invoice','purchase_invoice'),
number nullable,                       -- assigned on post, unique(doc_type, number)
party_id FK -> parties,
doc_date date, due_date date nullable,
status ENUM('draft','posted','partial','settled','void') default 'draft',
subtotal      decimal(12,2) default 0,
discount      decimal(12,2) default 0,
tax_amount    decimal(12,2) default 0,
total         decimal(12,2) default 0,
settled_total decimal(12,2) default 0,  -- sum of allocated payments
external_ref  varchar nullable,         -- supplier's own invoice number
notes, posted_at nullable, timestamps
index (party_id, doc_type, status), index (doc_type, doc_date)
```

### document_lines
```
id, document_id FK cascade, item_id FK nullable,
description, qty decimal(12,3), unit_price decimal(12,2), line_total decimal(12,2),
meta jsonb nullable,      -- light module payloads only (e.g. appointment_id)
sort_order int, timestamps
```

### payments
```
id, direction ENUM('in','out'), party_id FK, payment_date date,
amount decimal(12,2), method ENUM('cash','card','transfer','cheque'),
reference, notes, timestamps
```
`in` = money received from a customer. `out` = money paid to a supplier.

### payment_allocations
```
id, payment_id FK cascade, document_id FK, amount decimal(12,2), timestamps
unique (payment_id, document_id)
```

### expenses
```
id, expense_date date, category varchar, description varchar nullable,
supplier_id FK -> parties nullable, amount decimal(12,2),
method ENUM('cash','card','transfer','cheque'), reference varchar nullable,
notes text nullable, timestamps
index (expense_date), index (category)
```
Non-invoice spend — rent, salaries, fuel, bank fees. A plain fact, recorded once:
no draft/post lifecycle, no allocations, no engine. `category` is free text with a
suggested list in `config/expenses.php`. `supplier_id` is an optional payee.

### audit_logs
```
id, user_id FK -> users nullable, action varchar,
auditable_type varchar nullable, auditable_id bigint nullable,
summary varchar, properties jsonb nullable, created_at
```
Append-only (no `updated_at`). Written by `AuditLogSubscriber` on `DocumentPosted`
/ `DocumentVoided` / `PaymentRecorded`, inside the service transaction.

### assistant_conversations / assistant_messages
```
assistant_conversations: id, user_id FK cascade, timestamps
assistant_messages: id, assistant_conversation_id FK cascade,
                    role ENUM('user','assistant'), content text, timestamps
```
Backs the in-app help assistant (a floating "Ask AI" widget). Swappable
driver via `config/assistant.php`: `canned` (matches `resources/assistant/
topics.php`, no API, no cost - the default) or `claude` (Anthropic SDK,
dormant until installed + keyed). `AssistantController` streams the reply;
no queue.

---

## Document engine (the part worth showing)

`App\Services\DocumentService`

**post(Document)**
1. Reject if status != `draft`, or if it has no lines.
2. Reject if `doc_type = sales_invoice` and party is not a customer (same for supplier).
3. Recompute: `subtotal` = sum of line totals, `tax_amount` = (subtotal − discount) × rate,
   `total` = subtotal − discount + tax_amount. Round each to 2dp at the point of storage.
4. Assign `number` via `NumberGenerator::next($docType)` — `lockForUpdate()` on the
   `sequences` row, inside the same transaction. Never `max(id)+1`.
5. Set `status = posted`, `posted_at = now()`.
6. Fire `DocumentPosted($document)`.

**void(Document)**
1. Reject if it has any payment allocations — settle or unallocate first.
2. Fire `DocumentVoided($document)` *before* flipping status, so listeners can
   veto (the stock listener throws if reversing would drive a variant negative).
3. Set `status = void`. Keep the number. Never delete a posted document.

Draft documents are the only editable or deletable ones.

`App\Services\PaymentService::record()`
- One transaction. Allocations must target documents of the matching direction
  (`in` → sales_invoice, `out` → purchase_invoice) and the same party.
- Each allocation ≤ that document's remaining balance. Sum of allocations ≤ payment amount.
- After allocating, recompute each document's `settled_total` and set status:
  `settled_total >= total` → `settled`, `> 0` → `partial`, else stays `posted`.
- Fire `PaymentRecorded`.

---

## Reports (plain Blade tables, date-range filters)
1. **Sales by period** — posted sales invoices grouped by day, with total and VAT.
2. **Purchases by period** — same for purchase invoices.
3. **A/R aging** — per customer, outstanding split Current / 1–30 / 31–60 / 60+.
4. **A/P aging** — same for suppliers.
5. **Party statement** — documents and payments for one party, running balance.
6. **VAT return** — for a date range: output VAT (non-void sales) − input VAT
   (non-void purchases) = net payable. Optional month-by-month rows.
7. **Gross margin** — for a date range: revenue (sales subtotal − discount, ex-VAT)
   − COGS (Σ line qty × `items.cost_price`) − expenses = operating result.

These three (VAT return, gross margin, expenses) exist **without a general
ledger**. COGS uses the item's current `cost_price`, not a per-line snapshot — an
approximation, and that is stated on the report. A true P&L / balance sheet stays
out of scope.

---

## Clothing module (`ACTIVE_MODULES=clothing`)

### item_variants
`id, item_id FK, size, color, sku unique, stock_qty int default 0, reorder_level int default 0, timestamps`

### stock_movements
```
id, item_variant_id FK, direction ENUM('in','out'), qty int,
unit_cost decimal(12,2) nullable, reference_type, reference_id,
moved_at datetime, note, timestamps
index (item_variant_id, moved_at)
```

### document_line_variants
`id, document_line_id FK cascade, item_variant_id FK, unique(document_line_id)`
Module-owned link table. This is how the module attaches a variant to a core
line without adding a column to `document_lines`.

Behaviour:
- **Stock** screen: variants list, add variant, manual adjustment (writes a
  movement with `reference_type = 'adjustment'`).
- When active, the module registers a Blade partial that the core line form
  renders — a variant dropdown. The core never mentions variants.
- `DocumentPosted` listener:
  - `purchase_invoice` → `in` movement per line with a variant, `unit_cost` = line unit price, `stock_qty += qty`.
  - `sales_invoice` → `out` movement, `stock_qty -= qty`.
- `DocumentVoided` listener reverses it, and throws if the reversal would make
  `stock_qty` negative.
- Reports: **stock on hand** (qty × last unit_cost = valuation) and **low stock**
  (`stock_qty <= reorder_level`).

---

## Clinic module (`ACTIVE_MODULES=clinic`)

### patients
`id, party_id FK -> parties, date_of_birth date nullable, gender, notes, timestamps`
Creating a patient creates the linked party with `is_customer = 1`, so invoicing
works with zero core changes.

### appointments
```
id, patient_id FK, doctor_name, starts_at datetime, duration_minutes int default 30,
service_item_id FK nullable, status ENUM('scheduled','done','cancelled','invoiced'),
notes, timestamps
index (starts_at)
```

Behaviour:
- Patients CRUD. Appointments: day list filtered by date, create/edit, mark done.
- **Create invoice from appointment** → draft sales_invoice for the patient's
  party, one line from `service_item_id`, `meta.appointment_id` set, appointment
  status → `invoiced`. Same engine, different front door. This is the demo moment.

---

## Night 1 — core engine end to end
1. `laravel new erp-mvp`, DB, `.env`, `.env.testing`, seeded admin user.
2. Core migrations + models + factories + seeder (15 parties, 30 items, company settings).
3. Layout + sidebar + `ModuleRegistry` + `config/modules.php`, with **no modules enabled** —
   prove the core is a complete app on its own.
4. Parties: `BasePartyController` + `CustomerController` + `SupplierController`
   (customers list, suppliers list, shared form). Items CRUD.
5. `NumberGenerator`, `DocumentService`, `PaymentService`, the three events.
6. Sales invoices: `BaseDocumentController` + `SalesInvoiceController` — list,
   create with dynamic line rows, edit draft, post, void, print view.
   - 6a. Login gate — hand-rolled session auth, all routes behind `auth`.
   - 6b. Audit log — `AuditLogSubscriber` on the three events, `/audit` screen.
   - 6c. UI refactor — anonymous Blade component system, indigo theme,
     printable reports. Every screen on `<x-app-layout>`.
   - 6d. Help assistant — floating "Ask AI" widget, swappable driver
     (`canned` by default = free/offline; `claude` optional).
7. Purchase invoices: `PurchaseInvoiceController` (~6 lines, everything inherited)
   plus the `external_ref` field.
8. Payments in and out, with allocation to open documents.
9. Expenses — `ExpenseController` CRUD, `config/expenses.php` categories,
   `views/expenses/`. No engine, no allocations.
10. The seven reports.
11. Pest tests: post assigns sequential numbers per type; empty draft can't post;
    allocation can't exceed balance; wrong-direction allocation is rejected;
    payment flips status to partial then settled; voiding an allocated doc fails.

## Night 2 — modules
1. **Clinic first** (smaller, guarantees two modules exist): provider, migrations,
   patients, appointments, invoice-from-appointment, seeder. **Done.**
   Module lives in `app/Modules/Clinic/` (autoloaded by the `App\` PSR-4 root, no
   composer change). `ClinicServiceProvider` loads routes/views/migrations and
   registers menu items + a demo seeder via `ModuleRegistry` (`addMenuItem`,
   `addSeeder`). `ACTIVE_MODULES` set for tests in `phpunit.xml`.
2. **Clothing**: provider, migrations, variants, stock screens, line partial,
   stock listeners, seeder.
3. Clothing reports: stock on hand, low stock.
4. Tests: purchase posting raises stock, sale lowers it, void restores it,
   void that would go negative is blocked, appointment produces a correct draft.
5. Dashboard tiles: receivables total, payables total, open documents, today's
   appointments (clinic) / low stock count (clothing).
6. `.env` presets to flip `ACTIVE_MODULES` live. `.env.clinic` done
   (`cp .env.clinic .env && php artisan config:clear`) — sets `APP_NAME`,
   `ACTIVE_MODULES=clinic`. The seeder is industry-aware: company name, demo
   users (`reception@erp.test` / `shopfloor@erp.test`), and module demo data
   follow the active module. `.env.clothing` lands with step 2.

**Stretch, only if ahead:** per-line `cost_price` snapshot on `document_lines` so
the gross-margin report's COGS is exact instead of using the item's current cost.

---

## The 5-minute demo
1. `ACTIVE_MODULES=clothing`. Buy 50 shirts from a supplier — post the purchase,
   stock goes up. Sell 3 — post the invoice, stock goes down. Record the customer
   payment, invoice flips to settled.
2. Open A/R aging and A/P aging side by side.
3. Change one env value, reload. Same app is now a clinic: patients, today's
   appointments, invoice created from an appointment.
4. Open `app/Modules/` and say the line: *one core, one module per client industry,
   no forks of the codebase.*
5. Run `php artisan test` in front of him.
