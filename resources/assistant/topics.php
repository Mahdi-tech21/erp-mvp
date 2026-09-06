<?php

/*
|--------------------------------------------------------------------------
| Assistant knowledge base
|--------------------------------------------------------------------------
|
| One entry per thing a user might ask about. The `canned` driver matches
| the user's question against `keywords` + the canonical `question` and
| returns `answer` verbatim. The `claude` driver folds every `answer` into
| its system prompt as the grounding material. Keep answers short, concrete
| and written for a non-technical business owner.
|
*/

return [

    [
        'question' => 'What is this system?',
        'keywords' => ['what is this', 'overview', 'introduce', 'what can you', 'what can this', 'help me with', 'get started', 'how does this work'],
        'answer' => "This is your accounting system. It handles:\n\n- **Customers & suppliers** — the people you sell to and buy from.\n- **Items** — the products and services on your price list.\n- **Invoices** — what you bill customers.\n- **Bills** — what suppliers bill you.\n- **Payments** — money in from customers, money out to suppliers.\n- **Expenses** — other costs like rent, fuel and fees.\n- **Reports** — who owes you, who you owe, VAT, and profit.\n\nAsk me about any of these.",
    ],

    [
        'question' => 'How do I create an invoice?',
        'keywords' => ['create invoice', 'new invoice', 'raise invoice', 'make an invoice', 'bill a customer', 'add invoice'],
        'answer' => "1. Open **Invoices** in the sidebar and click **New Invoice**.\n2. Pick the **customer**, set the **date**, and add a **due date** if you want one.\n3. Add one line per thing you're charging for — pick an item (its price fills in) or type a description, then set the quantity.\n4. Add a discount if needed. The subtotal, VAT and total update as you type.\n5. Click **Save draft**.\n\nA saved invoice is still a **draft** — nothing is final until you post it.",
    ],

    [
        'question' => 'What does posting an invoice do?',
        'keywords' => ['post invoice', 'posting', 'what does post', 'finalise', 'finalize', 'confirm invoice', 'issue invoice'],
        'answer' => "Posting turns a draft into a real, issued document:\n\n- It gets its **number** (e.g. INV-2026-0001).\n- The totals are **frozen** — they can't change afterwards.\n- It starts counting towards what the customer owes you and shows up in your reports.\n\nAfter posting you can no longer edit it. If something is wrong, you **void** it and start again. Post from the invoice's page with the **Post** button.",
    ],

    [
        'question' => 'What do the invoice statuses mean?',
        'keywords' => ['status', 'draft', 'posted', 'partial', 'settled', 'void', 'what does draft mean', 'what does settled mean'],
        'answer' => "- **Draft** — still being worked on. Editable and deletable.\n- **Posted** — issued and frozen. The customer owes the full amount.\n- **Partial** — some payment has been received, but not all.\n- **Settled** — fully paid.\n- **Void** — cancelled. It keeps its number for the record but counts for nothing.",
    ],

    [
        'question' => "Why can't I edit this invoice?",
        'keywords' => ['cannot edit', "can't edit", 'edit posted', 'edit invoice', 'no edit button', 'why is it locked', 'change a posted'],
        'answer' => "Once an invoice is **posted** it's locked — its number and totals are a permanent record. Only **draft** invoices can be edited or deleted.\n\nIf a posted invoice is wrong and has **no payments** against it, use the **Void** button to cancel it, then create a corrected one.",
    ],

    [
        'question' => 'How do I void or cancel an invoice?',
        'keywords' => ['void', 'cancel invoice', 'delete posted', 'undo post', 'reverse invoice', 'cancel a bill'],
        'answer' => "Open the invoice and click **Void**. It stays in the system with its number but no longer counts towards anything.\n\nYou can only void a document that has **no payments allocated** to it. If a payment is attached, that has to be dealt with first.",
    ],

    [
        'question' => 'How do I record a payment from a customer?',
        'keywords' => ['record payment', 'customer paid', 'receive payment', 'money in', 'mark as paid', 'log a payment', 'enter payment'],
        'answer' => "1. Go to **Payments** and click **Customer payment**.\n2. Pick the **customer**. Their open invoices appear.\n3. Enter the **amount**, **date** and **method** (cash, card, transfer, cheque).\n4. In the **Allocate** column, put how much of this payment goes against each invoice — or click **full** to cover one completely.\n5. Save.\n\nInvoices you fully cover flip to **Settled**; partly covered ones show as **Partial**.",
    ],

    [
        'question' => 'How do I record a payment to a supplier?',
        'keywords' => ['pay a supplier', 'supplier payment', 'money out', 'pay a bill', 'paid supplier', 'record supplier payment'],
        'answer' => 'Go to **Payments → Supplier payment**, pick the supplier, enter the amount and method, then allocate it against their open **bills**. It works exactly like a customer payment, just in the other direction.',
    ],

    [
        'question' => 'What does allocated and unallocated mean?',
        'keywords' => ['allocated', 'unallocated', 'allocation', 'what is allocation', 'on account', 'apply payment'],
        'answer' => "**Allocating** a payment means saying which invoice or bill it pays off. The invoice's remaining balance is its total minus what's been allocated to it.\n\nIf you record a payment without allocating all of it, the leftover is **unallocated** — a credit sitting on that customer's or supplier's account. You can allocate it to an invoice later.",
    ],

    [
        'question' => 'What is a bill and how is it different from an invoice?',
        'keywords' => ['bill', 'purchase invoice', 'supplier invoice', 'difference between bill and invoice', 'record a bill', 'enter a supplier bill'],
        'answer' => "A **bill** is a purchase invoice — something a **supplier** charges **you**. It works just like a customer invoice (draft → post → pay) but on the buying side, and it has an extra field for the **supplier's own invoice number** so you can match it to their paperwork. Find them under **Bills**.",
    ],

    [
        'question' => 'How do customers and suppliers work?',
        'keywords' => ['customer', 'supplier', 'party', 'add a customer', 'add a supplier', 'same company both', 'contact'],
        'answer' => "A contact can be a **customer**, a **supplier**, or both. The **Customers** and **Suppliers** screens are the same list filtered by role. Add someone from either screen; tick \"Also a supplier\" (or customer) if they're both. A contact can't be deleted once they have invoices, payments or expenses against them.",
    ],

    [
        'question' => 'What are items?',
        'keywords' => ['item', 'product', 'service', 'price list', 'catalogue', 'catalog', 'add an item', 'stock'],
        'answer' => '**Items** are your price list — each has a code (SKU), a unit price and a cost price. **Products** are physical goods; **services** are labour or time. When you add an item to an invoice line its price fills in automatically. The cost price feeds the gross-margin report.',
    ],

    [
        'question' => 'How do I record an expense?',
        'keywords' => ['expense', 'record expense', 'rent', 'fuel', 'bank charges', 'overhead', 'cost', 'add expense'],
        'answer' => "Go to **Expenses → New Expense**. Pick a date and a category (rent, utilities, fuel, bank charges…), enter the amount and how it was paid, and optionally link a supplier as the payee. Expenses are just recorded — there's no draft or posting step. They feed the gross-margin report.",
    ],

    [
        'question' => 'How do I see who owes me money?',
        'keywords' => ['who owes me', 'receivables', 'ar aging', 'a/r aging', 'outstanding invoices', 'overdue customers', 'debtors', 'aged receivables'],
        'answer' => 'Open **Reports → A/R aging**. It lists each customer and how much they owe, split by how overdue it is: **Current**, **1–30**, **31–60** and **60+** days. The 60+ column is the one to chase.',
    ],

    [
        'question' => 'How do I see who I owe money to?',
        'keywords' => ['who do i owe', 'payables', 'ap aging', 'a/p aging', 'unpaid bills', 'aged payables', 'creditors', 'what do we owe'],
        'answer' => 'Open **Reports → A/P aging**. Same idea as A/R aging but for suppliers — each supplier and what you owe them, bucketed by how overdue it is.',
    ],

    [
        'question' => 'What is a party statement?',
        'keywords' => ['statement', 'party statement', 'customer statement', 'account history', 'running balance', 'transactions for one customer'],
        'answer' => '**Reports → Party statement** shows every invoice/bill and every payment for one contact over a date range, with a running balance. Choose whether to view them as a **customer** or a **supplier**. A positive balance means they owe you (customer side) or you owe them (supplier side).',
    ],

    [
        'question' => 'What is the VAT return report?',
        'keywords' => ['vat', 'vat return', 'tax return', 'output vat', 'input vat', 'how much vat', 'tax owed'],
        'answer' => "**Reports → VAT return** takes a date range and shows **output VAT** (charged on your sales) minus **input VAT** (paid on your purchases). The difference is what you owe the tax office — or reclaim, if it's negative. There's a month-by-month breakdown too.",
    ],

    [
        'question' => 'How do I check if the business is making money?',
        'keywords' => ['profit', 'making money', 'gross margin', 'margin', 'profitability', 'income', 'am i profitable', 'p&l', 'profit and loss'],
        'answer' => "**Reports → Gross margin** takes a date range and works out: **revenue** (sales excluding VAT) − **cost of goods sold** − **expenses** = **operating result**. It's an approximation (it uses each item's current cost price), not a formal profit-and-loss statement, but it tells you if you're ahead.",
    ],

    [
        'question' => 'How does the numbering work?',
        'keywords' => ['number', 'numbering', 'invoice number', 'inv-', 'bill-', 'sequence', 'reference number'],
        'answer' => 'Numbers are assigned only when you **post** a document, never before. Invoices get **INV-YEAR-####** and bills get **BILL-YEAR-####**, each on its own sequence that resets every year. Numbers are never reused, even if a document is voided.',
    ],

    [
        'question' => 'What is the audit log?',
        'keywords' => ['audit', 'audit log', 'who did what', 'history of changes', 'track changes', 'activity log'],
        'answer' => "**Reports → Audit log** (under System) records every significant action — a document posted, a document voided, a payment recorded — with who did it and when. It's read-only.",
    ],

];
