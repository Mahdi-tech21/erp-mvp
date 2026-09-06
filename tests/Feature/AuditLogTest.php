<?php

use App\Events\DocumentVoided;
use App\Exceptions\DomainException;
use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
    CompanySetting::factory()->create(['tax_rate' => 0]);
});

it('records an entry, with the acting user, when a document is posted', function () {
    $document = app(DocumentService::class)->post(
        draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 100]])
    );

    $log = AuditLog::where('action', 'document.posted')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($this->admin->id)
        ->and($log->auditable_id)->toBe($document->id)
        ->and($log->summary)->toContain($document->number)
        ->and($log->properties['doc_type'])->toBe('sales_invoice');
});

it('records an entry when a document is voided', function () {
    $document = app(DocumentService::class)->post(draftDocument());
    app(DocumentService::class)->void($document);

    expect(AuditLog::where('action', 'document.voided')->exists())->toBeTrue();
});

it('records an entry when a payment is recorded', function () {
    $document = app(DocumentService::class)->post(
        draftDocument('sales_invoice', [['qty' => 1, 'unit_price' => 100]])
    );

    app(PaymentService::class)->record([
        'direction' => 'in',
        'party_id' => $document->party_id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'method' => 'cash',
    ], [['document_id' => $document->id, 'amount' => 50]]);

    $log = AuditLog::where('action', 'payment.recorded')->first();

    expect($log->summary)->toContain('cash')
        ->and($log->properties['allocations'])->toBe(1);
});

it('rolls the audit row back when the action is vetoed', function () {
    Event::listen(DocumentVoided::class, fn () => throw new DomainException('veto'));

    $document = app(DocumentService::class)->post(draftDocument());
    expect(AuditLog::where('action', 'document.posted')->count())->toBe(1);

    try {
        app(DocumentService::class)->void($document);
    } catch (DomainException) {
        // expected
    }

    expect(AuditLog::where('action', 'document.voided')->count())->toBe(0);
});

it('shows the audit log screen', function () {
    app(DocumentService::class)->post(draftDocument());

    $this->get(route('audit.index'))
        ->assertOk()
        ->assertSee('document.posted')
        ->assertSee($this->admin->name);
});
